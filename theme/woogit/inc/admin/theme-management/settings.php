<?php
if (!defined('ABSPATH')) exit;

function woogit_register_theme_settings() {
    register_setting('woogit_theme', 'woogit_theme_options', [
        'sanitize_callback' => 'woogit_sanitize_theme_options',
    ]);
}

function woogit_theme_json_keys() {
    return ['faq_json', 'features_json', 'steps_json', 'social_links_json'];
}

function woogit_theme_image_keys() {
    return ['logo_id', 'alternate_logo_id', 'favicon_id', 'og_image_id', 'hero_image_id', 'hero_mobile_image_id', 'enamad_image_id'];
}

function woogit_theme_url_keys() {
    return ['hero_cta_url', 'hero_secondary_url', 'enamad_verification_url'];
}

function woogit_sanitize_theme_options($input) {
    $input = is_array($input) ? $input : [];
    $out = [];
    foreach ($input as $key => $value) {
        $key = sanitize_key($key);
        if ($key === 'enamad_enabled' || strpos($key, '_enabled') !== false) {
            $out[$key] = !empty($value) ? '1' : '0';
            continue;
        }
        if (in_array($key, woogit_theme_json_keys(), true)) {
            $decoded = json_decode(wp_unslash((string) $value), true);
            $out[$key] = is_array($decoded) ? wp_json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '[]';
            continue;
        }
        if (in_array($key, woogit_theme_image_keys(), true)) {
            $out[$key] = absint($value);
            continue;
        }
        if (in_array($key, woogit_theme_url_keys(), true)) {
            $out[$key] = esc_url_raw($value);
            continue;
        }
        if ($key === 'enamad_code') {
            $out[$key] = wp_kses_post($value);
            continue;
        }
        if ($key === 'enamad_placement') {
            $out[$key] = in_array($value, ['footer', 'contact'], true) ? $value : 'footer';
            continue;
        }
        $out[$key] = sanitize_textarea_field($value);
    }
    return $out;
}

function woogit_theme_option($key, $default = '') {
    $options = woogit_theme_options();
    return array_key_exists($key, $options) ? $options[$key] : $default;
}

function woogit_theme_media_data($id, $size = 'medium') {
    $id = absint($id);
    if (!$id) return ['id' => 0, 'url' => ''];
    return ['id' => $id, 'url' => woogit_theme_image($id, $size)];
}
