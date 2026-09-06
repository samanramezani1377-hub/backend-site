<?php
/**
 * Plugin Name: WooGit Backend
 * Description: WooGit V1 secure transparent gateway/proxy.
 * Version: 0.2.3
 * Requires at least: 6.4
 * Requires PHP: 8.1
 */

defined('ABSPATH') || exit;

define('WOOGIT_BACKEND_VERSION','0.2.3');
define('WOOGIT_BACKEND_FILE',__FILE__);
define('WOOGIT_BACKEND_DIR',plugin_dir_path(__FILE__));

foreach(['Database','AccountService','SiteService','EntitlementService','SessionService','IdempotencyService','OperationService','ProxyPolicy','WooCommerceProxy','RateLimitService','VersionGate','BillingService','RestController','BillingController'] as $file) require_once WOOGIT_BACKEND_DIR.'src/'.$file.'.php';

register_activation_hook(__FILE__,['WooGit\\Backend\\Database','install']);
add_action('plugins_loaded',static function():void{
    if((string)get_option('woogit_backend_db_version','') !== WOOGIT_BACKEND_VERSION) \WooGit\Backend\Database::install();
    (new \WooGit\Backend\BillingService())->registerHooks();
});
add_action('init',static function():void{
    if(!wp_next_scheduled('woogit_backend_cleanup')) wp_schedule_event(time()+300,'daily','woogit_backend_cleanup');
});
add_action('woogit_backend_cleanup',static function():void{
    global $wpdb;$now=current_time('mysql',true);$old7=gmdate('Y-m-d H:i:s',time()-7*DAY_IN_SECONDS);$old2=gmdate('Y-m-d H:i:s',time()-2*DAY_IN_SECONDS);
    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}woogit_sessions WHERE expires_at < %s",$now));
    // Never expire pending/unknown idempotency records: doing so could allow a later retry
    // of an indeterminate mutation to create a duplicate upstream resource.
    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}woogit_idempotency WHERE updated_at < %s AND state IN ('succeeded','failed')",$old7));
    // Unknown operations remain authoritative until an explicit/domain-specific reconciliation
    // exists; generic automatic retry is intentionally forbidden.
    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}woogit_operations WHERE expires_at IS NOT NULL AND expires_at < %s AND status IN ('succeeded','failed')",$now));
    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}woogit_rate_limits WHERE updated_at < %s",$old2));
});
add_action('rest_api_init',static function():void{
    (new WooGit\\Backend\\RestController())->register();
    (new WooGit\\Backend\\BillingController())->register();
});
