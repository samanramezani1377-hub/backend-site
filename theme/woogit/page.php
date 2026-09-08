<?php
if (!defined('ABSPATH')) exit;

$slug = get_post_field('post_name', get_queried_object_id());

$portal_templates = [
  'portal' => 'overview',
  'subscription' => 'subscription',
  'billing' => 'billing',
  'payments' => 'payments',
  'connected-site' => 'connected-site',
  'account-security' => 'account-security',
];

if (isset($portal_templates[$slug])) {
  if (!woogit_logged_in()) {
    wp_safe_redirect(woogit_page_url('login'));
    exit;
  }
  get_template_part('templates/portal/' . $portal_templates[$slug]);
  return;
}

if ($slug === 'login') {
  get_template_part('templates/auth/login');
  return;
}

if ($slug === 'register') {
  get_template_part('templates/auth/register');
  return;
}

if ($slug === 'payment-result') {
  get_template_part('templates/public/payment-result');
  return;
}

if ($slug === 'pricing') {
  get_template_part('page', 'pricing');
  return;
}

get_template_part('template-parts/public-page');
