<?php
if (!defined('ABSPATH')) exit;
get_header();
?>
<section class="wg-page-head wg-container">
  <span class="wg-eyebrow">WooGit App</span>
  <h1><?php echo esc_html(get_the_title() ?: 'دریافت WooGit'); ?></h1>
  <p>اپلیکیشن اندروید WooGit را دریافت کنید و مدیریت فروشگاه ووکامرس را از موبایل ادامه دهید.</p>
</section>

<section class="wg-section wg-container">
  <div class="wg-grid wg-grid--2">
    <article class="wg-card">
      <span class="wg-eyebrow">Android</span>
      <h2>اپلیکیشن WooGit</h2>
      <p>نسخه اندروید WooGit برای مدیریت سفارش‌ها، محصولات و اعلان‌های فروشگاه طراحی شده است.</p>
      <a class="wg-btn wg-btn--primary wg-btn--large" href="https://github.com/samanramezani1377-hub/woogit" target="_blank" rel="noopener noreferrer">دریافت اپلیکیشن</a>
    </article>
    <article class="wg-card">
      <span class="wg-eyebrow">نسخه</span>
      <h2>نسخه قابل دریافت</h2>
      <p>آخرین وضعیت build و فایل‌های قابل دریافت اپلیکیشن از مخزن رسمی WooGit قابل پیگیری است.</p>
      <a class="wg-btn wg-btn--ghost" href="https://github.com/samanramezani1377-hub/woogit/actions" target="_blank" rel="noopener noreferrer">مشاهده Buildها</a>
    </article>
  </div>
</section>

<section class="wg-cta">
  <div class="wg-container">
    <span class="wg-eyebrow">WooGit</span>
    <h2>برای استفاده از نسخه وب آماده‌اید؟</h2>
    <p>اگر قبلاً اپلیکیشن را دریافت کرده‌اید، می‌توانید از مسیر وب وارد حساب خود شوید.</p>
    <a class="wg-btn wg-btn--ghost" href="<?php echo esc_url(woogit_page_url('login')); ?>">ورود به پرتال</a>
  </div>
</section>
<?php get_footer(); ?>
