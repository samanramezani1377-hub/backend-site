<?php
if(!defined('ABSPATH'))exit;
$options=woogit_theme_options();
$portal=woogit_is_portal_page();
$logged_in=woogit_logged_in();
$managed_logo=woogit_theme_image($options['logo_id']??0,'medium');
$brand_name=$options['brand_name']??'WooGit';
$logo=$managed_logo?'<a class="wg-brand" href="'.esc_url(home_url('/')).'" aria-label="'.esc_attr($brand_name).'"><img src="'.esc_url($managed_logo).'" alt="'.esc_attr($brand_name).'" style="max-height:48px;width:auto;display:block"></a>':get_custom_logo();
?><!doctype html><html <?php language_attributes(); ?>><head><meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><?php wp_head(); ?></head><body <?php body_class($logged_in?'wg-user-authenticated':'wg-user-guest'); ?>><?php wp_body_open(); ?><a class="wg-skip" href="#main">پرش به محتوا</a>
<header class="wg-header"><div class="wg-container wg-header__inner">
<?php if($logo): ?><div class="wg-brand-wrap"><?php echo $logo; ?></div><?php else: ?><a class="wg-brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr($brand_name); ?>"><?php echo esc_html($brand_name); ?></a><?php endif; ?>
<button class="wg-menu-toggle" type="button" aria-expanded="false" aria-controls="wg-nav" aria-label="باز کردن منوی اصلی"><span class="wg-menu-icon" aria-hidden="true"><i></i><i></i><i></i></span><span class="screen-reader-text">منو</span></button>
<nav id="wg-nav" class="wg-nav" aria-label="ناوبری اصلی" aria-hidden="true">
<?php if($logged_in): ?>
  <div class="wg-account-nav" aria-label="ناوبری حساب">
    <?php foreach(woogit_account_nav() as $item): ?>
      <a class="<?php echo $portal&&get_query_var('pagename')===$item[0]?'is-active':''; ?>" href="<?php echo esc_url(woogit_page_url($item[0])); ?>"><?php echo esc_html($item[1]); ?></a>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <a href="<?php echo esc_url(woogit_page_url('features')); ?>">قابلیت‌ها</a><a href="<?php echo esc_url(woogit_page_url('how-it-works')); ?>">نحوه کار</a><a href="<?php echo esc_url(woogit_page_url('pricing')); ?>">قیمت</a><a href="<?php echo esc_url(woogit_page_url('faq')); ?>">سؤالات متداول</a><a href="<?php echo esc_url(woogit_page_url('documentation')); ?>">مستندات</a><a href="<?php echo esc_url(woogit_page_url('support')); ?>">پشتیبانی</a>
<?php endif; ?>
<button class="wg-theme-toggle" type="button" data-woogit-theme-toggle aria-label="تغییر پوسته" aria-pressed="false">◐</button>
<?php if($logged_in): ?>
  <a class="wg-account-pill <?php echo $portal?'is-current':''; ?>" href="<?php echo esc_url(woogit_page_url('portal')); ?>" aria-label="حساب کاربری">حساب من</a>
  <button class="wg-btn wg-btn--ghost" type="button" data-woogit-logout>خروج</button>
<?php else: ?>
  <a class="wg-btn wg-btn--ghost" href="<?php echo esc_url(woogit_page_url('login')); ?>">ورود</a><a class="wg-btn wg-btn--primary" href="<?php echo esc_url(woogit_page_url('download')); ?>">نصب اپ WooGit</a>
<?php endif; ?>
</nav></div></header><main id="main">
