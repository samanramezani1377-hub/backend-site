<?php
if (!defined('ABSPATH')) exit;
function woogit_enamad_settings() { $o=woogit_theme_options(); return ['enabled'=>!empty($o['enamad_id']) || !empty($o['enamad_code']),'namad_id'=>$o['enamad_id']??'','namad_code'=>$o['enamad_code']??'','verification_url'=>$o['enamad_verification_url']??'','alt_text'=>$o['enamad_alt']??'']; }
