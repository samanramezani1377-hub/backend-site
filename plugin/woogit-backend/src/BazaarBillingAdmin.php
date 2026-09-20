<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class BazaarBillingAdmin
{
    private const OPTION = 'woogit_bazaar_settings';

    public function register(): void
    {
        add_submenu_page(
            'woogit',
            'کافه‌بازار',
            'کافه‌بازار',
            'manage_options',
            'woogit-bazaar',
            [$this, 'render']
        );
    }

    public static function settings(): array
    {
        $saved = get_option(self::OPTION, []);
        return is_array($saved) ? $saved : [];
    }

    public static function value(string $key): string
    {
        $settings = self::settings();
        return trim((string)($settings[$key] ?? ''));
    }

    public function render(): void
    {
        if (!current_user_can('manage_options')) wp_die('دسترسی غیرمجاز.');
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['woogit_bazaar_save'])) {
            check_admin_referer('woogit_bazaar_settings');
            $settings = self::settings();
            foreach (['package_name','client_id','client_secret','refresh_token','api_base_url'] as $key) {
                if (array_key_exists($key, $_POST)) {
                    $value = sanitize_text_field(wp_unslash((string)$_POST[$key]));
                    if (in_array($key, ['client_secret','refresh_token'], true) && $value === '') continue;
                    $settings[$key] = $value;
                }
            }
            if (!empty($_POST['woogit_bazaar_clear_secrets'])) {
                $settings['client_secret'] = '';
                $settings['refresh_token'] = '';
            }
            update_option(self::OPTION, $settings, false);
            echo '<div class="notice notice-success is-dismissible"><p>تنظیمات کافه‌بازار ذخیره شد.</p></div>';
        }

        $s = self::settings();
        ?>
        <div class="wrap">
            <h1>تنظیمات کافه‌بازار WooGit</h1>
            <p>اطلاعات اتصال Developer API کافه‌بازار فقط در Backend نگهداری می‌شود و هرگز به اپ ارسال نمی‌شود.</p>
            <form method="post">
                <?php wp_nonce_field('woogit_bazaar_settings'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="woogit_bazaar_package_name">Package Name</label></th>
                        <td><input name="package_name" id="woogit_bazaar_package_name" class="regular-text" value="<?php echo esc_attr($s['package_name'] ?? ''); ?>" autocomplete="off"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="woogit_bazaar_client_id">Client ID</label></th>
                        <td><input name="client_id" id="woogit_bazaar_client_id" class="regular-text" value="<?php echo esc_attr($s['client_id'] ?? ''); ?>" autocomplete="off"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="woogit_bazaar_client_secret">Client Secret</label></th>
                        <td><input type="password" name="client_secret" id="woogit_bazaar_client_secret" class="regular-text" value="" placeholder="<?php echo !empty($s['client_secret']) ? esc_attr('••••••••  (ذخیره شده؛ برای حفظ آن خالی بگذارید)') : ''; ?>" autocomplete="new-password"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="woogit_bazaar_refresh_token">Refresh Token</label></th>
                        <td><input type="password" name="refresh_token" id="woogit_bazaar_refresh_token" class="large-text" value="" placeholder="<?php echo !empty($s['refresh_token']) ? esc_attr('••••••••  (ذخیره شده؛ برای حفظ آن خالی بگذارید)') : ''; ?>" autocomplete="new-password"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="woogit_bazaar_api_base_url">API Base URL</label></th>
                        <td><input name="api_base_url" id="woogit_bazaar_api_base_url" class="regular-text" value="<?php echo esc_attr($s['api_base_url'] ?? ''); ?>" placeholder="https://pardakht.cafebazaar.ir"><p class="description">اختیاری؛ اگر خالی باشد مقدار پیش‌فرض Backend استفاده می‌شود.</p></td>
                    </tr>
                </table>
                <p><label><input type="checkbox" name="woogit_bazaar_clear_secrets" value="1"> Client Secret و Refresh Token ذخیره‌شده پاک شوند.</label></p>
                <p class="submit"><button type="submit" name="woogit_bazaar_save" class="button button-primary">ذخیره تنظیمات</button></p>
            </form>
            <hr>
            <h2>SKU پلن‌ها</h2>
            <p>شناسه محصول کافه‌بازار را از صفحه هر محصول/Variation ووکامرس، در فیلد <strong>Cafe Bazaar SKU</strong> وارد کنید. این SKU باید دقیقاً با Product ID تعریف‌شده در کافه‌بازار یکسان باشد.</p>
            <p><strong>نکته امنیتی:</strong> Secret و Refresh Token در Git، APK یا پاسخ API قرار نمی‌گیرند. برای محیط‌های حساس، استفاده از ثابت‌های <code>wp-config.php</code> یا environment variable همچنان گزینه ترجیحی است.</p>
        </div>
        <?php
    }
}
