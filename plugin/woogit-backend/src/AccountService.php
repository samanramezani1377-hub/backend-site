<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class AccountService
{
    public function get(int $accountId): ?array
    {
        global $wpdb;$table=$wpdb->prefix.'woogit_accounts';$row=$wpdb->get_row($wpdb->prepare("SELECT id,email,web_password_hash,status FROM {$table} WHERE id=%d LIMIT 1",$accountId),ARRAY_A);return ($row&&$row['status']==='active')?$row:null;
    }

    /** Email is contact metadata only; it is never used to resolve Account identity. */
    public function create(string $email=''): ?array
    {
        $email=sanitize_email($email);if($email!==''&&!is_email($email))return null;global $wpdb;$table=$wpdb->prefix.'woogit_accounts';$now=current_time('mysql',true);
        $ok=$wpdb->insert($table,['email'=>$email!==''?$email:null,'web_password_hash'=>null,'status'=>'active','created_at'=>$now,'updated_at'=>$now],['%s','%s','%s','%s','%s']);if(!$ok)return null;return ['id'=>(int)$wpdb->insert_id,'email'=>$email,'web_password_hash'=>null,'status'=>'active'];
    }

    public function updateContactEmail(int $accountId,string $email): bool
    {
        $email=sanitize_email($email);if($email!==''&&!is_email($email))return false;global $wpdb;$table=$wpdb->prefix.'woogit_accounts';return false!==$wpdb->update($table,['email'=>$email!==''?$email:null,'updated_at'=>current_time('mysql',true)],['id'=>$accountId],['%s','%s'],['%d']);
    }

    public function hasWebPassword(int $accountId): bool
    {
        $account=$this->get($accountId);return $account!==null&&is_string($account['web_password_hash'])&&$account['web_password_hash']!=='';
    }

    public function setWebPassword(int $accountId,string $password): bool
    {
        if(strlen($password)<12||strlen($password)>256)return false;
        $hash=password_hash($password,PASSWORD_ARGON2ID);
        if(!is_string($hash)||$hash==='')return false;
        global $wpdb;$table=$wpdb->prefix.'woogit_accounts';
        return false!==$wpdb->update($table,['web_password_hash'=>$hash,'updated_at'=>current_time('mysql',true)],['id'=>$accountId],['%s','%s'],['%d']);
    }

    public function verifyWebPassword(int $accountId,string $password): bool
    {
        $account=$this->get($accountId);if(!$account||!is_string($account['web_password_hash'])||$account['web_password_hash']==='')return false;
        $ok=password_verify($password,$account['web_password_hash']);
        if($ok&&password_needs_rehash($account['web_password_hash'],PASSWORD_ARGON2ID))$this->setWebPassword($accountId,$password);
        return $ok;
    }

    public function deleteIfEmpty(int $accountId): void
    {
        global $wpdb;$accounts=$wpdb->prefix.'woogit_accounts';$sites=$wpdb->prefix.'woogit_sites';
        $hasSite=$wpdb->get_var($wpdb->prepare("SELECT id FROM {$sites} WHERE account_id=%d LIMIT 1",$accountId));
        if(!$hasSite)$wpdb->delete($accounts,['id'=>$accountId],['%d']);
    }
}
