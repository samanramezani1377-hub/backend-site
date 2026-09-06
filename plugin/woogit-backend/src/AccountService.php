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

    /**
     * Create an Account for an already verified Site.
     * Email is contact metadata only and is never used to resolve Account identity.
     */
    public function create(string $email = ''): ?array
    {
        $email = sanitize_email($email);
        if ($email !== '' && !is_email($email)) return null;
        global $wpdb;
        $table = $wpdb->prefix . 'woogit_accounts';
        $now = current_time('mysql', true);
        $ok = $wpdb->insert($table, ['email'=>$email !== '' ? $email : null,'status'=>'active','created_at'=>$now,'updated_at'=>$now], ['%s','%s','%s','%s']);
        if (!$ok) return null;
        return ['id'=>(int)$wpdb->insert_id,'email'=>$email,'status'=>'active'];
    }

    public function updateContactEmail(int $accountId, string $email): bool
    {
        $email = sanitize_email($email);
        if ($email !== '' && !is_email($email)) return false;
        global $wpdb;
        $table = $wpdb->prefix . 'woogit_accounts';
        return false !== $wpdb->update($table, ['email'=>$email !== '' ? $email : null,'updated_at'=>current_time('mysql', true)], ['id'=>$accountId], ['%s','%s'], ['%d']);
    }
}
