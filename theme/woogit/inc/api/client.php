<?php
if (!defined('ABSPATH')) exit;
function woogit_api_request($method, $path, $body=null) {
  $allowed = ['GET','POST']; $method = strtoupper($method); if (!in_array($method,$allowed,true)) return new WP_Error('invalid_method');
  $url = trailingslashit(rest_url('woogit/v1')) . ltrim($path,'/');
  $headers = ['Accept'=>'application/json','X-WooGit-Client'=>'web','X-WooGit-Client-Version'=>WOOGIT_THEME_VERSION,'X-WP-Nonce'=>wp_create_nonce('wp_rest')];
  $session = woogit_web_session(); if ($session) $headers['X-WooGit-Web-Session'] = $session;
  $args = ['method'=>$method,'headers'=>$headers,'timeout'=>15,'redirection'=>2,'sslverify'=>true];
  if ($body !== null) { $args['headers']['Content-Type']='application/json'; $args['body']=wp_json_encode($body); }
  $response = wp_remote_request($url,$args);
  if (is_wp_error($response)) return $response;
  $code=(int)wp_remote_retrieve_response_code($response); $raw=wp_remote_retrieve_body($response); $data=json_decode($raw,true);
  if ($code >= 400) { $err = is_array($data) && isset($data['code']) ? sanitize_key($data['code']) : 'api_error'; return new WP_Error($err, 'WooGit request failed', ['status'=>$code,'data'=>$data]); }
  return is_array($data) ? $data : [];
}
function woogit_api_get($path) { return woogit_api_request('GET',$path); }
function woogit_api_post($path,$body=[]) { return woogit_api_request('POST',$path,$body); }
