<?php
if (!defined('ABSPATH')) exit;
$options = woogit_theme_options();
$slug = get_post_field('post_name', get_queried_object_id());
$title = get_the_title();
$intro = '';
$section_title = '';
$items = [];
$defaults = [
  'features' => [
    ['title'=>'اتصال امن و قراردادی','description'=>'احراز هویت، مالکیت سایت و مجوزها توسط Backend مرجع WooGit تعیین می‌شوند.','icon'=>'01','enabled'=>true],
    ['title'=>'پرتال شفاف مشتری','description'=>'اشتراک، پرداخت، سایت متصل و امنیت حساب در یک تجربه متمرکز قابل پیگیری هستند.','icon'=>'02','enabled'=>true],
    ['title'=>'طراحی سریع و responsive','description'=>'رابط سبک، خوانا و سازگار با موبایل و دسکتاپ با Liquid Glass کنترل‌شده.','icon'=>'03','enabled'=>true],
    ['title'=>'تفکیک داده و مسئولیت','description'=>'Theme به Customer WooCommerce متصل نمی‌شود و فقط داده قراردادی موردنیاز presentation را مصرف می‌کند.','icon'=>'04','enabled'=>true],
    ['title'=>'وضعیت‌های قابل فهم','description'=>'Loading، Success، Empty، Pending و Error به‌صورت مشخص و قابل بازیابی نمایش داده می‌شوند.','icon'=>'05','enabled'=>true],
    ['title'=>'تجربه روشن و تاریک','description'=>'Light و Dark Mode بر پایه semantic tokenها و بدون تغییر در ساختار یا business logic.','icon'=>'06','enabled'=>true],
  ],
  'steps' => [
    ['title'=>'فروشگاه را آماده کنید','description'=>'اطلاعات لازم برای Web Bootstrap را در مسیر رسمی WooGit وارد کنید.','enabled'=>true],
    ['title'=>'هویت و مالکیت تأیید می‌شود','description'=>'Backend اعتبار حساب، مالکیت سایت و دسترسی موردنیاز را بررسی می‌کند.','enabled'=>true],
    ['title'=>'حساب را از پرتال مدیریت کنید','description'=>'وضعیت اشتراک، صورتحساب، پرداخت‌ها، سایت متصل و امنیت حساب را دنبال کنید.','enabled'=>true],
  ],
  'faq' => [
    ['question'=>'آیا Theme به WooCommerce فروشگاه من متصل می‌شود؟','answer'=>'خیر. Customer WooCommerce از WooCommerce خود woogit.ir جداست و Theme نباید به فروشگاه مشتری متصل شود.'],
    ['question'=>'اطلاعات پرداخت از کجا می‌آید؟','answer'=>'داده تجاری خرید WooGit مانند سفارش، روش پرداخت و وضعیت پرداخت از WooCommerce خود woogit.ir و از مسیر adapter مجاز مصرف می‌شود.'],
    ['question'=>'آیا نتیجه برگشت از درگاه به‌تنهایی پرداخت را تأیید می‌کند؟','answer'=>'خیر. Payment Return proof پرداخت نیست؛ وضعیت سفارش/پرداخت و entitlement باید از منابع authoritative تأیید شوند.'],
    ['question'=>'آیا نشست وب با نشست اپلیکیشن یکی است؟','answer'=>'خیر. Web Session با X-WooGit-Web-Session و App Session با X-WooGit-Session جدا هستند و Theme نباید App Session را reuse یا جعل کند.'],
    ['question'=>'آیا اطلاعات ورود فروشگاه ذخیره می‌شود؟','answer'=>'Credentialهای WordPress/WooCommerce در Theme نباید در DB، session پایدار، cache، cookie نامناسب، log، telemetry، HTML یا JavaScript نگهداری شوند.'],
    ['question'=>'اگر درخواست با timeout تمام شود چه می‌شود؟','answer'=>'عملیات موفقیت‌آمیز یا ناموفق حدس زده نمی‌شود؛ وضعیت Unknown مدیریت می‌شود و retry همان logical operation باید Idempotency-Key قبلی را حفظ کند.'],
  ],
];

