<?php
/**
 * Plugin Name: WooGit Backend
 * Description: WooGit V1 secure transparent gateway/proxy.
 * Version: 0.3.8
 * Requires at least: 6.4
 * Requires PHP: 8.1
 */

defined('ABSPATH') || exit;

define('WOOGIT_BACKEND_VERSION','0.3.8');
define('WOOGIT_BACKEND_FILE',__FILE__);
define('WOOGIT_BACKEND_DIR',plugin_dir_path(__FILE__));
foreach(['Database','IdentityService','AccountService','SiteService','EntitlementService','SessionService','WebSessionService','IdempotencyService','OperationService','ProxyPolicy','WooCommerceProxy','RateLimitService','VersionGate','VersionAdmin','AnnouncementService','AnnouncementAdmin','AnnouncementController','RequirementService','BillingService','TrialService','AutomaticTrialMiloBridge','MiloBillingAdminCompatibility','MiloEntitlementReconciler','ZarinPalPaymentBridge','PaymentReturnRedirect','WebSessionRestBridge','RestController','BillingController','WebAuthController','AccountsAdmin','LifecycleAdmin'] as $file) require_once WOOGIT_BACKEND_DIR.'src/'.$file.'.php';
register_activation_hook(__FILE__,['WooGit\\Backend\\Database','install']);
add_action('plugins_loaded',static function():void{$from=(string)get_option('woogit_backend_db_version','');if($from !== WOOGIT_BACKEND_VERSION) \WooGit\Backend\Database::install($from);$trial=new \WooGit\Backend\TrialService();$trial->registerHooks();$billing=new \WooGit\Backend\BillingService();$billing->registerHooks();(new \WooGit\Backend\AutomaticTrialMiloBridge())->registerHooks();(new \WooGit\Backend\MiloEntitlementReconciler())->registerHooks();(new \WooGit\Backend\MiloBillingAdminCompatibility())->registerHooks();(new \WooGit\Backend\ZarinPalPaymentBridge())->register();(new \WooGit\Backend\PaymentReturnRedirect())->register();(new \WooGit\Backend\WebSessionRestBridge())->register();});
add_action('admin_menu',static function():void{
    $accounts=new \WooGit\Backend\AccountsAdmin();
    $lifecycle=new \WooGit\Backend\LifecycleAdmin();
    $version=new \WooGit\Backend\VersionAdmin();
    $announcement=new \WooGit\Backend\AnnouncementAdmin();
    add_menu_page('WooGit','WooGit','manage_options','woogit',[$accounts,'render'],'dashicons-admin-users',56);
    add_submenu_page('woogit','مدیریت Accounts','Accounts','manage_options','woogit-accounts',[$accounts,'render']);
    add_submenu_page('woogit','WooGit Sessions','Sessionها','manage_options','woogit-sessions',[$lifecycle,'renderSessions']);
    add_submenu_page('woogit','WooGit Trials','Trialها','manage_options','woogit-trials',[$lifecycle,'renderTrials']);
    add_submenu_page('woogit','WooGit App Versions','App Versions','manage_options','woogit-app-versions',[$version,'render']);
    add_submenu_page('woogit','WooGit Announcements','Announcements','manage_options','woogit-announcements',[$announcement,'render']);
},10);
add_action('admin_head',static function():void{if(($_GET['page']??'')!=='woogit-accounts')return;?>
<style>
#wpbody-content>.wrap{max-width:1500px}
#wpbody-content>.wrap h1{font-size:28px;font-weight:700;letter-spacing:-.3px;margin-bottom:6px}
#wpbody-content>.wrap>p{color:#646970;font-size:14px}
#wpbody-content>.wrap table.widefat{border:0;border-radius:14px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.05);background:#fff}
#wpbody-content>.wrap table.widefat thead th{background:#f6f7f9;font-weight:700;padding:13px 12px;white-space:nowrap}
#wpbody-content>.wrap table.widefat tbody td{padding:15px 12px;vertical-align:top}
#wpbody-content>.wrap table.widefat tbody tr:hover{background:#fafbfc}
#wpbody-content>.wrap table.widefat code{font-size:11px}
#wpbody-content>.wrap details{background:#f8f9fb;border:1px solid #e1e4e8;border-radius:12px;padding:8px}
#wpbody-content>.wrap details summary{cursor:pointer;font-weight:600;list-style:none}
#wpbody-content>.wrap details summary::-webkit-details-marker{display:none}
#wpbody-content>.wrap details[open] summary{margin-bottom:8px}
#wpbody-content>.wrap details form{background:#fff;border:1px solid #e3e5e8;border-radius:10px;padding:13px!important}
#wpbody-content>.wrap details form strong{font-size:13px}
#wpbody-content>.wrap details input,#wpbody-content>.wrap details select{box-sizing:border-box;min-height:38px;border-radius:8px;border:1px solid #c9cdd2;margin-top:5px}
#wpbody-content>.wrap details .button{min-height:36px;border-radius:8px;padding:0 13px}
#wpbody-content>.wrap details .button-primary{box-shadow:none}
@media (max-width:900px){
 #wpbody-content>.wrap{margin-right:10px;margin-left:10px}
 #wpbody-content>.wrap h1{font-size:23px}
 #wpbody-content>.wrap form[method="get"]{display:flex;gap:8px;align-items:center}
 #wpbody-content>.wrap form[method="get"] input[type="search"]{min-width:0!important;width:100%;height:40px}
 #wpbody-content>.wrap table.widefat{display:block;border:0;box-shadow:none;background:transparent}
 #wpbody-content>.wrap table.widefat thead{display:none}
 #wpbody-content>.wrap table.widefat tbody{display:grid;gap:12px}
 #wpbody-content>.wrap table.widefat tr{display:block;background:#fff;border:1px solid #dcdcde;border-radius:14px;box-shadow:0 2px 10px rgba(0,0,0,.04);overflow:hidden}
 #wpbody-content>.wrap table.widefat td{display:block;border:0!important;border-bottom:1px solid #f0f0f1!important;padding:10px 13px!important}
 #wpbody-content>.wrap table.widefat td:last-child{border-bottom:0!important;padding-top:12px!important}
 #wpbody-content>.wrap table.widefat td:before{display:block;color:#646970;font-size:11px;font-weight:600;margin-bottom:3px}
 #wpbody-content>.wrap table.widefat td:nth-child(1):before{content:'حساب'}
 #wpbody-content>.wrap table.widefat td:nth-child(2):before{content:'هویت و تماس'}
 #wpbody-content>.wrap table.widefat td:nth-child(3):before{content:'سایت'}
 #wpbody-content>.wrap table.widefat td:nth-child(4):before{content:'اشتراک'}
 #wpbody-content>.wrap table.widefat td:nth-child(5):before{content:'Session'}
 #wpbody-content>.wrap table.widefat td:nth-child(6):before{content:'عملیات'}
 #wpbody-content>.wrap table.widefat td[colspan]{display:block;text-align:center}
 #wpbody-content>.wrap details>div{max-width:none!important;padding:4px!important}
 #wpbody-content>.wrap details form{margin:8px 0!important}
}
@media (max-width:480px){
 #wpbody-content>.wrap form[method="get"]{flex-direction:column;align-items:stretch}
 #wpbody-content>.wrap form[method="get"] .button{width:100%;height:40px}
}
</style>
<?php });
add_action('init',static function():void{if(!wp_next_scheduled('woogit_backend_cleanup')) wp_schedule_event(time()+300,'daily','woogit_backend_cleanup');});
add_action('woogit_backend_cleanup',static function():void{global $wpdb;$now=current_time('mysql',true);$old7=gmdate('Y-m-d H:i:s',time()-7*DAY_IN_SECONDS);$old2=gmdate('Y-m-d H:i:s',time()-2*DAY_IN_SECONDS);$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}woogit_sessions WHERE expires_at < %s",$now));$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}woogit_web_sessions WHERE expires_at < %s",$now));$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}woogit_idempotency WHERE updated_at < %s AND state IN ('succeeded','failed')",$old7));$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}woogit_operations WHERE expires_at IS NOT NULL AND expires_at < %s AND status IN ('succeeded','failed')",$now));$rateLimitTable=$wpdb->prefix.'woogit_rate_limits';$wpdb->query($wpdb->prepare("DELETE FROM {$rateLimitTable} WHERE window_start < %s LIMIT 1000",$old2));});
add_action('rest_api_init',static function():void{(new \WooGit\Backend\RestController())->register();(new \WooGit\Backend\AnnouncementController())->register();(new \WooGit\Backend\BillingController())->register();(new \WooGit\Backend\WebAuthController())->register();});