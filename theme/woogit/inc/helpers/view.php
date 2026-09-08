<?php
if (!defined('ABSPATH')) exit;
function woogit_view($key, $default = '') { $v = get_theme_mod($key, $default); return is_string($v) ? $v : $default; }
function woogit_asset($path) { return WOOGIT_THEME_URI . '/assets/' . ltrim($path, '/'); }
function woogit_page_url($slug) { $p = get_page_by_path($slug); return $p ? get_permalink($p) : home_url('/'.$slug.'/'); }
function woogit_render_status($type='loading', $message='') { get_template_part('template-parts/states/status', null, ['type'=>$type,'message'=>$message]); }
function woogit_safe_text($value, $fallback='') { return is_scalar($value) ? esc_html((string)$value) : esc_html($fallback); }
function woogit_is_portal() { return is_page(['portal','subscription','billing','payments','connected-site','account-security']); }
