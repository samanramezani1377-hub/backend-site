<?php
if (!defined('ABSPATH')) exit;
$slug = is_page() ? get_post_field('post_name', get_queried_object_id()) : '';
$portal_templates = ['portal'=>'overview','subscription'=>'subscription','billing'=>'billing','payments'=>'payments','connected-site'=>'connected-site','account-security'=>'account-security'];
if ($slug === 'login') { get_template_part('templates/auth/login'); return; }
if ($slug === 'register') { get_template_part('templates/auth/register'); return; }
if ($slug === 'payment-result') { get_template_part('templates/public/payment-result'); return; }
if (isset($portal_templates[$slug])) { get_template_part('templates/portal/'.$portal_templates[$slug]); return; }
get_header(); ?><section class="wg-section"><div class="wg-container wg-prose"><?php if(have_posts()): while(have_posts()): the_post(); ?><article><h1><?php the_title(); ?></h1><?php the_content(); ?></article><?php endwhile; else: ?><p>محتوایی یافت نشد.</p><?php endif; ?></div></section><?php get_footer(); ?>
