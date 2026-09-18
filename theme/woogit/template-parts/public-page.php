<?php
if (!defined('ABSPATH')) exit;
$options = woogit_theme_options();
$slug = get_post_field('post_name', get_queried_object_id());
$title = get_the_title();
$intro = '';
$section_title = '';
$items = [];
$defaults = [
  'features' => [['title'=>'مدیریت سفارش‌ها','description'=>'سفارش‌های فروشگاه را از موبایل مشاهده کنید، جزئیات را ببینید و وضعیت سفارش‌ها را مدیریت و پیگیری کنید.','icon'=>'▤'],['title'=>'به‌روزرسانی زنده سفارش‌ها','description'=>'با Live Update از سفارش‌های جدید و تغییرات سفارش‌ها باخبر بمانید و بدون تازه‌سازی مداوم وضعیت فروشگاه را دنبال کنید.','icon'=>'◉'],['title'=>'مدیریت محصولات','description'=>'محصولات را جستجو، بررسی و ویرایش کنید و قیمت، وضعیت، نوع، SKU، دسته‌بندی، ویژگی‌ها و تصاویر را در دسترس داشته باشید.','icon'=>'◈'],['title'=>'مدیریت موجودی','description'=>'موجودی محصولات را بررسی کنید، موارد کم‌موجودی را سریع‌تر پیدا کنید و وضعیت موجودی فروشگاه را از موبایل کنترل کنید.','icon'=>'▥'],['title'=>'مدیریت مشتریان','description'=>'اطلاعات مشتریان را مشاهده و مدیریت کنید و خلاصه اطلاعات و فعالیت‌های مرتبط با آن‌ها را در دسترس داشته باشید.','icon'=>'♙'],['title'=>'عملیات گروهی مشتریان','description'=>'برای مدیریت سریع‌تر، عملیات موردنیاز را روی چند مشتری به‌صورت گروهی انجام دهید.','icon'=>'♧'],['title'=>'مدیریت کوپن‌ها','description'=>'کوپن‌های تخفیف را مشاهده و مدیریت کنید و نوع، مبلغ، محدودیت‌ها و وضعیت استفاده از آن‌ها را بررسی کنید.','icon'=>'◇'],['title'=>'عملیات گروهی کوپن‌ها','description'=>'مدیریت چند کوپن را با عملیات گروهی سریع‌تر انجام دهید و زمان کمتری برای کارهای تکراری صرف کنید.','icon'=>'◆'],['title'=>'تحلیل استفاده از کوپن‌ها','description'=>'ببینید کوپن‌ها چگونه استفاده شده‌اند و عملکرد آن‌ها را در کنار اطلاعات فروش بررسی کنید.','icon'=>'◒'],['title'=>'تحلیل فروش','description'=>'فروش فروشگاه را در بازه‌های زمانی مختلف بررسی کنید و با گزارش‌های تحلیلی دید روشن‌تری از عملکرد فروش داشته باشید.','icon'=>'◌'],['title'=>'اسکن بارکد محصول','description'=>'با دوربین موبایل بارکد محصول را اسکن کنید و سریع‌تر به محصول موردنظر برسید.','icon'=>'▣'],['title'=>'جستجوی سریع با SKU','description'=>'محصول را با SKU پیدا کنید و برای مدیریت سریع‌تر لازم نیست همیشه نام محصول را جستجو کنید.','icon'=>'⌕'],['title'=>'تغییر گروهی وضعیت سفارش‌ها','description'=>'وضعیت چند سفارش را به‌صورت گروهی تغییر دهید تا مدیریت سفارش‌های پرتعداد سریع‌تر انجام شود.','icon'=>'⇄'],['title'=>'مدیریت ویژگی‌های محصول','description'=>'ویژگی‌ها و گزینه‌های محصول را مشاهده و مدیریت کنید تا اطلاعات محصول دقیق‌تر و منظم‌تر باشد.','icon'=>'✦'],['title'=>'جزئیات کامل محصول','description'=>'جزئیات مهم محصول از عنوان و قیمت تا موجودی، SKU، وضعیت، نوع، دسته‌بندی، ویژگی‌ها و تصاویر را یکجا بررسی کنید.','icon'=>'◫'],['title'=>'مدیریت تصاویر محصول','description'=>'تصاویر محصولات را در کنار سایر اطلاعات محصول مدیریت کنید و محتوای محصول را از موبایل کنترل کنید.','icon'=>'▧'],['title'=>'درون‌ریزی و برون‌ریزی محصولات','description'=>'در صورت فعال بودن قابلیت مربوط به نسخه یا پلن شما، داده‌های محصولات را برای انتقال و مدیریت راحت‌تر درون‌ریزی یا برون‌ریزی کنید.','icon'=>'⇅'],['title'=>'صدور و مدیریت فاکتور','description'=>'فاکتورهای مرتبط با سفارش را در جریان مدیریت فروشگاه در دسترس داشته باشید و خروجی فاکتور را استفاده کنید.','icon'=>'▤']
  ],
  'steps' => [['title'=>'اپلیکیشن WooGit را نصب کنید','description'=>'اپلیکیشن WooGit را روی موبایل خود نصب کنید و مدیریت فروشگاهتان را همیشه همراه خود داشته باشید.'],['title'=>'فروشگاه ووکامرس را متصل کنید','description'=>'فروشگاه WooCommerce خود را به WooGit متصل کنید تا اطلاعات فروشگاه در اختیار اپلیکیشن قرار بگیرد.'],['title'=>'محصولات و سفارش‌ها را مدیریت کنید','description'=>'سفارش‌ها را بررسی و مدیریت کنید و محصولات، قیمت، موجودی، SKU، دسته‌بندی، ویژگی‌ها و تصاویر را از موبایل کنترل کنید.'],['title'=>'مشتریان و کوپن‌ها را مدیریت کنید','description'=>'اطلاعات مشتریان را مشاهده و مدیریت کنید و کوپن‌ها، مبلغ، نوع، محدودیت‌ها و میزان استفاده از آن‌ها را کنترل کنید.'],['title'=>'فروشگاه را تحلیل کنید','description'=>'با تحلیل فروش و گزارش‌های مرتبط با کوپن‌ها، دید بهتری نسبت به عملکرد فروشگاهتان داشته باشید.'],['title'=>'از هرجا فروشگاه را مدیریت کنید','description'=>'کارهای روزمره فروشگاه را بدون نیاز به حضور دائمی پشت کامپیوتر، از طریق اپلیکیشن WooGit انجام دهید.']
  ],
  'faq' => [['question'=>'WooGit چیست؟','answer'=>'WooGit اپلیکیشن مدیریت فروشگاه WooCommerce است که ابزارهای موردنیاز برای مدیریت سفارش‌ها، محصولات، موجودی، مشتریان، کوپن‌ها و تحلیل فروش را از طریق موبایل در اختیار شما قرار می‌دهد.'],['question'=>'با WooGit چه کارهایی می‌توانم انجام دهم؟','answer'=>'می‌توانید سفارش‌ها و محصولات را مدیریت کنید، موجودی را بررسی کنید، مشتریان و کوپن‌ها را مدیریت کنید، عملیات گروهی انجام دهید و تحلیل فروش و استفاده از کوپن‌ها را ببینید.'],['question'=>'آیا می‌توانم سفارش‌ها را از موبایل مدیریت کنم؟','answer'=>'بله. می‌توانید سفارش‌ها را ببینید، جزئیات آن‌ها را بررسی کنید و وضعیت سفارش‌ها را مدیریت و پیگیری کنید.'],['question'=>'آیا سفارش‌های جدید را می‌توانم سریع‌تر ببینم؟','answer'=>'بله. قابلیت Live Update برای دنبال کردن تغییرات و سفارش‌های جدید در اختیار اپلیکیشن قرار گرفته است.'],['question'=>'آیا می‌توانم محصولات را از موبایل ویرایش کنم؟','answer'=>'بله. اطلاعاتی مانند قیمت، موجودی، SKU، وضعیت، نوع، دسته‌بندی، ویژگی‌ها و تصاویر محصول در جریان مدیریت محصول قابل استفاده هستند.'],['question'=>'آیا برای پیدا کردن محصول فقط باید نام آن را جستجو کنم؟','answer'=>'خیر. می‌توانید از جستجوی SKU و اسکن بارکد برای دسترسی سریع‌تر به محصول استفاده کنید.'],['question'=>'آیا می‌توانم چند سفارش را همزمان مدیریت کنم؟','answer'=>'بله. عملیات گروهی سفارش‌ها از جمله تغییر گروهی وضعیت سفارش‌ها برای مدیریت سریع‌تر سفارش‌های پرتعداد در نظر گرفته شده است.'],['question'=>'آیا مدیریت مشتریان هم وجود دارد؟','answer'=>'بله. می‌توانید اطلاعات مشتریان را مشاهده و مدیریت کنید و عملیات گروهی را برای مدیریت سریع‌تر انجام دهید.'],['question'=>'آیا می‌توانم کوپن‌ها را مدیریت کنم؟','answer'=>'بله. امکان مشاهده و مدیریت کوپن‌ها و بررسی نوع، مبلغ، محدودیت‌ها و وضعیت استفاده از آن‌ها وجود دارد.'],['question'=>'آیا عملکرد کوپن‌ها قابل بررسی است؟','answer'=>'بله. تحلیل استفاده از کوپن‌ها کمک می‌کند میزان استفاده و عملکرد کوپن‌ها را بهتر بررسی کنید.'],['question'=>'آیا WooGit آمار فروش را نمایش می‌دهد؟','answer'=>'بله. بخش تحلیل فروش برای بررسی عملکرد فروش در بازه‌های زمانی مختلف در نظر گرفته شده است.'],['question'=>'آیا برای استفاده باید همیشه پشت کامپیوتر باشم؟','answer'=>'خیر. هدف WooGit این است که کارهای مهم و روزمره فروشگاه را از طریق اپلیکیشن موبایل در دسترس شما قرار دهد.'],['question'=>'آیا WooGit به فروشگاه WooCommerce متصل می‌شود؟','answer'=>'بله. WooGit برای مدیریت فروشگاه‌های WooCommerce طراحی شده و پس از اتصال، قابلیت‌های مدیریت فروشگاه را در اپلیکیشن در اختیار شما قرار می‌دهد.'],['question'=>'اگر اتصال فروشگاه مشکل داشته باشد چه کار کنم؟','answer'=>'ابتدا آدرس فروشگاه و وضعیت اتصال را بررسی کنید. اگر مشکل ادامه داشت، از بخش پشتیبانی راهنمایی بگیرید و اطلاعات حساس ورود را در پیام پشتیبانی ارسال نکنید.'],['question'=>'آیا همه قابلیت‌ها برای همه کاربران فعال هستند؟','answer'=>'قابلیت‌ها ممکن است با توجه به نسخه اپلیکیشن، پلن فعال یا وضعیت سرویس در دسترس باشند. جزئیات پلن و امکانات فعال خود را از مسیر رسمی WooGit بررسی کنید.']
  ],
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
  <div class="wg-actions"><a class="wg-btn" href="<?php echo esc_url(woogit_page_url('download')); ?>">دریافت WooGit</a><a class="wg-btn wg-btn--ghost" href="<?php echo esc_url(woogit_page_url('pricing')); ?>">مشاهده قیمت</a></div>
</section>
<?php elseif ($slug === 'faq'): ?>
<section class="wg-section wg-container" aria-labelledby="wg-public-section-title"><div class="wg-section-head"><span class="wg-eyebrow">راهنما</span><h2 id="wg-public-section-title"><?php echo esc_html($section_title); ?></h2></div><div class="wg-faq-list wg-faq">
<?php foreach($items as $item): if(!is_array($item))continue; $question=wp_strip_all_tags((string)($item['question']??''));$answer=wp_kses_post((string)($item['answer']??''));if(!$question||!$answer)continue; ?><details class="wg-card wg-faq__item"><summary><?php echo esc_html($question); ?><span aria-hidden="true">+</span></summary><div class="wg-faq__answer"><?php echo $answer; ?></div></details><?php endforeach; ?></div></section>
<?php elseif ($slug === 'documentation'): ?>
<section class="wg-section wg-container"><div class="wg-grid wg-grid--3"><article class="wg-card"><span class="wg-eyebrow">01</span><h2>سفارش‌ها</h2><p>سفارش‌ها را از موبایل مشاهده، بررسی و مدیریت کنید و تغییرات آن‌ها را سریع‌تر دنبال کنید.</p></article><article class="wg-card"><span class="wg-eyebrow">02</span><h2>محصولات و موجودی</h2><p>محصولات را جستجو و ویرایش کنید، SKU و بارکد را برای دسترسی سریع‌تر به کار ببرید و موجودی را کنترل کنید.</p></article><article class="wg-card"><span class="wg-eyebrow">03</span><h2>مشتریان و کوپن‌ها</h2><p>مشتریان و کوپن‌ها را مدیریت کنید و برای کارهای تکراری از عملیات گروهی استفاده کنید.</p></article></div><div class="wg-card wg-content-card"><h2>تحلیل فروش و کوپن</h2><p>فروش را در بازه‌های زمانی مختلف بررسی کنید و میزان استفاده از کوپن‌ها را برای درک بهتر عملکرد فروشگاه دنبال کنید.</p></div></section>
<?php elseif ($slug === 'support'): ?>
<section class="wg-section wg-container"><div class="wg-grid wg-grid--2"><article class="wg-card"><span class="wg-eyebrow">اتصال</span><h2>مشکل ورود یا اتصال</h2><p>آدرس فروشگاه و وضعیت اتصال را بررسی کنید. Credentialهای حساس را در پیام پشتیبانی قرار ندهید.</p><a class="wg-btn wg-btn--ghost" href="<?php echo esc_url(woogit_page_url('login')); ?>">ورود</a></article><article class="wg-card"><span class="wg-eyebrow">سرویس</span><h2>مشکل اشتراک یا پرداخت</h2><p>موضوع را مشخص کنید؛ سفارش، محصول، موجودی، مشتری، کوپن یا تحلیل فروش و راهنمای مربوط را بررسی کنید.</p><a class="wg-btn wg-btn--ghost" href="<?php echo esc_url(woogit_page_url('service-status')); ?>">وضعیت سرویس</a></article></div></section>
<?php elseif ($slug === 'service-status'): ?>
<section class="wg-section wg-container"><div class="wg-card wg-status-card"><div><span class="wg-eyebrow">Live source</span><h2>منبع وضعیت زنده متصل نیست</h2><p>برای جلوگیری از نمایش وضعیت ساختگی، سرویس تا زمان دریافت داده معتبر با وضعیت نامشخص نمایش داده می‌شود.</p></div><span class="wg-badge">نامشخص</span></div></section>
<?php elseif ($slug === 'privacy' || $slug === 'terms'): ?>
<section class="wg-section wg-container"><article class="wg-card wg-content-card"><?php while(have_posts()):the_post();the_content();endwhile; ?></article></section>
<?php else: ?>
<section class="wg-section wg-container"><article class="wg-card wg-content-card"><?php while(have_posts()):the_post();the_content();endwhile; ?></article></section>
<?php endif; ?>

<?php if(in_array($slug,['features','how-it-works','faq','documentation','support'],true)): ?><section class="wg-cta"><div class="wg-container"><span class="wg-eyebrow">WooGit</span><h2>آماده شروع هستید؟</h2><p>از مسیر رسمی WooGit وارد شوید و حساب خود را مدیریت کنید.</p><a class="wg-btn wg-btn--large" href="<?php echo esc_url(woogit_page_url('download')); ?>">دریافت WooGit</a></div></section><?php endif; ?>
<?php get_footer();
