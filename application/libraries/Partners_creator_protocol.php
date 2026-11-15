<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Isolated M5.5 server authentication and PKCE helpers. */
class Partners_creator_protocol
{
    public static function parseKeyring($rawJson)
    {
        $entries = json_decode((string) $rawJson, true);
        if (!is_array($entries) || !$entries) {
            throw new InvalidArgumentException('Invalid creator keyring.');
        }

        $keys = array();
        $seen = array();
        $active = 0;
        foreach ($entries as $entry) {
            if (!is_array($entry)
                || !isset($entry['keyId'], $entry['secret'], $entry['active'])
                || !is_string($entry['keyId'])
                || !preg_match('/^legacy-to-partners-[A-Za-z0-9._-]{1,80}$/', $entry['keyId'])
                || isset($seen[$entry['keyId']])
                || !is_string($entry['secret'])
                || !is_bool($entry['active'])) {
                throw new InvalidArgumentException('Invalid creator keyring.');
            }
            $secret = self::decodeBase64Url($entry['secret']);
            if (strlen($secret) < 32) {
                throw new InvalidArgumentException('Invalid creator keyring.');
            }
            if ($entry['active']) $active++;
            $seen[$entry['keyId']] = true;
            $keys[] = array('keyId' => $entry['keyId'], 'secret' => $secret, 'active' => $entry['active']);
        }
        if ($active !== 1) throw new InvalidArgumentException('Invalid creator keyring.');
        return $keys;
    }

    public static function parseEncryptionKeys($entries)
    {
        if (!is_array($entries) || !$entries) throw new InvalidArgumentException('Invalid creator encryption keys.');
        $keys = array();
        $active = 0;
        foreach ($entries as $entry) {
            if (!is_array($entry) || !isset($entry['keyId'], $entry['secret'], $entry['active'])
                || !is_string($entry['keyId']) || !preg_match('/^[A-Za-z0-9._-]{1,80}$/', $entry['keyId'])
                || !is_string($entry['secret']) || !is_bool($entry['active'])) {
                throw new InvalidArgumentException('Invalid creator encryption keys.');
            }
            $key = self::decodeBase64Url($entry['secret']);
            if (strlen($key) !== 32) throw new InvalidArgumentException('Invalid creator encryption keys.');
            if (isset($keys[$entry['keyId']])) throw new InvalidArgumentException('Invalid creator encryption keys.');
            if ($entry['active']) $active++;
            $keys[$entry['keyId']] = array('key' => $key, 'active' => $entry['active']);
        }
        if ($active !== 1) throw new InvalidArgumentException('Invalid creator encryption keys.');
        return $keys;
    }

    public static function decodeBase64Url($value)
    {
        if (!is_string($value) || !preg_match('/^[A-Za-z0-9_-]+$/', $value)) {
            throw new InvalidArgumentException('Invalid base64url value.');
        }
        $padded = strtr($value, '-_', '+/');
        if (strlen($padded) % 4) $padded .= str_repeat('=', 4 - strlen($padded) % 4);
        $decoded = base64_decode($padded, true);
        if (!is_string($decoded) || rtrim(strtr(base64_encode($decoded), '+/', '-_'), '=') !== $value) {
            throw new InvalidArgumentException('Invalid base64url value.');
        }
        return $decoded;
    }

    public static function encodeBase64Url($value)
    {
        return rtrim(strtr(base64_encode((string) $value), '+/', '-_'), '=');
    }

    public static function randomToken($bytes = 32)
    {
        return self::encodeBase64Url(random_bytes((int) $bytes));
    }

    public static function pkceChallenge($verifier)
    {
        if (!is_string($verifier) || !preg_match('/^[A-Za-z0-9._~-]{43,128}$/', $verifier)) {
            throw new InvalidArgumentException('Invalid PKCE verifier.');
        }
        return self::encodeBase64Url(hash('sha256', $verifier, true));
    }

    public static function canonicalString($keyId, $method, $pathAndQuery, $timestamp, $nonce, $rawBody)
    {
        return implode("\n", array(
            'VP-HMAC-SHA256-V1', (string) $keyId, strtoupper((string) $method),
            (string) $pathAndQuery, (string) $timestamp, (string) $nonce, hash('sha256', (string) $rawBody),
        ));
    }

    public static function signature($secret, $canonical)
    {
        return hash_hmac('sha256', (string) $canonical, (string) $secret);
    }

    public static function newUuid()
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);
        return sprintf('%s-%s-%s-%s-%s', substr($hex, 0, 8), substr($hex, 8, 4), substr($hex, 12, 4), substr($hex, 16, 4), substr($hex, 20, 12));
    }
}
