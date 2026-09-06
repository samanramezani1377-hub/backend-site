<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class IdempotencyService
{
    public function fingerprint(string $method,string $path,array $query,?array $body): string
    {
        return hash('sha256',wp_json_encode(['method'=>strtoupper($method),'path'=>$path,'query'=>$query,'body'=>$body],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
    }

    public function lookup(int $accountId,int $siteId,string $key,string $fingerprint): ?array
    {
        global $wpdb; $table=$wpdb->prefix.'woogit_idempotency';
        $row=$wpdb->get_row($wpdb->prepare("SELECT request_fingerprint,status_code,response_body FROM {$table} WHERE account_id=%d AND site_id=%d AND idempotency_key=%s LIMIT 1",$accountId,$siteId,$key),ARRAY_A);
        if(!$row)return null;
        if(!hash_equals($row['request_fingerprint'],$fingerprint))return ['conflict'=>true];
        $body=json_decode($row['response_body'],true); return ['conflict'=>false,'status'=>(int)$row['status_code'],'body'=>is_array($body)?$body:[]];
    }

    public function store(int $accountId,int $siteId,string $key,string $fingerprint,int $status,array $body): void
    {
        global $wpdb; $table=$wpdb->prefix.'woogit_idempotency';
        $wpdb->insert($table,['account_id'=>$accountId,'site_id'=>$siteId,'idempotency_key'=>$key,'request_fingerprint'=>$fingerprint,'status_code'=>$status,'response_body'=>wp_json_encode($body,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),'created_at'=>current_time('mysql',true)],['%d','%d','%s','%s','%d','%s','%s']);
    }
}
