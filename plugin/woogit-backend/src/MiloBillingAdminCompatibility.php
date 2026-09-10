<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class MiloBillingAdminCompatibility
{
    public function registerHooks(): void
    {
        add_action('admin_footer-post.php', [$this, 'unhideWooGitPlanFields']);
        add_action('admin_footer-post-new.php', [$this, 'unhideWooGitPlanFields']);
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
