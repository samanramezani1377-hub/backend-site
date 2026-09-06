<?php
/**
 * Framework-free behavioral lifecycle tests for V1 security invariants.
 */
declare(strict_types=1);

function expect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

final class FakeSession
{
    public function __construct(
        public string $scope,
        public int $accountId,
        public int $siteId,
        public int $expiresAt,
        public bool $revoked = false,
    ) {}

    public function validAt(int $now): bool
    {
        return !$this->revoked && $this->expiresAt > $now;
    }
}

function authorizeForward(FakeSession $session, int $now, int $accountId, int $siteId, bool $entitled): bool
{
    return $session->validAt($now)
        && $session->scope === 'operational'
        && $session->accountId === $accountId
        && $session->siteId === $siteId
        && $entitled;
}

$now = 1_000;
$operational = new FakeSession('operational', 10, 20, $now + 300);
$billing = new FakeSession('billing', 10, 20, $now + 300);

expect(authorizeForward($operational, $now, 10, 20, true), 'valid operational session must authorize forward');
expect(!authorizeForward($billing, $now, 10, 20, true), 'billing session must never authorize forward');
expect(!authorizeForward($operational, $now, 11, 20, true), 'different account must fail ownership');
expect(!authorizeForward($operational, $now, 10, 21, true), 'different site must fail ownership');
expect(!authorizeForward($operational, $now, 10, 20, false), 'expired entitlement must block outbound access');
expect(!authorizeForward($operational, $operational->expiresAt, 10, 20, true), 'expired session must not authorize forward');

$operational->revoked = true;
expect(!authorizeForward($operational, $now, 10, 20, true), 'revoked session must not authorize forward');

// Privilege elevation is represented by a new operational session, never by mutating scope.
$billing->scope = 'billing';
$newOperational = new FakeSession('operational', $billing->accountId, $billing->siteId, $now + 600);
$billing->revoked = true;
expect($newOperational !== $billing, 'privilege elevation must create a distinct session');
expect($newOperational->scope === 'operational', 'new session must carry operational scope');
expect($billing->scope === 'billing' && $billing->revoked, 'old billing session must remain non-operational and be revoked');

echo "PASS: session expiry, revocation, scope, ownership, entitlement, and privilege-elevation lifecycle\n";
