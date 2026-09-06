<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class AnnouncementController
{
    private AnnouncementService $announcements;
    private VersionGate $versionGate;
    private RateLimitService $rateLimits;

    public function __construct(){ $this->announcements=new AnnouncementService(); $this->versionGate=new VersionGate(); $this->rateLimits=new RateLimitService(); }

    public function register(): void
    {
        register_rest_route('woogit/v1','/announcements',[
            'methods'=>\WP_REST_Server::READABLE,
            'permission_callback'=>'__return_true',
            'callback'=>[$this,'list'],
        ]);
    }

    public function list(\WP_REST_Request $request): \WP_REST_Response
    {
        $ip=$_SERVER['REMOTE_ADDR']??'unknown';
        if(!$this->rateLimits->allow('announcement:ip:'.hash('sha256',$ip),30,60)) return new \WP_REST_Response(['code'=>'RATE_LIMITED'],429);
        $version=sanitize_text_field((string)$request->get_header('X-WooGit-App-Version'));
        $gate=$this->versionGate->check($version);
        if(!$gate['allowed']) return new \WP_REST_Response(['code'=>$gate['code']??'APP_VERSION_REJECTED','version'=>$version,'update_required'=>true,'minimum_supported_version'=>$gate['policy']['minimum_supported_version']??null,'latest_version'=>$gate['policy']['latest_version']??null,'recommended_version'=>$gate['policy']['recommended_version']??null],426);
        return new \WP_REST_Response(['announcements'=>$this->announcements->active($version!==''?$version:null)],200);
    }
}
