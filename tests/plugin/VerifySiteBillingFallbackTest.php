<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Regression contract test for the verifySite -> billing fallback flow.
 *
 * This test is intentionally source-level: the plugin test suite must fail if
 * a valid store verification is changed to reject customers merely because
 * they have no active commerce entitlement.
 */
final class VerifySiteBillingFallbackTest extends TestCase
{
    public function testNoEntitlementKeepsVerifySuccessfulAndReturnsBillingContext(): void
    {
        $source = $this->loadRestControllerSource();

        $this->assertStringContainsString(
            "if(!$this->entitlements->grantTrial($accountId,$siteId))",
            $source,
            'verifySite must still establish the account/site entitlement context before deciding access.'
        );

        $this->assertStringContainsString(
            '$accessEnabled=$this->entitlements->isAllowed($accountId,$siteId,\'commerce\');',
            $source,
            'Verify must distinguish entitlement from identity/store verification.'
        );

        $this->assertStringContainsString(
            '$billingToken=$this->sessions->issueBilling($accountId,$siteId);',
            $source,
            'A billing session must be issued for customers without operational access.'
        );

        $this->assertStringContainsString(
            '$token=$accessEnabled\n            ? $this->sessions->issueOperational($accountId,$siteId,(int)$expires)\n            : $billingToken;',
            $source,
            'When commerce access is disabled, the returned session must fall back to the billing session.'
        );

        $this->assertStringContainsString(
            '$scope=$accessEnabled\n    ? SessionService::SCOPE_OPERATIONAL\n    : SessionService::SCOPE_BILLING;',
            $source,
            'A no-entitlement verification must return billing scope rather than operational scope.'
        );

        $this->assertStringContainsString(
            "'access_enabled'=>$accessEnabled",
            $source,
            'Verify response must expose that operational access is disabled.'
        );

        $this->assertStringContainsString(
            "'billing_required'=>!$accessEnabled",
            $source,
            'Verify response must explicitly tell the App that billing is required.'
        );

        $this->assertStringContainsString(
            "return new \\WP_REST_Response($body,200);",
            $source,
            'No-entitlement verification must remain an HTTP 200 billing-authenticated success.'
        );
    }

    public function testBillingSessionIsTheSessionReturnedWhenOperationalAccessIsDisabled(): void
    {
        $source = $this->loadRestControllerSource();

        $billingFallback = <<<'PHP'
$token=$accessEnabled
    ? $this->sessions->issueOperational($accountId,$siteId,(int)$expires)
    : $billingToken;
PHP;

        $this->assertStringContainsString(
            $billingFallback,
            $source,
            'The primary session returned by verifySite must be the billing session when entitlement is unavailable.'
        );

        $this->assertStringContainsString(
            "'billing_session'=>$billingToken",
            $source,
            'The dedicated billing session must also be returned for subsequent billing calls.'
        );
    }

    private function loadRestControllerSource(): string
    {
        $path = dirname(__DIR__, 2) . '/plugin/woogit-backend/src/RestController.php';

        $this->assertFileExists($path);

        $source = file_get_contents($path);
        $this->assertIsString($source);

        return $source;
    }
}
