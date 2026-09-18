<?php
if (!defined('ABSPATH')) exit;
$options = woogit_theme_options();
$slug = get_post_field('post_name', get_queried_object_id());
$title = get_the_title();
$intro = '';
$section_title = '';
$items = [];
$defaults = [
  'features' => woogit_default_content('features'),
  'steps' => woogit_default_content('steps'),
  'faq' => woogit_default_content('faq'),
];

switch ($slug) {
  case 'features':
    $intro = woogit_safe_text($options['features_intro'] ?? 'ابزارهای اصلی WooGit برای مدیریت روزمره فروشگاه WooCommerce از موبایل.');
    $section_title = 'امکاناتی که برای مدیریت فروشگاه در اختیار شماست';
    $items = woogit_theme_items('features_json', $defaults['features']);
    break;
  case 'how-it-works':
    $intro = woogit_safe_text($options['how_it_works_intro'] ?? 'از نصب اپلیکیشن و اتصال فروشگاه تا مدیریت سفارش‌ها، محصولات، مشتریان، کوپن‌ها و تحلیل فروش، مسیر استفاده ساده و مرحله‌به‌مرحله است.');
    $section_title = 'از نصب اپلیکیشن تا مدیریت فروشگاه';
    $items = woogit_theme_items('steps_json', $defaults['steps']);
    break;
  case 'faq':
    $intro = woogit_safe_text($options['faq_intro'] ?? 'پاسخ پرسش‌های رایج درباره امکانات WooGit، اتصال فروشگاه و استفاده از اپلیکیشن.');
    $section_title = 'پرسش‌های متداول';
    $items = woogit_theme_items('faq_json', $defaults['faq']);
    break;
  case 'documentation':
    $intro = woogit_safe_text($options['documentation_intro'] ?? 'راهنمای شروع و استفاده از WooGit برای مدیریت فروشگاه از موبایل.');
    $section_title = 'شروع سریع';
    break;
  case 'support':
    $intro = woogit_safe_text($options['support_intro'] ?? 'برای اتصال، ورود، اشتراک یا پرداخت، ابتدا وضعیت سرویس و اطلاعات حساب را بررسی کنید.');
    $section_title = 'مسیر پشتیبانی';
    break;
  case 'service-status':
    $intro = woogit_safe_text($options['status_intro'] ?? 'وضعیت سرویس‌ها باید از منبع زنده و معتبر نمایش داده شود؛ در نبود منبع زنده، حدس یا داده ساختگی نمایش داده نمی‌شود.');
    $section_title = 'وضعیت فعلی';
    break;
  case 'privacy':
    $intro = woogit_safe_text($options['privacy_intro'] ?? 'حریم خصوصی WooGit بر پایه حداقل‌سازی داده و تفکیک مسئولیت‌ها طراحی شده است.');
    $section_title = 'اصول حریم خصوصی';
    break;
  case 'terms':
    $intro = woogit_safe_text($options['terms_intro'] ?? 'شرایط استفاده، پرداخت و مسئولیت‌های امنیتی سرویس WooGit.');
    $section_title = 'شرایط استفاده';
    break;
}

get_header();
?>
<section class="wg-page-head wg-container">
  <span class="wg-eyebrow">WooGit</span>
  <h1><?php echo esc_html($title); ?></h1>
  <?php if ($intro): ?><p><?php echo esc_html($intro); ?></p><?php endif; ?>
</section>

<?php if ($slug === 'features' || $slug === 'how-it-works'): ?>
<section class="wg-section wg-container" aria-labelledby="wg-public-section-title">
  <div class="wg-section-head"><span class="wg-eyebrow">WooGit V1</span><h2 id="wg-public-section-title"><?php echo esc_html($section_title); ?></h2></div>
  <div class="wg-grid wg-grid--3">
    <?php $n=0; foreach($items as $item): if(!is_array($item)||isset($item['enabled'])&&!$item['enabled'])continue; $n++; $heading=wp_strip_all_tags((string)($item['title']??$item['name']??'')); $body=wp_kses_post((string)($item['description']??$item['text']??'')); ?>
      <article class="wg-card wg-feature-card"><span class="wg-icon"><?php echo esc_html($item['icon']??sprintf('%02d',$n)); ?></span><h3><?php echo esc_html($heading); ?></h3><p><?php echo $body; ?></p></article>
    <?php endforeach; ?>
  </div>
  <div class="wg-actions"><a class="wg-btn wg-btn--primary" href="<?php echo esc_url(woogit_page_url('download')); ?>">نصب اپ WooGit</a><a class="wg-btn wg-btn--ghost" href="<?php echo esc_url(woogit_page_url('pricing')); ?>">مشاهده قیمت</a></div>
