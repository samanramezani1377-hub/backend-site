<?php
namespace WooGit\\Backend;

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
}
