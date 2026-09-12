<?php
if (!defined('ABSPATH')) exit;

$order_id = absint($_GET['order_id'] ?? $_GET['order'] ?? 0);
$order_status = '';
$order = null;

if ($order_id > 0 && function_exists('wc_get_order')) {
    $candidate = wc_get_order($order_id);
    if ($candidate && method_exists($candidate, 'get_created_via') && (string)$candidate->get_created_via() === 'woogit') {
        $order = $candidate;
        $order_status = (string)$candidate->get_status();
    }
}

$paid = in_array($order_status, ['processing', 'completed'], true);
$pending = in_array($order_status, ['pending', 'on-hold'], true);
$failed = in_array($order_status, ['failed', 'cancelled'], true);
$refunded = $order_status === 'refunded';

if ($paid) {
    $state = 'success';
    $title = 'پرداخت با موفقیت انجام شد';
    $message = 'پرداخت شما ثبت شد. وضعیت اشتراک در WooGit در حال به‌روزرسانی است.';
} elseif ($pending) {
    $state = 'pending';
    $title = 'پرداخت در حال بررسی است';
    $message = 'نتیجه نهایی پرداخت هنوز ثبت نشده است. لطفاً دوباره پرداخت نکنید.';
} elseif ($failed || $refunded) {
    $state = 'failed';
    $title = $refunded ? 'پرداخت مسترد شده است' : 'پرداخت ناموفق یا لغوشده';
    $message = $refunded ? 'مبلغ پرداختی مسترد شده است.' : 'پرداخت نهایی نشده است. در صورت نیاز می‌توانید از داخل WooGit دوباره اقدام کنید.';
} else {
    $state = 'pending';
    $title = 'نتیجه پرداخت دریافت شد';
    $message = 'برای اطمینان، وضعیت پرداخت و اشتراک در WooGit دوباره بررسی می‌شود.';
}

get_header();
?>
<section class="wg-section"><div class="wg-container"><div class="wg-card wg-payment-result" style="max-width:680px;margin:48px auto;text-align:center;">
<span class="wg-eyebrow">WooGit App</span>
<div class="wg-state-card wg-state-card--<?php echo esc_attr($state === 'failed' ? 'danger' : $state); ?>">
<h1><?php echo esc_html($title); ?></h1>
<p><?php echo esc_html($message); ?></p>
<?php if ($order_id > 0): ?><div class="wg-status wg-status--<?php echo esc_attr($state); ?>" role="status">سفارش #<?php echo (int)$order_id; ?></div><?php endif; ?>
</div>
<div class="wg-actions" style="justify-content:center;margin-top:28px;">
<a class="wg-btn" href="<?php echo esc_url(add_query_arg(['app' => '1', 'order_id' => $order_id, 'return_to' => 'woogit', 'close_app' => '1'], woogit_page_url('payment-result'))); ?>">بازگشت به WooGit</a>
</div>
</div></div></section>
<?php get_footer(); ?>
