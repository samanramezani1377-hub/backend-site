<?php
if (!defined('ABSPATH')) exit;
get_header();
?>
<section class="wg-page-head wg-container">
  <span class="wg-eyebrow">اپلیکیشن WooGit</span>
  <h1><?php echo esc_html(get_the_title() ?: 'دریافت WooGit'); ?></h1>
  <p>WooGit را روی موبایل داشته باشید و کارهای مهم فروشگاه ووکامرس را هرجا که هستید مدیریت و پیگیری کنید.</p>
</section>
<section class="wg-section wg-container">
  <div class="wg-grid wg-grid--2">
    <article class="wg-card">
      <span class="wg-eyebrow">Android</span>
      <h2>مدیریت فروشگاه از موبایل</h2>
      <p>سفارش‌ها و محصولات فروشگاهتان را بررسی کنید، وضعیت فروشگاه را دنبال کنید و کارهای روزمره را بدون وابستگی به کامپیوتر انجام دهید.</p>
      <a class="wg-btn wg-btn--primary wg-btn--large" href="https://github.com/samanramezani1377-hub/woogit" target="_blank" rel="noopener noreferrer">دریافت WooGit</a>
    </article>
    <article class="wg-card">
      <span class="wg-eyebrow">برای مدیران فروشگاه</span>
      <h2>همیشه یک قدم نزدیک‌تر به فروشگاه</h2>
      <p>وقتی در محل کار نیستید هم می‌توانید از وضعیت سفارش‌ها و محصولات باخبر بمانید و سریع‌تر به کارهای مهم فروشگاه رسیدگی کنید.</p>
      <a class="wg-btn wg-btn--ghost" href="<?php echo esc_url(woogit_page_url('register')); ?>">ساخت حساب WooGit</a>
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
    <h2>وقت آن است فروشگاهتان را همراه خودتان ببرید</h2>
    <p>اپلیکیشن را دریافت کنید و مدیریت فروشگاه را از موبایل شروع کنید.</p>
    <a class="wg-btn wg-btn--primary" href="https://github.com/samanramezani1377-hub/woogit" target="_blank" rel="noopener noreferrer">دریافت WooGit</a>
  </div>
</section>
<?php get_footer(); ?>
