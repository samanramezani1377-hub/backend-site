<?php
if (!defined('ABSPATH')) exit;
function woogit_theme_management_menu() { add_theme_page('WooGit Theme','WooGit Theme','manage_options','woogit-theme','woogit_render_theme_management'); }
function woogit_render_theme_management() { if(!current_user_can('manage_options')) return; echo '<div class="wrap"><h1>WooGit Theme</h1><p>Presentation and content settings only. Billing, entitlement and payment truth remain external.</p><form method="post" action="options.php">'; settings_fields('woogit_theme'); do_settings_sections('woogit-theme'); submit_button(); echo '</form></div>'; }
function woogit_ajax_save_theme_setting() { check_ajax_referer('woogit_theme_admin'); if(!current_user_can('manage_options')) wp_send_json_error(['code'=>'forbidden'],403); $key=sanitize_key($_POST['key']??''); $value=sanitize_text_field(wp_unslash($_POST['value']??'')); $o=woogit_theme_options(); $o[$key]=$value; update_option('woogit_theme_options',$o,false); wp_send_json_success(); }
