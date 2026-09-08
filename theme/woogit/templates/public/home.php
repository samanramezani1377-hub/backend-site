<?php
if(!defined('ABSPATH'))exit;

$plans=woogit_plans();
$options=woogit_theme_options();
$features=woogit_theme_items('features_json',[
 ['title'=>'امنیت و اعتماد','text'=>'مرز روشن بین Theme، Backend و فروشگاه مشتری.'],
 ['title'=>'تجربه ساده','text'=>'رابط تمیز با hierarchy مشخص و بدون شلوغی.'],
 ['title'=>'اطلاعات شفاف','text'=>'وضعیت حساب، اشتراک و سرویس در جای درست.'],
 ['title'=>'اتصال مطمئن','text'=>'جریان‌های وب بر اساس قراردادهای معتبر Backend.'],
 ['title'=>'طراحی آینده‌نگر','text'=>'Liquid Glass ملایم، نه یک داشبورد شلوغ.'],
]);
$steps=woogit_theme_items('steps_json',[
 ['title'=>'شروع وب','text'=>'حساب خود را از مسیر رسمی وب وارد یا ایجاد کنید و جریان Web Session را طی کنید.'],
 ['title'=>'انتخاب سرویس','text'=>'پلن‌ها و اطلاعات تجاری از منبع معتبر نمایش داده می‌شوند؛ قیمت و entitlement در Theme محاسبه نمی‌شوند.'],
 ['title'=>'مدیریت از پرتال','text'=>'اشتراک، Billing، پرداخت‌ها، سایت متصل و Account/Security را از Customer Portal مدیریت کنید.'],
]);
$default_tablet_image='https://media.easy-peasy.ai/20b11164-c351-41a4-b167-3e302745223d/905d6e89-6fc3-48da-bf19-d26197f6ed81_medium.webp';
$default_mobile_image='https://miro.medium.com/v2/resize%3Afit%3A2048/1%2AalY3BpAQQDdSCIZpvslNxg.png';
$tablet_image=woogit_theme_image(absint($options['hero_image_id']??0),'large')?:$default_tablet_image;
$mobile_image=woogit_theme_image(absint($options['hero_mobile_image_id']??0),'large')?:$default_mobile_image;
$using_default_tablet_image=$tablet_image===$default_tablet_image;
$using_default_mobile_image=$mobile_image===$default_mobile_image;
?>
<section class="wg-hero" id="home" aria-labelledby="wg-hero-title">
 <div class="wg-container wg-hero__grid">
  <div class="wg-hero__copy">
   <span class="wg-eyebrow">مدیریت هوشمند فروشگاه ووکامرس</span>
   <h1 id="wg-hero-title">فروشگاه شما، با <span>WooGit</span> ساده‌تر و هوشمندتر</h1>
   <p>WooGit ابزارهایی برای مدیریت و هوشمندسازی تجربه فروشگاه ووکامرس شما فراهم می‌کند؛ با یک تجربه وب تمیز، سریع و قابل اعتماد.</p>
   <div class="wg-actions">
    <a class="wg-btn wg-btn--primary wg-btn--large" href="<?php echo esc_url(woogit_page_url('download')); ?>">دریافت WooGit <span aria-hidden="true">←</span></a>
    <a class="wg-btn wg-btn--ghost wg-btn--large" href="#wg-home-preview">مشاهده پیش‌نمایش</a>
   </div>
   <div class="wg-hero__note">بدون ذخیره‌سازی Credentialهای فروشگاه مشتری در Theme</div>
  </div>

  <div class="wg-home-devices" id="wg-home-preview" aria-label="پیش‌نمایش تصویری WooGit">
   <div class="wg-home-motion-dot wg-dot-one" aria-hidden="true"></div><div class="wg-home-motion-dot wg-dot-two" aria-hidden="true"></div>
   <div class="wg-home-tablet">
    <div class="wg-tablet-screen">
     <img src="<?php echo esc_url($tablet_image); ?>" alt="پیش‌نمایش رابط کاربری WooGit در تبلت" style="display:block;width:100%;height:100%;object-fit:cover;border-radius:21px;" loading="eager" decoding="async">
    </div>
   </div>
   <div class="wg-home-mobile">
    <div class="wg-device-notch"></div>
    <div class="wg-mobile-screen" style="padding:0;">
     <img src="<?php echo esc_url($mobile_image); ?>" alt="پیش‌نمایش رابط کاربری WooGit در موبایل" style="display:block;width:100%;height:100%;object-fit:cover;object-position:center top;border-radius:27px;" loading="eager" decoding="async">
    </div>
   </div>
   <?php if($using_default_tablet_image): ?><a href="https://easy-peasy.ai/" target="_blank" rel="noopener noreferrer" style="position:absolute;inset-inline-start:1rem;bottom:.35rem;font-size:.62rem;color:var(--muted);opacity:.72;z-index:4;">تصویر تبلت: Easy-Peasy.AI</a><?php endif; ?>
   <?php if($using_default_mobile_image): ?><a href="https://medium.com/" target="_blank" rel="noopener noreferrer" style="position:absolute;inset-inline-end:1rem;bottom:.35rem;font-size:.62rem;color:var(--muted);opacity:.72;z-index:4;">تصویر موبایل: Medium</a><?php endif; ?>
  </div>
 </div>
