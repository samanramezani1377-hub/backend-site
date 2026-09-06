<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class AccountService
{
    public function get(int $accountId): ?array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'woogit_accounts';
        $row = $wpdb->get_row($wpdb->prepare("SELECT id,email,status FROM {$table} WHERE id = %d LIMIT 1", $accountId), ARRAY_A);
        return ($row && $row['status'] === 'active') ? $row : null;
    }

    public function findOrCreate(string $email): ?array
    {
        $email = sanitize_email($email);
        if (!is_email($email)) return null;
        global $wpdb;
        $table = $wpdb->prefix . 'woogit_accounts';
        $existing = $wpdb->get_row($wpdb->prepare("SELECT id,email,status FROM {$table} WHERE email = %s LIMIT 1", $email), ARRAY_A);
        if ($existing) return $existing['status'] === 'active' ? $existing : null;
        $now = current_time('mysql', true);
        $ok = $wpdb->insert($table, ['email'=>$email,'status'=>'active','created_at'=>$now,'updated_at'=>$now], ['%s','%s','%s','%s']);
        if (!$ok) return null;
        return ['id'=>(int)$wpdb->insert_id,'email'=>$email,'status'=>'active'];
    }
}
