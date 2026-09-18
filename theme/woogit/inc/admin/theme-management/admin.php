<?php
if (!defined('ABSPATH')) exit;

function woogit_theme_management_menu() {
    add_theme_page('WooGit Theme', 'WooGit Theme', 'manage_options', 'woogit-theme', 'woogit_render_theme_management');
}
add_action('admin_menu', 'woogit_theme_management_menu');

function woogit_theme_admin_enqueue($hook) {
    if ($hook === 'appearance_page_woogit-theme') wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'woogit_theme_admin_enqueue');

function woogit_theme_admin_css() {
    if (($_GET['page'] ?? '') !== 'woogit-theme') return;
    echo <<<'HTML'
<style>
.wg-tm{max-width:1180px;margin:24px 20px 40px 0;direction:rtl}.wg-tm *{box-sizing:border-box}.wg-tm__head{display:flex;justify-content:space-between;gap:20px;align-items:flex-end;margin-bottom:20px}.wg-tm__head h1{margin:0 0 6px;font-size:28px}.wg-tm__head p{margin:0;color:#646970}.wg-tm__save{position:sticky;top:32px;z-index:5}.wg-tm__tabs{display:flex;gap:6px;flex-wrap:wrap;border-bottom:1px solid #dcdcde;margin-bottom:18px}.wg-tm__tab{border:0;background:transparent;padding:11px 15px;cursor:pointer;color:#50575e;font-weight:600;border-bottom:3px solid transparent}.wg-tm__tab.is-active{color:#2271b1;border-bottom-color:#2271b1}.wg-tm__panel{display:none}.wg-tm__panel.is-active{display:block}.wg-tm__grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.wg-tm__card{background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:20px;margin-bottom:16px;box-shadow:0 1px 2px rgba(0,0,0,.03)}.wg-tm__card h2{margin:0 0 6px;font-size:17px}.wg-tm__field{margin:0 0 16px}.wg-tm__field label{display:block;font-weight:600;margin-bottom:6px}.wg-tm__field input[type=text],.wg-tm__field input[type=url],.wg-tm__field input[type=email],.wg-tm__field input[type=number],.wg-tm__field textarea,.wg-tm__field select{width:100%;max-width:none}.wg-tm__field textarea{min-height:90px}.wg-tm__help{font-size:12px;color:#646970;margin-top:5px}.wg-tm__media{display:flex;align-items:center;gap:12px;padding:12px;border:1px dashed #c3c4c7;border-radius:10px}.wg-tm__media img,.wg-tm__media-placeholder{width:76px;height:76px;object-fit:cover;border-radius:8px;background:#f0f0f1}.wg-tm__file{padding:12px;border:1px dashed #c3c4c7;border-radius:10px}.wg-tm__file-name{font-weight:600;margin-bottom:10px;word-break:break-all}.wg-tm__media-placeholder{display:grid;place-items:center;color:#646970;font-size:11px;text-align:center}.wg-tm__media-actions{display:flex;gap:7px;flex-wrap:wrap}.wg-tm__item{border:1px solid #dcdcde;border-radius:10px;padding:14px;background:#fafafa;margin-bottom:10px}.wg-tm__item-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}.wg-tm__item-actions{display:flex;gap:5px}.wg-tm__mini{display:grid;grid-template-columns:1fr 1fr;gap:10px}.wg-tm__add{margin-top:10px}.wg-tm__notice{padding:12px 14px;background:#f0f6fc;border-right:4px solid #2271b1;border-radius:7px;margin-bottom:16px}.wg-tm__check{display:flex!important;align-items:center;gap:8px;font-weight:500!important}.wg-tm__check input{margin:0}.wg-tm__readonly{background:#f6f7f7;border:1px solid #dcdcde;padding:12px;border-radius:8px}.wg-tm__danger{color:#b32d2e}.wg-tm__json{display:none!important}@media(max-width:800px){.wg-tm__grid,.wg-tm__mini{grid-template-columns:1fr}.wg-tm__head{align-items:flex-start;flex-direction:column}.wg-tm__save{position:static}}
</style>
HTML;
}
add_action('admin_head', 'woogit_theme_admin_css');

function woogit_tm_text($key, $label, $default = '', $type = 'text', $help = '') {
    $value = woogit_theme_option($key, $default);
    echo '<div class="wg-tm__field"><label for="wg-tm-'.esc_attr($key).'">'.esc_html($label).'</label>';
    if ($type === 'textarea') {
        printf('<textarea id="wg-tm-%1$s" name="woogit_theme_options[%1$s]">%2$s</textarea>', esc_attr($key), esc_textarea($value));
    } else {
        printf('<input id="wg-tm-%1$s" type="%2$s" name="woogit_theme_options[%1$s]" value="%3$s">', esc_attr($key), esc_attr($type), esc_attr($value));
    }
    if ($help) echo '<div class="wg-tm__help">'.esc_html($help).'</div>';
    echo '</div>';
}

function woogit_tm_checkbox($key, $label, $default = false) {
    $value = woogit_theme_option($key, $default ? '1' : '0');
    printf('<div class="wg-tm__field"><input type="hidden" name="woogit_theme_options[%1$s]" value="0"><label class="wg-tm__check"><input type="checkbox" name="woogit_theme_options[%1$s]" value="1" %2$s> %3$s</label></div>', esc_attr($key), checked($value, '1', false), esc_html($label));
}

function woogit_tm_media($key, $label, $help = '') {
    $id = absint(woogit_theme_option($key, 0));
    $url = $id ? woogit_theme_image($id, 'medium') : '';
    echo '<div class="wg-tm__field"><label>'.esc_html($label).'</label><div class="wg-tm__media" data-media-control>';
    if ($url) printf('<img src="%s" alt="">', esc_url($url)); else echo '<div class="wg-tm__media-placeholder">تصویری انتخاب نشده</div>';
    echo '<div><input type="hidden" class="wg-tm__media-id" name="woogit_theme_options['.esc_attr($key).']" value="'.esc_attr($id).'">';
    echo '<div class="wg-tm__media-actions"><button type="button" class="button wg-tm-pick">انتخاب تصویر</button><button type="button" class="button wg-tm-remove" '.($id ? '' : 'disabled').'>حذف</button></div>';
    if ($help) echo '<div class="wg-tm__help">'.esc_html($help).'</div>';
    echo '</div></div></div>';
}

function woogit_tm_file($key, $label, $help = '') {
    $id = absint(woogit_theme_option($key, 0));
    $url = $id ? wp_get_attachment_url($id) : '';
    $name = $id ? get_the_title($id) : '';
    echo '<div class="wg-tm__field"><label>'.esc_html($label).'</label><div class="wg-tm__file" data-file-control><input type="hidden" class="wg-tm__file-id" name="woogit_theme_options['.esc_attr($key).']" value="'.esc_attr($id).'">';
    echo '<div class="wg-tm__file-name">'.esc_html($name ?: 'فایلی انتخاب نشده').'</div>';
    echo '<div class="wg-tm__media-actions"><button type="button" class="button wg-tm-file-pick">انتخاب یا آپلود APK</button><button type="button" class="button wg-tm-file-remove" '.($id ? '' : 'disabled').'>حذف</button></div>';
    if ($url) echo '<div class="wg-tm__help"><a href="'.esc_url($url).'" target="_blank" rel="noopener noreferrer">مشاهده فایل فعلی</a></div>';
    if ($help) echo '<div class="wg-tm__help">'.esc_html($help).'</div>';
    echo '</div></div>';
}

function woogit_tm_repeater($key, $label, $defaults, $columns, $help = '') {
    $items = woogit_theme_items($key, $defaults);
    echo '<div class="wg-tm__field wg-tm-repeater" data-columns="'.esc_attr(wp_json_encode($columns)).'">';
    echo '<label>'.esc_html($label).'</label>';
    if ($help) echo '<div class="wg-tm__help">'.esc_html($help).'</div>';
    printf('<input class="wg-tm__json" type="text" name="woogit_theme_options[%1$s]" value="%2$s">', esc_attr($key), esc_attr(wp_json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));
    echo '<div class="wg-tm__repeat-list">';
    foreach ($items as $i => $item) woogit_tm_repeater_item($i, is_array($item) ? $item : [], $columns);
    echo '</div><button type="button" class="button wg-tm__add">افزودن مورد</button></div>';
}

function woogit_tm_repeater_item($index, $item, $columns) {
    echo '<div class="wg-tm__item"><div class="wg-tm__item-head"><strong class="wg-tm__item-title">مورد '.((int)$index + 1).'</strong><div class="wg-tm__item-actions"><button type="button" class="button wg-tm-up">↑</button><button type="button" class="button wg-tm-down">↓</button><button type="button" class="button wg-tm__danger wg-tm-delete">حذف</button></div></div><div class="wg-tm__mini">';
    foreach ($columns as $field => $cfg) {
        $type = $cfg['type'] ?? 'text';
        $value = $item[$field] ?? ($cfg['default'] ?? '');
        echo '<div><label>'.esc_html($cfg['label'] ?? $field).'</label>';
        if ($type === 'media') {
            $id = absint($value);
            $url = $id ? woogit_theme_image($id, 'thumbnail') : '';
            echo '<div class="wg-tm__media" data-repeat-media><input type="hidden" class="wg-tm-repeater-value" data-field="'.esc_attr($field).'" value="'.esc_attr($id).'">';
            if ($url) printf('<img src="%s" alt="">', esc_url($url)); else echo '<div class="wg-tm__media-placeholder">تصویر</div>';
            echo '<button type="button" class="button wg-tm-repeat-pick">انتخاب تصویر</button><button type="button" class="button wg-tm-repeat-remove">حذف</button></div>';
        } elseif ($type === 'checkbox') {
            printf('<label class="wg-tm__check"><input type="checkbox" class="wg-tm-repeater-value" data-field="%1$s" %2$s> فعال</label>', esc_attr($field), checked(!empty($value), true, false));
        } elseif ($type === 'textarea') {
            printf('<textarea class="wg-tm-repeater-value" data-field="%1$s">%2$s</textarea>', esc_attr($field), esc_textarea($value));
        } else {
            printf('<input type="%1$s" class="wg-tm-repeater-value" data-field="%2$s" value="%3$s">', esc_attr($type), esc_attr($field), esc_attr($value));
        }
        echo '</div>';
    }
    echo '</div></div>';
}

function woogit_render_theme_management() {
    if (!current_user_can('manage_options')) return;
    $tabs = ['general'=>'عمومی','home'=>'صفحه اصلی','features'=>'قابلیت‌ها','steps'=>'روش کار','pricing'=>'قیمت‌گذاری','faq'=>'FAQ','footer'=>'Footer','social'=>'شبکه‌های اجتماعی'];
    $features = woogit_theme_items('features_json', [['title'=>'مدیریت سفارش‌ها','description'=>'سفارش‌های فروشگاه را از موبایل مشاهده کنید، جزئیات را ببینید و وضعیت سفارش‌ها را مدیریت و پیگیری کنید.','icon'=>'▤','enabled'=>true],['title'=>'به‌روزرسانی زنده سفارش‌ها','description'=>'با Live Update از سفارش‌های جدید و تغییرات سفارش‌ها باخبر بمانید.','icon'=>'◉','enabled'=>true],['title'=>'مدیریت محصولات','description'=>'محصولات را جستجو، بررسی و ویرایش کنید و قیمت، موجودی، SKU، دسته‌بندی، ویژگی‌ها و تصاویر را مدیریت کنید.','icon'=>'◈','enabled'=>true],['title'=>'مدیریت موجودی','description'=>'موجودی محصولات و موارد کم‌موجودی را از موبایل کنترل کنید.','icon'=>'▥','enabled'=>true],['title'=>'مدیریت مشتریان','description'=>'اطلاعات مشتریان را مشاهده و مدیریت کنید.','icon'=>'♙','enabled'=>true],['title'=>'عملیات گروهی مشتریان','description'=>'عملیات موردنیاز را روی چند مشتری به‌صورت گروهی انجام دهید.','icon'=>'♧','enabled'=>true],['title'=>'مدیریت کوپن‌ها','description'=>'کوپن‌ها، نوع، مبلغ، محدودیت‌ها و وضعیت استفاده را مدیریت کنید.','icon'=>'◇','enabled'=>true],['title'=>'عملیات گروهی کوپن‌ها','description'=>'چند کوپن را با عملیات گروهی سریع‌تر مدیریت کنید.','icon'=>'◆','enabled'=>true],['title'=>'تحلیل استفاده از کوپن‌ها','description'=>'میزان استفاده و عملکرد کوپن‌ها را بررسی کنید.','icon'=>'◒','enabled'=>true],['title'=>'تحلیل فروش','description'=>'فروش را در بازه‌های زمانی مختلف بررسی کنید.','icon'=>'◌','enabled'=>true],['title'=>'اسکن بارکد محصول','description'=>'بارکد محصول را با دوربین موبایل اسکن کنید.','icon'=>'▣','enabled'=>true],['title'=>'جستجوی سریع با SKU','description'=>'محصول را با SKU سریع‌تر پیدا کنید.','icon'=>'⌕','enabled'=>true],['title'=>'تغییر گروهی وضعیت سفارش‌ها','description'=>'وضعیت چند سفارش را همزمان تغییر دهید.','icon'=>'⇄','enabled'=>true],['title'=>'مدیریت ویژگی‌های محصول','description'=>'ویژگی‌ها و گزینه‌های محصول را مدیریت کنید.','icon'=>'✦','enabled'=>true],['title'=>'جزئیات کامل محصول','description'=>'جزئیات قیمت، موجودی، SKU، وضعیت، نوع، دسته‌بندی، ویژگی‌ها و تصاویر را یکجا ببینید.','icon'=>'◫','enabled'=>true],['title'=>'مدیریت تصاویر محصول','description'=>'تصاویر محصولات را از موبایل مدیریت کنید.','icon'=>'▧','enabled'=>true],['title'=>'درون‌ریزی و برون‌ریزی محصولات','description'=>'در صورت فعال بودن قابلیت مربوط به نسخه یا پلن، داده‌های محصولات را جابه‌جا کنید.','icon'=>'⇅','enabled'=>true],['title'=>'صدور و مدیریت فاکتور','description'=>'در صورت فعال بودن قابلیت مربوط، فاکتور سفارش را در اختیار داشته باشید.','icon'=>'▤','enabled'=>true]]);
    $steps = woogit_theme_items('steps_json', [['title'=>'اپلیکیشن WooGit را نصب کنید','description'=>'اپلیکیشن WooGit را روی موبایل نصب کنید و مدیریت فروشگاه را همراه خود داشته باشید.','enabled'=>true],['title'=>'فروشگاه ووکامرس را متصل کنید','description'=>'فروشگاه WooCommerce را به WooGit متصل کنید.','enabled'=>true],['title'=>'محصولات و سفارش‌ها را مدیریت کنید','description'=>'سفارش‌ها و محصولات را از موبایل مدیریت کنید.','enabled'=>true],['title'=>'مشتریان و کوپن‌ها را مدیریت کنید','description'=>'مشتریان و کوپن‌ها را مدیریت و عملیات گروهی انجام دهید.','enabled'=>true],['title'=>'فروشگاه را تحلیل کنید','description'=>'فروش و استفاده از کوپن‌ها را تحلیل کنید.','enabled'=>true],['title'=>'از هرجا فروشگاه را مدیریت کنید','description'=>'کارهای روزمره فروشگاه را از طریق اپلیکیشن انجام دهید.','enabled'=>true]]);
    $faq = woogit_theme_items('faq_json', [['question'=>'WooGit چیست؟','answer'=>'WooGit اپلیکیشن مدیریت فروشگاه WooCommerce است که ابزارهای مدیریت سفارش‌ها، محصولات، موجودی، مشتریان، کوپن‌ها و تحلیل فروش را از موبایل در اختیار شما قرار می‌دهد.','enabled'=>true],['question'=>'با WooGit چه کارهایی می‌توانم انجام دهم؟','answer'=>'سفارش‌ها، محصولات، موجودی، مشتریان و کوپن‌ها را مدیریت کنید، عملیات گروهی انجام دهید و تحلیل فروش و کوپن را ببینید.','enabled'=>true],['question'=>'آیا می‌توانم سفارش‌ها را از موبایل مدیریت کنم؟','answer'=>'بله؛ مشاهده، بررسی، مدیریت و پیگیری سفارش‌ها از موبایل امکان‌پذیر است.','enabled'=>true],['question'=>'آیا سفارش‌های جدید را سریع‌تر می‌بینم؟','answer'=>'بله؛ Live Update برای دنبال کردن تغییرات و سفارش‌های جدید در اپلیکیشن وجود دارد.','enabled'=>true],['question'=>'آیا می‌توانم محصولات را ویرایش کنم؟','answer'=>'بله؛ قیمت، موجودی، SKU، وضعیت، نوع، دسته‌بندی، ویژگی‌ها و تصاویر محصول قابل مدیریت هستند.','enabled'=>true],['question'=>'آیا SKU و بارکد هم پشتیبانی می‌شوند؟','answer'=>'بله؛ جستجوی SKU و اسکن بارکد برای دسترسی سریع‌تر به محصول در دسترس است.','enabled'=>true],['question'=>'آیا عملیات گروهی سفارش‌ها وجود دارد؟','answer'=>'بله؛ از جمله تغییر گروهی وضعیت سفارش‌ها.','enabled'=>true],['question'=>'آیا مشتریان را می‌توانم مدیریت کنم؟','answer'=>'بله؛ اطلاعات مشتریان و عملیات گروهی مرتبط قابل استفاده است.','enabled'=>true],['question'=>'آیا کوپن‌ها را می‌توانم مدیریت کنم؟','answer'=>'بله؛ مشاهده و مدیریت کوپن‌ها و بررسی نوع، مبلغ، محدودیت‌ها و استفاده از آن‌ها امکان‌پذیر است.','enabled'=>true],['question'=>'آیا تحلیل فروش وجود دارد؟','answer'=>'بله؛ تحلیل فروش برای بازه‌های زمانی مختلف در نظر گرفته شده است.','enabled'=>true],['question'=>'آیا تحلیل استفاده از کوپن وجود دارد؟','answer'=>'بله؛ میزان استفاده و عملکرد کوپن‌ها قابل بررسی است.','enabled'=>true],['question'=>'آیا برای استفاده باید پشت کامپیوتر باشم؟','answer'=>'خیر؛ هدف WooGit در دسترس قرار دادن کارهای مهم فروشگاه از طریق موبایل است.','enabled'=>true],['question'=>'آیا WooGit به WooCommerce متصل می‌شود؟','answer'=>'بله؛ WooGit برای مدیریت فروشگاه‌های WooCommerce طراحی شده است.','enabled'=>true],['question'=>'اگر اتصال مشکل داشت چه کنم؟','answer'=>'آدرس فروشگاه و وضعیت اتصال را بررسی کنید و در صورت ادامه مشکل از پشتیبانی کمک بگیرید.','enabled'=>true],['question'=>'آیا همه قابلیت‌ها برای همه کاربران فعال هستند؟','answer'=>'قابلیت‌ها ممکن است با توجه به نسخه، پلن یا وضعیت سرویس در دسترس باشند.','enabled'=>true]]);
    $social = woogit_theme_items('social_links_json', []);
    ?>
    <div class="wg-tm">
      <div class="wg-tm__head"><div><h1>WooGit Theme</h1><p>مدیریت محتوای سایت WooGit؛ متن‌ها، قابلیت‌ها، مراحل استفاده و سؤالات متداول را از همین بخش ویرایش کنید.</p></div><div class="wg-tm__save"><button type="submit" form="wg-theme-form" class="button button-primary button-large">ذخیره تغییرات</button></div></div>
      <form id="wg-theme-form" method="post" action="options.php">
        <?php settings_fields('woogit_theme'); ?>
        <div class="wg-tm__tabs">
          <?php $first = true; foreach ($tabs as $id => $title): ?><button type="button" class="wg-tm__tab <?php echo $first ? 'is-active' : ''; ?>" data-tab="<?php echo esc_attr($id); ?>"><?php echo esc_html($title); ?></button><?php $first = false; endforeach; ?>
        </div>
        <section class="wg-tm__panel is-active" data-panel="general"><div class="wg-tm__notice">تصاویر فقط از Media Library انتخاب می‌شوند؛ Theme فقط Media ID را نگه می‌دارد.</div><div class="wg-tm__grid"><div class="wg-tm__card"><h2>هویت برند</h2><?php woogit_tm_text('brand_name','نام برند','WooGit'); woogit_tm_text('tagline','توضیح کوتاه سایت','مدیریت فروشگاه ووکامرس از موبایل.','textarea'); woogit_tm_text('support_email','ایمیل پشتیبانی','','email'); woogit_tm_media('logo_id','لوگوی اصلی'); woogit_tm_media('alternate_logo_id','لوگوی جایگزین'); ?></div><div class="wg-tm__card"><h2>دارایی‌های عمومی</h2><?php woogit_tm_media('favicon_id','Favicon'); woogit_tm_media('og_image_id','Open Graph image'); ?></div></div></section>
        <section class="wg-tm__panel" data-panel="home"><div class="wg-tm__card"><h2>Hero</h2><?php woogit_tm_text('hero_title','عنوان اصلی','فروشگاهتان را با <span>WooGit</span> ساده‌تر، سریع‌تر و از هرجا مدیریت کنید','textarea'); woogit_tm_text('hero_text','توضیح Hero','WooGit به شما کمک می‌کند سفارش‌ها، محصولات، موجودی، مشتریان، کوپن‌ها و تحلیل فروش را از موبایل مدیریت کنید.','textarea'); woogit_tm_text('hero_cta_text','متن CTA اصلی','دریافت WooGit'); woogit_tm_text('hero_cta_url','لینک CTA اصلی'); woogit_tm_text('app_google_play_url','لینک Google Play'); woogit_tm_text('app_bazaar_url','لینک بازار'); woogit_tm_file('app_apk_id','نسخه APK قابل دانلود','فقط APK نسخه فعلی را انتخاب کنید؛ کاربران از صفحه نصب سایت همین فایل را دانلود می‌کنند.'); woogit_tm_text('app_version','نسخه اپلیکیشن','','text','مثلاً 1.0.0'); woogit_tm_text('hero_secondary_text','متن CTA دوم','مشاهده امکانات'); woogit_tm_text('hero_secondary_url','لینک CTA دوم','#wg-home-preview'); ?></div><div class="wg-tm__grid"><div class="wg-tm__card"><h2>تصویر Tablet</h2><?php woogit_tm_media('hero_image_id','Hero تبلت'); ?></div><div class="wg-tm__card"><h2>تصویر Mobile</h2><?php woogit_tm_media('hero_mobile_image_id','Hero موبایل'); ?></div></div><div class="wg-tm__card"><h2>محتوای Sectionها</h2><?php foreach (['features_intro'=>'معرفی قابلیت‌ها','how_it_works_intro'=>'معرفی روش کار','pricing_intro'=>'معرفی قیمت‌گذاری','faq_intro'=>'معرفی FAQ','documentation_intro'=>'معرفی مستندات','support_intro'=>'معرفی پشتیبانی','status_intro'=>'معرفی وضعیت سرویس','privacy_intro'=>'معرفی حریم خصوصی','terms_intro'=>'معرفی قوانین'] as $k=>$l) woogit_tm_text($k,$l); ?></div><div class="wg-tm__card"><h2>نمایش Sectionها</h2><?php foreach (['features'=>'قابلیت‌ها','how'=>'روش کار','pricing'=>'قیمت‌گذاری','faq'=>'سؤالات متداول','app'=>'معرفی محصول'] as $k=>$l) woogit_tm_checkbox('section_'.$k.'_enabled',$l,true); ?></div></section>
        <section class="wg-tm__panel" data-panel="features"><div class="wg-tm__card"><h2>قابلیت‌ها</h2><p>عنوان، توضیح، icon، تصویر، وضعیت و ترتیب.</p><?php woogit_tm_repeater('features_json','لیست قابلیت‌ها',$features,['title'=>['label'=>'عنوان','type'=>'text'],'description'=>['label'=>'توضیح','type'=>'textarea'],'icon'=>['label'=>'Icon','type'=>'text'],'image_id'=>['label'=>'تصویر','type'=>'media'],'enabled'=>['label'=>'فعال','type'=>'checkbox','default'=>true]]); ?></div></section>
        <section class="wg-tm__panel" data-panel="steps"><div class="wg-tm__card"><h2>روش کار</h2><p>عنوان، توضیح، تصویر، لینک اختیاری، وضعیت و ترتیب.</p><?php woogit_tm_repeater('steps_json','مراحل',$steps,['title'=>['label'=>'عنوان','type'=>'text'],'description'=>['label'=>'توضیح','type'=>'textarea'],'image_id'=>['label'=>'تصویر','type'=>'media'],'url'=>['label'=>'لینک اختیاری','type'=>'url'],'enabled'=>['label'=>'فعال','type'=>'checkbox','default'=>true]]); ?></div></section>
        <section class="wg-tm__panel" data-panel="pricing"><div class="wg-tm__card"><h2>قیمت‌گذاری</h2><div class="wg-tm__readonly"><strong>منبع حقیقت:</strong> قیمت، currency، duration، limits، features و entitlement از Backend/Commerce می‌آیند و دستی در Theme ذخیره نمی‌شوند.</div><?php woogit_tm_text('pricing_intro','معرفی Pricing','پلن مناسب خودتان را انتخاب کنید.','textarea'); ?></div></section>
        <section class="wg-tm__panel" data-panel="faq"><div class="wg-tm__card"><h2>FAQ</h2><p>سؤال، پاسخ، وضعیت و ترتیب.</p><?php woogit_tm_repeater('faq_json','سؤالات',$faq,['question'=>['label'=>'سؤال','type'=>'text'],'answer'=>['label'=>'پاسخ','type'=>'textarea'],'enabled'=>['label'=>'فعال','type'=>'checkbox','default'=>true]]); ?></div></section>
        <section class="wg-tm__panel" data-panel="footer"><div class="wg-tm__grid"><div class="wg-tm__card"><h2>Footer</h2><?php woogit_tm_text('footer_text','متن معرفی Footer','مدیریت هوشمند فروشگاه ووکامرس.','textarea'); woogit_tm_text('phone','شماره تماس'); woogit_tm_text('address','آدرس'); woogit_tm_text('support_hours','ساعات پاسخ‌گویی'); woogit_tm_text('copyright_text','متن Copyright'); ?></div><div class="wg-tm__card"><h2>اینماد</h2><?php woogit_tm_checkbox('enamad_enabled','نمایش اینماد',false); woogit_tm_text('enamad_id','شناسه اینماد'); woogit_tm_text('enamad_code','کد اینماد','','textarea','کد رسمی دریافتی را بدون تغییر معنایی وارد کنید.'); woogit_tm_text('enamad_verification_url','لینک اعتبارسنجی'); woogit_tm_text('enamad_alt','متن جایگزین','نماد اعتماد الکترونیکی'); woogit_tm_text('enamad_optional_text','متن اختیاری کنار نماد'); woogit_tm_media('enamad_image_id','تصویر اینماد'); ?><div class="wg-tm__field"><label>محل نمایش</label><select name="woogit_theme_options[enamad_placement]"><option value="footer" <?php selected(woogit_theme_option('enamad_placement','footer'),'footer'); ?>>Footer</option><option value="contact" <?php selected(woogit_theme_option('enamad_placement','footer'),'contact'); ?>>Contact</option></select></div></div></div></section>
        <section class="wg-tm__panel" data-panel="social"><div class="wg-tm__card"><h2>شبکه‌های اجتماعی</h2><p>نام، URL، وضعیت و ترتیب؛ HTML خام ذخیره نمی‌شود.</p><?php woogit_tm_repeater('social_links_json','شبکه‌ها',$social,['label'=>['label'=>'نام شبکه','type'=>'text'],'url'=>['label'=>'URL','type'=>'url'],'enabled'=>['label'=>'فعال','type'=>'checkbox','default'=>true]]); ?></div></section>
        <div style="margin-top:20px"><button type="submit" class="button button-primary button-large">ذخیره تغییرات</button></div>
      </form>
    </div>
    <?php
}

function woogit_theme_admin_js() {
    if (($_GET['page'] ?? '') !== 'woogit-theme') return;
    echo <<<'HTML'
<script>
jQuery(function($){
  const form=$('#wg-theme-form');
  function activate(tab,write){$('.wg-tm__tab').removeClass('is-active');$('.wg-tm__tab[data-tab="'+tab+'"]').addClass('is-active');$('.wg-tm__panel').removeClass('is-active');$('.wg-tm__panel[data-panel="'+tab+'"]').addClass('is-active');if(write)history.replaceState(null,'',location.pathname+'?page=woogit-theme&tab='+encodeURIComponent(tab));}
  const initial=new URLSearchParams(location.search).get('tab')||location.hash.replace('#','');if(initial&&$('.wg-tm__tab[data-tab="'+initial+'"]').length)activate(initial,false);
  $('.wg-tm__tab').on('click',function(){activate($(this).data('tab'),true);});
  function sync(box){const rows=[];box.find('.wg-tm__item').each(function(i){$(this).find('.wg-tm__item-title').text('مورد '+(i+1));const obj={};$(this).find('.wg-tm-repeater-value').each(function(){const k=$(this).data('field');obj[k]=$(this).is(':checkbox')?$(this).prop('checked'):$(this).val();});rows.push(obj);});box.find('.wg-tm__json').val(JSON.stringify(rows));}
  function wire(box){box.on('input change','.wg-tm-repeater-value',()=>sync(box));box.on('click','.wg-tm-delete',function(){$(this).closest('.wg-tm__item').remove();sync(box);});box.on('click','.wg-tm-up,.wg-tm-down',function(){const row=$(this).closest('.wg-tm__item');$(this).hasClass('wg-tm-up')?row.prev().before(row):row.next().after(row);sync(box);});box.on('click','.wg-tm-repeat-pick',function(e){e.preventDefault();const c=$(this).closest('[data-repeat-media]');const f=wp.media({title:'انتخاب تصویر',button:{text:'استفاده از تصویر'},multiple:false});f.on('select',function(){const a=f.state().get('selection').first().toJSON();c.find('.wg-tm-repeater-value').val(a.id);c.find('img,.wg-tm-media-placeholder').remove();c.prepend($('<img/>',{src:a.url,alt:''}));sync(box);});f.open();});box.on('click','.wg-tm-repeat-remove',function(){const c=$(this).closest('[data-repeat-media]');c.find('.wg-tm-repeater-value').val('0');c.find('img').remove();if(!c.find('.wg-tm-media-placeholder').length)c.prepend('<div class="wg-tm__media-placeholder">تصویر</div>');sync(box);});box.find('.wg-tm__add').on('click',function(){const cols=JSON.parse(box.attr('data-columns')||'{}');const row=$('<div class="wg-tm__item"><div class="wg-tm__item-head"><strong class="wg-tm__item-title"></strong><div class="wg-tm__item-actions"><button type="button" class="button wg-tm-up">↑</button><button type="button" class="button wg-tm-down">↓</button><button type="button" class="button wg-tm__danger wg-tm-delete">حذف</button></div></div><div class="wg-tm__mini"></div></div>');Object.keys(cols).forEach(k=>{const c=cols[k],wrap=$('<div/>'),label=$('<label/>').text(c.label||k);wrap.append(label);let el;if(c.type==='textarea')el=$('<textarea/>');else if(c.type==='checkbox')el=$('<input type="checkbox"/>');else el=$('<input/>',{type:c.type==='media'?'hidden':(c.type||'text')});if(c.type==='media'){const media=$('<div class="wg-tm__media" data-repeat-media><div class="wg-tm__media-placeholder">تصویر</div></div>');el.addClass('wg-tm-repeater-value').attr('data-field',k).val('0');media.append(el).append('<button type="button" class="button wg-tm-repeat-pick">انتخاب تصویر</button><button type="button" class="button wg-tm-repeat-remove">حذف</button>');wrap.append(media);}else{el.addClass('wg-tm-repeater-value').attr('data-field',k);if(c.type==='checkbox')wrap.append($('<label class="wg-tm__check">').append(el).append(' فعال'));else wrap.append(el);}row.find('.wg-tm__mini').append(wrap);});box.find('.wg-tm__repeat-list').append(row);sync(box);});sync(box);}
  $('.wg-tm-repeater').each(function(){wire($(this));});
  $(document).on('click','.wg-tm-pick',function(e){e.preventDefault();const box=$(this).closest('[data-media-control]');const f=wp.media({title:'انتخاب تصویر',button:{text:'استفاده از تصویر'},multiple:false});f.on('select',function(){const a=f.state().get('selection').first().toJSON();box.find('.wg-tm__media-id').val(a.id);box.find('img,.wg-tm__media-placeholder').remove();box.prepend($('<img/>',{src:a.url,alt:''}));box.find('.wg-tm-remove').prop('disabled',false);});f.open();});
  $(document).on('click','.wg-tm-remove',function(e){e.preventDefault();const box=$(this).closest('[data-media-control]');box.find('.wg-tm__media-id').val('0');box.find('img').remove();if(!box.find('.wg-tm__media-placeholder').length)box.prepend('<div class="wg-tm__media-placeholder">تصویری انتخاب نشده</div>');$(this).prop('disabled',true);});
  form.on('submit',function(){$('.wg-tm-repeater').each(function(){sync($(this));});});
  $(document).on('click','.wg-tm-file-pick',function(e){e.preventDefault();const box=$(this).closest('[data-file-control]');const f=wp.media({title:'انتخاب یا آپلود APK',button:{text:'استفاده از این APK'},multiple:false,library:{type:'application/vnd.android.package-archive'}});f.on('select',function(){const m=f.state().get('selection').first().toJSON();box.find('.wg-tm__file-id').val(m.id);box.find('.wg-tm__file-name').text(m.filename||m.title||'APK');box.find('.wg-tm-file-remove').prop('disabled',false);});f.open();});
  $(document).on('click','.wg-tm-file-remove',function(e){e.preventDefault();const box=$(this).closest('[data-file-control]');box.find('.wg-tm__file-id').val('0');box.find('.wg-tm__file-name').text('فایلی انتخاب نشده');$(this).prop('disabled',true);});
});
</script>
HTML;
}
add_action('admin_footer', 'woogit_theme_admin_js');
