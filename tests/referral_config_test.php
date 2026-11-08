<?php

/**
 * Standalone tests for the referral keyring configuration loader.
 * Run with PHP 7.4+: php tests/referral_config_test.php
 */
if (!defined('BASEPATH')) {
    define('BASEPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
}
if (!defined('FCPATH')) {
    define('FCPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
}
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development');
}

class ReferralConfigTestConfig
{
    public function item($key)
    {
        return $key === 'base_url' ? 'http://vizaje-nica.local/' : null;
    }
}

class ReferralConfigTestCI
{
    public $config;

    public function __construct()
    {
        $this->config = new ReferralConfigTestConfig();
    }
}

$GLOBALS['referralConfigTestCI'] = new ReferralConfigTestCI();

function &get_instance()
{
    return $GLOBALS['referralConfigTestCI'];
}

function referralConfigTestAssert($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function referralConfigTestClearEnvironment()
{
    $names = array(
        'VP_LEGACY_CANONICAL_ORIGIN',
        'VP_LEGACY_ALLOWED_HOSTS',
        'VP_REFERRAL_HMAC_KEYRING',
        'VP_REFERRAL_PRIVATE_CONFIG',
    );
    foreach ($names as $name) {
        putenv($name);
        unset($_SERVER[$name], $_ENV[$name]);
    }
}

function referralConfigTestLoad()
{
    $config = array();
    include dirname(__DIR__) . DIRECTORY_SEPARATOR . 'application'
        . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'referral.php';
    return $config['referral'];
}

function referralConfigTestWritePrivateFile($contents, $extension = '.php')
{
    $temporaryPath = tempnam(sys_get_temp_dir(), 'vp-referral-config-');
    if ($temporaryPath === false) {
        throw new RuntimeException('Could not create a temporary test fixture.');
    }
    $path = $temporaryPath . $extension;
    unlink($temporaryPath);
    if (file_put_contents($path, $contents) === false) {
        if (is_file($path)) {
            unlink($path);
        }
        throw new RuntimeException('Could not write a temporary test fixture.');
    }
    return $path;
}

$dummySecret = base64_encode(str_repeat('x', 32));
$validEnvKeyring = array(
    'partners-dev-2026' => array(
        'principal' => 'vizaje-partners',
        'secretBase64' => $dummySecret,
    ),
);
$privatePath = null;
$invalidPrivatePath = null;
$throwingPrivatePath = null;
$outputPrivatePath = null;
$nonPhpPrivatePath = null;
$testsPassed = 0;

try {
    referralConfigTestClearEnvironment();
    referralConfigTestAssert(referralConfigTestLoad()['hmac_keyring'] === array(), 'Empty configuration must stay unconfigured.');
    $testsPassed++;

    referralConfigTestClearEnvironment();
    putenv('VP_REFERRAL_HMAC_KEYRING=' . json_encode($validEnvKeyring));
    referralConfigTestAssert(
        referralConfigTestLoad()['hmac_keyring'] === $validEnvKeyring,
        'A valid existing environment keyring must remain supported.'
    );
    $testsPassed++;

    foreach (array(
        array('bad key id' => $validEnvKeyring['partners-dev-2026']),
        array('short-key' => array('principal' => 'vizaje-partners', 'secretBase64' => base64_encode('short'))),
        array('wrong-principal' => array('principal' => 'unknown-service', 'secretBase64' => $dummySecret)),
        array('bad-base64' => array('principal' => 'vizaje-partners', 'secretBase64' => '%%%')),
    ) as $invalidKeyring) {
        referralConfigTestClearEnvironment();
        putenv('VP_REFERRAL_HMAC_KEYRING=' . json_encode($invalidKeyring));
        referralConfigTestAssert(referralConfigTestLoad()['hmac_keyring'] === array(), 'Malformed keyring must fail closed.');
        $testsPassed++;
    }

    $privateKeyring = array(
        'partners-dev-private' => array(
            'principal' => 'vizaje-partners',
            'secretBase64' => $dummySecret,
        ),
    );
    $privatePath = referralConfigTestWritePrivateFile(
        '<?php return ' . var_export(array('hmac_keyring' => $privateKeyring), true) . ';'
    );
    referralConfigTestClearEnvironment();
    putenv('VP_REFERRAL_HMAC_KEYRING=' . json_encode($validEnvKeyring));
    putenv('VP_REFERRAL_PRIVATE_CONFIG=' . $privatePath);
    $loaded = referralConfigTestLoad();
    referralConfigTestAssert($loaded['hmac_keyring'] === $privateKeyring, 'A valid private keyring must take precedence over env JSON.');
    $testsPassed++;

    referralConfigTestClearEnvironment();
    putenv('VP_REFERRAL_HMAC_KEYRING=' . json_encode($validEnvKeyring));
    putenv('VP_REFERRAL_PRIVATE_CONFIG=' . $privatePath . '.missing');
    referralConfigTestAssert(referralConfigTestLoad()['hmac_keyring'] === array(), 'A missing explicit private file must not fall back to env JSON.');
    $testsPassed++;

    referralConfigTestClearEnvironment();
    putenv('VP_REFERRAL_PRIVATE_CONFIG=relative/private.php');
    referralConfigTestAssert(referralConfigTestLoad()['hmac_keyring'] === array(), 'A relative private path must fail closed.');
    $testsPassed++;

    referralConfigTestClearEnvironment();
    putenv('VP_REFERRAL_PRIVATE_CONFIG=' . __FILE__);
    referralConfigTestAssert(referralConfigTestLoad()['hmac_keyring'] === array(), 'A keyring file inside the document root must be rejected.');
    $testsPassed++;

    $nonPhpPrivatePath = referralConfigTestWritePrivateFile('not a PHP config file', '.txt');
    referralConfigTestClearEnvironment();
    putenv('VP_REFERRAL_PRIVATE_CONFIG=' . $nonPhpPrivatePath);
    referralConfigTestAssert(referralConfigTestLoad()['hmac_keyring'] === array(), 'A non-PHP private config file must be rejected.');
    $testsPassed++;

    $invalidPrivatePath = referralConfigTestWritePrivateFile('<?php return array("hmac_keyring" => array("short" => array("principal" => "vizaje-partners", "secretBase64" => "c2hvcnQ=")));');
    referralConfigTestClearEnvironment();
    putenv('VP_REFERRAL_PRIVATE_CONFIG=' . $invalidPrivatePath);
    referralConfigTestAssert(referralConfigTestLoad()['hmac_keyring'] === array(), 'An invalid private keyring must fail closed.');
    $testsPassed++;

    $throwingPrivatePath = referralConfigTestWritePrivateFile('<?php throw new RuntimeException("fixture failure");');
    referralConfigTestClearEnvironment();
    putenv('VP_REFERRAL_PRIVATE_CONFIG=' . $throwingPrivatePath);
    referralConfigTestAssert(referralConfigTestLoad()['hmac_keyring'] === array(), 'A private config exception must fail closed.');
    $testsPassed++;

    $outputPrivatePath = referralConfigTestWritePrivateFile('<?php echo "fixture output"; return array();');
    referralConfigTestClearEnvironment();
    putenv('VP_REFERRAL_PRIVATE_CONFIG=' . $outputPrivatePath);
    ob_start();
    $loaded = referralConfigTestLoad();
    $unexpectedOutput = ob_get_clean();
    referralConfigTestAssert($loaded['hmac_keyring'] === array() && $unexpectedOutput === '', 'Private config output must not leak into the HTTP response.');
    $testsPassed++;

    referralConfigTestClearEnvironment();
    putenv('VP_LEGACY_CANONICAL_ORIGIN=https://dev.vizaje-nica.com');
    $loaded = referralConfigTestLoad();
    referralConfigTestAssert($loaded['canonical_origin'] === 'https://dev.vizaje-nica.com', 'Unrelated existing environment settings must be preserved.');
    $testsPassed++;
} finally {
    referralConfigTestClearEnvironment();
    if (is_string($privatePath) && is_file($privatePath)) {
        unlink($privatePath);
    }
    if (is_string($invalidPrivatePath) && is_file($invalidPrivatePath)) {
        unlink($invalidPrivatePath);
    }
    if (is_string($throwingPrivatePath) && is_file($throwingPrivatePath)) {
        unlink($throwingPrivatePath);
    }
    if (is_string($outputPrivatePath) && is_file($outputPrivatePath)) {
        unlink($outputPrivatePath);
    }
    if (is_string($nonPhpPrivatePath) && is_file($nonPhpPrivatePath)) {
        unlink($nonPhpPrivatePath);
    }
}

echo 'Referral config tests passed: ' . $testsPassed . PHP_EOL;
