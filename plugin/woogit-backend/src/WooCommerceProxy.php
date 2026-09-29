<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class WooCommerceProxy
{
    private ?string $pinnedHost = null;
    private array $pinnedIps = [];

    /**
     * Detect the REST URL style once per connected site. WooGit identity is site-based,
     * so the host is the stable cache key; no credentials are ever stored.
     */
    public function verify(string $baseUrl,string $username,string $applicationPassword,string $consumerKey,string $consumerSecret): array
    {
        $mode=$this->getRestMode($baseUrl);
        $wp=$this->requestRest($baseUrl,'/',$username,$applicationPassword,$mode);
        if(is_wp_error($wp))return ['ok'=>false,'reason'=>'wordpress_unreachable'];

        $wpStatus=wp_remote_retrieve_response_code($wp);
        if($wpStatus===404){
            $alternate=$mode==='query'?'pretty':'query';
            $wp=$this->requestRest($baseUrl,'/',$username,$applicationPassword,$alternate);
            if(is_wp_error($wp))return ['ok'=>false,'reason'=>'wordpress_unreachable'];
            $wpStatus=wp_remote_retrieve_response_code($wp);
            if($wpStatus>=200&&$wpStatus<300){$mode=$alternate;$this->rememberRestMode($baseUrl,$mode);}
            elseif($wpStatus===404)return ['ok'=>false,'reason'=>'wordpress_rest_unavailable'];
        }
        if($wpStatus<200||$wpStatus>=300)return ['ok'=>false,'reason'=>'wordpress_auth_failed'];

        $wc=$this->requestWooCommerce($baseUrl.'/wp-json/wc/v3/products?per_page=1',$consumerKey,$consumerSecret,$mode);
        if(is_wp_error($wc))return ['ok'=>false,'reason'=>'woocommerce_unreachable'];
        $wcStatus=wp_remote_retrieve_response_code($wc);
        if($wcStatus===404){
            $alternate=$mode==='query'?'pretty':'query';
            $wc=$this->requestWooCommerce($baseUrl.'/wp-json/wc/v3/products?per_page=1',$consumerKey,$consumerSecret,$alternate);
            if(is_wp_error($wc))return ['ok'=>false,'reason'=>'woocommerce_unreachable'];
            $wcStatus=wp_remote_retrieve_response_code($wc);
            if($wcStatus>=200&&$wcStatus<300){$mode=$alternate;$this->rememberRestMode($baseUrl,$mode);}
            elseif($wcStatus===404)return ['ok'=>false,'reason'=>'woocommerce_rest_unavailable'];
        }
        if($wcStatus<200||$wcStatus>=300)return ['ok'=>false,'reason'=>'woocommerce_auth_failed'];
        $this->rememberRestMode($baseUrl,$mode);
        return ['ok'=>true];
    }

    public function forward(string $baseUrl,string $path,string $method,string $username,string $applicationPassword,string $consumerKey,string $consumerSecret,array $query,string $rawBody,string $contentType,string $contentDisposition=''): array
    {
        $isWordPressMedia=$path==='/wp-json/wp/v2/media'||str_starts_with($path,'/wp-json/wp/v2/media/');
        $mode=$this->getRestMode($baseUrl);
        $url=$this->restUrl($baseUrl,$path,$mode);
        if($query!==[])$url=add_query_arg($query,$url);
        $authorization=$isWordPressMedia?'Basic '.base64_encode($username.':'.$applicationPassword):'Basic '.base64_encode($consumerKey.':'.$consumerSecret);
        $headers=['Authorization'=>$authorization,'Accept'=>'application/json','User-Agent'=>'WooGit-Backend/'.WOOGIT_BACKEND_VERSION];if($contentType!=='')$headers['Content-Type']=$contentType;if($isWordPressMedia&&$contentDisposition!=='')$headers['Content-Disposition']=$contentDisposition;
        $args=['method'=>strtoupper($method),'timeout'=>20,'redirection'=>0,'headers'=>$headers,'data_format'=>'body'];if($rawBody!==''&&in_array(strtoupper($method),['POST','PUT','PATCH'],true))$args['body']=$rawBody;
        $response=$this->safePinnedRequest($url,$args);
        if(!is_wp_error($response)&&wp_remote_retrieve_response_code($response)===404){
            $alternate=$mode==='query'?'pretty':'query';
            $retryUrl=$this->restUrl($baseUrl,$path,$alternate);if($query!==[])$retryUrl=add_query_arg($query,$retryUrl);
            $response=$this->safePinnedRequest($retryUrl,$args);
            if(!is_wp_error($response)&&wp_remote_retrieve_response_code($response)!==404)$this->rememberRestMode($baseUrl,$alternate);
        }
        if(is_wp_error($response)){$message=strtolower((string)$response->get_error_message());$timeout=str_contains($message,'timed out')||str_contains($message,'timeout')||str_contains($message,'operation timed out');return ['status'=>$timeout?504:502,'body'=>'','headers'=>[],'timeout'=>$timeout];}
        $responseHeaders=[];foreach(['content-type','x-wp-total','x-wp-totalpages'] as $name){$value=wp_remote_retrieve_header($response,$name);if($value!=='')$responseHeaders[$name]=$value;}
        return ['status'=>wp_remote_retrieve_response_code($response),'body'=>wp_remote_retrieve_body($response),'headers'=>$responseHeaders,'timeout'=>false];
    }

    private function requestRest(string $baseUrl,string $route,string $username,string $applicationPassword,string $mode='pretty')
    {
        return $this->safePinnedRequest($this->restUrl($baseUrl,$route,$mode),['timeout'=>10,'redirection'=>0,'headers'=>['Authorization'=>'Basic '.base64_encode($username.':'.$applicationPassword),'Accept'=>'application/json','User-Agent'=>'WooGit-Backend/'.WOOGIT_BACKEND_VERSION]]);
    }

    private function requestWooCommerce(string $url,string $consumerKey,string $consumerSecret,string $mode='pretty')
    {
        $path=(string)wp_parse_url($url,PHP_URL_PATH);$route=preg_replace('#^/wp-json#','',$path);$route=$route===''?'/':$route;
        $query=(string)wp_parse_url($url,PHP_URL_QUERY);
        $target=$this->restUrl($this->baseFromUrl($url),$route,$mode);
        if($query!=='')parse_str($query,$params);else $params=[];
        if($params!==[])$target=add_query_arg($params,$target);
        return $this->safePinnedRequest($target,['timeout'=>10,'redirection'=>0,'headers'=>['Authorization'=>'Basic '.base64_encode($consumerKey.':'.$consumerSecret),'Accept'=>'application/json','User-Agent'=>'WooGit-Backend/'.WOOGIT_BACKEND_VERSION]]);
    }

    private function restUrl(string $baseUrl,string $path,string $mode): string
    {
        $route=preg_replace('#^/wp-json#','',$path);$route=$route===''?'/':('/'.ltrim($route,'/'));
        if($mode==='query')return add_query_arg('rest_route',$route,rtrim($baseUrl,'/').'/');
        return rtrim($baseUrl,'/').'/wp-json'.($route==='/'?'/':$route);
    }

    private function baseFromUrl(string $url): string
    {
        $parts=wp_parse_url($url);return rtrim((string)($parts['scheme']??'https').'://'.(string)($parts['host']??''),'/');
    }

    private function restModeKey(string $baseUrl): string
    {
        $host=strtolower(rtrim((string)wp_parse_url($baseUrl,PHP_URL_HOST),'.'));return 'woogit_rest_mode_'.substr(hash('sha256',$host),0,32);
    }

    private function getRestMode(string $baseUrl): string
    {
        $mode=get_transient($this->restModeKey($baseUrl));return $mode==='query'?'query':'pretty';
    }

    private function rememberRestMode(string $baseUrl,string $mode): void
    {
        if($mode!=='query'&&$mode!=='pretty')return;
        set_transient($this->restModeKey($baseUrl),$mode,30*DAY_IN_SECONDS);
    }

    /** Resolve and validate the destination immediately before the HTTP call, then pin every validated public IP. */
    private function safePinnedRequest(string $url,array $args)
    {
        $destination=$this->resolvePublicDestination($url);
        if($destination===null)return new \WP_Error('unsafe_destination','Unsafe or unresolvable upstream destination.');
        if(!function_exists('curl_init'))return new \WP_Error('secure_transport_unavailable','Secure pinned proxy transport is unavailable.');
        $this->pinnedHost=$destination['host'];$this->pinnedIps=$destination['ips'];add_action('http_api_curl',[$this,'pinCurl'],10,3);
        try{return wp_safe_remote_request($url,$args);}finally{remove_action('http_api_curl',[$this,'pinCurl'],10);$this->pinnedHost=null;$this->pinnedIps=[];}
    }

    public function pinCurl($handle,array $parsedArgs,string $url): void{if($this->pinnedHost===null||$this->pinnedIps===[]||!defined('CURLOPT_RESOLVE'))return;$entries=[];foreach($this->pinnedIps as $ip)$entries[]=$this->pinnedHost.':443:'.$ip;curl_setopt($handle,CURLOPT_RESOLVE,$entries);}
    private function resolvePublicDestination(string $url): ?array
    {
        $parts=wp_parse_url($url);if(!$parts||strtolower((string)($parts['scheme']??''))!=='https')return null;$host=strtolower(rtrim((string)($parts['host']??''),'.'));if($host==='')return null;if(!empty($parts['user'])||!empty($parts['pass'])||(!empty($parts['port'])&&(int)$parts['port']!==443))return null;
        if(filter_var($host,FILTER_VALIDATE_IP)){if(!filter_var($host,FILTER_VALIDATE_IP,FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE))return null;return ['host'=>$host,'ips'=>[$host]];}
        if($host==='localhost'||str_ends_with($host,'.localhost')||str_ends_with($host,'.local'))return null;$records=[];foreach([DNS_A,DNS_AAAA] as $type){$resolved=@dns_get_record($host,$type);if(is_array($resolved))$records=array_merge($records,$resolved);}if($records===[])return null;
        $safe=[];foreach($records as $record){$ip=$record['ip']??($record['ipv6']??'');if($ip===''||!filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE))return null;$safe[]=$ip;}$safe=array_values(array_unique($safe));if($safe===[])return null;return ['host'=>$host,'ips'=>$safe];
    }
}
