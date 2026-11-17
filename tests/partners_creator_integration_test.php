<?php
/** Standalone, network-free regression checks for Legacy M5.5.2. PHP 7.4 syntax. */
if (!defined('BASEPATH')) define('BASEPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
if (!defined('APPPATH')) define('APPPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR);
if (!defined('FCPATH')) define('FCPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
if (!defined('ENVIRONMENT')) define('ENVIRONMENT', 'development');
if (!class_exists('CI_Model')) { class CI_Model {} }

require_once APPPATH . 'libraries/Partners_creator_protocol.php';
require_once APPPATH . 'libraries/Partners_creator_config_loader.php';
require_once APPPATH . 'libraries/Partners_creator_grant_crypto.php';
require_once APPPATH . 'libraries/Partners_creator_security.php';
require_once APPPATH . 'libraries/Partners_creator_client.php';
require_once APPPATH . 'libraries/Partners_creator_dashboard_presenter.php';
require_once APPPATH . 'models/Partners_creator_model.php';

function creatorAssert($condition, $message)
{
    if (!$condition) throw new RuntimeException($message);
}

function creatorExpectInvalid(callable $callback, $message)
{
    try { $callback(); }
    catch (Throwable $error) { return; }
    throw new RuntimeException($message);
}

class CreatorLinkTestResult
{
    private $row;
    public function __construct($row) { $this->row = $row; }
    public function row_array() { return $this->row; }
}

class CreatorLinkTestDatabase
{
    public $db_debug = true;
    public $links = array();
    public $clientFlags = array(42 => 0, 43 => 0);
    public $history = array(array('order_id' => 7001), array('event_id' => 'historic-event'));
    private $where = array();
    private $snapshot = null;
    private $failed = false;

    public function table_exists($table) { return $table === 'creator_partner_links'; }
    public function field_exists($field, $table) { return $field === 'is_creator' && $table === 'clients'; }
    public function where($field, $value) { $this->where[$field] = $value; return $this; }
    public function limit($limit) { return $this; }
    public function get($table)
    {
        $row = null;
        if ($table === 'creator_partner_links') {
            foreach ($this->links as $link) {
                $matches = true;
                foreach ($this->where as $field => $value) if (($link[$field] ?? null) != $value) $matches = false;
                if ($matches) { $row = $link; break; }
            }
        }
        $this->where = array();
        return new CreatorLinkTestResult($row);
    }
    public function insert($table, $row)
    {
        if ($table !== 'creator_partner_links') return false;
        foreach ($this->links as $existing) {
            if ((int) $existing['client_id'] === (int) $row['client_id']
                || strtolower($existing['partner_id']) === strtolower($row['partner_id'])) {
                $this->failed = true;
                return false;
            }
        }
        $this->links[] = $row;
        return true;
    }
    public function update($table, $data)
    {
        if ($table !== 'clients') return false;
        $id = (int) ($this->where['id'] ?? 0);
        $this->where = array();
        if (!array_key_exists($id, $this->clientFlags)) { $this->failed = true; return false; }
        $this->clientFlags[$id] = (int) $data['is_creator'];
        return true;
    }
    public function delete($table)
    {
        $id = (int) ($this->where['client_id'] ?? 0);
        $this->where = array();
        if ($table !== 'creator_partner_links') return false;
        $this->links = array_values(array_filter($this->links, function ($row) use ($id) {
            return (int) $row['client_id'] !== $id;
        }));
        return true;
    }
    public function trans_begin()
    {
        $this->snapshot = array($this->links, $this->clientFlags);
        $this->failed = false;
        return true;
    }
    public function trans_status() { return !$this->failed; }
    public function trans_commit()
    {
        if ($this->failed) { $this->trans_rollback(); return false; }
        $this->snapshot = null;
        return true;
    }
    public function trans_rollback()
    {
        if ($this->snapshot !== null) list($this->links, $this->clientFlags) = $this->snapshot;
        $this->snapshot = null;
        $this->failed = false;
        return true;
    }
}

function creatorTestEnv($overrides = array())
{
    return array_merge(array(
        'LEGACY_TO_PARTNERS_HMAC_KEYS' => '',
        'LEGACY_PARTNERS_GRANT_ENCRYPTION_KEYS' => '',
        'PARTNERS_CREATOR_INTEGRATION_ENABLED' => 'false',
        'PARTNERS_API_URL' => '',
        'PARTNERS_WEB_URL' => 'https://partners.vizaje-nica.com',
        'LEGACY_ACCOUNT_LINK_REDIRECT_URI' => '',
    ), $overrides);
}

function creatorWriteFixture($contents, $directory)
{
    $path = tempnam($directory, 'vp-creator-');
    if ($path === false || file_put_contents($path, $contents) === false) throw new RuntimeException('Could not create temporary fixture.');
    return $path;
}

$passed = 0;
$files = array();
$directories = array();
$dummySecret = Partners_creator_protocol::encodeBase64Url(str_repeat("\0", 32));
$hmacKeys = array(array('keyId' => 'legacy-to-partners-test-2026', 'secret' => $dummySecret, 'active' => true));
$encryptionKeys = array('grant-test-2026' => array('key' => str_repeat('e', 32), 'active' => true));

try {
    $parsedKeys = Partners_creator_protocol::parseKeyring(json_encode($hmacKeys));
    creatorAssert(count($parsedKeys) === 1 && strlen($parsedKeys[0]['secret']) === 32, 'Valid separate Partners HMAC keyring must decode.'); $passed++;
    creatorExpectInvalid(function () use ($hmacKeys) {
        $duplicate = $hmacKeys; $duplicate[] = $hmacKeys[0]; Partners_creator_protocol::parseKeyring(json_encode($duplicate));
    }, 'Duplicate HMAC key IDs must be rejected.'); $passed++;
    creatorExpectInvalid(function () use ($dummySecret) {
        Partners_creator_protocol::parseKeyring(json_encode(array(
            array('keyId' => 'legacy-to-partners-a', 'secret' => $dummySecret, 'active' => true),
            array('keyId' => 'legacy-to-partners-b', 'secret' => $dummySecret, 'active' => true),
        )));
    }, 'Multiple active HMAC keys must be rejected.'); $passed++;

    $verifier = Partners_creator_protocol::randomToken(32);
    $state = Partners_creator_protocol::randomToken(32);
    creatorAssert(strlen($verifier) === 43 && strlen($state) === 43, 'PKCE verifier and state must be strong Base64URL values.'); $passed++;
    creatorAssert(Partners_creator_protocol::pkceChallenge($verifier) === Partners_creator_protocol::encodeBase64Url(hash('sha256', $verifier, true)), 'PKCE challenge must be SHA-256 Base64URL without padding.'); $passed++;
    creatorAssert(strlen(Partners_creator_protocol::randomToken(16)) === 22, 'Each service request nonce must encode 16 random bytes.'); $passed++;
    $rawBody = '{"name":"Тест"}';
    $canonical = Partners_creator_protocol::canonicalString('legacy-to-partners-test-2026', 'post', '/v1/internal/account-linking/initiate', '1794860000', 'nonce', $rawBody);
    $expectedCanonical = "VP-HMAC-SHA256-V1\nlegacy-to-partners-test-2026\nPOST\n/v1/internal/account-linking/initiate\n1794860000\nnonce\n" . hash('sha256', $rawBody);
    creatorAssert($canonical === $expectedCanonical, 'Partners canonical string must contain exact seven LF-separated lines.'); $passed++;
    $signature = Partners_creator_protocol::signature($parsedKeys[0]['secret'], $canonical);
    creatorAssert(preg_match('/^[a-f0-9]{64}$/', $signature) === 1, 'HMAC signature must be lowercase SHA-256 hex.'); $passed++;

    $client = (object) array('id' => 42, 'is_creator' => 1, 'email' => 'creator@example.test', 'password' => 'hashed-password');
    $session = array('user_id' => 42, 'user_login' => 'creator@example.test', 'usr_key' => hash('sha512', $client->email . $client->password));
    creatorAssert(Partners_creator_security::isAuthenticatedCreatorSession($session, $client), 'Authenticated creator Legacy session must pass.'); $passed++;
    $ordinary = clone $client; $ordinary->is_creator = 0; $ordinary->email = 'buyer@example.test';
    $ordinarySession = array('user_id' => 42, 'user_login' => $ordinary->email, 'usr_key' => hash('sha512', $ordinary->email . $ordinary->password));
    creatorAssert(Partners_creator_security::isAuthenticatedLegacySession($ordinarySession, $ordinary), 'Ordinary authenticated Legacy customer may start account linking without is_creator.'); $passed++;
    $session['user_id'] = 43;
    creatorAssert(!Partners_creator_security::isAuthenticatedCreatorSession($session, $client), 'A different Legacy client ID must fail.'); $passed++;
    $session['user_id'] = 42;
    creatorAssert(!Partners_creator_security::isAuthenticatedCreatorSession($session, $ordinary), 'Ordinary Legacy users must fail creator authorization.'); $passed++;
    creatorAssert(!Partners_creator_security::isAuthenticatedLegacySession($ordinarySession, $client), 'Session email/client mismatch must fail account linking.'); $passed++;
    $csrf = str_repeat('a', 64);
    creatorAssert(Partners_creator_security::isValidCsrfToken($csrf, $csrf, 42, 42), 'Matching session-bound CSRF token passes.'); $passed++;
    creatorAssert(!Partners_creator_security::isValidCsrfToken($csrf, $csrf, 43, 42), 'CSRF token bound to another Legacy client fails.'); $passed++;
    creatorAssert(!Partners_creator_security::isValidCsrfToken($csrf, str_repeat('b', 64), 42, 42), 'Mismatched CSRF token fails.'); $passed++;

    $flow = array('client_id' => 42, 'session_id' => 'test-session-id', 'state' => $state, 'verifier' => $verifier, 'started_at' => 1000);
    creatorAssert(Partners_creator_security::isValidCallback($flow, 42, 'test-session-id', $state, Partners_creator_protocol::randomToken(32), 1100), 'Valid one-time callback context must pass.'); $passed++;
    creatorAssert(!Partners_creator_security::isValidCallback($flow, 42, 'other-session', $state, Partners_creator_protocol::randomToken(32), 1100), 'Callback from a different Legacy session must fail.'); $passed++;
    creatorAssert(!Partners_creator_security::isValidCallback($flow, 42, 'test-session-id', Partners_creator_protocol::randomToken(32), Partners_creator_protocol::randomToken(32), 1100), 'Mismatched callback state must fail.'); $passed++;
    creatorAssert(!Partners_creator_security::isValidCallback($flow, 42, 'test-session-id', $state, Partners_creator_protocol::randomToken(32), 1400), 'Expired callback state must fail.'); $passed++;
    creatorAssert(!Partners_creator_security::isValidCallback(null, 42, 'test-session-id', $state, Partners_creator_protocol::randomToken(32), 1100), 'Consumed or replayed callback state must fail.'); $passed++;

    $exchange = array('partnerId' => '47de7761-3730-43d2-ac10-de533fbf7786', 'creatorId' => 'CRE-000001', 'type' => 'creator', 'status' => 'active', 'grantToken' => Partners_creator_protocol::randomToken(32), 'scopes' => array('account:status', 'analytics:read', 'links:read', 'links:write', 'account:unlink'));
    creatorAssert(Partners_creator_security::isValidExchangeResponse($exchange), 'Contract-shaped active creator exchange must pass.'); $passed++;
    creatorAssert(Partners_creator_security::isEligibleExchangeResponse($exchange), 'Only an active approved creator exchange is eligible to link.'); $passed++;
    $badExchange = $exchange; $badExchange['scopes'] = array('analytics:read');
    creatorAssert(!Partners_creator_security::isValidExchangeResponse($badExchange), 'Exchange without account:status must fail closed.'); $passed++;
    $badExchange = $exchange; $badExchange['type'] = 'buyer';
    creatorAssert(!Partners_creator_security::isValidExchangeResponse($badExchange), 'Non-creator Partner account must not be linked.'); $passed++;
    $badExchange = $exchange; $badExchange['status'] = 'suspended';
    creatorAssert(Partners_creator_security::isValidExchangeResponse($badExchange) && $badExchange['status'] !== 'active', 'Suspended exchange response is structurally valid but cannot be persisted by the callback.'); $passed++;
    creatorAssert(!Partners_creator_security::isEligibleExchangeResponse($badExchange), 'Suspended Partner account is denied linking.'); $passed++;
    $badExchange['status'] = 'banned';
    creatorAssert(!Partners_creator_security::isEligibleExchangeResponse($badExchange), 'Banned Partner account is denied linking.'); $passed++;
    $row = array('partner_id' => $exchange['partnerId'], 'creator_id' => $exchange['creatorId']);
    creatorAssert(Partners_creator_security::isValidStatusResponse(array('partnerId' => $exchange['partnerId'], 'creatorId' => 'CRE-000001', 'type' => 'creator', 'status' => 'active'), $row), 'Status response for the stored identity must pass.'); $passed++;
    creatorAssert(!Partners_creator_security::isValidStatusResponse(array('partnerId' => '47de7761-3730-43d2-ac10-de533fbf7777', 'creatorId' => 'CRE-000001', 'type' => 'creator', 'status' => 'active'), $row), 'Status response for another Partner identity must fail.'); $passed++;
    creatorAssert(!Partners_creator_security::isActiveStatusResponse(array('partnerId' => $exchange['partnerId'], 'creatorId' => 'CRE-000001', 'type' => 'creator', 'status' => 'suspended'), $row), 'Suspended or revoked Partner status cannot read protected dashboard data.'); $passed++;

    $linkDb = new CreatorLinkTestDatabase();
    $linkModel = new Partners_creator_model();
    $linkModel->db = $linkDb;
    $testGrant = Partners_creator_protocol::randomToken(32);
    creatorAssert($linkModel->saveLink(42, $exchange['partnerId'], 'CRE-000001', 'active', $exchange['scopes'], $testGrant, $encryptionKeys), 'Ordinary Legacy customer can persist a verified active Partners creator link.'); $passed++;
    creatorAssert($linkDb->clientFlags[42] === 1, 'Successful linking synchronizes the Legacy creator UI flag.'); $passed++;
    creatorAssert(!$linkModel->saveLink(42, '47de7761-3730-43d2-ac10-de533fbf7777', 'CRE-000002', 'active', $exchange['scopes'], $testGrant, $encryptionKeys), 'Repeated or competing link cannot replace the existing client mapping.'); $passed++;
    creatorAssert(!$linkModel->saveLink(43, $exchange['partnerId'], 'CRE-000001', 'active', $exchange['scopes'], $testGrant, $encryptionKeys), 'One Partners identity cannot be linked to a second Legacy client.'); $passed++;
    $linkedRow = $linkModel->getForClient(42);
    $linkedRow['grant_encrypted'] = 'tampered.grant.value';
    creatorAssert($linkModel->grantToken($linkedRow, $encryptionKeys) === false, 'Revoked/tampered grant cannot authorize dashboard access.'); $passed++;
    creatorAssert($linkModel->deleteForClient(42) && $linkModel->getForClient(42) === null, 'Successful unlink removes the active mapping so protected dashboard data is no longer available.'); $passed++;
    creatorAssert(count($linkDb->history) === 2, 'Unlink preserves order and analytics history.'); $passed++;

    creatorAssert(Partners_creator_security::isAllowedTarget('https://vizaje-nica.com/ru/katalog/teni/111157'), 'RU product destination must be allowed.'); $passed++;
    creatorAssert(Partners_creator_security::isAllowedTarget('https://vizaje-nica.com/ro/catalog/anastasia-beverly-hills'), 'RO brand destination must be allowed.'); $passed++;
    creatorAssert(!Partners_creator_security::isAllowedTarget('https://evil.example/ru/katalog/teni/111157'), 'External destination must be rejected.'); $passed++;
    creatorAssert(!Partners_creator_security::isAllowedTarget('https://user:pass@vizaje-nica.com/ru/katalog/teni/111157'), 'Credential-bearing destination must be rejected.'); $passed++;
    creatorAssert(!Partners_creator_security::isAllowedTarget('http://vizaje-nica.com/ru/katalog/teni/111157'), 'Insecure public destination must be rejected.'); $passed++;
    creatorAssert(!Partners_creator_security::isAllowedTarget('https://vizaje-nica.com/katalog/teni/111157'), 'Destination without RU/RO prefix must be rejected.'); $passed++;

    $analytics = Partners_creator_dashboard_presenter::analytics(array('summary' => array('clicks' => 12, 'addToCart' => 3, 'ordersCreated' => 2, 'ordersConfirmed' => 1, 'confirmedSalesMdl' => 420, 'conversion' => 8.3)));
    creatorAssert(!empty($analytics['available']) && $analytics['metrics']['confirmed_orders'] === 1, 'Complete analytics summary must map to the dashboard whitelist.'); $passed++;
    $emptyAnalytics = Partners_creator_dashboard_presenter::analytics(array('summary' => array('clicks' => 0, 'addToCart' => 0, 'ordersCreated' => 0, 'ordersConfirmed' => 0, 'confirmedSalesMdl' => 0, 'conversion' => 0)));
    creatorAssert(!empty($emptyAnalytics['available']) && !empty($emptyAnalytics['empty']), 'Zero analytics must render as a real empty state.'); $passed++;
    creatorAssert(empty(Partners_creator_dashboard_presenter::analytics(array('unexpected' => 'shape'))['available']), 'Unknown analytics response shape must not be rendered as fabricated metrics.'); $passed++;

    $linkResponse = array('links' => array(array('id' => 'link-123', 'linkType' => 'product', 'shortUrl' => 'https://vizaje-nica.com/r/abc123', 'targetUrl' => 'https://vizaje-nica.com/ru/katalog/teni/111157', 'label' => 'Test', 'createdAt' => '2026-11-16T10:00:00Z', 'status' => 'active', 'clicks' => 3, 'orders' => 1)));
    $links = Partners_creator_dashboard_presenter::links($linkResponse);
    creatorAssert(!empty($links['available']) && count($links['links']) === 1 && $links['links'][0]['short_url'] === $linkResponse['links'][0]['shortUrl'], 'Contract-shaped link response must be presented.'); $passed++;
    $badLinks = Partners_creator_dashboard_presenter::links(array('links' => array(array('id' => 'link-123', 'linkType' => 'product', 'shortUrl' => 'https://evil.example/r/abc123'))));
    creatorAssert(empty($badLinks['available']), 'Untrusted short URL must not be exposed as a clickable link.'); $passed++;
    creatorAssert(Partners_creator_dashboard_presenter::createdLink(array('link' => $linkResponse['links'][0])) !== null, 'Create response link object must pass the same output whitelist.'); $passed++;

    $authorizeConfig = array('partner_origin' => 'https://partners.vizaje-nica.com');
    $apiClient = new Partners_creator_client($authorizeConfig);
    creatorAssert($apiClient->isAllowedAuthorizeUrl('https://partners.vizaje-nica.com/link/legacy/authorize?ticket=' . Partners_creator_protocol::randomToken(32)), 'Registered Partners authorization URL must pass.'); $passed++;
    creatorAssert(!$apiClient->isAllowedAuthorizeUrl('https://partners.vizaje-nica.com/link/legacy/authorize?ticket=' . Partners_creator_protocol::randomToken(32) . '&ticket=duplicate'), 'Duplicate authorize ticket query parameters must fail.'); $passed++;
    creatorAssert(!$apiClient->isAllowedAuthorizeUrl('https://evil.example/link/legacy/authorize?ticket=' . Partners_creator_protocol::randomToken(32)), 'Foreign authorize host must fail.'); $passed++;
    creatorAssert(!$apiClient->isAllowedAuthorizeUrl('https://partners.vizaje-nica.com/link/legacy/authorize?ticket=' . Partners_creator_protocol::randomToken(32) . '#fragment'), 'Authorize URL fragments must fail.'); $passed++;

    $grant = Partners_creator_protocol::randomToken(32);
    $encryptedGrant = Partners_creator_grant_crypto::encrypt($grant, $encryptionKeys);
    creatorAssert(Partners_creator_grant_crypto::decrypt($encryptedGrant, $encryptionKeys) === $grant, 'Grant AES-GCM encrypt/decrypt must round-trip.'); $passed++;
    $parts = explode('.', $encryptedGrant); $parts[3] = Partners_creator_protocol::encodeBase64Url(str_repeat('x', 16));
    creatorExpectInvalid(function () use ($parts, $encryptionKeys) { Partners_creator_grant_crypto::decrypt(implode('.', $parts), $encryptionKeys); }, 'Tampered grant envelope must be rejected.'); $passed++;
    creatorExpectInvalid(function () use ($encryptedGrant) { Partners_creator_grant_crypto::decrypt($encryptedGrant, array()); }, 'Unknown encryption key ID must fail closed.'); $passed++;
    creatorAssert(Partners_creator_model::isUuid($exchange['partnerId']), 'Partner UUID format must be stable.'); $passed++;
    creatorAssert(!Partners_creator_model::isUuid('not-a-uuid'), 'Invalid Partner IDs must be rejected.'); $passed++;

    $envHmacJson = json_encode($hmacKeys);
    $envEncryptionJson = json_encode(array(array('keyId' => 'grant-test-2026', 'secret' => Partners_creator_protocol::encodeBase64Url(str_repeat('e', 32)), 'active' => true)));
    $env = creatorTestEnv(array(
        'LEGACY_TO_PARTNERS_HMAC_KEYS' => $envHmacJson,
        'LEGACY_PARTNERS_GRANT_ENCRYPTION_KEYS' => $envEncryptionJson,
        'PARTNERS_CREATOR_INTEGRATION_ENABLED' => 'true',
        'PARTNERS_API_URL' => 'https://partners-api.vizaje-nica.com/',
        'LEGACY_ACCOUNT_LINK_REDIRECT_URI' => 'https://dev.vizaje-nica.com/account/creator-link/callback',
    ));
    $envReader = function ($name, $default = '') use ($env) { return array_key_exists($name, $env) ? $env[$name] : $default; };
    $config = Partners_creator_config_loader::load($envReader, 'production', '', FCPATH);
    creatorAssert(!empty($config['enabled']) && $config['api_origin'] === 'https://partners-api.vizaje-nica.com', 'Existing server environment variables must load.'); $passed++;

    $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'vp-creator-private-' . bin2hex(random_bytes(5));
    if (!mkdir($directory, 0700)) throw new RuntimeException('Could not create temporary private config directory.');
    $directories[] = $directory;
    $privateSettings = array(
        'enabled' => true,
        'api_url' => 'https://partners-api.vizaje-nica.com',
        'partner_origin' => 'https://partners.vizaje-nica.com',
        'redirect_uri' => 'https://dev.vizaje-nica.com/account/creator-link/callback',
        'hmac_keys' => $hmacKeys,
        'grant_encryption_keys' => array(array('keyId' => 'grant-test-2026', 'secret' => Partners_creator_protocol::encodeBase64Url(str_repeat('e', 32)), 'active' => true)),
    );
    $privatePath = creatorWriteFixture('<?php return ' . var_export($privateSettings, true) . ';', $directory);
    $files[] = $privatePath;
    $privateConfig = Partners_creator_config_loader::load(null, 'production', $privatePath, FCPATH);
    creatorAssert(!empty($privateConfig['enabled']) && $privateConfig['redirect_uri'] === $privateSettings['redirect_uri'], 'External private PHP config must load after validation.'); $passed++;
    $missingPath = $directory . DIRECTORY_SEPARATOR . 'absent.php';
    $failedClosed = Partners_creator_config_loader::load($envReader, 'production', $missingPath, FCPATH);
    creatorAssert(empty($failedClosed['enabled']) && empty($failedClosed['hmac_keys']), 'Invalid explicit private path must fail closed without environment fallback.'); $passed++;

    $insidePath = creatorWriteFixture('<?php return ' . var_export($privateSettings, true) . ';', FCPATH);
    $files[] = $insidePath;
    $insideConfig = Partners_creator_config_loader::load(null, 'production', $insidePath, FCPATH);
    creatorAssert(empty($insideConfig['enabled']), 'Private config under document root must be rejected.'); $passed++;

    $outputPath = creatorWriteFixture('<?php echo "do-not-expose"; return ' . var_export($privateSettings, true) . ';', $directory);
    $files[] = $outputPath;
    ob_start();
    $outputConfig = Partners_creator_config_loader::load(null, 'production', $outputPath, FCPATH);
    $output = ob_get_clean();
    creatorAssert($output === '' && empty($outputConfig['enabled']), 'Config output must be suppressed and the config rejected.'); $passed++;

    $badSettings = $privateSettings; $badSettings['redirect_uri'] .= '?unsafe=1';
    $badPath = creatorWriteFixture('<?php return ' . var_export($badSettings, true) . ';', $directory);
    $files[] = $badPath;
    creatorAssert(empty(Partners_creator_config_loader::load(null, 'production', $badPath, FCPATH)['enabled']), 'Redirect query string must be rejected.'); $passed++;
} finally {
    foreach ($files as $path) if (is_file($path)) unlink($path);
    foreach ($directories as $directory) if (is_dir($directory)) rmdir($directory);
}

echo 'Legacy Partners creator integration checks passed: ' . $passed . PHP_EOL;
