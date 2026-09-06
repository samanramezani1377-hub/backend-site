<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class Database
{
    public static function install(): void
    {
        global $wpdb;
        require_once ABSPATH.'wp-admin/includes/upgrade.php';
        $charset=$wpdb->get_charset_collate();
        $prefix=$wpdb->prefix.'woogit_';

        dbDelta("CREATE TABLE {$prefix}accounts (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,email VARCHAR(190) NULL,status VARCHAR(32) NOT NULL DEFAULT 'active',created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY (id),KEY status (status)) {$charset};");
        $emailIndex=$wpdb->get_row($wpdb->prepare("SHOW INDEX FROM {$prefix}accounts WHERE Key_name=%s",'email'));if($emailIndex)$wpdb->query("ALTER TABLE {$prefix}accounts DROP INDEX email");
        dbDelta("CREATE TABLE {$prefix}sites (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,account_id BIGINT UNSIGNED NOT NULL,canonical_url TEXT NOT NULL,host VARCHAR(190) NOT NULL,status VARCHAR(32) NOT NULL DEFAULT 'active',created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY (id),UNIQUE KEY host (host),KEY account_id (account_id),KEY status (status)) {$charset};");
        $accountSiteIndex=$wpdb->get_row($wpdb->prepare("SHOW INDEX FROM {$prefix}sites WHERE Key_name=%s",'account_site'));if($accountSiteIndex)$wpdb->query("ALTER TABLE {$prefix}sites DROP INDEX account_site");
        dbDelta("CREATE TABLE {$prefix}sessions (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,account_id BIGINT UNSIGNED NOT NULL,site_id BIGINT UNSIGNED NOT NULL,scope VARCHAR(20) NULL,token_hash CHAR(64) NOT NULL,expires_at DATETIME NOT NULL,revoked_at DATETIME NULL,created_at DATETIME NOT NULL,PRIMARY KEY (id),UNIQUE KEY token_hash (token_hash),KEY account_site (account_id,site_id),KEY account_site_scope (account_id,site_id,scope),KEY expires_at (expires_at)) {$charset};");
        dbDelta("CREATE TABLE {$prefix}entitlements (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,account_id BIGINT UNSIGNED NOT NULL,site_id BIGINT UNSIGNED NOT NULL,status VARCHAR(32) NOT NULL,starts_at DATETIME NOT NULL,expires_at DATETIME NULL,capabilities LONGTEXT NOT NULL,created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY (id),UNIQUE KEY account_site (account_id,site_id),KEY status (status),KEY expires_at (expires_at)) {$charset};");
        dbDelta("CREATE TABLE {$prefix}idempotency (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,account_id BIGINT UNSIGNED NOT NULL,site_id BIGINT UNSIGNED NOT NULL,idempotency_key VARCHAR(190) NOT NULL,request_fingerprint CHAR(64) NOT NULL,operation_id VARCHAR(64) NOT NULL,state VARCHAR(16) NOT NULL DEFAULT 'pending',status_code SMALLINT UNSIGNED NOT NULL DEFAULT 0,response_body LONGTEXT NOT NULL,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY (id),UNIQUE KEY account_site_key (account_id,site_id,idempotency_key),UNIQUE KEY operation_id (operation_id),KEY state (state),KEY updated_at (updated_at)) {$charset};");
        $wpdb->query("UPDATE {$prefix}idempotency SET state=IF(status_code=0,'pending',IF(status_code BETWEEN 200 AND 299,'succeeded','failed')) WHERE state='pending'");
        dbDelta("CREATE TABLE {$prefix}operations (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,operation_id VARCHAR(64) NOT NULL,account_id BIGINT UNSIGNED NOT NULL,site_id BIGINT UNSIGNED NOT NULL,idempotency_key VARCHAR(190) NOT NULL,request_fingerprint CHAR(64) NOT NULL,resource VARCHAR(32) NOT NULL,operation_path TEXT NOT NULL,method VARCHAR(10) NOT NULL,status VARCHAR(16) NOT NULL,upstream_status SMALLINT UNSIGNED NULL,response_body LONGTEXT NULL,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,expires_at DATETIME NULL,PRIMARY KEY (id),UNIQUE KEY operation_id (operation_id),KEY account_site (account_id,site_id),KEY status (status),KEY expires_at (expires_at)) {$charset};");
        dbDelta("CREATE TABLE {$prefix}rate_limits (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,bucket VARCHAR(64) NOT NULL,identifier CHAR(64) NOT NULL,window_start DATETIME NOT NULL,hits INT UNSIGNED NOT NULL DEFAULT 0,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY (id),UNIQUE KEY bucket_identifier_window (bucket,identifier,window_start),KEY window_start (window_start),KEY updated_at (updated_at)) {$charset};");

        self::migrateSessionScope($prefix);

        if(false===get_option('woogit_backend_version_policy',false))add_option('woogit_backend_version_policy',['latest_version'=>WOOGIT_BACKEND_VERSION,'recommended_version'=>WOOGIT_BACKEND_VERSION,'minimum_supported_version'=>'0.0.0','deprecated_versions'=>[]], '', false);
        update_option('woogit_backend_db_version',WOOGIT_BACKEND_VERSION,false);
    }

    private static function migrateSessionScope(string $prefix): void
    {
        global $wpdb;
        $table=$prefix.'sessions';
        $column=$wpdb->get_row($wpdb->prepare("SHOW COLUMNS FROM {$table} LIKE %s",'scope'));
        if(!$column) $wpdb->query("ALTER TABLE {$table} ADD COLUMN scope VARCHAR(20) NULL AFTER site_id");
        $wpdb->query($wpdb->prepare("UPDATE {$table} SET scope=%s WHERE scope IS NULL OR scope=''",SessionService::SCOPE_OPERATIONAL));
        $wpdb->query("ALTER TABLE {$table} MODIFY COLUMN scope VARCHAR(20) NOT NULL");
    }
}
