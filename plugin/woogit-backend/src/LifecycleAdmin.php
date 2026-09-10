<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class LifecycleAdmin
{
    private const ACTION='woogit_lifecycle_admin_action';

    public function register():void{}

    public function renderSessions():void
    {
        if(!current_user_can('manage_options'))wp_die('دسترسی غیرمجاز.');
        global $wpdb;$accounts=$wpdb->prefix.'woogit_accounts';$sites=$wpdb->prefix.'woogit_sites';$web=$wpdb->prefix.'woogit_web_sessions';$app=$wpdb->prefix.'woogit_sessions';$notice=$this->handleSessionAction();
        $rows=$wpdb->get_results("SELECT 'web' AS type,w.id,w.account_id,w.site_id,w.expires_at,w.revoked_at,w.created_at,a.email,s.host FROM {$web} w LEFT JOIN {$accounts} a ON a.id=w.account_id LEFT JOIN {$sites} s ON s.id=w.site_id UNION ALL SELECT 'app' AS type,x.id,x.account_id,x.site_id,x.expires_at,x.revoked_at,x.created_at,a.email,s.host FROM {$app} x LEFT JOIN {$accounts} a ON a.id=x.account_id LEFT JOIN {$sites} s ON s.id=x.site_id ORDER BY created_at DESC LIMIT 500",ARRAY_A);
        $active=(int)$wpdb->get_var("SELECT COUNT(*) FROM {$web} WHERE revoked_at IS NULL AND expires_at>UTC_TIMESTAMP()")+(int)$wpdb->get_var("SELECT COUNT(*) FROM {$app} WHERE revoked_at IS NULL AND expires_at>UTC_TIMESTAMP()");$expired=(int)$wpdb->get_var("SELECT COUNT(*) FROM {$web} WHERE expires_at<=UTC_TIMESTAMP()")+(int)$wpdb->get_var("SELECT COUNT(*) FROM {$app} WHERE expires_at<=UTC_TIMESTAMP()");
        ?><div class="wrap woogit-admin"><style>.woogit-admin-cards{display:grid;grid-template-columns:repeat(3,minmax(150px,1fr));gap:12px;margin:18px 0}.woogit-admin-cards>div{background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:16px}.woogit-admin-cards strong{display:block;font-size:24px;margin-bottom:4px}.woogit-admin-cards span{color:#646970}.woogit-admin-table-wrap{overflow:auto}.woogit-admin-toolbar{margin:16px 0}.woogit-action-stack{display:flex;gap:8px;flex-wrap:wrap}.woogit-status-active{font-weight:600}@media(max-width:782px){.woogit-admin-cards{grid-template-columns:1fr}.woogit-admin .widefat{min-width:850px}}</style><h1>مدیریت Sessionهای WooGit</h1><p>تمام Web Session و App/Operational Sessionها از اینجا قابل مشاهده و لغو هستند.</p><?php $this->notice($notice); ?><div class="woogit-admin-cards"><div><strong><?php echo esc_html($active); ?></strong><span>Session فعال</span></div><div><strong><?php echo esc_html($expired); ?></strong><span>Session منقضی</span></div><div><strong><?php echo esc_html(count($rows)); ?></strong><span>رکورد قابل نمایش</span></div></div><form method="post" class="woogit-admin-toolbar"><?php wp_nonce_field(self::ACTION); ?><input type="hidden" name="woogit_lifecycle_action" value="purge_expired_sessions"><button class="button">پاک‌سازی Sessionهای منقضی</button></form><div class="woogit-admin-table-wrap"><table class="widefat striped"><thead><tr><th>نوع</th><th>Account</th><th>Site</th><th>ایمیل</th><th>ایجاد</th><th>انقضا</th><th>وضعیت</th><th>عملیات</th></tr></thead><tbody><?php if(!$rows): ?><tr><td colspan="8">Sessionی وجود ندارد.</td></tr><?php else:foreach($rows as $r):$isActive=!$r['revoked_at']&&strtotime($r['expires_at'])>time(); ?><tr><td><strong><?php echo esc_html(strtoupper($r['type'])); ?></strong></td><td>#<?php echo esc_html($r['account_id']); ?></td><td><?php echo esc_html($r['host']?:('Site #'.$r['site_id'])); ?></td><td><?php echo esc_html($r['email']?:'—'); ?></td><td><?php echo esc_html($r['created_at']); ?></td><td><?php echo esc_html($r['expires_at']); ?></td><td><?php echo $isActive?'<span class="woogit-status-active">فعال</span>':($r['revoked_at']?'لغوشده':'منقضی'); ?></td><td><?php if($isActive): ?><form method="post"><?php wp_nonce_field(self::ACTION); ?><input type="hidden" name="woogit_lifecycle_action" value="revoke_session"><input type="hidden" name="session_type" value="<?php echo esc_attr($r['type']); ?>"><input type="hidden" name="session_id" value="<?php echo esc_attr($r['id']); ?>"><button class="button button-link-delete">لغو</button></form><?php else: ?>—<?php endif; ?></td></tr><?php endforeach;endif; ?></tbody></table></div></div><?php
    }

    public function renderTrials():void
    {
        if(!current_user_can('manage_options'))wp_die('دسترسی غیرمجاز.');
        global $wpdb;$a=$wpdb->prefix.'woogit_accounts';$s=$wpdb->prefix.'woogit_sites';$e=$wpdb->prefix.'woogit_entitlements';$notice=$this->handleTrialAction();
        $rows=$wpdb->get_results("SELECT a.id account_id,a.email,a.trial_used_at,s.id site_id,s.host,e.id entitlement_id,e.status,e.starts_at,e.expires_at FROM {$a} a LEFT JOIN {$s} s ON s.account_id=a.id LEFT JOIN {$e} e ON e.account_id=a.id AND e.site_id=s.id AND e.entitlement_type='trial' ORDER BY a.id DESC LIMIT 500",ARRAY_A);
        ?><div class="wrap woogit-admin"><h1>مدیریت Trialهای WooGit</h1><p>وضعیت Trial هر Account و Site و کنترل reset/revoke.</p><?php $this->notice($notice); ?><table class="widefat striped"><thead><tr><th>Account</th><th>ایمیل</th><th>Site</th><th>Trial</th><th>Entitlement</th><th>عملیات</th></tr></thead><tbody><?php if(!$rows): ?><tr><td colspan="6">Trialی وجود ندارد.</td></tr><?php else:foreach($rows as $r): ?><tr><td>#<?php echo esc_html($r['account_id']); ?></td><td><?php echo esc_html($r['email']?:'—'); ?></td><td><?php echo esc_html($r['host']?:($r['site_id']?'Site #'.$r['site_id']:'—')); ?></td><td><?php echo $r['trial_used_at']?'مصرف‌شده':'آزاد'; ?><?php if($r['trial_used_at']): ?><br><small><?php echo esc_html($r['trial_used_at']); ?></small><?php endif; ?></td><td><?php echo esc_html($r['status']?:'none'); ?><?php if($r['expires_at']): ?><br><small><?php echo esc_html($r['expires_at']); ?></small><?php endif; ?></td><td><form method="post" style="display:inline-block;margin-right:6px"><?php wp_nonce_field(self::ACTION); ?><input type="hidden" name="woogit_lifecycle_action" value="reset_trial"><input type="hidden" name="account_id" value="<?php echo esc_attr($r['account_id']); ?>"><button class="button">Reset Trial</button></form><?php if($r['entitlement_id']): ?><form method="post" style="display:inline-block"><?php wp_nonce_field(self::ACTION); ?><input type="hidden" name="woogit_lifecycle_action" value="revoke_trial"><input type="hidden" name="entitlement_id" value="<?php echo esc_attr($r['entitlement_id']); ?>"><button class="button button-link-delete">Revoke Trial</button></form><?php endif; ?></td></tr><?php endforeach;endif; ?></tbody></table></div><?php
    }

    private function handleSessionAction():?array
    {
        if(($_SERVER['REQUEST_METHOD']??'')!=='POST'||empty($_POST['woogit_lifecycle_action']))return null;
        if(!current_user_can('manage_options'))return ['error','دسترسی غیرمجاز.'];check_admin_referer(self::ACTION);global $wpdb;$action=sanitize_key((string)$_POST['woogit_lifecycle_action']);$now=gmdate('Y-m-d H:i:s');
        if($action==='purge_expired_sessions'){$n=0;foreach([$wpdb->prefix.'woogit_sessions',$wpdb->prefix.'woogit_web_sessions'] as $table){$r=$wpdb->query("DELETE FROM {$table} WHERE expires_at<=UTC_TIMESTAMP()");if($r!==false)$n+=(int)$r;}return ['success',$n.' Session منقضی پاک شد.'];}
        if($action==='revoke_session'){$type=sanitize_key((string)($_POST['session_type']??''));$id=absint($_POST['session_id']??0);$table=$type==='web'?$wpdb->prefix.'woogit_web_sessions':($type==='app'?$wpdb->prefix.'woogit_sessions':'');if(!$table||!$id)return ['error','Session نامعتبر است.'];$ok=false!==$wpdb->update($table,['revoked_at'=>$now],['id'=>$id],['%s'],['%d']);return [$ok?'success':'error',$ok?'Session لغو شد.':'لغو Session انجام نشد.'];}
        return ['error','عملیات ناشناخته است.'];
    }

    private function handleTrialAction():?array
    {
        if(($_SERVER['REQUEST_METHOD']??'')!=='POST'||empty($_POST['woogit_lifecycle_action']))return null;
        if(!current_user_can('manage_options'))return ['error','دسترسی غیرمجاز.'];check_admin_referer(self::ACTION);global $wpdb;$action=sanitize_key((string)$_POST['woogit_lifecycle_action']);$now=gmdate('Y-m-d H:i:s');
        if($action==='reset_trial'){$id=absint($_POST['account_id']??0);if(!$id)return ['error','Account نامعتبر است.'];$ok=false!==$wpdb->update($wpdb->prefix.'woogit_accounts',['trial_used_at'=>null,'updated_at'=>$now],['id'=>$id],['%s','%s'],['%d']);return [$ok?'success':'error',$ok?'Trial بازنشانی شد.':'بازنشانی Trial انجام نشد.'];}
        if($action==='revoke_trial'){$id=absint($_POST['entitlement_id']??0);if(!$id)return ['error','Trial نامعتبر است.'];$ok=false!==$wpdb->update($wpdb->prefix.'woogit_entitlements',['status'=>'revoked','updated_at'=>$now],['id'=>$id,'entitlement_type'=>'trial'],['%s','%s'],['%d','%s']);return [$ok?'success':'error',$ok?'Trial لغو شد.':'لغو Trial انجام نشد.'];}
        return ['error','عملیات ناشناخته است.'];
    }

    private function notice(?array $notice):void{if(!$notice)return;$class=$notice[0]==='success'?'updated':'error';echo '<div class="notice '.esc_attr($class).' is-dismissible"><p>'.esc_html($notice[1]).'</p></div>';}
}
