<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class BillingService
{
    private const ACCOUNT_META = '_woogit_account_id';
    private const SITE_META = '_woogit_site_id';
    private const PRODUCT_META = '_woogit_plan_product_id';
    private const PLAN_KEY_META = '_woogit_plan_key';

    public function registerHooks(): void
    {
        add_action('woocommerce_payment_complete', [$this, 'onOrderPaid'], 20, 1);
        add_action('woocommerce_order_status_processing', [$this, 'onOrderPaid'], 20, 1);
        add_action('woocommerce_order_status_completed', [$this, 'onOrderPaid'], 20, 1);
        if (function_exists('wcs_get_subscription')) {
            add_action('woocommerce_subscription_status_active', [$this, 'onSubscriptionActive'], 20, 1);
            add_action('woocommerce_subscription_payment_complete', [$this, 'onSubscriptionPaymentComplete'], 20, 1);
        }
    }

    public function getPlans(): array
    {
        if (!function_exists('wc_get_products')) return [];
        $products = wc_get_products([
            'status' => 'publish',
            'limit' => -1,
            'type' => ['subscription', 'variable-subscription'],
            'orderby' => 'menu_order',
            'order' => 'ASC',
        ]);
        $plans = [];
        foreach ($products as $product) {
            if (!$product || !$product->is_purchasable()) continue;
            $plans[] = [
                'id' => (int)$product->get_id(),
                'name' => (string)$product->get_name(),
                'price' => (string)$product->get_price(),
                'regular_price' => (string)$product->get_regular_price(),
                'currency' => function_exists('get_woocommerce_currency') ? (string)get_woocommerce_currency() : '',
                'billing_period' => (string)$product->get_meta('_subscription_period'),
                'billing_interval' => (int)($product->get_meta('_subscription_period_interval') ?: 1),
                'description' => wp_strip_all_tags((string)$product->get_short_description()),
            ];
        }
        return $plans;
    }

    public function createCheckout(int $accountId, int $siteId, int $productId): array
    {
        if (!function_exists('wc_get_product') || !function_exists('wc_create_order')) {
            return ['ok' => false, 'code' => 'billing_unavailable'];
        }
        $product = wc_get_product($productId);
        if (!$product || !$product->exists() || $product->get_status() !== 'publish' || !$product->is_purchasable()) {
            return ['ok' => false, 'code' => 'plan_not_found'];
        }
        $type = (string)$product->get_type();
        if (!in_array($type, ['subscription', 'variable-subscription'], true)) {
            return ['ok' => false, 'code' => 'plan_not_subscription'];
        }

        $order = wc_create_order(['status' => 'pending']);
        if (is_wp_error($order)) return ['ok' => false, 'code' => 'checkout_creation_failed'];

        $item = $order->add_product($product, 1);
        if (!$item) {
            $order->delete(true);
            return ['ok' => false, 'code' => 'checkout_creation_failed'];
        }
        $order->update_meta_data(self::ACCOUNT_META, $accountId);
        $order->update_meta_data(self::SITE_META, $siteId);
        $order->update_meta_data(self::PRODUCT_META, $productId);
        $order->update_meta_data(self::PLAN_KEY_META, sanitize_title((string)$product->get_name()));
        $order->set_created_via('woogit');
        $order->calculate_totals();
        $order->save();

        return [
            'ok' => true,
            'order_id' => (int)$order->get_id(),
            'payment_url' => (string)$order->get_checkout_payment_url(true),
            'status' => (string)$order->get_status(),
        ];
    }

    public function getStatus(int $accountId, int $siteId): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'woogit_entitlements';
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT status,starts_at,expires_at,capabilities FROM {$table} WHERE account_id=%d AND site_id=%d LIMIT 1",
            $accountId, $siteId
        ), ARRAY_A);
        if (!$row) return ['status' => 'none', 'starts_at' => null, 'expires_at' => null, 'capabilities' => []];
        $caps = json_decode((string)$row['capabilities'], true);
        return [
            'status' => (string)$row['status'],
            'starts_at' => $row['starts_at'],
            'expires_at' => $row['expires_at'],
            'capabilities' => is_array($caps) ? array_values($caps) : [],
        ];
    }

    public function onOrderPaid(int $orderId): void
    {
        if (!function_exists('wc_get_order')) return;
        $order = wc_get_order($orderId);
        if (!$order) return;
        $accountId = (int)$order->get_meta(self::ACCOUNT_META);
        $siteId = (int)$order->get_meta(self::SITE_META);
        if ($accountId <= 0 || $siteId <= 0) return;

        if (function_exists('wcs_get_subscriptions_for_order')) {
            $subscriptions = wcs_get_subscriptions_for_order($orderId, ['order_type' => 'parent']);
            if (!empty($subscriptions)) return;
        }
        $productId = (int)$order->get_meta(self::PRODUCT_META);
        $product = function_exists('wc_get_product') ? wc_get_product($productId) : null;
        $days = $this->durationDays($product);
        if ($days > 0) $this->activate($accountId, $siteId, null, time() + ($days * DAY_IN_SECONDS));
    }

    public function onSubscriptionActive($subscriptionId): void
    {
        $this->syncSubscription((int)$subscriptionId);
    }

    public function onSubscriptionPaymentComplete($subscription): void
    {
        $id = is_object($subscription) && method_exists($subscription, 'get_id') ? (int)$subscription->get_id() : (int)$subscription;
        if ($id > 0) $this->syncSubscription($id);
    }

    private function syncSubscription(int $subscriptionId): void
    {
        if (!function_exists('wcs_get_subscription')) return;
        $subscription = wcs_get_subscription($subscriptionId);
        if (!$subscription || !method_exists($subscription, 'get_parent_id')) return;
        $parentId = (int)$subscription->get_parent_id();
        if ($parentId <= 0 && method_exists($subscription, 'get_id')) $parentId = (int)$subscription->get_id();
        if (!function_exists('wc_get_order')) return;
        $order = wc_get_order($parentId);
        if (!$order) return;
        $accountId = (int)$order->get_meta(self::ACCOUNT_META);
        $siteId = (int)$order->get_meta(self::SITE_META);
        if ($accountId <= 0 || $siteId <= 0) return;
        $nextPayment = method_exists($subscription, 'get_time') ? (int)$subscription->get_time('next_payment') : 0;
        if ($nextPayment <= 0) $nextPayment = 0;
        $status = method_exists($subscription, 'has_status') && $subscription->has_status(['active', 'pending-cancel']) ? 'active' : 'inactive';
        if ($status !== 'active') return;
        $this->activate($accountId, $siteId, $subscriptionId, $nextPayment ?: null);
    }

    private function activate(int $accountId, int $siteId, ?int $subscriptionId, ?int $expiresTimestamp): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'woogit_entitlements';
        $existing = $wpdb->get_row($wpdb->prepare("SELECT starts_at,expires_at FROM {$table} WHERE account_id=%d AND site_id=%d LIMIT 1", $accountId, $siteId), ARRAY_A);
        $now = time();
        $starts = $now;
        if ($existing && !empty($existing['expires_at'])) {
            $old = strtotime((string)$existing['expires_at']);
            if ($old > $now) $starts = $old;
        }
        $expires = $expiresTimestamp && $expiresTimestamp > $starts ? gmdate('Y-m-d H:i:s', $expiresTimestamp) : null;
        if ($subscriptionId && !$expires) return;
        if ($expires === null && !$subscriptionId) return;

        $data = [
            'status' => 'active',
            'starts_at' => gmdate('Y-m-d H:i:s', $starts),
            'expires_at' => $expires,
            'capabilities' => wp_json_encode(['commerce']),
            'updated_at' => gmdate('Y-m-d H:i:s'),
        ];
        if ($existing) {
            $wpdb->update($table, $data, ['account_id' => $accountId, 'site_id' => $siteId], ['%s','%s','%s','%s','%s'], ['%d','%d']);
        } else {
            $wpdb->insert($table, array_merge($data, ['account_id' => $accountId, 'site_id' => $siteId, 'created_at' => gmdate('Y-m-d H:i:s')]), ['%s','%s','%s','%s','%s','%d','%d','%s']);
        }
    }

    private function durationDays($product): int
    {
        if (!$product) return 0;
        $period = (string)$product->get_meta('_subscription_period');
        $interval = max(1, (int)($product->get_meta('_subscription_period_interval') ?: 1));
        return match ($period) {
            'day' => $interval,
            'week' => $interval * 7,
            'month' => $interval * 30,
            'year' => $interval * 365,
            default => 0,
        };
    }
}
