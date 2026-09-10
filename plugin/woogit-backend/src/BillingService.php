<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class BillingService
{
    private const ACCOUNT_META = '_woogit_account_id';
    private const SITE_META = '_woogit_site_id';
    private const PRODUCT_META = '_woogit_plan_product_id';
    private const VARIATION_META = '_woogit_plan_variation_id';
    private const PLAN_KEY_META = '_woogit_plan_key';
    private const CHECKOUT_IDEMPOTENCY_META = '_woogit_checkout_idempotency_key';
    private const PLAN_ENABLED_META = '_woogit_plan_enabled';

    public function registerHooks(): void
    {
        add_action('woocommerce_payment_complete', [$this, 'onOrderPaid'], 20, 1);
        add_action('woocommerce_order_status_processing', [$this, 'onOrderPaid'], 20, 1);
        add_action('woocommerce_order_status_completed', [$this, 'onOrderPaid'], 20, 1);
        add_action('milo_subscriptions_subscription_created', [$this, 'onMiloSubscriptionCreated'], 20, 2);
        add_action('milo_subscriptions_subscription_manually_created', [$this, 'onMiloSubscriptionCreated'], 20, 1);
        add_action('milo_subscriptions_subscription_status_updated', [$this, 'onMiloSubscriptionStatusUpdated'], 20, 3);
        add_action('milo_subscriptions_renewal_order_created', [$this, 'onMiloRenewalOrderCreated'], 20, 2);
        add_action('milo_subscriptions_renewal_payment_complete', [$this, 'onMiloRenewalPaymentComplete'], 20, 2);
        add_action('milo_subscriptions_product_data_panel', [$this, 'renderPlanFields'], 20, 1);
        add_action('woocommerce_product_options_general_product_data', [$this, 'renderPlanFields'], 20, 0);
        add_action('woocommerce_process_product_meta', [$this, 'savePlanFields'], 20, 1);
        if (function_exists('wcs_get_subscription')) {
            add_action('woocommerce_subscription_status_active', [$this, 'onSubscriptionActive'], 20, 1);
            add_action('woocommerce_subscription_payment_complete', [$this, 'onSubscriptionPaymentComplete'], 20, 1);
        }
    }

    public function renderPlanFields($hookProduct = null): void
    {
        global $product_object;
        $product = is_object($hookProduct) ? $hookProduct : $product_object;
        if (!$product || !$this->isSubscriptionProduct($product)) return;
        $enabled = get_post_meta((int)$product->get_id(), self::PLAN_ENABLED_META, true);
        if ($enabled === '') $enabled = 'yes';
        $key = (string)get_post_meta((int)$product->get_id(), self::PLAN_KEY_META, true);
        if ($key === '') $key = sanitize_title((string)$product->get_name());
        echo '<div class="options_group show_if_subscription show_if_variable-subscription">';
        woocommerce_wp_checkbox(['id' => self::PLAN_ENABLED_META, 'value' => $enabled, 'label' => 'WooGit Plan', 'description' => 'این محصول به‌عنوان پلن قابل خرید WooGit در API Billing نمایش داده شود.', 'desc_tip' => true]);
        woocommerce_wp_text_input(['id' => self::PLAN_KEY_META, 'value' => $key, 'label' => 'WooGit Plan Key', 'description' => 'شناسه پایدار پلن که App می‌تواند برای نمایش/ردیابی استفاده کند.', 'desc_tip' => true]);
        echo '</div>';
    }

    public function savePlanFields(int $productId): void
    {
        if (!current_user_can('edit_post', $productId)) return;
        $product = function_exists('wc_get_product') ? wc_get_product($productId) : null;
        if (!$product || !$this->isSubscriptionProduct($product)) return;
        $enabled = isset($_POST[self::PLAN_ENABLED_META]) ? 'yes' : 'no';
        $key = isset($_POST[self::PLAN_KEY_META]) ? sanitize_title(wp_unslash((string)$_POST[self::PLAN_KEY_META])) : '';
        if ($key === '') $key = sanitize_title((string)$product->get_name());
        update_post_meta($productId, self::PLAN_ENABLED_META, $enabled);
        update_post_meta($productId, self::PLAN_KEY_META, $key);
    }

    public function getPlans(): array
    {
        if (!function_exists('wc_get_products')) return [];
        $plans = [];
        $page = 1;
        $perPage = 100;
        do {
            $result = wc_get_products(['status' => 'publish', 'limit' => $perPage, 'page' => $page, 'paginate' => true, 'orderby' => 'menu_order', 'order' => 'ASC']);
            $products = is_object($result) && isset($result->products) ? (array)$result->products : (array)$result;
            foreach ($products as $product) {
                if (!$product || !$this->isSubscriptionProduct($product) || !$product->is_purchasable() || !$this->isPlanEnabled($product)) continue;
                $type = (string)$product->get_type();
                $plan = ['id' => (int)$product->get_id(), 'key' => $this->planKey($product), 'name' => (string)$product->get_name(), 'price' => (string)$product->get_price(), 'regular_price' => (string)$product->get_regular_price(), 'currency' => function_exists('get_woocommerce_currency') ? (string)get_woocommerce_currency() : '', 'billing_period' => (string)$product->get_meta('_subscription_period'), 'billing_interval' => (int)($product->get_meta('_subscription_period_interval') ?: 1), 'description' => wp_strip_all_tags((string)$product->get_short_description()), 'type' => $type, 'requires_variation' => $this->isVariableSubscriptionProduct($product)];
                if ($this->isVariableSubscriptionProduct($product)) $plan['variations'] = $this->getVariations($product);
                $plans[] = $plan;
            }
            $maxPages = is_object($result) && isset($result->max_num_pages) ? (int)$result->max_num_pages : ($products === [] ? $page : $page);
            $page++;
        } while ($products !== [] && $page <= $maxPages);
        return $plans;
    }

    public function createCheckout(int $accountId, int $siteId, int $productId, int $variationId = 0, string $idempotencyKey = ''): array
    {
        if (!function_exists('wc_get_product') || !function_exists('wc_create_order')) return ['ok' => false, 'code' => 'billing_unavailable'];
        if ($idempotencyKey !== '') {
            $existing = $this->findCheckoutByIdempotencyKey($accountId, $siteId, $idempotencyKey);
            if ($existing['ok']) return $existing;
        }
        $product = wc_get_product($productId);
        if (!$product || !$product->exists() || $product->get_status() !== 'publish' || !$product->is_purchasable()) return ['ok' => false, 'code' => 'plan_not_found'];
        if (!$this->isSubscriptionProduct($product) || !$this->isPlanEnabled($product)) return ['ok' => false, 'code' => 'plan_not_subscription'];
        $lineProduct = $product;
        if ($this->isVariableSubscriptionProduct($product)) {
            if ($variationId <= 0) return ['ok' => false, 'code' => 'missing_plan_variation'];
            $variation = wc_get_product($variationId);
            if (!$variation || $variation->get_parent_id() !== $productId || $variation->get_status() !== 'publish' || !$variation->is_purchasable() || !$this->isSubscriptionProduct($variation)) return ['ok' => false, 'code' => 'invalid_plan_variation'];
            $lineProduct = $variation;
        } elseif ($variationId > 0) return ['ok' => false, 'code' => 'invalid_plan_variation'];
        $customerId = (new IdentityService())->getUserId($accountId);
        $orderArgs = ['status' => 'pending'];
        if ($customerId > 0) $orderArgs['customer_id'] = $customerId;
        $order = wc_create_order($orderArgs);
        if (is_wp_error($order)) return ['ok' => false, 'code' => 'checkout_creation_failed'];
        $item = $order->add_product($lineProduct, 1);
        if (!$item) {
            $order->delete(true);
            return ['ok' => false, 'code' => 'checkout_creation_failed'];
        }
        $order->update_meta_data(self::ACCOUNT_META, $accountId);
        $order->update_meta_data(self::SITE_META, $siteId);
        $order->update_meta_data(self::PRODUCT_META, $productId);
        $order->update_meta_data(self::VARIATION_META, $variationId);
        $order->update_meta_data(self::PLAN_KEY_META, $this->planKey($product));
        if ($idempotencyKey !== '') $order->update_meta_data(self::CHECKOUT_IDEMPOTENCY_META, hash('sha256', $idempotencyKey));
        $order->set_created_via('woogit');
        $order->calculate_totals();
        $order->save();
        return ['ok' => true, 'order_id' => (int)$order->get_id(), 'payment_url' => (string)$order->get_checkout_payment_url(true), 'status' => (string)$order->get_status()];
    }

    public function findCheckoutByIdempotencyKey(int $accountId, int $siteId, string $idempotencyKey): array
    {
        if ($idempotencyKey === '' || !function_exists('wc_get_orders')) return ['ok' => false];
        $orders = wc_get_orders(['limit' => 1, 'orderby' => 'date', 'order' => 'DESC', 'return' => 'objects', 'meta_query' => [['key' => self::ACCOUNT_META, 'value' => (string)$accountId, 'compare' => '='], ['key' => self::SITE_META, 'value' => (string)$siteId, 'compare' => '='], ['key' => self::CHECKOUT_IDEMPOTENCY_META, 'value' => hash('sha256', $idempotencyKey), 'compare' => '=']]]);
        if (empty($orders)) return ['ok' => false];
        $order = $orders[0];
        return ['ok' => true, 'order_id' => (int)$order->get_id(), 'payment_url' => (string)$order->get_checkout_payment_url(true), 'status' => (string)$order->get_status()];
    }

    public function getPaymentHistory(int $accountId, int $siteId, int $page = 1, int $perPage = 20): array
    {
        if (!function_exists('wc_get_orders')) return ['orders' => [], 'page' => max(1, $page), 'per_page' => min(50, max(1, $perPage)), 'total' => 0, 'total_pages' => 0];
        $page = max(1, $page);
        $perPage = min(50, max(1, $perPage));
        $orders = wc_get_orders(['limit' => $perPage, 'page' => $page, 'paginate' => true, 'return' => 'objects', 'orderby' => 'date', 'order' => 'DESC', 'meta_query' => [['key' => self::ACCOUNT_META, 'value' => (string)$accountId, 'compare' => '='], ['key' => self::SITE_META, 'value' => (string)$siteId, 'compare' => '=']]]);
        $items = [];
        foreach ((array)($orders->orders ?? []) as $order) {
            $productId = (int)$order->get_meta(self::PRODUCT_META);
            $planKey = (string)$order->get_meta(self::PLAN_KEY_META);
            if ($productId > 0 && $planKey === '') {
                $product = function_exists('wc_get_product') ? wc_get_product($productId) : null;
                $planKey = $product ? $this->planKey($product) : '';
            }
            $items[] = ['order_id' => (int)$order->get_id(), 'status' => (string)$order->get_status(), 'total' => (string)$order->get_total(), 'currency' => (string)$order->get_currency(), 'created_at' => $order->get_date_created() ? $order->get_date_created()->date('c') : null, 'plan_key' => $planKey];
        }
        return ['orders' => $items, 'page' => $page, 'per_page' => $perPage, 'total' => (int)($orders->total ?? count($items)), 'total_pages' => (int)($orders->max_num_pages ?? ($items === [] ? 0 : 1))];
    }

    public function getStatus(int $accountId, int $siteId): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'woogit_entitlements';
        $sql = 'SELECT status,starts_at,expires_at,capabilities FROM ' . $table . ' WHERE account_id=%d AND site_id=%d LIMIT 1';
        $row = $wpdb->get_row($wpdb->prepare($sql, $accountId, $siteId), ARRAY_A);
        if (!$row) return ['status' => 'none', 'starts_at' => null, 'expires_at' => null, 'capabilities' => []];
        $status = strtolower(trim((string)$row['status']));
        $expiresAt = $row['expires_at'];
        if (in_array($status, ['trial', 'active'], true) && !empty($expiresAt)) {
            $expiresTimestamp = strtotime((string)$expiresAt);
            if ($expiresTimestamp !== false && $expiresTimestamp <= time()) $status = 'expired';
        }
        $caps = json_decode((string)$row['capabilities'], true);
        return ['status' => $status, 'starts_at' => $row['starts_at'], 'expires_at' => $expiresAt, 'capabilities' => is_array($caps) ? array_values($caps) : []];
    }

    public function onOrderPaid(int $orderId): void
    {
        if (!function_exists('wc_get_order')) return;
        $order = wc_get_order($orderId);
        if (!$order) return;
        $accountId = (int)$order->get_meta(self::ACCOUNT_META);
        $siteId = (int)$order->get_meta(self::SITE_META);
        if ($accountId <= 0 || $siteId <= 0) return;
        if ($this->isMiloOrder($order)) return;
        if (function_exists('wcs_get_subscriptions_for_order')) {
            $subscriptions = wcs_get_subscriptions_for_order($orderId, ['order_type' => 'parent']);
            if (!empty($subscriptions)) return;
        }
        $productId = (int)$order->get_meta(self::PRODUCT_META);
        $product = function_exists('wc_get_product') ? wc_get_product($productId) : null;
        $days = $this->durationDays($product);
        if ($days > 0) $this->activate($accountId, $siteId, null, time() + ($days * DAY_IN_SECONDS));
    }

    public function onMiloSubscriptionCreated($subscription, $order = null): void
    {
        if (!is_object($subscription) || !method_exists($subscription, 'get_id')) return;
        $subscriptionId = (int)$subscription->get_id();
        if ($subscriptionId <= 0) return;
        $parentOrder = $this->subscriptionParentOrder($subscription, $order);
        if ($parentOrder) $this->copyWooGitMeta($parentOrder, $subscription);
        $this->syncMiloSubscription($subscription, $parentOrder);
    }

    public function onMiloSubscriptionStatusUpdated($subscription, string $newStatus, string $oldStatus = ''): void
    {
        if (!is_object($subscription) || !method_exists($subscription, 'get_id')) return;
        $status = strtolower(trim($newStatus));
        if (in_array($status, ['active', 'pending-cancel'], true)) {
            $this->syncMiloSubscription($subscription);
            return;
        }
        if (in_array($status, ['cancelled', 'expired'], true)) $this->deactivateForSubscription($subscription);
    }

    public function onMiloRenewalOrderCreated($renewalOrder, $subscription): void
    {
        if (!is_object($renewalOrder) || !is_object($subscription)) return;
        $parentOrder = $this->subscriptionParentOrder($subscription);
        if ($parentOrder) $this->copyWooGitMeta($parentOrder, $renewalOrder);
    }

    public function onMiloRenewalPaymentComplete($subscription, $order = null): void
    {
        if (!is_object($subscription) || !method_exists($subscription, 'get_id')) return;
        $parentOrder = $this->subscriptionParentOrder($subscription);
        $this->syncMiloSubscription($subscription, $parentOrder ?: $order);
    }

    public function onSubscriptionActive($subscriptionId): void { $this->syncSubscription((int)$subscriptionId); }

    public function onSubscriptionPaymentComplete($subscription): void
    {
        $id = is_object($subscription) && method_exists($subscription, 'get_id') ? (int)$subscription->get_id() : (int)$subscription;
        if ($id > 0) $this->syncSubscription($id);
    }

    private function syncMiloSubscription($subscription, $parentOrder = null): void
    {
        if (!is_object($subscription) || !method_exists($subscription, 'get_id')) return;
        $parentOrder = $parentOrder ?: $this->subscriptionParentOrder($subscription);
        $accountId = $parentOrder ? (int)$parentOrder->get_meta(self::ACCOUNT_META) : (int)$this->objectMeta($subscription, self::ACCOUNT_META);
        $siteId = $parentOrder ? (int)$parentOrder->get_meta(self::SITE_META) : (int)$this->objectMeta($subscription, self::SITE_META);
        if ($accountId <= 0 || $siteId <= 0) return;
        $status = method_exists($subscription, 'get_status') ? strtolower((string)$subscription->get_status()) : '';
        if ($status === '' && method_exists($subscription, 'has_status')) $status = $subscription->has_status(['active', 'pending-cancel']) ? 'active' : 'inactive';
        if (!in_array($status, ['active', 'pending-cancel'], true)) return;
        $nextPayment = method_exists($subscription, 'get_time') ? (int)$subscription->get_time('next_payment') : 0;
        $end = method_exists($subscription, 'get_time') ? (int)$subscription->get_time('end') : 0;
        $expires = $nextPayment > 0 ? $nextPayment : ($end > 0 ? $end : null);
        $this->activate($accountId, $siteId, (int)$subscription->get_id(), $expires);
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
        $status = method_exists($subscription, 'has_status') && $subscription->has_status(['active', 'pending-cancel']) ? 'active' : 'inactive';
        if ($status !== 'active') return;
        $this->activate($accountId, $siteId, $subscriptionId, $nextPayment ?: null);
    }

    private function subscriptionParentOrder($subscription, $fallbackOrder = null)
    {
        if ($fallbackOrder && is_object($fallbackOrder) && method_exists($fallbackOrder, 'get_id')) return $fallbackOrder;
        if (!is_object($subscription)) return null;
        $parentId = method_exists($subscription, 'get_parent_id') ? (int)$subscription->get_parent_id() : 0;
        if ($parentId <= 0 && method_exists($subscription, 'get_parent_order_id')) $parentId = (int)$subscription->get_parent_order_id();
        if ($parentId > 0 && function_exists('wc_get_order')) return wc_get_order($parentId) ?: null;
        return null;
    }

    private function copyWooGitMeta($source, $target): void
    {
        if (!is_object($source) || !is_object($target) || !method_exists($source, 'get_meta') || !method_exists($target, 'update_meta_data')) return;
        foreach ([self::ACCOUNT_META, self::SITE_META, self::PRODUCT_META, self::VARIATION_META, self::PLAN_KEY_META] as $key) {
            $value = $source->get_meta($key);
            if ($value !== '' && $value !== null) $target->update_meta_data($key, $value);
        }
        if (method_exists($target, 'save')) $target->save();
    }

    private function objectMeta($object, string $key): string { return method_exists($object, 'get_meta') ? (string)$object->get_meta($key) : ''; }

    private function isMiloOrder($order): bool
    {
        if (!is_object($order)) return false;
        if ($this->isMiloAvailable() && method_exists($order, 'get_items')) {
            foreach ((array)$order->get_items('line_item') as $item) {
                $product = method_exists($item, 'get_product') ? $item->get_product() : null;
                if ($product && $this->isSubscriptionProduct($product)) return true;
            }
        }
        return false;
    }

    private function isMiloAvailable(): bool
    {
        return has_action('milo_subscriptions_subscription_created') !== false || has_action('milo_subscriptions_renewal_payment_complete') !== false;
    }

    private function deactivateForSubscription($subscription): void
    {
        $parentOrder = $this->subscriptionParentOrder($subscription);
        $accountId = $parentOrder ? (int)$parentOrder->get_meta(self::ACCOUNT_META) : (int)$this->objectMeta($subscription, self::ACCOUNT_META);
        $siteId = $parentOrder ? (int)$parentOrder->get_meta(self::SITE_META) : (int)$this->objectMeta($subscription, self::SITE_META);
        if ($accountId <= 0 || $siteId <= 0) return;
        global $wpdb;
        $table = $wpdb->prefix . 'woogit_entitlements';
        $wpdb->update($table, ['status' => 'inactive', 'updated_at' => gmdate('Y-m-d H:i:s')], ['account_id' => $accountId, 'site_id' => $siteId], ['%s', '%s'], ['%d', '%d']);
    }

    private function activate(int $accountId, int $siteId, ?int $subscriptionId, ?int $expiresTimestamp): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'woogit_entitlements';
        $sql = 'SELECT starts_at,expires_at FROM ' . $table . ' WHERE account_id=%d AND site_id=%d LIMIT 1';
        $existing = $wpdb->get_row($wpdb->prepare($sql, $accountId, $siteId), ARRAY_A);
        $now = time();
        $starts = $now;
        if ($existing && !empty($existing['expires_at'])) {
            $old = strtotime((string)$existing['expires_at']);
            if ($old > $now) $starts = $old;
        }
        $expires = $expiresTimestamp && $expiresTimestamp > $starts ? gmdate('Y-m-d H:i:s', $expiresTimestamp) : null;
        if ($subscriptionId && !$expires) return;
        if ($expires === null && !$subscriptionId) return;
        $data = ['status' => 'active', 'starts_at' => gmdate('Y-m-d H:i:s', $starts), 'expires_at' => $expires, 'capabilities' => wp_json_encode(['commerce']), 'updated_at' => gmdate('Y-m-d H:i:s')];
        if ($existing) $wpdb->update($table, $data, ['account_id' => $accountId, 'site_id' => $siteId], ['%s', '%s', '%s', '%s', '%s'], ['%d', '%d']);
        else $wpdb->insert($table, array_merge($data, ['account_id' => $accountId, 'site_id' => $siteId, 'created_at' => gmdate('Y-m-d H:i:s')]), ['%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s']);
    }

    private function isPlanEnabled($product): bool
    {
        $value = get_post_meta((int)$product->get_id(), self::PLAN_ENABLED_META, true);
        return $value === '' || $value === 'yes' || $value === '1';
    }

    private function planKey($product): string
    {
        $key = sanitize_title((string)get_post_meta((int)$product->get_id(), self::PLAN_KEY_META, true));
        return $key !== '' ? $key : sanitize_title((string)$product->get_name());
    }

    private function isSubscriptionProduct($product): bool
    {
        if (!is_object($product) || !method_exists($product, 'get_type')) return false;
        $type = strtolower((string)$product->get_type());
        if (in_array($type, ['subscription', 'variable-subscription', 'subscription_variation'], true)) return true;
        return str_contains($type, 'subscription');
    }

    private function isVariableSubscriptionProduct($product): bool
    {
        if (!$this->isSubscriptionProduct($product)) return false;
        $type = strtolower((string)$product->get_type());
        return str_contains($type, 'variable') || method_exists($product, 'get_children') && !empty((array)$product->get_children());
    }

    private function getVariations($product): array
    {
        $items = [];
        foreach ((array)$product->get_children() as $variationId) {
            $variation = function_exists('wc_get_product') ? wc_get_product((int)$variationId) : null;
            if (!$variation || !$this->isSubscriptionProduct($variation) || $variation->get_status() !== 'publish' || !$variation->is_purchasable()) continue;
            $items[] = ['id' => (int)$variation->get_id(), 'attributes' => array_map('strval', (array)$variation->get_attributes()), 'price' => (string)$variation->get_price(), 'regular_price' => (string)$variation->get_regular_price()];
        }
        return $items;
    }

    private function durationDays($product): int
    {
        if (!$product || !method_exists($product, 'get_meta')) return 0;
        $period = strtolower((string)$product->get_meta('_subscription_period'));
        $interval = max(1, (int)($product->get_meta('_subscription_period_interval') ?: 1));
        if ($period === 'day') return $interval;
        if ($period === 'week') return $interval * 7;
        if ($period === 'month') return $interval * 30;
        if ($period === 'year') return $interval * 365;
        return 0;
    }
}
