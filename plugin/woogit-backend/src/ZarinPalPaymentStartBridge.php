<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class ZarinPalPaymentStartBridge
{
    private const PATH = '/payment-start/';
    private const TOKEN_PREFIX = 'woogit_zp_start_';
    private const TTL = 600;
    private const ACCOUNT_META = '_woogit_account_id';
    private const SITE_META = '_woogit_site_id';

    public function register(): void
    {
        add_action('template_redirect', [$this, 'handleRequest'], 0);
    }

    public function createStartUrl(int $orderId): string
    {
        if ($orderId <= 0 || !function_exists('wc_get_order')) return '';

        $order = wc_get_order($orderId);
        if (!is_object($order) || !method_exists($order, 'get_id')) return '';
        if (method_exists($order, 'get_created_via') && (string)$order->get_created_via() !== 'woogit') return '';
        if (!method_exists($order, 'get_status') || (string)$order->get_status() !== 'pending') return '';
        if (!method_exists($order, 'needs_payment') || !$order->needs_payment()) return '';

        $accountId = method_exists($order, 'get_meta') ? (int)$order->get_meta(self::ACCOUNT_META) : 0;
        $siteId = method_exists($order, 'get_meta') ? (int)$order->get_meta(self::SITE_META) : 0;
        if ($accountId <= 0 || $siteId <= 0) return '';

        $token = bin2hex(random_bytes(32));
        $key = self::TOKEN_PREFIX . hash('sha256', $token);
        $payload = [
            'order_id' => $orderId,
            'account_id' => $accountId,
            'site_id' => $siteId,
            'created_at' => time(),
            'payment_url' => '',
        ];

        if (!set_transient($key, $payload, self::TTL)) return '';

        return add_query_arg(['token' => $token], home_url(self::PATH));
    }

    public function handleRequest(): void
    {
        if (!$this->isStartRequest()) return;

        nocache_headers();
        header('Referrer-Policy: origin');
        header('X-Robots-Tag: noindex, nofollow, noarchive');

        $token = isset($_GET['token']) ? trim((string)wp_unslash($_GET['token'])) : '';
        if ($token === '' || !preg_match('/^[a-f0-9]{64}$/', $token)) {
            $this->errorResponse('لینک شروع پرداخت نامعتبر یا منقضی شده است.', 400);
        }

        $key = self::TOKEN_PREFIX . hash('sha256', $token);
        $payload = get_transient($key);
        if (!is_array($payload)) {
            $this->errorResponse('لینک شروع پرداخت منقضی شده است. دوباره پرداخت را از داخل WooGit شروع کنید.', 410);
        }

        $orderId = (int)($payload['order_id'] ?? 0);
        $accountId = (int)($payload['account_id'] ?? 0);
        $siteId = (int)($payload['site_id'] ?? 0);
        if ($orderId <= 0 || $accountId <= 0 || $siteId <= 0) {
            delete_transient($key);
            $this->errorResponse('اطلاعات پرداخت نامعتبر است.', 400);
        }

        $order = function_exists('wc_get_order') ? wc_get_order($orderId) : null;
        if (!is_object($order) || !method_exists($order, 'get_meta')
            || (int)$order->get_meta(self::ACCOUNT_META) !== $accountId
            || (int)$order->get_meta(self::SITE_META) !== $siteId
            || (method_exists($order, 'get_created_via') && (string)$order->get_created_via() !== 'woogit')
            || (method_exists($order, 'get_status') && (string)$order->get_status() !== 'pending')
            || (method_exists($order, 'needs_payment') && !$order->needs_payment())) {
            delete_transient($key);
            $this->errorResponse('این سفارش دیگر قابل پرداخت نیست.', 409);
        }

        $paymentUrl = trim((string)($payload['payment_url'] ?? ''));
        if ($paymentUrl === '') {
            $paymentUrl = (new ZarinPalPaymentBridge())->resolvePaymentUrl($orderId);
            if ($paymentUrl === '') {
                $this->errorResponse('درگاه پرداخت زرین‌پال در دسترس نیست. لطفاً دوباره تلاش کنید.', 503);
            }

            $payload['payment_url'] = $paymentUrl;
            set_transient($key, $payload, self::TTL);
        }

        $this->renderRedirectPage($paymentUrl);
    }

    private function isStartRequest(): bool
    {
        $requestPath = wp_parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
        $startPath = wp_parse_url(home_url(self::PATH), PHP_URL_PATH);
        return is_string($requestPath) && is_string($startPath)
            && untrailingslashit($requestPath) === untrailingslashit($startPath);
    }

    private function renderRedirectPage(string $paymentUrl): void
    {
        $safeUrl = esc_url($paymentUrl);
        $jsUrl = wp_json_encode($paymentUrl, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        status_header(200);
        header('Content-Type: text/html; charset=' . get_bloginfo('charset'));
        echo '<!doctype html><html lang="fa" dir="rtl"><head>';
        echo '<meta charset="utf-8"><meta name="referrer" content="origin">';
        echo '<meta name="robots" content="noindex,nofollow,noarchive">';
        echo '<meta name="viewport" content="width=device-width,initial-scale=1">';
        echo '<title>انتقال به درگاه پرداخت</title>';
        echo '<style>body{font-family:system-ui,sans-serif;display:grid;place-items:center;min-height:100vh;margin:0;padding:24px;background:#f7f7f8;color:#222}main{text-align:center;max-width:420px}a{display:inline-block;margin-top:16px}</style>';
        echo '</head><body><main><h1>در حال انتقال به درگاه پرداخت</h1><p>لطفاً چند لحظه صبر کنید.</p>';
        echo '<a href="' . $safeUrl . '">اگر انتقال انجام نشد، ورود به درگاه</a>';
        echo '<script>window.location.replace(' . $jsUrl . ');</script>';
        echo '</main></body></html>';
        exit;
    }

    private function errorResponse(string $message, int $status): void
    {
        status_header($status);
        wp_die(
            '<h1>پرداخت WooGit</h1><p>' . esc_html($message) . '</p>',
            'WooGit — Payment',
            ['response' => $status, 'back_link' => true]
        );
    }
}
