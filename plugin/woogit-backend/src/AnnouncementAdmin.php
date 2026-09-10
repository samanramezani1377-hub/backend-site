<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class AnnouncementAdmin
{
    private AnnouncementService $announcements;
    private const OPTION='woogit_backend_announcements';
    public function __construct(){ $this->announcements=new AnnouncementService(); }
    public function register(): void{add_submenu_page('woogit','WooGit Announcements','Announcements','manage_options','woogit-announcements',[$this,'render']);}
    public function render(): void
    {
        if(!current_user_can('manage_options'))return;$error='';$saved=false;
        if(($_SERVER['REQUEST_METHOD']??'')==='POST'&&isset($_POST['woogit_announcement_save'])){check_admin_referer('woogit_announcement_save');$items=$this->normalize($_POST['announcements']??[],$error);if($error===''){update_option(self::OPTION,$items,false);$saved=true;}}
