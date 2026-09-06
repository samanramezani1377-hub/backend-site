<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class IdempotencyService
{
    public function fingerprint(string $method, string $path, array $query, string $rawBody): string
    {
        return hash('sha256', wp_json_encode([
            'method'=>strtoupper($method),
            'path'=>$path,
            'query'=>$query,
            'body_hash'=>hash('sha256',$rawBody),
        ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
    }

    public function lookup(int $accountId, int $siteId, string $key, string $fingerprint): array
    {
        global $wpdb;
        $row=$wpdb->get_row($wpdb->prepare("SELECT request_fingerprint,operation_id,status_code,response_body FROM {$wpdb->prefix}woogit_idempotency WHERE account_id=%d AND site_id=%d AND idempotency_key=%s LIMIT 1",$accountId,$siteId,$key),ARRAY_A);
        if(!$row)return ['state'=>'absent'];
        if(!hash_equals((string)$row['request_fingerprint'],$fingerprint))return ['state'=>'conflict'];
        if((int)$row['status_code']===0)return ['state'=>'pending','operation_id'=>(string)$row['operation_id']];
        $body=json_decode($row['response_body'],true);
        return ['state'=>'completed','operation_id'=>(string)$row['operation_id'],'status'=>(int)$row['status_code'],'body'=>is_array($body)?$body:[]];
    }

    public function claim(int $accountId,int $siteId,string $key,string $fingerprint,string $operationId): array
    {
        global $wpdb;$table=$wpdb->prefix.'woogit_idempotency';$now=current_time('mysql',true);
        $inserted=$wpdb->insert($table,['account_id'=>$accountId,'site_id'=>$siteId,'idempotency_key'=>$key,'request_fingerprint'=>$fingerprint,'operation_id'=>$operationId,'status_code'=>0,'response_body'=>'','created_at'=>$now,'updated_at'=>$now],['%d','%d','%s','%s','%s','%d','%s','%s','%s']);
        if($inserted)return ['state'=>'claimed','operation_id'=>$operationId];
        return $this->lookup($accountId,$siteId,$key,$fingerprint);
    }

    public function complete(int $accountId,int $siteId,string $key,int $status,array $body): void
    {
        global $wpdb;$wpdb->update($wpdb->prefix.'woogit_idempotency',['status_code'=>$status,'response_body'=>wp_json_encode($body,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),'updated_at'=>current_time('mysql',true)],['account_id'=>$accountId,'site_id'=>$siteId,'idempotency_key'=>$key],['%d','%s','%s'],['%d','%d','%s']);
    }
}
