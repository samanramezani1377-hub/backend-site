<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class EntitlementService
{
    public function isAllowed(int $accountId, int $siteId, string $capability = 'commerce'): bool
    {
        global $wpdb;
        $accounts=$wpdb->prefix.'woogit_accounts';$entitlements=$wpdb->prefix.'woogit_entitlements';
        $row=$wpdb->get_row($wpdb->prepare("SELECT a.status AS account_status,e.status,e.expires_at,e.capabilities FROM {$accounts} a INNER JOIN {$entitlements} e ON e.account_id=a.id AND e.site_id=%d WHERE a.id=%d LIMIT 1",$siteId,$accountId),ARRAY_A);
        if(!$row||$row['account_status']!=='active'||!in_array($row['status'],['trial','active'],true))return false;
        if(!empty($row['expires_at'])&&$row['expires_at']<=current_time('mysql',true))return false;
        $capabilities=json_decode((string)$row['capabilities'],true);return is_array($capabilities)&&in_array($capability,$capabilities,true);
    }

    public function getExpiresAt(int $accountId, int $siteId): ?int
    {
        global $wpdb;
        $table=$wpdb->prefix.'woogit_entitlements';
        $value=$wpdb->get_var($wpdb->prepare("SELECT expires_at FROM {$table} WHERE account_id=%d AND site_id=%d LIMIT 1",$accountId,$siteId));
        if(!$value)return null;
        $timestamp=strtotime((string)$value.' UTC');
        return $timestamp===false?null:$timestamp;
    }

    public function grantTrial(int $accountId,int $siteId,int $days=15): bool
    {
        global $wpdb;$table=$wpdb->prefix.'woogit_entitlements';
        $existing=$wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE account_id=%d AND site_id=%d LIMIT 1",$accountId,$siteId));if($existing)return true;
        $start=current_time('mysql',true);$end=gmdate('Y-m-d H:i:s',time()+($days*DAY_IN_SECONDS));
        $ok=$wpdb->insert($table,['account_id'=>$accountId,'site_id'=>$siteId,'status'=>'trial','starts_at'=>$start,'expires_at'=>$end,'capabilities'=>wp_json_encode(['commerce'])],['%d','%d','%s','%s','%s','%s']);
        if($ok)return true;
        return (bool)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE account_id=%d AND site_id=%d LIMIT 1",$accountId,$siteId));
    }
}
