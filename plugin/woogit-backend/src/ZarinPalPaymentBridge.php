<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class ZarinPalPaymentBridge
{
    private const CHECKOUT_ROUTE = '/woogit/v1/billing/checkout';
    private const REDIRECT_MARKER = '__WOOGIT_ZARINPAL_REDIRECT__';

    public function register(): void
    {
        add_filter('rest_request_after_callbacks', [$this, 'afterCheckout'], 20, 3);
    }

    public function afterCheckout($response, array $handler, \WP_REST_Request $request)
    {
        if (!$response instanceof \WP_REST_Response) return $response;
        if (strtoupper($request->get_method()) !== 'POST' || $request->get_route() !== self::CHECKOUT_ROUTE) return $response;

        $status = (int)$response->get_status();
        if ($status < 200 || $status >= 300) return $response;

        $data = $response->get_data();
        if (!is_array($data) || empty($data['order_id'])) return $response;

        $paymentUrl = $this->resolvePaymentUrl((int)$data['order_id']);
        if ($paymentUrl === '') {
            return new \WP_REST_Response([
                'code' => 'payment_gateway_unavailable',
                'order_id' => (int)$data['order_id'],
                'message' => 'درگاه پرداخت زرین‌پال در دسترس نیست یا نتوانست آدرس پرداخت را ایجاد کند.',
                'retryable' => true,
            ], 503);
        }

        $data['payment_url'] = $paymentUrl;
        $data['payment_method'] = 'zarinpal';
        $response->set_data($data);
        return $response;
    }

    private function resolvePaymentUrl(int $orderId): string
    {
        if ($orderId <= 0 || !function_exists('wc_get_order') || !function_exists('WC')) return '';
        if (!WC()->payment_gateways()) return '';

        $gateways = WC()->payment_gateways()->payment_gateways();
        $gateway = null;

        foreach ($gateways as $candidate) {
            if (!is_object($candidate)) continue;
            $id = strtolower((string)($candidate->id ?? ''));
            if ($id === 'wc_zpal' || method_exists($candidate, 'Send_to_ZarinPal_Gateway')) {
                $gateway = $candidate;
                break;
            }
        }

        if (!$gateway || !method_exists($gateway, 'Send_to_ZarinPal_Gateway')) return '';
        if (isset($gateway->enabled) && (string)$gateway->enabled !== 'yes') return '';

        if (WC()->session === null && method_exists(WC(), 'initialize_session')) {
            WC()->initialize_session();
        }

        $captured = '';
        $filter = static function ($location, $status) use (&$captured) {
            $captured = (string)$location;
            throw new \RuntimeException(self::REDIRECT_MARKER . $captured);
        };

        ob_start();
        add_filter('wp_redirect', $filter, PHP_INT_MAX, 2);
        try {
            $gateway->Send_to_ZarinPal_Gateway($orderId);
        } catch (\RuntimeException $e) {
            $message = $e->getMessage();
            if (strpos($message, self::REDIRECT_MARKER) !== 0) $captured = '';
        } catch (\Throwable $e) {
            $captured = '';
        } finally {
            remove_filter('wp_redirect', $filter, PHP_INT_MAX);
            ob_end_clean();
        }

        if ($captured === '') return '';
        $parts = wp_parse_url($captured);
        $host = isset($parts['host']) ? strtolower((string)$parts['host']) : '';
        if ($host === '' || !preg_match('/(^|\.)zarinpal\.com$/i', $host)) return '';

        return esc_url_raw($captured, ['https']);
    }
}
