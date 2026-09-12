<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class PaymentReturnRedirect
{
    private const ACCOUNT_META = '_woogit_account_id';
    private const SITE_META = '_woogit_site_id';
    private const CONTEXT_META = '_woogit_payment_context';
    private const APP_CONTEXT = 'app';

    public function register(): void
    {
        add_action('woocommerce_new_order', [$this, 'markAppOrder'], 20, 2);
        add_filter('woocommerce_get_return_url', [$this, 'filterReturnUrl'], 20, 2);
    }

    public function markAppOrder($orderId, $order = null): void
    {
        if (!isset($_SERVER['HTTP_X_WOOGIT_APP_VERSION']) || trim((string)$_SERVER['HTTP_X_WOOGIT_APP_VERSION']) === '') return;
        if (!is_object($order) && function_exists('wc_get_order')) $order = wc_get_order((int)$orderId);
        if (!is_object($order) || !method_exists($order, 'update_meta_data')) return;
        $order->update_meta_data(self::CONTEXT_META, self::APP_CONTEXT);
        $order->save();
    }

    public function filterReturnUrl(string $returnUrl, $order): string
    {
        if (!is_object($order) || !method_exists($order, 'get_created_via')) return $returnUrl;
        if ((string)$order->get_created_via() !== 'woogit') return $returnUrl;
        if (!method_exists($order, 'get_meta')) return $returnUrl;

        $accountId = (int)$order->get_meta(self::ACCOUNT_META);
        $siteId = (int)$order->get_meta(self::SITE_META);
        if ($accountId <= 0 || $siteId <= 0) return $returnUrl;

        $page = get_page_by_path('payment-result', OBJECT, 'page');
        if (!$page instanceof \WP_Post || $page->post_status !== 'publish') return $returnUrl;

        $pageUrl = get_permalink($page);
        if (!$pageUrl) return $returnUrl;

        $args = ['order_id' => (int)$order->get_id()];
        if ((string)$order->get_meta(self::CONTEXT_META) === self::APP_CONTEXT) {
            $args['app'] = '1';
            $args['return_to'] = 'woogit';
        }
        return add_query_arg($args, $pageUrl);
    }
}
