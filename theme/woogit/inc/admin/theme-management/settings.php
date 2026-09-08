<?php
if (!defined('ABSPATH')) exit;

function woogit_register_theme_settings() {
    register_setting('woogit_theme', 'woogit_theme_options', ['sanitize_callback' => 'woogit_sanitize_theme_options']);
}

function woogit_theme_json_keys() { return ['faq_json','features_json','steps_json','social_links_json']; }
function woogit_theme_image_keys() { return ['logo_id','alternate_logo_id','favicon_id','og_image_id','hero_image_id','hero_mobile_image_id','enamad_image_id']; }
function woogit_theme_url_keys() { return ['hero_cta_url','hero_secondary_url','enamad_verification_url']; }
function woogit_theme_bool_keys() { return ['enamad_enabled','section_features_enabled','section_how_enabled','section_pricing_enabled','section_faq_enabled','section_app_enabled']; }

function woogit_theme_clean_items($key, $items) {
    if (!is_array($items)) return [];
    $out = [];
    foreach ($items as $item) {
        if (!is_array($item)) continue;
        if ($key === 'features_json') {
            $title = sanitize_text_field($item['title'] ?? '');
            $description = sanitize_textarea_field($item['description'] ?? ($item['text'] ?? ''));
            if ($title === '' && $description === '') continue;
            $out[] = ['title'=>$title,'description'=>$description,'icon'=>sanitize_text_field($item['icon'] ?? ''),'image_id'=>absint($item['image_id'] ?? 0),'enabled'=>!empty($item['enabled'])];
        } elseif ($key === 'steps_json') {
            $title = sanitize_text_field($item['title'] ?? '');
            $description = sanitize_textarea_field($item['description'] ?? ($item['text'] ?? ''));
            if ($title === '' && $description === '') continue;
            $out[] = ['title'=>$title,'description'=>$description,'image_id'=>absint($item['image_id'] ?? 0),'url'=>esc_url_raw($item['url'] ?? ''),'enabled'=>!empty($item['enabled'])];
        } elseif ($key === 'faq_json') {
            $question = sanitize_text_field($item['question'] ?? '');
            $answer = sanitize_textarea_field($item['answer'] ?? '');
            if ($question === '' && $answer === '') continue;
            $out[] = ['question'=>$question,'answer'=>$answer,'enabled'=>array_key_exists('enabled',$item) ? !empty($item['enabled']) : true];
        } else {
            $label = sanitize_text_field($item['label'] ?? '');
            $url = esc_url_raw($item['url'] ?? '');
            if ($label === '' && $url === '') continue;
            $out[] = ['label'=>$label,'url'=>$url,'enabled'=>array_key_exists('enabled',$item) ? !empty($item['enabled']) : true];
        }
    }
    return $out;
}

function woogit_sanitize_theme_options($input) {
    $input = is_array($input) ? $input : [];
    $out = woogit_theme_options();
    foreach ($input as $key => $value) {
        $key = sanitize_key($key);
        if (in_array($key, woogit_theme_bool_keys(), true) || strpos($key, '_enabled') !== false) { $out[$key] = !empty($value) ? '1' : '0'; continue; }
        if (in_array($key, woogit_theme_json_keys(), true)) {
            $decoded = json_decode(wp_unslash((string)$value), true);
            $out[$key] = wp_json_encode(woogit_theme_clean_items($key, is_array($decoded) ? $decoded : []), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
            continue;
        }
        if (in_array($key, woogit_theme_image_keys(), true)) { $out[$key] = absint($value); continue; }
        if (in_array($key, woogit_theme_url_keys(), true)) { $out[$key] = esc_url_raw($value); continue; }
        if ($key === 'support_email') { $out[$key] = sanitize_email($value); continue; }
        if ($key === 'enamad_code') { $out[$key] = wp_kses_post($value); continue; }
        if ($key === 'enamad_placement') { $out[$key] = in_array($value,['footer','contact'],true) ? $value : 'footer'; continue; }
        $out[$key] = sanitize_textarea_field($value);
    }
    return $out;
}

function woogit_theme_option($key, $default = '') { $options=woogit_theme_options(); return array_key_exists($key,$options) ? $options[$key] : $default; }
function woogit_theme_media_data($id, $size='medium') { $id=absint($id); return ['id'=>$id,'url'=>$id?woogit_theme_image($id,$size):'']; }
