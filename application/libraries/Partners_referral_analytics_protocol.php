<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Pure protocol helpers shared by the Legacy analytics client and tests. */
class Partners_referral_analytics_protocol
{
    const EVENTS_PATH = '/v1/internal/referral-analytics/events';
    const ORDER_EVENTS_PATH = '/v1/internal/referral-analytics/order-events';
    const ATTRIBUTION_EXCHANGE_PATH = '/v1/internal/referral-analytics/attribution/exchange';

    public static function parseKeyring($rawJson)
    {
        $entries = json_decode((string) $rawJson, true);
        if (!is_array($entries) || count($entries) < 1) {
            throw new InvalidArgumentException('Invalid analytics keyring.');
        }

        $keys = array();
        $seen = array();
        $activeCount = 0;

        foreach ($entries as $entry) {
            if (!is_array($entry)
                || !isset($entry['keyId'], $entry['secret'], $entry['active'])
                || !is_string($entry['keyId'])
                || !preg_match('/^legacy-analytics-to-partners-[A-Za-z0-9._-]{1,80}$/', $entry['keyId'])
                || isset($seen[$entry['keyId']])
                || !is_string($entry['secret'])
                || !is_bool($entry['active'])) {
                throw new InvalidArgumentException('Invalid analytics keyring.');
            }

            $secret = self::decodeBase64Url($entry['secret']);
            if (strlen($secret) < 32) {
                throw new InvalidArgumentException('Invalid analytics keyring.');
            }

            if ($entry['active']) {
                $activeCount++;
            }

            $seen[$entry['keyId']] = true;
            $keys[] = array(
                'keyId' => $entry['keyId'],
                'secret' => $secret,
                'active' => $entry['active'],
            );
        }

        if ($activeCount !== 1) {
            throw new InvalidArgumentException('Invalid analytics keyring.');
        }

        return $keys;
    }

    public static function decodeBase64Url($value)
    {
        if (!is_string($value) || !preg_match('/^[A-Za-z0-9_-]+$/', $value)) {
            throw new InvalidArgumentException('Invalid base64url value.');
        }

        $padded = strtr($value, '-_', '+/');
        $remainder = strlen($padded) % 4;
        if ($remainder !== 0) {
            $padded .= str_repeat('=', 4 - $remainder);
        }

        $decoded = base64_decode($padded, true);
        if (!is_string($decoded) || rtrim(strtr(base64_encode($decoded), '+/', '-_'), '=') !== $value) {
            throw new InvalidArgumentException('Invalid base64url value.');
        }

        return $decoded;
    }

    public static function newNonce()
    {
        return rtrim(strtr(base64_encode(random_bytes(16)), '+/', '-_'), '=');
    }

    public static function canonicalString($keyId, $method, $pathAndQuery, $timestamp, $nonce, $rawBody)
    {
        return implode("\n", array(
            'VP-HMAC-SHA256-V1',
            (string) $keyId,
            strtoupper((string) $method),
            (string) $pathAndQuery,
            (string) $timestamp,
            (string) $nonce,
            hash('sha256', (string) $rawBody),
        ));
    }

    public static function sign($secret, $canonical)
    {
        return hash_hmac('sha256', (string) $canonical, (string) $secret);
    }

