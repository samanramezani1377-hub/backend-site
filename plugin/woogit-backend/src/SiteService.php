<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class SiteService
{
    public function findByHost(string $host): ?array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'woogit_sites';
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
        $host = strtolower($parts['host']);
        global $wpdb;
        $table = $wpdb->prefix . 'woogit_sites';
        $existing = $this->findByHost($host);
        if ($existing) return ((int)$existing['account_id'] === $accountId && $existing['status'] === 'active') ? $existing : null;
        $now = current_time('mysql', true);
        $ok = $wpdb->insert($table, ['account_id'=>$accountId,'canonical_url'=>rtrim($canonicalUrl,'/'),'host'=>$host,'status'=>'active','created_at'=>$now,'updated_at'=>$now], ['%d','%s','%s','%s','%s','%s']);
        if (!$ok) return null;
        return ['id'=>(int)$wpdb->insert_id,'account_id'=>$accountId,'canonical_url'=>rtrim($canonicalUrl,'/'),'host'=>$host,'status'=>'active'];
    }
}
