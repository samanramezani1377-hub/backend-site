<?php
if (!defined('ABSPATH')) exit;

define('WOOGIT_THEME_VERSION', '1.0.0');
define('WOOGIT_THEME_DIR', get_template_directory());
define('WOOGIT_THEME_URI', get_template_directory_uri());

require_once WOOGIT_THEME_DIR . '/inc/setup/theme.php';
require_once WOOGIT_THEME_DIR . '/inc/helpers/view.php';
require_once WOOGIT_THEME_DIR . '/inc/api/client.php';
require_once WOOGIT_THEME_DIR . '/inc/auth/session.php';
require_once WOOGIT_THEME_DIR . '/inc/portal/data.php';
require_once WOOGIT_THEME_DIR . '/inc/admin/theme-management/settings.php';
require_once WOOGIT_THEME_DIR . '/inc/admin/theme-management/meta.php';
require_once WOOGIT_THEME_DIR . '/inc/admin/theme-management/admin.php';

add_action('after_setup_theme', 'woogit_theme_setup');
add_action('wp_enqueue_scripts', 'woogit_enqueue_assets');
add_action('wp_head', 'woogit_print_head_meta', 1);
add_action('admin_menu', 'woogit_theme_management_menu');
add_action('admin_init', 'woogit_register_theme_settings');
add_action('wp_ajax_woogit_save_theme_setting', 'woogit_ajax_save_theme_setting');
