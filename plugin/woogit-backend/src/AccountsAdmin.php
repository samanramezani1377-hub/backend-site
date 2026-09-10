<?php
namespace WooGit\Backend;
defined('ABSPATH') || exit;

final class AccountsAdmin
{
    private const ACTION='woogit_account_admin_action';
    private const ACCOUNT_STATUSES=['active','suspended','disabled'];
    private const SITE_STATUSES=['active','suspended','disabled'];

    public function register():void
    {
        add_menu_page('WooGit','WooGit','manage_options','woogit-accounts',[$this,'render'],'dashicons-admin-users',56);
        add_submenu_page('woogit-accounts','مدیریت Accounts','Accounts','manage_options','woogit-accounts',[$this,'render']);
    }

    public function render():void
    {
        if(!current_user_can('manage_options')) wp_die('دسترسی غیرمجاز.');
        $notice=$this->action();
        global $wpdb;
        $a=$wpdb->prefix.'woogit_accounts'; $s=$wpdb->prefix.'woogit_sites'; $e=$wpdb->prefix.'woogit_entitlements';
        $u=$wpdb->users; $ws=$wpdb->prefix.'woogit_web_sessions'; $ss=$wpdb->prefix.'woogit_sessions';
        $q=isset($_GET['s'])?sanitize_text_field(wp_unslash((string)$_GET['s'])):'';
        $where='';
        if($q!==''){
            $like='%'.$wpdb->esc_like($q).'%';
            $where=$wpdb->prepare(' WHERE a.email LIKE %s OR s.host LIKE %s OR u.user_login LIKE %s OR CAST(a.id AS CHAR) LIKE %s ',$like,$like,$like,$like);
        }
        $rows=$wpdb->get_results("SELECT a.*,u.user_login,u.display_name,s.id site_id,s.canonical_url,s.host,s.status site_status,e.status entitlement_status,e.starts_at,e.expires_at,e.capabilities,(SELECT COUNT(*) FROM {$ws} w WHERE w.account_id=a.id AND w.revoked_at IS NULL AND w.expires_at>UTC_TIMESTAMP()) active_web_sessions,(SELECT COUNT(*) FROM {$ss} x WHERE x.account_id=a.id AND x.revoked_at IS NULL AND x.expires_at>UTC_TIMESTAMP()) active_app_sessions FROM {$a} a LEFT JOIN {$u} u ON u.ID=a.wp_user_id LEFT JOIN {$s} s ON s.account_id=a.id LEFT JOIN {$e} e ON e.account_id=a.id AND e.site_id=s.id {$where} ORDER BY a.id DESC",ARRAY_A);
        $plans=$this->plans();
        $stats=[
            'accounts'=>(int)$wpdb->get_var("SELECT COUNT(*) FROM {$a}"),
            'active'=>(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$a} WHERE status=%s",'active')),
            'suspended'=>(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$a} WHERE status=%s",'suspended')),
            'entitled'=>(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$e} WHERE status=%s AND (expires_at IS NULL OR expires_at>UTC_TIMESTAMP())",'active')),
            'trials'=>(int)$wpdb->get_var("SELECT COUNT(*) FROM {$a} WHERE trial_used_at IS NOT NULL"),
        ];
        ?>
        <div class="wrap">
            <h1>WooGit Accounts</h1>
            <p>مرکز مدیریت کامل حساب، سایت، Identity، Session، Trial و Entitlement.</p>
            <?php if($notice): ?><div class="notice notice-<?php echo esc_attr($notice[0]); ?> is-dismissible"><p><?php echo esc_html($notice[1]); ?></p></div><?php endif; ?>
            <div style="display:grid;grid-template-columns:repeat(5,minmax(130px,1fr));gap:12px;margin:18px 0">
                <?php foreach([['کل حساب‌ها',$stats['accounts']],['فعال',$stats['active']],['معلق',$stats['suspended']],['بسته فعال',$stats['entitled']],['Trial مصرف‌شده',$stats['trials']]] as $card): ?><div style="background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:14px"><strong style="display:block;font-size:22px"><?php echo esc_html($card[1]); ?></strong><span><?php echo esc_html($card[0]); ?></span></div><?php endforeach; ?>
            </div>
            <form method="get" style="margin:16px 0"><input type="hidden" name="page" value="woogit-accounts"><input type="search" name="s" value="<?php echo esc_attr($q); ?>" placeholder="Account ID، دامنه، username یا email" style="min-width:380px"><button class="button">جستجو</button></form>
            <table class="widefat striped"><thead><tr><th>Account</th><th>Identity / Contact</th><th>Site</th><th>Entitlement</th><th>Session</th><th>مدیریت</th></tr></thead><tbody>
            <?php if(!$rows): ?><tr><td colspan="6">حسابی پیدا نشد.</td></tr><?php else: foreach($rows as $r): $caps=$this->decodeCapabilities($r['capabilities']??''); ?>
                <tr>
                    <td><strong>#<?php echo esc_html($r['id']); ?></strong><br><small><?php echo esc_html($r['status']); ?></small><br><small><?php echo esc_html($r['created_at']); ?></small></td>
                    <td><?php echo $r['wp_user_id']?'WP #'.esc_html($r['wp_user_id']).' · <code>'.esc_html($r['user_login']??'').'</code>':'بدون Identity'; ?><br><?php echo esc_html($r['email']?:'بدون ایمیل'); ?><br><small><?php echo $r['trial_used_at']?'Trial مصرف شده':'Trial آزاد'; ?></small></td>
                    <td><?php if($r['site_id']): ?><a href="<?php echo esc_url($r['canonical_url']); ?>" target="_blank" rel="noopener"><?php echo esc_html($r['host']); ?></a><br><small><?php echo esc_html($r['site_status']); ?></small><?php else: ?>بدون سایت<?php endif; ?></td>
                    <td><strong><?php echo esc_html($r['entitlement_status']?:'none'); ?></strong><br><small><?php echo $r['expires_at']?'انقضا: '.esc_html($r['expires_at']):'بدون انقضا'; ?></small><br><small><?php echo esc_html(implode(', ',$caps)); ?></small></td>
                    <td>Web: <?php echo esc_html($r['active_web_sessions']); ?><br>App: <?php echo esc_html($r['active_app_sessions']); ?></td>
                    <td>
                        <details><summary class="button">مدیریت حساب</summary>
                        <div style="padding:14px;display:grid;gap:12px;max-width:520px">
                            <form method="post"><?php wp_nonce_field(self::ACTION); ?><input type="hidden" name="woogit_action" value="edit"><input type="hidden" name="account_id" value="<?php echo esc_attr($r['id']); ?>"><strong>۱. ویرایش حساب</strong><label>ایمیل تماس<br><input type="email" name="email" value="<?php echo esc_attr($r['email']??''); ?>" style="width:100%"></label><label>وضعیت حساب<br><select name="status" style="width:100%"><?php foreach(self::ACCOUNT_STATUSES as $v): ?><option value="<?php echo esc_attr($v); ?>" <?php selected($r['status'],$v); ?>><?php echo esc_html($v); ?></option><?php endforeach; ?></select></label><button class="button button-primary">ذخیره</button></form>
                            <?php if($r['site_id']): ?>
                            <form method="post"><?php wp_nonce_field(self::ACTION); ?><input type="hidden" name="woogit_action" value="site_status"><input type="hidden" name="account_id" value="<?php echo esc_attr($r['id']); ?>"><input type="hidden" name="site_id" value="<?php echo esc_attr($r['site_id']); ?>"><strong>۲. وضعیت سایت</strong><select name="site_status" style="width:100%"><?php foreach(self::SITE_STATUSES as $v): ?><option value="<?php echo esc_attr($v); ?>" <?php selected($r['site_status'],$v); ?>><?php echo esc_html($v); ?></option><?php endforeach; ?></select><button class="button">ذخیره وضعیت سایت</button></form>
                            <form method="post"><?php wp_nonce_field(self::ACTION); ?><input type="hidden" name="woogit_action" value="activate"><input type="hidden" name="account_id" value="<?php echo esc_attr($r['id']); ?>"><input type="hidden" name="site_id" value="<?php echo esc_attr($r['site_id']); ?>"><strong>۳. فعال‌سازی دستی بسته</strong><select name="product_id" required style="width:100%"><option value="">انتخاب بسته...</option><?php foreach($plans as $p): ?><option value="<?php echo esc_attr($p['id']); ?>"><?php echo esc_html($p['name'].' — '.$p['days'].' روز'); ?></option><?php endforeach; ?></select><input type="number" name="extra_days" min="0" max="3650" placeholder="روز اضافه اختیاری" style="width:100%"><button class="button button-primary">فعال‌سازی</button></form>
                            <form method="post"><?php wp_nonce_field(self::ACTION); ?><input type="hidden" name="woogit_action" value="extend"><input type="hidden" name="account_id" value="<?php echo esc_attr($r['id']); ?>"><input type="hidden" name="site_id" value="<?php echo esc_attr($r['site_id']); ?>"><strong>۴. تمدید دستی اعتبار</strong><input type="number" name="days" min="1" max="3650" required placeholder="تعداد روز" style="width:100%"><button class="button">تمدید</button></form>
                            <form method="post"><?php wp_nonce_field(self::ACTION); ?><input type="hidden" name="woogit_action" value="deactivate"><input type="hidden" name="account_id" value="<?php echo esc_attr($r['id']); ?>"><input type="hidden" name="site_id" value="<?php echo esc_attr($r['site_id']); ?>"><strong>۵. لغو دسترسی بسته</strong><button class="button">غیرفعال‌سازی Entitlement</button></form>
                            <form method="post"><?php wp_nonce_field(self::ACTION); ?><input type="hidden" name="woogit_action" value="capabilities"><input type="hidden" name="account_id" value="<?php echo esc_attr($r['id']); ?>"><input type="hidden" name="site_id" value="<?php echo esc_attr($r['site_id']); ?>"><strong>۶. مدیریت Capability</strong><input type="text" name="capabilities" value="<?php echo esc_attr(implode(', ',$caps)); ?>" placeholder="commerce, reports" style="width:100%"><button class="button">ذخیره Capability</button></form>
                            <?php endif; ?>
                            <form method="post"><?php wp_nonce_field(self::ACTION); ?><input type="hidden" name="woogit_action" value="logout"><input type="hidden" name="account_id" value="<?php echo esc_attr($r['id']); ?>"><strong>۷. خروج اجباری</strong><button class="button">لغو همه Sessionها</button></form>
                            <form method="post"><?php wp_nonce_field(self::ACTION); ?><input type="hidden" name="woogit_action" value="repair"><input type="hidden" name="account_id" value="<?php echo esc_attr($r['id']); ?>"><strong>۸. تعمیر Identity</strong><button class="button">بررسی و تعمیر WP Identity</button></form>
                            <form method="post"><?php wp_nonce_field(self::ACTION); ?><input type="hidden" name="woogit_action" value="trial"><input type="hidden" name="account_id" value="<?php echo esc_attr($r['id']); ?>"><strong>۹. مدیریت Trial</strong><button class="button">بازنشانی Trial</button></form>
                            <form method="post"><?php wp_nonce_field(self::ACTION); ?><input type="hidden" name="woogit_action" value="clear_sessions"><input type="hidden" name="account_id" value="<?php echo esc_attr($r['id']); ?>"><strong>۱۰. پاک‌سازی Sessionهای منقضی</strong><button class="button">پاک‌سازی رکوردهای منقضی</button></form>
                            <form method="post"><?php wp_nonce_field(self::ACTION); ?><input type="hidden" name="woogit_action" value="delete"><input type="hidden" name="account_id" value="<?php echo esc_attr($r['id']); ?>"><strong>۱۱. حذف کامل حساب</strong><button class="button button-link-delete" onclick="return confirm('حذف کامل حساب و Identity انجام شود؟ سفارش‌های WooCommerce حفظ می‌شوند.');">حذف دائمی</button></form>
                        </div></details>
                    </td>
                </tr>
            <?php endforeach; endif; ?></tbody></table>
            <h2>عملیات مدیریتی</h2><p>ویرایش حساب، وضعیت حساب، وضعیت سایت، فعال‌سازی بسته، تمدید اعتبار، لغو Entitlement، مدیریت Capability، خروج اجباری Session، تعمیر Identity، بازنشانی Trial، پاک‌سازی Sessionهای منقضی و حذف کامل حساب.</p>
        </div>
        <?php
    }

