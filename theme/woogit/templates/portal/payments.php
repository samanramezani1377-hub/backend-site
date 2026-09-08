<?php
if(!defined('ABSPATH'))exit;
$d=woogit_portal_data();
get_header();
if(!$d['authenticated']){woogit_render_status('error','نشست شما منقضی شده است.');get_footer();return;}
$p=$d['payments'];$orders=(array)($p['orders']??[]);$current=max(1,(int)($p['page']??1));$totalPages=max(1,(int)($p['total_pages']??1));
?>
<section class="wg-section wg-portal">
  <div class="wg-container">
    <div class="wg-section__head"><div><span class="wg-eyebrow">Payments</span><h1>تاریخچه پرداخت‌ها</h1><p>سوابق خرید WooGit از WooCommerce رسمی سایت نمایش داده می‌شود.</p></div></div>
    <?php if(!$orders): ?>
      <div class="wg-card wg-empty"><h2>هنوز پرداختی ثبت نشده است.</h2><p>پس از اولین Order معتبر، سوابق اینجا نمایش داده می‌شود.</p><a class="wg-btn" href="<?php echo esc_url(woogit_page_url('pricing')); ?>">مشاهده پلن‌ها</a></div>
    <?php else: ?>
      <div class="wg-card wg-table-card"><div class="wg-table-scroll"><table class="wg-table"><thead><tr><th>Order</th><th>تاریخ</th><th>وضعیت</th><th>روش پرداخت</th><th>مبلغ</th></tr></thead><tbody>
      <?php foreach($orders as $o): $status=woogit_portal_status($o['status']??'unknown'); ?>
        <tr><td>#<?php echo (int)($o['order_id']??0); ?></td><td><?php echo !empty($o['created_at'])&&strtotime($o['created_at'])?esc_html(wp_date('Y/m/d H:i',strtotime($o['created_at']))):'—'; ?></td><td><span class="wg-status wg-status--<?php echo esc_attr($status[1]); ?>"><?php echo woogit_safe_text($status[0]); ?></span></td><td><?php echo woogit_safe_text($o['payment_method_title']??$o['payment_method']??'—'); ?></td><td><?php echo woogit_safe_text($o['total']??'—'); ?> <?php echo woogit_safe_text($o['currency']??''); ?></td></tr>
      <?php endforeach; ?></tbody></table></div></div>
      <?php if($totalPages>1): ?>
        <nav class="wg-pagination" aria-label="صفحات پرداخت">
          <span>صفحه <?php echo (int)$current; ?> از <?php echo (int)$totalPages; ?></span>
          <div class="wg-actions">
            <?php if($current>1): ?><a class="wg-btn wg-btn--ghost" href="<?php echo esc_url(add_query_arg('payment_page',$current-1)); ?>">قبلی</a><?php endif; ?>
            <?php if($current<$totalPages): ?><a class="wg-btn wg-btn--ghost" href="<?php echo esc_url(add_query_arg('payment_page',$current+1)); ?>">بعدی</a><?php endif; ?>
          </div>
        </nav>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>
<?php get_footer(); ?>
