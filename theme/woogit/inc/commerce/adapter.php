<?php
if (!defined('ABSPATH')) exit;

/**
 * Presentation adapter for WooCommerce installed on woogit.ir itself.
 * This is intentionally isolated from templates and never targets a customer store.
 */
function woogit_commerce_payment_history(int $account_id, int $site_id, int $page = 1, int $per_page = 20): array {
  $page = max(1, $page);
  $per_page = min(50, max(1, $per_page));
  $empty = ['orders'=>[], 'page'=>$page, 'per_page'=>$per_page, 'total'=>0, 'total_pages'=>0];
  if (!function_exists('wc_get_orders')) return $empty;

  $orders = wc_get_orders([
    'limit'=>$per_page,
    'page'=>$page,
    'paginate'=>true,
    'return'=>'objects',
    'orderby'=>'date',
    'order'=>'DESC',
    'meta_query'=>[
      ['key'=>'_woogit_account_id', 'value'=>(string)$account_id, 'compare'=>'='],
      ['key'=>'_woogit_site_id', 'value'=>(string)$site_id, 'compare'=>'='],
    ],
  ]);

  $items = [];
  foreach ((array)($orders->orders ?? []) as $order) {
    if (!is_object($order)) continue;
    $items[] = [
      'order_id'=>(int)$order->get_id(),
      'status'=>(string)$order->get_status(),
      'total'=>(string)$order->get_total(),
      'currency'=>(string)$order->get_currency(),
      'payment_method'=>(string)$order->get_payment_method(),
      'payment_method_title'=>(string)$order->get_payment_method_title(),
      'created_at'=>$order->get_date_created() ? $order->get_date_created()->date('c') : null,
    ];
  }

  return [
    'orders'=>$items,
    'page'=>$page,
    'per_page'=>$per_page,
    'total'=>(int)($orders->total ?? count($items)),
    'total_pages'=>(int)($orders->max_num_pages ?? ($items === [] ? 0 : 1)),
  ];
}

function woogit_commerce_payment_order(int $account_id, int $site_id, int $order_id): array {
  if ($account_id<=0 || $site_id<=0 || $order_id<=0 || !function_exists('wc_get_order')) return [];
  $order=wc_get_order($order_id);
  if(!is_object($order)) return [];
  if((int)$order->get_meta('_woogit_account_id')!==$account_id || (int)$order->get_meta('_woogit_site_id')!==$site_id) return [];
  return [
    'order_id'=>(int)$order->get_id(),
    'status'=>(string)$order->get_status(),
    'total'=>(string)$order->get_total(),
    'currency'=>(string)$order->get_currency(),
    'payment_method'=>(string)$order->get_payment_method(),
    'payment_method_title'=>(string)$order->get_payment_method_title(),
    'created_at'=>$order->get_date_created() ? $order->get_date_created()->date('c') : null,
  ];
}

/**
 * Deterministic payment-method selection: prefer the most recent paid order;
 * only fall back to the newest order when no paid order exists.
 */
function woogit_commerce_payment_method(array $payments): array {
  $orders = array_values(array_filter((array)($payments['orders'] ?? []), 'is_array'));
  foreach ($orders as $order) {
    if (in_array((string)($order['status'] ?? ''), ['processing','completed'], true)
      && (!empty($order['payment_method_title']) || !empty($order['payment_method']))) return $order;
  }
  foreach ($orders as $order) {
    if (!empty($order['payment_method_title']) || !empty($order['payment_method'])) return $order;
  }
  return [];
}

/**
 * Returns the canonical WooCommerce/Milo trial product for the public Theme.
 * This intentionally bypasses WooGit BillingService so App billing contracts stay untouched.
 * Supports the current V1 shape: zero price + 15-day subscription, and Milo's native
 * 15-day free-trial metadata when present.
 */
function woogit_commerce_trial_plan(): array {
  if (!function_exists('wc_get_products')) return [];
  $products = wc_get_products(['status'=>'publish','limit'=>100,'return'=>'objects','orderby'=>'menu_order','order'=>'ASC']);
  $fallback = null;
  foreach ((array)$products as $product) {
    if (!is_object($product) || !method_exists($product,'get_type')) continue;
    $type = strtolower((string)$product->get_type());
    if (strpos($type,'subscription') === false) continue;
    $enabled = get_post_meta((int)$product->get_id(), '_woogit_plan_enabled', true);
    if ($enabled !== '' && !in_array($enabled,['yes','1'],true)) continue;
    if ((float)$product->get_price() !== 0.0) continue;
    $trialLength=(int)$product->get_meta('_subscription_trial_length');
    $period=strtolower((string)$product->get_meta('_subscription_period'));
    $interval=max(1,(int)($product->get_meta('_subscription_period_interval') ?: 1));
    $is15Day=$trialLength===15 || ($period==='day' && $interval===15);
    if (!$is15Day) continue;
    $key=sanitize_title((string)get_post_meta((int)$product->get_id(), '_woogit_plan_key', true));
    $name=strtolower((string)$product->get_name());
    if ($key==='trial' || $key==='free-t' || $key==='free_t' || strpos($name,'trial')!==false || strpos($name,'آزمایشی')!==false) {
      $fallback=$product;
      break;
    }
    if ($fallback===null) $fallback=$product;
  }
  if (!$fallback) return [];
  $id=(int)$fallback->get_id();
  $url=function_exists('get_permalink') ? (string)get_permalink($id) : '';
  return [
    'id'=>$id,
    'key'=>sanitize_title((string)get_post_meta($id,'_woogit_plan_key',true)) ?: 'trial',
    'name'=>(string)$fallback->get_name(),
    'price'=>'0',
    'regular_price'=>(string)$fallback->get_regular_price(),
    'currency'=>function_exists('get_woocommerce_currency') ? (string)get_woocommerce_currency() : '',
    'billing_period'=>(string)$fallback->get_meta('_subscription_period'),
    'billing_interval'=>(int)($fallback->get_meta('_subscription_period_interval') ?: 1),
    'description'=>wp_strip_all_tags((string)$fallback->get_short_description()),
    'type'=>(string)$fallback->get_type(),
    'requires_variation'=>false,
    'trial_days'=>15,
    'web_checkout_url'=>$url,
  ];
}
