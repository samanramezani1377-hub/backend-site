<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class BillingController
{
    private SessionService $sessions;
    private AccountService $accounts;
    private SiteService $sites;
    private BillingService $billing;
    private EntitlementService $entitlements;
    private VersionGate $versionGate;
    private RateLimitService $rateLimits;

    private const PLANS_LIMIT = 60;
    private const STATUS_LIMIT = 30;
    private const CHECKOUT_LIMIT = 5;
    private const ACTIVATE_SESSION_LIMIT = 5;
    private const WINDOW_SECONDS = 60;

    public function __construct()
    {
        $this->sessions = new SessionService();
        $this->accounts = new AccountService();
        $this->sites = new SiteService();
        $this->billing = new BillingService();
        $this->entitlements = new EntitlementService();
        $this->versionGate = new VersionGate();
        $this->rateLimits = new RateLimitService();
    }

    public function register(): void
    {
        register_rest_route('woogit/v1', '/billing/plans', ['methods'=>'GET','permission_callback'=>'__return_true','callback'=>[$this,'plans']]);
        register_rest_route('woogit/v1', '/billing/status', ['methods'=>'GET','permission_callback'=>'__return_true','callback'=>[$this,'status']]);
        register_rest_route('woogit/v1', '/billing/checkout', ['methods'=>'POST','permission_callback'=>'__return_true','callback'=>[$this,'checkout']]);
        register_rest_route('woogit/v1', '/billing/activate-session', ['methods'=>'POST','permission_callback'=>'__return_true','callback'=>[$this,'activateSession']]);
    }

    public function plans(\WP_REST_Request $request): \WP_REST_Response
    {
        $gate=$this->versionResponse($request);if($gate instanceof \WP_REST_Response)return $gate;
        $limit=$this->rateLimits->check('billing_plans_ip',$this->clientIp(),self::PLANS_LIMIT,self::WINDOW_SECONDS);
        if(!$limit['allowed'])return $this->rateLimited($limit['retry_after']);
        return new \WP_REST_Response(['plans'=>$this->billing->getPlans()],200);
    }

    public function status(\WP_REST_Request $request): \WP_REST_Response
    {
        $gate=$this->versionResponse($request);if($gate instanceof \WP_REST_Response)return $gate;
        $ipLimit=$this->rateLimits->check('billing_status_ip',$this->clientIp(),self::STATUS_LIMIT,self::WINDOW_SECONDS);
        if(!$ipLimit['allowed'])return $this->rateLimited($ipLimit['retry_after']);
        $context=$this->authenticateAccountContext($request);if($context instanceof \WP_REST_Response)return $context;
        $accountSiteLimit=$this->billingLimit('billing_status_account_site',(string)$context['account_id'].':'.(string)$context['site_id'],self::STATUS_LIMIT);
        if(!$accountSiteLimit['allowed'])return $this->rateLimited($accountSiteLimit['retry_after']);
        $sessionLimit=$this->billingLimit('billing_status_session',$this->sessionKey($request),self::STATUS_LIMIT);
        if(!$sessionLimit['allowed'])return $this->rateLimited($sessionLimit['retry_after']);
        return new \WP_REST_Response(['account_id'=>(int)$context['account_id'],'site_id'=>(int)$context['site_id'],'billing'=>$this->billing->getStatus((int)$context['account_id'],(int)$context['site_id'])],200);
    }

    public function checkout(\WP_REST_Request $request): \WP_REST_Response
    {
        $gate=$this->versionResponse($request);if($gate instanceof \WP_REST_Response)return $gate;
        $ipLimit=$this->rateLimits->check('billing_checkout_ip',$this->clientIp(),self::CHECKOUT_LIMIT,self::WINDOW_SECONDS);
        if(!$ipLimit['allowed'])return $this->rateLimited($ipLimit['retry_after']);
        $context=$this->authenticateAccountContext($request);if($context instanceof \WP_REST_Response)return $context;
        $accountSiteLimit=$this->billingLimit('billing_checkout_account_site',(string)$context['account_id'].':'.(string)$context['site_id'],self::CHECKOUT_LIMIT);
        if(!$accountSiteLimit['allowed'])return $this->rateLimited($accountSiteLimit['retry_after']);
        $input=$request->get_json_params();$productId=is_array($input)?(int)($input['plan_id']??0):0;$variationId=is_array($input)?(int)($input['variation_id']??0):0;
        if($productId<=0)return new \WP_REST_Response(['code'=>'missing_plan'],400);
        $result=$this->billing->createCheckout((int)$context['account_id'],(int)$context['site_id'],$productId,$variationId);
        if(!$result['ok'])return new \WP_REST_Response(['code'=>$result['code']],400);
        return new \WP_REST_Response(['order_id'=>$result['order_id'],'payment_url'=>$result['payment_url'],'status'=>$result['status']],201);
    }

    public function activateSession(\WP_REST_Request $request): \WP_REST_Response
    {
        $gate=$this->versionResponse($request);if($gate instanceof \WP_REST_Response)return $gate;
        $ipLimit=$this->rateLimits->check('billing_activate_session_ip',$this->clientIp(),self::ACTIVATE_SESSION_LIMIT,self::WINDOW_SECONDS);
        if(!$ipLimit['allowed'])return $this->rateLimited($ipLimit['retry_after']);
        $context=$this->authenticateAccountContext($request);if($context instanceof \WP_REST_Response)return $context;
        $accountSiteLimit=$this->billingLimit('billing_activate_session_account_site',(string)$context['account_id'].':'.(string)$context['site_id'],self::ACTIVATE_SESSION_LIMIT);
        if(!$accountSiteLimit['allowed'])return $this->rateLimited($accountSiteLimit['retry_after']);
        $sessionLimit=$this->billingLimit('billing_activate_session_session',$this->sessionKey($request),self::ACTIVATE_SESSION_LIMIT);
        if(!$sessionLimit['allowed'])return $this->rateLimited($sessionLimit['retry_after']);
        if(($context['scope']??'')!==SessionService::SCOPE_BILLING)return new \WP_REST_Response(['code'=>'session_already_operational'],409);
        $accountId=(int)$context['account_id'];$siteId=(int)$context['site_id'];
        if(!$this->entitlements->isAllowed($accountId,$siteId,'commerce'))return new \WP_REST_Response(['code'=>'not_entitled'],403);
        $expires=$this->entitlements->getExpiresAt($accountId,$siteId);
        if($expires===null||$expires<=time()+300)return new \WP_REST_Response(['code'=>'entitlement_expiring'],403);
        $token=$this->sessions->activateOperationalFromBilling($accountId,$siteId,$expires);
        if(!$token)return new \WP_REST_Response(['code'=>'session_creation_failed'],500);
        return new \WP_REST_Response(['session'=>$token,'scope'=>SessionService::SCOPE_OPERATIONAL,'expires_at'=>gmdate('Y-m-d H:i:s',$expires)],200);
    }

    private function authenticateAccountContext(\WP_REST_Request $request): array|\WP_REST_Response
    {
        $session=$this->sessions->authenticate((string)$request->get_header('X-WooGit-Session'));
        if($session===null)return new \WP_REST_Response(['code'=>'invalid_session'],401);
        $account=$this->accounts->get((int)$session['account_id']);if(!$account)return new \WP_REST_Response(['code'=>'account_inactive'],403);
        $site=$this->sites->getOwned((int)$session['account_id'],(int)$session['site_id']);if(!$site)return new \WP_REST_Response(['code'=>'site_not_owned'],403);
        $session['site']=$site;return $session;
    }

    private function billingLimit(string $bucket,string $key,int $limit): array
    {
        return $this->rateLimits->check($bucket,$key,$limit,self::WINDOW_SECONDS);
    }

    private function sessionKey(\WP_REST_Request $request): string
    {
        $token=trim((string)$request->get_header('X-WooGit-Session'));
        return hash('sha256',$token);
    }

    private function versionResponse(\WP_REST_Request $request): ?\WP_REST_Response
    {
        $result=$this->versionGate->check((string)$request->get_header('X-WooGit-App-Version'));if($result['allowed'])return null;$policy=$result['policy'];$status=$result['code']==='APP_VERSION_DEPRECATED'?426:400;
        return new \WP_REST_Response(['code'=>$result['code'],'message'=>$result['code']==='APP_VERSION_DEPRECATED'?'این نسخه از WooGit دیگر پشتیبانی نمی‌شود.':'نسخه Client نامعتبر است.','minimum_supported_version'=>$policy['minimum_supported_version'],'latest_version'=>$policy['latest_version'],'recommended_version'=>$policy['recommended_version'],'update_required'=>$result['code']==='APP_VERSION_DEPRECATED','retryable'=>false],$status);
    }

    private function rateLimited(int $retryAfter): \WP_REST_Response
    {
        $retryAfter=max(1,$retryAfter);
        $response=new \WP_REST_Response(['code'=>'RATE_LIMITED','retry_after'=>$retryAfter,'retryable'=>true],429);
        $response->header('Retry-After',(string)$retryAfter);
        return $response;
    }

    private function clientIp(): string
    {
        $ip=isset($_SERVER['REMOTE_ADDR'])?trim((string)$_SERVER['REMOTE_ADDR']):'';
        return filter_var($ip,FILTER_VALIDATE_IP)?$ip:'unknown';
    }
}
