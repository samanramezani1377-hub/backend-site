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
add_action('wp_ajax_nopriv_woogit_web_auth', 'woogit_ajax_web_auth');
add_action('wp_ajax_woogit_web_auth', 'woogit_ajax_web_auth');
function woogit_ajax_web_auth() {
  if (!check_ajax_referer('woogit_web_auth', 'nonce', false)) wp_send_json_error(['code'=>'invalid_nonce'], 403);
  $type=sanitize_key($_POST['type']??'');
  $allowed=['login','register']; if(!in_array($type,$allowed,true)) wp_send_json_error(['code'=>'invalid_request'],400);
  $keys=['site_url','web_password']; if($type==='register') $keys=array_merge($keys,['wp_username','wp_application_password','consumer_key','consumer_secret']);
  $body=[]; foreach($keys as $key){$body[$key]=isset($_POST[$key])?sanitize_text_field(wp_unslash($_POST[$key])):'';}
  foreach($keys as $key){if($body[$key]==='') wp_send_json_error(['code'=>'missing_field'],400);}
  $result=$type==='login'?woogit_api_post('web/login',$body):woogit_api_post('account/web-bootstrap',$body);
  if(is_wp_error($result)){ $status=(int)($result->get_error_data()['status']??500); wp_send_json_error(['code'=>$result->get_error_code(),'message'=>'امکان انجام عملیات وجود ندارد.'], $status>=400&&$status<600?$status:500); }
  $token=$result['web_session']??($result['session']??'');
  if($token) woogit_set_web_session($token, isset($result['expires_in'])?(int)$result['expires_in']:3600);
  wp_send_json_success(['authenticated'=>(bool)$token]);
}
