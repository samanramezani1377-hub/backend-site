<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class WooCommerceProxy
{
    private ?string $pinnedHost = null;
    private array $pinnedIps = [];

    public function verify(string $baseUrl,string $username,string $applicationPassword,string $consumerKey,string $consumerSecret): array
    {
        $wp=$this->request($baseUrl.'/wp-json/',$username,$applicationPassword);
        if(is_wp_error($wp))return ['ok'=>false,'reason'=>'wordpress_unreachable'];
        $wpStatus=wp_remote_retrieve_response_code($wp);if($wpStatus<200||$wpStatus>=300)return ['ok'=>false,'reason'=>'wordpress_auth_failed'];
        $wc=$this->requestWooCommerce($baseUrl.'/wp-json/wc/v3/products?per_page=1',$consumerKey,$consumerSecret);
        if(is_wp_error($wc))return ['ok'=>false,'reason'=>'woocommerce_unreachable'];
        $wcStatus=wp_remote_retrieve_response_code($wc);if($wcStatus<200||$wcStatus>=300)return ['ok'=>false,'reason'=>'woocommerce_auth_failed'];
        return ['ok'=>true];
    }

    public function forward(string $baseUrl,string $path,string $method,string $username,string $applicationPassword,string $consumerKey,string $consumerSecret,array $query,string $rawBody,string $contentType): array
    {
        $isWordPressMedia=$path==='/wp-json/wp/v2/media'||str_starts_with($path,'/wp-json/wp/v2/media/');$url=$baseUrl.$path;if($query!==[])$url=add_query_arg($query,$url);
        $authorization=$isWordPressMedia?'Basic '.base64_encode($username.':'.$applicationPassword):'Basic '.base64_encode($consumerKey.':'.$consumerSecret);
        $headers=['Authorization'=>$authorization,'Accept'=>'application/json','User-Agent'=>'WooGit-Backend/'.WOOGIT_BACKEND_VERSION];if($contentType!=='')$headers['Content-Type']=$contentType;
        $args=['method'=>strtoupper($method),'timeout'=>20,'redirection'=>0,'headers'=>$headers,'data_format'=>'body'];if($rawBody!==''&&in_array(strtoupper($method),['POST','PUT','PATCH'],true))$args['body']=$rawBody;
        $response=$this->safePinnedRequest($url,$args);
        if(is_wp_error($response)){$message=strtolower((string)$response->get_error_message());$timeout=str_contains($message,'timed out')||str_contains($message,'timeout')||str_contains($message,'operation timed out');return ['status'=>$timeout?504:502,'body'=>'','headers'=>[],'timeout'=>$timeout];}
        $responseHeaders=[];foreach(['content-type','x-wp-total','x-wp-totalpages'] as $name){$value=wp_remote_retrieve_header($response,$name);if($value!=='')$responseHeaders[$name]=$value;}
        return ['status'=>wp_remote_retrieve_response_code($response),'body'=>wp_remote_retrieve_body($response),'headers'=>$responseHeaders,'timeout'=>false];
    }

    private function request(string $url,string $username,string $applicationPassword)
    {
        return $this->safePinnedRequest($url,['timeout'=>10,'redirection'=>0,'headers'=>['Authorization'=>'Basic '.base64_encode($username.':'.$applicationPassword),'Accept'=>'application/json','User-Agent'=>'WooGit-Backend/'.WOOGIT_BACKEND_VERSION]]);
    }

    private function requestWooCommerce(string $url,string $consumerKey,string $consumerSecret)
    {
        return $this->safePinnedRequest($url,['timeout'=>10,'redirection'=>0,'headers'=>['Authorization'=>'Basic '.base64_encode($consumerKey.':'.$consumerSecret),'Accept'=>'application/json','User-Agent'=>'WooGit-Backend/'.WOOGIT_BACKEND_VERSION]]);
    }

    private function safePinnedRequest(string $url,array $args)
    {
        $destination=$this->resolvePublicDestination($url);
        if($destination===null)return new \WP_Error('unsafe_destination','Unsafe or unresolvable upstream destination.');
        if(!function_exists('curl_init'))return new \WP_Error('secure_transport_unavailable','Secure pinned proxy transport is unavailable.');
        $this->pinnedHost=$destination['host'];
        $this->pinnedIps=$destination['ips'];
        add_action('http_api_curl',[$this,'pinCurl'],10,3);
        try{return wp_safe_remote_request($url,$args);}finally{remove_action('http_api_curl',[$this,'pinCurl'],10);$this->pinnedHost=null;$this->pinnedIps=[];}
    }

    public function pinCurl($handle,array $parsedArgs,string $url): void
    {
        if($this->pinnedHost===null||$this->pinnedIps===[]||!defined('CURLOPT_RESOLVE'))return;
        $entries=[];foreach($this->pinnedIps as $ip)$entries[]=$this->pinnedHost.':443:'.$ip;
        curl_setopt($handle,CURLOPT_RESOLVE,$entries);
    }

    private function resolvePublicDestination(string $url): ?array
    {
        $parts=wp_parse_url($url);if(!$parts||strtolower((string)($parts['scheme']??''))!=='https')return null;
        $host=strtolower(rtrim((string)($parts['host']??''),'.'));if($host==='')return null;
        if(!empty($parts['user'])||!empty($parts['pass'])||(!empty($parts['port'])&&(int)$parts['port']!==443))return null;
        if(filter_var($host,FILTER_VALIDATE_IP)){if(!filter_var($host,FILTER_VALIDATE_IP,FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE))return null;return ['host'=>$host,'ips'=>[$host]];}
        if($host==='localhost'||str_ends_with($host,'.localhost')||str_ends_with($host,'.local'))return null;
        $records=[];foreach([DNS_A,DNS_AAAA] as $type){$resolved=@dns_get_record($host,$type);if(is_array($resolved))$records=array_merge($records,$resolved);}
        if($records===[])return null;
        $safe=[];foreach($records as $record){$ip=$record['ip']??($record['ipv6']??'');if($ip===''||!filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE))return null;$safe[]=$ip;}
        $safe=array_values(array_unique($safe));if($safe===[])return null;
        return ['host'=>$host,'ips'=>$safe];
    }
}
