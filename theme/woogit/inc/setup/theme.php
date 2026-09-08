<?php
if (!defined('ABSPATH')) exit;

function woogit_theme_setup() {
  load_theme_textdomain('woogit', WOOGIT_THEME_DIR.'/languages');
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  add_theme_support('html5',['search-form','comment-form','comment-list','gallery','caption','style','script']);
  add_theme_support('custom-logo',['height'=>80,'width'=>240,'flex-height'=>true,'flex-width'=>true]);
  register_nav_menus(['primary'=>'Primary Navigation','footer'=>'Footer Navigation']);
}

function woogit_asset_version($relative) {
  $path=WOOGIT_THEME_DIR.$relative;
  return file_exists($path)?(string)filemtime($path):WOOGIT_THEME_VERSION;
}

function woogit_enqueue_assets() {
  wp_enqueue_style('woogit-foundation',WOOGIT_THEME_URI.'/assets/css/foundation.css',[],woogit_asset_version('/assets/css/foundation.css'));
  wp_enqueue_style('woogit-components',WOOGIT_THEME_URI.'/assets/css/components.css',['woogit-foundation'],woogit_asset_version('/assets/css/components.css'));
  wp_enqueue_style('woogit-pages',WOOGIT_THEME_URI.'/assets/css/pages.css',['woogit-components'],woogit_asset_version('/assets/css/pages.css'));
  wp_enqueue_style('woogit-responsive',WOOGIT_THEME_URI.'/assets/css/responsive.css',['woogit-pages'],woogit_asset_version('/assets/css/responsive.css'));
  wp_enqueue_script('woogit-core',WOOGIT_THEME_URI.'/assets/js/core.js',[],woogit_asset_version('/assets/js/core.js'),true);
  wp_enqueue_script('woogit-navigation',WOOGIT_THEME_URI.'/assets/js/navigation.js',['woogit-core'],woogit_asset_version('/assets/js/navigation.js'),true);
  $slug=is_page()?get_post_field('post_name',get_queried_object_id()):'';
  if(in_array($slug,['login','register'],true)) wp_enqueue_script('woogit-auth',WOOGIT_THEME_URI.'/assets/js/auth.js',['woogit-core'],woogit_asset_version('/assets/js/auth.js'),true);
  if(woogit_is_portal()||$slug==='pricing') wp_enqueue_script('woogit-portal',WOOGIT_THEME_URI.'/assets/js/portal.js',['woogit-core'],woogit_asset_version('/assets/js/portal.js'),true);
  wp_localize_script('woogit-core','WooGitTheme',[
    'restUrl'=>esc_url_raw(rest_url('woogit/v1/')),
    'nonce'=>wp_create_nonce('wp_rest'),
    'authAjax'=>admin_url('admin-ajax.php'),
    'authNonce'=>wp_create_nonce('woogit_web_auth'),
    'portalNonce'=>wp_create_nonce('woogit_portal'),
    'home'=>home_url('/'),
  ]);
}
function woogit_print_head_meta(){echo '<meta name="theme-color" content="#080a0f">';}
