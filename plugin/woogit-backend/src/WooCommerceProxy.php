<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class WooCommerceProxy
{
    public function verify(string $baseUrl,string $username,string $applicationPassword,string $consumerKey,string $consumerSecret): array
    {
        $wp = $this->request($baseUrl.'/wp-json/',$username,$applicationPassword);
        if (is_wp_error($wp)) return ['ok'=>false,'reason'=>'wordpress_unreachable'];
        $wpStatus = wp_remote_retrieve_response_code($wp);
        if ($wpStatus < 200 || $wpStatus >= 400) return ['ok'=>false,'reason'=>'wordpress_auth_failed'];
        $wcUrl = add_query_arg(['consumer_key'=>$consumerKey,'consumer_secret'=>$consumerSecret,'per_page'=>1],$baseUrl.'/wp-json/wc/v3/products');
        $wc = $this->request($wcUrl,$username,$applicationPassword);
        if (is_wp_error($wc)) return ['ok'=>false,'reason'=>'woocommerce_unreachable'];
        $wcStatus = wp_remote_retrieve_response_code($wc);
        if ($wcStatus < 200 || $wcStatus >= 300) return ['ok'=>false,'reason'=>'woocommerce_auth_failed'];
        return ['ok'=>true];
    }

    public function forward(string $baseUrl,string $path,string $method,string $username,string $applicationPassword,string $consumerKey,string $consumerSecret,array $query,?array $body): array
    {
        $url=$baseUrl.$path;
        $query['consumer_key']=$consumerKey; $query['consumer_secret']=$consumerSecret;
        if($query!==[])$url=add_query_arg($query,$url);
        $headers=['Authorization'=>'Basic '.base64_encode($username.':'.$applicationPassword),'Accept'=>'application/json','Content-Type'=>'application/json','User-Agent'=>'WooGit-Backend/'.WOOGIT_BACKEND_VERSION];
        $args=['method'=>strtoupper($method),'timeout'=>20,'redirection'=>2,'headers'=>$headers,'data_format'=>'body'];
        if($body!==null&&in_array(strtoupper($method),['POST','PUT','PATCH'],true))$args['body']=wp_json_encode($body,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        $response=wp_safe_remote_request($url,$args);
        if(is_wp_error($response))return ['status'=>502,'body'=>['code'=>'upstream_unreachable']];
        $status=wp_remote_retrieve_response_code($response); $raw=wp_remote_retrieve_body($response); $decoded=json_decode($raw,true);
        return ['status'=>$status,'body'=>json_last_error()===JSON_ERROR_NONE?$decoded:['raw'=>$raw]];
    }

    private function request(string $url,string $username,string $applicationPassword)
    {
        return wp_safe_remote_get($url,['timeout'=>10,'redirection'=>2,'headers'=>['Authorization'=>'Basic '.base64_encode($username.':'.$applicationPassword),'Accept'=>'application/json','User-Agent'=>'WooGit-Backend/'.WOOGIT_BACKEND_VERSION]]);
    }
}
