<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class AnnouncementController
{
    private AnnouncementService $announcements;
    private SessionService $sessions;
    private EntitlementService $entitlements;
    private VersionGate $versionGate;
    private RateLimitService $rateLimits;

    public function __construct(){ $this->announcements=new AnnouncementService();$this->sessions=new SessionService();$this->entitlements=new EntitlementService();$this->versionGate=new VersionGate();$this->rateLimits=new RateLimitService(); }

    public function register(): void
    {
        register_rest_route('woogit/v1','/announcements',['methods'=>\WP_REST_Server::READABLE,'permission_callback'=>'__return_true','callback'=>[$this,'list']]);
    }

    public function list(\WP_REST_Request $request): \WP_REST_Response
    {
        $ip=$_SERVER['REMOTE_ADDR']??'unknown';
        if(!$this->rateLimits->allow('announcement:ip:'.hash('sha256',$ip),30,60))return new \WP_REST_Response(['code'=>'RATE_LIMITED'],429);
        $version=sanitize_text_field((string)$request->get_header('X-WooGit-App-Version'));
        $accountId=0;$siteId=0;$session=null;
        $token=trim((string)$request->get_header('X-WooGit-Session'));
        if($token!==''){$session=$this->sessions->authenticate($token);if($session){$accountId=(int)$session['account_id'];$siteId=(int)$session['site_id'];}}
        $items=$this->announcements->active($version!==''?$version:null,$accountId,$siteId);
        // VersionGate is intentionally not enforced here: deprecated clients must be able to retrieve the update banner.
        $gate=$this->versionGate->check($version);
        if(!$gate['allowed']){
            $items[]=['id'=>'system-app-version-deprecated','type'=>'critical','title'=>'نسخه اپ منسوخ شده است','message'=>'برای ادامه استفاده، اپ را به نسخه جدیدتر بروزرسانی کنید.','priority'=>100000,'display_type'=>1,'action'=>['type'=>'update','label'=>'بروزرسانی'],'dismissible'=>false,'starts_at'=>null,'expires_at'=>null];
        }
        if($accountId>0&&$siteId>0){
            $expires=$this->entitlements->getExpiresAt($accountId,$siteId);
            if($expires!==null){$remaining=$expires-time();if($remaining>0&&$remaining<=7*DAY_IN_SECONDS)$items[]=['id'=>'system-entitlement-expiring','type'=>'warning','title'=>'اعتبار شما رو به اتمام است','message'=>'اعتبار این سایت کمتر از ۷ روز دیگر منقضی می‌شود.','priority'=>90000,'display_type'=>1,'action'=>['type'=>'billing','label'=>'تمدید اعتبار'],'dismissible'=>true,'starts_at'=>null,'expires_at'=>gmdate('Y-m-d H:i:s',$expires)];}
        }
        usort($items,static fn(array $a,array $b):int=>((int)($b['priority']??0))<=>((int)($a['priority']??0)));
        return new \WP_REST_Response(['announcements'=>array_slice($items,0,AnnouncementService::MAX)],200);
    }
}
