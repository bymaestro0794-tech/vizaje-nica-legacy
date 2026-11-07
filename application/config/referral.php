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

$validateKeyring = static function ($candidate) {
    if (!is_array($candidate) || count($candidate) === 0) {
        return array();
    }

    foreach ($candidate as $keyId => $entry) {
        if (!is_string($keyId)
            || !preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]{0,99}$/', $keyId)
            || !is_array($entry)
            || !isset($entry['principal'], $entry['secretBase64'])
            || !is_string($entry['principal'])
            || !in_array($entry['principal'], array('vizaje-partners', 'vizaje-legacy'), true)
            || !is_string($entry['secretBase64'])
            || $entry['secretBase64'] === '') {
            return array();
        }

        $secret = base64_decode($entry['secretBase64'], true);
        if (!is_string($secret)
            || strlen($secret) < 32
            || base64_encode($secret) !== $entry['secretBase64']) {
            return array();
        }
    }

    return $candidate;
};

$keyring = array();
$privateConfigPath = trim((string) $referralEnv('VP_REFERRAL_PRIVATE_CONFIG', ''));
if ($privateConfigPath !== '') {
    // When the private path is explicitly configured, never fall back to an
    // environment keyring if the file is missing, unsafe, or malformed.
    $isWindowsDrivePath = strlen($privateConfigPath) >= 3
        && ctype_alpha($privateConfigPath[0])
        && $privateConfigPath[1] === ':'
        && ($privateConfigPath[2] === '\\' || $privateConfigPath[2] === '/');
    $isWindowsUncPath = strlen($privateConfigPath) >= 2
        && $privateConfigPath[0] === '\\'
        && $privateConfigPath[1] === '\\';
    $isAbsolutePath = (isset($privateConfigPath[0]) && $privateConfigPath[0] === '/')
        || $isWindowsDrivePath
        || $isWindowsUncPath;
    $resolvedPrivatePath = $isAbsolutePath ? @realpath($privateConfigPath) : false;
    $resolvedDocumentRoot = defined('FCPATH') ? @realpath(FCPATH) : false;

    if (is_string($resolvedPrivatePath)
        && is_file($resolvedPrivatePath)
        && strtolower(pathinfo($resolvedPrivatePath, PATHINFO_EXTENSION)) === 'php'
        && is_readable($resolvedPrivatePath)
        && is_string($resolvedDocumentRoot)) {
        $normalizedPrivatePath = rtrim(str_replace('\\', '/', $resolvedPrivatePath), '/');
        $normalizedDocumentRoot = rtrim(str_replace('\\', '/', $resolvedDocumentRoot), '/');
        if (DIRECTORY_SEPARATOR === '\\') {
            $normalizedPrivatePath = strtolower($normalizedPrivatePath);
            $normalizedDocumentRoot = strtolower($normalizedDocumentRoot);
        }
        $isInsideDocumentRoot = $normalizedPrivatePath === $normalizedDocumentRoot
            || strpos($normalizedPrivatePath, $normalizedDocumentRoot . '/') === 0;

        if (!$isInsideDocumentRoot) {
            $privateConfig = null;
            $outputBufferLevel = ob_get_level();
            ob_start();
            try {
                $privateConfig = @include $resolvedPrivatePath;
            } catch (Throwable $error) {
                $privateConfig = null;
            }
            while (ob_get_level() > $outputBufferLevel) {
                ob_end_clean();
            }

            if (is_array($privateConfig) && isset($privateConfig['hmac_keyring'])) {
                $keyring = $validateKeyring($privateConfig['hmac_keyring']);
            }
        }
    }
} else {
    $keyringJson = (string) $referralEnv('VP_REFERRAL_HMAC_KEYRING', '');
    if ($keyringJson !== '') {
        $decodedKeyring = json_decode($keyringJson, true);
        if (is_array($decodedKeyring)) {
            $keyring = $validateKeyring($decodedKeyring);
        }
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

unset(
    $referralEnv,
    $canonicalOrigin,
    $allowedHosts,
    $configuredHosts,
    $host,
    $keyring,
    $keyringJson,
    $decodedKeyring,
    $validateKeyring,
    $privateConfigPath,
    $isWindowsDrivePath,
    $isWindowsUncPath,
    $isAbsolutePath,
    $resolvedPrivatePath,
    $resolvedDocumentRoot,
    $normalizedPrivatePath,
    $normalizedDocumentRoot,
    $isInsideDocumentRoot,
    $privateConfig,
    $outputBufferLevel,
    $error,
    $secret,
    $CI
);
