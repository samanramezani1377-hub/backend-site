<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class SiteService
{
    public function findByHost(string $host): ?array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'woogit_sites';
        $host = strtolower(rtrim(trim($host), '.'));
        $row = $wpdb->get_row($wpdb->prepare("SELECT id,account_id,canonical_url,host,status FROM {$table} WHERE host = %s LIMIT 1", $host), ARRAY_A);
        return $row ?: null;
    }

    public function getOwned(int $accountId, int $siteId): ?array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'woogit_sites';
        $row = $wpdb->get_row($wpdb->prepare("SELECT id,account_id,canonical_url,host,status FROM {$table} WHERE id = %d AND account_id = %d LIMIT 1", $siteId, $accountId), ARRAY_A);
        return ($row && $row['status'] === 'active') ? $row : null;
    }

    public function findOrCreate(int $accountId, string $canonicalUrl): ?array
    {
        $parts = wp_parse_url($canonicalUrl);
        if (!$parts || empty($parts['host'])) return null;
        $host = strtolower(rtrim((string)$parts['host'], '.'));
        global $wpdb;
        $table = $wpdb->prefix . 'woogit_sites';
        $existing = $this->findByHost($host);
        if ($existing) return ((int)$existing['account_id'] === $accountId && $existing['status'] === 'active') ? $existing : null;

        // WooGit Account identity is one-to-one with the connected site.
        // Never attach a second host to an existing Account.
        $owned = $wpdb->get_row($wpdb->prepare("SELECT id,account_id,canonical_url,host,status FROM {$table} WHERE account_id = %d LIMIT 1", $accountId), ARRAY_A);
        if ($owned) return ((int)$owned['account_id'] === $accountId && $owned['status'] === 'active' && $owned['host'] === $host) ? $owned : null;

        $now = current_time('mysql', true);
        $canonical = rtrim($canonicalUrl,'/');
        $ok = $wpdb->insert($table, ['account_id'=>$accountId,'canonical_url'=>$canonical,'host'=>$host,'status'=>'active','created_at'=>$now,'updated_at'=>$now], ['%d','%s','%s','%s','%s','%s']);
        if ($ok) return ['id'=>(int)$wpdb->insert_id,'account_id'=>$accountId,'canonical_url'=>$canonical,'host'=>$host,'status'=>'active'];
        // A concurrent verifier may have won the unique host race. Never create a second owner.
        $existing = $this->findByHost($host);
        return ($existing && (int)$existing['account_id'] === $accountId && $existing['status'] === 'active') ? $existing : null;
    }
}
