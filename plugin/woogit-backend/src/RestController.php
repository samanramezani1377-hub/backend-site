<?php
namespace WooGit\\Backend;

defined('ABSPATH') || exit;

final class RestController
{
    private SessionService $sessions;
    private ProxyPolicy $policy;
    private WooCommerceProxy $proxy;

    public function __construct(){ $this->sessions = new SessionService(); $this->policy = new ProxyPolicy(); $this->proxy = new WooCommerceProxy(); }

    public function register(): void
    {
        register_rest_route('woogit/v1', '/forward', ['methods' => ['GET','POST','PUT','PATCH','DELETE'],'permission_callback' => '__return_true','callback' => [$this,'forward']]);
    }

    public function forward(\\WP_REST_Request $request): \\WP_REST_Response
    {
        $session = $this->sessions->authenticate((string) $request->get_header('X-WooGit-Session'));
        if ($session === null) return new \\WP_REST_Response(['code' => 'invalid_session'], 401);
        $resource = sanitize_key((string) $request->get_param('resource'));
        $rawId = $request->get_param('id');
        $id = ($rawId === null || $rawId === '') ? null : absint($rawId);
        $path = $this->policy->resolve($resource, $id);
        $method = strtoupper($request->get_method());
        $idempotencyKey = trim((string) $request->get_header('Idempotency-Key'));
        if ($path === null) return new \\WP_REST_Response(['code' => 'operation_not_allowed'], 404);
        if (!$this->policy->validateMethod($method, $idempotencyKey !== '')) return new \\WP_REST_Response(['code' => 'invalid_mutation_request'], 400);
        $site = $this->getSite((int) $session['account_id'], (int) $session['site_id']);
        if ($site === null) return new \\WP_REST_Response(['code' => 'site_not_owned'], 403);
        $baseUrl = $this->policy->resolveSiteUrl($site['canonical_url']);
        if ($baseUrl === null) return new \\WP_REST_Response(['code' => 'invalid_site_identity'], 403);
        $input = $request->get_json_params();
        $input = is_array($input) ? $input : [];
        foreach (['wordpress_username','wordpress_application_password','consumer_key','consumer_secret'] as $key) {
            if (!isset($input[$key]) || !is_string($input[$key]) || $input[$key] === '') return new \\WP_REST_Response(['code' => 'missing_customer_credentials'], 400);
        }
        $body = isset($input['body']) && is_array($input['body']) ? $input['body'] : null;
        $query = isset($input['query']) && is_array($input['query']) ? $input['query'] : [];
        $result = $this->proxy->forward($baseUrl,$path,$method,$input['wordpress_username'],$input['wordpress_application_password'],$input['consumer_key'],$input['consumer_secret'],$query,$body);
        return new \\WP_REST_Response($result['body'], $result['status']);
    }

    private function getSite(int $accountId,int $siteId): ?array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'woogit_sites';
        $row = $wpdb->get_row($wpdb->prepare("SELECT id, account_id, canonical_url, status FROM {$table} WHERE id = %d AND account_id = %d LIMIT 1",$siteId,$accountId),ARRAY_A);
        return (!$row || $row['status'] !== 'active') ? null : $row;
    }
}
