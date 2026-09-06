<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class RestController
{
    private SessionService $sessions; private AccountService $accounts; private SiteService $sites; private EntitlementService $entitlements; private IdempotencyService $idempotency; private OperationService $operations; private ProxyPolicy $policy; private WooCommerceProxy $proxy;
    public function __construct(){ $this->sessions=new SessionService();$this->accounts=new AccountService();$this->sites=new SiteService();$this->entitlements=new EntitlementService();$this->idempotency=new IdempotencyService();$this->operations=new OperationService();$this->policy=new ProxyPolicy();$this->proxy=new WooCommerceProxy(); }

    public function register(): void
    {
        register_rest_route('woogit/v1','/sites/verify',['methods'=>'POST','permission_callback'=>'__return_true','callback'=>[$this,'verifySite']]);
        register_rest_route('woogit/v1','/sessions/revoke',['methods'=>'POST','permission_callback'=>'__return_true','callback'=>[$this,'revokeSession']]);
        register_rest_route('woogit/v1','/operations/(?P<operation_id>[A-Za-z0-9_-]+)',['methods'=>'GET','permission_callback'=>'__return_true','callback'=>[$this,'getOperation']]);
        register_rest_route('woogit/v1','/forward',['methods'=>['GET','POST','PUT','PATCH','DELETE'],'permission_callback'=>'__return_true','callback'=>[$this,'forward']]);
    }

    public function verifySite(\WP_REST_Request $request): \WP_REST_Response
    {
        $input=$request->get_json_params();$input=is_array($input)?$input:[];$url=trim((string)($input['url']??''));$email=sanitize_email((string)($input['email']??''));
        foreach(['wordpress_username','wordpress_application_password','consumer_key','consumer_secret'] as $key) if(!isset($input[$key])||!is_string($input[$key])||$input[$key]==='') return new \WP_REST_Response(['code'=>'missing_customer_credentials'],400);
        $base=$this->policy->resolveSiteUrl($url);if($base===null||!is_email($email))return new \WP_REST_Response(['code'=>'invalid_site_or_email'],400);
        $verification=$this->proxy->verify($base,$input['wordpress_username'],$input['wordpress_application_password'],$input['consumer_key'],$input['consumer_secret']);
        if(!$verification['ok'])return new \WP_REST_Response(['code'=>'site_verification_failed','reason'=>$verification['reason']],502);
        $account=$this->accounts->findOrCreate($email);if(!$account)return new \WP_REST_Response(['code'=>'account_unavailable'],500);
        $site=$this->sites->findOrCreate((int)$account['id'],$base);if(!$site)return new \WP_REST_Response(['code'=>'site_ownership_conflict'],409);
        if(!$this->entitlements->grantTrial((int)$account['id'],(int)$site['id']))return new \WP_REST_Response(['code'=>'entitlement_unavailable'],500);
        if(!$this->entitlements->isAllowed((int)$account['id'],(int)$site['id'],'commerce'))return new \WP_REST_Response(['code'=>'not_entitled'],403);
        $token=$this->sessions->issue((int)$account['id'],(int)$site['id']);if(!$token)return new \WP_REST_Response(['code'=>'session_creation_failed'],500);
        return new \WP_REST_Response(['account_id'=>(int)$account['id'],'site_id'=>(int)$site['id'],'session'=>$token,'expires_in'=>86400],200);
    }

    public function revokeSession(\WP_REST_Request $request): \WP_REST_Response
    { return new \WP_REST_Response(['revoked'=>$this->sessions->revoke((string)$request->get_header('X-WooGit-Session'))],200); }

    public function getOperation(\WP_REST_Request $request): \WP_REST_Response
    {
        $session=$this->authenticateContext($request);if($session instanceof \WP_REST_Response)return $session;
        $operationId=sanitize_text_field((string)$request['operation_id']);$operation=$this->operations->getForAccount((int)$session['account_id'],(int)$session['site_id'],$operationId);
        if(!$operation)return new \WP_REST_Response(['code'=>'operation_not_found'],404);
        return new \WP_REST_Response(['operation_id'=>$operation['operation_id'],'status'=>$operation['status'],'path'=>$operation['operation_path'],'method'=>$operation['method'],'upstream_status'=>$operation['upstream_status'],'response'=>$operation['response_body'],'created_at'=>$operation['created_at'],'updated_at'=>$operation['updated_at'],'expires_at'=>$operation['expires_at']],200);
    }

    public function forward(\WP_REST_Request $request): \WP_REST_Response
    {
        $session=$this->authenticateContext($request);if($session instanceof \WP_REST_Response)return $session;
        $path=$this->policy->validatePath((string)$request->get_param('path'));if($path===null)return new \WP_REST_Response(['code'=>'operation_not_allowed'],404);
        $method=strtoupper($request->get_method());$key=trim((string)$request->get_header('Idempotency-Key'));if(!$this->policy->validateMethod($method,$key!=='') )return new \WP_REST_Response(['code'=>'invalid_mutation_request'],400);
        $base=$this->policy->resolveSiteUrl($session['site']['canonical_url']);if($base===null)return new \WP_REST_Response(['code'=>'invalid_site_identity'],403);
        $credentials=$this->credentials($request);if($credentials===null)return new \WP_REST_Response(['code'=>'missing_customer_credentials'],400);
        $query=$request->get_query_params();unset($query['path']);$rawBody=(string)$request->get_body();$contentType=(string)$request->get_header('content-type');
        $fingerprint=$this->idempotency->fingerprint($method,$path,$query,$rawBody);$operationId='';
        if($key!==''){
            $candidate=$this->operations->create((int)$session['account_id'],(int)$session['site_id'],$key,$fingerprint,$path,$path,$method);
            if($candidate===null){$existing=$this->idempotency->lookup((int)$session['account_id'],(int)$session['site_id'],$key,$fingerprint);if($existing['state']==='conflict')return new \WP_REST_Response(['code'=>'idempotency_conflict'],409);if($existing['state']==='completed')return new \WP_REST_Response($existing['body'],$existing['status']);if($existing['state']==='pending')return new \WP_REST_Response(['code'=>'operation_in_progress','operation_id'=>$existing['operation_id']],202);return new \WP_REST_Response(['code'=>'operation_unavailable'],500);}
            $operationId=$candidate['operation_id'];$claim=$this->idempotency->claim((int)$session['account_id'],(int)$session['site_id'],$key,$fingerprint,$operationId);
            if($claim['state']!=='claimed'){
                $this->operations->deletePending((int)$session['account_id'],(int)$session['site_id'],$operationId);
                if($claim['state']==='conflict')return new \WP_REST_Response(['code'=>'idempotency_conflict'],409);if($claim['state']==='completed')return new \WP_REST_Response($claim['body'],$claim['status']);if($claim['state']==='pending')return new \WP_REST_Response(['code'=>'operation_in_progress','operation_id'=>$claim['operation_id']],202);return new \WP_REST_Response(['code'=>'operation_unavailable'],500);
            }
        }
        $result=$this->proxy->forward($base,$path,$method,$credentials['wordpress_username'],$credentials['wordpress_application_password'],$credentials['consumer_key'],$credentials['consumer_secret'],$query,$rawBody,$contentType);
        $status=(int)$result['status'];$responseBody=(string)$result['body'];$decoded=json_decode($responseBody,true);$idempotentBody=is_array($decoded)?$decoded:['raw'=>$responseBody];
        if($key!==''){
            if(!empty($result['timeout'])){$this->operations->markUnknown((int)$session['account_id'],(int)$session['site_id'],$operationId);return new \WP_REST_Response(['code'=>'upstream_timeout','operation_id'=>$operationId,'status'=>'unknown','retryable'=>true],504);}
            $operationStatus=$status>=200&&$status<300?'succeeded':'failed';$this->operations->update((int)$session['account_id'],(int)$session['site_id'],$operationId,$operationStatus,$status,$idempotentBody);$this->idempotency->complete((int)$session['account_id'],(int)$session['site_id'],$key,$status,$idempotentBody);
        }
        $response=new \WP_REST_Response($responseBody,$status);foreach($result['headers'] as $header=>$value)$response->header($header,$value);return $response;
    }

    private function authenticateContext(\WP_REST_Request $request): array|\WP_REST_Response
    {
        $session=$this->sessions->authenticate((string)$request->get_header('X-WooGit-Session'));if($session===null)return new \WP_REST_Response(['code'=>'invalid_session'],401);
        $account=$this->accounts->get((int)$session['account_id']);if(!$account)return new \WP_REST_Response(['code'=>'account_inactive'],403);
        $site=$this->sites->getOwned((int)$session['account_id'],(int)$session['site_id']);if(!$site)return new \WP_REST_Response(['code'=>'site_not_owned'],403);
        if(!$this->entitlements->isAllowed((int)$session['account_id'],(int)$session['site_id'],'commerce'))return new \WP_REST_Response(['code'=>'not_entitled'],403);
        $session['site']=$site;return $session;
    }

    private function credentials(\WP_REST_Request $request): ?array
    {
        $keys=['wordpress_username','wordpress_application_password','consumer_key','consumer_secret'];$result=[];
        foreach($keys as $key){$header='X-WooGit-'.str_replace('_','-',ucwords($key,'_'));$value=(string)$request->get_header($header);if($value==='')return null;$result[$key]=$value;}
        return $result;
    }
}
