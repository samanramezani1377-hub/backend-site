<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class AccountsAdmin
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
            56
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
                a.created_at AS account_created_at,
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
            <p>Connected WooGit accounts and the WordPress identity and site associated with each account.</p>

            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th scope="col">Account</th>
                        <th scope="col">WordPress User</th>
                        <th scope="col">Contact Email</th>
                        <th scope="col">Connected Site</th>
                        <th scope="col">Account Status</th>
                        <th scope="col">Site Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$rows): ?>
                    <tr><td colspan="6">No WooGit accounts found.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td>
                                <strong>#<?php echo esc_html((string) $row['account_id']); ?></strong>
                                <?php if (!empty($row['account_created_at'])): ?>
                                    <br><small>Created: <?php echo esc_html((string) $row['account_created_at']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($row['wp_user_id'])): ?>
                                    <strong>ID <?php echo esc_html((string) $row['wp_user_id']); ?></strong><br>
                                    <code><?php echo esc_html((string) ($row['user_login'] ?? '')); ?></code>
                                    <?php if (!empty($row['display_name'])): ?>
                                        <br><?php echo esc_html((string) $row['display_name']); ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td><?php echo $row['contact_email'] !== null && $row['contact_email'] !== '' ? esc_html((string) $row['contact_email']) : '—'; ?></td>
                            <td>
                                <?php if (!empty($row['canonical_url'])): ?>
                                    <a href="<?php echo esc_url((string) $row['canonical_url']); ?>" target="_blank" rel="noopener noreferrer">
                                        <?php echo esc_html((string) $row['canonical_url']); ?>
                                    </a>
                                    <?php if (!empty($row['host'])): ?><br><small><?php echo esc_html((string) $row['host']); ?></small><?php endif; ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html((string) ($row['account_status'] ?? '—')); ?></td>
                            <td><?php echo esc_html((string) ($row['site_status'] ?? '—')); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
