<?php
if (!defined('ABSPATH')) exit;
$slug = is_page() ? get_post_field('post_name', get_queried_object_id()) : '';
if ($slug === 'login') { get_template_part('templates/auth/login'); return; }
if ($slug === 'register') { get_template_part('templates/auth/register'); return; }
get_header(); ?><section class="wg-section"><div class="wg-container wg-prose"><?php if(have_posts()): while(have_posts()): the_post(); ?><article><h1><?php the_title(); ?></h1><?php the_content(); ?></article><?php endwhile; else: ?><p>محتوایی یافت نشد.</p><?php endif; ?></div></section><?php get_footer(); ?>
