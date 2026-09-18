<?php
if (!defined('ABSPATH')) exit;

function woogit_register_theme_settings() {
    register_setting('woogit_theme', 'woogit_theme_options', ['sanitize_callback' => 'woogit_sanitize_theme_options']);
}

function woogit_theme_json_keys() { return ['faq_json','features_json','steps_json','social_links_json']; }
function woogit_theme_image_keys() { return ['logo_id','alternate_logo_id','favicon_id','og_image_id','hero_image_id','hero_mobile_image_id','enamad_image_id']; }
function woogit_theme_url_keys() { return ['hero_cta_url','hero_secondary_url','app_download_url','app_google_play_url','app_bazaar_url','enamad_verification_url']; }
function woogit_theme_apk_mimes($mimes) { $mimes['apk'] = 'application/vnd.android.package-archive'; return $mimes; }
add_filter('upload_mimes', 'woogit_theme_apk_mimes');
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
        if (in_array($key, woogit_theme_image_keys(), true) || $key === 'app_apk_id') { $out[$key] = absint($value); continue; }
        if (in_array($key, woogit_theme_url_keys(), true)) { $out[$key] = esc_url_raw($value); continue; }
        if ($key === 'support_email') { $out[$key] = sanitize_email($value); continue; }
        if ($key === 'enamad_code') { $out[$key] = wp_kses_post($value); continue; }
        if ($key === 'enamad_placement') { $out[$key] = in_array($value,['footer','contact'],true) ? $value : 'footer'; continue; }
        $out[$key] = sanitize_textarea_field($value);
    }
    return $out;
}

function woogit_theme_option($key, $default = '') { $options=woogit_theme_options(); return array_key_exists($key,$options) ? $options[$key] : $default; }
/**
 * One-time migration for the public content collections.
 * Older Theme versions could persist a partially populated repeater.
 * That stored JSON then took precedence over the defaults and made the
 * public site appear to contain only one item.
 */
function woogit_theme_migrate_content_collections() {
    $version = '2026-09-19-v1-content-collections';
    if (get_option('woogit_theme_content_migration') === $version) return;

    $options = get_option('woogit_theme_options', []);
    if (!is_array($options)) $options = [];

    foreach (['features_json', 'steps_json', 'faq_json'] as $key) {
        $raw = $options[$key] ?? '';
        $items = is_string($raw) ? json_decode($raw, true) : $raw;
        if (is_array($items) && count($items) <= 1) {
            unset($options[$key]);
        }
    }

    update_option('woogit_theme_options', $options);
    update_option('woogit_theme_content_migration', $version, false);
}
add_action('after_setup_theme', 'woogit_theme_migrate_content_collections', 20);

