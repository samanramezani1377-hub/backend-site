<?php
namespace WooGit\\Backend;

defined('ABSPATH') || exit;

final class WooCommerceProxy
{
    public function forward(string $baseUrl,string $path,string $method,string $username,string $applicationPassword,string $consumerKey,string $consumerSecret,array $query,?array $body): array
    {
        // Customer credentials are request-scoped only. Never persist or log them.
        $url = $baseUrl . $path;
        if ($query !== []) $url = add_query_arg($query, $url);
        $headers = ['Authorization' => 'Basic ' . base64_encode($username . ':' . $applicationPassword),'Accept' => 'application/json','Content-Type' => 'application/json','User-Agent' => 'WooGit-Backend/' . WOOGIT_BACKEND_VERSION];
        if ($consumerKey !== '' && $consumerSecret !== '') $url = add_query_arg(['consumer_key' => $consumerKey,'consumer_secret' => $consumerSecret], $url);
        $args = ['method' => strtoupper($method),'timeout' => 20,'redirection' => 2,'headers' => $headers,'data_format' => 'body'];
        if ($body !== null && in_array(strtoupper($method), ['POST','PUT','PATCH'], true)) $args['body'] = wp_json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $response = wp_safe_remote_request($url, $args);
        if (is_wp_error($response)) return ['status' => 502,'body' => ['code' => 'upstream_unreachable']];
        $status = wp_remote_retrieve_response_code($response);
        $raw = wp_remote_retrieve_body($response);
        $decoded = json_decode($raw, true);
        return ['status' => $status,'body' => json_last_error() === JSON_ERROR_NONE ? $decoded : ['raw' => $raw]];
    }
}
