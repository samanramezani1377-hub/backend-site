<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

/**
 * Rebuilds WooGit entitlement expiry from successful WooGit subscription payments.
 *
 * Milo owns subscription scheduling. WooGit entitlement represents paid access,
 * so the paid orders are the durable source for stacking purchased periods.
 */
final class MiloEntitlementReconciler
{
    private const ACCOUNT_META = '_woogit_account_id';
    private const SITE_META = '_woogit_site_id';
    private const PRODUCT_META = '_woogit_plan_product_id';
    private const VARIATION_META = '_woogit_plan_variation_id';

    public function registerHooks(): void
    {
        add_action('woocommerce_payment_complete', [$this, 'onOrderPaid'], 25, 1);
        add_action('woocommerce_order_status_processing', [$this, 'onOrderPaid'], 25, 1);
        add_action('woocommerce_order_status_completed', [$this, 'onOrderPaid'], 25, 1);
        add_action('milo_subscriptions_renewal_payment_complete', [$this, 'onMiloRenewalPaid'], 25, 2);
        add_filter('rest_request_before_callbacks', [$this, 'beforeBillingStatus'], 5, 3);
    }

    public function onOrderPaid(int $orderId): void
    {
        if (!function_exists('wc_get_order')) return;
        $order = wc_get_order($orderId);
        if (!$order) return;
        $accountId = (int)$order->get_meta(self::ACCOUNT_META);
        $siteId = (int)$order->get_meta(self::SITE_META);
        if ($accountId <= 0 || $siteId <= 0) return;
        $this->reconcile($accountId, $siteId);
    }

    public function onMiloRenewalPaid($subscription, $order = null): void
    {
        $candidate = is_object($order) && method_exists($order, 'get_id') ? $order : null;
        if (!$candidate && is_object($subscription) && method_exists($subscription, 'get_parent_id') && function_exists('wc_get_order')) {
            $parentId = (int)$subscription->get_parent_id();
            $candidate = $parentId > 0 ? wc_get_order($parentId) : null;
        }
        if ($candidate) {
            $accountId = (int)$candidate->get_meta(self::ACCOUNT_META);
            $siteId = (int)$candidate->get_meta(self::SITE_META);
            if ($accountId > 0 && $siteId > 0) $this->reconcile($accountId, $siteId);
        }
    }

    /**
     * Reconcile before the billing-status response so already-paid historical
     * orders are fixed without requiring the customer to buy another period.
     */
    public function beforeBillingStatus($response, $handler, $request)
    {
        if (!$request instanceof \WP_REST_Request) return $response;
        if (strtoupper((string)$request->get_method()) !== 'GET') return $response;
        if ($request->get_route() !== '/woogit/v1/billing/status') return $response;

        $webToken = trim((string)$request->get_header('X-WooGit-Web-Session'));
        $accountId = 0;
        $siteId = 0;
        if ($webToken !== '') {
            $session = (new WebSessionService())->authenticate($webToken);
        } else {
            $session = (new SessionService())->authenticate((string)$request->get_header('X-WooGit-Session'));
        }
        if (!is_array($session)) return $response;
        $accountId = (int)($session['account_id'] ?? 0);
        $siteId = (int)($session['site_id'] ?? 0);
        if ($accountId <= 0 || $siteId <= 0) return $response;
        if (!(new AccountService())->get($accountId)) return $response;
        if (!(new SiteService())->getOwned($accountId, $siteId)) return $response;

        $this->reconcile($accountId, $siteId);
        return $response;
    }

