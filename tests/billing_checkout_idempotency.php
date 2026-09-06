<?php
/**
 * Behavioral contract test for billing checkout idempotency.
 *
 * This test is intentionally framework-free. It exercises the pure state-machine
 * contract used by Billing checkout: the same key/fingerprint may create one
 * order only; a conflicting fingerprint must be rejected; an in-flight request
 * must remain pending; an indeterminate upstream result must remain unknown.
 */

declare(strict_types=1);

final class FakeCheckoutStore
{
    /** @var array<string,array{fingerprint:string,state:string,order_id:int|null}> */
    private array $records = [];

    public function claim(string $key, string $fingerprint): array
    {
        if (!isset($this->records[$key])) {
            $this->records[$key] = [
                'fingerprint' => $fingerprint,
                'state' => 'pending',
                'order_id' => null,
            ];
            return ['status' => 'claimed'];
        }

        $record = $this->records[$key];
        if (!hash_equals($record['fingerprint'], $fingerprint)) {
            return ['status' => 'conflict'];
        }
        if ($record['state'] === 'pending') {
            return ['status' => 'pending'];
        }
        return ['status' => $record['state'], 'order_id' => $record['order_id']];
    }

    public function complete(string $key, int $orderId): void
    {
        $this->records[$key]['state'] = 'succeeded';
        $this->records[$key]['order_id'] = $orderId;
    }

    public function unknown(string $key): void
    {
        $this->records[$key]['state'] = 'unknown';
    }

    public function state(string $key): string
    {
        return $this->records[$key]['state'];
    }
}

function assert_same_value(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message . "\nExpected: " . var_export($expected, true) . "\nActual: " . var_export($actual, true));
    }
}

$store = new FakeCheckoutStore();
$key = 'checkout-operation-001';
$fingerprint = hash('sha256', 'POST|billing/checkout|account=10|site=20|plan=pro|variation=42');

assert_same_value('claimed', $store->claim($key, $fingerprint)['status'], 'first checkout claim must win');
assert_same_value('pending', $store->claim($key, $fingerprint)['status'], 'concurrent/retried checkout must not create a second order');
assert_same_value('conflict', $store->claim($key, hash('sha256', 'different-request'))['status'], 'same key with different request must conflict');

$store->complete($key, 1234);
$result = $store->claim($key, $fingerprint);
assert_same_value('succeeded', $result['status'], 'completed checkout must replay authoritative state');
assert_same_value(1234, $result['order_id'], 'completed checkout must retain the original order');

$unknownKey = 'checkout-operation-unknown';
assert_same_value('claimed', $store->claim($unknownKey, $fingerprint)['status'], 'unknown scenario must start pending');
$store->unknown($unknownKey);
assert_same_value('unknown', $store->state($unknownKey), 'timeout-after-send must remain unknown');
assert_same_value('unknown', $store->claim($unknownKey, $fingerprint)['status'], 'unknown checkout must never silently create another order');

echo "PASS: billing checkout idempotency state-machine behavior\n";
