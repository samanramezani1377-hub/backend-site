<?php
if (!defined('ABSPATH')) exit;
get_header();
?>
<section class="wg-section wg-container" aria-labelledby="wg-404-title">
  <div class="wg-card wg-content-card" style="text-align:center">
    <span class="wg-eyebrow">404</span>
    <h1 id="wg-404-title">صفحه موردنظر پیدا نشد</h1>
    <p>آدرسی که دنبال آن هستید وجود ندارد یا ممکن است جابه‌جا شده باشد.</p>
    <div class="wg-actions" style="justify-content:center">
      <a class="wg-btn wg-btn--primary" href="<?php echo esc_url(home_url('/')); ?>">بازگشت به صفحه اصلی</a>
      <a class="wg-btn wg-btn--ghost" href="<?php echo esc_url(woogit_page_url('download')); ?>">نصب اپ WooGit</a>
    </div>
  </div>
</section>
<?php get_footer();