    public function reconcile(int $accountId, int $siteId): void
    {
        if ($accountId <= 0 || $siteId <= 0 || !function_exists('wc_get_orders')) return;

        $orders = wc_get_orders([
            'limit' => 500,
            'orderby' => 'date',
            'order' => 'ASC',
            'return' => 'objects',
            'status' => ['processing', 'completed'],
            'meta_query' => [
                ['key' => self::ACCOUNT_META, 'value' => (string)$accountId, 'compare' => '='],
                ['key' => self::SITE_META, 'value' => (string)$siteId, 'compare' => '='],
            ],
        ]);

        $cursor = 0;
        $firstPaidAt = 0;
        $found = false;
        foreach ((array)$orders as $order) {
            if (!is_object($order)) continue;
            $duration = $this->orderDurationSeconds($order);
            if ($duration <= 0) continue;
            $paidAt = 0;
            if (method_exists($order, 'get_date_paid')) {
                $datePaid = $order->get_date_paid();
                if ($datePaid) $paidAt = $datePaid->getTimestamp();
            }
            if ($paidAt <= 0 && method_exists($order, 'get_date_created')) {
                $dateCreated = $order->get_date_created();
                if ($dateCreated) $paidAt = $dateCreated->getTimestamp();
            }
            if ($paidAt <= 0) continue;
            if ($firstPaidAt <= 0) $firstPaidAt = $paidAt;
            if ($cursor < $paidAt) $cursor = $paidAt;
            $cursor += $duration;
            $found = true;
        }
        if (!$found || $cursor <= 0) return;

        global $wpdb;
        $table = $wpdb->prefix . 'woogit_entitlements';
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id,starts_at,expires_at FROM {$table} WHERE account_id=%d AND site_id=%d LIMIT 1",
            $accountId,
            $siteId
        ), ARRAY_A);

        $existingExpiry = $existing && !empty($existing['expires_at']) ? (strtotime((string)$existing['expires_at']) ?: 0) : 0;
        // Never shorten access from a manual/admin extension or another valid
        // entitlement source. Paid-order reconciliation only adds missing time.
        $expiresAt = max($existingExpiry, $cursor);
        $startsAt = $existing && !empty($existing['starts_at']) ? (strtotime((string)$existing['starts_at']) ?: $firstPaidAt) : $firstPaidAt;
        if ($startsAt <= 0) $startsAt = $firstPaidAt;

        $data = [
            'status' => $expiresAt > time() ? 'active' : 'expired',
            'starts_at' => gmdate('Y-m-d H:i:s', $startsAt),
            'expires_at' => gmdate('Y-m-d H:i:s', $expiresAt),
            'capabilities' => wp_json_encode(['commerce']),
            'updated_at' => gmdate('Y-m-d H:i:s'),
        ];
        if ($existing) {
            $wpdb->update($table, $data, ['id' => (int)$existing['id']], ['%s', '%s', '%s', '%s', '%s'], ['%d']);
        } else {
            $wpdb->insert(
                $table,
                array_merge($data, ['account_id' => $accountId, 'site_id' => $siteId, 'created_at' => gmdate('Y-m-d H:i:s')]),
                ['%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s']
            );
        }
    }

    private function orderDurationSeconds($order): int
    {
        $productId = method_exists($order, 'get_meta') ? (int)$order->get_meta(self::PRODUCT_META) : 0;
        $variationId = method_exists($order, 'get_meta') ? (int)$order->get_meta(self::VARIATION_META) : 0;
        $product = $variationId > 0 && function_exists('wc_get_product') ? wc_get_product($variationId) : null;
        if (!$product && $productId > 0 && function_exists('wc_get_product')) $product = wc_get_product($productId);
        $duration = $this->productDurationSeconds($product);
        if ($duration > 0) {
            $quantity = 1;
            if (method_exists($order, 'get_items')) {
                foreach ((array)$order->get_items('line_item') as $item) {
                    if (method_exists($item, 'get_quantity') && (int)$item->get_quantity() > 0) {
                        $quantity = (int)$item->get_quantity();
                        break;
                    }
                }
            }
            return $duration * max(1, $quantity);
        }

        $total = 0;
        if (method_exists($order, 'get_items')) {
            foreach ((array)$order->get_items('line_item') as $item) {
                $lineProduct = method_exists($item, 'get_product') ? $item->get_product() : null;
                $lineDuration = $this->productDurationSeconds($lineProduct);
                $quantity = method_exists($item, 'get_quantity') ? max(1, (int)$item->get_quantity()) : 1;
                $total += $lineDuration * $quantity;
            }
        }
        return $total;
    }

    private function productDurationSeconds($product): int
    {
        if (!is_object($product) || !method_exists($product, 'get_meta') || !method_exists($product, 'get_type')) return 0;
        $type = strtolower((string)$product->get_type());
        if (strpos($type, 'subscription') === false) return 0;
        $period = strtolower((string)$product->get_meta('_subscription_period'));
        $interval = max(1, (int)($product->get_meta('_subscription_period_interval') ?: 1));
        if ($period === '' && method_exists($product, 'get_parent_id') && (int)$product->get_parent_id() > 0 && function_exists('wc_get_product')) {
            $parent = wc_get_product((int)$product->get_parent_id());
            if ($parent) {
                $period = strtolower((string)$parent->get_meta('_subscription_period'));
                $interval = max(1, (int)($parent->get_meta('_subscription_period_interval') ?: 1));
            }
        }
        if ($period === 'day') return $interval * DAY_IN_SECONDS;
        if ($period === 'week') return $interval * 7 * DAY_IN_SECONDS;
        if ($period === 'month') return $interval * 30 * DAY_IN_SECONDS;
        if ($period === 'year') return $interval * 365 * DAY_IN_SECONDS;
        return 0;
    }
}
