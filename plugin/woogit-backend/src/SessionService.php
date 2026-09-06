<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class SessionService
{
    public function authenticate(string $token): ?array
    {
        $token = trim($token);
        if ($token === '') return null;
        global $wpdb;
        $table = $wpdb->prefix . 'woogit_sessions';
        $hash = hash('sha256', $token);
        $now = current_time('mysql', true);
        $session = $wpdb->get_row($wpdb->prepare("SELECT id, account_id, site_id, expires_at, revoked_at FROM {$table} WHERE token_hash = %s LIMIT 1", $hash), ARRAY_A);
        if (!$session || $session['revoked_at'] !== null || $session['expires_at'] <= $now) return null;
        return $session;
    }

    public function issue(int $accountId, int $siteId, int $ttlSeconds = 86400): ?string
    {
        if ($ttlSeconds < 300) return null;
        global $wpdb;
        $table = $wpdb->prefix . 'woogit_sessions';
        $token = 'wgs_' . bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);
        $now = current_time('mysql', true);
        $expires = gmdate('Y-m-d H:i:s', time() + $ttlSeconds);
        $ok = $wpdb->insert($table, ['account_id'=>$accountId,'site_id'=>$siteId,'token_hash'=>$hash,'expires_at'=>$expires,'created_at'=>$now], ['%d','%d','%s','%s','%s']);
        return $ok ? $token : null;
    }

    public function revoke(string $token): bool
    {
        $token = trim($token);
        if ($token === '') return false;
        global $wpdb;
        $table = $wpdb->prefix . 'woogit_sessions';
        return false !== $wpdb->update($table, ['revoked_at'=>current_time('mysql', true)], ['token_hash'=>hash('sha256',$token)], ['%s'], ['%s']);
    }
}
