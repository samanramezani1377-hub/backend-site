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
  <div class="wg-actions"><a class="wg-btn wg-btn--primary" href="<?php echo esc_url(woogit_page_url('download')); ?>">نصب اپ WooGit</a><a class="wg-btn wg-btn--ghost" href="<?php echo esc_url(woogit_page_url('documentation')); ?>">راهنمای کامل</a><a class="wg-btn wg-btn--ghost" href="<?php echo esc_url(woogit_page_url('faq')); ?>">سؤالات متداول</a></div>
</section>
<?php elseif ($slug === 'faq'): ?>
<section class="wg-section wg-container" aria-labelledby="wg-public-section-title"><div class="wg-section-head"><span class="wg-eyebrow">راهنما</span><h2 id="wg-public-section-title"><?php echo esc_html($section_title); ?></h2><p>پاسخ‌های این صفحه درباره قابلیت‌های فعلی WooGit، اتصال فروشگاه WooCommerce و شروع استفاده از اپلیکیشن است.</p></div><div class="wg-faq-list wg-faq">
<?php foreach($items as $item): if(!is_array($item))continue; $question=wp_strip_all_tags((string)($item['question']??''));$answer=wp_kses_post((string)($item['answer']??''));if(!$question||!$answer)continue; ?><details class="wg-card wg-faq__item"><summary><?php echo esc_html($question); ?></summary><div class="wg-faq__answer"><?php echo $answer; ?></div></details><?php endforeach; ?></div><div class="wg-actions"><a class="wg-btn wg-btn--primary" href="<?php echo esc_url(woogit_page_url('download')); ?>">نصب اپ WooGit</a><a class="wg-btn wg-btn--ghost" href="<?php echo esc_url(woogit_page_url('documentation')); ?>">راهنمای کامل</a></div></section>
<?php elseif ($slug === 'documentation'): ?>
<section class="wg-section wg-container" aria-labelledby="wg-doc-title">
  <div class="wg-section-head"><span class="wg-eyebrow">راهنمای WooGit</span><h2 id="wg-doc-title">WooGit چه امکاناتی دارد؟</h2><p>این راهنما قابلیت‌های فعلی اپلیکیشن را به‌صورت روشن و دسته‌بندی‌شده توضیح می‌دهد.</p></div>
  <div class="wg-grid wg-grid--2">
    <article class="wg-card"><span class="wg-eyebrow">سفارش‌ها</span><h2>مدیریت سفارش‌ها</h2><p>سفارش‌ها را مشاهده و بررسی کنید، جزئیات و اقلام سفارش را ببینید و وضعیت سفارش‌ها را مدیریت کنید. Live Update نیز برای دنبال کردن تغییرات و سفارش‌های جدید در دسترس است.</p><p>برای سفارش‌های پرتعداد می‌توانید وضعیت چند سفارش را به‌صورت گروهی تغییر دهید و نتیجه عملیات موفق و ناموفق را بررسی کنید.</p></article>
    <article class="wg-card"><span class="wg-eyebrow">محصولات</span><h2>مدیریت محصولات</h2><p>محصولات را جستجو، ایجاد و ویرایش کنید و اطلاعاتی مانند قیمت، وضعیت، نوع، SKU، دسته‌بندی، ویژگی‌ها و تصاویر را مدیریت کنید.</p><p>محصولات متغیر و Variationها نیز در جریان مدیریت محصول پشتیبانی می‌شوند.</p></article>
    <article class="wg-card"><span class="wg-eyebrow">موجودی</span><h2>مدیریت موجودی</h2><p>موجودی محصولات را بررسی کنید و محصولات کم‌موجودی یا ناموجود را سریع‌تر پیدا کنید. جستجوی SKU نیز برای دسترسی سریع به محصول در دسترس است.</p></article>
    <article class="wg-card"><span class="wg-eyebrow">بارکد و SKU</span><h2>دسترسی سریع به محصول</h2><p>بارکد محصول را با دوربین موبایل اسکن کنید یا با SKU محصول موردنظر را پیدا کنید و سریع‌تر وارد مدیریت محصول شوید.</p></article>
    <article class="wg-card"><span class="wg-eyebrow">مشتریان</span><h2>مدیریت مشتریان</h2><p>مشتریان را مشاهده، ایجاد، ویرایش و حذف کنید و جزئیات مشتری و سفارش‌های مرتبط با او را بررسی کنید.</p></article>
    <article class="wg-card"><span class="wg-eyebrow">کوپن‌ها</span><h2>مدیریت کوپن‌ها</h2><p>کوپن‌ها را مشاهده، ایجاد، ویرایش و حذف کنید و نوع تخفیف، مبلغ، تاریخ انقضا، محدودیت‌های استفاده، محصولات و دسته‌بندی‌های مرتبط و میزان استفاده را بررسی کنید.</p></article>
    <article class="wg-card"><span class="wg-eyebrow">تحلیل</span><h2>تحلیل فروش</h2><p>فروش، سفارش‌ها، میانگین ارزش سفارش، وضعیت سفارش‌ها، محصولات پرفروش و اطلاعات مشتریان را در بازه‌های مختلف بررسی کنید. بازه‌های ۷ روزه، ۳۰ روزه، ۹۰ روزه و یک‌ساله در موتور تحلیل تعریف شده‌اند.</p></article>
    <article class="wg-card"><span class="wg-eyebrow">تحلیل کوپن</span><h2>تحلیل استفاده از کوپن‌ها</h2><p>تعداد استفاده از کوپن‌ها، مجموع تخفیف و فروش مرتبط با کوپن‌ها را برای بررسی عملکرد تخفیف‌ها دنبال کنید.</p></article>
    <article class="wg-card"><span class="wg-eyebrow">فاکتور</span><h2>فاکتور و PDF</h2><p>اطلاعات فاکتور سفارش را در جریان مدیریت فروشگاه در دسترس داشته باشید و از خروجی PDF فاکتور استفاده کنید.</p></article>
    <article class="wg-card"><span class="wg-eyebrow">انتقال</span><h2>درون‌ریزی و برون‌ریزی محصولات</h2><p>محصولات را در قالب بسته WooGit منتقل کنید. اطلاعات محصول، دسته‌بندی‌ها، ویژگی‌ها، تصاویر و Variationها در جریان انتقال پشتیبانی می‌شوند.</p></article>
    <article class="wg-card"><span class="wg-eyebrow">هوش مصنوعی</span><h2>مدیریت هوشمند با AI</h2><p>قابلیت AI تکمیل شده است و برای تعامل هوشمند با فروشگاه و استفاده از ابزارهای مدیریتی WooGit در اپلیکیشن ارائه می‌شود.</p></article>
    <article class="wg-card"><span class="wg-eyebrow">همگام‌سازی</span><h2>مدیریت از موبایل</h2><p>WooGit برای مدیریت کارهای روزمره فروشگاه از موبایل طراحی شده و بخش‌هایی از داده‌ها و سفارش‌ها را با سازوکارهای به‌روزرسانی و همگام‌سازی دنبال می‌کند.</p></article>
  </div>
  <div class="wg-actions"><a class="wg-btn wg-btn--primary" href="<?php echo esc_url(woogit_page_url('download')); ?>">نصب اپ WooGit</a><a class="wg-btn wg-btn--ghost" href="<?php echo esc_url(woogit_page_url('features')); ?>">مشاهده همه امکانات</a><a class="wg-btn wg-btn--ghost" href="<?php echo esc_url(woogit_page_url('faq')); ?>">سؤالات متداول</a></div>
</section>
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