</section>

<section class="wg-section" id="features" aria-labelledby="wg-features-title">
 <div class="wg-container">
  <div class="wg-section-head"><span class="wg-eyebrow">چرا WooGit؟</span><h2 id="wg-features-title">یک تجربه ساده برای یک محصول جدی</h2><p>هویت مدرن، hierarchy واضح و تمرکز روی کاری که واقعاً برای شما مهم است.</p></div>
  <div class="wg-grid wg-grid--3 wg-reference-features">
   <?php foreach(array_slice($features,0,5) as $i=>$feature): ?>
    <article class="wg-card"><div class="wg-icon" aria-hidden="true"><?php echo esc_html(['◈','▣','⌁','↻','✦'][$i]??'✦'); ?></div><h3><?php echo esc_html($feature['title']??''); ?></h3><p><?php echo esc_html($feature['text']??''); ?></p></article>
   <?php endforeach; ?>
  </div>
 </div>
</section>

<section class="wg-section wg-steps" id="how" aria-labelledby="wg-how-title">
 <div class="wg-container">
  <div class="wg-section-head"><span class="wg-eyebrow">نحوه کار</span><h2 id="wg-how-title">مسیر شروع، کوتاه و روشن</h2><p>جزئیات امنیتی و business truth در Backend باقی می‌ماند.</p></div>
  <div class="wg-grid wg-grid--3">
   <?php foreach(array_slice($steps,0,3) as $i=>$step): ?><article class="wg-card"><div class="wg-step-number"><?php echo esc_html((string)($i+1)); ?></div><h3><?php echo esc_html($step['title']??''); ?></h3><p><?php echo esc_html($step['text']??''); ?></p></article><?php endforeach; ?>
  </div>
 </div>
</section>

<section class="wg-section wg-app-section" aria-labelledby="wg-app-title">
 <div class="wg-container">
  <div class="wg-home-app">
   <div class="wg-home-mini-devices" aria-label="پیش‌نمایش محصول WooGit">
    <div class="wg-home-mini-tablet"><div class="wg-mini-content"><strong>WooGit</strong><div></div><div></div><div></div></div></div>
    <div class="wg-home-mini-phone"><div class="wg-mini-phone-content"><strong>WooGit</strong><span>فروشگاه متصل</span><span>اشتراک حرفه‌ای</span><span>وضعیت حساب</span></div></div>
   </div>
   <div class="wg-home-app__copy"><span class="wg-eyebrow">محصول WooGit</span><h2 id="wg-app-title">یک هویت مشترک برای وب و محصول</h2><p>Theme فقط لایه وب است؛ عملیات فروشگاه مشتری متعلق به App و حقیقت، مجوز و امنیت متعلق به Backend است. این صفحه عمداً یک Store Dashboard عملیاتی نیست.</p><a class="wg-btn wg-btn--ghost" href="<?php echo esc_url(woogit_page_url('documentation')); ?>">آشنایی با مستندات</a></div>
  </div>
 </div>
</section>