</section>
<?php elseif ($slug === 'faq'): ?>
<section class="wg-section wg-container" aria-labelledby="wg-public-section-title"><div class="wg-section-head"><span class="wg-eyebrow">راهنما</span><h2 id="wg-public-section-title"><?php echo esc_html($section_title); ?></h2></div><div class="wg-faq-list wg-faq">
<?php foreach($items as $item): if(!is_array($item))continue; $question=wp_strip_all_tags((string)($item['question']??''));$answer=wp_kses_post((string)($item['answer']??''));if(!$question||!$answer)continue; ?><details class="wg-card wg-faq__item"><summary><?php echo esc_html($question); ?><span aria-hidden="true">+</span></summary><div class="wg-faq__answer"><?php echo $answer; ?></div></details><?php endforeach; ?></div></section>
<?php elseif ($slug === 'documentation'): ?>
<section class="wg-section wg-container"><div class="wg-grid wg-grid--3"><article class="wg-card"><span class="wg-eyebrow">01</span><h2>سفارش‌ها</h2><p>سفارش‌ها را از موبایل مشاهده، بررسی و مدیریت کنید و تغییرات آن‌ها را سریع‌تر دنبال کنید.</p></article><article class="wg-card"><span class="wg-eyebrow">02</span><h2>محصولات و موجودی</h2><p>محصولات را جستجو و ویرایش کنید، SKU و بارکد را برای دسترسی سریع‌تر به کار ببرید و موجودی را کنترل کنید.</p></article><article class="wg-card"><span class="wg-eyebrow">03</span><h2>مشتریان و کوپن‌ها</h2><p>مشتریان و کوپن‌ها را مدیریت کنید و برای کارهای تکراری از عملیات گروهی استفاده کنید.</p></article></div><div class="wg-card wg-content-card"><h2>تحلیل فروش و کوپن</h2><p>فروش را در بازه‌های زمانی مختلف بررسی کنید و میزان استفاده از کوپن‌ها را برای درک بهتر عملکرد فروشگاه دنبال کنید.</p></div></section>
<?php elseif ($slug === 'support'): ?>
<section class="wg-section wg-container"><div class="wg-grid wg-grid--2"><article class="wg-card"><span class="wg-eyebrow">اتصال</span><h2>مشکل ورود یا اتصال</h2><p>آدرس فروشگاه و وضعیت اتصال را بررسی کنید. Credentialهای حساس را در پیام پشتیبانی قرار ندهید.</p><a class="wg-btn wg-btn--ghost" href="<?php echo esc_url(woogit_page_url('login')); ?>">ورود</a></article><article class="wg-card"><span class="wg-eyebrow">سرویس</span><h2>مشکل اشتراک یا پرداخت</h2><p>اگر درباره اشتراک یا پرداخت سؤال دارید، ابتدا وضعیت حساب و راهنمای مربوط را بررسی کنید.</p><a class="wg-btn wg-btn--ghost" href="<?php echo esc_url(woogit_page_url('service-status')); ?>">وضعیت سرویس</a></article></div></section>
<?php elseif ($slug === 'service-status'): ?>
<section class="wg-section wg-container" aria-labelledby="wg-status-title">
  <div class="wg-section-head"><div><span class="wg-eyebrow">وضعیت سرویس</span><h2 id="wg-status-title">وضعیت فعلی WooGit</h2></div><button type="button" class="wg-btn wg-btn--ghost" data-woogit-status-refresh>بررسی مجدد</button></div>
  <div class="wg-grid wg-grid--3 wg-service-status-grid">
    <article class="wg-card wg-status-card" data-woogit-service-card="website"><div><span class="wg-eyebrow">وب‌سایت</span><h3>سایت WooGit</h3><p>این صفحه از وب‌سایت WooGit با موفقیت بارگذاری شده است.</p></div><span class="wg-status wg-status--success" data-woogit-service-state>فعال</span></article>
    <article class="wg-card wg-status-card" data-woogit-service-card="api"><div><span class="wg-eyebrow">API</span><h3>API و سرویس وب</h3><p>با یک درخواست سبک به یکی از مسیرهای عمومی موجود، دسترسی سرویس بررسی می‌شود.</p></div><span class="wg-status" data-woogit-service-state>در حال بررسی…</span></article>
    <article class="wg-card wg-status-card" data-woogit-service-card="portal"><div><span class="wg-eyebrow">حساب</span><h3>پرتال و حساب</h3><p>این بخش بدون ورود به حساب شما بررسی می‌شود و از نشست یا اطلاعات شخصی استفاده نمی‌کند.</p></div><span class="wg-status" data-woogit-service-state>در حال بررسی…</span></article>
  </div>
  <div class="wg-card wg-service-status-meta"><p><strong>آخرین بررسی:</strong> <time data-woogit-status-time>—</time></p><p><strong>زمان پاسخ سرویس:</strong> <span data-woogit-status-latency>—</span></p><p class="wg-hint" data-woogit-status-note>بررسی خودکار فقط در بارگذاری صفحه انجام می‌شود؛ بررسی دستی نیز محدود شده تا درخواست‌های اضافی به سرویس ارسال نشود.</p></div>
</section>
<?php elseif ($slug === 'privacy' || $slug === 'terms'): ?>
<section class="wg-section wg-container"><article class="wg-card wg-content-card"><?php while(have_posts()):the_post();the_content();endwhile; ?></article></section>
<?php else: ?>
<section class="wg-section wg-container"><article class="wg-card wg-content-card"><?php while(have_posts()):the_post();the_content();endwhile; ?></article></section>
<?php endif; ?>

<?php if(in_array($slug,['features','how-it-works','faq','documentation','support'],true)): ?><section class="wg-cta"><div class="wg-container"><span class="wg-eyebrow">WooGit</span><h2>آماده‌اید WooGit را روی موبایل داشته باشید؟</h2><p>اپلیکیشن WooGit را نصب کنید و مدیریت فروشگاه را از موبایل شروع کنید.</p><a class="wg-btn wg-btn--large" href="<?php echo esc_url(woogit_page_url('download')); ?>">نصب اپ WooGit</a></div></section><?php endif; ?>
<?php get_footer();
