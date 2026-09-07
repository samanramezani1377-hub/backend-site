<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class AccountService
{
    public function get(int $accountId): ?array
    {
        global $wpdb;$table=$wpdb->prefix.'woogit_accounts';$row=$wpdb->get_row($wpdb->prepare("SELECT id,email,wp_user_id,web_password_hash,status FROM {$table} WHERE id=%d LIMIT 1",$accountId),ARRAY_A);return ($row&&$row['status']==='active')?$row:null;
    }
    /** Email is contact metadata; identity is the linked WordPress/WooCommerce customer user. */
    public function create(string $email=''): ?array
    {
        $email=sanitize_email($email);if($email!==''&&!is_email($email))return null;global $wpdb;$table=$wpdb->prefix.'woogit_accounts';$now=current_time('mysql',true);
        $ok=$wpdb->insert($table,['email'=>$email!==''?$email:null,'wp_user_id'=>null,'web_password_hash'=>null,'status'=>'active','created_at'=>$now,'updated_at'=>$now],['%s','%d','%s','%s','%s','%s']);if(!$ok)return null;return ['id'=>(int)$wpdb->insert_id,'email'=>$email,'wp_user_id'=>0,'web_password_hash'=>null,'status'=>'active'];
    }
    public function updateContactEmail(int $accountId,string $email): bool
    {
        $email=sanitize_email($email);if($email!==''&&!is_email($email))return false;global $wpdb;$table=$wpdb->prefix.'woogit_accounts';$ok=false!==$wpdb->update($table,['email'=>$email!==''?$email:null,'updated_at'=>current_time('mysql',true)],['id'=>$accountId],['%s','%s'],['%d']);if(!$ok)return false;$identity=new IdentityService();if($email!==''&&$identity->getUser($accountId))return $identity->updateEmail($accountId,$email);return true;
    }
    public function hasWebPassword(int $accountId): bool
    {
        return (new IdentityService())->getUser($accountId) instanceof \WP_User;
    }
    public function setWebPassword(int $accountId,string $password): bool
    {
        return (new IdentityService())->setPassword($accountId,$password);
    }
    public function verifyWebPassword(int $accountId,string $password): bool
    {
        return (new IdentityService())->verifyPassword($accountId,$password);
    }
    public function deleteIfEmpty(int $accountId): void
    {
        global $wpdb;$accounts=$wpdb->prefix.'woogit_accounts';$sites=$wpdb->prefix.'woogit_sites';$hasSite=$wpdb->get_var($wpdb->prepare("SELECT id FROM {$sites} WHERE account_id=%d LIMIT 1",$accountId));if(!$hasSite)$wpdb->delete($accounts,['id'=>$accountId],['%d']);
    }
}
