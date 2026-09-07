<?php
declare(strict_types=1);
function check(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }
final class FakeMutation
{
    public int $upstreamCalls = 0;
    public string $state = 'pending';
    public function send(bool $timeoutAfterSend): void { $this->upstreamCalls++; $this->state = $timeoutAfterSend ? 'unknown' : 'succeeded'; }
}
$mutation = new FakeMutation();
$mutation->send(true);
check($mutation->upstreamCalls === 1, 'initial mutation must be sent exactly once');
check($mutation->state === 'unknown', 'timeout after send must become unknown');
check($mutation->state === 'unknown', 'same-key retry must observe unknown state');
check($mutation->upstreamCalls === 1, 'unknown mutation must not be forwarded a second time');
$successful = new FakeMutation();
$successful->send(false);
check($successful->state === 'succeeded', 'successful upstream response must become succeeded');
check($successful->upstreamCalls === 1, 'successful mutation must have one upstream call');
$failed = new FakeMutation();
$failed->upstreamCalls++;
$failed->state = 'failed';
check($failed->state === 'failed', 'failed upstream response must remain failed');
check($failed->upstreamCalls === 1, 'failed mutation must not be executed twice by state replay');
echo "PASS: timeout-after-send, unknown, success, failure, and no-second-forward lifecycle\n";
