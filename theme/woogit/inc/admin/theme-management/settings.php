<?php
if (!defined('ABSPATH')) exit;

function woogit_register_theme_settings() {
  register_setting('woogit_theme','woogit_theme_options',['sanitize_callback'=>'woogit_sanitize_theme_options']);
  add_settings_section('woogit_general','عمومی','__return_false','woogit-theme');
  add_settings_section('woogit_home','صفحه اصلی','__return_false','woogit-theme');
  add_settings_section('woogit_trust','اعتماد و Footer','__return_false','woogit-theme');
  $fields=[
    'tagline'=>'Tagline','support_email'=>'Support email','hero_title'=>'Hero title','hero_text'=>'Hero text',
    'hero_cta_text'=>'Hero CTA text','hero_cta_url'=>'Hero CTA URL','hero_secondary_text'=>'Hero secondary CTA','hero_secondary_url'=>'Hero secondary URL',
    'hero_image_id'=>'Hero image media ID','hero_mobile_image_id'=>'Mobile hero image media ID',
    'faq_json'=>'FAQ JSON','features_json'=>'Features JSON','steps_json'=>'How It Works JSON',
    'social_links_json'=>'Social links JSON','enamad_enabled'=>'Enamad enabled','enamad_id'=>'Enamad ID','enamad_code'=>'Enamad code','enamad_verification_url'=>'Enamad verification URL','enamad_image_id'=>'Enamad image media ID','enamad_alt'=>'Enamad alt text','enamad_placement'=>'Enamad placement','footer_text'=>'Footer text'
  ];
  foreach($fields as $key=>$label){$section=in_array($key,['tagline','support_email'],true)?'woogit_general':(in_array($key,['hero_title','hero_text','hero_cta_text','hero_cta_url','hero_secondary_text','hero_secondary_url','hero_image_id','hero_mobile_image_id','features_json','steps_json'],true)?'woogit_home':'woogit_trust');add_settings_field($key,$label,'woogit_setting_field','woogit-theme',$section,['key'=>$key]);}
}
function woogit_sanitize_theme_options($input) {
  $out=[];$input=(array)$input;
  foreach($input as $k=>$v){$k=sanitize_key($k);if(in_array($k,['faq_json','features_json','steps_json','social_links_json'],true)){ $decoded=json_decode(wp_unslash((string)$v),true);$out[$k]=is_array($decoded)?wp_json_encode($decoded,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES):'[]'; }elseif($k==='enamad_code')$out[$k]=wp_kses_post($v);elseif(in_array($k,['hero_image_id','hero_mobile_image_id','enamad_image_id'],true))$out[$k]=absint($v);elseif($k==='enamad_enabled')$out[$k]=!empty($v)?'1':'0';elseif(in_array($k,['hero_cta_url','hero_secondary_url','enamad_verification_url'],true))$out[$k]=esc_url_raw($v);else $out[$k]=sanitize_text_field($v);}
  return $out;
}
function woogit_setting_field($args) {
  $o=woogit_theme_options();$k=$args['key'];$value=$o[$k]??'';
  if($k==='enamad_enabled'){printf('<label><input type="checkbox" name="woogit_theme_options[%1$s]" value="1" %2$s> فعال</label>',$k,checked($value,'1',false));return;}
  if(in_array($k,['faq_json','features_json','steps_json','social_links_json'],true)){printf('<textarea class="large-text code" rows="10" name="woogit_theme_options[%1$s]" placeholder="%2$s">%3$s</textarea>',$k,esc_attr($k==='features_json'?'[{"title":"عنوان","description":"توضیح","icon":"01","enabled":true}]':'[]'),esc_textarea($value));return;}
  printf('<input class="regular-text" name="woogit_theme_options[%1$s]" value="%2$s">',$k,esc_attr($value));
}
