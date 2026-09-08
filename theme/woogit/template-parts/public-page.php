<?php
if (!defined('ABSPATH')) exit;

$options = woogit_theme_options();
$slug = get_post_field('post_name', get_queried_object_id());
$title = get_the_title();
$intro = '';
$section_title = '';
$items = [];

switch ($slug) {
  case 'features':
    $intro = woogit_safe_text($options['features_intro'] ?? 'قابلیت‌های WooGit برای مدیریت بهتر اتصال و حساب شما.');
    $section_title = 'قابلیت‌ها';
    $items = woogit_theme_items('features_json', []);
    break;
  case 'how-it-works':
    $intro = woogit_safe_text($options['how_it_works_intro'] ?? 'فرآیند اتصال و استفاده از WooGit ساده و شفاف طراحی شده است.');
    $section_title = 'نحوه کار';
    $items = woogit_theme_items('steps_json', []);
    break;
  case 'faq':
    $intro = woogit_safe_text($options['faq_intro'] ?? 'پاسخ پرسش‌های رایج درباره WooGit.');
    $section_title = 'سؤالات متداول';
    $items = woogit_theme_items('faq_json', []);
    break;
  case 'documentation':
    $intro = woogit_safe_text($options['documentation_intro'] ?? 'مستندات و راهنمای استفاده از WooGit.');
    $section_title = 'مستندات';
    $items = [];
    break;
  case 'support':
    $intro = woogit_safe_text($options['support_intro'] ?? 'برای دریافت راهنمایی و پشتیبانی با ما در ارتباط باشید.');
    $section_title = 'پشتیبانی';
    $items = [];
    break;
  case 'service-status':
    $intro = woogit_safe_text($options['status_intro'] ?? 'وضعیت سرویس‌ها را از همین صفحه بررسی کنید.');
    $section_title = 'وضعیت سرویس';
    $items = [];
    break;
  case 'privacy':
    $intro = woogit_safe_text($options['privacy_intro'] ?? 'نحوه جمع‌آوری، استفاده و حفاظت از اطلاعات.');
    $section_title = 'حریم خصوصی';
    break;
  case 'terms':
    $intro = woogit_safe_text($options['terms_intro'] ?? 'شرایط و ضوابط استفاده از WooGit.');
    $section_title = 'قوانین و شرایط';
    break;
}

get_header();
?>
<section class="wg-page-head wg-container">
  <span class="wg-eyebrow">WooGit</span>
  <h1><?php echo esc_html($title); ?></h1>
  <?php if ($intro): ?><p><?php echo $intro; ?></p><?php endif; ?>
</section>

<section class="wg-section wg-container" aria-labelledby="wg-public-section-title">
  <div class="wg-section__head">
    <h2 id="wg-public-section-title"><?php echo esc_html($section_title ?: $title); ?></h2>
  </div>

  <?php if ($slug === 'faq' && $items): ?>
    <div class="wg-faq-list">
      <?php foreach ($items as $item):
        $question = isset($item['question']) ? wp_strip_all_tags((string)$item['question']) : '';
        $answer = isset($item['answer']) ? wp_kses_post((string)$item['answer']) : '';
        if (!$question || !$answer) continue;
      ?>
        <details class="wg-card">
          <summary><?php echo esc_html($question); ?></summary>
          <div class="wg-card__body"><?php echo $answer; ?></div>
        </details>
      <?php endforeach; ?>
    </div>
  <?php elseif ($items): ?>
    <div class="wg-grid wg-grid--3">
      <?php foreach ($items as $index => $item):
        $heading = isset($item['title']) ? wp_strip_all_tags((string)$item['title']) : (isset($item['name']) ? wp_strip_all_tags((string)$item['name']) : '');
        $body = isset($item['description']) ? wp_kses_post((string)$item['description']) : (isset($item['text']) ? wp_kses_post((string)$item['text']) : '');
        if (!$heading && !$body) continue;
      ?>
        <article class="wg-card">
          <?php if ($heading): ?><h3><?php echo esc_html($heading); ?></h3><?php endif; ?>
          <?php if ($body): ?><div class="wg-card__body"><?php echo $body; ?></div><?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  <?php elseif ($slug === 'documentation' || $slug === 'support' || $slug === 'privacy' || $slug === 'terms'): ?>
    <article class="wg-card wg-content-card">
      <?php while (have_posts()): the_post(); the_content(); endwhile; ?>
    </article>
  <?php elseif ($slug === 'service-status'): ?>
    <article class="wg-card">
      <div class="wg-status wg-status--neutral" role="status">اطلاعات وضعیت سرویس در حال حاضر از یک منبع زنده دریافت نمی‌شود.</div>
    </article>
  <?php else: ?>
    <article class="wg-card">
      <?php while (have_posts()): the_post(); the_content(); endwhile; ?>
    </article>
  <?php endif; ?>
</section>
<?php get_footer();
