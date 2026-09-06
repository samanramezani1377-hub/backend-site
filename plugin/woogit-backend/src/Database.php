<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class Database
{
    public static function install(string $fromVersion = ''): void
    {
        global $wpdb;
        require_once ABSPATH.'wp-admin/includes/upgrade.php';
        $charset=$wpdb->get_charset_collate();
        $prefix=$wpdb->prefix.'woogit_';

        dbDelta("CREATE TABLE {$prefix}accounts (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,email VARCHAR(190) NULL,status VARCHAR(32) NOT NULL DEFAULT 'active',created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY (id),KEY status (status)) {$charset};");
        dbDelta("CREATE TABLE {$prefix}sites (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,account_id BIGINT UNSIGNED NOT NULL,canonical_url TEXT NOT NULL,host VARCHAR(190) NOT NULL,status VARCHAR(32) NOT NULL DEFAULT 'active',created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY (id),UNIQUE KEY host (host),KEY account_id (account_id),KEY status (status)) {$charset};");
        dbDelta("CREATE TABLE {$prefix}sessions (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,account_id BIGINT UNSIGNED NOT NULL,site_id BIGINT UNSIGNED NOT NULL,scope VARCHAR(20) NULL,token_hash CHAR(64) NOT NULL,expires_at DATETIME NOT NULL,revoked_at DATETIME NULL,created_at DATETIME NOT NULL,PRIMARY KEY (id),UNIQUE KEY token_hash (token_hash),KEY account_site (account_id,site_id),KEY account_site_scope (account_id,site_id,scope),KEY expires_at (expires_at)) {$charset};");
        dbDelta("CREATE TABLE {$prefix}entitlements (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,account_id BIGINT UNSIGNED NOT NULL,site_id BIGINT UNSIGNED NOT NULL,status VARCHAR(32) NOT NULL,starts_at DATETIME NOT NULL,expires_at DATETIME NULL,capabilities LONGTEXT NOT NULL,created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY (id),UNIQUE KEY account_site (account_id,site_id),KEY status (status),KEY expires_at (expires_at)) {$charset};");
        dbDelta("CREATE TABLE {$prefix}idempotency (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,account_id BIGINT UNSIGNED NOT NULL,site_id BIGINT UNSIGNED NOT NULL,idempotency_key VARCHAR(190) NOT NULL,request_fingerprint CHAR(64) NOT NULL,operation_id VARCHAR(64) NOT NULL,state VARCHAR(16) NOT NULL DEFAULT 'pending',status_code SMALLINT UNSIGNED NOT NULL DEFAULT 0,response_body LONGTEXT NOT NULL,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY (id),UNIQUE KEY account_site_key (account_id,site_id,idempotency_key),UNIQUE KEY operation_id (operation_id),KEY state (state),KEY updated_at (updated_at)) {$charset};");
        dbDelta("CREATE TABLE {$prefix}operations (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,operation_id VARCHAR(64) NOT NULL,account_id BIGINT UNSIGNED NOT NULL,site_id BIGINT UNSIGNED NOT NULL,idempotency_key VARCHAR(190) NOT NULL,request_fingerprint CHAR(64) NOT NULL,resource VARCHAR(32) NOT NULL,operation_path TEXT NOT NULL,method VARCHAR(10) NOT NULL,status VARCHAR(16) NOT NULL,upstream_status SMALLINT UNSIGNED NULL,response_body LONGTEXT NULL,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,expires_at DATETIME NULL,PRIMARY KEY (id),UNIQUE KEY operation_id (operation_id),KEY account_site (account_id,site_id),KEY status (status),KEY expires_at (expires_at)) {$charset};");
        dbDelta("CREATE TABLE {$prefix}rate_limits (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,bucket VARCHAR(64) NOT NULL,identifier CHAR(64) NOT NULL,window_start DATETIME NOT NULL,hits INT UNSIGNED NOT NULL DEFAULT 0,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY (id),UNIQUE KEY bucket_identifier_window (bucket,identifier,window_start),KEY window_start (window_start),KEY updated_at (updated_at)) {$charset};");

        if(version_compare($fromVersion ?: '0.0.0','0.3.2','<') && !self::migrateSessionScope($prefix)){
            error_log('[WooGit Backend] Database migration to session scope failed; database version was not advanced.');
            return;
        }
        if(false===get_option('woogit_backend_version_policy',false))add_option('woogit_backend_version_policy',['latest_version'=>WOOGIT_BACKEND_VERSION,'recommended_version'=>WOOGIT_BACKEND_VERSION,'minimum_supported_version'=>'0.0.0','deprecated_versions'=>[]], '', false);
        update_option('woogit_backend_db_version',WOOGIT_BACKEND_VERSION,false);
    }

    private static function migrateSessionScope(string $prefix): bool
    {
        global $wpdb;
        $table=$prefix.'sessions';
        $column=$wpdb->get_row($wpdb->prepare("SHOW COLUMNS FROM {$table} LIKE %s",'scope'));
        if(!$column){
            $result=$wpdb->query("ALTER TABLE {$table} ADD COLUMN scope VARCHAR(20) NULL AFTER site_id");
            if(false===$result)return false;
        }
        $updated=$wpdb->query($wpdb->prepare("UPDATE {$table} SET scope=%s WHERE scope IS NULL OR scope=''",SessionService::SCOPE_OPERATIONAL));
        if(false===$updated)return false;
        $modified=$wpdb->query("ALTER TABLE {$table} MODIFY COLUMN scope VARCHAR(20) NOT NULL");
        return false!==$modified;
    }
}
