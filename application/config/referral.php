<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$referralEnv = static function ($name, $default = null) {
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

$CI =& get_instance();

$canonicalOrigin = rtrim(
    (string) $referralEnv(
        'VP_LEGACY_CANONICAL_ORIGIN',
        ENVIRONMENT === 'production'
            ? ''
            : $CI->config->item('base_url')
    ),
    '/'
);

$allowedHosts = array();
$configuredHosts = (string) $referralEnv('VP_LEGACY_ALLOWED_HOSTS', '');
foreach (explode(',', $configuredHosts) as $host) {
    $host = strtolower(rtrim(trim($host), '.'));
    if ($host !== '') {
        $allowedHosts[] = $host;
    }
}

if (ENVIRONMENT === 'production') {
    $allowedHosts[] = 'vizaje-nica.com';
    $allowedHosts[] = 'www.vizaje-nica.com';
}

if ($canonicalOrigin !== '') {
    $canonicalHost = parse_url($canonicalOrigin, PHP_URL_HOST);
    if (is_string($canonicalHost) && $canonicalHost !== '') {
        $allowedHosts[] = strtolower(rtrim($canonicalHost, '.'));
    }
}

$keyring = array();
$keyringJson = (string) $referralEnv('VP_REFERRAL_HMAC_KEYRING', '');
if ($keyringJson !== '') {
    $decodedKeyring = json_decode($keyringJson, true);
    if (is_array($decodedKeyring)) {
        $keyring = $decodedKeyring;
    }
}

$config['referral'] = array(
    'canonical_origin' => $canonicalOrigin,
    'allowed_hosts' => array_values(array_unique($allowedHosts)),
    // JSON object: {"key-id":{"principal":"vizaje-partners","secretBase64":"..."}}
    // Values are provisioned in server environment only; never commit secrets.
    'hmac_keyring' => $keyring,
    'max_body_bytes' => 16384,
    'max_clock_skew_seconds' => 60,
    'nonce_retention_seconds' => 900,
    'resolver_requests_per_minute' => 60,
    'catalog_page_id' => 3,
);

unset($referralEnv, $canonicalOrigin, $allowedHosts, $configuredHosts, $host, $keyring, $keyringJson, $decodedKeyring, $CI);
