<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class Database
{
    public static function install(): void
    {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $charset = $wpdb->get_charset_collate();
        $prefix = $wpdb->prefix . 'woogit_';
        dbDelta("CREATE TABLE {$prefix}accounts (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,email VARCHAR(190) NULL,status VARCHAR(32) NOT NULL DEFAULT 'active',created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY (id),KEY status (status)) {$charset};");
        // Email is contact metadata, not an Account identity or uniqueness boundary.
        $emailIndex = $wpdb->get_row($wpdb->prepare("SHOW INDEX FROM {$prefix}accounts WHERE Key_name = %s", 'email'));
        if ($emailIndex) $wpdb->query("ALTER TABLE {$prefix}accounts DROP INDEX email");
        dbDelta("CREATE TABLE {$prefix}sites (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,account_id BIGINT UNSIGNED NOT NULL,canonical_url TEXT NOT NULL,host VARCHAR(190) NOT NULL,status VARCHAR(32) NOT NULL DEFAULT 'active',created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY (id),UNIQUE KEY account_site (account_id,host),KEY account_id (account_id),KEY status (status)) {$charset};");
        dbDelta("CREATE TABLE {$prefix}sessions (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,account_id BIGINT UNSIGNED NOT NULL,site_id BIGINT UNSIGNED NOT NULL,token_hash CHAR(64) NOT NULL,expires_at DATETIME NOT NULL,revoked_at DATETIME NULL,created_at DATETIME NOT NULL,PRIMARY KEY (id),UNIQUE KEY token_hash (token_hash),KEY account_site (account_id,site_id),KEY expires_at (expires_at)) {$charset};");
        dbDelta("CREATE TABLE {$prefix}entitlements (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,account_id BIGINT UNSIGNED NOT NULL,site_id BIGINT UNSIGNED NOT NULL,status VARCHAR(32) NOT NULL,starts_at DATETIME NOT NULL,expires_at DATETIME NULL,capabilities LONGTEXT NOT NULL,created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY (id),UNIQUE KEY account_site (account_id,site_id),KEY status (status),KEY expires_at (expires_at)) {$charset};");
        dbDelta("CREATE TABLE {$prefix}idempotency (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,account_id BIGINT UNSIGNED NOT NULL,site_id BIGINT UNSIGNED NOT NULL,idempotency_key VARCHAR(190) NOT NULL,request_fingerprint CHAR(64) NOT NULL,operation_id VARCHAR(64) NOT NULL,status_code SMALLINT UNSIGNED NOT NULL DEFAULT 0,response_body LONGTEXT NOT NULL,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY (id),UNIQUE KEY account_site_key (account_id,site_id,idempotency_key),UNIQUE KEY operation_id (operation_id)) {$charset};");
        dbDelta("CREATE TABLE {$prefix}operations (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,operation_id VARCHAR(64) NOT NULL,account_id BIGINT UNSIGNED NOT NULL,site_id BIGINT UNSIGNED NOT NULL,idempotency_key VARCHAR(190) NOT NULL,request_fingerprint CHAR(64) NOT NULL,resource VARCHAR(32) NOT NULL,operation_path TEXT NOT NULL,method VARCHAR(10) NOT NULL,status VARCHAR(16) NOT NULL,upstream_status SMALLINT UNSIGNED NULL,response_body LONGTEXT NULL,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,expires_at DATETIME NULL,PRIMARY KEY (id),UNIQUE KEY operation_id (operation_id),KEY account_site (account_id,site_id),KEY status (status),KEY expires_at (expires_at)) {$charset};");
        update_option('woogit_backend_db_version', WOOGIT_BACKEND_VERSION, false);
    }
}
