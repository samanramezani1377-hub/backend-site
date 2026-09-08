<?php
if (!defined('ABSPATH')) exit;
function woogit_api_request($method,$path,$body=null,array $extra_headers=[]) {
  $method=strtoupper($method); if(!in_array($method,['GET','POST'],true))return new WP_Error('invalid_method');
  $url=trailingslashit(rest_url('woogit/v1')).ltrim($path,'/');
  $headers=array_merge(['Accept'=>'application/json','X-WooGit-Client'=>'web','X-WooGit-Client-Version'=>WOOGIT_THEME_VERSION],$extra_headers);
  $session=woogit_web_session(); if($session)$headers['X-WooGit-Web-Session']=$session;
  $args=['method'=>$method,'headers'=>$headers,'timeout'=>15,'redirection'=>2,'sslverify'=>true];
  if($body!==null){$args['headers']['Content-Type']='application/json';$args['body']=wp_json_encode($body);}
  $response=wp_remote_request($url,$args); if(is_wp_error($response))return $response;
  $code=(int)wp_remote_retrieve_response_code($response);$raw=wp_remote_retrieve_body($response);$data=json_decode($raw,true);
  if($code>=400){$err=is_array($data)&&isset($data['code'])?sanitize_key($data['code']):'api_error';return new WP_Error($err,is_array($data)&&isset($data['message'])?sanitize_text_field($data['message']):'WooGit request failed',['status'=>$code,'data'=>$data]);}
  return is_array($data)?$data:[];
}
function woogit_api_get($path,array $headers=[]){return woogit_api_request('GET',$path,null,$headers);}
function woogit_api_post($path,$body=[],array $headers=[]){return woogit_api_request('POST',$path,$body,$headers);}