    private function action():?array
    {
        if(($_SERVER['REQUEST_METHOD']??'')!=='POST'||empty($_POST['woogit_action'])) return null;
        if(!current_user_can('manage_options')) return ['error','دسترسی غیرمجاز.'];
        check_admin_referer(self::ACTION);
        $act=sanitize_key((string)$_POST['woogit_action']); $id=absint($_POST['account_id']??0);
        if(!$id) return ['error','Account نامعتبر است.'];
        global $wpdb;
        $a=$wpdb->prefix.'woogit_accounts'; $s=$wpdb->prefix.'woogit_sites'; $e=$wpdb->prefix.'woogit_entitlements';
        $ws=$wpdb->prefix.'woogit_web_sessions'; $ss=$wpdb->prefix.'woogit_sessions';
        if(!$wpdb->get_var($wpdb->prepare("SELECT id FROM {$a} WHERE id=%d",$id))) return ['error','Account پیدا نشد.'];
        $now=gmdate('Y-m-d H:i:s');
        if($act==='edit'){
            $email=sanitize_email(wp_unslash((string)($_POST['email']??''))); $status=sanitize_key((string)($_POST['status']??''));
            if($email!==''&&!is_email($email)||!in_array($status,self::ACCOUNT_STATUSES,true)) return ['error','اطلاعات حساب نامعتبر است.'];
            $ok=false!==$wpdb->update($a,['email'=>$email!==''?$email:null,'status'=>$status,'updated_at'=>$now],['id'=>$id],['%s','%s','%s'],['%d']);
            if($status!=='active') $this->revokeSessions($id,$now);
            return [$ok?'success':'error',$ok?'حساب ویرایش شد.':'ویرایش ذخیره نشد.'];
        }
        if($act==='site_status'){
            $site=absint($_POST['site_id']??0); $status=sanitize_key((string)($_POST['site_status']??''));
            if(!$site||!in_array($status,self::SITE_STATUSES,true)) return ['error','وضعیت سایت نامعتبر است.'];
            $ok=false!==$wpdb->update($s,['status'=>$status,'updated_at'=>$now],['id'=>$site,'account_id'=>$id],['%s','%s'],['%d','%d']);
            if($status!=='active') $this->revokeSessions($id,$now);
            return [$ok?'success':'error',$ok?'وضعیت سایت تغییر کرد.':'وضعیت سایت ذخیره نشد.'];
        }
        if($act==='activate') return $this->activate($id,absint($_POST['site_id']??0),absint($_POST['product_id']??0),absint($_POST['extra_days']??0));
        if($act==='extend'){
            $site=absint($_POST['site_id']??0); $days=max(1,min(3650,absint($_POST['days']??0))); if(!$site||!$days)return['error','مقادیر تمدید نامعتبر است.'];
            $row=$wpdb->get_row($wpdb->prepare("SELECT id,status,expires_at FROM {$e} WHERE account_id=%d AND site_id=%d LIMIT 1",$id,$site),ARRAY_A); if(!$row)return['error','Entitlement پیدا نشد.'];
            $base=max(time(),strtotime((string)$row['expires_at']?:'')); $expires=gmdate('Y-m-d H:i:s',$base+$days*DAY_IN_SECONDS);
            $ok=false!==$wpdb->update($e,['status'=>'active','expires_at'=>$expires,'updated_at'=>$now],['id'=>(int)$row['id']],['%s','%s','%s'],['%d']); $this->revokeSessions($id,$now);
            return[$ok?'success':'error',$ok?'اعتبار '.$days.' روز تمدید شد.':'تمدید ذخیره نشد.'];
        }
        if($act==='deactivate'){
            $site=absint($_POST['site_id']??0); if(!$site)return['error','Site نامعتبر است.'];
            $ok=false!==$wpdb->update($e,['status'=>'inactive','updated_at'=>$now],['account_id'=>$id,'site_id'=>$site],['%s','%s'],['%d','%d']); $this->revokeSessions($id,$now);
            return[$ok?'success':'error',$ok?'دسترسی بسته لغو شد.':'Entitlement پیدا نشد.'];
        }
        if($act==='capabilities'){
            $site=absint($_POST['site_id']??0); if(!$site)return['error','Site نامعتبر است.'];
            $raw=explode(',',sanitize_text_field(wp_unslash((string)($_POST['capabilities']??'')))); $caps=[];
            foreach($raw as $cap){$cap=sanitize_key(trim($cap));if($cap!==''&&!in_array($cap,$caps,true))$caps[]=$cap;}
            $row=$wpdb->get_row($wpdb->prepare("SELECT id FROM {$e} WHERE account_id=%d AND site_id=%d LIMIT 1",$id,$site),ARRAY_A);if(!$row)return['error','Entitlement پیدا نشد.'];
            $ok=false!==$wpdb->update($e,['capabilities'=>wp_json_encode($caps),'updated_at'=>$now],['id'=>(int)$row['id']],['%s','%s'],['%d']); $this->revokeSessions($id,$now);
            return[$ok?'success':'error',$ok?'Capabilityها ذخیره شدند.':'Capabilityها ذخیره نشدند.'];
        }
        if($act==='logout'){ $this->revokeSessions($id,$now); return['success','همه Sessionهای فعال لغو شدند.']; }
        if($act==='repair') return(new AccountService())->ensureIdentity($id)?['success','Identity بررسی/تعمیر شد.']:['error','تعمیر Identity انجام نشد.'];
        if($act==='trial'){$ok=false!==$wpdb->update($a,['trial_used_at'=>null,'updated_at'=>$now],['id'=>$id],['%s','%s'],['%d']);return[$ok?'success':'error',$ok?'Trial بازنشانی شد.':'Trial بازنشانی نشد.'];}
        if($act==='clear_sessions'){
            $cutoff=gmdate('Y-m-d H:i:s'); $n1=(int)$wpdb->query($wpdb->prepare("DELETE FROM {$ws} WHERE account_id=%d AND (revoked_at IS NOT NULL OR expires_at<=%s)",$id,$cutoff)); $n2=(int)$wpdb->query($wpdb->prepare("DELETE FROM {$ss} WHERE account_id=%d AND (revoked_at IS NOT NULL OR expires_at<=%s)",$id,$cutoff));
            return['success','Sessionهای منقضی/لغوشده پاک شدند: '.($n1+$n2).' رکورد.'];
        }
        if($act==='delete'){
            $uid=(int)$wpdb->get_var($wpdb->prepare("SELECT wp_user_id FROM {$a} WHERE id=%d",$id));
            foreach([$ws,$ss,$e,$wpdb->prefix.'woogit_idempotency',$wpdb->prefix.'woogit_operations',$s,$a] as $table) $wpdb->delete($table,['account_id'=>$id],['%d']);
            if($uid>0) wp_delete_user($uid);
            return['success','حساب، سایت، Entitlement، Session و داده‌های عملیاتی آن حذف شد؛ سفارش‌های WooCommerce دست‌نخورده ماندند.'];
        }
        return['error','عملیات ناشناخته است.'];
    }

