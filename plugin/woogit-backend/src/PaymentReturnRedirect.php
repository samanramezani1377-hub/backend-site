<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class PaymentReturnRedirect
{
    private const ACCOUNT_META = '_woogit_account_id';
    private const SITE_META = '_woogit_site_id';

    public function register(): void
    {
        add_filter('woocommerce_get_return_url', [$this, 'filterReturnUrl'], 20, 2);
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

        return add_query_arg(['order_id' => (int)$order->get_id()], $pageUrl);
    }
}
