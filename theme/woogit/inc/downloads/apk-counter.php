<?php
if (!defined('ABSPATH')) exit;

define('WOOGIT_APK_DOWNLOAD_COUNT_OPTION', 'woogit_apk_download_count');

function woogit_apk_download_url($attachment_id = 0) {
    $attachment_id = absint($attachment_id);
    if (!$attachment_id) return '';
    $url = get_permalink(get_queried_object_id());
    if (!$url) $url = home_url('/');
    return add_query_arg('woogit_apk_download', $attachment_id, $url);
}

function woogit_apk_download_count() {
    return max(0, (int) get_option(WOOGIT_APK_DOWNLOAD_COUNT_OPTION, 0));
}

function woogit_handle_apk_download() {
    $attachment_id = absint($_GET['woogit_apk_download'] ?? 0);
    if (!$attachment_id) return;

    $configured_id = absint(woogit_theme_option('app_apk_id', 0));
    if (!$configured_id || $attachment_id !== $configured_id) {
        status_header(404);
        exit;
    }

    $apk_url = wp_get_attachment_url($attachment_id);
    if (!$apk_url) {
        status_header(404);
        exit;
    }

    global $wpdb;
    $table = $wpdb->options;
    $updated = $wpdb->query($wpdb->prepare(
        "UPDATE {$table} SET option_value = CAST(option_value AS UNSIGNED) + 1 WHERE option_name = %s",
        WOOGIT_APK_DOWNLOAD_COUNT_OPTION
    ));
    if (!$updated) {
        add_option(WOOGIT_APK_DOWNLOAD_COUNT_OPTION, 1, '', false);
    }

    wp_safe_redirect($apk_url, 302);
    exit;
}
add_action('template_redirect', 'woogit_handle_apk_download', 0);
