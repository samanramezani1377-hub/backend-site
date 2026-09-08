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
