<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class OperationService
{
    public function create(int $accountId, int $siteId, string $key, string $fingerprint, string $resource, string $path, string $method): ?array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'woogit_operations';
        $operationId = 'wgo_' . bin2hex(random_bytes(16));
        return $this->insert($accountId, $siteId, $key, $fingerprint, $resource, $path, $method, $operationId);
    }

    private function insert(int $accountId, int $siteId, string $key, string $fingerprint, string $resource, string $path, string $method, string $operationId): ?array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'woogit_operations';
        $now = current_time('mysql', true);
        $ok = $wpdb->insert($table, [
            'operation_id'=>$operationId,'account_id'=>$accountId,'site_id'=>$siteId,
            'idempotency_key'=>$key,'request_fingerprint'=>$fingerprint,'resource'=>$resource,
            'operation_path'=>$path,'method'=>strtoupper($method),'status'=>'pending',
            'created_at'=>$now,'updated_at'=>$now,'expires_at'=>gmdate('Y-m-d H:i:s', time()+86400),
        ], ['%s','%d','%d','%s','%s','%s','%s','%s','%s','%s','%s','%s']);
        return $ok ? ['operation_id'=>$operationId,'status'=>'pending'] : null;
    }

    public function deletePending(int $accountId, int $siteId, string $operationId): void
    {
        global $wpdb;
        $wpdb->delete($wpdb->prefix.'woogit_operations', ['account_id'=>$accountId,'site_id'=>$siteId,'operation_id'=>$operationId,'status'=>'pending'], ['%d','%d','%s','%s']);
    }

    public function update(int $accountId, int $siteId, string $operationId, string $status, int $upstreamStatus, array $body): bool
    {
        global $wpdb;
        return false !== $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}woogit_operations SET status=%s,upstream_status=%d,response_body=%s,updated_at=%s WHERE operation_id=%s AND account_id=%d AND site_id=%d AND status='pending'",$status,$upstreamStatus,wp_json_encode($body,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),current_time('mysql',true),$operationId,$accountId,$siteId));
    }

    public function markUnknown(int $accountId, int $siteId, string $operationId): bool
    {
        global $wpdb;
        return false !== $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}woogit_operations SET status='unknown',updated_at=%s WHERE operation_id=%s AND account_id=%d AND site_id=%d AND status='pending'",current_time('mysql',true),$operationId,$accountId,$siteId));
    }

    public function getForAccount(int $accountId, int $siteId, string $operationId): ?array
    {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare("SELECT operation_id,account_id,site_id,resource,operation_path,method,status,upstream_status,response_body,created_at,updated_at,expires_at FROM {$wpdb->prefix}woogit_operations WHERE account_id=%d AND site_id=%d AND operation_id=%s LIMIT 1", $accountId, $siteId, $operationId), ARRAY_A);
        if (!$row) return null;
        $body = $row['response_body'] !== null ? json_decode($row['response_body'], true) : null;
        $row['upstream_status'] = $row['upstream_status'] !== null ? (int)$row['upstream_status'] : null;
        $row['response_body'] = is_array($body) ? $body : null;
        return $row;
    }
}
