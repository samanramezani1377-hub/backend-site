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
        $identifier = hash('sha256', $bucket . ':' . $key);

        $sql = $wpdb->prepare(
            "INSERT INTO {$table} (bucket,identifier,window_start,hits,created_at,updated_at)
             VALUES (%s,%s,%s,1,%s,%s)
             ON DUPLICATE KEY UPDATE hits = IF(window_start=%s, hits+1, 1), window_start=%s, updated_at=%s",
            $bucket, $identifier, $windowStart, gmdate('Y-m-d H:i:s'), gmdate('Y-m-d H:i:s'),
            $windowStart, $windowStart, gmdate('Y-m-d H:i:s')
        );
        if (false === $wpdb->query($sql)) {
            return ['allowed'=>false,'retry_after'=>min($windowSeconds,60),'remaining'=>0,'error'=>true];
        }

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
