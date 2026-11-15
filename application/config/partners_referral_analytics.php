<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$partnersAnalyticsEnv = static function ($name, $default = '') {
    $value = getenv($name);
    if ($value !== false) {
        return $value;
    }
    if (isset($_SERVER[$name])) {
        return $_SERVER[$name];
    }
    if (isset($_ENV[$name])) {
        return $_ENV[$name];
    }
    return $default;
};

$apiOrigin = rtrim(trim((string) $partnersAnalyticsEnv('PARTNERS_REFERRAL_ANALYTICS_API_URL')), '/');
$parsedOrigin = $apiOrigin !== '' ? parse_url($apiOrigin) : false;
$validOrigin = is_array($parsedOrigin)
    && isset($parsedOrigin['scheme'], $parsedOrigin['host'])
    && !isset($parsedOrigin['user'], $parsedOrigin['pass'], $parsedOrigin['query'], $parsedOrigin['fragment'])
    && (!isset($parsedOrigin['path']) || $parsedOrigin['path'] === '' || $parsedOrigin['path'] === '/');

if ($validOrigin && strtolower($parsedOrigin['scheme']) === 'https') {
    $apiOrigin = 'https://' . strtolower($parsedOrigin['host'])
        . (isset($parsedOrigin['port']) ? ':' . (int) $parsedOrigin['port'] : '');
} elseif ($validOrigin && ENVIRONMENT !== 'production'
    && strtolower($parsedOrigin['scheme']) === 'http'
    && in_array(strtolower($parsedOrigin['host']), array('localhost', '127.0.0.1', '::1'), true)) {
    $apiOrigin = 'http://' . strtolower($parsedOrigin['host'])
        . (isset($parsedOrigin['port']) ? ':' . (int) $parsedOrigin['port'] : '');
} else {
    $apiOrigin = '';
}

$config['partners_referral_analytics'] = array(
    'enabled' => strtolower(trim((string) $partnersAnalyticsEnv('PARTNERS_REFERRAL_ANALYTICS_ENABLED', 'false'))) === 'true',
    'api_origin' => $apiOrigin,
    // Provision this server environment variable through the PHP-FPM/pool environment,
    // never in the webroot or source control. Schema matches Partners' keyring entries.
    'hmac_keys_json' => (string) $partnersAnalyticsEnv('LEGACY_ANALYTICS_HMAC_KEYS', ''),
    'connect_timeout_ms' => 500,
    'timeout_ms' => 2000,
    'max_request_bytes' => 65536,
    'max_response_bytes' => 8192,
    'max_context_bytes' => 512,
);

unset($partnersAnalyticsEnv, $apiOrigin, $parsedOrigin, $validOrigin);