    private function revokeSessions(int $accountId,string $now):void
    {
        global $wpdb; $ws=$wpdb->prefix.'woogit_web_sessions'; $ss=$wpdb->prefix.'woogit_sessions';
        $wpdb->query($wpdb->prepare("UPDATE {$ws} SET revoked_at=%s WHERE account_id=%d AND revoked_at IS NULL",$now,$accountId));
        $wpdb->query($wpdb->prepare("UPDATE {$ss} SET revoked_at=%s WHERE account_id=%d AND revoked_at IS NULL",$now,$accountId));
    }

    private function activate(int $id,int $site,int $productId,int $extra):array
    {
        if(!$site||!$productId)return['error','سایت یا بسته انتخاب نشده است.'];
        global $wpdb; $s=$wpdb->prefix.'woogit_sites'; $e=$wpdb->prefix.'woogit_entitlements';
        if(!(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$s} WHERE id=%d AND account_id=%d",$site,$id)))return['error','Site متعلق به این Account نیست.'];
        if(!function_exists('wc_get_product'))return['error','WooCommerce در دسترس نیست.'];
        $p=wc_get_product($productId); if(!$p||$p->get_status()!=='publish')return['error','بسته معتبر نیست.'];
        $enabled=get_post_meta($productId,'_woogit_plan_enabled',true); if($enabled!==''&&$enabled!=='yes')return['error','این محصول WooGit Plan فعال نیست.'];
        $period=(string)$p->get_meta('_subscription_period'); $interval=max(1,(int)($p->get_meta('_subscription_period_interval')?:1));
        $days=$this->days($period,$interval)+max(0,min(3650,$extra)); if($days<=0)return['error','مدت بسته قابل تشخیص نیست.'];
        $now=time(); $data=['status'=>'active','starts_at'=>gmdate('Y-m-d H:i:s',$now),'expires_at'=>gmdate('Y-m-d H:i:s',$now+$days*DAY_IN_SECONDS),'capabilities'=>wp_json_encode(['commerce']),'updated_at'=>gmdate('Y-m-d H:i:s',$now)];
        $old=$wpdb->get_var($wpdb->prepare("SELECT id FROM {$e} WHERE account_id=%d AND site_id=%d LIMIT 1",$id,$site));
        if($old)$ok=false!==$wpdb->update($e,$data,['id'=>(int)$old],['%s','%s','%s','%s','%s'],['%d']);
        else{$data['account_id']=$id;$data['site_id']=$site;$data['created_at']=gmdate('Y-m-d H:i:s',$now);$ok=false!==$wpdb->insert($e,$data,['%s','%s','%s','%s','%s','%d','%d','%s']);}
        if(!$ok)return['error','فعال‌سازی بسته ذخیره نشد.'];
        $this->revokeSessions($id,gmdate('Y-m-d H:i:s',$now));
        return['success','بسته «'.$p->get_name().'» برای حساب فعال شد.'];
    }

    private function plans():array
    {
        if(!function_exists('wc_get_products'))return[]; $out=[];
        foreach((array)wc_get_products(['status'=>'publish','limit'=>-1]) as $p){
            if(!$p||!$p->is_purchasable())continue; $enabled=get_post_meta((int)$p->get_id(),'_woogit_plan_enabled',true); if($enabled!==''&&$enabled!=='yes')continue;
            $days=$this->days((string)$p->get_meta('_subscription_period'),max(1,(int)($p->get_meta('_subscription_period_interval')?:1))); if($days<=0)continue;
            $out[]=['id'=>(int)$p->get_id(),'name'=>(string)$p->get_name(),'days'=>$days];
        }
        usort($out,static fn($x,$y)=>$x['days']<=>$y['days']); return$out;
    }

    private function days(string $period,int $interval):int
    {
        switch(strtolower($period)){case'day':return $interval;case'week':return 7*$interval;case'month':return 30*$interval;case'year':return 365*$interval;default:return 0;}
    }

    private function decodeCapabilities(string $json):array
    {
        $v=json_decode($json,true); if(!is_array($v))return[]; return array_values(array_filter(array_map('sanitize_key',$v)));
    }
}