function woogit_theme_normalize_capability_content() {
    $version = '2026-09-19-v4-capability-content';
    if (get_option('woogit_theme_capability_migration') === $version) return;

    $options = get_option('woogit_theme_options', []);
    if (!is_array($options)) $options = [];

    // Reconcile stored defaults instead of replacing the whole collection. This keeps
    // administrator-added custom entries while bringing known Theme-owned entries up to date.
    $features = isset($options['features_json']) ? json_decode((string)$options['features_json'], true) : [];
    if (is_array($features)) {
        $feature_updates = [
            'درون‌ریزی و برون‌ریزی محصولات' => 'محصولات را همراه با اطلاعات، دسته‌بندی‌ها، ویژگی‌ها، تصاویر و Variationها در قالب بسته WooGit درون‌ریزی یا برون‌ریزی کنید.',
            'مدیریت هوشمند با AI' => 'از قابلیت‌های هوش مصنوعی WooGit برای تعامل هوشمند با فروشگاه و استفاده از ابزارهای مدیریتی اپلیکیشن بهره بگیرید.',
        ];
        $seen = [];
        $normalized = [];
        foreach ($features as $item) {
            if (!is_array($item)) continue;
            $title = (string)($item['title'] ?? '');
            if ($title === 'عملیات گروهی مشتریان' || $title === 'عملیات گروهی کوپن‌ها') continue;
            if (isset($seen[$title])) continue;
            if (isset($feature_updates[$title])) $item['description'] = $feature_updates[$title];
            $normalized[] = $item;
            $seen[$title] = true;
        }
        if (!isset($seen['مدیریت هوشمند با AI'])) {
            $normalized[] = ['title'=>'مدیریت هوشمند با AI','description'=>$feature_updates['مدیریت هوشمند با AI'],'icon'=>'✧','image_id'=>0,'enabled'=>true];
        }
        $options['features_json'] = wp_json_encode($normalized, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    $faq_updates = [
        'WooGit چیست؟' => 'WooGit اپلیکیشن مدیریت فروشگاه WooCommerce است که ابزارهای مدیریت سفارش‌ها، محصولات، موجودی، مشتریان و کوپن‌ها، تحلیل فروش و کوپن، فاکتور PDF، انتقال محصولات، بارکد و SKU و قابلیت‌های هوش مصنوعی را از طریق موبایل در اختیار شما قرار می‌دهد.',
        'با WooGit چه کارهایی می‌توانم انجام دهم؟' => 'می‌توانید سفارش‌ها و محصولات را مدیریت کنید، موجودی را بررسی کنید، مشتریان و کوپن‌ها را مدیریت کنید، وضعیت چند سفارش را به‌صورت گروهی تغییر دهید، با بارکد یا SKU محصول را پیدا کنید، فاکتور PDF داشته باشید، محصولات را انتقال دهید و تحلیل فروش و استفاده از کوپن‌ها را ببینید.',
        'آیا مدیریت مشتریان هم وجود دارد؟' => 'بله. می‌توانید اطلاعات مشتریان را مشاهده، ایجاد، ویرایش و حذف کنید و جزئیات و سفارش‌های مرتبط با هر مشتری را بررسی کنید.',
        'آیا می‌توانم کوپن‌ها را مدیریت کنم؟' => 'بله. می‌توانید کوپن‌ها را مشاهده، ایجاد و ویرایش و حذف کنید و نوع تخفیف، مبلغ، تاریخ انقضا، محدودیت‌های استفاده، محصولات و دسته‌بندی‌های مرتبط و میزان استفاده را بررسی کنید.',
        'آیا WooGit قابلیت هوش مصنوعی دارد؟' => 'بله. قابلیت AI تکمیل شده است و برای تعامل هوشمند با فروشگاه و استفاده از ابزارهای مدیریتی WooGit در اپلیکیشن ارائه می‌شود.',
    ];
    $faq = isset($options['faq_json']) ? json_decode((string)$options['faq_json'], true) : [];
    if (is_array($faq)) {
        $seen = [];
        $normalized = [];
        foreach ($faq as $item) {
            if (!is_array($item)) continue;
            $question = (string)($item['question'] ?? '');
            if (!$question || isset($seen[$question])) continue;
            if (isset($faq_updates[$question])) $item['answer'] = $faq_updates[$question];
            $normalized[] = $item;
            $seen[$question] = true;
        }
        if (!isset($seen['آیا WooGit قابلیت هوش مصنوعی دارد؟'])) {
            $normalized[] = ['question'=>'آیا WooGit قابلیت هوش مصنوعی دارد؟','answer'=>$faq_updates['آیا WooGit قابلیت هوش مصنوعی دارد؟'],'enabled'=>true];
        }
        $options['faq_json'] = wp_json_encode($normalized, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    update_option('woogit_theme_options', $options);
    update_option('woogit_theme_capability_migration', $version, false);
}
add_action('after_setup_theme', 'woogit_theme_normalize_capability_content', 25);

function woogit_theme_media_data($id, $size='medium') { $id=absint($id); return ['id'=>$id,'url'=>$id?woogit_theme_image($id,$size):'']; }
