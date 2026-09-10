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
require_once WOOGIT_THEME_DIR . '/inc/commerce/adapter.php';
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
add_action('wp_ajax_nopriv_woogit_portal_action', 'woogit_ajax_portal_action');
add_action('wp_ajax_woogit_portal_action', 'woogit_ajax_portal_action');

function woogit_ajax_web_auth() {
  if (!check_ajax_referer('woogit_web_auth', 'nonce', false)) wp_send_json_error(['code'=>'invalid_nonce'], 403);
  $type=sanitize_key($_POST['type']??'');
  if(!in_array($type,['login','register'],true)) wp_send_json_error(['code'=>'invalid_request'],400);
  $keys=['site_url'];
  if($type==='login') $keys[]='web_password';
  if($type==='register') $keys=array_merge($keys,['wp_username','wp_application_password','consumer_key','consumer_secret']);
  $body=[];
  foreach($keys as $key) $body[$key]=isset($_POST[$key])?(string)wp_unslash($_POST[$key]):'';
  foreach($keys as $key) if($body[$key]==='') wp_send_json_error(['code'=>'missing_field'],400);
  if($type==='register'){
    $idempotency=sanitize_text_field(wp_unslash($_POST['idempotency_key']??''));
    if($idempotency===''||!preg_match('/^[A-Za-z0-9._:-]{1,190}$/',$idempotency)) wp_send_json_error(['code'=>'invalid_idempotency_key'],400);
    $result=woogit_api_post('account/web-bootstrap',$body,['Idempotency-Key'=>$idempotency]);
  }else{
    $result=woogit_api_post('web/login',['site_url'=>$body['site_url'],'password'=>$body['web_password']]);
  }
  if(is_wp_error($result)){
    $status=(int)($result->get_error_data()['status']??500);
    wp_send_json_error(['code'=>$result->get_error_code(),'message'=>'امکان انجام عملیات وجود ندارد.'],$status>=400&&$status<600?$status:500);
  }
  $token=$result['web_session']??($result['session']??'');
  if($token){
    $expires_at=(int)($result['expires_at']??0);
    $ttl=$expires_at>time()?$expires_at-time():3600;
    woogit_set_web_session($token,$ttl);
  }
  wp_send_json_success(['authenticated'=>(bool)$token,'web_password_configured'=>!empty($result['web_password_configured'])]);
}

function woogit_ajax_portal_action() {
  if (!check_ajax_referer('woogit_portal', 'nonce', false)) wp_send_json_error(['code'=>'invalid_nonce'], 403);
  if (!woogit_logged_in()) wp_send_json_error(['code'=>'auth_required'], 401);
  $action=sanitize_key($_POST['portal_action']??'');
  if($action==='logout'){
    $result=woogit_api_post('web/logout');
    woogit_clear_web_session();
    if(is_wp_error($result)) wp_send_json_success(['logged_out'=>true]);
    wp_send_json_success(['logged_out'=>true]);
  }
  $body=[];
  if($action==='contact_email'){
    $body['email']=isset($_POST['email'])?sanitize_email(wp_unslash($_POST['email'])):'';
    if($body['email']!==''&&!is_email($body['email'])) wp_send_json_error(['code'=>'invalid_contact_email'],400);
    $result=woogit_api_post('web/account/contact-email',$body);
  }elseif($action==='setup_web_credentials'){
    $body['password']=isset($_POST['password'])?(string)wp_unslash($_POST['password']):'';
    $body['password_confirmation']=isset($_POST['password_confirmation'])?(string)wp_unslash($_POST['password_confirmation']):'';
    $result=woogit_api_post('web/account/setup-password',$body);
  }elseif($action==='password'){
    $body['current_password']=isset($_POST['current_password'])?(string)wp_unslash($_POST['current_password']):'';
    $body['password']=isset($_POST['password'])?(string)wp_unslash($_POST['password']):'';
    $body['password_confirmation']=isset($_POST['password_confirmation'])?(string)wp_unslash($_POST['password_confirmation']):'';
    $result=woogit_api_post('web/account/password',$body);
  }else wp_send_json_error(['code'=>'invalid_request'],400);
  if(is_wp_error($result)){
    $status=(int)($result->get_error_data()['status']??500);
    wp_send_json_error(['code'=>$result->get_error_code(),'message'=>'امکان انجام عملیات وجود ندارد.'],$status>=400&&$status<600?$status:500);
  }
  if($action==='password') woogit_clear_web_session();
  wp_send_json_success($result);
}
