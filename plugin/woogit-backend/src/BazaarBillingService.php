<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class BazaarBillingService
{
    private const ACCESS_TRANSIENT = 'woogit_bazaar_access_token';
    private const DEFAULT_BASE_URL = 'https://pardakht.cafebazaar.ir';

    private function config(string $key): string
    {
        $constant = 'WOOGIT_BAZAAR_' . strtoupper($key);
        if (defined($constant)) {
            $value = trim((string)constant($constant));
            if ($value !== '') return $value;
        }
        return '';
    }

    public function verify(int $accountId, int $siteId, string $productId, string $purchaseToken, string $packageName): array
    {
        $productId=trim($productId);$purchaseToken=trim($purchaseToken);$packageName=trim($packageName);
        if($productId===''||$purchaseToken===''||$packageName==='')return ['ok'=>false,'code'=>'invalid_bazaar_purchase'];
        $configuredPackage=$this->config('package_name');
        if($configuredPackage===''||!hash_equals($configuredPackage,$packageName))return ['ok'=>false,'code'=>'bazaar_package_mismatch'];
        global $wpdb;$table=$wpdb->prefix.'woogit_bazaar_purchases';
        $existing=$wpdb->get_row($wpdb->prepare("SELECT account_id,site_id,product_id,expires_at FROM {$table} WHERE purchase_token=%s LIMIT 1",$purchaseToken),ARRAY_A);
        if($existing){
            if((int)$existing['account_id']!==$accountId||(int)$existing['site_id']!==$siteId)return ['ok'=>false,'code'=>'bazaar_purchase_already_claimed'];
            return ['ok'=>true,'already_processed'=>true,'expires_at'=>$existing['expires_at']?:null,'product_id'=>(string)$existing['product_id']];
        }
        $validated=$this->validateSubscription($packageName,$productId,$purchaseToken);if(!$validated['ok'])return $validated;
        $payload=$validated['data'];$state=$this->purchaseState($payload);
        if($state!==null&&$state!==0)return ['ok'=>false,'code'=>'bazaar_purchase_not_active'];
        $billing=new BillingService();$plan=$billing->findBazaarPlanBySku($productId);if(!$plan['ok'])return $plan;
        $expiry=$this->expiryTimestamp($payload);if(!$expiry)$expiry=time()+max(0,$billing->durationDaysForBazaar($plan['product'],(int)$plan['variation_id']))*DAY_IN_SECONDS;
        if(!$expiry||$expiry<=time())return ['ok'=>false,'code'=>'bazaar_purchase_expired'];
        if(!$billing->activateBazaarEntitlement($accountId,$siteId,$plan['product'],(int)$plan['variation_id'],$expiry))return ['ok'=>false,'code'=>'entitlement_activation_failed'];
        $inserted=$wpdb->insert($table,['account_id'=>$accountId,'site_id'=>$siteId,'product_id'=>$productId,'purchase_token'=>$purchaseToken,'order_id'=>isset($payload['orderId'])?sanitize_text_field((string)$payload['orderId']):'','purchase_time'=>$this->purchaseTime($payload),'expires_at'=>gmdate('Y-m-d H:i:s',$expiry),'verified_at'=>gmdate('Y-m-d H:i:s')],['%d','%d','%s','%s','%s','%s','%s','%s']);
        if($inserted===false){
            $existing=$wpdb->get_row($wpdb->prepare("SELECT account_id,site_id,expires_at FROM {$table} WHERE purchase_token=%s LIMIT 1",$purchaseToken),ARRAY_A);
            if($existing&&(int)$existing['account_id']===$accountId&&(int)$existing['site_id']===$siteId)return ['ok'=>true,'already_processed'=>true,'expires_at'=>$existing['expires_at']?:null];
            return ['ok'=>false,'code'=>'bazaar_purchase_record_failed'];
        }
        return ['ok'=>true,'already_processed'=>false,'expires_at'=>gmdate('Y-m-d H:i:s',$expiry)];
    }

    private function validateSubscription(string $packageName,string $productId,string $purchaseToken): array
    {
        $accessToken=$this->accessToken();if($accessToken==='')return ['ok'=>false,'code'=>'bazaar_authorization_unavailable'];
        $base=$this->config('api_base_url');if($base==='')$base=self::DEFAULT_BASE_URL;$base=rtrim($base,'/');
        $path='/devapi/v2/api/applications/'.rawurlencode($packageName).'/subscriptions/'.rawurlencode($productId).'/purchases/'.rawurlencode($purchaseToken).'/';
        $response=wp_remote_get($base.$path.'?access_token='.rawurlencode($accessToken),['timeout'=>12,'redirection'=>2]);
        if(is_wp_error($response))return ['ok'=>false,'code'=>'bazaar_verification_unavailable'];
        $status=(int)wp_remote_retrieve_response_code($response);
        if($status===401){delete_transient(self::ACCESS_TRANSIENT);$accessToken=$this->accessToken();if($accessToken!=='')$response=wp_remote_get($base.$path.'?access_token='.rawurlencode($accessToken),['timeout'=>12,'redirection'=>2]);$status=(int)wp_remote_retrieve_response_code($response);}
        if($status<200||$status>=300)return ['ok'=>false,'code'=>'bazaar_purchase_invalid'];
        $json=json_decode((string)wp_remote_retrieve_body($response),true);if(!is_array($json))return ['ok'=>false,'code'=>'bazaar_invalid_response'];
        return ['ok'=>true,'data'=>$json];
    }

    private function accessToken(): string
    {
        $cached=get_transient(self::ACCESS_TRANSIENT);if(is_string($cached)&&$cached!=='')return $cached;
        $clientId=$this->config('client_id');$clientSecret=$this->config('client_secret');$refreshToken=$this->config('refresh_token');
        if($clientId===''||$clientSecret===''||$refreshToken==='')return '';
        $base=$this->config('api_base_url');if($base==='')$base=self::DEFAULT_BASE_URL;$base=rtrim($base,'/');
        $response=wp_remote_post($base.'/devapi/v2/auth/token/',['timeout'=>12,'body'=>['client_id'=>$clientId,'client_secret'=>$clientSecret,'refresh_token'=>$refreshToken,'grant_type'=>'refresh_token']]);
        if(is_wp_error($response)||(int)wp_remote_retrieve_response_code($response)<200||(int)wp_remote_retrieve_response_code($response)>=300)return '';
        $json=json_decode((string)wp_remote_retrieve_body($response),true);$token=is_array($json)?trim((string)($json['access_token']??'')):'';$expires=is_array($json)?max(60,(int)($json['expires_in']??3600)):3600;
        if($token!=='')set_transient(self::ACCESS_TRANSIENT,$token,max(60,$expires-60));return $token;
    }

    private function purchaseState(array $payload): ?int { if(!array_key_exists('purchaseState',$payload))return null;return is_numeric($payload['purchaseState'])?(int)$payload['purchaseState']:null; }
    private function expiryTimestamp(array $payload): int { foreach(['expiryTimeMillis','expiry_time_millis','expirationTimeMillis'] as $key)if(isset($payload[$key])&&is_numeric($payload[$key]))return(int)floor(((int)$payload[$key])/1000);foreach(['expires_at','expiryTime','expirationTime'] as $key)if(isset($payload[$key])){$value=$payload[$key];if(is_numeric($value))return(int)$value>20000000000?(int)floor(((int)$value)/1000):(int)$value;$parsed=strtotime((string)$value);if($parsed)return$parsed;}return 0; }
    private function purchaseTime(array $payload): string { $value=$payload['purchaseTime']??$payload['purchase_time']??null;if(is_numeric($value))return gmdate('Y-m-d H:i:s',((int)$value>20000000000?(int)floor(((int)$value)/1000):(int)$value));$parsed=is_string($value)?strtotime($value):false;return$parsed?gmdate('Y-m-d H:i:s',$parsed):gmdate('Y-m-d H:i:s'); }
}
