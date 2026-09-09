<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class VersionGate
{
    public function check(string $version): array
    {
        $version=trim($version);$policy=get_option('woogit_backend_version_policy',[]);if(!is_array($policy))$policy=[];
        $policy=array_merge(['latest_version'=>WOOGIT_BACKEND_VERSION,'recommended_version'=>WOOGIT_BACKEND_VERSION,'minimum_supported_version'=>'0.0.0','deprecated_versions'=>[],'update_url'=>'https://woogit.ir/download-app/'],$policy);
        if($version==='')return ['allowed'=>false,'code'=>'APP_VERSION_REQUIRED','policy'=>$policy];
        if(!preg_match('/^\d+(?:\.\d+){0,3}(?:[-+][0-9A-Za-z.-]+)?$/',$version))return ['allowed'=>false,'code'=>'APP_VERSION_INVALID','policy'=>$policy];
        $deprecated=array_map('strval',is_array($policy['deprecated_versions'])?$policy['deprecated_versions']:[]);
        if(in_array($version,$deprecated,true)||version_compare($version,(string)$policy['minimum_supported_version'],'<'))return ['allowed'=>false,'code'=>'APP_VERSION_DEPRECATED','policy'=>$policy];
        return ['allowed'=>true,'legacy'=>false,'policy'=>$policy];
    }
}
