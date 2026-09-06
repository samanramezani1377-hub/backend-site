<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class IdempotencyService
{
    public function fingerprint(string $method, string $path, array $query, string $rawBody): string
    {
        return hash('sha256', wp_json_encode([
            'method'=>strtoupper($method), 'path'=>$path, 'query'=>$query,
            'body_hash'=>hash('sha256',$rawBody),
        ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
    }

    public function lookup(int $accountId, int $siteId, string $key, string $fingerprint): array
    {
        global $wpdb;
        $row=$wpdb->get_row($wpdb->prepare("SELECT request_fingerprint,operation_id,state,status_code,response_body FROM {$wpdb->prefix}woogit_idempotency WHERE account_id=%d AND site_id=%d AND idempotency_key=%s LIMIT 1",$accountId,$siteId,$key),ARRAY_A);
        if(!$row)return ['state'=>'absent'];
        if(!hash_equals((string)$row['request_fingerprint'],$fingerprint))return ['state'=>'conflict'];
        $state=(string)$row['state'];
        if($state==='pending')return ['state'=>'pending','operation_id'=>(string)$row['operation_id']];
        if($state==='unknown')return ['state'=>'unknown','operation_id'=>(string)$row['operation_id']];
        $body=json_decode((string)$row['response_body'],true);
        return ['state'=>$state==='succeeded'||$state==='failed'?'completed':'unknown','operation_id'=>(string)$row['operation_id'],'status'=>(int)$row['status_code'],'body'=>is_array($body)?$body:[]];
    }

    public function claim(int $accountId,int $siteId,string $key,string $fingerprint,string $operationId): array
    {
        global $wpdb;$table=$wpdb->prefix.'woogit_idempotency';$now=current_time('mysql',true);
        $inserted=$wpdb->insert($table,['account_id'=>$accountId,'site_id'=>$siteId,'idempotency_key'=>$key,'request_fingerprint'=>$fingerprint,'operation_id'=>$operationId,'state'=>'pending','status_code'=>0,'response_body'=>'','created_at'=>$now,'updated_at'=>$now],['%d','%d','%s','%s','%s','%s','%d','%s','%s','%s']);
        if($inserted)return ['state'=>'claimed','operation_id'=>$operationId];
        return $this->lookup($accountId,$siteId,$key,$fingerprint);
    }

    public function markUnknown(int $accountId,int $siteId,string $key): bool
    {
        global $wpdb;
        return false !== $wpdb->update($wpdb->prefix.'woogit_idempotency',['state'=>'unknown','updated_at'=>current_time('mysql',true)],['account_id'=>$accountId,'site_id'=>$siteId,'idempotency_key'=>$key],['%s','%s'],['%d','%d','%s']);
    }

    public function complete(int $accountId,int $siteId,string $key,int $status,array $body): bool
    {
        global $wpdb;$state=($status>=200&&$status<300)?'succeeded':'failed';
        return false !== $wpdb->update($wpdb->prefix.'woogit_idempotency',['state'=>$state,'status_code'=>$status,'response_body'=>wp_json_encode($body,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),'updated_at'=>current_time('mysql',true)],['account_id'=>$accountId,'site_id'=>$siteId,'idempotency_key'=>$key],['%s','%d','%s','%s'],['%d','%d','%s']);
    }
}
