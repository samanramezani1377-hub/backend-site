<?php
if (!defined('ABSPATH')) exit;

// Keep the gateway return page lightweight. Do not bootstrap the full portal
// (payment history, payment method, etc.) before rendering the result.
$user = function_exists('woogit_current_user') ? woogit_current_user() : null;
$authenticated = !is_wp_error($user) && !empty($user) && empty($user['error']);
$order = null;
$order_status = '';
$entitlement = 'none';

if ($authenticated) {
    $order_id = absint($_GET['order_id'] ?? $_GET['order'] ?? 0);
    $account_id = (int)($user['account_id'] ?? 0);
    $site_id = (int)($user['site_id'] ?? 0);

    if ($order_id > 0 && $account_id > 0 && $site_id > 0 && function_exists('woogit_commerce_payment_order')) {
        $candidate = woogit_commerce_payment_order($account_id, $site_id, $order_id);
        if (is_array($candidate)) {
            $order = $candidate;
            $order_status = (string)($candidate['status'] ?? '');
        }
    }

    // Billing status is useful for the final state, but failure to obtain it
    // must never make the payment return page fatal.
    $status = function_exists('woogit_api_get') ? woogit_api_get('billing/status') : null;
    if (!is_wp_error($status) && is_array($status)) {
        $billing = (array)($status['billing'] ?? $status);
        $entitlement = (string)($billing['status'] ?? 'none');
    }
}

$paid = in_array($order_status, ['processing', 'completed'], true);
$pending = in_array($order_status, ['pending', 'on-hold'], true);
$failed = in_array($order_status, ['failed', 'cancelled'], true);
$refunded = $order_status === 'refunded';
$entitlement_active = $entitlement === 'active';

if (!$authenticated) {
    $state = 'pending';
    $title = 'نیاز به ورود';
    $message = 'بازگشت از درگاه به‌تنهایی تأیید پرداخت نیست. برای بررسی وضعیت واقعی، ابتدا وارد حساب شوید.';
} elseif ($paid && $entitlement_active) {
    $state = 'success';
    $title = 'پرداخت و فعال‌سازی تأیید شد';
    $message = 'پرداخت در WooCommerce ثبت شده و دسترسی حساب نیز فعال است.';
} elseif ($paid) {
    $state = 'pending';
    $title = 'پرداخت تأیید شد؛ فعال‌سازی در حال تکمیل است';
    $message = 'پرداخت ثبت شده اما فعال‌شدن دسترسی هنوز از Backend تأیید نشده است. پرداخت را دوباره انجام ندهید.';
} elseif ($pending) {
    $state = 'pending';
    $title = 'پرداخت در حال بررسی است';
    $message = 'سفارش هنوز وضعیت نهایی پرداخت ندارد. از ایجاد سفارش یا پرداخت تکراری خودداری کنید.';
} elseif ($failed) {
    $state = 'failed';
    $title = 'پرداخت ناموفق یا لغوشده';
    $message = 'سفارش وضعیت ناموفق یا لغوشده دارد. در صورت نیاز از مسیر پرداخت دوباره اقدام کنید.';
} elseif ($refunded) {
    $state = 'failed';
    $title = 'پرداخت مسترد شده است';
    $message = 'سفارش ثبت شده اما مبلغ آن مسترد شده است.';
} else {
    $state = 'unknown';
    $title = 'وضعیت پرداخت نامشخص است';
    $message = 'بازگشت درگاه proof پرداخت نیست و سفارش معتبر قابل تطبیق نیست. برای جلوگیری از پرداخت تکراری، ابتدا تاریخچه پرداخت را بررسی کنید.';
}

get_header();
?>
<section class="wg-section"><div class="wg-container"><div class="wg-card wg-payment-result">
<span class="wg-eyebrow">Payment Return</span>
<?php if (!$authenticated): ?>
<h1><?php echo esc_html($title); ?></h1><p><?php echo esc_html($message); ?></p><a class="wg-btn" href="<?php echo esc_url(woogit_page_url('login')); ?>">ورود به حساب</a>
<?php else: ?>
<div class="wg-state-card wg-state-card--<?php echo esc_attr($state === 'failed' ? 'danger' : $state); ?>"><h1><?php echo esc_html($title); ?></h1><p><?php echo esc_html($message); ?></p><div class="wg-status wg-status--<?php echo esc_attr($state); ?>" role="status"><?php if ($order): ?>سفارش #<?php echo (int)$order['order_id']; ?> — <?php echo esc_html($order_status); ?><?php else: ?>تطبیق سفارش انجام نشد<?php endif; ?></div></div>
<?php if ($order): ?><dl class="wg-meta-list"><div><dt>مبلغ</dt><dd><?php echo woogit_safe_text($order['total'] ?? '—', '—'); ?> <?php echo woogit_safe_text($order['currency'] ?? ''); ?></dd></div><div><dt>روش پرداخت</dt><dd><?php echo woogit_safe_text($order['payment_method_title'] ?? ($order['payment_method'] ?? ''), '—'); ?></dd></div><div><dt>وضعیت دسترسی</dt><dd><?php echo esc_html($entitlement_active ? 'فعال' : 'در انتظار همگام‌سازی'); ?></dd></div></dl><?php endif; ?>
<div class="wg-actions"><a class="wg-btn" href="<?php echo esc_url(woogit_page_url('portal')); ?>">بازگشت به پرتال</a><a class="wg-btn wg-btn--ghost" href="<?php echo esc_url(woogit_page_url('payments')); ?>">مشاهده پرداخت‌ها</a></div>
<?php endif; ?></div></div></section><?php get_footer(); ?>