<?php
if(!defined('ABSPATH'))exit;
$d=woogit_portal_data();get_header();
?>
<section class="wg-section"><div class="wg-container"><div class="wg-card wg-payment-result">
<span class="wg-eyebrow">Payment Return</span>
<?php if(!$d['authenticated']): ?><h1>نیاز به ورود</h1><p>بازگشت از درگاه به‌تنهایی تأیید پرداخت نیست. برای بررسی وضعیت واقعی، ابتدا وارد حساب شوید.</p><a class="wg-btn" href="<?php echo esc_url(woogit_page_url('login')); ?>">ورود به حساب</a>
<?php else:
$order_id=absint($_GET['order_id']??$_GET['order']??0);$order=woogit_commerce_payment_order((int)($d['user']['account_id']??0),(int)($d['user']['site_id']??0),$order_id);$order_status=(string)($order['status']??'');
$paid=in_array($order_status,['processing','completed'],true);$pending=in_array($order_status,['pending','on-hold'],true);$failed=in_array($order_status,['failed','cancelled'],true);$refunded=$order_status==='refunded';
$billing=(array)($d['billing']??[]);$entitlement=(string)($billing['status']??'none');$entitlementActive=$entitlement==='active';
if($paid&&$entitlementActive){$state='success';$title='پرداخت و فعال‌سازی تأیید شد';$message='پرداخت در WooCommerce رسمی WooGit ثبت شده و دسترسی حساب نیز فعال است.';}
elseif($paid){$state='pending';$title='پرداخت تأیید شد؛ فعال‌سازی در حال تکمیل است';$message='پرداخت ثبت شده اما فعال‌شدن دسترسی هنوز از Backend تأیید نشده است. پرداخت را دوباره انجام ندهید.';}
elseif($pending){$state='pending';$title='پرداخت در حال بررسی است';$message='سفارش هنوز وضعیت نهایی پرداخت ندارد. از ایجاد سفارش یا پرداخت تکراری خودداری کنید.';}
elseif($failed){$state='failed';$title='پرداخت ناموفق یا لغوشده';$message='سفارش وضعیت ناموفق یا لغوشده دارد. در صورت نیاز از مسیر پرداخت دوباره اقدام کنید.';}
elseif($refunded){$state='failed';$title='پرداخت مسترد شده است';$message='سفارش ثبت شده اما مبلغ آن مسترد شده است.';}
else{$state='unknown';$title='وضعیت پرداخت نامشخص است';$message='بازگشت درگاه proof پرداخت نیست و سفارش معتبر قابل تطبیق نیست. برای جلوگیری از پرداخت تکراری، ابتدا تاریخچه پرداخت را بررسی کنید.';}
?>
<div class="wg-state-card wg-state-card--<?php echo esc_attr($state==='failed'?'danger':$state); ?>"><h1><?php echo esc_html($title); ?></h1><p><?php echo esc_html($message); ?></p><div class="wg-status wg-status--<?php echo esc_attr($state); ?>" role="status"><?php if($order): ?>سفارش #<?php echo (int)$order['order_id']; ?> — <?php echo esc_html($order_status); ?><?php else: ?>تطبیق سفارش انجام نشد<?php endif; ?></div></div>
<?php if($order): ?><dl class="wg-meta-list"><div><dt>مبلغ</dt><dd><?php echo woogit_safe_text($order['total'],'—'); ?> <?php echo woogit_safe_text($order['currency']); ?></dd></div><div><dt>روش پرداخت</dt><dd><?php echo woogit_safe_text($order['payment_method_title']?:$order['payment_method'],'—'); ?></dd></div><div><dt>وضعیت دسترسی</dt><dd><?php echo esc_html($entitlementActive?'فعال':'در انتظار همگام‌سازی'); ?></dd></div></dl><?php endif; ?>
<div class="wg-actions"><a class="wg-btn" href="<?php echo esc_url(woogit_page_url('portal')); ?>">بازگشت به پرتال</a><a class="wg-btn wg-btn--ghost" href="<?php echo esc_url(woogit_page_url('payments')); ?>">مشاهده پرداخت‌ها</a></div>
<?php endif; ?></div></div></section><?php get_footer(); ?>