<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class AccountsAdmin
{
    private const ACTION = 'woogit_account_admin_action';

    public function register(): void
    {
        add_menu_page('WooGit Accounts','WooGit Accounts','manage_options','woogit-accounts',[$this,'render'],'dashicons-admin-users',56);
    }

    public function render(): void
    {
        if (!current_user_can('manage_options')) wp_die(esc_html__('You do not have permission to manage WooGit accounts.','woogit-backend'));
        $notice=$this->handleAction();
        global $wpdb;
        $accounts=$wpdb->prefix.'woogit_accounts'; $sites=$wpdb->prefix.'woogit_sites'; $users=$wpdb->users; $ent=$wpdb->prefix.'woogit_entitlements';
        $q=isset($_GET['s'])?sanitize_text_field(wp_unslash((string)$_GET['s'])):'';
        $where=''; $params=[];
        if($q!==''){$like='%'.$wpdb->esc_like($q).'%';$where=$wpdb->prepare(' WHERE a.email LIKE %s OR s.host LIKE %s OR u.user_login LIKE %s OR CAST(a.id AS CHAR) LIKE %s ', $like,$like,$like,$like);}
        $sql="SELECT a.id account_id,a.email contact_email,a.wp_user_id,a.status account_status,a.trial_used_at,a.created_at,a.updated_at,u.user_login,u.display_name,s.id site_id,s.canonical_url,s.host,s.status site_status,e.status entitlement_status,e.starts_at,e.expires_at,e.capabilities FROM {$accounts} a LEFT JOIN {$users} u ON u.ID=a.wp_user_id LEFT JOIN {$sites} s ON s.account_id=a.id LEFT JOIN {$ent} e ON e.account_id=a.id AND e.site_id=s.id {$where} ORDER BY a.id DESC";
        $rows=$wpdb->get_results($sql,ARRAY_A);
        $plans=$this->plans();
        ?>
        <div class="wrap">
            <h1>WooGit Accounts</h1>
            <p>مدیریت کامل حساب، هویت WordPress، سایت، Session و Entitlement از یک پنل.</p>
            <?php if($notice): ?><div class="notice notice-<?php echo esc_attr($notice['type']); ?> is-dismissible"><p><?php echo esc_html($notice['message']); ?></p></div><?php endif; ?>
            <form method="get" style="margin:16px 0"><input type="hidden" name="page" value="woogit-accounts"><input type="search" name="s" value="<?php echo esc_attr($q); ?>" placeholder="جستجو با Account ID، دامنه، نام کاربری یا ایمیل" style="min-width:360px"><button class="button">جستجو</button> <?php if($q!==''): ?><a class="button" href="<?php echo esc_url(admin_url('admin.php?page=woogit-accounts')); ?>">پاک کردن</a><?php endif; ?></form>
            <table class="widefat fixed striped">
                <thead><tr><th>Account</th><th>Identity</th><th>Site</th><th>Package</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if(!$rows): ?><tr><td colspan="6">حسابی پیدا نشد.</td></tr><?php else: foreach($rows as $r):
                    $caps=json_decode((string)$r['capabilities'],true); $caps=is_array($caps)?$caps:[];
                ?>
                    <tr>
                        <td><strong>#<?php echo esc_html($r['account_id']); ?></strong><br><small>Created: <?php echo esc_html($r['created_at']); ?></small><br><small>Updated: <?php echo esc_html($r['updated_at']); ?></small></td>
                        <td><?php echo $r['wp_user_id']?'ID '.esc_html($r['wp_user_id']).'<br><code>'.esc_html($r['user_login']??'').'</code>':'—'; ?><br><?php echo esc_html($r['contact_email']?:'بدون ایمیل'); ?><br><small><?php echo $r['trial_used_at']?'Trial used':'Trial available'; ?></small></td>
                        <td><?php if($r['canonical_url']): ?><a href="<?php echo esc_url($r['canonical_url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($r['host']); ?></a><br><small><?php echo esc_html($r['site_status']); ?></small><?php else: ?>—<?php endif; ?></td>
                        <td><?php echo esc_html($r['entitlement_status']?:'none'); ?><br><small><?php echo $r['expires_at']?'Expires: '.esc_html($r['expires_at']):'No expiry'; ?></small><br><small><?php echo esc_html(implode(', ',$caps)); ?></small></td>
                        <td><strong><?php echo esc_html($r['account_status']); ?></strong></td>
                        <td>
                            <details><summary class="button">مدیریت</summary>
                            <div style="padding:10px;display:grid;gap:8px;max-width:420px">
                                <form method="post"><?php wp_nonce_field(self::ACTION); ?><input type="hidden" name="woogit_action" value="edit"><input type="hidden" name="account_id" value="<?php echo esc_attr($r['account_id']); ?>"><label>ایمیل تماس<br><input type="email" name="email" value="<?php echo esc_attr($r['contact_email']??''); ?>" style="width:100%"></label><label>وضعیت حساب<br><select name="status"><option value="active" <?php selected($r['account_status'],'active'); ?>>active</option><option value="suspended" <?php selected($r['account_status'],'suspended'); ?>>suspended</option><option value="disabled" <?php selected($r['account_status'],'disabled'); ?>>disabled</option></select></label><button class="button button-primary">ذخیره ویرایش</button></form>
                                <?php if($r['site_id']): ?><form method="post"><?php wp_nonce_field(self::ACTION); ?><input type="hidden" name="woogit_action" value="activate_plan"><input type="hidden" name="account_id" value="<?php echo esc_attr($r['account_id']); ?>"><input type="hidden" name="site_id" value="<?php echo esc_attr($r['site_id']); ?>"><label>فعال‌سازی بسته<br><select name="product_id" style="width:100%"><option value="">انتخاب بسته...</option><?php foreach($plans as $p): ?><option value="<?php echo esc_attr($p['id']); ?>"><?php echo esc_html($p['name'].' — '.$p['key'].' — '.$p['days'].' روز'); ?></option><?php endforeach; ?></select></label><label>مدت اضافه (روز، اختیاری)<br><input type="number" name="extra_days" min="1" max="3650" value=""></label><button class="button button-primary">فعال کردن بسته</button></form>
                                <form method="post"><?php wp_nonce_field(self::ACTION); ?><input type="hidden" name="woogit_action" value="deactivate"><input type="hidden" name="account_id" value="<?php echo esc_attr($r['account_id']); ?>"><input type="hidden" name="site_id" value="<?php echo esc_attr($r['site_id']); ?>"><button class="button">غیرفعال کردن بسته</button></form><?php endif; ?>
                                <form method="post"><?php wp_nonce_field(self::ACTION); ?><input type="hidden" name="woogit_action" value="logout"><input type="hidden" name="account_id" value="<?php echo esc_attr($r['account_id']); ?>"><button class="button">خروج اجباری از همه Sessionها</button></form>
                                <form method="post"><?php wp_nonce_field(self::ACTION); ?><input type="hidden" name="woogit_action" value="repair_identity"><input type="hidden" name="account_id" value="<?php echo esc_attr($r['account_id']); ?>"><button class="button">تعمیر هویت WordPress</button></form>
                                <form method="post"><?php wp_nonce_field(self::ACTION); ?><input type="hidden" name="woogit_action" value="reset_trial"><input type="hidden" name="account_id" value="<?php echo esc_attr($r['account_id']); ?>"><button class="button">بازنشانی Trial</button></form>
                                <form method="post" onsubmit="return confirm('این حساب و داده‌های احراز هویت آن حذف می‌شود. ادامه می‌دهید؟');"><?php wp_nonce_field(self::ACTION); ?><input type="hidden" name="woogit_action" value="delete"><input type="hidden" name="account_id" value="<?php echo esc_attr($r['account_id']); ?>"><button class="button button-link-delete">حذف کامل حساب</button></form>
                            </div></details>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
            <h2 style="margin-top:28px">قابلیت‌های مدیریتی</h2>
            <p>جستجو و فیلتر، ویرایش اطلاعات تماس، فعال/تعلیق/غیرفعال‌سازی حساب، فعال‌سازی دستی بسته، تمدید مدت، لغو بسته، خروج اجباری Sessionها، تعمیر هویت WordPress، بازنشانی Trial، حذف کامل حساب، مشاهده سایت متصل، مشاهده وضعیت Entitlement، مشاهده تاریخ انقضا، مشاهده Capabilityها و کنترل مستقیم چرخه دسترسی.</p>
        </div>
        <?php
    }

    private function handleAction(): ?array
    {
        if(($_SERVER['REQUEST_METHOD']??'')!=='POST'||empty($_POST['woogit_action']))return null;
        if(!current_user_can('manage_options'))return ['type'=>'error','message'=>'دسترسی غیرمجاز.'];
        check_admin_referer(self::ACTION);
        $action=sanitize_key((string)$_POST['woogit_action']); $accountId=absint($_POST['account_id']??0);
        if($accountId<=0)return ['type'=>'error','message'=>'Account نامعتبر است.'];
        global $wpdb;$a=$wpdb->prefix.'woogit_accounts';$s=$wpdb->prefix.'woogit_sites';$e=$wpdb->prefix.'woogit_entitlements';$ws=$wpdb->prefix.'woogit_web_sessions';$ss=$wpdb->prefix.'woogit_sessions';
        if(!$wpdb->get_var($wpdb->prepare("SELECT id FROM {$a} WHERE id=%d",$accountId)))return ['type'=>'error','message'=>'Account پیدا نشد.'];
        if($action==='edit'){
            $email=sanitize_email(wp_unslash((string)($_POST['email']??'')));$status=sanitize_key((string)($_POST['status']??'active'));if($email!==''&&!is_email($email)||!in_array($status,['active','suspended','disabled'],true))return ['type'=>'error','message'=>'اطلاعات ویرایش نامعتبر است.'];
            $wpdb->update($a,['email'=>$email!==''?$email:null,'status'=>$status,'updated_at'=>gmdate('Y-m-d H:i:s')],['id'=>$accountId],['%s','%s','%s'],['%d']);if($status!=='active'){$wpdb->query($wpdb->prepare("UPDATE {$ws} SET revoked_at=%s WHERE account_id=%d AND revoked_at IS NULL",gmdate('Y-m-d H:i:s'),$accountId));$wpdb->query($wpdb->prepare("UPDATE {$ss} SET revoked_at=%s WHERE account_id=%d AND revoked_at IS NULL",gmdate('Y-m-d H:i:s'),$accountId));}return ['type'=>'success','message'=>'حساب ویرایش شد.'];
        }
        if($action==='logout'){$now=gmdate('Y-m-d H:i:s');$wpdb->query($wpdb->prepare("UPDATE {$ws} SET revoked_at=%s WHERE account_id=%d AND revoked_at IS NULL",$now,$accountId));$wpdb->query($wpdb->prepare("UPDATE {$ss} SET revoked_at=%s WHERE account_id=%d AND revoked_at IS NULL",$now,$accountId));return ['type'=>'success','message'=>'تمام Sessionهای حساب لغو شدند.'];}
        if($action==='repair_identity'){return (new AccountService())->ensureIdentity($accountId)?['type'=>'success','message'=>'هویت WordPress بررسی/تعمیر شد.']:['type'=>'error','message'=>'تعمیر هویت انجام نشد.'];}
        if($action==='reset_trial'){$ok=false!==$wpdb->update($a,['trial_used_at'=>null,'updated_at'=>gmdate('Y-m-d H:i:s')],['id'=>$accountId],['%s','%s'],['%d']);return ['type'=>$ok?'success':'error','message'=>$ok?'Trial بازنشانی شد.':'بازنشانی Trial انجام نشد.'];}
        if($action==='deactivate'){$siteId=absint($_POST['site_id']??0);if(!$siteId)return ['type'=>'error','message'=>'Site نامعتبر است.'];$ok=false!==$wpdb->update($e,['status'=>'inactive','updated_at'=>gmdate('Y-m-d H:i:s')],['account_id'=>$accountId,'site_id'=>$siteId],['%s','%s'],['%d','%d']);(new WebSessionService())->revokeAllForAccount($accountId);return ['type'=>$ok?'success':'error','message'=>$ok?'بسته غیرفعال شد و Sessionها لغو شدند.':'Entitlement پیدا نشد.'];}
        if($action==='activate_plan')return $this->activatePlan($accountId,absint($_POST['site_id']??0),absint($_POST['product_id']??0),absint($_POST['extra_days']??0));
        if($action==='delete'){
            $userId=(int)$wpdb->get_var($wpdb->prepare("SELECT wp_user_id FROM {$a} WHERE id=%d",$accountId));$wpdb->delete($ws,['account_id'=>$accountId],['%d']);$wpdb->delete($ss,['account_id'=>$accountId],['%d']);$wpdb->delete($e,['account_id'=>$accountId],['%d']);$wpdb->delete($wpdb->prefix.'woogit_idempotency',['account_id'=>$accountId],['%d']);$wpdb->delete($wpdb->prefix.'woogit_operations',['account_id'=>$accountId],['%d']);$wpdb->delete($s,['account_id'=>$accountId],['%d']);$wpdb->delete($a,['id'=>$accountId],['%d']);if($userId>0)wp_delete_user($userId);return ['type'=>'success','message'=>'حساب، سایت، Entitlement و Sessionهای آن حذف شدند. سوابق سفارش WooCommerce برای حسابداری حفظ شدند.'];
        }
        return ['type'=>'error','message'=>'عملیات ناشناخته است.'];
    }

    private function activatePlan(int $accountId,int $siteId,int $productId,int $extraDays): array
    {
        if($siteId<=0||$productId<=0)return ['type'=>'error','message'=>'سایت یا بسته انتخاب نشده است.'];
        if(!function_exists('wc_get_product'))return ['type'=>'error','message'=>'WooCommerce در دسترس نیست.'];
        $product=wc_get_product($productId);if(!$product||$product->get_status()!=='publish')return ['type'=>'error','message'=>'بسته پیدا نشد یا منتشر نشده است.'];
        $enabled=get_post_meta($productId,'_woogit_plan_enabled',true);if($enabled!==''&&$enabled!=='yes')return ['type'=>'error','message'=>'این محصول به‌عنوان WooGit Plan فعال نیست.'];
        $period=(string)$product->get_meta('_subscription_period');$interval=max(1,(int)($product->get_meta('_subscription_period_interval')?:1));$days=$this->periodDays($period,$interval)+max(0,min(3650,$extraDays));if($days<=0)$days=max(1,$extraDays);if($days<=0)return ['type'=>'error','message'=>'مدت بسته قابل تشخیص نیست.'];
        global $wpdb;$table=$wpdb->prefix.'woogit_entitlements';$now=time();$expires=$now+($days*DAY_IN_SECONDS);$caps=['commerce'];$existing=$wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE account_id=%d AND site_id=%d LIMIT 1",$accountId,$siteId));$data=['status'=>'active','starts_at'=>gmdate('Y-m-d H:i:s',$now),'expires_at'=>gmdate('Y-m-d H:i:s',$expires),'capabilities'=>wp_json_encode($caps),'updated_at'=>gmdate('Y-m-d H:i:s')];$formats=['%s','%s','%s','%s','%s'];if($existing)$ok=false!==$wpdb->update($table,$data,['id'=>(int)$existing],$formats,['%d']);else{$data['account_id']=$accountId;$data['site_id']=$siteId;$data['created_at'=>gmdate('Y-m-d H:i:s',$now)];$ok=false!==$wpdb->insert($table,$data,['%s','%s','%s','%s','%s','%d','%d','%s']);}
        if(!$ok)return ['type'=>'error','message'=>'فعال‌سازی بسته انجام نشد.'];(new WebSessionService())->revokeAllForAccount($accountId);return ['type'=>'success','message'=>'بسته «'.$product->get_name().'» برای حساب فعال شد؛ اعتبار تا '.gmdate('Y-m-d H:i:s',$expires).' UTC است.'];
    }

    private function plans(): array
    {
        if(!function_exists('wc_get_products'))return [];$out=[];$products=wc_get_products(['status'=>'publish','limit'=>-1]);foreach((array)$products as $p){if(!$p||!$p->is_purchasable())continue;$enabled=get_post_meta((int)$p->get_id(),'_woogit_plan_enabled',true);if($enabled!==''&&$enabled!=='yes')continue;$days=$this->periodDays((string)$p->get_meta('_subscription_period'),max(1,(int)($p->get_meta('_subscription_period_interval')?:1)));if($days<=0)continue;$out[]=['id'=>(int)$p->get_id(),'name'=>(string)$p->get_name(),'key'=>(string)(get_post_meta((int)$p->get_id(),'_woogit_plan_key',true)?:sanitize_title((string)$p->get_name())),'days'=>$days];}return $out;
    }
    private function periodDays(string $period,int $interval): int { $map=['day'=>1,'week'=>7,'month'=>30,'year'=>365];return isset($map[$period])?$map[$period]*$interval:0; }
}
