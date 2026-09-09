<?php
/**
 * Plugin Name: WooGit Backend
 * Description: WooGit V1 secure transparent gateway/proxy.
 * Version: 0.3.7
 * Requires at least: 6.4
 * Requires PHP: 8.1
 */

defined('ABSPATH') || exit;

define('WOOGIT_BACKEND_VERSION','0.3.7');
define('WOOGIT_BACKEND_FILE',__FILE__);
define('WOOGIT_BACKEND_DIR',plugin_dir_path(__FILE__));
foreach(['Database','IdentityService','AccountService','SiteService','EntitlementService','SessionService','WebSessionService','IdempotencyService','OperationService','ProxyPolicy','WooCommerceProxy','RateLimitService','VersionGate','VersionAdmin','AnnouncementService','AnnouncementAdmin','AnnouncementController','RequirementService','BillingService','RestController','BillingController','WebAuthController','AccountAdmin'] as $file) require_once WOOGIT_BACKEND_DIR.'src/'.$file.'.php';
register_activation_hook(__FILE__,['WooGit\\Backend\\Database','install']);
add_action('plugins_loaded',static function():void{$from=(string)get_option('woogit_backend_db_version','');if($from !== WOOGIT_BACKEND_VERSION) \WooGit\Backend\Database::install($from);(new \WooGit\Backend\BillingService())->registerHooks();});
add_action('admin_menu',static function():void{(new \WooGit\Backend\VersionAdmin())->register();(new \WooGit\Backend\AnnouncementAdmin())->register();(new \WooGit\Backend\AccountAdmin())->register();});
add_action('init',static function():void{if(!wp_next_scheduled('woogit_backend_cleanup')) wp_schedule_event(time()+300,'daily','woogit_backend_cleanup');});
add_action('woogit_backend_cleanup',static function():void{global $wpdb;$now=current_time('mysql',true);$old7=gmdate('Y-m-d H:i:s',time()-7*DAY_IN_SECONDS);$old2=gmdate('Y-m-d H:i:s',time()-2*DAY_IN_SECONDS);$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}woogit_sessions WHERE expires_at < %s",$now));$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}woogit_web_sessions WHERE expires_at < %s",$now));$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}woogit_idempotency WHERE updated_at < %s AND state IN ('succeeded','failed')",$old7));$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}woogit_operations WHERE expires_at IS NOT NULL AND expires_at < %s AND status IN ('succeeded','failed')",$now));$rateLimitTable=$wpdb->prefix.'woogit_rate_limits';$wpdb->query($wpdb->prepare("DELETE FROM {$rateLimitTable} WHERE window_start < %s LIMIT 1000",$old2));});
add_action('rest_api_init',static function():void{(new \WooGit\Backend\RestController())->register();(new \WooGit\Backend\AnnouncementController())->register();(new \WooGit\Backend\BillingController())->register();(new \WooGit\Backend\WebAuthController())->register();});
