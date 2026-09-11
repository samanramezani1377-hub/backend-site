<?php
if (!defined('ABSPATH')) exit;

function woogit_portal_data() {
  $user=woogit_current_user();
  if(is_wp_error($user) || empty($user)) return ['user'=>[],'billing'=>[],'payments'=>[],'authenticated'=>false,'payment_method'=>[],'site'=>[]];
  $status=woogit_api_get('billing/status');
  $status_data=is_wp_error($status)?[]:(array)$status;
  $billing=(array)($status_data['billing']??$status_data);
  $accountId=(int)($status_data['account_id']??$user['account_id']??0);
  $siteId=(int)($status_data['site_id']??$user['site_id']??0);
  $page=max(1,absint($_GET['payment_page']??1));
  $payments=($accountId>0&&$siteId>0)?woogit_commerce_payment_history($accountId,$siteId,$page,20):['orders'=>[],'page'=>$page,'per_page'=>20,'total'=>0,'total_pages'=>0];
  $paymentMethod=woogit_commerce_payment_method($payments);
  $site=(array)($status_data['site']??$user['site']??[]);
  return ['user'=>(array)$user,'billing'=>$billing,'payments'=>$payments,'payment_method'=>$paymentMethod,'site'=>$site,'authenticated'=>true];
}

function woogit_plans() {
  $v=woogit_api_get('billing/plans');
  return is_wp_error($v)?[]:(array)($v['plans']??$v);
}

function woogit_portal_value(array $data, array $keys, $fallback='') {
  foreach($keys as $key){
    $parts=explode('.',$key); $value=$data;
    foreach($parts as $part){ if(!is_array($value)||!array_key_exists($part,$value)){$value=null;break;} $value=$value[$part]; }
    if($value!==null && $value!=='') return $value;
  }
  return $fallback;
}

function woogit_portal_status($value, $fallback='نامشخص') {
  $key=sanitize_key((string)$value);
  $map=[
    'active'=>['فعال','success'],'enabled'=>['فعال','success'],'paid'=>['پرداخت‌شده','success'],'completed'=>['تکمیل‌شده','success'],'success'=>['موفق','success'],'connected'=>['متصل','success'],
    'pending'=>['در انتظار','warning'],'processing'=>['در حال پردازش','warning'],'on-hold'=>['در انتظار','warning'],'trial'=>['آزمایشی','warning'],
    'none'=>['بدون اشتراک','muted'],
    'inactive'=>['غیرفعال','muted'],'disabled'=>['غیرفعال','muted'],'cancelled'=>['لغوشده','danger'],'canceled'=>['لغوشده','danger'],'failed'=>['ناموفق','danger'],'error'=>['خطا','danger'],'expired'=>['منقضی‌شده','danger'],'disconnected'=>['قطع‌شده','danger']
  ];
  return $map[$key]??[(string)($value!==''?$value:$fallback),'muted'];
}
