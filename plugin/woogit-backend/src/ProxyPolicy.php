<?php
namespace WooGit\\Backend;

defined('ABSPATH') || exit;

final class ProxyPolicy
{
    private const RESOURCES = [
        'products' => '/wp-json/wc/v3/products',
        'orders' => '/wp-json/wc/v3/orders',
        'customers' => '/wp-json/wc/v3/customers',
        'categories' => '/wp-json/wc/v3/products/categories',
        'variations' => '/wp-json/wc/v3/products/{id}/variations',
    ];

    public function resolve(string $resource, ?int $id = null): ?string
    {
        if (!isset(self::RESOURCES[$resource])) return null;
        $path = self::RESOURCES[$resource];
        if (str_contains($path, '{id}')) {
            if ($id === null || $id < 1) return null;
            $path = str_replace('{id}', (string) $id, $path);
        }
        return $path;
    }

    public function validateMethod(string $method, bool $hasIdempotencyKey): bool
    {
        $method = strtoupper($method);
        if (!in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], true)) return false;
        return !in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true) || $hasIdempotencyKey;
    }

    public function resolveSiteUrl(string $canonicalUrl): ?string
    {
        $parts = wp_parse_url($canonicalUrl);
        if (!$parts || empty($parts['scheme']) || empty($parts['host'])) return null;
        if (strtolower($parts['scheme']) !== 'https') return null;
        $host = strtolower($parts['host']);
        if ($this->isPrivateHost($host)) return null;
        return rtrim($canonicalUrl, '/');
    }

    private function isPrivateHost(string $host): bool
    {
        if ($host === 'localhost' || str_ends_with($host, '.localhost') || str_ends_with($host, '.local')) return true;
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
        }
        return false;
    }
}
