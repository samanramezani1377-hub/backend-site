<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class LifecycleAdmin
{
    private const ACTION = 'woogit_lifecycle_admin_action';

    public function register(): void
    {
        add_submenu_page('woogit-accounts', 'WooGit Sessions', 'Sessionها', 'manage_options', 'woogit-sessions', [$this, 'renderSessions']);
        add_submenu_page('woogit-accounts', 'WooGit Trials', 'Trialها', 'manage_options', 'woogit-trials', 'renderTrials');
    }

    public function renderSessions(): void
    {
        if (!current_user_can('manage_options')) wp_die('دسترسی غیرمجاز.');
        global $wpdb;
        $accounts = $wpdb->prefix . 'woogit_accounts';
        $sites = $wpdb->prefix . 'woogit_sites';
        $web = $wpdb->prefix . 'woogit_web_sessions';
        $app = $wpdb->prefix . 'woogit_sessions';
        $notice = $this->handleSessionAction();
        $rows = $wpdb->get_results("SELECT 'web' AS type,w.id,w.account_id,w.site_id,w.expires_at,w.revoked_at,w.created_at,a.email,s.host FROM {$web} w LEFT JOIN {$accounts} a ON a.id=w.account_id LEFT JOIN {$sites} s ON s.id=w.site_id UNION ALL SELECT 'app' AS type,x.id,x.account_id,x.site_id,x.expires_at,x.revoked_at,x.created_at,a.email,s.host FROM {$app} x LEFT JOIN {$accounts} a ON a.id=x.account_id LEFT JOIN {$sites} s ON s.id=x.site_id ORDER BY created_at DESC LIMIT 500", ARRAY_A);
        $active = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$web} WHERE revoked_at IS NULL AND expires_at>UTC_TIMESTAMP()") + (int)$wpdb->get_var("SELECT COUNT(*) FROM {$app} WHERE revoked_at IS NULL AND expires_at>UTC_TIMESTAMP()");
        $expired = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$web} WHERE expires_at<=UTC_TIMESTAMP()") + (int)$wpdb->get_var("SELECT COUNT(*) FROM {$app} WHERE expires_at<=UTC_TIMESTAMP()");
        ?>
        <div class="wrap woogit-admin">
            <h1>مدیریت Sessionهای WooGit</h1>
            <p>تمام Web Session و App/Operational Sessionها از اینجا قابل مشاهده و لغو هستند.</p>
            <?php $this->notice($notice); ?>
            <div class="woogit-admin-cards"><div><strong><?php echo esc_html($active); ?></strong><span>Session فعال</span></div><div><strong><?php echo esc_html($expired); ?></strong><span>Session منقضی</span></div><div><strong><?php echo esc_html(count($rows)); ?></strong><span>رکورد قابل نمایش</span></div></div>
            <form method="post" class="woogit-admin-toolbar"><?php wp_nonce_field(self::ACTION); ?><input type="hidden" name="woogit_lifecycle_action" value="purge_expired_sessions"><button class="button">پاک‌سازی Sessionهای منقضی</button></form>
            <div class="woogit-admin-table-wrap"><table class="widefat striped"><thead><tr><th>نوع</th><th>Account</th><th>Site</th><th>ایمیل</th><th>ایجاد</th><th>انقضا</th><th>وضعیت</th><th>عملیات</th></tr></thead><tbody>
            <?php if (!$rows): ?><tr><td colspan="8">Sessionی وجود ندارد.</td></tr><?php else: foreach ($rows as $r): $isActive=!$r['revoked_at'] && strtotime($r['expires_at'])>time(); ?>
                <tr><td><strong><?php echo esc_html(strtoupper($r['type'])); ?></strong></td><td>#<?php echo esc_html($r['account_id']); ?></td><td><?php echo esc_html($r['host'] ?: ('Site #'.$r['site_id'])); ?></td><td><?php echo esc_html($r['email'] ?: '—'); ?></td><td><?php echo esc_html($r['created_at']); ?></td><td><?php echo esc_html($r['expires_at']); ?></td><td><?php echo $isActive ? '<span class="woogit-status-active">فعال</span>' : ($r['revoked_at'] ? 'لغوشده' : 'منقضی'); ?></td><td><?php if ($isActive): ?><form method="post"><?php wp_nonce_field(self::ACTION); ?><input type="hidden" name="woogit_lifecycle_action" value="revoke_session"><input type="hidden" name="session_type" value="<?php echo esc_attr($r['type']); ?>"><input type="hidden" name="session_id" value="<?php echo esc_attr($r['id']); ?>"><button class="button button-link-delete">لغو</button></form><?php else: ?>—<?php endif; ?></td></tr>
            <?php endforeach; endif; ?></tbody></table></div>
        </div>
        <?php
    }

    public function renderTrials(): void
    {
        if (!current_user_can('manage_options')) wp_die('دسترسی غیرمجاز.');
        global $wpdb;
        $accounts = $wpdb->prefix . 'woogit_accounts';
        $sites = $wpdb->prefix . 'woogit_sites';
        $entitlements = $wpdb->prefix . 'woogit_entitlements';
        $notice = $this->handleTrialAction();
        $rows = $wpdb->get_results("SELECT a.id AS account_id,a.email,a.trial_used_at,s.id AS site_id,s.host,e.id AS entitlement_id,e.status,e.starts_at,e.expires_at,e.capabilities FROM {$accounts} a LEFT JOIN {$sites} s ON s.account_id=a.id LEFT JOIN {$entitlements} e ON e.account_id=a.id AND e.site_id=s.id AND e.status='trial' ORDER BY a.id DESC", ARRAY_A);
        $used = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$accounts} WHERE trial_used_at IS NOT NULL");
        $active = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$entitlements} WHERE status='trial' AND (expires_at IS NULL OR expires_at>UTC_TIMESTAMP())");
        ?>
        <div class="wrap woogit-admin">
            <h1>مدیریت Trialهای WooGit</h1>
            <p>وضعیت مصرف Trial، Trial فعال و Entitlement مربوط به Trial را مدیریت کنید.</p>
            <?php $this->notice($notice); ?>
            <div class="woogit-admin-cards"><div><strong><?php echo esc_html($used); ?></strong><span>Trial مصرف‌شده</span></div><div><strong><?php echo esc_html($active); ?></strong><span>Trial فعال</span></div><div><strong>۱۵ روز</strong><span>مدت Trial V1</span></div></div>
            <div class="woogit-admin-table-wrap"><table class="widefat striped"><thead><tr><th>Account</th><th>Site</th><th>وضعیت مصرف</th><th>Trial Entitlement</th><th>شروع</th><th>انقضا</th><th>عملیات</th></tr></thead><tbody>
            <?php if (!$rows): ?><tr><td colspan="7">Trialی ثبت نشده است.</td></tr><?php else: foreach ($rows as $r): ?>
                <tr><td><strong>#<?php echo esc_html($r['account_id']); ?></strong><br><?php echo esc_html($r['email'] ?: '—'); ?></td><td><?php echo esc_html($r['host'] ?: '—'); ?></td><td><?php echo $r['trial_used_at'] ? 'مصرف‌شده<br><small>'.esc_html($r['trial_used_at']).'</small>' : 'آزاد'; ?></td><td><?php echo $r['entitlement_id'] ? esc_html($r['status']) : 'ندارد'; ?></td><td><?php echo esc_html($r['starts_at'] ?: '—'); ?></td><td><?php echo esc_html($r['expires_at'] ?: '—'); ?></td><td><div class="woogit-action-stack"><form method="post"><?php wp_nonce_field(self::ACTION); ?><input type="hidden" name="woogit_lifecycle_action" value="reset_trial"><input type="hidden" name="account_id" value="<?php echo esc_attr($r['account_id']); ?>"><button class="button">بازنشانی Trial</button></form><?php if ($r['entitlement_id']): ?><form method="post" onsubmit="return confirm('Trial این Account غیرفعال شود؟');"><?php wp_nonce_field(self::ACTION); ?><input type="hidden" name="woogit_lifecycle_action" value="revoke_trial"><input type="hidden" name="account_id" value="<?php echo esc_attr($r['account_id']); ?>"><input type="hidden" name="site_id" value="<?php echo esc_attr($r['site_id']); ?>"><button class="button button-link-delete">لغو Trial</button></form><?php endif; ?></div></td></tr>
            <?php endforeach; endif; ?></tbody></table></div>
        </div>
        <?php
    }

    private function handleSessionAction(): ?array
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') return null;
        if (!current_user_can('manage_options')) return ['error', 'دسترسی غیرمجاز.'];
        if (empty($_POST['woogit_lifecycle_action'])) return null;
        check_admin_referer(self::ACTION);
        global $wpdb;
        $action = sanitize_key((string)$_POST['woogit_lifecycle_action']);
        if ($action === 'purge_expired_sessions') {
            $n = 0;
            foreach ([$wpdb->prefix.'woogit_web_sessions', $wpdb->prefix.'woogit_sessions'] as $table) {
                $result = $wpdb->query("DELETE FROM {$table} WHERE expires_at<=UTC_TIMESTAMP()");
                if ($result !== false) $n += (int)$result;
            }
            return ['success', $n.' Session منقضی پاک‌سازی شد.'];
        }
        if ($action === 'revoke_session') {
            $type = sanitize_key((string)($_POST['session_type'] ?? '')); $id = absint($_POST['session_id'] ?? 0);
            $table = $type === 'web' ? $wpdb->prefix.'woogit_web_sessions' : ($type === 'app' ? $wpdb->prefix.'woogit_sessions' : '');
            if (!$table || !$id) return ['error', 'Session نامعتبر است.'];
            $ok = false !== $wpdb->update($table, ['revoked_at'=>gmdate('Y-m-d H:i:s')], ['id'=>$id], ['%s'], ['%d']);
            return [$ok ? 'success' : 'error', $ok ? 'Session لغو شد.' : 'لغو Session انجام نشد.'];
        }
        return null;
    }

    private function handleTrialAction(): ?array
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') return null;
        if (!current_user_can('manage_options')) return ['error', 'دسترسی غیرمجاز.'];
        if (empty($_POST['woogit_lifecycle_action'])) return null;
        check_admin_referer(self::ACTION);
        global $wpdb;
        $accounts = $wpdb->prefix.'woogit_accounts'; $entitlements=$wpdb->prefix.'woogit_entitlements'; $accountId=absint($_POST['account_id']??0); $siteId=absint($_POST['site_id']??0);
        if (!$accountId) return ['error','Account نامعتبر است.'];
        $action=sanitize_key((string)$_POST['woogit_lifecycle_action']);
        if ($action==='reset_trial') {
            $ok=false!==$wpdb->update($accounts,['trial_used_at'=>null,'updated_at'=>gmdate('Y-m-d H:i:s')],['id'=>$accountId],['%s','%s'],['%d']);
            return [$ok?'success':'error',$ok?'Trial برای این Account دوباره آزاد شد.':'بازنشانی Trial انجام نشد.'];
        }
        if ($action==='revoke_trial') {
            if (!$siteId) return ['error','Site نامعتبر است.'];
            $ok=false!==$wpdb->query($wpdb->prepare("UPDATE {$entitlements} SET status='revoked',updated_at=%s WHERE account_id=%d AND site_id=%d AND status='trial'",gmdate('Y-m-d H:i:s'),$accountId,$siteId));
            return [$ok?'success':'error',$ok?'Trial غیرفعال شد.':'لغو Trial انجام نشد.'];
        }
        return null;
    }

    private function notice(?array $notice): void
    {
        if ($notice) echo '<div class="notice notice-'.esc_attr($notice[0]).' is-dismissible"><p>'.esc_html($notice[1]).'</p></div>';
    }
}
