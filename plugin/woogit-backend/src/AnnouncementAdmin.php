<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class AnnouncementAdmin
{
    private AnnouncementService $announcements;
    private const OPTION='woogit_backend_announcements';
    public function __construct(){ $this->announcements=new AnnouncementService(); }
    public function register(): void{add_options_page('WooGit Announcements','WooGit Announcements','manage_options','woogit-announcements',[$this,'render']);}
    public function render(): void
    {
        if(!current_user_can('manage_options'))return;$error='';$saved=false;
        if(($_SERVER['REQUEST_METHOD']??'')==='POST'&&isset($_POST['woogit_announcement_save'])){check_admin_referer('woogit_announcement_save');$items=$this->normalize($_POST['announcements']??[],$error);if($error===''){update_option(self::OPTION,$items,false);$saved=true;}}
        $items=$this->announcements->all();
        if($saved)echo '<div class="notice notice-success is-dismissible"><p>WooGit announcements saved.</p></div>';
        if($error!=='')echo '<div class="notice notice-error"><p>'.esc_html($error).'</p></div>';
        echo '<div class="wrap"><h1>WooGit Announcements</h1><p>These records are data only. The app owns the visual presentation; <code>display_type</code> is an opaque numeric contract.</p><form method="post">'.wp_nonce_field('woogit_announcement_save','_wpnonce',true,false);
        echo '<table class="widefat striped"><thead><tr><th>ID</th><th>Type</th><th>Title</th><th>Message</th><th>Priority</th><th>Display type</th><th>Action JSON</th><th>Dismissible</th><th>Starts</th><th>Expires</th><th>App versions</th><th>Account IDs</th><th>Site IDs</th></tr></thead><tbody>';
        if(!$items)echo '<tr><td colspan="13">No announcements.</td></tr>';
        foreach($items as $i=>$a){echo '<tr>';foreach(['id','type','title','message'] as $f)echo '<td><input type="text" name="announcements['.$i.']['.$f.']" value="'.esc_attr((string)($a[$f]??'')).'" class="regular-text"></td>';echo '<td><input type="number" name="announcements['.$i.'][priority]" value="'.esc_attr((string)($a['priority']??0)).'" min="0" max="100000"></td><td><input type="number" name="announcements['.$i.'][display_type]" value="'.esc_attr((string)($a['display_type']??1)).'" min="1" max="999"></td><td><textarea name="announcements['.$i.'][action]" rows="3" cols="20">'.esc_textarea(wp_json_encode($a['action']??null)).'</textarea></td><td><input type="checkbox" name="announcements['.$i.'][dismissible]" value="1" '.checked(!empty($a['dismissible']),true,false).'></td>';foreach(['starts_at','expires_at'] as $f)echo '<td><input type="datetime-local" name="announcements['.$i.']['.$f.']" value="'.esc_attr($this->datetimeLocal($a[$f]??'')).'"></td>';foreach(['app_versions','account_ids','site_ids'] as $f)echo '<td><input type="text" name="announcements['.$i.']['.$f.']" value="'.esc_attr(implode(', ',is_array($a[$f]??null)?$a[$f]:[])).'" placeholder="comma separated"></td>';echo '</tr>';}
        echo '</tbody></table><p><button class="button button-primary" name="woogit_announcement_save" value="1">Save announcements</button></p></form></div>';
    }
    private function normalize($raw,string &$error): array
    {
        $error='';if(!is_array($raw))return [];$out=[];
        foreach(array_slice($raw,0,AnnouncementService::MAX) as $a){if(!is_array($a))continue;$id=sanitize_key($a['id']??'');if($id===''){$error='Every announcement needs an ID.';break;}$type=sanitize_key($a['type']??'info');$title=sanitize_text_field($a['title']??'');$message=sanitize_textarea_field($a['message']??'');$display=(int)($a['display_type']??1);if($display<1||$display>999){$error='Display type must be between 1 and 999.';break;}$action=null;if(isset($a['action'])&&trim((string)$a['action'])!==''){$action=json_decode(wp_unslash((string)$a['action']),true);if(json_last_error()!==JSON_ERROR_NONE||!is_array($action)){$error='Action must be valid JSON object.';break;}}$out[]=['id'=>$id,'type'=>$type,'title'=>$title,'message'=>$message,'priority'=>max(0,min(100000,(int)($a['priority']??0))),'display_type'=>$display,'action'=>$action,'dismissible'=>!empty($a['dismissible']),'starts_at'=>$this->normalizeDate($a['starts_at']??''),'expires_at'=>$this->normalizeDate($a['expires_at']??''),'app_versions'=>$this->csv($a['app_versions']??''),'account_ids'=>$this->csvInts($a['account_ids']??''),'site_ids'=>$this->csvInts($a['site_ids']??'')];}
        return $out;
    }
    private function csv($v):array{return array_values(array_filter(array_map('trim',explode(',',(string)$v))));}
    private function csvInts($v):array{return array_values(array_unique(array_filter(array_map('intval',explode(',',(string)$v)),static fn(int $n):bool=>$n>0)));}
    private function normalizeDate($v):?string{$v=trim((string)$v);if($v==='')return null;$ts=strtotime($v);return $ts===false?null:gmdate('Y-m-d H:i:s',$ts);}
    private function datetimeLocal($v):string{$ts=$v?strtotime((string)$v):false;return $ts===false?'':gmdate('Y-m-d\TH:i',$ts);}
}
