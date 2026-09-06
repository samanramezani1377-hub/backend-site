<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class WebSessionService
{
    public const TTL=2592000;

    public function issue(int $accountId,int $siteId): ?array
    {
        if($accountId<=0||$siteId<=0)return null;
        $token=bin2hex(random_bytes(32));$hash=hash('sha256',$token);$now=time();$expires=$now+self::TTL;
        global $wpdb;$table=$wpdb->prefix.'woogit_web_sessions';
        $ok=$wpdb->insert($table,['account_id'=>$accountId,'site_id'=>$siteId,'token_hash'=>$hash,'expires_at'=>gmdate('Y-m-d H:i:s',$expires),'revoked_at'=>null,'created_at'=>gmdate('Y-m-d H:i:s',$now)],['%d','%d','%s','%s','%s','%s']);
        if(!$ok)return null;
        return ['token'=>$token,'expires_at'=>$expires];
    }

    public function authenticate(string $token): ?array
    {
        $token=trim($token);if($token===''||strlen($token)>128)return null;
        global $wpdb;$table=$wpdb->prefix.'woogit_web_sessions';$hash=hash('sha256',$token);
        $row=$wpdb->get_row($wpdb->prepare("SELECT id,account_id,site_id,expires_at,revoked_at FROM {$table} WHERE token_hash=%s LIMIT 1",$hash),ARRAY_A);
        if(!$row||$row['revoked_at']!==null)return null;
        $expires=strtotime((string)$row['expires_at'].' UTC');if($expires===false||$expires<=time())return null;
        return ['id'=>(int)$row['id'],'account_id'=>(int)$row['account_id'],'site_id'=>(int)$row['site_id'],'expires_at'=>$expires];
    }

    public function revoke(string $token): bool
    {
        $token=trim($token);if($token==='')return false;
        global $wpdb;$table=$wpdb->prefix.'woogit_web_sessions';
        return false!==$wpdb->update($table,['revoked_at'=>gmdate('Y-m-d H:i:s')],['token_hash'=>hash('sha256',$token),'revoked_at'=>null],['%s'],['%s','%s']);
    }
}
