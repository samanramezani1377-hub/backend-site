<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class VersionAdmin
{
    private const OPTION = 'woogit_backend_version_policy';
    private const VERSION_PATTERN = '/^\d+(?:\.\d+){0,3}(?:[-+][0-9A-Za-z.-]+)?$/';

    public function register(): void
    {
        add_submenu_page('woogit','WooGit App Versions','App Versions','manage_options','woogit-app-versions',[$this,'render']);
    }

    public function render(): void
    {
        if (!current_user_can('manage_options')) wp_die(esc_html__('You do not have permission to manage WooGit app versions.', 'woogit-backend'));
        $policy=get_option(self::OPTION,[]);$policy=is_array($policy)?array_merge($this->defaults(),$policy):$this->defaults();$message='';$error='';
        if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['woogit_version_policy_save'])){check_admin_referer('woogit_version_policy_save');[$valid,$normalized,$error]=$this->validateAndNormalize($_POST);if($valid){if(update_option(self::OPTION,$normalized,false)){$policy=$normalized;$message='Version policy saved.';}else{$current=get_option(self::OPTION,[]);$current=is_array($current)?array_merge($this->defaults(),$current):$this->defaults();if($current===$normalized){$policy=$normalized;$message='Version policy is already up to date.';}else{$error='The version policy could not be saved.';}}}}
