<?php
if (!defined('ABSPATH')) exit;
get_header();
?>
<section class="wg-page-head wg-container">
  <span class="wg-eyebrow">اپلیکیشن WooGit</span>
  <h1><?php echo esc_html(get_the_title() ?: 'دریافت WooGit'); ?></h1>
  <p>اپلیکیشن WooGit را روی موبایل نصب کنید و کارهای مهم فروشگاه WooCommerce را هرجا که هستید مدیریت و پیگیری کنید.</p>
</section>
<section class="wg-section wg-container">
  <div class="wg-grid wg-grid--2">
    <article class="wg-card">
      <span class="wg-eyebrow">Android</span>
      <h2>مدیریت فروشگاه از موبایل</h2>
      <p>سفارش‌ها و محصولات فروشگاهتان را بررسی کنید، وضعیت فروشگاه را دنبال کنید و کارهای روزمره را بدون وابستگی به کامپیوتر انجام دهید.</p>
      <?php $app_url=woogit_theme_option('app_download_url',''); ?><?php if($app_url): ?><a class="wg-btn wg-btn--primary wg-btn--large" href="<?php echo esc_url($app_url); ?>" target="_blank" rel="noopener noreferrer">نصب اپ WooGit</a><?php else: ?><span class="wg-btn wg-btn--primary wg-btn--large" aria-disabled="true">لینک نصب به‌زودی</span><?php endif; ?>
    </article>
    <article class="wg-card">
      <span class="wg-eyebrow">برای مدیران فروشگاه</span>
      <h2>همیشه یک قدم نزدیک‌تر به فروشگاه</h2>
      <p>بعد از نصب اپ، فروشگاهتان را متصل کنید و سفارش‌ها، محصولات و سایر بخش‌های مهم فروشگاه را از موبایل مدیریت کنید.</p>
      <a class="wg-btn wg-btn--ghost" href="<?php echo esc_url(woogit_page_url('register')); ?>">اتصال فروشگاه</a>
    </article>
  </div>
</section>
<section class="wg-section wg-container">
  <div class="wg-section-head">
    <span class="wg-eyebrow">با WooGit چه چیزی در اختیار دارید؟</span>
    <h2>مدیریت ساده‌تر، هرجا که هستید</h2>
  </div>
  <div class="wg-grid wg-grid--3">
    <article class="wg-card"><h3>سفارش‌ها</h3><p>سفارش‌های فروشگاه را ببینید و وضعیت آن‌ها را از موبایل پیگیری کنید.</p></article>
    <article class="wg-card"><h3>محصولات</h3><p>به اطلاعات محصولات دسترسی داشته باشید و مدیریت آن‌ها را ساده‌تر کنید.</p></article>
    <article class="wg-card"><h3>وضعیت فروشگاه</h3><p>اطلاعات مهم فروشگاهتان را سریع‌تر ببینید و برای کارهای بعدی تصمیم بگیرید.</p></article>
  </div>
</section>
<section class="wg-cta">
  <div class="wg-container">
    <span class="wg-eyebrow">WooGit</span>
    <h2>مدیریت فروشگاه را از موبایل شروع کنید</h2>
    <p>اپلیکیشن WooGit را نصب کنید، سپس فروشگاهتان را متصل کنید و مدیریت روزمره را شروع کنید.</p>
    <?php if($app_url): ?><a class="wg-btn wg-btn--primary" href="<?php echo esc_url($app_url); ?>" target="_blank" rel="noopener noreferrer">نصب اپ WooGit</a><?php else: ?><span class="wg-btn wg-btn--primary" aria-disabled="true">لینک نصب به‌زودی</span><?php endif; ?>
  </div>
</section>
<?php get_footer(); ?>
