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
      <?php $apk_id=absint(woogit_theme_option('app_apk_id',0)); $apk_url=$apk_id?woogit_apk_download_url($apk_id):''; $play_url=woogit_theme_option('app_google_play_url',''); $bazaar_url=woogit_theme_option('app_bazaar_url',''); $version=woogit_theme_option('app_version',''); ?>
      <?php if($apk_url): ?><a class="wg-btn wg-btn--primary wg-btn--large" href="<?php echo esc_url($apk_url); ?>">دانلود مستقیم APK</a><?php else: ?><span class="wg-btn wg-btn--primary wg-btn--large" aria-disabled="true">دانلود مستقیم به‌زودی</span><?php endif; ?>
      <?php if($version): ?><div style="margin-top:10px">نسخه <?php echo esc_html($version); ?></div><?php endif; ?>
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
  <div class="wg-grid wg-grid--3">
    <?php if($play_url): ?><article class="wg-card"><span class="wg-eyebrow">Google Play</span><h2>نصب از Google Play</h2><p>نسخه منتشرشده را از Google Play نصب کنید.</p><a class="wg-btn wg-btn--ghost" href="<?php echo esc_url($play_url); ?>" target="_blank" rel="noopener noreferrer">نصب از Google Play</a></article><?php endif; ?>
    <?php if($bazaar_url): ?><article class="wg-card"><span class="wg-eyebrow">بازار</span><h2>نصب از بازار</h2><p>نسخه منتشرشده را از بازار دریافت کنید.</p><a class="wg-btn wg-btn--ghost" href="<?php echo esc_url($bazaar_url); ?>" target="_blank" rel="noopener noreferrer">نصب از بازار</a></article><?php endif; ?>
    <?php if($apk_url): ?><article class="wg-card"><span class="wg-eyebrow">APK</span><h2>دانلود مستقیم</h2><p>آخرین APK قرارگرفته در سایت را مستقیماً دانلود کنید.</p><a class="wg-btn wg-btn--ghost" href="<?php echo esc_url($apk_url); ?>">دانلود APK</a></article><?php endif; ?>
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
    <?php if($apk_url): ?><a class="wg-btn wg-btn--primary" href="<?php echo esc_url($apk_url); ?>">دانلود APK</a><?php elseif($play_url): ?><a class="wg-btn wg-btn--primary" href="<?php echo esc_url($play_url); ?>" target="_blank" rel="noopener noreferrer">نصب از Google Play</a><?php elseif($bazaar_url): ?><a class="wg-btn wg-btn--primary" href="<?php echo esc_url($bazaar_url); ?>" target="_blank" rel="noopener noreferrer">نصب از بازار</a><?php else: ?><span class="wg-btn wg-btn--primary" aria-disabled="true">لینک نصب به‌زودی</span><?php endif; ?>
  </div>
</section>
<?php get_footer(); ?>
