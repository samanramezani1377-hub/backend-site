<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class GlobalDataCleanupAdmin
{
    private const ACTION='woogit_global_data_cleanup';
    private const PAGE='woogit-accounts';

    public function register(): void
    {
        add_action('admin_init', [$this, 'handle']);
        add_action('admin_footer', [$this, 'renderButton']);
    }

    public function handle(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') return;
        if (($_POST['woogit_global_cleanup'] ?? '') !== '1') return;
        if (($_GET['page'] ?? '') !== self::PAGE) return;
        if (!current_user_can('manage_options')) wp_die('دسترسی غیرمجاز.');
        check_admin_referer(self::ACTION);

        $confirmation = sanitize_text_field(wp_unslash((string) ($_POST['woogit_cleanup_confirmation'] ?? '')));
        if ($confirmation !== 'DELETE WOOGIT') {
            wp_safe_redirect(add_query_arg(['page' => self::PAGE, 'woogit_cleanup' => 'failed'], admin_url('admin.php')));
            exit;
        }

        global $wpdb;
        $prefix = $wpdb->prefix . 'woogit_';
        $tables = [
            'idempotency',
            'operations',
            'web_sessions',
            'sessions',
            'entitlements',
            'sites',
            'accounts',
            'rate_limits',
        ];

        $wpUserIds = $wpdb->get_col("SELECT wp_user_id FROM {$prefix}accounts WHERE wp_user_id IS NOT NULL AND wp_user_id > 0");
        $deleted = 0;

        foreach ($tables as $table) {
            $name = $prefix . $table;
            $result = $wpdb->query("DELETE FROM {$name}");
            if ($result === false) {
                wp_die('پاک‌سازی WooGit کامل نشد. عملیات متوقف شد و خطای دیتابیس رخ داد.');
            }
            $deleted += (int) $result;
        }

        delete_option('woogit_backend_db_version');
        delete_option('woogit_backend_version_policy');

        foreach (array_unique(array_map('absint', $wpUserIds)) as $userId) {
            if ($userId > 0 && $userId !== get_current_user_id()) {
                require_once ABSPATH . 'wp-admin/includes/user.php';
                wp_delete_user($userId);
            }
        }

        wp_safe_redirect(add_query_arg([
            'page' => self::PAGE,
            'woogit_cleanup' => 'done',
            'woogit_cleanup_rows' => $deleted,
        ], admin_url('admin.php')));
        exit;
    }

    public function renderButton(): void
    {
        if (($_GET['page'] ?? '') !== self::PAGE || !current_user_can('manage_options')) return;
        $state = sanitize_key((string) ($_GET['woogit_cleanup'] ?? ''));
        ?>
        <div class="woogit-global-cleanup" style="max-width:1500px;margin:28px 20px 40px;padding:20px;border:1px solid #dcdcde;border-radius:12px;background:#fff;box-sizing:border-box;">
            <h2 style="margin:0 0 8px;">منطقه خطر</h2>
            <p style="margin:0 0 14px;color:#646970;">پاک‌سازی کل داده‌های WooGit را انجام می‌دهد: Accounts، Site، Entitlement، Session، عملیات، Idempotency و Rate Limit. هویت‌های WordPress متصل به Accounts نیز حذف می‌شوند. سفارش‌های WooCommerce دست‌نخورده باقی می‌مانند.</p>
            <?php if ($state === 'done'): ?>
                <div class="notice notice-success inline"><p>تمام داده‌های WooGit پاک شد. تعداد رکوردهای جدول‌های WooGit: <?php echo esc_html(absint($_GET['woogit_cleanup_rows'] ?? 0)); ?>.</p></div>
            <?php elseif ($state === 'failed'): ?>
                <div class="notice notice-error inline"><p>تأیید پاک‌سازی صحیح نبود. هیچ داده‌ای پاک نشد.</p></div>
            <?php endif; ?>
            <form method="post" onsubmit="return window.confirm('هشدار نهایی: تمام داده‌های WooGit و WordPress Identityهای متصل حذف می‌شوند. سفارش‌های WooCommerce حفظ می‌شوند. برای ادامه باید عبارت DELETE WOOGIT را وارد کنید.');" style="margin-top:14px;">
                <?php wp_nonce_field(self::ACTION); ?>
                <input type="hidden" name="woogit_global_cleanup" value="1">
                <label for="woogit-cleanup-confirm"><strong>برای فعال شدن دکمه، DELETE WOOGIT را وارد کنید:</strong></label>
                <div style="display:flex;gap:8px;align-items:center;margin-top:8px;max-width:620px;">
                    <input id="woogit-cleanup-confirm" type="text" name="woogit_cleanup_confirmation" autocomplete="off" required placeholder="DELETE WOOGIT" style="flex:1;min-height:38px;">
                    <button type="submit" class="button button-link-delete" style="min-height:38px;">پاک‌سازی کل داده‌های WooGit</button>
                </div>
            </form>
        </div>
        <?php
    }
}
