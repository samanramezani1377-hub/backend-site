<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

/**
 * Security boundary for the transparent App -> Backend -> Customer-site proxy.
 * This class deliberately does not model WooCommerce resources or responses.
 */
final class ProxyPolicy
{
    private const ALLOWED_PREFIXES = [
        '/wp-json/wc/v3/',
        '/wp-json/wp/v2/media',
    ];

    private const ALLOWED_METHODS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

    public function resolveSiteUrl(string $canonicalUrl): ?string
    {
        $parts = wp_parse_url(trim($canonicalUrl));
        if (!$parts || empty($parts['scheme']) || empty($parts['host'])) return null;
        if (strtolower((string)$parts['scheme']) !== 'https') return null;
        if (!empty($parts['user']) || !empty($parts['pass']) || (!empty($parts['port']) && (int)$parts['port'] !== 443)) return null;

        $host = strtolower((string)$parts['host']);
        if ($this->isPrivateHost($host)) return null;

        $base = 'https://' . $host;
        if (!empty($parts['path']) && $parts['path'] !== '/') $base .= '/' . trim((string)$parts['path'], '/');
        return rtrim($base, '/');
    }

    public function validatePath(string $path): ?string
    {
        $path = trim($path);
        if ($path === '' || $path[0] !== '/' || str_contains($path, '\\') || str_contains($path, '..') || str_contains($path, "\0")) return null;
        $normalized = wp_parse_url($path, PHP_URL_PATH);
        if (!is_string($normalized) || $normalized !== $path) return null;

        foreach (self::ALLOWED_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) return $path;
        }
        return null;
    }

    public function validateMethod(string $method, bool $hasIdempotencyKey): bool
    {
        $method = strtoupper($method);
        if (!in_array($method, self::ALLOWED_METHODS, true)) return false;
        return !in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true) || $hasIdempotencyKey;
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
