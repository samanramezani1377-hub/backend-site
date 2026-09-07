<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class IdentityService
{
    private const WEB_PASSWORD_META = '_woogit_web_password_configured';

    public function getUserId(int $accountId): int
    {
        global $wpdb; $table=$wpdb->prefix.'woogit_accounts';
        return (int)$wpdb->get_var($wpdb->prepare("SELECT wp_user_id FROM {$table} WHERE id=%d LIMIT 1",$accountId));
    }
    public function getUser(int $accountId): ?\WP_User
    {
        $id=$this->getUserId($accountId); if($id<=0)return null;
        $user=get_userdata($id); return $user instanceof \WP_User?$user:null;
    }
    /** A linked WP user is the single Web/WooCommerce identity for this Account. */
    public function isPasswordConfigured(int $accountId): bool
    {
        $user=$this->getUser($accountId);
        return $user instanceof \WP_User && get_user_meta($user->ID,self::WEB_PASSWORD_META,true)==='1';
    }
    /** Create the central WooCommerce customer identity after successful Site verification. */
    public function createCustomer(): int
    {
        $login='woogit_'.strtolower(wp_generate_password(24,false,false));
        $password=wp_generate_password(48,true,true);
        $userId=wp_insert_user(wp_slash(['user_login'=>$login,'user_pass'=>$password,'user_email'=>'','role'=>'customer','display_name'=>$login]));
        if(is_wp_error($userId)||!$userId)return 0;
        $user=get_userdata((int)$userId);
        if($user instanceof \WP_User)$user->set_role('customer');
        return (int)$userId;
    }
    public function verifyPassword(int $accountId,string $password): bool
    {
        $user=$this->getUser($accountId);
        return $this->isPasswordConfigured($accountId) && $user instanceof \WP_User && wp_check_password($password,$user->user_pass,$user->ID);
    }
    public function setPassword(int $accountId,string $password): bool
    {
        if(strlen($password)<12||strlen($password)>256)return false;
        $user=$this->getUser($accountId); if(!$user)return false;
        wp_set_password($password,$user->ID);
        update_user_meta($user->ID,self::WEB_PASSWORD_META,'1');
        return true;
    }
}
