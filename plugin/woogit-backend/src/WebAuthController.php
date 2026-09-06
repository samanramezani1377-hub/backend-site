<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class WebAuthController
{
    private AccountService $accounts;
    private SiteService $sites;
    private SessionService $sessions;
    private WebSessionService $webSessions;
    private ProxyPolicy $policy;
    private RateLimitService $rateLimits;

    public function __construct()
    {
        $this->accounts=new AccountService();
        $this->sites=new SiteService();
        $this->sessions=new SessionService();
        $this->webSessions=new WebSessionService();
        $this->policy=new ProxyPolicy();
        $this->rateLimits=new RateLimitService();
    }

    public function register(): void
    {
        register_rest_route('woogit/v1','/account/requirements',['methods'=>'GET','permission_callback'=>'__return_true','callback'=>[$this,'requirements']]);
        register_rest_route('woogit/v1','/account/setup-web-credentials',['methods'=>'POST','permission_callback'=>'__return_true','callback'=>[$this,'setupWebCredentials']]);
        register_rest_route('woogit/v1','/web/login',['methods'=>'POST','permission_callback'=>'__return_true','callback'=>[$this,'login']]);
        register_rest_route('woogit/v1','/web/logout',['methods'=>'POST','permission_callback'=>'__return_true','callback'=>[$this,'logout']]);
        register_rest_route('woogit/v1','/web/me',['methods'=>'GET','permission_callback'=>'__return_true','callback'=>[$this,'me']]);
    }

    public function requirements(\WP_REST_Request $request): \WP_REST_Response
    {
        $context=$this->apiContext($request);if($context instanceof \WP_REST_Response)return $context;
        $account=$this->accounts->get((int)$context['account_id']);
        if(!$account)return new \WP_REST_Response(['code'=>'account_inactive'],403);
        $requirements=[];
        if(!$this->accounts->hasWebPassword((int)$context['account_id']))$requirements[]=['id'=>'web_account_password','type'=>'account_setup','required'=>true];
        $requirements[]=['id'=>'contact_email','type'=>'contact_metadata','required'=>false,'configured'=>!empty($account['email'])];
        return new \WP_REST_Response(['account_id'=>(int)$context['account_id'],'site_id'=>(int)$context['site_id'],'requirements'=>$requirements],200);
    }

    public function setupWebCredentials(\WP_REST_Request $request): \WP_REST_Response
    {
        $context=$this->apiContext($request);if($context instanceof \WP_REST_Response)return $context;
        $accountId=(int)$context['account_id'];
        $limit=$this->rateLimits->check('web_setup',(string)$accountId.':'.(string)$context['site_id'],5,60);if(!$limit['allowed'])return $this->rateLimited($limit['retry_after']);
        if($this->accounts->hasWebPassword($accountId))return new \WP_REST_Response(['code'=>'web_credentials_already_configured'],409);
        $input=$request->get_json_params();$input=is_array($input)?$input:[];
        $password=(string)($input['password']??'');$confirmation=(string)($input['password_confirmation']??'');
        if(strlen($password)<12||strlen($password)>256)return new \WP_REST_Response(['code'=>'invalid_web_password'],400);
        if(!hash_equals($password,$confirmation))return new \WP_REST_Response(['code'=>'password_confirmation_mismatch'],400);
        if(isset($input['email'])&&!is_string($input['email']))return new \WP_REST_Response(['code'=>'invalid_contact_email'],400);
        $email=array_key_exists('email',$input)?sanitize_email((string)$input['email']):'';
        if($email!==''&&!is_email($email))return new \WP_REST_Response(['code'=>'invalid_contact_email'],400);
        if($email!==''&&!$this->accounts->updateContactEmail($accountId,$email))return new \WP_REST_Response(['code'=>'contact_email_unavailable'],500);
        if(!$this->accounts->setWebPassword($accountId,$password))return new \WP_REST_Response(['code'=>'web_password_unavailable'],500);
        return new \WP_REST_Response(['configured'=>true,'contact_email_configured'=>$email!==''||!empty($this->accounts->get($accountId)['email'])],200);
    }

    public function login(\WP_REST_Request $request): \WP_REST_Response
    {
        $ipLimit=$this->rateLimits->check('web_login_ip',$this->clientIp(),5,60);if(!$ipLimit['allowed'])return $this->rateLimited($ipLimit['retry_after']);
        $input=$request->get_json_params();$input=is_array($input)?$input:[];$url=trim((string)($input['site_url']??''));$password=(string)($input['password']??'');
        $base=$this->policy->resolveSiteUrl($url);$host=$base!==null?strtolower(rtrim((string)wp_parse_url($base,PHP_URL_HOST),'.')):'';
        if($host!==''){$hostLimit=$this->rateLimits->check('web_login_site',$host,5,60);if(!$hostLimit['allowed'])return $this->rateLimited($hostLimit['retry_after']);}
        if($base===null||$password==='')return new \WP_REST_Response(['code'=>'invalid_web_credentials'],401);
        $site=$this->sites->findByHost($host);
        $account=$site?$this->accounts->get((int)$site['account_id']):null;
        if(!$site||!$account||$site['status']!=='active'||!$this->accounts->verifyWebPassword((int)$account['id'],$password))return new \WP_REST_Response(['code'=>'invalid_web_credentials'],401);
        $session=$this->webSessions->issue((int)$account['id'],(int)$site['id']);
        if(!$session)return new \WP_REST_Response(['code'=>'web_session_creation_failed'],500);
        return new \WP_REST_Response(['session'=>$session['token'],'expires_at'=>gmdate('Y-m-d H:i:s',$session['expires_at']),'account_id'=>(int)$account['id'],'site_id'=>(int)$site['id']],200);
    }

    public function logout(\WP_REST_Request $request): \WP_REST_Response
    {
        return new \WP_REST_Response(['revoked'=>$this->webSessions->revoke((string)$request->get_header('X-WooGit-Web-Session'))],200);
    }

    public function me(\WP_REST_Request $request): \WP_REST_Response
    {
        $session=$this->webSessions->authenticate((string)$request->get_header('X-WooGit-Web-Session'));
        if($session===null)return new \WP_REST_Response(['code'=>'invalid_web_session'],401);
        $account=$this->accounts->get((int)$session['account_id']);$site=$this->sites->getOwned((int)$session['account_id'],(int)$session['site_id']);
        if(!$account||!$site)return new \WP_REST_Response(['code'=>'account_unavailable'],403);
        return new \WP_REST_Response(['account_id'=>(int)$account['id'],'contact_email'=>$account['email'],'site_id'=>(int)$site['id'],'site_url'=>$site['canonical_url'],'web_password_configured'=>$this->accounts->hasWebPassword((int)$account['id']),'session_expires_at'=>gmdate('Y-m-d H:i:s',$session['expires_at'])],200);
    }

    private function apiContext(\WP_REST_Request $request): array|\WP_REST_Response
    {
        $session=$this->sessions->authenticate((string)$request->get_header('X-WooGit-Session'));
        if($session===null)return new \WP_REST_Response(['code'=>'invalid_session'],401);
        $account=$this->accounts->get((int)$session['account_id']);if(!$account)return new \WP_REST_Response(['code'=>'account_inactive'],403);
        $site=$this->sites->getOwned((int)$session['account_id'],(int)$session['site_id']);if(!$site)return new \WP_REST_Response(['code'=>'site_not_owned'],403);
        return ['account_id'=>(int)$account['id'],'site_id'=>(int)$site['id'],'scope'=>(string)($session['scope']??'')];
    }

    private function rateLimited(int $retryAfter): \WP_REST_Response
    {
        $retryAfter=max(1,$retryAfter);$response=new \WP_REST_Response(['code'=>'RATE_LIMITED','retry_after'=>$retryAfter,'retryable'=>true],429);$response->header('Retry-After',(string)$retryAfter);return $response;
    }

    private function clientIp(): string
    {
        $ip=isset($_SERVER['REMOTE_ADDR'])?trim((string)$_SERVER['REMOTE_ADDR']):'';return filter_var($ip,FILTER_VALIDATE_IP)?$ip:'unknown';
    }
}
