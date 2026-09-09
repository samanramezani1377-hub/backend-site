<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class AccountAdmin
{
    public function register(): void
    {
        add_menu_page(
            'WooGit Accounts',
            'WooGit Accounts',
            'manage_options',
            'woogit-accounts',
            [$this, 'render'],
            'dashicons-admin-users',
            58
        );
    }

    public function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to view WooGit accounts.', 'woogit-backend'));
        }

        global $wpdb;
        $accounts = $wpdb->prefix . 'woogit_accounts';
        $sites = $wpdb->prefix . 'woogit_sites';
        $users = $wpdb->users;

        $rows = $wpdb->get_results(
            "SELECT
                a.id AS account_id,
                a.email AS contact_email,
                a.wp_user_id,
                a.status AS account_status,
                a.created_at,
                u.user_login,
                u.display_name,
                s.id AS site_id,
                s.canonical_url,
                s.host,
                s.status AS site_status
             FROM {$accounts} a
             LEFT JOIN {$users} u ON u.ID = a.wp_user_id
             LEFT JOIN {$sites} s ON s.account_id = a.id
             ORDER BY a.id DESC",
            ARRAY_A
        );
        ?>
        <div class="wrap">
            <h1>WooGit Accounts</h1>
            <p>Connected WooGit accounts, their WordPress identities, and linked sites.</p>
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th>Account</th>
                        <th>WordPress User</th>
                        <th>Contact Email</th>
                        <th>Connected Site</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$rows): ?>
                    <tr><td colspan="6">No WooGit accounts found.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><strong>#<?php echo esc_html((string) $row['account_id']); ?></strong></td>
                            <td>
                                <?php if (!empty($row['wp_user_id'])): ?>
                                    <strong><?php echo esc_html((string) ($row['display_name'] ?: $row['user_login'])); ?></strong>
                                    <br><code><?php echo esc_html((string) $row['user_login']); ?></code>
                                    <br><span class="description">WP ID: <?php echo esc_html((string) $row['wp_user_id']); ?></span>
                                <?php else: ?>
                                    <span class="description">No WordPress user</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo ($row['contact_email'] !== null && $row['contact_email'] !== '') ? esc_html((string) $row['contact_email']) : '&mdash;'; ?></td>
                            <td>
                                <?php if (!empty($row['canonical_url'])): ?>
                                    <a href="<?php echo esc_url((string) $row['canonical_url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html((string) $row['canonical_url']); ?></a>
                                    <?php if (!empty($row['host'])): ?><br><span class="description"><?php echo esc_html((string) $row['host']); ?></span><?php endif; ?>
                                <?php else: ?>
                                    <span class="description">No connected site</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo esc_html((string) $row['account_status']); ?>
                                <?php if (!empty($row['site_status'])): ?><br><span class="description">Site: <?php echo esc_html((string) $row['site_status']); ?></span><?php endif; ?>
                            </td>
                            <td><?php echo esc_html((string) $row['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
