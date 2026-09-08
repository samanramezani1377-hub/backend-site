<?php
if(!defined('ABSPATH'))exit;
$portal=woogit_is_portal();
$logo=get_custom_logo();
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="<?php echo esc_attr(get_bloginfo('description')); ?>">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="wg-skip" href="#main">پرش به محتوا</a>
<header class="wg-header">
  <div class="wg-container wg-header__inner">
    <?php if($logo): ?>
      <div class="wg-brand"><?php echo $logo; ?></div>
    <?php else: ?>
      <a class="wg-brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="WooGit">WooGit</a>
    <?php endif; ?>
    <button class="wg-menu-toggle" type="button" aria-expanded="false" aria-controls="wg-nav" aria-label="باز کردن منوی اصلی"><span aria-hidden="true">☰</span><span class="screen-reader-text">منو</span></button>
    <nav id="wg-nav" class="wg-nav" aria-label="ناوبری اصلی">
      <?php if($portal): ?>
        <a href="<?php echo esc_url(woogit_page_url('portal')); ?>">پرتال</a>
        <a href="<?php echo esc_url(woogit_page_url('subscription')); ?>">اشتراک</a>
        <a href="<?php echo esc_url(woogit_page_url('billing')); ?>">صورتحساب</a>
        <a href="<?php echo esc_url(woogit_page_url('payments')); ?>">پرداخت‌ها</a>
        <a href="<?php echo esc_url(woogit_page_url('connected-site')); ?>">فروشگاه</a>
        <a href="<?php echo esc_url(woogit_page_url('account-security')); ?>">امنیت</a>
      <?php else: ?>
        <a href="<?php echo esc_url(woogit_page_url('features')); ?>">قابلیت‌ها</a>
        <a href="<?php echo esc_url(woogit_page_url('how-it-works')); ?>">نحوه کار</a>
        <a href="<?php echo esc_url(woogit_page_url('pricing')); ?>">قیمت</a>
        <a href="<?php echo esc_url(woogit_page_url('faq')); ?>">سؤالات متداول</a>
        <a href="<?php echo esc_url(woogit_page_url('documentation')); ?>">مستندات</a>
        <a href="<?php echo esc_url(woogit_page_url('support')); ?>">پشتیبانی</a>
      <?php endif; ?>
      <button class="wg-theme-toggle" type="button" data-woogit-theme-toggle aria-label="تغییر پوسته" aria-pressed="false">◐</button>
      <?php if($portal): ?>
        <button class="wg-btn wg-btn--ghost" type="button" data-woogit-logout>خروج</button>
      <?php else: ?>
        <a class="wg-btn wg-btn--ghost" href="<?php echo esc_url(woogit_page_url('login')); ?>">ورود</a>
        <a class="wg-btn" href="<?php echo esc_url(woogit_page_url('register')); ?>">شروع کنید</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main id="main">
