<?php
if (!defined('ABSPATH')) exit;
function woogit_portal_data() {
  $user=woogit_current_user();
  $status=woogit_api_get('billing/status');
  return ['user'=>is_wp_error($user)?[]:$user,'billing'=>is_wp_error($status)?[]:$status];
}
function woogit_plans() { $v=woogit_api_get('billing/plans'); return is_wp_error($v)?[]:$v; }
