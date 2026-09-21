<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

/**
 * Server-side CafeBazaar Developer API integration for WooGit subscriptions.
 *
 * Credentials must be supplied outside the repository through wp-config.php:
 * WOOGIT_BAZAAR_CLIENT_ID
 * WOOGIT_BAZAAR_CLIENT_SECRET
 * WOOGIT_BAZAAR_REFRESH_TOKEN
 * WOOGIT_BAZAAR_PACKAGE_NAME (defaults to the WooGit package)
 */
final class BazaarService
{
    private const API_BASE = 'https://pardakht.cafebazaar.ir/devapi/v2';
    private const TOKEN_URL = self::API_BASE . '/auth/token/';
    private const TOKEN_CACHE_KEY = 'woogit_bazaar_access_token';
    private const TOKEN_CACHE_TTL = 3300;
    private const PURCHASES_TABLE = 'woogit_bazaar_purchases';

    public function isConfigured(): bool
    {
        return $this->clientId() !== '' && $this->clientSecret() !== '' && $this->refreshToken() !== '';
    }

    public function packageName(): string
    {
        $configured = defined('WOOGIT_BAZAAR_PACKAGE_NAME') ? trim((string) WOOGIT_BAZAAR_PACKAGE_NAME) : '';
        return $configured !== '' ? $configured : 'com.samanramezani1377.woogit';
    }

    /**
     * Verify a Bazaar subscription against CafeBazaar's Developer API.
     *
     * @return array{ok:bool,code:string,product_id?:string,order_id?:string,purchase_time?:int,expires_at?:int,auto_renewing?:bool,raw?:array}
     */
    public function verifySubscription(string $productId, string $purchaseToken, string $packageName = ''): array
    {
        $productId = trim($productId);
        $purchaseToken = trim($purchaseToken);
        $packageName = trim($packageName) !== '' ? trim($packageName) : $this->packageName();

        if ($productId === '' || $purchaseToken === '') {
            return ['ok' => false, 'code' => 'invalid_purchase'];
        }
        if (!$this->isConfigured()) {
            return ['ok' => false, 'code' => 'bazaar_not_configured'];
        }

        $accessToken = $this->getAccessToken();
        if ($accessToken === '') {
            return ['ok' => false, 'code' => 'bazaar_auth_failed'];
        }

        $url = self::API_BASE . '/api/applications/' . rawurlencode($packageName)
            . '/subscriptions/' . rawurlencode($productId)
            . '/purchases/' . rawurlencode($purchaseToken) . '/';

        $response = wp_remote_get($url, [
            'timeout' => 15,
            'redirection' => 2,
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Accept' => 'application/json',
            ],
        ]);

        if (is_wp_error($response)) {
            return ['ok' => false, 'code' => 'bazaar_unreachable'];
        }

        $status = (int) wp_remote_retrieve_response_code($response);
        $body = json_decode((string) wp_remote_retrieve_body($response), true);
        if (!is_array($body)) $body = [];

        if ($status < 200 || $status >= 300) {
            if ($status === 401) {
                delete_transient(self::TOKEN_CACHE_KEY);
                return ['ok' => false, 'code' => 'bazaar_auth_failed'];
            }
            if ($status === 404) return ['ok' => false, 'code' => 'bazaar_purchase_not_found'];
            return ['ok' => false, 'code' => 'bazaar_verify_failed'];
        }

        $purchaseState = $this->intValue($body, ['purchaseState', 'purchase_state', 'state']);
        if ($purchaseState !== null && $purchaseState !== 0) {
            return ['ok' => false, 'code' => 'bazaar_purchase_not_active'];
        }

        $endTime = $this->timestampMs($body, [
            'endTime', 'end_time', 'expirationTime', 'expiration_time',
            'nextTime', 'next_time', 'expiryTime', 'expiry_time',
        ]);

        $startTime = $this->timestampMs($body, [
            'startTime', 'start_time', 'initiationTime', 'initiation_time',
        ]);

        $autoRenewing = $this->boolValue($body, ['autoRenewing', 'auto_renewing']);
        $orderId = $this->stringValue($body, ['orderId', 'order_id']);
        $serverProductId = $this->stringValue($body, ['subscriptionId', 'subscription_id', 'productId', 'product_id']);

        if ($serverProductId !== '' && !hash_equals($productId, $serverProductId)) {
            return ['ok' => false, 'code' => 'bazaar_product_mismatch'];
        }

        return [
            'ok' => true,
            'code' => 'verified',
            'product_id' => $serverProductId !== '' ? $serverProductId : $productId,
            'order_id' => $orderId,
            'purchase_time' => $startTime,
            'expires_at' => $endTime,
            'auto_renewing' => $autoRenewing,
            'raw' => $body,
        ];
    }

    private function getAccessToken(): string
    {
        $cached = get_transient(self::TOKEN_CACHE_KEY);
        if (is_string($cached) && $cached !== '') return $cached;

        $response = wp_remote_post(self::TOKEN_URL, [
            'timeout' => 15,
            'redirection' => 2,
            'headers' => ['Accept' => 'application/json'],
            'body' => [
                'grant_type' => 'refresh_token',
                'client_id' => $this->clientId(),
                'client_secret' => $this->clientSecret(),
                'refresh_token' => $this->refreshToken(),
            ],
        ]);

        if (is_wp_error($response)) return '';

        $status = (int) wp_remote_retrieve_response_code($response);
        $body = json_decode((string) wp_remote_retrieve_body($response), true);
        if ($status < 200 || $status >= 300 || !is_array($body)) return '';

        $token = trim((string) ($body['access_token'] ?? ''));
        if ($token === '') return '';

        $expires = max(300, min(self::TOKEN_CACHE_TTL, (int) ($body['expires_in'] ?? self::TOKEN_CACHE_TTL)));
        set_transient(self::TOKEN_CACHE_KEY, $token, $expires);
        return $token;
    }

    private function clientId(): string
    {
        return defined('WOOGIT_BAZAAR_CLIENT_ID') ? trim((string) WOOGIT_BAZAAR_CLIENT_ID) : '';
    }

    private function clientSecret(): string
    {
        return defined('WOOGIT_BAZAAR_CLIENT_SECRET') ? trim((string) WOOGIT_BAZAAR_CLIENT_SECRET) : '';
    }

    private function refreshToken(): string
    {
        return defined('WOOGIT_BAZAAR_REFRESH_TOKEN') ? trim((string) WOOGIT_BAZAAR_REFRESH_TOKEN) : '';
    }

    private function stringValue(array $body, array $keys): string
    {
        foreach ($keys as $key) {
            if (isset($body[$key]) && is_scalar($body[$key])) return trim((string) $body[$key]);
        }
        return '';
    }

    private function intValue(array $body, array $keys): ?int
    {
        foreach ($keys as $key) {
            if (isset($body[$key]) && is_numeric($body[$key])) return (int) $body[$key];
        }
        return null;
    }

    private function boolValue(array $body, array $keys): ?bool
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $body)) continue;
            $value = $body[$key];
            if (is_bool($value)) return $value;
            if (is_numeric($value)) return ((int) $value) === 1;
            if (is_string($value)) return in_array(strtolower(trim($value)), ['1', 'true', 'yes'], true);
        }
        return null;
    }

    private function timestampMs(array $body, array $keys): ?int
    {
        $value = $this->intValue($body, $keys);
        if ($value === null || $value <= 0) return null;
        return $value > 100000000000 ? (int) floor($value / 1000) : $value;
    }
}
