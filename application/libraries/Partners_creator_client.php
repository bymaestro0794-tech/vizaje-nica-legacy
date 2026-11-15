<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Signed server-to-server client for the M5.5 account and dashboard routes. */
class Partners_creator_client
{
    private $config;

    public function __construct($config)
    {
        $this->config = is_array($config) ? $config : array();
    }

    public function enabled()
    {
        return !empty($this->config['enabled']) && !empty($this->config['api_origin'])
            && !empty($this->config['redirect_uri']) && !empty($this->config['hmac_keys']);
    }

    public function initiate($legacyClientId, $challenge, $state)
    {
        return $this->post('/v1/internal/account-linking/initiate', array(
            'legacyClientId' => (string) (int) $legacyClientId,
            'redirectUri' => $this->config['redirect_uri'],
            'codeChallenge' => (string) $challenge,
            'state' => (string) $state,
        ));
    }

    public function exchange($code, $legacyClientId, $verifier)
    {
        return $this->post('/v1/internal/account-linking/exchange', array(
            'code' => (string) $code,
            'legacyClientId' => (string) (int) $legacyClientId,
            'redirectUri' => $this->config['redirect_uri'],
            'codeVerifier' => (string) $verifier,
        ));
    }

    public function status($grant, $legacyClientId)
    {
        return $this->post('/v1/internal/account-linking/partner-status', $this->grantPayload($grant, $legacyClientId));
    }

    public function analytics($grant, $legacyClientId)
    {
        return $this->post('/v1/internal/creator-dashboard/analytics', $this->grantPayload($grant, $legacyClientId));
    }

    public function listLinks($grant, $legacyClientId)
    {
        return $this->post('/v1/internal/creator-dashboard/referral-links/list', $this->grantPayload($grant, $legacyClientId, array('status' => 'all')));
    }

    public function createLink($grant, $legacyClientId, $idempotencyKey, $link)
    {
        return $this->post('/v1/internal/creator-dashboard/referral-links/create', $this->grantPayload($grant, $legacyClientId, array(
            'idempotencyKey' => $idempotencyKey, 'link' => $link,
        )));
    }

    public function revokeLink($grant, $legacyClientId, $linkId)
    {
        if (!is_string($linkId) || !preg_match('/^[A-Za-z0-9_-]{1,100}$/', $linkId)) return self::localFailure('INVALID_LINK_ID');
        return $this->post('/v1/internal/creator-dashboard/referral-links/' . rawurlencode($linkId) . '/revoke', $this->grantPayload($grant, $legacyClientId));
    }

    public function unlink($grant, $legacyClientId)
    {
        return $this->post('/v1/internal/creator-dashboard/unlink', $this->grantPayload($grant, $legacyClientId));
    }

    public function isAllowedAuthorizeUrl($url)
    {
        if (!is_string($url) || strlen($url) > 2048 || empty($this->config['partner_origin'])) return false;
        $parts = parse_url($url); $origin = parse_url($this->config['partner_origin']);
        if (!is_array($parts) || !is_array($origin) || empty($parts['scheme']) || empty($parts['host'])
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment'])
            || strtolower($parts['scheme']) !== 'https' || strtolower($parts['host']) !== strtolower($origin['host'])
            || (isset($parts['port']) ? (int) $parts['port'] : 443) !== (isset($origin['port']) ? (int) $origin['port'] : 443)
            || ($parts['path'] ?? '') !== '/link/legacy/authorize') return false;
        // Avoid parse_str's duplicate-key collapsing: the callback URL must have one exact ticket parameter.
        return isset($parts['query'])
            && preg_match('/^ticket=[A-Za-z0-9_-]{43}$/D', $parts['query']) === 1;
    }

    private function grantPayload($grant, $legacyClientId, $extra = array())
    {
        if (!is_string($grant) || !preg_match('/^[A-Za-z0-9_-]{43}$/', $grant)) return array();
        return array_merge(array('grantToken' => $grant, 'legacyClientId' => (string) (int) $legacyClientId), $extra);
    }

