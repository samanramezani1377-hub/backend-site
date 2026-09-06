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

foreach(['Database','AccountService','SiteService','EntitlementService','SessionService','IdempotencyService','OperationService','ProxyPolicy','WooCommerceProxy','RateLimitService','VersionGate','RestController'] as $file) require_once WOOGIT_BACKEND_DIR.'src/'.$file.'.php';

register_activation_hook(__FILE__,['WooGit\\Backend\\Database','install']);
add_action('plugins_loaded',static function():void{
    if((string)get_option('woogit_backend_db_version','') !== WOOGIT_BACKEND_VERSION) \WooGit\Backend\Database::install();
});
add_action('init',static function():void{
    if(!wp_next_scheduled('woogit_backend_cleanup')) wp_schedule_event(time()+300,'daily','woogit_backend_cleanup');
});
add_action('woogit_backend_cleanup',static function():void{
    global $wpdb;$now=current_time('mysql',true);
    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}woogit_sessions WHERE expires_at < %s",$now));
    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}woogit_idempotency WHERE updated_at < %s",gmdate('Y-m-d H:i:s',time()-7*DAY_IN_SECONDS)));
    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}woogit_operations WHERE expires_at IS NOT NULL AND expires_at < %s AND status IN ('succeeded','failed')",$now));
    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}woogit_rate_limits WHERE updated_at < %s",gmdate('Y-m-d H:i:s',time()-2*DAY_IN_SECONDS)));
});
add_action('rest_api_init',static function():void{(new WooGit\\Backend\\RestController())->register();});
