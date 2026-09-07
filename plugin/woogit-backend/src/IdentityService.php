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
        if ($id <= 0) {
            return null;
        }
        $user = get_userdata($id);
        return $user instanceof \WP_User ? $user : null;
    }

    /** A linked WP user is the single Web/WooCommerce identity for this Account. */
    public function isPasswordConfigured(int $accountId): bool
    {
        return $this->getUser($accountId) instanceof \WP_User;
    }

    /**
     * Creates or links the single central WordPress/WooCommerce identity during
     * explicit web-credential setup. Account creation and site verification never
     * call this method.
     */
    public function linkOrCreate(int $accountId, string $email, string $password, string $currentPassword = ''): array
    {
        $email = sanitize_email($email);
        if ($accountId <= 0 || $email === '' || !is_email($email)) {
            return ['ok' => false, 'code' => 'invalid_contact_email'];
        }
        if (strlen($password) < 12 || strlen($password) > 256) {
            return ['ok' => false, 'code' => 'invalid_web_password'];
        }

        // setup is first-time only; an existing linked identity is handled by
        // the authenticated password-change endpoint instead.
        if ($this->getUser($accountId)) {
            return ['ok' => false, 'code' => 'web_credentials_already_configured'];
        }

        $owner = email_exists($email);
        if ($owner) {
            $user = get_userdata((int) $owner);
            if (!$user instanceof \WP_User || !$this->isCustomerIdentity($user)) {
                return ['ok' => false, 'code' => 'account_identity_unavailable'];
            }
            if ($currentPassword === '' || !wp_check_password($currentPassword, $user->user_pass, $user->ID)) {
                return ['ok' => false, 'code' => 'identity_verification_required'];
            }
            if (!$this->link($accountId, (int) $user->ID)) {
                return ['ok' => false, 'code' => 'identity_link_failed'];
            }
            wp_set_password($password, (int) $user->ID);
            return ['ok' => true, 'user_id' => (int) $user->ID, 'created' => false];
        }

        $login = 'woogit_' . strtolower(wp_generate_password(20, false, false));
        $userId = function_exists('wc_create_new_customer')
            ? wc_create_new_customer($email, $login, $password)
            : wp_insert_user(wp_slash([
                'user_login' => $login,
                'user_pass' => $password,
                'user_email' => $email,
                'role' => 'subscriber',
                'display_name' => $email,
            ]));

        if (is_wp_error($userId) || !$userId) {
            return ['ok' => false, 'code' => 'identity_creation_failed'];
        }

        if (!$this->link($accountId, (int) $userId)) {
            wp_delete_user((int) $userId);
            return ['ok' => false, 'code' => 'identity_link_failed'];
        }

        return ['ok' => true, 'user_id' => (int) $userId, 'created' => true];
    }

    public function verifyPassword(int $accountId, string $password): bool
    {
        $user = $this->getUser($accountId);
        return $user instanceof \WP_User && wp_check_password($password, $user->user_pass, $user->ID);
    }

    public function setPassword(int $accountId, string $password): bool
    {
        if (strlen($password) < 12 || strlen($password) > 256) {
            return false;
        }
        $user = $this->getUser($accountId);
        if (!$user) {
            return false;
        }
        wp_set_password($password, $user->ID);
        return true;
    }

    public function updateEmail(int $accountId, string $email): bool
    {
        $email = sanitize_email($email);
        if ($email === '' || !is_email($email)) {
            return false;
        }
        $user = $this->getUser($accountId);
        if (!$user) {
            return false;
        }
        $owner = email_exists($email);
        if ($owner && (int) $owner !== (int) $user->ID) {
            return false;
        }
        $result = wp_update_user(['ID' => (int) $user->ID, 'user_email' => $email]);
        return !is_wp_error($result);
    }

    private function link(int $accountId, int $userId): bool
    {
        global $wpdb;
        $table = $wpdb->prefix . 'woogit_accounts';
        $exists = (int) $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE wp_user_id=%d AND id<>%d LIMIT 1", $userId, $accountId));
        if ($exists > 0) {
            return false;
        }
        return false !== $wpdb->update(
            $table,
            ['wp_user_id' => $userId, 'updated_at' => current_time('mysql', true)],
            ['id' => $accountId],
            ['%d', '%s'],
            ['%d']
        );
    }

    private function isCustomerIdentity(\WP_User $user): bool
    {
        foreach ((array) $user->roles as $role) {
            if (in_array($role, ['administrator', 'editor', 'author', 'shop_manager'], true)) {
                return false;
            }
        }
        return true;
    }
}
