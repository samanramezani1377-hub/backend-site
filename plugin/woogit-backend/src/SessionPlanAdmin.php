<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

/**
 * Stores the per-plan concurrent operational-session limit on the plan product.
 * The value is read by EntitlementService; it is never supplied by the app.
 */
final class SessionPlanAdmin
{
    private const META = '_woogit_max_sessions';

    public function registerHooks(): void
    {
        add_action('woocommerce_product_options_general_product_data', [$this, 'render']);
        add_action('woocommerce_process_product_meta', [$this, 'save'], 30, 1);
    }

    public function render(): void
    {
        global $product_object;
        if (!is_object($product_object) || !method_exists($product_object, 'get_id') || !$this->isSubscriptionProduct($product_object)) return;
        $value = get_post_meta((int)$product_object->get_id(), self::META, true);
        echo '<div class="options_group show_if_subscription show_if_variable-subscription">';
        woocommerce_wp_text_input([
            'id' => self::META,
            'value' => $value,
            'label' => 'WooGit Max Sessions',
            'description' => 'حداکثر Session عملیاتی همزمان این پلن. عدد مثبت؛ خالی یعنی بدون سقف Session.',
            'desc_tip' => true,
            'type' => 'number',
            'custom_attributes' => ['min' => '1', 'step' => '1'],
        ]);
        echo '</div>';
    }

    public function save(int $productId): void
    {
        if (!current_user_can('edit_post', $productId)) return;
        $product = function_exists('wc_get_product') ? wc_get_product($productId) : null;
        if (!$product || !$this->isSubscriptionProduct($product)) return;
        $raw = isset($_POST[self::META]) ? trim((string)wp_unslash($_POST[self::META])) : '';
        if ($raw === '') {
            delete_post_meta($productId, self::META);
            return;
        }
        $value = max(1, min(1000, absint($raw)));
        update_post_meta($productId, self::META, (string)$value);
    }

    private function isSubscriptionProduct($product): bool
    {
        if (!is_object($product) || !method_exists($product, 'get_type')) return false;
        return str_contains(strtolower((string)$product->get_type()), 'subscription');
    }
}
