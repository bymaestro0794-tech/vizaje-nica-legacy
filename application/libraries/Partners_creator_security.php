<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Pure validation shared by the Legacy callback and its regression tests. */
class Partners_creator_security
{
    public static function isAuthenticatedLegacySession($session, $client)
    {
        if (!is_array($session) || !is_object($client) || empty($client->id)
            || empty($client->email) || !isset($client->password)
            || empty($session['user_id']) || (int) $session['user_id'] !== (int) $client->id
            || empty($session['user_login']) || !is_string($session['user_login'])
            || !hash_equals((string) $client->email, $session['user_login'])
            || empty($session['usr_key']) || !is_string($session['usr_key'])) return false;

        return hash_equals(hash('sha512', (string) $client->email . (string) $client->password), $session['usr_key']);
    }

    /** Retained for callers that specifically need the legacy creator entitlement. */
    public static function isAuthenticatedCreatorSession($session, $client)
    {
        return is_object($client) && !empty($client->is_creator)
            && self::isAuthenticatedLegacySession($session, $client);
    }

    public static function isValidCsrfToken($expected, $actual, $sessionClientId, $clientId)
    {
        return is_string($expected) && preg_match('/^[a-f0-9]{64}$/', $expected) === 1
            && is_string($actual) && $actual !== '' && hash_equals($expected, $actual)
            && (string) (int) $sessionClientId === (string) (int) $clientId;
    }

    public static function isValidCallback($flow, $clientId, $sessionId, $state, $code, $now = null)
    {
        $now = $now === null ? time() : (int) $now;
        return is_array($flow) && (int) ($flow['client_id'] ?? 0) === (int) $clientId
            && (string) ($flow['session_id'] ?? '') !== '' && is_string($sessionId)
            && hash_equals((string) $flow['session_id'], $sessionId)
            && (int) ($flow['started_at'] ?? 0) >= $now - 300
            && (int) ($flow['started_at'] ?? 0) <= $now + 10
            && is_string($flow['state'] ?? null) && is_string($state) && $state !== ''
            && hash_equals($flow['state'], $state)
            && preg_match('/^[A-Za-z0-9_-]{20,200}$/', (string) $code) === 1
            && preg_match('/^[A-Za-z0-9_-]{43}$/', $state) === 1
            && is_string($flow['verifier'] ?? null)
            && preg_match('/^[A-Za-z0-9._~-]{43,128}$/', $flow['verifier']) === 1;
    }

    public static function isValidExchangeResponse($body)
    {
        if (!is_array($body) || !isset($body['partnerId'], $body['creatorId'], $body['type'], $body['status'], $body['grantToken'], $body['scopes'])
            || !Partners_creator_model::isUuid($body['partnerId']) || !preg_match('/^CRE-[0-9]{6}$/', (string) $body['creatorId'])
            || $body['type'] !== 'creator' || !in_array($body['status'], array('active', 'suspended', 'banned'), true)
            || !is_string($body['grantToken']) || !preg_match('/^[A-Za-z0-9_-]{43}$/', $body['grantToken'])
            || !is_array($body['scopes']) || !$body['scopes']) return false;
        foreach ($body['scopes'] as $scope) if (!is_string($scope) || !preg_match('/^[a-z:]{3,80}$/', $scope)) return false;
        return in_array('account:status', $body['scopes'], true);
    }

    public static function isEligibleExchangeResponse($body)
    {
        return self::isValidExchangeResponse($body) && $body['status'] === 'active';
    }

    public static function isValidStatusResponse($body, $row)
    {
        return is_array($body) && is_array($row) && isset($body['partnerId'], $body['creatorId'], $body['type'], $body['status'])
            && Partners_creator_model::isUuid($body['partnerId'])
            && hash_equals(strtolower((string) ($row['partner_id'] ?? '')), strtolower((string) $body['partnerId']))
            && hash_equals((string) ($row['creator_id'] ?? ''), (string) $body['creatorId'])
            && $body['type'] === 'creator'
            && in_array($body['status'], array('active', 'suspended', 'banned'), true);
    }

    public static function isActiveStatusResponse($body, $row)
    {
        return self::isValidStatusResponse($body, $row) && $body['status'] === 'active';
    }

    public static function isAllowedTarget($url)
    {
        if (!is_string($url) || strlen($url) > 2048) return false;
        $parts = parse_url($url);
        if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host']) || empty($parts['path'])
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment'])) return false;
        $scheme = strtolower($parts['scheme']);
        $host = strtolower($parts['host']);
        $allowedHost = in_array($host, array('vizaje-nica.com', 'www.vizaje-nica.com', 'dev.vizaje-nica.com', 'vizaje-nica.local'), true);
        return $allowedHost && ($scheme === 'https' || ($scheme === 'http' && $host === 'vizaje-nica.local'))
            && preg_match('#^/(ru|ro)(/|$)#', $parts['path']) === 1;
    }
}
