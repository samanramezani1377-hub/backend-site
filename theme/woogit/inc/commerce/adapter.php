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
      'transaction_id'=>(string)$order->get_transaction_id(),
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
    'transaction_id'=>(string)$order->get_transaction_id(),
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
