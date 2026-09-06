<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class VersionAdmin
{
    private const OPTION = 'woogit_backend_version_policy';
    private const VERSION_PATTERN = '/^\d+(?:\.\d+){0,3}(?:[-+][0-9A-Za-z.-]+)?$/';

    public function register(): void
    {
        add_options_page(
            'WooGit App Versions',
            'WooGit App Versions',
            'manage_options',
            'woogit-app-versions',
            [$this, 'render']
        );
    }

    public function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to manage WooGit app versions.', 'woogit-backend'));
        }

        $policy = get_option(self::OPTION, []);
        $policy = is_array($policy) ? array_merge($this->defaults(), $policy) : $this->defaults();
        $message = '';
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['woogit_version_policy_save'])) {
            check_admin_referer('woogit_version_policy_save');
            [$valid, $normalized, $error] = $this->validateAndNormalize($_POST);
            if ($valid) {
                if (update_option(self::OPTION, $normalized, false)) {
                    $policy = $normalized;
                    $message = 'Version policy saved.';
                } else {
                    $current = get_option(self::OPTION, []);
                    $current = is_array($current) ? array_merge($this->defaults(), $current) : $this->defaults();
                    if ($current === $normalized) {
                        $policy = $normalized;
                        $message = 'Version policy is already up to date.';
                    } else {
                        $error = 'The version policy could not be saved.';
                    }
                }
            }
        }

        $deprecated = is_array($policy['deprecated_versions']) ? $policy['deprecated_versions'] : [];
        ?>
        <div class="wrap">
            <h1>WooGit App Versions</h1>
            <p>Control which WooGit Android client versions are accepted by the Backend API.</p>
            <?php if ($message !== ''): ?>
                <div class="notice notice-success is-dismissible"><p><?php echo esc_html($message); ?></p></div>
            <?php endif; ?>
            <?php if ($error !== ''): ?>
                <div class="notice notice-error"><p><?php echo esc_html($error); ?></p></div>
            <?php endif; ?>
            <form method="post">
                <?php wp_nonce_field('woogit_version_policy_save'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="woogit-latest-version">Latest version</label></th>
                        <td><input id="woogit-latest-version" name="latest_version" type="text" class="regular-text" value="<?php echo esc_attr((string) $policy['latest_version']); ?>" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="woogit-recommended-version">Recommended version</label></th>
                        <td><input id="woogit-recommended-version" name="recommended_version" type="text" class="regular-text" value="<?php echo esc_attr((string) $policy['recommended_version']); ?>" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="woogit-minimum-version">Minimum supported version</label></th>
                        <td>
                            <input id="woogit-minimum-version" name="minimum_supported_version" type="text" class="regular-text" value="<?php echo esc_attr((string) $policy['minimum_supported_version']); ?>" required>
                            <p class="description">Versions below this value are rejected with APP_VERSION_DEPRECATED.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="woogit-deprecated-versions">Deprecated versions</label></th>
                        <td>
                            <textarea id="woogit-deprecated-versions" name="deprecated_versions" rows="8" class="large-text code" placeholder="1.0.0&#10;1.1.0"><?php echo esc_textarea(implode("\n", array_map('strval', $deprecated))); ?></textarea>
                            <p class="description">One exact version per line. A listed version is rejected even when it is above the minimum supported version.</p>
                        </td>
                    </tr>
                </table>
                <p class="submit"><button type="submit" name="woogit_version_policy_save" class="button button-primary">Save version policy</button></p>
            </form>
            <hr>
            <p><strong>Current Backend plugin:</strong> <?php echo esc_html(WOOGIT_BACKEND_VERSION); ?></p>
            <p>Changing this policy takes effect immediately for API requests that send <code>X-WooGit-App-Version</code>.</p>
        </div>
        <?php
    }

    private function defaults(): array
    {
        return [
            'latest_version' => WOOGIT_BACKEND_VERSION,
            'recommended_version' => WOOGIT_BACKEND_VERSION,
            'minimum_supported_version' => '0.0.0',
            'deprecated_versions' => [],
        ];
    }

    private function validateAndNormalize(array $input): array
    {
        $latest = trim((string) ($input['latest_version'] ?? ''));
        $recommended = trim((string) ($input['recommended_version'] ?? ''));
        $minimum = trim((string) ($input['minimum_supported_version'] ?? ''));
        $rawDeprecated = (string) ($input['deprecated_versions'] ?? '');
        $deprecated = [];

        foreach (preg_split('/\R/', $rawDeprecated) ?: [] as $version) {
            $version = trim($version);
            if ($version === '') {
                continue;
            }
            if (!preg_match(self::VERSION_PATTERN, $version)) {
                return [false, [], 'Invalid deprecated version: ' . $version];
            }
            $deprecated[] = $version;
        }

        foreach (['latest version' => $latest, 'recommended version' => $recommended, 'minimum supported version' => $minimum] as $label => $version) {
            if (!preg_match(self::VERSION_PATTERN, $version)) {
                return [false, [], 'Invalid ' . $label . ': ' . $version];
            }
        }

        if (version_compare($minimum, $latest, '>')) {
            return [false, [], 'Minimum supported version cannot be greater than latest version.'];
        }
        if (version_compare($recommended, $latest, '>') || version_compare($recommended, $minimum, '<')) {
            return [false, [], 'Recommended version must be between minimum supported and latest version.'];
        }

        $deprecated = array_values(array_unique($deprecated));
        if (in_array($latest, $deprecated, true) || in_array($recommended, $deprecated, true)) {
            return [false, [], 'Latest and recommended versions cannot be deprecated.'];
        }

        return [true, [
            'latest_version' => $latest,
            'recommended_version' => $recommended,
            'minimum_supported_version' => $minimum,
            'deprecated_versions' => $deprecated,
        ], ''];
    }
}
