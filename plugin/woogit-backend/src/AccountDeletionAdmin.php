<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class AccountDeletionAdmin
{
    public function register(): void
    {
        add_action('admin_init', [$this, 'handle']);
    }

    public function handle(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') return;
        if (sanitize_key((string)($_POST['woogit_action'] ?? '')) !== 'delete') return;
        if (!current_user_can('manage_options')) wp_die('دسترسی غیرمجاز.');
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash((string)$_POST['_wpnonce'])), 'woogit_account_admin_action')) {
            wp_die('درخواست نامعتبر است.');
        }

        $accountId = absint($_POST['account_id'] ?? 0);
        if (!$accountId) $this->finish('error', 'Account نامعتبر است.');

        global $wpdb;
        $accounts = $wpdb->prefix . 'woogit_accounts';
        $account = $wpdb->get_row($wpdb->prepare("SELECT id, wp_user_id FROM {$accounts} WHERE id=%d LIMIT 1", $accountId), ARRAY_A);
        if (!$account) $this->finish('error', 'Account پیدا نشد.');

        $tables = [
            'idempotency' => $wpdb->prefix . 'woogit_idempotency',
            'operations'  => $wpdb->prefix . 'woogit_operations',
            'web_sessions'=> $wpdb->prefix . 'woogit_web_sessions',
            'sessions'    => $wpdb->prefix . 'woogit_sessions',
            'entitlements'=> $wpdb->prefix . 'woogit_entitlements',
            'sites'       => $wpdb->prefix . 'woogit_sites',
        ];

        // Delete every WooGit row owned by this Account before deleting the Account itself.
        foreach ($tables as $table) {
            $wpdb->query($wpdb->prepare("DELETE FROM {$table} WHERE account_id=%d", $accountId));
        }

        $deleted = $wpdb->delete($accounts, ['id' => $accountId], ['%d']);
        if ($deleted !== 1) $this->finish('error', 'حذف Account کامل نشد.');

        // wp_delete_user also removes the WordPress user and all of its usermeta.
        $wpUserId = (int)$account['wp_user_id'];
        if ($wpUserId > 0 && get_user_by('id', $wpUserId)) {
            if (!wp_delete_user($wpUserId)) {
                error_log('[WooGit Backend] Account deleted but linked WordPress identity could not be deleted: ' . $wpUserId);
                $this->finish('error', 'Account حذف شد اما Identity وردپرس حذف نشد؛ حذف Identity باید بررسی شود.');
            }
        }

        $this->finish('success', 'Account و تمام داده‌های WooGit مرتبط با آن حذف شد. سفارش‌های WooCommerce حفظ شدند.');
    }

    private function finish(string $type, string $message): void
    {
        $url = add_query_arg([
            'page' => 'woogit-accounts',
            'woogit_notice' => $type,
            'woogit_notice_message' => $message,
        ], admin_url('admin.php'));
        wp_safe_redirect($url);
        exit;
    }
}
