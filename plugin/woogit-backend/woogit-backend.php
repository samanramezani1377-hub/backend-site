<?php
/**
 * Plugin Name: WooGit Backend
 * Description: WooGit V1 secure transparent gateway/proxy.
 * Version: 0.2.2
 * Requires at least: 6.4
 * Requires PHP: 8.1
 */

defined('ABSPATH') || exit;

define('WOOGIT_BACKEND_VERSION','0.2.2');
define('WOOGIT_BACKEND_FILE',__FILE__);
define('WOOGIT_BACKEND_DIR',plugin_dir_path(__FILE__));

foreach(['Database','AccountService','SiteService','EntitlementService','SessionService','IdempotencyService','OperationService','ProxyPolicy','WooCommerceProxy','RestController'] as $file) require_once WOOGIT_BACKEND_DIR.'src/'.$file.'.php';

register_activation_hook(__FILE__,['WooGit\\Backend\\Database','install']);
add_action('plugins_loaded',static function():void{
    if((string)get_option('woogit_backend_db_version','') !== WOOGIT_BACKEND_VERSION) \WooGit\Backend\Database::install();
});
add_action('rest_api_init',static function():void{(new WooGit\\Backend\\RestController())->register();});
