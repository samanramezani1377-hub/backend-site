<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class IdempotencyService
{
    public function fingerprint(string $method,string $path,array $query,string $rawBody): string
    {
        $query=$this->canonicalize($query);return hash('sha256',wp_json_encode(['method'=>strtoupper($method),'path'=>$path,'query'=>$query,'body_hash'=>hash('sha256',$rawBody)],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
    }
    private function canonicalize(array $value): array{foreach($value as $key=>$item)if(is_array($item))$value[$key]=$this->canonicalize($item);ksort($value,SORT_STRING);return $value;}

    public function lookup(int $accountId,int $siteId,string $key,string $fingerprint): array
    {
        global $wpdb;$row=$wpdb->get_row($wpdb->prepare("SELECT request_fingerprint,operation_id,state,status_code,response_body FROM {$wpdb->prefix}woogit_idempotency WHERE account_id=%d AND site_id=%d AND idempotency_key=%s LIMIT 1",$accountId,$siteId,$key),ARRAY_A);if(!$row)return ['state'=>'absent'];if(!hash_equals((string)$row['request_fingerprint'],$fingerprint))return ['state'=>'conflict'];$state=(string)$row['state'];
        if($state==='pending'){$operation=$wpdb->get_row($wpdb->prepare("SELECT status,upstream_status,response_body FROM {$wpdb->prefix}woogit_operations WHERE operation_id=%s AND account_id=%d AND site_id=%d LIMIT 1",(string)$row['operation_id'],$accountId,$siteId),ARRAY_A);if(!$operation||(string)$operation['status']==='unknown'){$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}woogit_idempotency SET state='unknown',updated_at=%s WHERE account_id=%d AND site_id=%d AND idempotency_key=%s AND state='pending'",current_time('mysql',true),$accountId,$siteId,$key));$state='unknown';}elseif(in_array((string)$operation['status'],['succeeded','failed'],true)){$operationBody=(string)$operation['response_body'];$operationStatus=(int)$operation['upstream_status'];$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}woogit_idempotency SET state=%s,status_code=%d,response_body=%s,updated_at=%s WHERE account_id=%d AND site_id=%d AND idempotency_key=%s AND state='pending'",(string)$operation['status'],$operationStatus,$operationBody,current_time('mysql',true),$accountId,$siteId,$key));$state=(string)$operation['status'];$row['status_code']=$operationStatus;$row['response_body']=$operationBody;}}
        if($state==='pending')return ['state'=>'pending','operation_id'=>(string)$row['operation_id']];if($state==='unknown')return ['state'=>'unknown','operation_id'=>(string)$row['operation_id']];$body=json_decode((string)$row['response_body'],true);return ['state'=>$state==='succeeded'||$state==='failed'?'completed':'unknown','operation_id'=>(string)$row['operation_id'],'status'=>(int)$row['status_code'],'body'=>is_array($body)?$body:[]];
    }

    public function claim(int $accountId,int $siteId,string $key,string $fingerprint,string $operationId): array
    {global $wpdb;$table=$wpdb->prefix.'woogit_idempotency';$now=current_time('mysql',true);$inserted=$wpdb->insert($table,['account_id'=>$accountId,'site_id'=>$siteId,'idempotency_key'=>$key,'request_fingerprint'=>$fingerprint,'operation_id'=>$operationId,'state'=>'pending','status_code'=>0,'response_body'=>'','created_at'=>$now,'updated_at'=>$now],['%d','%d','%s','%s','%s','%s','%d','%s','%s','%s']);if($inserted)return ['state'=>'claimed','operation_id'=>$operationId];return $this->lookup($accountId,$siteId,$key,$fingerprint);}
    public function markUnknown(int $accountId,int $siteId,string $key): bool{global $wpdb;$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}woogit_idempotency SET state='unknown',updated_at=%s WHERE account_id=%d AND site_id=%d AND idempotency_key=%s AND state='pending'",current_time('mysql',true),$accountId,$siteId,$key));return 1===(int)$wpdb->rows_affected;}
    public function complete(int $accountId,int $siteId,string $key,int $status,array $body): bool{global $wpdb;$state=($status>=200&&$status<300)?'succeeded':'failed';$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}woogit_idempotency SET state=%s,status_code=%d,response_body=%s,updated_at=%s WHERE account_id=%d AND site_id=%d AND idempotency_key=%s AND state='pending'",$state,$status,wp_json_encode($body,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),current_time('mysql',true),$accountId,$siteId,$key));return 1===(int)$wpdb->rows_affected;}

    public function lookupVerify(string $key,string $fingerprint): array
    {
        return $this->lookupScoped(0,0,$key,$fingerprint,true);
    }
    public function claimVerify(string $key,string $fingerprint,string $operationId): array{return $this->claim(0,0,$key,$fingerprint,$operationId);}
    public function markVerifyUnknown(string $key): bool{return $this->markUnknown(0,0,$key);}
    public function completeVerify(string $key,int $status,array $body): bool
    {
        global $wpdb;$state=($status>=200&&$status<300)?'succeeded':'failed';$stored=$this->encryptVerifyResponse($body);if($stored===null)return false;
        $row=$wpdb->get_row($wpdb->prepare("SELECT operation_id FROM {$wpdb->prefix}woogit_idempotency WHERE account_id=0 AND site_id=0 AND idempotency_key=%s AND state='pending' LIMIT 1",$key),ARRAY_A);
        if(!$row)return false;
        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}woogit_idempotency SET state=%s,status_code=%d,response_body=%s,updated_at=%s WHERE account_id=0 AND site_id=0 AND idempotency_key=%s AND state='pending'",$state,$status,$stored,current_time('mysql',true),$key));
        $saved=1===(int)$wpdb->rows_affected;
        if($saved&&$state==='failed')$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}woogit_operations SET status='failed',upstream_status=%d,response_body=%s,updated_at=%s WHERE operation_id=%s AND status='pending'",$status,wp_json_encode($body,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),current_time('mysql',true),(string)$row['operation_id']));
        return $saved;
    }

    private function lookupScoped(int $accountId,int $siteId,string $key,string $fingerprint,bool $verify): array
    {
        global $wpdb;$row=$wpdb->get_row($wpdb->prepare("SELECT request_fingerprint,operation_id,state,status_code,response_body FROM {$wpdb->prefix}woogit_idempotency WHERE account_id=%d AND site_id=%d AND idempotency_key=%s LIMIT 1",$accountId,$siteId,$key),ARRAY_A);if(!$row)return ['state'=>'absent'];if(!hash_equals((string)$row['request_fingerprint'],$fingerprint))return ['state'=>'conflict'];$state=(string)$row['state'];
        if($state==='pending')return ['state'=>'pending','operation_id'=>(string)$row['operation_id']];if($state==='unknown')return ['state'=>'unknown','operation_id'=>(string)$row['operation_id']];
        $body=$verify?$this->decryptVerifyResponse((string)$row['response_body']):json_decode((string)$row['response_body'],true);if($body===null)return ['state'=>'unknown','operation_id'=>(string)$row['operation_id']];return ['state'=>'completed','operation_id'=>(string)$row['operation_id'],'status'=>(int)$row['status_code'],'body'=>is_array($body)?$body:[]];
    }

    private function encryptVerifyResponse(array $body): ?string
    {
        if(!function_exists('sodium_crypto_secretbox')||!function_exists('sodium_crypto_secretbox_keygen'))return null;$key=hash('sha256',wp_salt('auth').'|woogit-verify-idempotency',true);$nonce=random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);$cipher=sodium_crypto_secretbox(wp_json_encode($body,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),$nonce,$key);return 'v1:'.base64_encode($nonce.$cipher);
    }
    private function decryptVerifyResponse(string $stored): ?array
    {
        if(!str_starts_with($stored,'v1:')||!function_exists('sodium_crypto_secretbox_open'))return null;$raw=base64_decode(substr($stored,3),true);if($raw===false||strlen($raw)<=SODIUM_CRYPTO_SECRETBOX_NONCEBYTES)return null;$key=hash('sha256',wp_salt('auth').'|woogit-verify-idempotency',true);$plain=sodium_crypto_secretbox_open(substr($raw,SODIUM_CRYPTO_SECRETBOX_NONCEBYTES),substr($raw,0,SODIUM_CRYPTO_SECRETBOX_NONCEBYTES),$key);if($plain===false)return null;$body=json_decode($plain,true);return is_array($body)?$body:null;
    }
}
