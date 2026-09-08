<?php
if (!defined('ABSPATH')) exit;

function woogit_portal_data() {
  $user=woogit_current_user();
  if(is_wp_error($user) || empty($user)) return ['user'=>[],'billing'=>[],'payments'=>[],'authenticated'=>false];
  $status=woogit_api_get('billing/status');
  $accountId=(int)($status['account_id']??$user['account_id']??0);
  $siteId=(int)($status['site_id']??$user['site_id']??0);
  $payments=($accountId>0&&$siteId>0)?woogit_commerce_payment_history($accountId,$siteId):['orders'=>[],'page'=>1,'per_page'=>20,'total'=>0,'total_pages'=>0];
  return [
    'user'=>$user,
    'billing'=>is_wp_error($status)?[]:(array)($status['billing']??$status),
    'payments'=>$payments,
    'authenticated'=>true,
  ];
}

function woogit_plans() {
  $v=woogit_api_get('billing/plans');
  return is_wp_error($v)?[]:(array)($v['plans']??$v);
}
