<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class AnnouncementController
{
    private AnnouncementService $announcements;
    private SessionService $sessions;
    private RateLimitService $rateLimits;

    public function __construct(){ $this->announcements=new AnnouncementService();$this->sessions=new SessionService();$this->rateLimits=new RateLimitService(); }

    public function register(): void
    {
        register_rest_route('woogit/v1','/announcements',['methods'=>\WP_REST_Server::READABLE,'permission_callback'=>'__return_true','callback'=>[$this,'list']]);
    }

    public function list(\WP_REST_Request $request): \WP_REST_Response
    {
        $ip=$_SERVER['REMOTE_ADDR']??'unknown';
        if(!$this->rateLimits->allow('announcement:ip:'.hash('sha256',$ip),30,60))return new \WP_REST_Response(['code'=>'RATE_LIMITED'],429);
        $version=sanitize_text_field((string)$request->get_header('X-WooGit-App-Version'));
        $accountId=0;$siteId=0;
        $token=trim((string)$request->get_header('X-WooGit-Session'));
        if($token!==''){
            $session=$this->sessions->authenticate($token);
            if($session){$accountId=(int)$session['account_id'];$siteId=(int)$session['site_id'];}
        }
        // This endpoint intentionally does not apply VersionGate: a deprecated client must still be able to fetch an in-app update banner.
        return new \WP_REST_Response(['announcements'=>$this->announcements->active($version!==''?$version:null,$accountId,$siteId)],200);
    }
}
