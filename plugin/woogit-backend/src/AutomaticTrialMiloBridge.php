<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

/**
 * Bridges the existing automatic 15-day WooGit trial into Milo.
 *
 * Site verification remains the trigger for the automatic trial in V1, but the
 * trial is represented by a real WooCommerce/Milo subscription instead of an
 * internal-only entitlement. A configured zero-price subscription product with a
 * 15-day trial is used as the canonical trial product.
 */
final class AutomaticTrialMiloBridge
{
    private const AUTO_TRIAL_META = '_woogit_automatic_trial';
    private const AUTO_TRIAL_VALUE = 'yes';
    private const TRIAL_DAYS = 15;

    public function registerHooks(): void
    {
        add_filter('rest_request_after_callbacks', [$this, 'afterSiteVerify'], 25, 3);
    }

    /**
     * WordPress passes the second filter argument as the route attributes array,
     * not as WP_REST_Server. Keep this argument intentionally untyped.
     */
    public function afterSiteVerify($response, $server, \WP_REST_Request $request)
    {
        if (strpos($request->get_route(), '/woogit/v1/sites/verify') !== 0) return $response;
        if (strtoupper($request->get_method()) !== 'POST') return $response;
        if (is_wp_error($response) || !$response instanceof \WP_REST_Response) return $response;
        if ($response->get_status() < 200 || $response->get_status() >= 300) return $response;

        $data = $response->get_data();
        if (!is_array($data)) return $response;
        $accountId = (int)($data['account_id'] ?? 0);
        $siteId = (int)($data['site_id'] ?? 0);
        if ($accountId <= 0 || $siteId <= 0) return $response;

        // Existing automatic trial order/subscription is authoritative for this
        // Account/Site. Never create another one on a repeated verification.
        if (!$this->ensureCustomer($accountId) || !$this->ensureTrial($accountId, $siteId)) return $response;

        // Milo callbacks normally run synchronously when the zero-value order is
        // moved to processing/completed. If they did, refresh the returned session
        // so the first verification response can immediately be operational.
        $entitlements = new EntitlementService();
        if (!$entitlements->isAllowed($accountId, $siteId, 'commerce')) return $response;
        $expires = $entitlements->getExpiresAt($accountId, $siteId);
        if (!$expires || $expires <= time() + 300) return $response;
        $token = (new SessionService())->issueOperational($accountId, $siteId, $expires);
        if (!$token) return $response;

        $data['session'] = $token;
        $data['scope'] = SessionService::SCOPE_OPERATIONAL;
        $data['access_enabled'] = true;
        $data['billing_required'] = false;
        $response->set_data($data);
        return $response;
    }

    private function ensureCustomer(int $accountId): bool
    {
        $userId = (new IdentityService())->getUserId($accountId);
        if ($userId <= 0) return false;
        $user = get_userdata($userId);
        if (!$user instanceof \WP_User) return false;
        if (function_exists('wc_get_customer')) {
            // Keep the central WP/WooCommerce identity aligned with the Account.
            if (!in_array('customer', (array)$user->roles, true)) $user->set_role('customer');
        }
        return in_array('customer', (array)$user->roles, true);
    }

    private function ensureTrial(int $accountId, int $siteId): bool
    {
        if (!function_exists('wc_get_products') || !function_exists('wc_create_order')) return false;
        if (has_action('milo_subscriptions_subscription_created') === false && has_action('milo_subscriptions_subscription_manually_created') === false) return false;

        $existing = $this->findExistingTrialOrder($accountId, $siteId);
        if ($existing) return true;

        $product = $this->findTrialProduct();
        if (!$product) return false;

        $userId = (new IdentityService())->getUserId($accountId);
        $orderArgs = ['status' => 'pending'];
        if ($userId > 0) $orderArgs['customer_id'] = $userId;
        $order = wc_create_order($orderArgs);
        if (is_wp_error($order)) return false;

        $item = $order->add_product($product, 1);
        if (!$item) {
            $order->delete(true);
            return false;
        }

        $order->update_meta_data('_woogit_account_id', $accountId);
        $order->update_meta_data('_woogit_site_id', $siteId);
        $order->update_meta_data('_woogit_plan_product_id', (int)$product->get_id());
        $order->update_meta_data('_woogit_plan_key', 'trial');
        $order->update_meta_data(self::AUTO_TRIAL_META, self::AUTO_TRIAL_VALUE);
        $order->set_created_via('woogit');
        $order->calculate_totals();

        // The automatic trial is free. Refuse a misconfigured paid trial product
        // rather than silently creating a billable order from site verification.
        if ((float)$order->get_total() > 0.0) {
            $order->delete(true);
            return false;
        }
        $order->save();

        // Milo creates its real subscription from a hand/API-created order when the
        // order reaches a processing/completed state. This is a zero-value trial,
        // so no payment gateway or client-side payment confirmation is involved.
        $order->update_status('processing', 'WooGit: automatic 15-day trial.');
        if ($order->get_status() !== 'processing' && $order->get_status() !== 'completed') {
            $order->update_status('completed', 'WooGit: automatic 15-day trial.');
        }

        return true;
    }

    private function findExistingTrialOrder(int $accountId, int $siteId)
    {
        if (!function_exists('wc_get_orders')) return null;
        $orders = wc_get_orders([
            'limit' => 1,
            'orderby' => 'date',
            'order' => 'DESC',
            'return' => 'objects',
            'meta_query' => [
                ['key' => '_woogit_account_id', 'value' => (string)$accountId, 'compare' => '='],
                ['key' => '_woogit_site_id', 'value' => (string)$siteId, 'compare' => '='],
                ['key' => self::AUTO_TRIAL_META, 'value' => self::AUTO_TRIAL_VALUE, 'compare' => '='],
            ],
        ]);
        return !empty($orders) ? $orders[0] : null;
    }

    private function findTrialProduct()
    {
        $result = wc_get_products([
            'status' => 'publish',
            'limit' => 100,
            'return' => 'objects',
        ]);
        foreach ((array)$result as $product) {
            if (!$product || !method_exists($product, 'get_type')) continue;
            $type = strtolower((string)$product->get_type());
            if (strpos($type, 'subscription') === false) continue;
            if ((int)$product->get_meta('_subscription_trial_length') !== self::TRIAL_DAYS) continue;
            if ((float)$product->get_price() !== 0.0) continue;
            return $product;
        }
        return null;
    }
}
