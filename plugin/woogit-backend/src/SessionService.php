<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class SessionService
{
    public const SCOPE_BILLING = 'billing';
    public const SCOPE_OPERATIONAL = 'operational';
    private const BILLING_TTL_SECONDS = 86400;
    private const ACTIVATION_LOCK_TIMEOUT = 5;

    public function authenticate(string $token): ?array
    {
        $token = trim($token);
        if ($token === '') return null;
        global $wpdb;
        $table = $wpdb->prefix . 'woogit_sessions';
        $hash = hash('sha256', $token);
        $now = current_time('mysql', true);
        $session = $wpdb->get_row($wpdb->prepare("SELECT id, account_id, site_id, scope, expires_at, revoked_at FROM {$table} WHERE token_hash = %s LIMIT 1", $hash), ARRAY_A);
        if (!$session || $session['revoked_at'] !== null || $session['expires_at'] <= $now) return null;
        return $session;
    }

    public function issueBilling(int $accountId, int $siteId): ?string
    {
        return $this->issue($accountId, $siteId, self::SCOPE_BILLING, self::BILLING_TTL_SECONDS);
    }

    public function issueOperational(int $accountId, int $siteId, int $entitlementExpiresAt): ?string
    {
        $remaining = $entitlementExpiresAt - time();
        if ($remaining < 300) return null;
        return $this->issue($accountId, $siteId, self::SCOPE_OPERATIONAL, $remaining);
    }

    public function activateOperationalFromBilling(int $accountId, int $siteId, int $entitlementExpiresAt): ?string
    {
        $remaining=$entitlementExpiresAt-time();
        if($remaining<300)return null;
        global $wpdb;
        $table=$wpdb->prefix.'woogit_sessions';
        $lockName='woogit.activate.'.$accountId.'.'.$siteId;
        $lock=(int)$wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s,%d)', $lockName, self::ACTIVATION_LOCK_TIMEOUT));
        if($lock!==1)return null;
        try{
            if($wpdb->query('START TRANSACTION')===false)return null;
            $billing=$wpdb->get_row($wpdb->prepare("SELECT id FROM {$table} WHERE account_id=%d AND site_id=%d AND scope=%s AND revoked_at IS NULL AND expires_at > %s ORDER BY id DESC LIMIT 1 FOR UPDATE",$accountId,$siteId,self::SCOPE_BILLING,current_time('mysql',true)),ARRAY_A);
            if(!$billing){$wpdb->query('ROLLBACK');return null;}
            $token='wgs_'.bin2hex(random_bytes(32));
            $hash=hash('sha256',$token);$now=current_time('mysql',true);$expires=gmdate('Y-m-d H:i:s',$entitlementExpiresAt);
            $inserted=$wpdb->insert($table,['account_id'=>$accountId,'site_id'=>$siteId,'scope'=>self::SCOPE_OPERATIONAL,'token_hash'=>$hash,'expires_at'=>$expires,'created_at'=>$now],['%d','%d','%s','%s','%s','%s']);
            if(!$inserted){$wpdb->query('ROLLBACK');return null;}
            $revoked=$wpdb->query($wpdb->prepare("UPDATE {$table} SET revoked_at=%s WHERE account_id=%d AND site_id=%d AND scope=%s AND revoked_at IS NULL",$now,$accountId,$siteId,self::SCOPE_BILLING));
            if($revoked===false){$wpdb->query('ROLLBACK');return null;}
            if($wpdb->query('COMMIT')===false){$wpdb->query('ROLLBACK');return null;}
            return $token;
        }finally{
            $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lockName));
        }
    }

    public function reconcileOperational(int $accountId, int $siteId, int $entitlementExpiresAt): int
    {
        if ($entitlementExpiresAt <= time()) return 0;
        global $wpdb;
        $table = $wpdb->prefix . 'woogit_sessions';
        $newExpiry = gmdate('Y-m-d H:i:s', $entitlementExpiresAt);
        return (int)$wpdb->query($wpdb->prepare(
            "UPDATE {$table} SET expires_at=%s WHERE account_id=%d AND site_id=%d AND scope=%s AND revoked_at IS NULL AND expires_at < %s",
            $newExpiry, $accountId, $siteId, self::SCOPE_OPERATIONAL, $newExpiry
        ));
    }

    public function revoke(string $token): bool
    {
        $token = trim($token);
        if ($token === '') return false;
        global $wpdb;
        $table = $wpdb->prefix . 'woogit_sessions';
        return false !== $wpdb->update($table, ['revoked_at'=>current_time('mysql', true)], ['token_hash'=>hash('sha256',$token)], ['%s'], ['%s']);
    }

    public function revokeScope(int $accountId, int $siteId, string $scope): int
    {
        global $wpdb;
        $table = $wpdb->prefix . 'woogit_sessions';
        return (int)$wpdb->query($wpdb->prepare(
            "UPDATE {$table} SET revoked_at=%s WHERE account_id=%d AND site_id=%d AND scope=%s AND revoked_at IS NULL",
            current_time('mysql', true), $accountId, $siteId, $scope
        ));
    }

    private function issue(int $accountId, int $siteId, string $scope, int $ttlSeconds): ?string
    {
        if ($ttlSeconds < 300 || !in_array($scope, [self::SCOPE_BILLING, self::SCOPE_OPERATIONAL], true)) return null;
        global $wpdb;
        $table = $wpdb->prefix . 'woogit_sessions';
        $token = 'wgs_' . bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);
        $now = current_time('mysql', true);
        $expires = gmdate('Y-m-d H:i:s', time() + $ttlSeconds);
        $ok = $wpdb->insert($table, ['account_id'=>$accountId,'site_id'=>$siteId,'scope'=>$scope,'token_hash'=>$hash,'expires_at'=>$expires,'created_at'=>$now], ['%d','%d','%s','%s','%s','%s']);
        return $ok ? $token : null;
    }
}
