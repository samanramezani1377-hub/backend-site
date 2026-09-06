<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

/**
 * Generic account-requirement contract.
 * The numeric type is an opaque wire value owned by the app. Backend only
 * declares what is required; the app owns presentation and behavior.
 */
final class RequirementService
{
    public const TYPE_WEB_ACCOUNT_PASSWORD = 1;
    public const TYPE_CONTACT_EMAIL = 2;

    public function build(array $account): array
    {
        $requirements = [];
        $accountId = (int)($account['id'] ?? 0);

        if ($accountId > 0 && !(new AccountService())->hasWebPassword($accountId)) {
            $requirements[] = [
                'id' => 'web_account_password',
                'type' => self::TYPE_WEB_ACCOUNT_PASSWORD,
                'required' => true,
                'configured' => false,
            ];
        }

        $requirements[] = [
            'id' => 'contact_email',
            'type' => self::TYPE_CONTACT_EMAIL,
            'required' => false,
            'configured' => !empty($account['email']),
        ];

        return $requirements;
    }
}