<section class="wg-section" id="pricing" aria-labelledby="wg-pricing-title">
 <div class="wg-container">
  <div class="wg-section-head"><span class="wg-eyebrow">قیمت‌گذاری</span><h2 id="wg-pricing-title">پلن مناسب خودتان را انتخاب کنید</h2><p>قیمت، مدت، قابلیت‌ها و محدودیت‌ها در نسخه واقعی از داده قراردادی دریافت می‌شوند.</p></div>
  <div class="wg-grid wg-grid--3 wg-pricing-grid">
   <?php if($plans): foreach(array_slice($plans,0,3) as $i=>$plan): $featured=$i===1; ?>
    <article class="wg-card wg-plan <?php echo $featured?'wg-plan--featured':''; ?>"><span class="wg-plan__badge"><?php echo $featured?'پیشنهاد اصلی':'WooGit'; ?></span><h3><?php echo esc_html($plan['name']??$plan['title']??'پلن WooGit'); ?></h3><div class="wg-plan__price"><?php echo esc_html($plan['price']??''); ?> <small><?php echo esc_html($plan['currency']??''); ?></small></div><p><?php echo esc_html($plan['description']??'برای استفاده از WooGit.'); ?></p><a class="wg-btn <?php echo $featured?'':'wg-btn--ghost'; ?>" href="<?php echo esc_url(woogit_page_url('login')); ?>">انتخاب پلن</a></article>
   <?php endforeach; else: ?>
    <article class="wg-card wg-plan"><span class="wg-plan__badge">آزمایشی</span><h3>شروع رایگان</h3><div class="wg-plan__price">رایگان</div><p>برای آشنایی و تست محدود سرویس.</p><ul><li>دسترسی آزمایشی</li><li>مدت محدود</li><li>Entitlement قراردادی</li></ul><a class="wg-btn wg-btn--ghost" href="<?php echo esc_url(woogit_page_url('register')); ?>">شروع</a></article>
    <article class="wg-card wg-plan wg-plan--featured"><span class="wg-plan__badge">پیشنهاد اصلی</span><h3>پلن حرفه‌ای</h3><div class="wg-plan__price">از داده سرویس</div><p>برای استفاده کامل‌تر از WooGit.</p><ul><li>قابلیت‌های پلن</li><li>مدت اشتراک</li><li>محدودیت‌های قراردادی</li></ul><a class="wg-btn" href="<?php echo esc_url(woogit_page_url('login')); ?>">انتخاب پلن</a></article>
    <article class="wg-card wg-plan"><span class="wg-plan__badge">سازمانی</span><h3>تماس با ما</h3><div class="wg-plan__price">اختصاصی</div><p>شرایط اختصاصی از قرارداد تعیین می‌شود.</p><ul><li>شرایط اختصاصی</li><li>اطلاعات از Backend</li><li>مسیر تماس رسمی</li></ul><a class="wg-btn wg-btn--ghost" href="<?php echo esc_url(woogit_page_url('support')); ?>">تماس با ما</a></article>
   <?php endif; ?>
  </div>
 </div>
</section>

<section class="wg-section wg-faq" id="faq" aria-labelledby="wg-faq-title">
 <div class="wg-container"><div class="wg-section-head"><span class="wg-eyebrow">سؤالات متداول</span><h2 id="wg-faq-title">پاسخ‌های کوتاه، قبل از شروع</h2></div>
  <div class="wg-faq__list">
   <details class="wg-faq__item"><summary>آیا Theme به فروشگاه ووکامرس مشتری متصل می‌شود؟</summary><p>خیر. Theme به Customer WooCommerce متصل نمی‌شود؛ Theme فقط presentation و orchestration قراردادی وب را انجام می‌دهد.</p></details>
   <details class="wg-faq__item"><summary>اطلاعات پرداخت از کجا می‌آید؟</summary><p>Payment Method، Payment History و Order/Payment state خرید WooGit از WooCommerce خود woogit.ir می‌آید و وضعیت Subscription/Entitlement از Backend.</p></details>
   <details class="wg-faq__item"><summary>آیا قیمت‌ها داخل Theme ثابت هستند؟</summary><p>خیر. Pricing باید data-driven باشد و قیمت، currency، duration، features و limits از داده قراردادی دریافت شوند.</p></details>
   <details class="wg-faq__item"><summary>آیا حالت تاریک وجود دارد؟</summary><p>بله. Light و Dark از Design System هستند و از semantic tokens مشترک استفاده می‌کنند.</p></details>
  </div>
 </div>
</section>

<section class="wg-cta" aria-labelledby="wg-cta-title"><div class="wg-container"><h2 id="wg-cta-title">با WooGit ساده‌تر شروع کنید</h2><p>یک تجربه وب سریع، خوانا و قابل اعتماد برای شروع مسیر شما.</p><a class="wg-btn wg-btn--primary wg-btn--large" href="<?php echo esc_url(woogit_page_url('download')); ?>">دریافت WooGit</a></div></section>
