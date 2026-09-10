<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class MiloBillingAdminCompatibility
{
    private const PLAN_ENABLED_META = '_woogit_plan_enabled';
    private const PLAN_KEY_META = '_woogit_plan_key';

    public function registerHooks(): void
    {
        // Milo exposes this hook inside the Subscription product-data panel.
        // Render the WooGit fields directly here so they are present even when
        // WooCommerce's legacy subscription visibility classes are not applied.
        add_action('milo_subscriptions_product_data_panel', [$this, 'renderPlanFields'], 30, 1);
        add_action('woocommerce_process_product_meta', [$this, 'savePlanFields'], 30, 1);
        add_action('admin_footer-post.php', [$this, 'unhideWooGitPlanFields']);
        add_action('admin_footer-post-new.php', [$this, 'unhideWooGitPlanFields']);
    }

    public function renderPlanFields($hookProduct = null): void
    {
        global $product_object;
        $product = is_object($hookProduct) ? $hookProduct : $product_object;
        if (!$product || !method_exists($product, 'get_id')) return;

        $productId = (int)$product->get_id();
        $enabled = get_post_meta($productId, self::PLAN_ENABLED_META, true);
        if ($enabled === '') $enabled = 'yes';
        $key = (string)get_post_meta($productId, self::PLAN_KEY_META, true);
        if ($key === '') $key = sanitize_title((string)$product->get_name());

        echo '<div class="options_group woogit-plan-fields">';
        woocommerce_wp_checkbox([
            'id' => self::PLAN_ENABLED_META,
            'value' => $enabled,
            'label' => 'WooGit Plan',
            'description' => 'این محصول به‌عنوان پلن قابل خرید WooGit در API Billing نمایش داده شود.',
            'desc_tip' => true,
        ]);
        woocommerce_wp_text_input([
            'id' => self::PLAN_KEY_META,
            'value' => $key,
            'label' => 'WooGit Plan Key',
            'description' => 'شناسه پایدار پلن که App می‌تواند برای نمایش/ردیابی استفاده کند.',
            'desc_tip' => true,
        ]);
        echo '</div>';
    }

    public function savePlanFields(int $productId): void
    {
        if (!current_user_can('edit_post', $productId)) return;
        if (!isset($_POST[self::PLAN_ENABLED_META]) && !isset($_POST[self::PLAN_KEY_META])) return;

        $product = function_exists('wc_get_product') ? wc_get_product($productId) : null;
        if (!$product) return;

        $enabled = isset($_POST[self::PLAN_ENABLED_META]) ? 'yes' : 'no';
        $key = isset($_POST[self::PLAN_KEY_META])
            ? sanitize_title(wp_unslash((string)$_POST[self::PLAN_KEY_META]))
            : '';
        if ($key === '') $key = sanitize_title((string)$product->get_name());

        update_post_meta($productId, self::PLAN_ENABLED_META, $enabled);
        update_post_meta($productId, self::PLAN_KEY_META, $key);
    }

    public function unhideWooGitPlanFields(): void
    {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || $screen->post_type !== 'product') return;

        ?>
        <script>
        (function () {
            function unhideWooGitFields() {
                var input = document.getElementById('_woogit_plan_enabled');
                if (!input) return;
                var group = input.closest('.options_group');
                if (!group) return;
                group.classList.remove('show_if_subscription', 'show_if_variable-subscription');
                group.style.display = '';
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', unhideWooGitFields);
            } else {
                unhideWooGitFields();
            }

            if (window.jQuery) {
                window.jQuery(document.body).on('woocommerce-product-type-change', unhideWooGitFields);
            }
        }());
        </script>
        <?php
    }
}
