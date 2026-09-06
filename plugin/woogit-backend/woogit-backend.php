<?php
/**
 * Plugin Name: WooGit Backend
 * Description: WooGit V1 backend and controlled proxy kernel.
 * Version: 0.1.0
 * Requires at least: 6.4
 * Requires PHP: 8.1
 */

defined('ABSPATH') || exit;

define('WOOGIT_BACKEND_VERSION', '0.1.0');
define('WOOGIT_BACKEND_FILE', __FILE__);
define('WOOGIT_BACKEND_DIR', plugin_dir_path(__FILE__));

require_once WOOGIT_BACKEND_DIR . 'src/Database.php';
require_once WOOGIT_BACKEND_DIR . 'src/SessionService.php';
require_once WOOGIT_BACKEND_DIR . 'src/ProxyPolicy.php';
require_once WOOGIT_BACKEND_DIR . 'src/WooCommerceProxy.php';
require_once WOOGIT_BACKEND_DIR . 'src/RestController.php';

register_activation_hook(__FILE__, ['WooGit\\Backend\\Database', 'install']);

add_action('rest_api_init', static function (): void {
    (new WooGit\\Backend\\RestController())->register();
});
