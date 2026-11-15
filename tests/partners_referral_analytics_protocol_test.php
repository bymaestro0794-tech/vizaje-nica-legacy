<?php
define('BASEPATH', __DIR__ . DIRECTORY_SEPARATOR);
require_once __DIR__ . '/../application/libraries/Partners_referral_analytics_protocol.php';

function analytics_assert($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function analytics_b64url($bytes)
{
    return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
}

$secretBytes = random_bytes(32);
$secret = analytics_b64url($secretBytes);
$keyId = 'legacy-analytics-to-partners-dev-test';
$validKeyring = json_encode(array(array(
    'keyId' => $keyId,
    'secret' => $secret,
    'active' => true,
)));
$passed = 0;

analytics_assert(count(Partners_referral_analytics_protocol::parseKeyring($validKeyring)) === 1, 'valid keyring'); $passed++;
try { Partners_referral_analytics_protocol::parseKeyring(''); throw new RuntimeException('empty keyring accepted'); } catch (InvalidArgumentException $e) { $passed++; }
try { Partners_referral_analytics_protocol::parseKeyring(json_encode(array(
    array('keyId' => $keyId, 'secret' => $secret, 'active' => true),
    array('keyId' => $keyId . '-old', 'secret' => $secret, 'active' => true),
))); throw new RuntimeException('multiple active keys accepted'); } catch (InvalidArgumentException $e) { $passed++; }
try { Partners_referral_analytics_protocol::parseKeyring(json_encode(array(array(
    'keyId' => $keyId, 'secret' => analytics_b64url(random_bytes(16)), 'active' => true,
)))); throw new RuntimeException('short secret accepted'); } catch (InvalidArgumentException $e) { $passed++; }

$nonce = Partners_referral_analytics_protocol::newNonce();
analytics_assert(strlen($nonce) === 22, 'nonce length'); $passed++;
analytics_assert((bool) preg_match('/^[A-Za-z0-9_-]{22}$/', $nonce), 'nonce alphabet'); $passed++;

$body = '{"eventId":"e17b8d2e-bcdf-4f72-9ef0-7706ebfb43f1"}';
$canonical = Partners_referral_analytics_protocol::canonicalString($keyId, 'post', '/v1/internal/referral-analytics/events?x=1', '1794500000', $nonce, $body);
analytics_assert(strpos($canonical, "VP-HMAC-SHA256-V1\n" . $keyId . "\nPOST\n") === 0, 'canonical prefix and method normalization'); $passed++;
analytics_assert(substr($canonical, -64) === hash('sha256', $body), 'canonical raw body hash'); $passed++;
$signature = Partners_referral_analytics_protocol::sign($secretBytes, $canonical);
analytics_assert((bool) preg_match('/^[a-f0-9]{64}$/', $signature), 'lowercase HMAC hex'); $passed++;

$eventId = 'e17b8d2e-bcdf-4f72-9ef0-7706ebfb43f1';
$context = 'v1.' . str_repeat('a', 40) . '.' . str_repeat('b', 43);
$payload = Partners_referral_analytics_protocol::addToCartPayload($eventId, $context, 11048, 2, '2026-11-12T12:00:00.000Z');
analytics_assert(array_keys($payload) === array('eventId', 'eventName', 'attributionContext', 'occurredAt', 'items'), 'add_to_cart top-level contract keys'); $passed++;
analytics_assert($payload['eventName'] === 'add_to_cart' && $payload['items'][0]['legacyProductId'] === '11048', 'internal product ID mapping'); $passed++;
analytics_assert($payload['items'][0]['quantity'] === 2, 'accepted quantity'); $passed++;
analytics_assert(Partners_referral_analytics_protocol::isUuid($eventId), 'UUID validation'); $passed++;
analytics_assert(Partners_referral_analytics_protocol::stableUuid('vizaje-nica:order:42:created') === Partners_referral_analytics_protocol::stableUuid('vizaje-nica:order:42:created'), 'stable event ID'); $passed++;
analytics_assert(!Partners_referral_analytics_protocol::payloadContainsPiiFields($payload), 'safe payload contains no PII fields'); $passed++;
analytics_assert(Partners_referral_analytics_protocol::payloadContainsPiiFields(array('customerEmail' => 'private')), 'PII key detection'); $passed++;
analytics_assert(Partners_referral_analytics_protocol::retryDelaySeconds(1) === 30, 'initial retry delay'); $passed++;
analytics_assert(Partners_referral_analytics_protocol::successfulHttpStatus(202) && !Partners_referral_analytics_protocol::successfulHttpStatus(503), 'HTTP acceptance classification'); $passed++;
analytics_assert(Partners_referral_analytics_protocol::isAttributionContext($context), 'valid opaque attribution context'); $passed++;
analytics_assert(!Partners_referral_analytics_protocol::isAttributionContext('v1.payload.signature'), 'reject short attribution context'); $passed++;
analytics_assert(Partners_referral_analytics_protocol::isoUtc(1794500000) === gmdate('Y-m-d\\TH:i:s', 1794500000) . '.000Z', 'UTC timestamp contract'); $passed++;
analytics_assert(Partners_referral_analytics_protocol::isoUtcTimestamp('2026-11-18T12:00:00.000Z') === strtotime('2026-11-18 12:00:00 UTC'), 'strict ISO UTC expiry parsing'); $passed++;
analytics_assert(Partners_referral_analytics_protocol::isoUtcTimestamp('2026-02-30T12:00:00.000Z') === false, 'reject normalized invalid ISO date'); $passed++;
analytics_assert(Partners_referral_analytics_protocol::isoUtcTimestamp('2026-11-18T12:00:00Z') === false, 'require contract milliseconds'); $passed++;
$legacyDate = new DateTimeImmutable('2026-11-12 15:00:00', new DateTimeZone('Europe/Chisinau'));
analytics_assert(Partners_referral_analytics_protocol::databaseDateTimestamp('2026-11-12 15:00:00') === $legacyDate->getTimestamp(), 'Legacy database date uses Chisinau timezone'); $passed++;
analytics_assert(Partners_referral_analytics_protocol::databaseDateTimestamp('2026-02-30 15:00:00') === false, 'reject invalid MySQL datetime'); $passed++;
analytics_assert(Partners_referral_analytics_protocol::analyticsConsentGranted('{"analytics":true}'), 'honor explicit analytics consent'); $passed++;
analytics_assert(!Partners_referral_analytics_protocol::analyticsConsentGranted('{"analytics":"true"}'), 'do not coerce analytics consent'); $passed++;
analytics_assert(Partners_referral_analytics_protocol::orderEventTypeForStatus('canceled') === 'order.cancelled', 'legacy cancellation mapping'); $passed++;
analytics_assert(Partners_referral_analytics_protocol::orderEventTypeForStatus('finished') === 'order.updated', 'finished is a status update, not payment'); $passed++;
analytics_assert(Partners_referral_analytics_protocol::isOrderStatusRegression('paid', 'progress'), 'suppress progress after paid'); $passed++;
analytics_assert(!Partners_referral_analytics_protocol::isOrderStatusRegression('finished', 'canceled'), 'allow cancellation from any state'); $passed++;
analytics_assert(Partners_referral_analytics_protocol::isOrderStatusRegression('canceled', 'finished'), 'cancellation is terminal'); $passed++;
$paidTransitions = Partners_referral_analytics_protocol::reconciledOrderTransitions('paid', 1, '2026-11-12 14:00:00', '2026-11-12 14:05:00');
analytics_assert(count($paidTransitions) === 1 && $paidTransitions[0][0] === 'paid', 'reconcile paid state exactly once'); $passed++;
$confirmedTransitions = Partners_referral_analytics_protocol::reconciledOrderTransitions('confirmed', 1, '2026-11-12 14:00:00', '2026-11-12 14:05:00');
analytics_assert(count($confirmedTransitions) === 2 && $confirmedTransitions[0][0] === 'paid' && $confirmedTransitions[1][0] === 'confirmed', 'reconcile payment before confirmed status'); $passed++;
$unverifiedPaidTransitions = Partners_referral_analytics_protocol::reconciledOrderTransitions('paid', 0, null, '2026-11-12 14:05:00');
analytics_assert(count($unverifiedPaidTransitions) === 0, 'do not infer paid status without payment marker'); $passed++;
$progressAfterPayment = Partners_referral_analytics_protocol::reconciledOrderTransitions('progress', 1, null, '2026-11-12 14:05:00');
analytics_assert(count($progressAfterPayment) === 0, 'suppress progress after payment marker'); $passed++;
try { Partners_referral_analytics_protocol::addToCartPayload($eventId, $context, 11048, 100, '2026-11-12T12:00:00.000Z'); throw new RuntimeException('quantity over 99 accepted'); } catch (InvalidArgumentException $e) { $passed++; }

echo 'Referral analytics protocol checks passed: ' . $passed . PHP_EOL;
