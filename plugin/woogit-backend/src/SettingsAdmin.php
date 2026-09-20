<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class SettingsAdmin
{
    private const BAZAAR_OPTION = 'woogit_bazaar_settings';

    public function register(): void
    {
        add_submenu_page(
            'woogit',
            'WooGit Settings',
            'Settings',
            'manage_options',
            'woogit-settings',
            [$this, 'render']
        );
    }

    public function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to manage WooGit settings.', 'woogit-backend'));
        }

        $tab = sanitize_key((string)($_GET['tab'] ?? 'general'));
        if (!in_array($tab, ['general', 'bazaar'], true)) {
            $tab = 'general';
        }

        $notice = '';
        $error = '';

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['woogit_bazaar_save'])) {
            check_admin_referer('woogit_bazaar_save');
            [$ok, $settings, $error] = $this->saveBazaar($_POST);
            if ($ok) {
                $notice = 'تنظیمات کافه‌بازار ذخیره شد.';
            }
        }

        $settings = $this->bazaarSettings();

        ?>
        <div class="wrap woogit-settings-admin">
            <style>
                .woogit-settings-admin{max-width:1100px}
                .woogit-settings-admin .wg-settings-head{display:flex;justify-content:space-between;align-items:flex-end;gap:20px;margin:20px 0}
                .woogit-settings-admin .wg-settings-head h1{margin:0 0 6px}
                .woogit-settings-admin .wg-settings-head p{margin:0;color:#646970}
                .woogit-settings-admin .wg-tabs{display:flex;gap:6px;border-bottom:1px solid #dcdcde;margin:22px 0 18px}
                .woogit-settings-admin .wg-tabs a{padding:10px 14px;text-decoration:none;border-bottom:3px solid transparent}
                .woogit-settings-admin .wg-tabs a.active{border-bottom-color:#2271b1;font-weight:600}
                .woogit-settings-admin .wg-card{background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:22px;margin-bottom:18px}
                .woogit-settings-admin .wg-card h2{margin-top:0}
                .woogit-settings-admin .wg-links{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
                .woogit-settings-admin .wg-link{display:block;padding:16px;border:1px solid #dcdcde;border-radius:10px;text-decoration:none}
                .woogit-settings-admin .wg-link strong{display:block;margin-bottom:5px}
                .woogit-settings-admin .wg-link span{color:#646970}
                .woogit-settings-admin .description{max-width:760px}
                @media(max-width:700px){.woogit-settings-admin .wg-links{grid-template-columns:1fr}.woogit-settings-admin .wg-settings-head{display:block}}
            </style>

            <div class="wg-settings-head">
                <div>
                    <h1>تنظیمات WooGit</h1>
                    <p>تنظیمات مدیریتی و سرویس‌های Backend از این بخش مرتب شده‌اند.</p>
                </div>
            </div>

            <?php if ($notice !== ''): ?>
                <div class="notice notice-success is-dismissible"><p><?php echo esc_html($notice); ?></p></div>
            <?php endif; ?>
            <?php if ($error !== ''): ?>
                <div class="notice notice-error"><p><?php echo esc_html($error); ?></p></div>
            <?php endif; ?>

            <nav class="wg-tabs" aria-label="WooGit settings">
                <a class="<?php echo $tab === 'general' ? 'active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=woogit-settings&tab=general')); ?>">تنظیمات عمومی</a>
                <a class="<?php echo $tab === 'bazaar' ? 'active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=woogit-settings&tab=bazaar')); ?>">کافه‌بازار</a>
            </nav>

            <?php if ($tab === 'general'): ?>
                <div class="wg-card">
                    <h2>تنظیمات مدیریتی موجود</h2>
                    <div class="wg-links">
                        <a class="wg-link" href="<?php echo esc_url(admin_url('admin.php?page=woogit-app-versions')); ?>">
                            <strong>App Versions</strong>
                            <span>حداقل نسخه، نسخه پیشنهادی، نسخه فعلی و نسخه‌های منسوخ.</span>
                        </a>
                        <a class="wg-link" href="<?php echo esc_url(admin_url('admin.php?page=woogit-announcements')); ?>">
                            <strong>Announcements</strong>
                            <span>اعلان‌های داخل اپ، زمان‌بندی و هدف‌گذاری Account/Site.</span>
                        </a>
                    </div>
                </div>
                <div class="wg-card">
                    <h2>مرزبندی تنظیمات</h2>
                    <p class="description">
                        اطلاعات Plan و SKU مربوط به فروش اشتراک داخل Product Data میلو نگهداری می‌شود.
                        تنظیمات امنیتی و اعتبارسنجی Backend در این بخش قرار می‌گیرند؛ اطلاعات محرمانه نیز فقط برای مدیران قابل مشاهده و تغییر است.
                    </p>
                </div>
            <?php else: ?>
                <div class="wg-card">
                    <h2>حساب Developer کافه‌بازار</h2>
                    <p class="description">
                        این اطلاعات فقط سمت Backend استفاده می‌شوند و نباید داخل APK یا repository قرار بگیرند.
                        اگر Constant متناظر در <code>wp-config.php</code> تعریف شده باشد، Constant بر مقدار ذخیره‌شده در این صفحه اولویت دارد.
                    </p>

                    <form method="post">
                        <?php wp_nonce_field('woogit_bazaar_save'); ?>
                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><label for="woogit-bazaar-package">Package Name</label></th>
                                <td>
                                    <input id="woogit-bazaar-package" name="package_name" type="text" class="regular-text code" value="<?php echo esc_attr($settings['package_name']); ?>" placeholder="com.samanramezani1377.woogit" autocomplete="off">
                                    <p class="description">باید دقیقاً با package نسخه Bazaar اپ یکسان باشد.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="woogit-bazaar-client-id">Client ID</label></th>
                                <td>
                                    <input id="woogit-bazaar-client-id" name="client_id" type="text" class="regular-text code" value="<?php echo esc_attr($settings['client_id']); ?>" autocomplete="off">
                                    <p class="description">شناسه Client حساب Developer کافه‌بازار.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="woogit-bazaar-client-secret">Client Secret</label></th>
                                <td>
                                    <input id="woogit-bazaar-client-secret" name="client_secret" type="password" class="regular-text code" value="" placeholder="<?php echo $settings['client_secret_set'] ? '••••••••••••••••' : ''; ?>" autocomplete="new-password">
                                    <p class="description"><?php echo $settings['client_secret_set'] ? 'Client Secret قبلاً تنظیم شده است؛ برای حفظ مقدار فعلی این فیلد را خالی بگذارید.' : 'Client Secret را وارد کنید.'; ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="woogit-bazaar-refresh-token">Refresh Token</label></th>
                                <td>
                                    <input id="woogit-bazaar-refresh-token" name="refresh_token" type="password" class="large-text code" value="" placeholder="<?php echo $settings['refresh_token_set'] ? '••••••••••••••••' : ''; ?>" autocomplete="new-password">
                                    <p class="description"><?php echo $settings['refresh_token_set'] ? 'Refresh Token قبلاً تنظیم شده است؛ برای حفظ مقدار فعلی این فیلد را خالی بگذارید.' : 'Refresh Token حساب Developer را وارد کنید.'; ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="woogit-bazaar-api-base">API Base URL</label></th>
                                <td>
                                    <input id="woogit-bazaar-api-base" name="api_base_url" type="url" class="large-text code" value="<?php echo esc_attr($settings['api_base_url']); ?>" placeholder="https://pardakht.cafebazaar.ir">
                                    <p class="description">در حالت عادی همان مقدار پیش‌فرض را نگه دارید.</p>
                                </td>
                            </tr>
                        </table>
                        <p class="submit">
                            <button type="submit" name="woogit_bazaar_save" value="1" class="button button-primary">ذخیره تنظیمات کافه‌بازار</button>
                        </p>
                    </form>
                </div>

                <div class="wg-card">
                    <h2>وضعیت پیکربندی</h2>
                    <ul>
                        <li>Package Name: <strong><?php echo $settings['package_name'] !== '' ? 'تنظیم شده' : 'تنظیم نشده'; ?></strong></li>
                        <li>Client ID: <strong><?php echo $settings['client_id'] !== '' ? 'تنظیم شده' : 'تنظیم نشده'; ?></strong></li>
                        <li>Client Secret: <strong><?php echo $settings['client_secret_set'] ? 'تنظیم شده' : 'تنظیم نشده'; ?></strong></li>
                        <li>Refresh Token: <strong><?php echo $settings['refresh_token_set'] ? 'تنظیم شده' : 'تنظیم نشده'; ?></strong></li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    public function bazaarSettings(): array
    {
        $stored = get_option(self::BAZAAR_OPTION, []);
        if (!is_array($stored)) $stored = [];

        $constants = [
            'package_name' => 'WOOGIT_BAZAAR_PACKAGE_NAME',
            'client_id' => 'WOOGIT_BAZAAR_CLIENT_ID',
            'client_secret' => 'WOOGIT_BAZAAR_CLIENT_SECRET',
            'refresh_token' => 'WOOGIT_BAZAAR_REFRESH_TOKEN',
            'api_base_url' => 'WOOGIT_BAZAAR_API_BASE_URL',
        ];

        foreach ($constants as $key => $constant) {
            if (defined($constant) && trim((string)constant($constant)) !== '') {
                $stored[$key] = trim((string)constant($constant));
            }
        }

        return [
            'package_name' => trim((string)($stored['package_name'] ?? '')),
            'client_id' => trim((string)($stored['client_id'] ?? '')),
            'client_secret_set' => trim((string)($stored['client_secret'] ?? '')) !== '',
            'refresh_token_set' => trim((string)($stored['refresh_token'] ?? '')) !== '',
            'api_base_url' => trim((string)($stored['api_base_url'] ?? 'https://pardakht.cafebazaar.ir')),
        ];
    }

    private function saveBazaar(array $input): array
    {
        $current = get_option(self::BAZAAR_OPTION, []);
        if (!is_array($current)) $current = [];

        $package = sanitize_text_field(wp_unslash((string)($input['package_name'] ?? '')));
        $clientId = sanitize_text_field(wp_unslash((string)($input['client_id'] ?? '')));
        $apiBase = esc_url_raw(trim((string)wp_unslash($input['api_base_url'] ?? '')));

        if ($package === '' || !preg_match('/^[A-Za-z0-9._-]+$/', $package)) {
            return [false, $current, 'Package Name معتبر نیست.'];
        }
        if ($clientId === '') {
            return [false, $current, 'Client ID را وارد کنید.'];
        }
        if ($apiBase === '' || !wp_http_validate_url($apiBase)) {
            return [false, $current, 'API Base URL معتبر نیست.'];
        }

        $current['package_name'] = $package;
        $current['client_id'] = $clientId;
        $current['api_base_url'] = rtrim($apiBase, '/');

        $secret = trim((string)wp_unslash($input['client_secret'] ?? ''));
        $refresh = trim((string)wp_unslash($input['refresh_token'] ?? ''));
        if ($secret !== '') $current['client_secret'] = $secret;
        if ($refresh !== '') $current['refresh_token'] = $refresh;

        if (empty($current['client_secret']) || empty($current['refresh_token'])) {
            return [false, $current, 'Client Secret و Refresh Token باید حداقل یک‌بار تنظیم شوند.'];
        }

        update_option(self::BAZAAR_OPTION, $current, false);
        return [true, $current, ''];
    }
}
