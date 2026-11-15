<?php

/** Standalone handoff and referral attribution regression checks (PHP 7.4). */
if (!defined('BASEPATH')) {
    define('BASEPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
}
if (!defined('APPPATH')) {
    define('APPPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR);
}

class CI_Model
{
    public $db;
}

$analyticsHandoffLogs = array();
function log_message($level, $message)
{
    global $analyticsHandoffLogs;
    $analyticsHandoffLogs[] = array($level, $message);
}

require_once APPPATH . 'libraries/Partners_referral_analytics_protocol.php';
require_once APPPATH . 'models/Partners_referral_analytics_model.php';

function referralAttributionAssert($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

class ReferralAttributionTestClient
{
    public $calls = array();
    public $exchangeResponse;
    public $exchangeException;
    public $exchangeResult;
    public $seenHandoffTokens = array();

    public function postJson($path, array $payload)
    {
        $this->calls[] = array($path, $payload);
        if ($this->exchangeException instanceof Throwable) {
            throw $this->exchangeException;
        }
        if (is_array($this->exchangeResult)) {
            return $this->exchangeResult;
        }
        $token = isset($payload['handoffToken']) ? $payload['handoffToken'] : '';
        if (isset($this->seenHandoffTokens[$token])) {
            return array('ok' => false, 'response' => array('error' => 'already consumed'));
        }
        $this->seenHandoffTokens[$token] = true;
        return array('ok' => true, 'response' => $this->exchangeResponse);
    }
}

class ReferralAttributionTestQuery
{
    private $row;
    private $rows;

    public function __construct($row = null, array $rows = array())
    {
        $this->row = $row;
        $this->rows = $rows;
    }

    public function row()
    {
        return $this->row;
    }

    public function result()
    {
        return $this->rows;
    }
}

class ReferralAttributionTestDatabase
{
    public $db_debug = true;
    public $rows = array();
    private $whereValue;
    private $selected;
    private $affectedRows = 0;

    public function field_exists($field, $table)
    {
        return $table === 'orders';
    }

    public function table_exists($table)
    {
        return $table === Partners_referral_analytics_model::OUTBOX_TABLE;
    }

    public function select($columns)
    {
        $this->selected = $columns;
        return $this;
    }

    public function where($field, $value = null)
    {
        if ($field === 'id' || $field === 'order_id' || $field === 'event_id') {
            $this->whereValue = array($field, $value);
        }
        return $this;
    }

    public function get($table)
    {
        if ($table === 'orders') {
            return new ReferralAttributionTestQuery((object) array(
                'id' => 41,
                'status' => 'new',
                'total' => '125.00',
                'delivery_price' => '0.00',
                'bonus_minus' => '0.00',
                'added' => date('Y-m-d H:i:s'),
                'updated' => date('Y-m-d H:i:s'),
                'pay_success' => 0,
                'partners_referral_context' => $_SESSION[Partners_referral_analytics_model::SESSION_KEY]['context'],
                'partners_referral_items_complete' => 1,
                'partners_referral_paid_at' => null,
            ));
        }
        if ($table === 'orders_products') {
            return new ReferralAttributionTestQuery(null, array((object) array(
                'product_id' => 9001,
                'qty' => 1,
                'price' => '125.00',
                'total' => '125.00',
            )));
        }
        if ($table === Partners_referral_analytics_model::OUTBOX_TABLE) {
            $field = is_array($this->whereValue) ? $this->whereValue[0] : null;
            $value = is_array($this->whereValue) ? $this->whereValue[1] : null;
            $this->whereValue = null;
            foreach ($this->rows as $row) {
                if ($field === 'event_id' && $row['event_id'] === $value) {
                    return new ReferralAttributionTestQuery((object) $row);
                }
            }
        }
        return new ReferralAttributionTestQuery();
    }

    public function insert($table, array $row)
    {
        foreach ($this->rows as $existing) {
            if ($existing['event_id'] === $row['event_id']) {
                $this->affectedRows = 0;
                return false;
            }
        }
        $this->rows[] = $row;
        $this->affectedRows = 1;
        return true;
    }

    public function affected_rows()
    {
        return $this->affectedRows;
    }
}

function referralAttributionModel($client, $database = null, $enabled = true)
{
    $reflection = new ReflectionClass('Partners_referral_analytics_model');
    $model = $reflection->newInstanceWithoutConstructor();
    foreach (array(
        'settings' => array('enabled' => (bool) $enabled, 'max_context_bytes' => 512, 'max_request_bytes' => 65536),
        'client' => $client,
        'db' => $database ?: new ReferralAttributionTestDatabase(),
    ) as $propertyName => $value) {
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($model, $value);
    }
    return $model;
}

function referralAttributionLastDiagnostic()
{
    global $analyticsHandoffLogs;
    if (empty($analyticsHandoffLogs)) {
        return null;
    }
    $last = end($analyticsHandoffLogs);
    return isset($last[1]) ? $last[1] : null;
}

$tempSessionPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'referral-attribution-' . bin2hex(random_bytes(6));
mkdir($tempSessionPath, 0700);
session_save_path($tempSessionPath);
ini_set('session.use_cookies', '0');
ini_set('session.use_strict_mode', '0');
session_id('referral-attribution-regression');
session_start();

$passed = 0;
$context = 'v1.' . str_repeat('a', 40) . '.' . str_repeat('b', 43);
$validToken = str_repeat('HandoffToken_7', 4);
$cookieKeysBefore = array_keys($_COOKIE);

// Guard against CI3's case-sensitive model property being reintroduced.
$modelCallSites = array(
    APPPATH . 'core/FrontEndController.php',
    APPPATH . 'controllers/Ajax.php',
    APPPATH . 'controllers/Paynet.php',
    APPPATH . 'controllers/frontend/Cart.php',
    APPPATH . 'controllers/backend/Orders.php',
    APPPATH . 'controllers/Partners_referral_analytics_worker.php',
);
foreach ($modelCallSites as $file) {
    $source = file_get_contents($file);
    referralAttributionAssert(is_string($source), 'referral model call site can be read');
    referralAttributionAssert(
        !preg_match("/load->model\\(\\s*['\"]Partners_referral_analytics_model['\"]\\s*\\)/", $source),
        'referral model is never loaded without an explicit alias'
    );
}
$passed++;

// A validated exchange persists only the opaque, short-lived context in the existing session.
$_COOKIE['cookie_consent'] = rawurlencode(json_encode(array('marketing' => true, 'analytics' => false)));
$client = new ReferralAttributionTestClient();
$client->exchangeResponse = array(
    'attributionContext' => $context,
    'expiresAt' => Partners_referral_analytics_protocol::isoUtc(time() + 3600),
);
$database = new ReferralAttributionTestDatabase();
$model = referralAttributionModel($client, $database);
referralAttributionAssert($model->exchangeAttributionHandoff($validToken), 'valid handoff is exchanged');
referralAttributionAssert(referralAttributionLastDiagnostic() === 'Partners referral handoff: HANDOFF_SUCCESS', 'successful exchange has a fixed diagnostic');
referralAttributionAssert(count($client->calls) === 1, 'valid handoff makes one server exchange');
$exchangeCall = $client->calls[0];
referralAttributionAssert(
    $exchangeCall[0] === Partners_referral_analytics_protocol::ATTRIBUTION_EXCHANGE_PATH
        && $exchangeCall[1] === array('handoffToken' => $validToken),
    'exchange request matches the documented endpoint and exact payload'
);
referralAttributionAssert($_SESSION[Partners_referral_analytics_model::SESSION_KEY]['context'] === $context, 'opaque context saved to active session');
referralAttributionAssert(!isset($_SESSION[Partners_referral_analytics_model::SESSION_KEY]['handoffToken']), 'handoff token is not persisted');
referralAttributionAssert(array_keys($_COOKIE) === array_merge($cookieKeysBefore, array('cookie_consent')), 'handoff does not create another cookie');
referralAttributionAssert($model->currentAttributionContext() === $context, 'context remains available to a consented session');
$orderData = array();
referralAttributionAssert($model->addAttributionToOrderData($orderData) && $orderData['partners_referral_context'] === $context, 'context can be snapshotted onto an order');
$passed++;

// Optional analytics consent remains independent: add_to_cart is rejected without analytics permission.
referralAttributionAssert(!$model->recordAddToCart(9001, 1) && count($database->rows) === 0, 'add_to_cart remains gated by analytics consent');
$_COOKIE['cookie_consent'] = rawurlencode(json_encode(array('marketing' => true, 'analytics' => true)));
referralAttributionAssert($model->recordAddToCart(9001, 1), 'add_to_cart queues with analytics consent');
$cartPayload = json_decode($database->rows[0]['payload'], true);
referralAttributionAssert(!Partners_referral_analytics_protocol::payloadContainsPiiFields($cartPayload), 'cart event excludes PII');
$passed++;

// Marketing consent is deliberately still a gate pending legal basis approval; verify both unset and denied.
foreach (array(null, false) as $marketingConsent) {
    unset($_SESSION[Partners_referral_analytics_model::SESSION_KEY]);
    $beforeCalls = count($client->calls);
    if ($marketingConsent === null) {
        unset($_COOKIE['cookie_consent']);
    } else {
        $_COOKIE['cookie_consent'] = rawurlencode(json_encode(array('marketing' => false, 'analytics' => true)));
    }
    referralAttributionAssert(!$model->exchangeAttributionHandoff($validToken), 'unset or denied marketing consent blocks handoff pending legal review');
    referralAttributionAssert(referralAttributionLastDiagnostic() === 'Partners referral handoff: HANDOFF_CONSENT_MISSING', 'missing marketing consent has a fixed diagnostic');
    referralAttributionAssert(count($client->calls) === $beforeCalls, 'consent-gated handoff does not spend a one-time token');
}
$passed++;

// Consent parser matches the site's JSON cookie: plain and encoded boolean true are accepted, strings are not.
$_COOKIE['cookie_consent'] = json_encode(array('marketing' => true));
referralAttributionAssert($model->exchangeAttributionHandoff($validToken . 'plain-cookie'), 'plain JSON consent cookie is accepted');
$_COOKIE['cookie_consent'] = rawurlencode(json_encode(array('marketing' => 'true')));
referralAttributionAssert(!$model->exchangeAttributionHandoff($validToken . 'string-cookie'), 'string true is not accepted as consent');
referralAttributionAssert(referralAttributionLastDiagnostic() === 'Partners referral handoff: HANDOFF_CONSENT_MISSING', 'invalid consent value is diagnosed safely');
$passed++;

// A disabled feature flag is distinguished from consent and never contacts Partners.
$_COOKIE['cookie_consent'] = rawurlencode(json_encode(array('marketing' => true)));
$disabledClient = new ReferralAttributionTestClient();
$disabledModel = referralAttributionModel($disabledClient, null, false);
referralAttributionAssert(!$disabledModel->exchangeAttributionHandoff($validToken . 'disabled'), 'disabled analytics blocks exchange');
referralAttributionAssert(referralAttributionLastDiagnostic() === 'Partners referral handoff: HANDOFF_DISABLED', 'disabled analytics has a fixed diagnostic');
referralAttributionAssert(count($disabledClient->calls) === 0, 'disabled analytics does not contact Partners');
$passed++;

// Invalid token, invalid/expired Partner response, replay and API outage are handled without sensitive logs.
$beforeLogs = count($analyticsHandoffLogs);
$_COOKIE['cookie_consent'] = rawurlencode(json_encode(array('marketing' => true, 'analytics' => false)));
referralAttributionAssert(!$model->exchangeAttributionHandoff('short'), 'invalid handoff token rejected');
referralAttributionAssert(referralAttributionLastDiagnostic() === 'Partners referral handoff: HANDOFF_TOKEN_INVALID', 'invalid token uses a fixed diagnostic');
$client->exchangeResponse = array('attributionContext' => $context, 'expiresAt' => Partners_referral_analytics_protocol::isoUtc(time() - 10));
referralAttributionAssert(!$model->exchangeAttributionHandoff($validToken . 'expired'), 'expired exchange context rejected');
referralAttributionAssert(referralAttributionLastDiagnostic() === 'Partners referral handoff: HANDOFF_RESPONSE_INVALID', 'expired response context is diagnosed as invalid');
$client->exchangeResponse = array('attributionContext' => 'invalid', 'expiresAt' => Partners_referral_analytics_protocol::isoUtc(time() + 3600));
referralAttributionAssert(!$model->exchangeAttributionHandoff($validToken . 'invalid'), 'invalid Partner response rejected');
referralAttributionAssert(referralAttributionLastDiagnostic() === 'Partners referral handoff: HANDOFF_RESPONSE_INVALID', 'invalid response shape has a fixed diagnostic');
$client->exchangeResponse = array(
    'attributionContext' => $context,
    'expiresAt' => Partners_referral_analytics_protocol::isoUtc(time() + 3600),
    'unexpected' => true,
);
referralAttributionAssert(!$model->exchangeAttributionHandoff($validToken . 'extra-field'), 'unexpected response fields are rejected');
referralAttributionAssert(referralAttributionLastDiagnostic() === 'Partners referral handoff: HANDOFF_RESPONSE_INVALID', 'response schema remains exact');
$client->exchangeResult = array('ok' => false, 'code' => 'INVALID_SUCCESS_RESPONSE', 'http_status' => 200, 'response' => null);
referralAttributionAssert(!$model->exchangeAttributionHandoff($validToken . 'bad-json'), 'malformed successful API response is rejected');
referralAttributionAssert(referralAttributionLastDiagnostic() === 'Partners referral handoff: HANDOFF_RESPONSE_INVALID', 'client invalid-success response maps to invalid response diagnostic');
$client->exchangeResult = null;
$client->exchangeResponse = array('attributionContext' => $context, 'expiresAt' => Partners_referral_analytics_protocol::isoUtc(time() + 3600));
$replayToken = $validToken . 'replay';
referralAttributionAssert($model->exchangeAttributionHandoff($replayToken), 'first use of one-time token accepted');
referralAttributionAssert(!$model->exchangeAttributionHandoff($replayToken), 'replayed token rejected by Partner response');
$client->exchangeException = new RuntimeException('SECRET_TOKEN_AND_PII_MUST_NOT_BE_LOGGED');
referralAttributionAssert(!$model->exchangeAttributionHandoff($validToken . 'offline'), 'Partners API failure is best-effort');
referralAttributionAssert(referralAttributionLastDiagnostic() === 'Partners referral handoff: HANDOFF_PARTNERS_UNAVAILABLE', 'Partners failure uses fixed unavailable diagnostic');
$newLogs = array_slice($analyticsHandoffLogs, $beforeLogs);
$logText = json_encode($newLogs);
referralAttributionAssert(strpos($logText, 'SECRET_TOKEN_AND_PII_MUST_NOT_BE_LOGGED') === false, 'diagnostics exclude exception contents');
referralAttributionAssert(strpos($logText, $validToken) === false, 'diagnostics exclude handoff tokens');
$passed++;

// Without an active server session, do not consume a single-use token that cannot be saved.
$client->exchangeException = null;
$callsBeforeNoSession = count($client->calls);
session_write_close();
referralAttributionAssert(!$model->exchangeAttributionHandoff($validToken . 'nosession'), 'handoff without active session is declined');
referralAttributionAssert(referralAttributionLastDiagnostic() === 'Partners referral handoff: HANDOFF_SESSION_UNAVAILABLE', 'session failure has a fixed diagnostic');
referralAttributionAssert(count($client->calls) === $callsBeforeNoSession, 'no-session path does not call Partners');
session_id('referral-attribution-regression');
session_start();
$passed++;

// Reconciliation of the same saved order is idempotent and the outbox payload stays non-PII.
$_COOKIE['cookie_consent'] = rawurlencode(json_encode(array('marketing' => true, 'analytics' => false)));
$_SESSION[Partners_referral_analytics_model::SESSION_KEY] = array('context' => $context, 'expires_at' => time() + 3600);
$orderDatabase = new ReferralAttributionTestDatabase();
$orderModel = referralAttributionModel($client, $orderDatabase);
referralAttributionAssert($orderModel->recordOrderCreated(41), 'order.created is queued');
referralAttributionAssert($orderModel->recordOrderCreated(41), 'repeated order.created is idempotently accepted');
referralAttributionAssert(count($orderDatabase->rows) === 1, 'order.created produces only one outbox row');
$orderPayload = json_decode($orderDatabase->rows[0]['payload'], true);
referralAttributionAssert($orderPayload['eventType'] === 'order.created', 'order event type is preserved');
referralAttributionAssert(!Partners_referral_analytics_protocol::payloadContainsPiiFields($orderPayload), 'order event excludes PII');
$passed++;

session_write_close();
foreach (glob($tempSessionPath . DIRECTORY_SEPARATOR . '*') as $sessionFile) {
    unlink($sessionFile);
}
rmdir($tempSessionPath);

echo 'Referral attribution checks passed: ' . $passed . PHP_EOL;
