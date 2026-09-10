<?php if (!defined('ABSPATH')) exit; get_header(); ?>
<section class="wg-auth" aria-labelledby="wg-register-title">
  <div class="wg-auth__card wg-card wg-register">
    <div class="wg-auth-head">
      <span class="wg-eyebrow">راه‌اندازی امن</span>
      <h1 id="wg-register-title">اتصال فروشگاه</h1>
      <p>ثبت‌نام در چهار مرحله انجام می‌شود؛ اطلاعات حساس فقط برای اعتبارسنجی لازم به Backend ارسال می‌شوند.</p>
    </div>
    <div class="wg-stepper" aria-label="مراحل اتصال" role="list">
      <span class="is-active" role="listitem">۱<span>سایت</span></span>
      <span role="listitem">۲<span>دسترسی</span></span>
      <span role="listitem">۳<span>فروشگاه</span></span>
      <span role="listitem">۴<span>تأیید</span></span>
    </div>
    <form data-woogit-auth="register" data-multistep novalidate>
      <fieldset data-step="1">
        <legend>۱. اطلاعات سایت</legend>
        <div class="wg-field">
          <label for="woogit-register-site-url">آدرس سایت</label>
          <input id="woogit-register-site-url" name="site_url" type="url" inputmode="url" dir="ltr" autocomplete="url" required placeholder="example.com" aria-describedby="woogit-register-site-url-hint">
          <small id="woogit-register-site-url-hint" class="wg-form-hint">فقط نام دامنه سایت فروشگاه را وارد کنید؛ نیازی به https:// نیست.</small>
        </div>
        <button class="wg-btn wg-step-next" type="button">ادامه</button>
      </fieldset>
      <fieldset data-step="2" hidden>
        <legend>۲. دسترسی WordPress</legend>
        <div class="wg-field">
          <label for="woogit-register-wp-username">نام کاربری WordPress</label>
          <input id="woogit-register-wp-username" name="wp_username" type="text" autocomplete="username" required>
        </div>
        <div class="wg-field">
          <label for="woogit-register-wp-application-password">Application Password</label>
          <input id="woogit-register-wp-application-password" name="wp_application_password" type="password" autocomplete="off" required>
        </div>
        <p class="wg-form-hint">این اطلاعات فقط برای اعتبارسنجی اتصال استفاده می‌شوند و توسط Theme به‌صورت پایدار ذخیره نمی‌شوند.</p>
        <div class="wg-step-actions"><button class="wg-btn wg-btn--ghost wg-step-prev" type="button">بازگشت</button><button class="wg-btn wg-step-next" type="button">ادامه</button></div>
      </fieldset>
      <fieldset data-step="3" hidden>
        <legend>۳. دسترسی WooCommerce</legend>
        <div class="wg-field">
          <label for="woogit-register-consumer-key">Consumer Key</label>
          <input id="woogit-register-consumer-key" name="consumer_key" type="text" dir="ltr" autocomplete="off" required>
        </div>
        <div class="wg-field">
          <label for="woogit-register-consumer-secret">Consumer Secret</label>
          <input id="woogit-register-consumer-secret" name="consumer_secret" type="password" dir="ltr" autocomplete="off" required>
        </div>
        <div class="wg-step-actions"><button class="wg-btn wg-btn--ghost wg-step-prev" type="button">بازگشت</button><button class="wg-btn wg-step-next" type="button">ادامه</button></div>
      </fieldset>
      <fieldset data-step="4" hidden>
        <legend>۴. بررسی و اتصال</legend>
        <div class="wg-review" data-register-review></div>
        <p class="wg-hint">با اتصال، Backend ابتدا اعتبار WordPress و WooCommerce و سپس وضعیت حساب WooGit و رمز عبور وب را بررسی می‌کند.</p>
        <div class="wg-step-actions"><button class="wg-btn wg-btn--ghost wg-step-prev" type="button">ویرایش</button><button class="wg-btn wg-btn--large" type="submit">اتصال امن فروشگاه</button></div>
      </fieldset>
      <div class="wg-form-message" role="status" aria-live="polite"></div>
      <p class="wg-auth-switch">قبلاً حساب دارید؟ <a href="<?php echo esc_url(woogit_page_url('login')); ?>">ورود</a></p>
    </form>
  </div>
</section>
<?php get_footer(); ?>