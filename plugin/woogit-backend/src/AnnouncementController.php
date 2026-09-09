<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class AnnouncementController
{
    private AnnouncementService $announcements; private SessionService $sessions; private EntitlementService $entitlements; private VersionGate $versionGate; private RateLimitService $rateLimits;
    public function __construct(){ $this->announcements=new AnnouncementService();$this->sessions=new SessionService();$this->entitlements=new EntitlementService();$this->versionGate=new VersionGate();$this->rateLimits=new RateLimitService(); }
    public function register(): void { register_rest_route('woogit/v1','/announcements',['methods'=>\WP_REST_Server::READABLE,'permission_callback'=>'__return_true','callback'=>[$this,'list']]); }
    public function list(\WP_REST_Request $request): \WP_REST_Response
    {
        $ip=$_SERVER['REMOTE_ADDR']??'unknown';$limit=$this->rateLimits->check('announcement_ip',hash('sha256',$ip),30,60);if(!$limit['allowed'])return new \WP_REST_Response(['code'=>'RATE_LIMITED','retry_after'=>$limit['retry_after'],'retryable'=>true],429);
        $version=sanitize_text_field((string)$request->get_header('X-WooGit-App-Version'));$gate=$this->versionGate->check($version);
        if(!$gate['allowed']&&$gate['code']==='APP_VERSION_REQUIRED')return new \WP_REST_Response(['code'=>'APP_VERSION_REQUIRED','message'=>'نسخه اپ باید ارسال شود.','minimum_supported_version'=>$gate['policy']['minimum_supported_version'],'latest_version'=>$gate['policy']['latest_version'],'recommended_version'=>$gate['policy']['recommended_version'],'update_required'=>false,'retryable'=>false],400);
        if(!$gate['allowed']&&$gate['code']==='APP_VERSION_DEPRECATED'){
            $updateUrl=esc_url_raw((string)($gate['policy']['update_url']??'https://woogit.ir/download-app/'));
            return new \WP_REST_Response(['announcements'=>[['id'=>'system-app-version-deprecated','type'=>'critical','title'=>'نسخه شما منسوخ شده است','message'=>'برای ادامه استفاده از WooGit باید اپ را به آخرین نسخه بروزرسانی کنید.','image'=>null,'priority'=>100000,'display_type'=>1,'actions'=>[['type'=>'update','label'=>'بروزرسانی','url'=>$updateUrl]],'dismissible'=>false,'notification_enabled'=>true,'notification_type'=>3,'notification_channel'=>'updates','starts_at'=>null,'expires_at'=>null]]],200);
        }
        $accountId=0;$siteId=0;$token=trim((string)$request->get_header('X-WooGit-Session'));if($token!==''){ $session=$this->sessions->authenticate($token);if($session){$accountId=(int)$session['account_id'];$siteId=(int)$session['site_id'];} }
        $items=$this->announcements->active($version,$accountId,$siteId);
        if($accountId>0&&$siteId>0){$expires=$this->entitlements->getExpiresAt($accountId,$siteId);if($expires!==null){$remaining=$expires-time();if($remaining>0&&$remaining<=7*DAY_IN_SECONDS)$items[]=['id'=>'system-entitlement-expiring','type'=>'warning','title'=>'اعتبار شما رو به اتمام است','message'=>'اعتبار این سایت کمتر از ۷ روز دیگر منقضی می‌شود.','image'=>null,'priority'=>90000,'display_type'=>1,'actions'=>[['type'=>'billing','label'=>'تمدید اعتبار','url'=>null]],'dismissible'=>true,'notification_enabled'=>false,'notification_type'=>1,'notification_channel'=>'announcements','starts_at'=>null,'expires_at'=>gmdate('Y-m-d H:i:s',$expires)];}}
        usort($items,static fn(array $a,array $b):int=>((int)($b['priority']??0))<=>((int)($a['priority']??0)));
        return new \WP_REST_Response(['announcements'=>array_slice($items,0,AnnouncementService::MAX)],200);
    }
}
