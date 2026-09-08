<?php
/**
 * Build a public static preview from the actual Theme PHP templates.
 *
 * This is a deliberately tiny WordPress-compatible render harness for the
 * public home template. It executes the Theme's front-page.php, header.php,
 * home.php and footer.php directly; it does not maintain a second HTML copy.
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$theme = $root . '/theme/woogit';
$out = $root . '/_site';

if (!is_dir($theme)) {
    fwrite(STDERR, "Theme directory not found\n");
    exit(1);
}

if (!is_dir($out) && !mkdir($out, 0777, true) && !is_dir($out)) {
    fwrite(STDERR, "Could not create output directory\n");
    exit(1);
}

// Minimal WordPress surface used by the public Theme templates.
define('ABSPATH', $root . '/');
define('WOOGIT_THEME_DIR', $theme);
define('WOOGIT_THEME_URI', '');

function esc_html($value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function esc_attr($value): string { return esc_html($value); }
function esc_url($value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function esc_url_raw($value): string { return (string)$value; }
function home_url(string $path = '/'): string { return '/' . ltrim($path, '/'); }
function get_template_directory(): string { return WOOGIT_THEME_DIR; }
function get_template_directory_uri(): string { return ''; }
function get_option(string $key, $default = false) { return $default; }
function get_theme_mod(string $key, $default = false) { return $default; }
function is_page($pages = null): bool { return false; }
function get_custom_logo(): string { return ''; }
function language_attributes(): void { echo 'lang="fa" dir="rtl"'; }
function bloginfo(string $show = ''): void { echo $show === 'charset' ? 'UTF-8' : ($show === 'description' ? 'WooGit' : 'WooGit'); }
function body_class(): void { echo 'class="home"'; }
function wp_body_open(): void {}
function wp_date(string $format): string { return date($format); }
function wp_head(): void {
    $files = ['foundation.css','components.css','pages.css','responsive.css','theme-polish.css'];
    foreach ($files as $file) {
        echo '<link rel="stylesheet" href="assets/css/' . esc_attr($file) . '">';
    }
}
function wp_footer(): void {}
function add_action(...$args): void {}
function add_filter(...$args): void {}
function get_queried_object_id(): int { return 0; }
function get_post_field(string $field, int $id): string { return ''; }
function get_page_by_path(string $slug) { return null; }
function get_permalink($page): string { return home_url('/'); }
function get_header(): void { require WOOGIT_THEME_DIR . '/header.php'; }
function get_footer(): void { require WOOGIT_THEME_DIR . '/footer.php'; }
function get_template_part(string $slug, ?string $name = null, array $args = []): void {
    if ($slug === 'templates/public/home') {
        require WOOGIT_THEME_DIR . '/templates/public/home.php';
    }
}
function woogit_api_get(string $path) { return []; }
function woogit_enamad_settings(): array { return ['enabled'=>false,'placement'=>'footer','verification_url'=>'','alt_text'=>'']; }

require $theme . '/inc/helpers/view.php';
require $theme . '/inc/portal/data.php';

ob_start();
require $theme . '/front-page.php';
$html = ob_get_clean();

if ($html === false || trim($html) === '') {
    fwrite(STDERR, "Theme render produced empty output\n");
    exit(1);
}

// The static artifact must contain only the rendered Theme page and its assets.
file_put_contents($out . '/index.html', "<!doctype html>\n" . $html);

echo "Rendered actual Theme front page to _site/index.html\n";
