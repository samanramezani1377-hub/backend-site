<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class AnnouncementService
{
    public const MAX = 100;
    private const OPTION = 'woogit_backend_announcements';

    public function all(): array
    {
        $items = get_option(self::OPTION, []);
        if(!is_array($items))return [];
        return array_values(array_map([$this,'normalize'], $items));
    }

    public function active(?string $appVersion = null, int $accountId = 0, int $siteId = 0): array
    {
        $now=time();$items=[];
        foreach($this->all() as $item){
            if(!is_array($item))continue;
            $starts=!empty($item['starts_at'])?strtotime((string)$item['starts_at']):null;
            $expires=!empty($item['expires_at'])?strtotime((string)$item['expires_at']):null;
            if($starts!==null&&$starts>$now)continue;
            if($expires!==null&&$expires<=$now)continue;
            if(!$this->targets($item,$appVersion,$accountId,$siteId))continue;
            $items[]=$item;
        }
        usort($items,static fn(array $a,array $b):int=>((int)($b['priority']??0))<=>((int)($a['priority']??0)));
        return array_slice($items,0,self::MAX);
    }

    private function normalize($item): array
    {
        if(!is_array($item))return [];
        $actions=$item['actions']??null;
        if(!is_array($actions)){
            $legacy=$item['action']??null;
            $actions=is_array($legacy)&&$legacy!==[]?[$legacy]:[];
        }
        $image=$item['image']??null;
        if(!is_array($image)||empty($image['url']))$image=null;else$image=['url'=>(string)$image['url'],'alt'=>(string)($image['alt']??'')];
        return [
            'id'=>(string)($item['id']??''),
            'type'=>(string)($item['type']??'info'),
            'title'=>(string)($item['title']??''),
            'message'=>(string)($item['message']??''),
            'image'=>$image,
            'priority'=>(int)($item['priority']??0),
            'display_type'=>(int)($item['display_type']??1),
            'actions'=>array_values($actions),
            'dismissible'=>!empty($item['dismissible']),
            'notification_enabled'=>!empty($item['notification_enabled']),
            'notification_type'=>max(1,(int)($item['notification_type']??1)),
            'notification_channel'=>(string)($item['notification_channel']??'announcements'),
            'starts_at'=>$item['starts_at']??null,
            'expires_at'=>$item['expires_at']??null,
            'app_versions'=>is_array($item['app_versions']??null)?array_values($item['app_versions']):[],
            'account_ids'=>is_array($item['account_ids']??null)?array_values($item['account_ids']):[],
            'site_ids'=>is_array($item['site_ids']??null)?array_values($item['site_ids']):[],
        ];
    }

    private function targets(array $item, ?string $version, int $accountId, int $siteId): bool
    {
        foreach([['app_versions',$version],['account_ids',$accountId],['site_ids',$siteId]] as [$field,$value]){
            $targets=$item[$field]??[];
            if(!is_array($targets)||$targets===[])continue;
            if($value===null||$value<=0||!in_array((string)$value,array_map('strval',$targets),true))return false;
        }
        return true;
    }
}
