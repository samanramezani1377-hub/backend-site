<?php
if (!defined('ABSPATH')) exit;

function woogit_enamad_settings() {
    $o=woogit_theme_options();
    return [
        'enabled'=>($o['enamad_enabled']??'0')==='1',
        'namad_id'=>sanitize_text_field($o['enamad_id']??''),
        'namad_code'=>$o['enamad_code']??'',
        'verification_url'=>esc_url($o['enamad_verification_url']??''),
        'alt_text'=>sanitize_text_field($o['enamad_alt']??''),
        'optional_text'=>sanitize_text_field($o['enamad_optional_text']??''),
        'image_media_id'=>absint($o['enamad_image_id']??0),
        'placement'=>in_array(($o['enamad_placement']??'footer'),['footer','contact'],true)?$o['enamad_placement']:'footer',
    ];
}

function woogit_print_head_meta() {
    $o=woogit_theme_options();
    $favicon=woogit_theme_image(absint($o['favicon_id']??0),'full');
    $og=woogit_theme_image(absint($o['og_image_id']??0),'large');
    if($favicon) echo '<link rel="icon" href="'.esc_url($favicon).'">';
    if($og) {
        echo '<meta property="og:image" content="'.esc_url($og).'">';
        echo '<meta name="twitter:card" content="summary_large_image">';
        echo '<meta name="twitter:image" content="'.esc_url($og).'">';
    }
}