    public static function uuidV4()
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);

        return sprintf('%s-%s-%s-%s-%s',
            substr($hex, 0, 8), substr($hex, 8, 4), substr($hex, 12, 4),
            substr($hex, 16, 4), substr($hex, 20, 12)
        );
    }

    /** Stable UUID for events reconstructed by the retry/reconciliation worker. */
    public static function stableUuid($source)
    {
        $bytes = substr(hash('sha256', (string) $source, true), 0, 16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x50);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);

        return sprintf('%s-%s-%s-%s-%s',
            substr($hex, 0, 8), substr($hex, 8, 4), substr($hex, 12, 4),
            substr($hex, 16, 4), substr($hex, 20, 12)
        );
    }

    public static function addToCartPayload($eventId, $context, $productId, $quantity, $occurredAt)
    {
        if (!self::isUuid($eventId)
            || !self::isAttributionContext($context)
            || (int) $productId <= 0 || (int) $quantity < 1 || (int) $quantity > 99) {
            throw new InvalidArgumentException('Invalid add-to-cart event.');
        }

        return array(
            'eventId' => $eventId,
            'eventName' => 'add_to_cart',
            'attributionContext' => $context,
            'occurredAt' => (string) $occurredAt,
            'items' => array(array(
                'legacyProductId' => (string) (int) $productId,
                'quantity' => (int) $quantity,
            )),
        );
    }

    public static function isAttributionContext($context)
    {
        return is_string($context)
            && strlen($context) >= 60
            && strlen($context) <= 512
            && (bool) preg_match('/^v1\.[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+$/', $context);
    }

    public static function isoUtc($timestamp)
    {
        return gmdate('Y-m-d\TH:i:s', (int) $timestamp) . '.000Z';
    }

    public static function isoUtcTimestamp($value)
    {
        if (!is_string($value)
            || !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}Z$/', $value)) {
            return false;
        }

        $date = DateTimeImmutable::createFromFormat(
            '!Y-m-d\\TH:i:s.v\\Z',
            $value,
            new DateTimeZone('UTC')
        );
        $errors = DateTimeImmutable::getLastErrors();
        if (!$date
            || (is_array($errors) && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
            || $date->format('Y-m-d\\TH:i:s.v\\Z') !== $value) {
            return false;
        }

        return $date->getTimestamp();
    }

    public static function databaseDateToIsoUtc($value)
    {
        $timestamp = self::databaseDateTimestamp($value);
        if ($timestamp === false || $timestamp > time() + 30) {
            return self::isoUtc(time());
        }

        return self::isoUtc($timestamp);
    }

    /** Parse a MySQL DATETIME written in the Legacy site's Chisinau timezone. */
    public static function databaseDateTimestamp($value)
    {
        if (!is_string($value)
            || !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
            return false;
        }

        $date = DateTimeImmutable::createFromFormat(
            '!Y-m-d H:i:s',
            $value,
            new DateTimeZone('Europe/Chisinau')
        );
        $errors = DateTimeImmutable::getLastErrors();
        if (!$date
            || (is_array($errors) && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
            || $date->format('Y-m-d H:i:s') !== $value) {
            return false;
        }

        return $date->getTimestamp();
    }

    public static function orderEventTypeForStatus($status)
    {
        $status = strtolower(trim((string) $status));
        if ($status === 'confirmed') {
            return 'order.confirmed';
        }
        if ($status === 'canceled' || $status === 'cancelled') {
            return 'order.cancelled';
        }
        if (in_array($status, array('progress', 'paid', 'finished'), true)) {
            return 'order.updated';
        }
        return null;
    }

    /** Build the missing status events for a saved order snapshot without duplicates. */
    public static function reconciledOrderTransitions($status, $paySuccess, $paidAt, $updatedAt)
    {
        $status = strtolower(trim((string) $status));
        $hasPaidAt = is_string($paidAt) && trim($paidAt) !== '';
        $paymentMarked = (int) $paySuccess === 1 || $hasPaidAt;
        $events = array();

        if ((int) $paySuccess === 1 && $hasPaidAt
            && $status !== 'canceled' && $status !== 'cancelled') {
            $events[] = array('paid', 'order.updated', (string) $paidAt);
        }

        $eventType = self::orderEventTypeForStatus($status);
        if ($eventType !== null && $status !== 'paid'
            && !($status === 'progress' && $paymentMarked)) {
            $normalizedStatus = $status === 'canceled' ? 'cancelled' : $status;
            $events[] = array($normalizedStatus, $eventType, (string) $updatedAt);
        }

        return $events;
    }

    public static function isOrderStatusRegression($currentStatus, $nextStatus)
    {
        $rank = array(
            'new' => 10,
            'progress' => 20,
            'paid' => 30,
            'confirmed' => 40,
            'finished' => 50,
            'canceled' => 100,
            'cancelled' => 100,
        );
        $currentStatus = strtolower(trim((string) $currentStatus));
        $nextStatus = strtolower(trim((string) $nextStatus));

        if ($currentStatus === 'canceled' || $currentStatus === 'cancelled') {
            return $nextStatus !== 'canceled' && $nextStatus !== 'cancelled';
        }
        if ($nextStatus === 'canceled' || $nextStatus === 'cancelled') {
            return false;
        }

        return isset($rank[$currentStatus], $rank[$nextStatus])
            && $rank[$nextStatus] < $rank[$currentStatus];
    }

    public static function isUuid($value)
    {
        return is_string($value) && (bool) preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[1-8][a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/i', $value);
    }

    public static function analyticsConsentGranted($rawCookie)
    {
        $rawCookie = (string) $rawCookie;
        $decoded = json_decode(rawurldecode($rawCookie), true);
        if (!is_array($decoded)) {
            $decoded = json_decode($rawCookie, true);
        }

        return is_array($decoded)
            && isset($decoded['analytics'])
            && $decoded['analytics'] === true;
    }

    public static function payloadContainsPiiFields($value)
    {
        $blockedExact = array(
            'phone', 'email', 'name', 'surname', 'full_name', 'fullname', 'customer_name',
            'customername', 'first_name', 'firstname', 'last_name', 'lastname', 'address',
            'ip', 'ip_address', 'ipaddress', 'user_agent', 'useragent', 'payment_card',
            'paymentcard', 'card_number', 'cardnumber', 'payment_details', 'paymentdetails',
        );

        if (!is_array($value)) {
            return false;
        }

        foreach ($value as $key => $item) {
            if (is_string($key)) {
                $normalized = strtolower(str_replace(array('-', ' '), '_', $key));
                if (in_array($normalized, $blockedExact, true)
                    || strpos($normalized, 'email') !== false
                    || strpos($normalized, 'phone') !== false
                    || strpos($normalized, 'address') !== false
                    || strpos($normalized, 'user_agent') !== false
                    || strpos($normalized, 'useragent') !== false
                    || strpos($normalized, 'ip_address') !== false
                    || strpos($normalized, 'payment_card') !== false
                    || strpos($normalized, 'card_number') !== false) {
                    return true;
                }
            }
            if (self::payloadContainsPiiFields($item)) {
                return true;
            }
        }

        return false;
    }

    public static function retryDelaySeconds($attempt)
    {
        $attempt = max(1, min(20, (int) $attempt));
        return min(86400, 30 * (2 ** min(11, $attempt - 1)));
    }

    public static function successfulHttpStatus($status)
    {
        return (int) $status >= 200 && (int) $status < 300;
    }
}
