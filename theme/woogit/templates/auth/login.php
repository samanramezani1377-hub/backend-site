<?php if (!defined('ABSPATH')) exit; get_header(); ?>
<section class="wg-auth" aria-labelledby="wg-login-title">
  <div class="wg-auth__card wg-card">
    <div class="wg-auth-head">
      <span class="wg-eyebrow">حساب WooGit</span>
      <h1 id="wg-login-title">ورود به حساب</h1>
      <p>برای ورود، آدرس سایت و رمز عبور وب WooGit خود را وارد کنید.</p>
    </div>
    <form data-woogit-auth="login" novalidate>
      <div class="wg-field">
        <label for="woogit-login-site-url">آدرس سایت</label>
        <input id="woogit-login-site-url" name="site_url" type="url" inputmode="url" dir="ltr" autocomplete="url" required placeholder="https://example.com" aria-describedby="woogit-login-site-url-hint">
        <small id="woogit-login-site-url-hint" class="wg-form-hint">آدرس کامل سایت متصل‌شده به WooGit.</small>
      </div>
      <div class="wg-field">
        <label for="woogit-login-web-password">رمز عبور وب</label>
        <input id="woogit-login-web-password" name="web_password" type="password" autocomplete="current-password" required>
      </div>
      <div class="wg-form-message" role="status" aria-live="polite"></div>
      <button class="wg-btn wg-btn--large" type="submit">ورود</button>
      <p class="wg-auth-switch">حساب ندارید؟ <a href="<?php echo esc_url(woogit_page_url('register')); ?>">اتصال فروشگاه</a></p>
    </form>
  </div>
</section>
<?php get_footer(); ?>
