<?php
if (!defined('ABSPATH')) exit;
function woogit_register_theme_settings() {
  register_setting('woogit_theme','woogit_theme_options',['sanitize_callback'=>'woogit_sanitize_theme_options']);
  add_settings_section('woogit_brand','Brand & Content','__return_false','woogit-theme');
  $fields=['tagline'=>'Tagline','hero_title'=>'Hero title','hero_text'=>'Hero text','support_email'=>'Support email','enamad_id'=>'Enamad ID','enamad_code'=>'Enamad code','enamad_verification_url'=>'Enamad verification URL','enamad_alt'=>'Enamad alt text'];
  foreach($fields as $key=>$label) add_settings_field($key,$label,'woogit_setting_field','woogit-theme','woogit_brand',['key'=>$key]);
}
function woogit_sanitize_theme_options($input) {
  $out=[]; foreach((array)$input as $k=>$v) $out[sanitize_key($k)] = in_array($k,['enamad_code'],true) ? wp_kses_post($v) : sanitize_text_field($v); return $out;
}
function woogit_theme_options() { $v=get_option('woogit_theme_options',[]); return is_array($v)?$v:[]; }
function woogit_setting_field($args) { $o=woogit_theme_options(); $k=$args['key']; printf('<input class="regular-text" name="woogit_theme_options[%1$s]" value="%2$s">',$k,esc_attr($o[$k]??'')); }
