<?php

/** Standalone PHP 7.4-compatible tests for Referral Analytics private config loading. */
if (!defined('BASEPATH')) {
    define('BASEPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
}
if (!defined('APPPATH')) {
    define('APPPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR);
}
if (!defined('FCPATH')) {
    define('FCPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
}
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development');
}

require_once APPPATH . 'libraries/Partners_referral_analytics_config_loader.php';

function partnersAnalyticsConfigAssert($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function partnersAnalyticsConfigTestSettings($overrides = array())
{
    return array_merge(array(
        'PARTNERS_REFERRAL_ANALYTICS_ENABLED' => 'false',
        'PARTNERS_REFERRAL_ANALYTICS_API_URL' => '',
        'LEGACY_ANALYTICS_HMAC_KEYS' => '',
    ), $overrides);
}

function partnersAnalyticsConfigWriteFixture($contents, $directory = null)
{
    $directory = $directory === null ? sys_get_temp_dir() : $directory;
    $temporaryPath = tempnam($directory, 'vp-analytics-config-');
    if ($temporaryPath === false) {
        throw new RuntimeException('Could not create a private config test fixture.');
    }
    $path = $temporaryPath . '.php';
    unlink($temporaryPath);
    if (file_put_contents($path, $contents) === false) {
        if (is_file($path)) {
            unlink($path);
        }
        throw new RuntimeException('Could not write a private config test fixture.');
    }

    return $path;
}

function partnersAnalyticsConfigPrivateFile($settings)
{
    return '<?php return ' . var_export($settings, true) . ';';
}

$dummySecret = rtrim(strtr(base64_encode(str_repeat('x', 32)), '+/', '-_'), '=');
$validKeyring = array(array(
    'keyId' => 'legacy-analytics-to-partners-test-key',
    'secret' => $dummySecret,
    'active' => true,
));
$validPrivateSettings = array(
    'enabled' => true,
    'api_url' => 'https://partners-api.vizaje-nica.com',
    'hmac_keys' => $validKeyring,
);
$createdFiles = array();
$passed = 0;

try {
    $emptyEnv = partnersAnalyticsConfigTestSettings();
    $loaded = Partners_referral_analytics_config_loader::load(
        sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'vp-analytics-config-does-not-exist.php',
        function ($name, $default = '') use ($emptyEnv) {
            return array_key_exists($name, $emptyEnv) ? $emptyEnv[$name] : $default;
        },
        'development',
        FCPATH
    );
    partnersAnalyticsConfigAssert($loaded['enabled'] === false, 'Missing config with empty env must leave analytics disabled.');
    partnersAnalyticsConfigAssert($loaded['api_origin'] === '' && $loaded['hmac_keys_json'] === '', 'Empty env must remain empty.');
    $passed++;

    $envKeyringJson = json_encode($validKeyring);
    $legacyEnv = partnersAnalyticsConfigTestSettings(array(
        'PARTNERS_REFERRAL_ANALYTICS_ENABLED' => 'true',
        'PARTNERS_REFERRAL_ANALYTICS_API_URL' => 'https://partners-api.vizaje-nica.com/',
        'LEGACY_ANALYTICS_HMAC_KEYS' => $envKeyringJson,
    ));
    $loaded = Partners_referral_analytics_config_loader::load(
        sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'vp-analytics-config-absent.php',
        function ($name, $default = '') use ($legacyEnv) {
            return array_key_exists($name, $legacyEnv) ? $legacyEnv[$name] : $default;
        },
        'production',
        FCPATH
    );
    partnersAnalyticsConfigAssert($loaded['enabled'] === true, 'Existing environment flag must remain supported.');
    partnersAnalyticsConfigAssert($loaded['api_origin'] === 'https://partners-api.vizaje-nica.com', 'Environment API URL must be normalized.');
    partnersAnalyticsConfigAssert($loaded['hmac_keys_json'] === $envKeyringJson, 'Environment keyring must remain supported.');
    $passed++;

    $invalidEnv = partnersAnalyticsConfigTestSettings(array(
        'PARTNERS_REFERRAL_ANALYTICS_ENABLED' => 'true',
        'PARTNERS_REFERRAL_ANALYTICS_API_URL' => 'https://partners-api.vizaje-nica.com',
        'LEGACY_ANALYTICS_HMAC_KEYS' => '{malformed',
    ));
    $loaded = Partners_referral_analytics_config_loader::load(
        sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'vp-analytics-config-no-private-file.php',
        function ($name, $default = '') use ($invalidEnv) {
            return array_key_exists($name, $invalidEnv) ? $invalidEnv[$name] : $default;
        },
        'production',
        FCPATH
    );
    partnersAnalyticsConfigAssert($loaded['enabled'] === false, 'Incomplete or malformed enabled environment config must fail closed.');
    $passed++;

    $validPath = partnersAnalyticsConfigWriteFixture(partnersAnalyticsConfigPrivateFile($validPrivateSettings));
    $createdFiles[] = $validPath;
    $loaded = Partners_referral_analytics_config_loader::load(
        $validPath,
        function ($name, $default = '') use ($legacyEnv) {
            return array_key_exists($name, $legacyEnv) ? $legacyEnv[$name] : $default;
        },
        'production',
        FCPATH
    );
    partnersAnalyticsConfigAssert($loaded['enabled'] === true, 'Private file enabled setting must load.');
    partnersAnalyticsConfigAssert($loaded['api_origin'] === 'https://partners-api.vizaje-nica.com', 'Private API URL must load.');
    partnersAnalyticsConfigAssert(
        Partners_referral_analytics_protocol::parseKeyring($loaded['hmac_keys_json'])[0]['keyId'] === $validKeyring[0]['keyId'],
        'Private keyring array must be converted to the client JSON format.'
    );
    $passed++;

    $invalidFixtures = array(
        '<?php return array("enabled" => "true", "api_url" => "https://partners-api.vizaje-nica.com", "hmac_keys" => array());',
        partnersAnalyticsConfigPrivateFile(array('enabled' => true, 'api_url' => 'http://partners-api.vizaje-nica.com', 'hmac_keys' => $validKeyring)),
        partnersAnalyticsConfigPrivateFile(array('enabled' => true, 'api_url' => 'https://partners-api.vizaje-nica.com/path', 'hmac_keys' => $validKeyring)),
        partnersAnalyticsConfigPrivateFile(array('enabled' => true, 'api_url' => 'https://partners-api.vizaje-nica.com', 'hmac_keys' => array())),
        partnersAnalyticsConfigPrivateFile(array('enabled' => true, 'api_url' => 'https://partners-api.vizaje-nica.com', 'hmac_keys' => array(array(
            'keyId' => 'invalid-key-id', 'secret' => $dummySecret, 'active' => true,
        )))),
    );
    foreach ($invalidFixtures as $fixture) {
        $path = partnersAnalyticsConfigWriteFixture($fixture);
        $createdFiles[] = $path;
        $loaded = Partners_referral_analytics_config_loader::load(
            $path,
            function ($name, $default = '') use ($legacyEnv) {
                return array_key_exists($name, $legacyEnv) ? $legacyEnv[$name] : $default;
            },
            'production',
            FCPATH
        );
        partnersAnalyticsConfigAssert(
            $loaded['enabled'] === false && $loaded['api_origin'] === '' && $loaded['hmac_keys_json'] === '',
            'Invalid private config must fail closed without falling back to environment secrets.'
        );
        $passed++;
    }

    $outputPath = partnersAnalyticsConfigWriteFixture('<?php echo "must-not-leak"; return ' . var_export($validPrivateSettings, true) . ';');
    $createdFiles[] = $outputPath;
    ob_start();
    $loaded = Partners_referral_analytics_config_loader::load($outputPath, null, 'production', FCPATH);
    $capturedOutput = ob_get_clean();
    partnersAnalyticsConfigAssert($capturedOutput === '', 'Private config output must not reach an HTTP response or CLI output.');
    partnersAnalyticsConfigAssert($loaded['enabled'] === false, 'Private config output must fail closed.');
    $passed++;

    $throwingPath = partnersAnalyticsConfigWriteFixture('<?php throw new RuntimeException("fixture error");');
    $createdFiles[] = $throwingPath;
    $loaded = Partners_referral_analytics_config_loader::load($throwingPath, null, 'production', FCPATH);
    partnersAnalyticsConfigAssert($loaded['enabled'] === false, 'Thrown errors in private config must fail closed.');
    $passed++;

    $warningPath = partnersAnalyticsConfigWriteFixture(
        '<?php fopen(__DIR__ . "/missing-config-test-file", "r"); return ' . var_export($validPrivateSettings, true) . ';'
    );
    $createdFiles[] = $warningPath;
    $loaded = Partners_referral_analytics_config_loader::load($warningPath, null, 'production', FCPATH);
    partnersAnalyticsConfigAssert($loaded['enabled'] === false, 'PHP warnings in private config must fail closed.');
    $passed++;

    $insideRootPath = partnersAnalyticsConfigWriteFixture(
        partnersAnalyticsConfigPrivateFile($validPrivateSettings),
        dirname(__DIR__)
    );
    $createdFiles[] = $insideRootPath;
    $loaded = Partners_referral_analytics_config_loader::load($insideRootPath, null, 'production', FCPATH);
    partnersAnalyticsConfigAssert($loaded['enabled'] === false, 'A private config inside the document root must be rejected.');
    $passed++;

    $serverValue = 'https://server-only.example.test';
    $_SERVER['PARTNERS_REFERRAL_ANALYTICS_API_URL'] = $serverValue;
    partnersAnalyticsConfigAssert(
        Partners_referral_analytics_config_loader::readEnvironment('PARTNERS_REFERRAL_ANALYTICS_API_URL') === $serverValue,
        'The existing $_SERVER environment fallback must remain supported.'
    );
    unset($_SERVER['PARTNERS_REFERRAL_ANALYTICS_API_URL']);
    $passed++;
} finally {
    foreach ($createdFiles as $path) {
        if (is_string($path) && is_file($path)) {
            unlink($path);
        }
    }
    unset($_SERVER['PARTNERS_REFERRAL_ANALYTICS_API_URL']);
}

echo 'Partners Referral Analytics config loader tests passed: ' . $passed . PHP_EOL;
