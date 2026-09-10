<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class WebSessionRestBridge
{
    public function register(): void
    {
        add_filter('rest_pre_dispatch', [$this, 'fromCookie'], 5, 3);
    }

    public function fromCookie($result, \WP_REST_Server $server, \WP_REST_Request $request)
    {
        if ($result !== null) return $result;
        if (trim((string)$request->get_header('X-WooGit-Web-Session')) !== '') return $result;
        if (strpos($request->get_route(), '/woogit/v1/') !== 0) return $result;

        $token = isset($_COOKIE['woogit_web_session'])
            ? trim((string)wp_unslash($_COOKIE['woogit_web_session']))
            : '';
        if ($token !== '' && strlen($token) <= 128) {
            $request->set_header('X-WooGit-Web-Session', sanitize_text_field($token));
        }
        return $result;
    }
}
