<?php
if (!defined('ABSPATH')) exit;
function woogit_theme_options() { $v=get_option('woogit_theme_options',[]); return is_array($v)?$v:[]; }
function woogit_view($key, $default = '') { $options=woogit_theme_options(); if(array_key_exists($key,$options)&&is_string($options[$key]))return $options[$key]; $v=get_theme_mod($key,$default); return is_string($v)?$v:$default; }
function woogit_asset($path) { return WOOGIT_THEME_URI . '/assets/' . ltrim($path, '/'); }
function woogit_page_url($slug) { $p=get_page_by_path($slug); return $p?get_permalink($p):home_url('/'.$slug.'/'); }
function woogit_render_status($type='loading',$message='') { get_template_part('template-parts/states/status',null,['type'=>$type,'message'=>$message]); }
function woogit_safe_text($value,$fallback='') { return is_scalar($value)?esc_html((string)$value):esc_html($fallback); }
function woogit_is_portal() { return is_page(['portal','subscription','billing','payments','connected-site','account-security']); }
function woogit_theme_image($id,$size='large') { $id=absint($id); if(!$id)return ''; $url=wp_get_attachment_image_url($id,$size); return $url?esc_url($url):''; }
function woogit_theme_items($key,$default=[]) { $options=woogit_theme_options(); $value=$options[$key]??$default; if(is_string($value)){$decoded=json_decode($value,true);return is_array($decoded)?$decoded:$default;} return is_array($value)?$value:$default; }
