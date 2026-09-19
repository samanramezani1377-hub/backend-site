<?php
if(!defined('ABSPATH'))exit;
$d=woogit_portal_data();
get_header();
if(!$d['authenticated']){woogit_render_status('error','نشست شما منقضی شده است. دوباره وارد شوید.');get_footer();return;}
$u=$d['user'];$b=$d['billing'];$site=$d['site'];
$billingStatus=woogit_portal_status(woogit_portal_value($b,['status','subscription_status'],'unknown'));
$entitlement=woogit_portal_status(woogit_portal_value($b,['entitlement_status','entitlement.status'],'unknown'));
$siteStatus=woogit_portal_status(woogit_portal_value($site,['status','connection_status'],'unknown'));
$name=woogit_portal_value($u,['name','display_name'],'کاربر');
$email=woogit_portal_value($u,['email','contact_email'],'—');
$plan=woogit_portal_value($b,['plan_name','plan.name','plan'],'بدون اشتراک');
$expiry=woogit_portal_value($b,['next_billing_at','renews_at','expires_at'],'—');
$siteUrl=woogit_portal_value($site,['url','site_url','domain'],'—');
$payments=(int)($d['payments']['total']??0);
$webPassword=!empty($u['web_password_configured']);
?>
<section class="wg-section wg-portal wg-account-dashboard">
<div class="wg-container">
  <div class="wg-account-hero">
    <div>
      <span class="wg-eyebrow">حساب WooGit</span>
      <h1>سلام، <?php echo woogit_safe_text($name); ?></h1>
      <p>حساب، اشتراک، فروشگاه متصل و امنیت ورودت را از یکجا مدیریت کن.</p>
    </div>
    <div class="wg-account-hero__actions">
      <a class="wg-btn wg-btn--primary" href="<?php echo esc_url(woogit_page_url('subscription')); ?>">مدیریت اشتراک</a>
      <a class="wg-btn wg-btn--ghost" href="<?php echo esc_url(woogit_page_url('account-security')); ?>">تنظیمات حساب</a>
    </div>
  </div>

  <div class="wg-account-grid">
    <article class="wg-card wg-account-card wg-account-card--identity">
      <div class="wg-account-card__head"><div><span class="wg-eyebrow">حساب</span><h2>اطلاعات حساب</h2></div><span class="wg-status wg-status--success">واردشده</span></div>
      <dl class="wg-account-list">
        <div><dt>نام</dt><dd><?php echo woogit_safe_text($name); ?></dd></div>
        <div><dt>ایمیل</dt><dd dir="ltr"><?php echo woogit_safe_text($email); ?></dd></div>
        <div><dt>امنیت ورود</dt><dd><?php echo $webPassword?'رمز وب تنظیم شده':'نیازمند تنظیم رمز وب'; ?></dd></div>
      </dl>
      <a class="wg-text-link" href="<?php echo esc_url(woogit_page_url('account-security')); ?>">مدیریت حساب و امنیت ←</a>
    </article>

    <article class="wg-card wg-account-card">
      <div class="wg-account-card__head"><div><span class="wg-eyebrow">اشتراک</span><h2><?php echo woogit_safe_text($plan); ?></h2></div><span class="wg-status wg-status--<?php echo esc_attr($entitlement[1]); ?>"><?php echo woogit_safe_text($entitlement[0]); ?></span></div>
      <p>وضعیت اشتراک: <strong><?php echo woogit_safe_text($billingStatus[0]); ?></strong></p>
      <p>انقضا / تمدید: <strong><?php echo woogit_safe_text($expiry); ?></strong></p>
      <a class="wg-text-link" href="<?php echo esc_url(woogit_page_url('subscription')); ?>">مشاهده و مدیریت اشتراک ←</a>
    </article>

    <article class="wg-card wg-account-card">
      <div class="wg-account-card__head"><div><span class="wg-eyebrow">فروشگاه</span><h2>فروشگاه متصل</h2></div><span class="wg-status wg-status--<?php echo esc_attr($siteStatus[1]); ?>"><?php echo woogit_safe_text($siteStatus[0]); ?></span></div>
      <strong class="wg-value-break" dir="ltr"><?php echo woogit_safe_text($siteUrl); ?></strong>
      <p>شناسه سایت: <?php echo woogit_safe_text(woogit_portal_value($site,['site_id','id'],'—')); ?></p>
      <a class="wg-text-link" href="<?php echo esc_url(woogit_page_url('connected-site')); ?>">جزئیات فروشگاه ←</a>
    </article>

    <article class="wg-card wg-account-card">
      <div class="wg-account-card__head"><div><span class="wg-eyebrow">پرداخت</span><h2>تاریخچه پرداخت</h2></div><strong class="wg-account-number"><?php echo $payments; ?></strong></div>
      <p>پرداخت ثبت‌شده در حساب شما.</p>
      <div class="wg-actions"><a class="wg-btn wg-btn--ghost" href="<?php echo esc_url(woogit_page_url('payments')); ?>">مشاهده پرداخت‌ها</a><a class="wg-btn wg-btn--ghost" href="<?php echo esc_url(woogit_page_url('billing')); ?>">صورتحساب</a></div>
    </article>
  </div>

  <div class="wg-account-links">
    <div class="wg-section__head"><div><span class="wg-eyebrow">دسترسی سریع</span><h2>مدیریت حساب</h2></div></div>
    <div class="wg-grid wg-grid--3">
      <a class="wg-card wg-card--interactive" href="<?php echo esc_url(woogit_page_url('subscription')); ?>"><span>اشتراک</span><strong>پلن و دسترسی ←</strong></a>
      <a class="wg-card wg-card--interactive" href="<?php echo esc_url(woogit_page_url('billing')); ?>"><span>صورتحساب</span><strong>وضعیت مالی ←</strong></a>
      <a class="wg-card wg-card--interactive" href="<?php echo esc_url(woogit_page_url('payments')); ?>"><span>پرداخت‌ها</span><strong>تاریخچه پرداخت ←</strong></a>
      <a class="wg-card wg-card--interactive" href="<?php echo esc_url(woogit_page_url('connected-site')); ?>"><span>فروشگاه</span><strong>فروشگاه متصل ←</strong></a>
      <a class="wg-card wg-card--interactive" href="<?php echo esc_url(woogit_page_url('account-security')); ?>"><span>امنیت</span><strong>رمز و ورود ←</strong></a>
    </div>
  </div>

  <div class="wg-account-footer"><span>کار شما با حساب تمام شد؟</span><button class="wg-btn wg-btn--ghost" type="button" data-woogit-logout>خروج از حساب</button></div>
</div>
</section>
<?php get_footer(); ?>