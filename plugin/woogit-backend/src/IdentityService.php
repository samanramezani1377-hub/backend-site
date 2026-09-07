<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class IdentityService
{
    public function getUserId(int $accountId): int
    {
        global $wpdb;
        $table = $wpdb->prefix . 'woogit_accounts';
        return (int) $wpdb->get_var($wpdb->prepare("SELECT wp_user_id FROM {$table} WHERE id=%d LIMIT 1", $accountId));
    }

    public function getUser(int $accountId): ?\WP_User
    {
        $id = $this->getUserId($accountId);
        if ($id <= 0) return null;
        $user = get_userdata($id);
        return $user instanceof \WP_User ? $user : null;
    }

    /** The linked WP user is the single Web/WooCommerce identity for this Account. */
    public function isPasswordConfigured(int $accountId): bool
    {
        return $this->getUser($accountId) instanceof \WP_User;
    }

    /** Create the single WP/WooCommerce customer identity after successful site verification. */
    public function createCustomer(): int
    {
        $login = 'woogit_' . strtolower(wp_generate_password(24, false, false));
        $password = wp_generate_password(48, true, true);
        $userId = wp_insert_user(wp_slash([
            'user_login' => $login,
            'user_pass' => $password,
            'user_email' => '',
            'role' => 'customer',
            'display_name' => $login,
        ]));
        if (is_wp_error($userId) || !$userId) return 0;

        $user = get_userdata((int)$userId);
        if ($user instanceof \WP_User && function_exists('wc_create_new_customer')) {
            $user->set_role('customer');
        }
        return (int)$userId;
    }

    public function verifyPassword(int $accountId, string $password): bool
    {
        $user = $this->getUser($accountId);
        return $user instanceof \WP_User && wp_check_password($password, $user->user_pass, $user->ID);
    }

    public function setPassword(int $accountId, string $password): bool
    {
        if (strlen($password) < 12 || strlen($password) > 256) return false;
        $user = $this->getUser($accountId);
        if (!$user) return false;
        wp_set_password($password, $user->ID);
        return true;
    }

    public function updateEmail(int $accountId, string $email): bool
    {
        $email = sanitize_email($email);
        if ($email === '' || !is_email($email)) return false;
        $user = $this->getUser($accountId);
        if (!$user) return false;
        $result = wp_update_user(['ID' => (int)$user->ID, 'user_email' => $email]);
        return !is_wp_error($result);
    }

    public function link(int $accountId, int $userId): bool
    {
        global $wpdb;
        $table = $wpdb->prefix . 'woogit_accounts';
        $exists = (int)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE wp_user_id=%d AND id<>%d LIMIT 1", $userId, $accountId));
        if ($exists > 0) return false;
        return false !== $wpdb->update(
            $table,
            ['wp_user_id' => $userId, 'updated_at' => current_time('mysql', true)],
            ['id' => $accountId],
            ['%d', '%s'],
            ['%d']
        );
    }
}
