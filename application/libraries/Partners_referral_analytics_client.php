<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Partners_referral_analytics_client
{
    private $settings = array();
    private $keys = array();
    private $configurationValid = false;

    public function __construct($params = array())
    {
        $CI =& get_instance();
        $CI->load->library('Partners_referral_analytics_protocol');
        if (!isset($CI->config)) {
            $CI->load->config('partners_referral_analytics');
        }
        $this->settings = (array) $CI->config->item('partners_referral_analytics');

        try {
            $this->keys = Partners_referral_analytics_protocol::parseKeyring(
                isset($this->settings['hmac_keys_json']) ? $this->settings['hmac_keys_json'] : ''
            );
            $this->configurationValid = !empty($this->settings['api_origin']);
        } catch (Throwable $exception) {
            // Keep configuration failures private; never log key IDs or secret material.
            $this->keys = array();
            $this->configurationValid = false;
        }
    }

    public function isEnabled()
    {
        return !empty($this->settings['enabled']);
    }

    public function isReady()
    {
        return $this->configurationValid && count($this->keys) > 0;
    }

    public function postJson($path, array $payload)
    {
        $rawBody = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($rawBody)) {
            return array('ok' => false, 'code' => 'INVALID_REQUEST_BODY', 'http_status' => 0, 'response' => null);
        }

        return $this->postRawJson($path, $rawBody);
    }

    /**
     * Posts exact JSON bytes to one of the contract paths. The caller/outbox owns retries.
     * The method never logs request bodies, signatures, response bodies, or secrets.
     */
    public function postRawJson($path, $rawBody)
    {
        $allowedPaths = array(
            Partners_referral_analytics_protocol::EVENTS_PATH,
            Partners_referral_analytics_protocol::ORDER_EVENTS_PATH,
            Partners_referral_analytics_protocol::ATTRIBUTION_EXCHANGE_PATH,
        );

        if (!$this->isEnabled() || !$this->isReady() || !in_array($path, $allowedPaths, true)) {
            return array('ok' => false, 'code' => 'CLIENT_NOT_READY', 'http_status' => 0);
        }
        if (!is_string($rawBody)
            || strlen($rawBody) > (int) $this->settings['max_request_bytes']
            || json_decode($rawBody, true) === null) {
            return array('ok' => false, 'code' => 'INVALID_REQUEST_BODY', 'http_status' => 0);
        }
        if (!function_exists('curl_init')) {
            return array('ok' => false, 'code' => 'CURL_UNAVAILABLE', 'http_status' => 0);
        }

        $key = null;
        foreach ($this->keys as $candidate) {
            if (!empty($candidate['active'])) {
                $key = $candidate;
                break;
            }
        }
        if (empty($key)) {
            return array('ok' => false, 'code' => 'CLIENT_NOT_READY', 'http_status' => 0);
        }

        try {
            $timestamp = (string) time();
            $nonce = Partners_referral_analytics_protocol::newNonce();
            $canonical = Partners_referral_analytics_protocol::canonicalString(
                $key['keyId'], 'POST', $path, $timestamp, $nonce, $rawBody
            );
            $signature = Partners_referral_analytics_protocol::sign($key['secret'], $canonical);
        } catch (Throwable $exception) {
            return array('ok' => false, 'code' => 'SIGNING_FAILED', 'http_status' => 0);
        }

        $url = $this->settings['api_origin'] . $path;
        $handle = curl_init($url);
        if ($handle === false) {
            return array('ok' => false, 'code' => 'TRANSPORT_FAILED', 'http_status' => 0);
        }

        curl_setopt_array($handle, array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $rawBody,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT_MS => (int) $this->settings['connect_timeout_ms'],
            CURLOPT_TIMEOUT_MS => (int) $this->settings['timeout_ms'],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json; charset=utf-8',
                'Accept: application/json',
                'x-vp-key-id: ' . $key['keyId'],
                'x-vp-timestamp: ' . $timestamp,
                'x-vp-nonce: ' . $nonce,
                'x-vp-signature: ' . $signature,
            ),
        ));

        $responseBody = '';
        $maxResponseBytes = (int) $this->settings['max_response_bytes'];
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($handle, CURLOPT_WRITEFUNCTION, function ($curl, $chunk) use (&$responseBody, $maxResponseBytes) {
            if (strlen($responseBody) + strlen($chunk) > $maxResponseBytes) {
                return 0;
            }
            $responseBody .= $chunk;
            return strlen($chunk);
        });

        $transportResult = curl_exec($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $transportOk = $transportResult !== false;
        curl_close($handle);

        $decodedResponse = $transportOk ? json_decode($responseBody, true) : null;
        $responseValid = is_array($decodedResponse);
        if ($responseValid && in_array($path, array(
            Partners_referral_analytics_protocol::EVENTS_PATH,
            Partners_referral_analytics_protocol::ORDER_EVENTS_PATH,
        ), true)) {
            $requestPayload = json_decode($rawBody, true);
            $responseKeys = array_keys($decodedResponse);
            sort($responseKeys);
            $responseValid = is_array($requestPayload)
                && isset($requestPayload['eventId'], $decodedResponse['eventId'], $decodedResponse['idempotent'])
                && count($decodedResponse) === 2
                && $responseKeys === array('eventId', 'idempotent')
                && is_string($decodedResponse['eventId'])
                && hash_equals((string) $requestPayload['eventId'], $decodedResponse['eventId'])
                && is_bool($decodedResponse['idempotent']);
        }

        return array(
            'ok' => $transportOk && Partners_referral_analytics_protocol::successfulHttpStatus($status) && $responseValid,
            'code' => !$transportOk
                ? 'TRANSPORT_FAILED'
                : (!$responseValid ? 'INVALID_SUCCESS_RESPONSE' : ($status >= 200 && $status < 300 ? 'ACCEPTED' : 'HTTP_' . $status)),
            'http_status' => $status,
            'response' => is_array($decodedResponse) ? $decodedResponse : null,
        );
    }
}
