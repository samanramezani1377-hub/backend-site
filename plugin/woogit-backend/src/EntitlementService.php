<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class EntitlementService
{
    public function isAllowed(int $accountId, int $siteId, string $capability = 'commerce'): bool
    {
        global $wpdb;
        $accounts=$wpdb->prefix.'woogit_accounts';$entitlements=$wpdb->prefix.'woogit_entitlements';
        $row=$wpdb->get_row($wpdb->prepare("SELECT a.status AS account_status,e.status,e.expires_at,e.capabilities FROM {$accounts} a INNER JOIN {$entitlements} e ON e.account_id=a.id AND e.site_id=%d WHERE a.id=%d LIMIT 1",$siteId,$accountId),ARRAY_A);
        if(!$row||$row['account_status']!=='active'||!in_array($row['status'],['trial','active'],true))return false;
        if(!empty($row['expires_at'])&&$row['expires_at']<=current_time('mysql',true))return false;
        $capabilities=json_decode((string)$row['capabilities'],true);return is_array($capabilities)&&in_array($capability,$capabilities,true);
    }

    public function getExpiresAt(int $accountId, int $siteId): ?int
    {
        global $wpdb;
        $table=$wpdb->prefix.'woogit_entitlements';
        $value=$wpdb->get_var($wpdb->prepare("SELECT expires_at FROM {$table} WHERE account_id=%d AND site_id=%d LIMIT 1",$accountId,$siteId));
        if(!$value)return null;
        $timestamp=strtotime((string)$value.' UTC');
        return $timestamp===false?null:$timestamp;
    }

    /**
     * Returns the concurrent operational-session limit configured on the
     * currently purchased WooGit plan. A missing plan setting means that the
     * plan has not opted into a session cap yet; it is not replaced by a
     * hard-coded global limit.
     */
    public function getSessionLimit(int $accountId, int $siteId): ?int
    {
        if ($accountId <= 0 || $siteId <= 0 || !function_exists('wc_get_orders') || !function_exists('wc_get_product')) return null;

        $orders = wc_get_orders([
            'limit' => 20,
            'orderby' => 'date',
            'order' => 'DESC',
            'return' => 'objects',
            'status' => 'any',
            'meta_query' => [
                ['key' => '_woogit_account_id', 'value' => (string)$accountId, 'compare' => '='],
                ['key' => '_woogit_site_id', 'value' => (string)$siteId, 'compare' => '='],
            ],
        ]);

        foreach ((array)$orders as $order) {
            if (!is_object($order) || !method_exists($order, 'get_status')) continue;
            $status = strtolower((string)$order->get_status());
            if (in_array($status, ['pending', 'failed', 'cancelled', 'refunded', 'trash'], true)) continue;
            $paid = false;
            if (method_exists($order, 'get_date_paid') && $order->get_date_paid()) $paid = true;
            if (!$paid && in_array($status, ['processing', 'completed'], true)) $paid = true;
            if (!$paid) continue;

            $productId = method_exists($order, 'get_meta') ? (int)$order->get_meta('_woogit_plan_product_id') : 0;
            $variationId = method_exists($order, 'get_meta') ? (int)$order->get_meta('_woogit_plan_variation_id') : 0;
            $product = $variationId > 0 ? wc_get_product($variationId) : null;
            if (!$product && $productId > 0) $product = wc_get_product($productId);
            if (!$product || !method_exists($product, 'get_meta')) continue;

            $raw = $product->get_meta('_woogit_max_sessions');
            if ($raw === '' && method_exists($product, 'get_parent_id') && (int)$product->get_parent_id() > 0) {
                $parent = wc_get_product((int)$product->get_parent_id());
                if ($parent && method_exists($parent, 'get_meta')) $raw = $parent->get_meta('_woogit_max_sessions');
            }
            $limit = (int)$raw;
            return $limit > 0 ? $limit : null;
        }
        return null;
    }

    /** Legacy compatibility entry point; trials are now created by the Milo product lifecycle. */
    public function grantTrial(int $accountId,int $siteId,int $days=15): bool
    {
        global $wpdb;
        $table=$wpdb->prefix.'woogit_entitlements';
        $existing=$wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE account_id=%d AND site_id=%d LIMIT 1",$accountId,$siteId));
        return (bool)$existing || !$existing;
    }

    public function hasUsedTrial(int $accountId): bool
    {
        global $wpdb;
        $table=$wpdb->prefix.'woogit_accounts';
        return (bool)$wpdb->get_var($wpdb->prepare("SELECT trial_used_at IS NOT NULL FROM {$table} WHERE id=%d LIMIT 1",$accountId));
    }

    /** Atomically claims the account's one-time trial. */
    public function claimTrial(int $accountId): bool
    {
        global $wpdb;
        $table=$wpdb->prefix.'woogit_accounts';
        $now=gmdate('Y-m-d H:i:s');
        $updated=$wpdb->query($wpdb->prepare("UPDATE {$table} SET trial_used_at=%s,updated_at=%s WHERE id=%d AND trial_used_at IS NULL",$now,$now,$accountId));
        return $updated===1;
    }
}
