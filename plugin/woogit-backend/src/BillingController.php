<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class BillingController
{
    private SessionService $sessions;
    private AccountService $accounts;
    private SiteService $sites;
    private BillingService $billing;
    private VersionGate $versionGate;

    public function __construct()
    {
        $this->sessions = new SessionService();
        $this->accounts = new AccountService();
        $this->sites = new SiteService();
        $this->billing = new BillingService();
        $this->versionGate = new VersionGate();
    }

    public function register(): void
    {
        register_rest_route('woogit/v1', '/billing/plans', [
            'methods' => 'GET', 'permission_callback' => '__return_true', 'callback' => [$this, 'plans'],
        ]);
        register_rest_route('woogit/v1', '/billing/status', [
            'methods' => 'GET', 'permission_callback' => '__return_true', 'callback' => [$this, 'status'],
        ]);
        register_rest_route('woogit/v1', '/billing/checkout', [
            'methods' => 'POST', 'permission_callback' => '__return_true', 'callback' => [$this, 'checkout'],
        ]);
    }

    public function plans(\WP_REST_Request $request): \WP_REST_Response
    {
        $gate = $this->versionResponse($request);
        if ($gate instanceof \WP_REST_Response) return $gate;
        return new \WP_REST_Response(['plans' => $this->billing->getPlans()], 200);
    }

    public function status(\WP_REST_Request $request): \WP_REST_Response
    {
        $gate = $this->versionResponse($request);
        if ($gate instanceof \WP_REST_Response) return $gate;
        $context = $this->authenticateAccountContext($request);
        if ($context instanceof \WP_REST_Response) return $context;
        return new \WP_REST_Response([
            'account_id' => (int)$context['account_id'],
            'site_id' => (int)$context['site_id'],
            'billing' => $this->billing->getStatus((int)$context['account_id'], (int)$context['site_id']),
        ], 200);
    }

    public function checkout(\WP_REST_Request $request): \WP_REST_Response
    {
        $gate = $this->versionResponse($request);
        if ($gate instanceof \WP_REST_Response) return $gate;
        $context = $this->authenticateAccountContext($request);
        if ($context instanceof \WP_REST_Response) return $context;
        $input = $request->get_json_params();
        $productId = is_array($input) ? (int)($input['plan_id'] ?? 0) : 0;
        if ($productId <= 0) return new \WP_REST_Response(['code' => 'missing_plan'], 400);
        $result = $this->billing->createCheckout((int)$context['account_id'], (int)$context['site_id'], $productId);
        if (!$result['ok']) return new \WP_REST_Response(['code' => $result['code']], 400);
        return new \WP_REST_Response([
            'order_id' => $result['order_id'],
            'payment_url' => $result['payment_url'],
            'status' => $result['status'],
        ], 201);
    }

    private function authenticateAccountContext(\WP_REST_Request $request): array|\WP_REST_Response
    {
        $session = $this->sessions->authenticate((string)$request->get_header('X-WooGit-Session'));
        if ($session === null) return new \WP_REST_Response(['code' => 'invalid_session'], 401);
        $account = $this->accounts->get((int)$session['account_id']);
        if (!$account) return new \WP_REST_Response(['code' => 'account_inactive'], 403);
        $site = $this->sites->getOwned((int)$session['account_id'], (int)$session['site_id']);
        if (!$site) return new \WP_REST_Response(['code' => 'site_not_owned'], 403);
        $session['site'] = $site;
        return $session;
    }

    private function versionResponse(\WP_REST_Request $request): ?\WP_REST_Response
    {
        $result = $this->versionGate->check((string)$request->get_header('X-WooGit-App-Version'));
        if ($result['allowed']) return null;
        $policy = $result['policy'];
        $status = $result['code'] === 'APP_VERSION_DEPRECATED' ? 426 : 400;
        return new \WP_REST_Response([
            'code' => $result['code'],
            'message' => $result['code'] === 'APP_VERSION_DEPRECATED' ? 'این نسخه از WooGit دیگر پشتیبانی نمی‌شود.' : 'نسخه Client نامعتبر است.',
            'minimum_supported_version' => $policy['minimum_supported_version'],
            'latest_version' => $policy['latest_version'],
            'recommended_version' => $policy['recommended_version'],
            'update_required' => $result['code'] === 'APP_VERSION_DEPRECATED',
            'retryable' => false,
        ], $status);
    }
}
