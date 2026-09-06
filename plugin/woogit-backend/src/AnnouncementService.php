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
        return is_array($items) ? array_values($items) : [];
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
