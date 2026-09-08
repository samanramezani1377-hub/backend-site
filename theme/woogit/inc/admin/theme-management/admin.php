<?php
if (!defined('ABSPATH')) exit;

function woogit_theme_management_menu() {
    add_theme_page('WooGit Theme', 'WooGit Theme', 'manage_options', 'woogit-theme', 'woogit_render_theme_management');
}

function woogit_theme_admin_enqueue($hook) {
    if ($hook !== 'appearance_page_woogit-theme') return;
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'woogit_theme_admin_enqueue');

function woogit_theme_admin_css() {
    if (!isset($_GET['page']) || $_GET['page'] !== 'woogit-theme') return;
    ?>
    <style>
      .wg-tm{max-width:1180px;margin:24px 20px 40px 0;direction:rtl}.wg-tm *{box-sizing:border-box}.wg-tm__head{display:flex;justify-content:space-between;gap:20px;align-items:flex-end;margin-bottom:20px}.wg-tm__head h1{margin:0 0 6px;font-size:28px}.wg-tm__head p{margin:0;color:#646970}.wg-tm__save{position:sticky;top:32px;z-index:5}.wg-tm__tabs{display:flex;gap:6px;flex-wrap:wrap;border-bottom:1px solid #dcdcde;margin-bottom:18px}.wg-tm__tab{border:0;background:transparent;padding:11px 15px;cursor:pointer;color:#50575e;font-weight:600;border-bottom:3px solid transparent}.wg-tm__tab.is-active{color:#2271b1;border-bottom-color:#2271b1}.wg-tm__panel{display:none}.wg-tm__panel.is-active{display:block}.wg-tm__grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.wg-tm__card{background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:20px;margin-bottom:16px;box-shadow:0 1px 2px rgba(0,0,0,.03)}.wg-tm__card h2{margin:0 0 6px;font-size:17px}.wg-tm__card>p{margin:0 0 16px;color:#646970}.wg-tm__field{margin:0 0 16px}.wg-tm__field:last-child{margin-bottom:0}.wg-tm__field label{display:block;font-weight:600;margin-bottom:6px}.wg-tm__field input[type=text],.wg-tm__field input[type=url],.wg-tm__field input[type=email],.wg-tm__field textarea,.wg-tm__field select{width:100%;max-width:none}.wg-tm__field textarea{min-height:90px}.wg-tm__help{font-size:12px;color:#646970;margin-top:5px}.wg-tm__media{display:flex;align-items:center;gap:12px;padding:12px;border:1px dashed #c3c4c7;border-radius:10px}.wg-tm__media img{width:76px;height:76px;object-fit:cover;border-radius:8px;background:#f0f0f1}.wg-tm__media-placeholder{width:76px;height:76px;border-radius:8px;background:#f0f0f1;display:grid;place-items:center;color:#646970;font-size:11px;text-align:center}.wg-tm__media-actions{display:flex;gap:7px;flex-wrap:wrap}.wg-tm__repeat{display:grid;gap:10px}.wg-tm__item{border:1px solid #dcdcde;border-radius:10px;padding:14px;background:#fafafa}.wg-tm__item-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}.wg-tm__item-title{font-weight:700}.wg-tm__item-actions{display:flex;gap:5px}.wg-tm__repeat .wg-tm__mini{display:grid;grid-template-columns:1fr 1fr;gap:10px}.wg-tm__repeat textarea{min-height:70px}.wg-tm__add{margin-top:10px}.wg-tm__notice{padding:12px 14px;background:#f0f6fc;border-right:4px solid #2271b1;border-radius:7px;margin-bottom:16px}.wg-tm__check{display:flex!important;align-items:center;gap:8px;font-weight:500!important}.wg-tm__check input{margin:0}.wg-tm__actions{display:flex;justify-content:flex-start;gap:8px;margin-top:20px}.wg-tm__readonly{background:#f6f7f7;border:1px solid #dcdcde;padding:12px;border-radius:8px}.wg-tm__danger{color:#b32d2e}.wg-tm__item[data-disabled="1"]{opacity:.58}.wg-tm__json{display:none!important}@media(max-width:800px){.wg-tm__grid{grid-template-columns:1fr}.wg-tm__head{align-items:flex-start;flex-direction:column}.wg-tm__save{position:static}.wg-tm__repeat .wg-tm__mini{grid-template-columns:1fr}}
    </style>
    <?php
}
add_action('admin_head', 'woogit_theme_admin_css');

function woogit_tm_text($key, $label, $default = '', $type = 'text', $help = '') {
    $value = woogit_theme_option($key, $default);
    printf('<div class="wg-tm__field"><label for="wg-tm-%1$s">%2$s</label>', esc_attr($key), esc_html($label));
    if ($type === 'textarea') {
        printf('<textarea id="wg-tm-%1$s" name="woogit_theme_options[%1$s]">%2$s</textarea>', esc_attr($key), esc_textarea($value));
    } else {
        printf('<input id="wg-tm-%1$s" type="%2$s" name="woogit_theme_options[%1$s]" value="%3$s">', esc_attr($key), esc_attr($type), esc_attr($value));
    }
    if ($help) printf('<div class="wg-tm__help">%s</div>', esc_html($help));
    echo '</div>';
}

function woogit_tm_checkbox($key, $label, $default = false) {
    $value = woogit_theme_option($key, $default ? '1' : '0');
    printf('<div class="wg-tm__field"><input type="hidden" name="woogit_theme_options[%1$s]" value="0"><label class="wg-tm__check"><input type="checkbox" name="woogit_theme_options[%1$s]" value="1" %2$s> %3$s</label></div>', esc_attr($key), checked($value, '1', false), esc_html($label));
}

function woogit_tm_media($key, $label, $help = '') {
    $id = absint(woogit_theme_option($key, 0));
    $url = $id ? woogit_theme_image($id, 'medium') : '';
    echo '<div class="wg-tm__field"><label>'.esc_html($label).'</label><div class="wg-tm__media" data-media-control data-target="'.esc_attr($key).'">';
    if ($url) printf('<img src="%s" alt="">', esc_url($url)); else echo '<div class="wg-tm__media-placeholder">تصویری انتخاب نشده</div>';
    echo '<div><input type="hidden" class="wg-tm__media-id" name="woogit_theme_options['.esc_attr($key).']" value="'.esc_attr($id).'">';
    echo '<div class="wg-tm__media-actions"><button type="button" class="button wg-tm-pick">انتخاب تصویر</button><button type="button" class="button wg-tm-remove" '.($id?'':'disabled').'>حذف</button></div>';
    if ($help) echo '<div class="wg-tm__help">'.esc_html($help).'</div>';
    echo '</div></div></div>';
}

function woogit_tm_repeater($key, $label, $defaults, $columns, $help = '') {
    $items = woogit_theme_items($key, $defaults);
    echo '<div class="wg-tm__field wg-tm-repeater" data-repeater="'.esc_attr($key).'" data-columns="'.esc_attr(wp_json_encode($columns)).'">';
    echo '<label>'.esc_html($label).'</label>';
    if ($help) echo '<div class="wg-tm__help" style="margin-bottom:8px">'.esc_html($help).'</div>';
    printf('<input class="wg-tm__json" type="text" name="woogit_theme_options[%1$s]" value="%2$s">', esc_attr($key), esc_attr(wp_json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));
    echo '<div class="wg-tm__repeat-list">';
    foreach ($items as $index => $item) woogit_tm_repeater_item($index, is_array($item) ? $item : [], $columns);
    echo '</div><button type="button" class="button wg-tm__add">افزودن مورد</button></div>';
}

function woogit_tm_repeater_item($index, $item, $columns) {
    echo '<div class="wg-tm__item" data-index="'.esc_attr($index).'">';
    echo '<div class="wg-tm__item-head"><span class="wg-tm__item-title">مورد '.esc_html((int)$index + 1).'</span><div class="wg-tm__item-actions"><button type="button" class="button wg-tm-up">↑</button><button type="button" class="button wg-tm-down">↓</button><button type="button" class="button wg-tm__danger wg-tm-delete">حذف</button></div></div><div class="wg-tm__mini">';
    foreach ($columns as $field => $config) {
        $label = $config['label']; $type = $config['type'] ?? 'text'; $value = $item[$field] ?? ($config['default'] ?? '');
        echo '<div class="wg-tm__subfield"><label>'.esc_html($label).'</label>';
        if ($type === 'checkbox') {
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
    $tabs = [
        'general' => 'تنظیمات عمومی', 'home' => 'صفحه اصلی', 'features' => 'قابلیت‌ها', 'steps' => 'روش کار',
        'pricing' => 'Pricing', 'faq' => 'FAQ', 'footer' => 'Footer', 'social' => 'شبکه‌های اجتماعی'
    ];
    $features = woogit_theme_items('features_json', [
        ['title'=>'امنیت و اعتماد','text'=>'مرز روشن بین Theme، Backend و فروشگاه مشتری.','icon'=>'01','enabled'=>true],
        ['title'=>'تجربه ساده','text'=>'رابط تمیز با hierarchy مشخص و بدون شلوغی.','icon'=>'02','enabled'=>true],
        ['title'=>'اطلاعات شفاف','text'=>'وضعیت حساب، اشتراک و سرویس در جای درست.','icon'=>'03','enabled'=>true],
    ]);
    $steps = woogit_theme_items('steps_json', [
        ['title'=>'شروع وب','text'=>'حساب خود را از مسیر رسمی وب وارد یا ایجاد کنید.','enabled'=>true],
        ['title'=>'انتخاب سرویس','text'=>'پلن‌ها از داده معتبر سرویس نمایش داده می‌شوند.','enabled'=>true],
        ['title'=>'مدیریت از پرتال','text'=>'اشتراک، Billing، پرداخت‌ها و امنیت حساب را مدیریت کنید.','enabled'=>true],
    ]);
    $faq = woogit_theme_items('faq_json', [
        ['question'=>'آیا Theme به WooCommerce فروشگاه من متصل می‌شود؟','answer'=>'خیر. Theme فقط presentation و orchestration وب است.'],
        ['question'=>'اطلاعات پرداخت از کجا می‌آید؟','answer'=>'از WooCommerce خود woogit.ir و منابع authoritative سرویس.'],
    ]);
    $social = woogit_theme_items('social_links_json', []);
    ?>
    <div class="wg-tm">
      <div class="wg-tm__head"><div><h1>WooGit Theme</h1><p>مدیریت محتوای وب و Presentation؛ بدون ورود به مسئولیت‌های Backend یا Store Dashboard.</p></div><div class="wg-tm__save"><button type="submit" form="wg-theme-form" class="button button-primary button-large">ذخیره تغییرات</button></div></div>
      <form id="wg-theme-form" method="post" action="options.php">
        <?php settings_fields('woogit_theme'); ?>
        <div class="wg-tm__tabs">
        <?php $first=true; foreach($tabs as $id=>$title): ?><button type="button" class="wg-tm__tab <?php echo $first?'is-active':''; ?>" data-tab="<?php echo esc_attr($id); ?>"><?php echo esc_html($title); ?></button><?php $first=false; endforeach; ?>
        </div>

        <section class="wg-tm__panel is-active" data-panel="general">
          <div class="wg-tm__notice">تمام تصاویر باید از Media Library انتخاب شوند. Theme فقط Media ID را نگه می‌دارد و فایل یا Base64 داخل Theme ذخیره نمی‌شود.</div>
          <div class="wg-tm__grid"><div class="wg-tm__card"><h2>هویت برند</h2><p>نام و پیام کوتاه برند.</p><?php woogit_tm_text('brand_name','نام برند','WooGit');woogit_tm_text('tagline','توضیح کوتاه سایت','مدیریت هوشمند فروشگاه ووکامرس.','textarea');woogit_tm_text('support_email','ایمیل پشتیبانی','','email');woogit_tm_media('logo_id','لوگوی اصلی');woogit_tm_media('alternate_logo_id','لوگوی جایگزین','برای زمینه‌های متفاوت در صورت نیاز.');</div>
          <div class="wg-tm__card"><h2>تصاویر عمومی</h2><p>دارایی‌های تصویری مشترک سایت.</p><?php woogit_tm_media('favicon_id','Favicon');woogit_tm_media('og_image_id','تصویر پیش‌فرض Open Graph'); ?></div></div>
        </section>

        <section class="wg-tm__panel" data-panel="home">
          <div class="wg-tm__card"><h2>Hero</h2><p>تمام محتوای اصلی Hero از اینجا قابل کنترل است.</p><?php woogit_tm_text('hero_title','عنوان اصلی','فروشگاه شما، با WooGit ساده‌تر و هوشمندتر','textarea');woogit_tm_text('hero_text','توضیح Hero','WooGit ابزارهایی برای مدیریت و هوشمندسازی تجربه فروشگاه ووکامرس شما فراهم می‌کند.','textarea');woogit_tm_text('hero_cta_text','متن CTA اصلی','دریافت WooGit');woogit_tm_text('hero_cta_url','لینک CTA اصلی');woogit_tm_text('hero_secondary_text','متن CTA دوم','مشاهده پیش‌نمایش');woogit_tm_text('hero_secondary_url','لینک CTA دوم','#wg-home-preview'); ?></div>
          <div class="wg-tm__grid"><div class="wg-tm__card"><h2>تصویر تبلت</h2><?php woogit_tm_media('hero_image_id','Hero تبلت','تصویر مخصوص نمایش Tablet.'); ?></div><div class="wg-tm__card"><h2>تصویر موبایل</h2><?php woogit_tm_media('hero_mobile_image_id','Hero موبایل','تصویر مخصوص نمایش Mobile.'); ?></div></div>
          <div class="wg-tm__card"><h2>معرفی Sectionها</h2><p>متن‌های معرفی قابل استفاده در صفحات عمومی.</p><?php woogit_tm_text('features_intro','معرفی قابلیت‌ها');woogit_tm_text('how_it_works_intro','معرفی روش کار');woogit_tm_text('faq_intro','معرفی FAQ');woogit_tm_text('documentation_intro','معرفی مستندات');woogit_tm_text('support_intro','معرفی پشتیبانی');woogit_tm_text('status_intro','معرفی وضعیت سرویس');woogit_tm_text('privacy_intro','معرفی حریم خصوصی');woogit_tm_text('terms_intro','معرفی قوانین'); ?></div>
          <div class="wg-tm__card"><h2>نمایش Sectionهای Home</h2><p>هر Section اختیاری را می‌توان بدون حذف داده خاموش کرد.</p><?php foreach(['features'=>'قابلیت‌ها','how'=>'روش کار','pricing'=>'قیمت‌گذاری','faq'=>'سؤالات متداول','app'=>'معرفی محصول'] as $k=>$label) woogit_tm_checkbox('section_'.$k.'_enabled',$label,true); ?></div>
        </section>

        <section class="wg-tm__panel" data-panel="features"><div class="wg-tm__card"><h2>قابلیت‌ها</h2><p>افزودن، حذف، فعال/غیرفعال کردن و جابه‌جایی ترتیب.</p><?php woogit_tm_repeater('features_json','لیست قابلیت‌ها',$features,['title'=>['label'=>'عنوان','type'=>'text'],'text'=>['label'=>'توضیح','type'=>'textarea'],'icon'=>['label'=>'Icon / شماره','type'=>'text','default'=>'01'],'enabled'=>['label'=>'فعال','type'=>'checkbox','default'=>true]],''); ?></div></section>
        <section class="wg-tm__panel" data-panel="steps"><div class="wg-tm__card"><h2>روش کار</h2><p>Stepها را اضافه، حذف، فعال/غیرفعال و مرتب کنید.</p><?php woogit_tm_repeater('steps_json','مراحل',$steps,['title'=>['label'=>'عنوان','type'=>'text'],'text'=>['label'=>'توضیح','type'=>'textarea'],'enabled'=>['label'=>'فعال','type'=>'checkbox','default'=>true]]); ?></div></section>

        <section class="wg-tm__panel" data-panel="pricing"><div class="wg-tm__card"><h2>Pricing</h2><p>قیمت و entitlement اینجا قابل ویرایش نیست؛ منبع حقیقت آن Backend/Commerce است.</p><div class="wg-tm__readonly"><strong>مرز داده:</strong> Theme فقط عنوان معرفی، توضیح و Presentation را مدیریت می‌کند. قیمت، currency، duration، limits و entitlement نباید به‌صورت دستی در Theme ثبت شوند.</div><?php woogit_tm_text('pricing_intro','معرفی Pricing','پلن مناسب خودتان را انتخاب کنید.','textarea'); ?></div></section>

        <section class="wg-tm__panel" data-panel="faq"><div class="wg-tm__card"><h2>سؤالات متداول</h2><p>FAQها را بدون ویرایش PHP مدیریت کنید.</p><?php woogit_tm_repeater('faq_json','سؤالات',$faq,['question'=>['label'=>'سؤال','type'=>'text'],'answer'=>['label'=>'پاسخ','type'=>'textarea']]); ?></div></section>

        <section class="wg-tm__panel" data-panel="footer"><div class="wg-tm__grid"><div class="wg-tm__card"><h2>Footer</h2><p>اطلاعات معرفی و پشتیبانی.</p><?php woogit_tm_text('footer_text','متن معرفی Footer','مدیریت هوشمند فروشگاه ووکامرس.','textarea');woogit_tm_text('phone','شماره تماس');woogit_tm_text('address','آدرس','');woogit_tm_text('support_hours','ساعات پاسخ‌گویی');woogit_tm_text('copyright_text','متن Copyright',''); ?></div><div class="wg-tm__card"><h2>اینماد</h2><p>اطلاعات اعتبار و ظاهر نمایش جدا نگه داشته می‌شوند.</p><?php woogit_tm_checkbox('enamad_enabled','نمایش اینماد',false);woogit_tm_text('enamad_id','namad_id');woogit_tm_text('enamad_code','namad_code','','text','کد دریافت‌شده از سامانه را بدون تغییر نگه دارید.');woogit_tm_text('enamad_verification_url','لینک اعتبارسنجی');woogit_tm_text('enamad_alt','متن جایگزین','نماد اعتماد الکترونیکی');woogit_tm_media('enamad_image_id','تصویر اینماد'); ?><div class="wg-tm__field"><label for="wg-tm-enamad-placement">محل نمایش</label><select id="wg-tm-enamad-placement" name="woogit_theme_options[enamad_placement]"><option value="footer" <?php selected(woogit_theme_option('enamad_placement','footer'),'footer'); ?>>Footer</option><option value="contact" <?php selected(woogit_theme_option('enamad_placement','footer'),'contact'); ?>>Contact</option></select></div></div></div></section>

        <section class="wg-tm__panel" data-panel="social"><div class="wg-tm__card"><h2>شبکه‌های اجتماعی</h2><p>برای هر شبکه نوع، لینک، وضعیت و ترتیب را مدیریت کنید. Icon HTML خام ذخیره نمی‌شود.</p><?php woogit_tm_repeater('social_links_json','شبکه‌ها',$social,['label'=>['label'=>'نام شبکه','type'=>'text'],'url'=>['label'=>'URL','type'=>'url'],'enabled'=>['label'=>'فعال','type'=>'checkbox','default'=>true]]); ?></div></section>
        <div class="wg-tm__actions"><button type="submit" class="button button-primary button-large">ذخیره تغییرات</button></div>
      </form>
    </div>
    <?php
}

function woogit_theme_admin_js() {
    if (!isset($_GET['page']) || $_GET['page'] !== 'woogit-theme') return;
    ?>
    <script>
    jQuery(function($){
      const form=$('#wg-theme-form');
      $('.wg-tm__tab').on('click',function(){const tab=$(this).data('tab');$('.wg-tm__tab').removeClass('is-active');$(this).addClass('is-active');$('.wg-tm__panel').removeClass('is-active');$('.wg-tm__panel[data-panel="'+tab+'"]').addClass('is-active');history.replaceState(null,'','#wpbody-content?'+new URLSearchParams({page:'woogit-theme',tab:tab}).toString());});
      const syncRepeater=(box)=>{const rows=[];box.find('.wg-tm__item').each(function(i){$(this).find('.wg-tm__item-title').text('مورد '+(i+1));const obj={};$(this).find('.wg-tm-repeater-value').each(function(){const key=$(this).data('field');obj[key]=$(this).is(':checkbox')?$(this).prop('checked'):$(this).val();});rows.push(obj);});box.find('.wg-tm__json').val(JSON.stringify(rows));};
      const wire=(box)=>{box.on('input change','.wg-tm-repeater-value',()=>syncRepeater(box));box.on('click','.wg-tm-delete',function(){if(box.find('.wg-tm__item').length>1)$(this).closest('.wg-tm__item').remove();else $(this).closest('.wg-tm__item').find('.wg-tm-repeater-value').each(function(){if($(this).is(':checkbox'))$(this).prop('checked',false);else $(this).val('');});syncRepeater(box);});box.on('click','.wg-tm-up,.wg-tm-down',function(){const row=$(this).closest('.wg-tm__item');if($(this).hasClass('wg-tm-up'))row.prev().before(row);else row.next().after(row);syncRepeater(box);});box.find('.wg-tm__add').on('click',function(){const cols=JSON.parse(box.attr('data-columns')||'{}');const row=$('<div class="wg-tm__item"><div class="wg-tm__item-head"><span class="wg-tm__item-title"></span><div class="wg-tm__item-actions"><button type="button" class="button wg-tm-up">↑</button><button type="button" class="button wg-tm-down">↓</button><button type="button" class="button wg-tm__danger wg-tm-delete">حذف</button></div></div><div class="wg-tm__mini"></div></div>');Object.keys(cols).forEach(k=>{const c=cols[k],wrap=$('<div class="wg-tm__subfield"></div>');wrap.append($('<label/>').text(c.label||k));let el;if(c.type==='textarea')el=$('<textarea/>');else if(c.type==='checkbox')el=$('<input type="checkbox"/>');else el=$('<input/>',{type:c.type||'text'});el.addClass('wg-tm-repeater-value').attr('data-field',k);if(c.type==='checkbox')wrap.append($('<label class="wg-tm__check">').append(el).append(' فعال'));else wrap.append(el);row.find('.wg-tm__mini').append(wrap);});box.find('.wg-tm__repeat-list').append(row);syncRepeater(box);});syncRepeater(box);};
      $('.wg-tm-repeater').each(function(){wire($(this));});
      $(document).on('click','.wg-tm-pick',function(e){e.preventDefault();const box=$(this).closest('[data-media-control]');const frame=wp.media({title:'انتخاب تصویر',button:{text:'استفاده از تصویر'},multiple:false});frame.on('select',function(){const a=frame.state().get('selection').first().toJSON();box.find('.wg-tm__media-id').val(a.id);box.find('img,.wg-tm-media-placeholder').remove();box.prepend($('<img/>',{src:a.url,alt:''}));box.find('.wg-tm-remove').prop('disabled',false);});frame.open();});
      $(document).on('click','.wg-tm-remove',function(e){e.preventDefault();const box=$(this).closest('[data-media-control]');box.find('.wg-tm__media-id').val('0');box.find('img').remove();if(!box.find('.wg-tm-media-placeholder').length)box.prepend('<div class="wg-tm__media-placeholder">تصویری انتخاب نشده</div>');$(this).prop('disabled',true);});
      form.on('submit',function(){$('.wg-tm-repeater').each(function(){syncRepeater($(this));});});
    });
    </script>
    <?php
}
add_action('admin_footer', 'woogit_theme_admin_js');
