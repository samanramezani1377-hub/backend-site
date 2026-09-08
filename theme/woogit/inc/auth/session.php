<?php
if (!defined('ABSPATH')) exit;
function woogit_web_session() {
  if (isset($_COOKIE['woogit_web_session'])) return sanitize_text_field(wp_unslash($_COOKIE['woogit_web_session']));
  return '';
}
function woogit_set_web_session($token, $expires=3600) {
  if (!$token) return false;
  return setcookie('woogit_web_session', $token, ['expires'=>time()+absint($expires),'path'=>COOKIEPATH ?: '/','secure'=>is_ssl(),'httponly'=>true,'samesite'=>'Lax']);
}
function woogit_clear_web_session() { setcookie('woogit_web_session','',['expires'=>time()-3600,'path'=>COOKIEPATH ?: '/','secure'=>is_ssl(),'httponly'=>true,'samesite'=>'Lax']); }
function woogit_current_user() { static $cached=null; if ($cached!==null) return $cached; $cached=woogit_api_get('web/me'); return $cached; }
function woogit_logged_in() { $u=woogit_current_user(); return !is_wp_error($u) && !empty($u) && empty($u['error']); }
