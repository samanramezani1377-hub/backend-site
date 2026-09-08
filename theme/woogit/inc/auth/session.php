<?php
if (!defined('ABSPATH')) exit;
function woogit_web_session(){return isset($_COOKIE['woogit_web_session'])?sanitize_text_field(wp_unslash($_COOKIE['woogit_web_session'])):'';}
function woogit_set_web_session($token,$expires=3600){if(!$token)return false;$secure=is_ssl()||(!empty($_SERVER['HTTPS'])&&strtolower((string)$_SERVER['HTTPS'])!=='off');return setcookie('woogit_web_session',$token,['expires'=>time()+max(1,absint($expires)),'path'=>COOKIEPATH?:'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Lax']);}
function woogit_clear_web_session(){ $secure=is_ssl()||(!empty($_SERVER['HTTPS'])&&strtolower((string)$_SERVER['HTTPS'])!=='off');return setcookie('woogit_web_session','',['expires'=>time()-3600,'path'=>COOKIEPATH?:'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Lax']); }
function woogit_current_user(){static $cached=null;if($cached!==null)return $cached;$session=woogit_web_session();if(!$session)return $cached=new WP_Error('auth_required','Authentication required',['status'=>401]);$cached=woogit_api_get('web/me');if(is_wp_error($cached)&&in_array((int)($cached->get_error_data()['status']??0),[401,403],true))woogit_clear_web_session();return $cached;}
function woogit_logged_in(){return !is_wp_error($u=woogit_current_user())&&!empty($u)&&empty($u['error']);}