    private function post($path, $payload)
    {
        if (!$this->enabled() || !$payload || !$this->isAllowedPath($path)) return self::localFailure('CREATOR_INTEGRATION_UNAVAILABLE');
        if (!function_exists('curl_init')) return self::localFailure('CREATOR_INTEGRATION_UNAVAILABLE');
        $rawBody = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (!is_string($rawBody) || strlen($rawBody) > (int) ($this->config['max_request_bytes'] ?? 16384)) return self::localFailure('CREATOR_REQUEST_INVALID');
        $activeKey = null;
        foreach ($this->config['hmac_keys'] as $entry) if (!empty($entry['active'])) { $activeKey = $entry; break; }
        if (!$activeKey) return self::localFailure('CREATOR_INTEGRATION_UNAVAILABLE');

        $timestamp = (string) time();
        try { $nonce = Partners_creator_protocol::randomToken(16); }
        catch (Throwable $error) { return self::localFailure('CREATOR_INTEGRATION_UNAVAILABLE'); }
        $canonical = Partners_creator_protocol::canonicalString($activeKey['keyId'], 'POST', $path, $timestamp, $nonce, $rawBody);
        $signature = Partners_creator_protocol::signature($activeKey['secret'], $canonical);
        $responseBody = '';
        $limit = (int) ($this->config['max_response_bytes'] ?? 65536);
        $ch = curl_init(rtrim($this->config['api_origin'], '/') . $path);
        if ($ch === false) return self::localFailure('CREATOR_INTEGRATION_UNAVAILABLE');
        curl_setopt_array($ch, array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $rawBody,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json; charset=utf-8', 'Accept: application/json',
                'X-VP-Key-Id: ' . $activeKey['keyId'], 'X-VP-Timestamp: ' . $timestamp,
                'X-VP-Nonce: ' . $nonce, 'X-VP-Signature: ' . $signature,
            ),
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_MAXREDIRS => 0,
            CURLOPT_CONNECTTIMEOUT_MS => (int) ($this->config['connect_timeout_ms'] ?? 800),
            CURLOPT_TIMEOUT_MS => (int) ($this->config['timeout_ms'] ?? 3500),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_WRITEFUNCTION => function ($curl, $chunk) use (&$responseBody, $limit) {
                if (strlen($responseBody) + strlen($chunk) > $limit) return 0;
                $responseBody .= $chunk;
                return strlen($chunk);
            },
        ));
        $ok = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($ok === false || $status < 100 || $responseBody === '') return self::localFailure('PARTNERS_UNAVAILABLE', $status);
        $decoded = json_decode($responseBody, true);
        if (!is_array($decoded)) return self::localFailure('PARTNERS_RESPONSE_INVALID', $status);
        return array('ok' => $status >= 200 && $status < 300, 'status' => $status, 'body' => $decoded, 'error' => self::errorCode($decoded));
    }

    private function isAllowedPath($path)
    {
        if (!is_string($path) || strlen($path) > 300 || strpos($path, '/v1/internal/') !== 0 || strpos($path, '?') !== false || strpos($path, '..') !== false) return false;
        return in_array($path, array(
            '/v1/internal/account-linking/initiate', '/v1/internal/account-linking/exchange',
            '/v1/internal/account-linking/partner-status', '/v1/internal/creator-dashboard/analytics',
            '/v1/internal/creator-dashboard/referral-links/list', '/v1/internal/creator-dashboard/referral-links/create',
            '/v1/internal/creator-dashboard/unlink',
        ), true) || preg_match('#^/v1/internal/creator-dashboard/referral-links/[A-Za-z0-9_-]{1,100}/revoke$#', $path) === 1;
    }

    private static function errorCode($body)
    {
        $code = $body['error']['code'] ?? ($body['code'] ?? '');
        return is_string($code) && preg_match('/^[A-Z0-9_]{1,80}$/', $code) ? $code : 'PARTNERS_ERROR';
    }

    private static function localFailure($code, $status = 0)
    {
        return array('ok' => false, 'status' => (int) $status, 'body' => array(), 'error' => (string) $code);
    }
}
