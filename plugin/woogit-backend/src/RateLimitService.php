<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class RateLimitService
{
    public function check(string $bucket, string $key, int $limit, int $windowSeconds): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'woogit_rate_limits';
        $now = time();
        $window = (int) floor($now / $windowSeconds) * $windowSeconds;
        $windowStart = gmdate('Y-m-d H:i:s', $window);
        $windowEnd = $window + $windowSeconds;
        $timestamp = gmdate('Y-m-d H:i:s', $now);
        $identifier = hash('sha256', $bucket . ':' . $key);

        // The unique key includes window_start, so a duplicate can only be
        // the current window. Keep the counter update atomic and avoid
        // rewriting updated_at on every hit; cleanup is window-based.
        $sql = $wpdb->prepare(
            "INSERT INTO {$table} (bucket,identifier,window_start,hits,created_at,updated_at)
             VALUES (%s,%s,%s,1,%s,%s)
             ON DUPLICATE KEY UPDATE hits = hits + 1",
            $bucket, $identifier, $windowStart, $timestamp, $timestamp
        );
        if (false === $wpdb->query($sql)) {
            return ['allowed'=>false,'retry_after'=>min($windowSeconds,60),'remaining'=>0,'error'=>true];
        }

        // Keep the read because WordPress supports a broad MySQL/MariaDB
        // range and portable INSERT ... RETURNING is not available across
        // the supported engines. This preserves exact remaining semantics.
        $hits = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT hits FROM {$table} WHERE bucket=%s AND identifier=%s AND window_start=%s LIMIT 1",
            $bucket, $identifier, $windowStart
        ));
        $allowed = $hits <= $limit;
        return [
            'allowed'=>$allowed,
            'retry_after'=>max(1,$windowEnd-$now),
            'remaining'=>max(0,$limit-$hits),
            'error'=>false,
        ];
    }
}