switch ($slug) {
  case 'features':
    $intro = woogit_safe_text($options['features_intro'] ?? 'قابلیت‌های اصلی WooGit برای یک تجربه وب امن، روشن و قابل اعتماد.');
    $section_title = 'یک سیستم، چند مسئولیت روشن';
    $items = woogit_theme_items('features_json', $defaults['features']);
    break;
  case 'how-it-works':
    $intro = woogit_safe_text($options['how_it_works_intro'] ?? 'مسیر استفاده از WooGit از اتصال اولیه تا مدیریت حساب، مرحله‌به‌مرحله و قابل فهم طراحی شده است.');
    $section_title = 'از اتصال تا مدیریت';
    $items = woogit_theme_items('steps_json', $defaults['steps']);
    break;
  case 'faq':
    $intro = woogit_safe_text($options['faq_intro'] ?? 'پاسخ‌های کوتاه به مهم‌ترین پرسش‌های مربوط به اتصال، امنیت، پرداخت و پرتال WooGit.');
    $section_title = 'پرسش‌های متداول';
    $items = woogit_theme_items('faq_json', $defaults['faq']);
    break;
  case 'documentation':
    $intro = woogit_safe_text($options['documentation_intro'] ?? 'راهنمای استفاده و قراردادهای کلیدی WooGit در یک نقطه.');
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
  <div class="wg-actions"><a class="wg-btn" href="<?php echo esc_url(woogit_page_url('register')); ?>">دریافت WooGit</a><a class="wg-btn wg-btn--ghost" href="<?php echo esc_url(woogit_page_url('pricing')); ?>">مشاهده قیمت</a></div>
</section>
<?php elseif ($slug === 'faq'): ?>
<section class="wg-section wg-container" aria-labelledby="wg-public-section-title"><div class="wg-section-head"><span class="wg-eyebrow">راهنما</span><h2 id="wg-public-section-title"><?php echo esc_html($section_title); ?></h2></div><div class="wg-faq-list wg-faq">
<?php foreach($items as $item): if(!is_array($item))continue; $question=wp_strip_all_tags((string)($item['question']??''));$answer=wp_kses_post((string)($item['answer']??''));if(!$question||!$answer)continue; ?><details class="wg-card wg-faq__item"><summary><?php echo esc_html($question); ?><span aria-hidden="true">+</span></summary><div class="wg-faq__answer"><?php echo $answer; ?></div></details><?php endforeach; ?></div></section>
<?php elseif ($slug === 'documentation'): ?>
<section class="wg-section wg-container"><div class="wg-grid wg-grid--3"><article class="wg-card"><span class="wg-eyebrow">01</span><h2>اتصال وب</h2><p>ورود و Web Bootstrap طبق قرارداد Backend انجام می‌شود و Web Session از App Session مستقل است.</p></article><article class="wg-card"><span class="wg-eyebrow">02</span><h2>اشتراک و پرداخت</h2><p>Pricing از Backend می‌آید و وضعیت سفارش و پرداخت از WooCommerce خود woogit.ir تأیید می‌شود.</p></article><article class="wg-card"><span class="wg-eyebrow">03</span><h2>پرتال مشتری</h2><p>Overview، Subscription، Billing، Payments، Connected Site و Account/Security در پرتال قرار دارند.</p></article></div><div class="wg-card wg-content-card"><h2>مرزهای Theme</h2><p>Theme برای عملیات Products، Orders، Inventory، Sync، Conflicts، Media یا Store Dashboard ساخته نشده است. این مرزها متعلق به App و Backend هستند.</p></div></section>
<?php elseif ($slug === 'support'): ?>
<section class="wg-section wg-container"><div class="wg-grid wg-grid--2"><article class="wg-card"><span class="wg-eyebrow">اتصال</span><h2>مشکل ورود یا اتصال</h2><p>آدرس سایت، Web Password و وضعیت نشست را بررسی کنید. Credentialهای حساس را در پیام پشتیبانی قرار ندهید.</p><a class="wg-btn wg-btn--ghost" href="<?php echo esc_url(woogit_page_url('login')); ?>">ورود</a></article><article class="wg-card"><span class="wg-eyebrow">سرویس</span><h2>مشکل اشتراک یا پرداخت</h2><p>ابتدا وضعیت سرویس و سپس Billing و Payments را بررسی کنید. وضعیت نهایی از منابع authoritative خوانده می‌شود.</p><a class="wg-btn wg-btn--ghost" href="<?php echo esc_url(woogit_page_url('service-status')); ?>">وضعیت سرویس</a></article></div></section>
<?php elseif ($slug === 'service-status'): ?>
<section class="wg-section wg-container"><div class="wg-card wg-status-card"><div><span class="wg-eyebrow">Live source</span><h2>منبع وضعیت زنده متصل نیست</h2><p>برای جلوگیری از نمایش وضعیت ساختگی، سرویس تا زمان دریافت داده معتبر با وضعیت نامشخص نمایش داده می‌شود.</p></div><span class="wg-badge">نامشخص</span></div></section>
<?php elseif ($slug === 'privacy' || $slug === 'terms'): ?>
<section class="wg-section wg-container"><article class="wg-card wg-content-card"><?php while(have_posts()):the_post();the_content();endwhile; ?></article></section>
<?php else: ?>
<section class="wg-section wg-container"><article class="wg-card wg-content-card"><?php while(have_posts()):the_post();the_content();endwhile; ?></article></section>
<?php endif; ?>

<?php if(in_array($slug,['features','how-it-works','faq','documentation','support'],true)): ?><section class="wg-cta"><div class="wg-container"><span class="wg-eyebrow">WooGit</span><h2>آماده شروع هستید؟</h2><p>از مسیر رسمی WooGit وارد شوید و حساب خود را مدیریت کنید.</p><a class="wg-btn wg-btn--large" href="<?php echo esc_url(woogit_page_url('register')); ?>">دریافت WooGit</a></div></section><?php endif; ?>
<?php get_footer();
