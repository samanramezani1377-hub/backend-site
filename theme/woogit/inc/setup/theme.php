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
function woogit_enqueue_assets() {
  wp_enqueue_style('woogit-foundation',WOOGIT_THEME_URI.'/assets/css/foundation.css',[],WOOGIT_THEME_VERSION);
  wp_enqueue_style('woogit-components',WOOGIT_THEME_URI.'/assets/css/components.css',['woogit-foundation'],WOOGIT_THEME_VERSION);
  wp_enqueue_style('woogit-pages',WOOGIT_THEME_URI.'/assets/css/pages.css',['woogit-components'],WOOGIT_THEME_VERSION);
  wp_enqueue_style('woogit-responsive',WOOGIT_THEME_URI.'/assets/css/responsive.css',['woogit-pages'],WOOGIT_THEME_VERSION);
  wp_enqueue_script('woogit-core',WOOGIT_THEME_URI.'/assets/js/core.js',[],WOOGIT_THEME_VERSION,true);
  wp_enqueue_script('woogit-navigation',WOOGIT_THEME_URI.'/assets/js/navigation.js',['woogit-core'],WOOGIT_THEME_VERSION,true);
  wp_enqueue_script('woogit-auth',WOOGIT_THEME_URI.'/assets/js/auth.js',['woogit-core'],WOOGIT_THEME_VERSION,true);
  wp_enqueue_script('woogit-portal',WOOGIT_THEME_URI.'/assets/js/portal.js',['woogit-core'],WOOGIT_THEME_VERSION,true);
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
