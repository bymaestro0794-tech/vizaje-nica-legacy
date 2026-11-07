<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Internal_referrals extends CI_Controller
{
    private $settings = array();
    private $requestId = '';

    public function __construct()
    {
        parent::__construct();
        $this->load->config('referral');
        $this->settings = (array) $this->config->item('referral');
        try {
            $this->requestId = bin2hex(random_bytes(12));
        } catch (Throwable $exception) {
            $this->requestId = substr(hash('sha256', uniqid('', true)), 0, 24);
        }
    }

    public function resolve_target()
    {
        $this->setResponseHeaders();

        if (strtoupper((string) $this->input->method()) !== 'POST') {
            return $this->respondError(405, 'METHOD_NOT_ALLOWED', 'Only POST is supported.', false);
        }

        if (ENVIRONMENT === 'production' && !$this->requestIsHttps()) {
            return $this->respondError(503, 'LEGACY_TEMPORARILY_UNAVAILABLE', 'The secure endpoint is unavailable.', true);
        }

        $requestUri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
        $requestParts = parse_url($requestUri);
        if (!is_array($requestParts)
            || isset($requestParts['query'])
            || isset($requestParts['fragment'])
            || !isset($requestParts['path'])
            || $requestParts['path'] !== '/internal/v1/referrals/resolve-target') {
            return $this->respondError(400, 'INVALID_JSON', 'The request target is invalid.', false);
        }

        $headers = $this->requestHeaders();
        if ($headers === false) {
            return $this->respondError(401, 'SERVICE_AUTH_INVALID', 'Service authentication failed.', false);
        }

        $contentType = $this->headerValue($headers, 'content-type');
        if (!is_string($contentType)
            || !preg_match('/^application\/json(?:\s*;\s*charset\s*=\s*"?utf-8"?)?\s*$/i', trim($contentType))) {
            return $this->respondError(400, 'INVALID_JSON', 'The request must use UTF-8 JSON.', false);
        }

        $keyId = $this->headerValue($headers, 'x-vp-key-id');
        $timestamp = $this->headerValue($headers, 'x-vp-timestamp');
        $nonce = $this->headerValue($headers, 'x-vp-nonce');
        $signature = $this->headerValue($headers, 'x-vp-signature');
        if (!is_string($keyId) || !preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]{0,99}$/', $keyId)
            || !is_string($nonce) || !$this->isCanonicalNonce($nonce)
            || !is_string($signature) || !preg_match('/^[0-9a-f]{64}$/', $signature)) {
            return $this->respondError(401, 'SERVICE_AUTH_INVALID', 'Service authentication failed.', false);
        }
        if (!is_string($timestamp) || !preg_match('/^(0|[1-9][0-9]{0,11})$/', $timestamp)) {
            return $this->respondError(401, 'SERVICE_TIMESTAMP_INVALID', 'The service timestamp is invalid.', false);
        }

        $maxBodyBytes = isset($this->settings['max_body_bytes']) ? (int) $this->settings['max_body_bytes'] : 16384;
        $contentLength = isset($_SERVER['CONTENT_LENGTH']) ? (int) $_SERVER['CONTENT_LENGTH'] : null;
        if ($contentLength !== null && $contentLength > $maxBodyBytes) {
            return $this->respondError(400, 'INVALID_JSON', 'The request body is too large.', false);
        }

        $bodyStream = fopen('php://input', 'rb');
        $rawBody = $bodyStream ? stream_get_contents($bodyStream, $maxBodyBytes + 1) : false;
        if (is_resource($bodyStream)) {
            fclose($bodyStream);
        }
        if (!is_string($rawBody) || strlen($rawBody) > $maxBodyBytes) {
            return $this->respondError(400, 'INVALID_JSON', 'The request body is invalid or too large.', false);
        }

        $keyResult = $this->getHmacKey($keyId);
        if ($keyResult['status'] === 'unknown') {
            return $this->respondError(401, 'SERVICE_AUTH_INVALID', 'Service authentication failed.', false);
        }
        if ($keyResult['status'] !== 'ok') {
            return $this->respondError(503, 'LEGACY_TEMPORARILY_UNAVAILABLE', 'Service authentication is not configured.', true);
        }
        $key = $keyResult['key'];

        $timestampValue = (int) $timestamp;
        $clockSkew = isset($this->settings['max_clock_skew_seconds'])
            ? (int) $this->settings['max_clock_skew_seconds']
            : 60;
        if (abs(time() - $timestampValue) > $clockSkew) {
            return $this->respondError(401, 'SERVICE_TIMESTAMP_INVALID', 'The service timestamp is outside the allowed window.', false);
        }

        $canonical = implode("\n", array(
            'VP-HMAC-SHA256-V1',
            $keyId,
            'POST',
            '/internal/v1/referrals/resolve-target',
            $timestamp,
            $nonce,
            hash('sha256', $rawBody),
        ));
        $expectedSignature = hash_hmac('sha256', $canonical, $key['secret']);
        if (!hash_equals($expectedSignature, $signature)) {
            return $this->respondError(401, 'SERVICE_AUTH_INVALID', 'Service authentication failed.', false);
        }

        if ($key['principal'] !== 'vizaje-partners') {
            return $this->respondError(401, 'SERVICE_AUTH_INVALID', 'Service authentication failed.', false);
        }

        $nonceResult = $this->consumeNonceAndRateLimit($keyId, $nonce, $key['principal']);
        if ($nonceResult['status'] === 'replay') {
            return $this->respondError(409, 'AUTH_NONCE_REPLAY', 'This authentication nonce has already been used.', false);
        }
        if ($nonceResult['status'] === 'unavailable') {
            return $this->respondError(503, 'LEGACY_TEMPORARILY_UNAVAILABLE', 'The resolver is temporarily unavailable.', true);
        }
        if ($nonceResult['status'] === 'limited') {
            $retryAfter = max(1, (int) $nonceResult['retry_after']);
            $this->output->set_header('Retry-After: ' . $retryAfter);
            return $this->respondError(429, 'RATE_LIMITED', 'The resolver request limit has been reached.', true);
        }

        $decoded = json_decode($rawBody, false, 16, JSON_BIGINT_AS_STRING);
        if (json_last_error() !== JSON_ERROR_NONE
            || !is_object($decoded)
            || $this->hasDuplicateJsonKeys($rawBody)) {
            return $this->respondError(400, 'INVALID_JSON', 'The request JSON is invalid.', false);
        }

        $keys = array_keys(get_object_vars($decoded));
        sort($keys);
        if ($keys !== array('expectedType', 'requestedLocale', 'target')
            || !isset($decoded->target, $decoded->expectedType, $decoded->requestedLocale)
            || !is_string($decoded->target)
            || !is_string($decoded->expectedType)
            || !is_string($decoded->requestedLocale)
            || $decoded->target === ''
            || $this->stringLength($decoded->target) > 2048
            || !in_array($decoded->expectedType, array('homepage', 'product', 'category', 'brand'), true)
            || !in_array($decoded->requestedLocale, array('ru', 'ro'), true)) {
            return $this->respondError(400, 'INVALID_JSON', 'The request JSON does not match the required schema.', false);
        }

        try {
            $this->load->library('referral_catalog_resolver');
            $response = $this->referral_catalog_resolver->resolve($decoded);
        } catch (Referral_catalog_resolver_error $exception) {
            return $this->respondError(
                $exception->httpStatus,
                $exception->errorCode,
                $exception->safeMessage,
                $exception->retryable
            );
        } catch (Throwable $exception) {
            // Database and runtime diagnostics are deliberately kept out of API responses and logs.
            return $this->respondError(503, 'LEGACY_TEMPORARILY_UNAVAILABLE', 'The resolver is temporarily unavailable.', true);
        }

        return $this->output
            ->set_status_header(200)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function requestHeaders()
    {
        $source = function_exists('getallheaders') ? getallheaders() : array();
        if (!is_array($source)) {
            $source = array();
        }

        $headers = array();
        foreach ($source as $name => $value) {
            $normalized = strtolower(trim((string) $name));
            if (isset($headers[$normalized]) || !is_string($value)) {
                return false;
            }
            $headers[$normalized] = trim($value);
        }

        $serverMap = array(
            'content-type' => 'CONTENT_TYPE',
            'x-vp-key-id' => 'HTTP_X_VP_KEY_ID',
            'x-vp-timestamp' => 'HTTP_X_VP_TIMESTAMP',
            'x-vp-nonce' => 'HTTP_X_VP_NONCE',
            'x-vp-signature' => 'HTTP_X_VP_SIGNATURE',
        );
        foreach ($serverMap as $normalized => $serverName) {
            if (!array_key_exists($normalized, $headers) && isset($_SERVER[$serverName])) {
                $value = (string) $_SERVER[$serverName];
                if (strpos($value, ',') !== false) {
                    return false;
                }
                $headers[$normalized] = trim($value);
            }
        }

        return $headers;
    }

    private function headerValue($headers, $name)
    {
        return array_key_exists($name, $headers) ? $headers[$name] : null;
    }

    private function getHmacKey($keyId)
    {
        $keyring = isset($this->settings['hmac_keyring']) && is_array($this->settings['hmac_keyring'])
            ? $this->settings['hmac_keyring']
            : array();
        if (count($keyring) === 0) {
            return array('status' => 'unconfigured');
        }
        if (!isset($keyring[$keyId])) {
            return array('status' => 'unknown');
        }
        if (!is_array($keyring[$keyId])) {
            return array('status' => 'misconfigured');
        }

        $principal = isset($keyring[$keyId]['principal']) ? (string) $keyring[$keyId]['principal'] : '';
        $encodedSecret = isset($keyring[$keyId]['secretBase64'])
            ? (string) $keyring[$keyId]['secretBase64']
            : '';
        $secret = $encodedSecret !== '' ? base64_decode($encodedSecret, true) : false;
        if (!in_array($principal, array('vizaje-partners', 'vizaje-legacy'), true)
            || !is_string($secret)
            || strlen($secret) < 32) {
            return array('status' => 'misconfigured');
        }

        return array('status' => 'ok', 'key' => array('principal' => $principal, 'secret' => $secret));
    }

    private function isCanonicalNonce($nonce)
    {
        if (!preg_match('/^[A-Za-z0-9_-]{22}$/', $nonce)) {
            return false;
        }
        $base64 = strtr($nonce, '-_', '+/') . '==';
        $bytes = base64_decode($base64, true);
        if (!is_string($bytes) || strlen($bytes) !== 16) {
            return false;
        }
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=') === $nonce;
    }

    private function consumeNonceAndRateLimit($keyId, $nonce, $principal)
    {
        $db = $this->db;
        if (empty($db)) {
            return array('status' => 'unavailable');
        }
        $oldDebug = isset($db->db_debug) ? $db->db_debug : null;
        $db->db_debug = false;
        $started = false;
        try {
            if (empty($db->conn_id) && !$db->initialize()) {
                return array('status' => 'unavailable');
            }
            if (!$db->trans_begin()) {
                return array('status' => 'unavailable');
            }
            $started = true;
            $now = gmdate('Y-m-d H:i:s');
            $retention = isset($this->settings['nonce_retention_seconds'])
                ? max(600, (int) $this->settings['nonce_retention_seconds'])
                : 900;
            $expiresAt = gmdate('Y-m-d H:i:s', time() + $retention);

            $db->query(
                'DELETE FROM referral_service_nonces WHERE expires_at < ? ORDER BY expires_at ASC LIMIT 100',
                array($now)
            );
            $db->query(
                'DELETE FROM referral_service_rate_limits WHERE window_start < ? ORDER BY window_start ASC LIMIT 100',
                array(gmdate('Y-m-d H:i:s', time() - 120))
            );

            $inserted = $db->query(
                'INSERT INTO referral_service_nonces (key_id, nonce, received_at, expires_at) VALUES (?, ?, ?, ?)',
                array($keyId, $nonce, $now, $expiresAt)
            );
            if (!$inserted) {
                $error = $db->error();
                $db->trans_rollback();
                $started = false;
                return isset($error['code']) && (int) $error['code'] === 1062
                    ? array('status' => 'replay')
                    : array('status' => 'unavailable');
            }

            $windowStart = gmdate('Y-m-d H:i:00');
            $db->query(
                'INSERT INTO referral_service_rate_limits (principal, window_start, request_count) VALUES (?, ?, 1) '
                . 'ON DUPLICATE KEY UPDATE request_count = request_count + 1',
                array($principal, $windowStart)
            );
            $rateRow = $db->query(
                'SELECT request_count FROM referral_service_rate_limits WHERE principal = ? AND window_start = ? LIMIT 1',
                array($principal, $windowStart)
            );
            if (!$rateRow || !$db->trans_status()) {
                $db->trans_rollback();
                $started = false;
                return array('status' => 'unavailable');
            }

            $countRow = $rateRow->row();
            if (!$db->trans_commit()) {
                $started = false;
                return array('status' => 'unavailable');
            }
            $started = false;

            $limit = isset($this->settings['resolver_requests_per_minute'])
                ? (int) $this->settings['resolver_requests_per_minute']
                : 60;
            if (!$countRow || (int) $countRow->request_count > $limit) {
                return array('status' => 'limited', 'retry_after' => 60 - (int) gmdate('s'));
            }
            return array('status' => 'ok');
        } catch (Throwable $exception) {
            if ($started) {
                $db->trans_rollback();
            }
            return array('status' => 'unavailable');
        } finally {
            if ($oldDebug !== null) {
                $db->db_debug = $oldDebug;
            }
        }
    }

    private function hasDuplicateJsonKeys($json)
    {
        $offset = 0;
        if (!$this->scanJsonValue($json, $offset, 0)) {
            return true;
        }
        $this->skipJsonWhitespace($json, $offset);
        return $offset !== strlen($json);
    }

    private function scanJsonValue($json, &$offset, $depth)
    {
        if ($depth > 16) {
            return false;
        }
        $this->skipJsonWhitespace($json, $offset);
        $length = strlen($json);
        if ($offset >= $length) {
            return false;
        }

        $char = $json[$offset];
        if ($char === '{') {
            $offset++;
            $this->skipJsonWhitespace($json, $offset);
            if (isset($json[$offset]) && $json[$offset] === '}') {
                $offset++;
                return true;
            }
            $seen = array();
            while ($offset < $length) {
                $key = $this->scanJsonString($json, $offset);
                if ($key === false || array_key_exists($key, $seen)) {
                    return false;
                }
                $seen[$key] = true;
                $this->skipJsonWhitespace($json, $offset);
                if (!isset($json[$offset]) || $json[$offset] !== ':') {
                    return false;
                }
                $offset++;
                if (!$this->scanJsonValue($json, $offset, $depth + 1)) {
                    return false;
                }
                $this->skipJsonWhitespace($json, $offset);
                if (!isset($json[$offset])) {
                    return false;
                }
                if ($json[$offset] === '}') {
                    $offset++;
                    return true;
                }
                if ($json[$offset] !== ',') {
                    return false;
                }
                $offset++;
                $this->skipJsonWhitespace($json, $offset);
            }
            return false;
        }

        if ($char === '[') {
            $offset++;
            $this->skipJsonWhitespace($json, $offset);
            if (isset($json[$offset]) && $json[$offset] === ']') {
                $offset++;
                return true;
            }
            while ($offset < $length) {
                if (!$this->scanJsonValue($json, $offset, $depth + 1)) {
                    return false;
                }
                $this->skipJsonWhitespace($json, $offset);
                if (!isset($json[$offset])) {
                    return false;
                }
                if ($json[$offset] === ']') {
                    $offset++;
                    return true;
                }
                if ($json[$offset] !== ',') {
                    return false;
                }
                $offset++;
                $this->skipJsonWhitespace($json, $offset);
            }
            return false;
        }

        if ($char === '"') {
            return $this->scanJsonString($json, $offset) !== false;
        }

        if (preg_match('/\G(?:true|false|null|-?(?:0|[1-9][0-9]*)(?:\.[0-9]+)?(?:[eE][+-]?[0-9]+)?)/', $json, $match, 0, $offset)) {
            $offset += strlen($match[0]);
            return true;
        }
        return false;
    }

    private function scanJsonString($json, &$offset)
    {
        $length = strlen($json);
        if (!isset($json[$offset]) || $json[$offset] !== '"') {
            return false;
        }
        $start = $offset;
        $offset++;
        while ($offset < $length) {
            if ($json[$offset] === '\\') {
                $offset += 2;
                continue;
            }
            if ($json[$offset] === '"') {
                $offset++;
                $decoded = json_decode(substr($json, $start, $offset - $start));
                return is_string($decoded) ? $decoded : false;
            }
            $offset++;
        }
        return false;
    }

    private function skipJsonWhitespace($json, &$offset)
    {
        $length = strlen($json);
        while ($offset < $length && strpos(" \t\r\n", $json[$offset]) !== false) {
            $offset++;
        }
    }

    private function stringLength($value)
    {
        return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
    }

    private function requestIsHttps()
    {
        $https = strtolower((string) (isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : ''));
        return in_array($https, array('on', '1', 'https'), true)
            || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);
    }

    private function setResponseHeaders()
    {
        $this->output
            ->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0')
            ->set_header('Pragma: no-cache')
            ->set_header('X-Content-Type-Options: nosniff');
    }

    private function respondError($status, $code, $message, $retryable)
    {
        $body = array(
            'error' => array(
                'code' => (string) $code,
                'message' => (string) $message,
                'retryable' => (bool) $retryable,
                'requestId' => $this->requestId,
            ),
        );
        return $this->output
            ->set_status_header((int) $status)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
