<?php

/**
 * Standalone, DB-free tests for the M5.1 catalog resolver and auth gates.
 * Run with: C:\\xampp74\\php\\php.exe tests\\referral_resolver_test.php
 */
if (!defined('BASEPATH')) {
    define('BASEPATH', __DIR__ . DIRECTORY_SEPARATOR);
}
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development');
}

class ResolverTestConfig
{
    private $items;

    public function __construct($items)
    {
        $this->items = $items;
    }

    public function item($key)
    {
        return isset($this->items[$key]) ? $this->items[$key] : null;
    }
}

class ResolverTestLoader
{
    public function config($name) {}
    public function model($name) {}
    public function library($name) {}
}

class ResolverTestMenuModel
{
    public function get_page_data_by_id($locale, $id)
    {
        if ((int) $id !== 3) {
            return false;
        }
        return (object) array(
            'uriRU' => 'katalog',
            'uriRO' => 'catalog',
            'isShown' => 1,
        );
    }
}

class ResolverTestCategoriesModel
{
    private $rows;

    public function __construct()
    {
        $this->rows = array(
            (object) array('id' => 101, 'parent_id' => 0, 'route_uri' => 'teni', 'uriRU' => 'teni', 'uriRO' => 'farduri', 'isShown' => 1),
            (object) array('id' => 103, 'parent_id' => 0, 'route_uri' => 'collision', 'uriRU' => 'collision', 'uriRO' => 'collision-ro', 'isShown' => 1),
            (object) array('id' => 104, 'parent_id' => 0, 'route_uri' => 'hidden-brand-uri', 'uriRU' => 'hidden-brand-uri', 'uriRO' => 'hidden-brand-uri-ro', 'isShown' => 0),
            (object) array('id' => 105, 'parent_id' => 0, 'route_uri' => 'ambiguous', 'uriRU' => 'ambiguous', 'uriRO' => 'ambiguous-ro', 'isShown' => 1),
            (object) array('id' => 106, 'parent_id' => 0, 'route_uri' => 'ambiguous', 'uriRU' => 'ambiguous', 'uriRO' => 'ambiguous-ro', 'isShown' => 1),
        );
    }

    public function find_referral_route_categories($locale, $uri)
    {
        $field = $locale === 'ru' ? 'uriRU' : 'uriRO';
        return array_values(array_filter($this->rows, static function ($row) use ($field, $uri) {
            return $row->{$field} === $uri;
        }));
    }
}

class ResolverTestBrandsModel
{
    private $rows = array(
        array('id' => 201, 'uri' => 'anastasia-beverly-hills', 'isShown' => 1),
        array('id' => 202, 'uri' => 'collision', 'isShown' => 1),
        array('id' => 203, 'uri' => 'hidden-brand-uri', 'isShown' => 1),
        array('id' => 204, 'uri' => 'ambiguous-brand', 'isShown' => 1),
        array('id' => 205, 'uri' => 'ambiguous-brand', 'isShown' => 1),
    );

    public function find_referral_route_brands($uri)
    {
        $matches = array_values(array_filter($this->rows, static function ($row) use ($uri) {
            return $row['uri'] === $uri;
        }));
        return array_map(static function ($row) {
            return (object) $row;
        }, $matches);
    }
}

class ResolverTestProductsModel
{
    private $rows = array(
        array('id' => 901, 'category_id' => 101, 'route_uri' => '111157', 'uriRU' => '111157', 'uriRO' => 'produs-111157', 'isShown' => 1, 'source_guid' => 'fixture-guid', 'is_gift' => 0),
        array('id' => 902, 'category_id' => 101, 'route_uri' => 'ru-only', 'uriRU' => 'ru-only', 'uriRO' => null, 'isShown' => 1, 'source_guid' => null, 'is_gift' => 0),
    );

    public function find_referral_route_products($categoryId, $locale, $uri)
    {
        $field = $locale === 'ru' ? 'uriRU' : 'uriRO';
        $matches = array_values(array_filter($this->rows, static function ($row) use ($categoryId, $field, $uri) {
            return (int) $row['category_id'] === (int) $categoryId && $row[$field] === $uri;
        }));
        return array_map(static function ($row) {
            return (object) $row;
        }, $matches);
    }
}

class ResolverTestCi
{
    public $load;
    public $config;
    public $menu_model;
    public $categories_model;
    public $brands_model;
    public $products_model;

    public function __construct($config)
    {
        $this->load = new ResolverTestLoader();
        $this->config = new ResolverTestConfig($config);
        $this->menu_model = new ResolverTestMenuModel();
        $this->categories_model = new ResolverTestCategoriesModel();
        $this->brands_model = new ResolverTestBrandsModel();
        $this->products_model = new ResolverTestProductsModel();
    }
}

function &get_instance()
{
    return $GLOBALS['resolver_test_ci'];
}

require_once dirname(__DIR__) . '/application/libraries/Referral_catalog_resolver.php';

class AuthTestOutput
{
    public $status = 200;
    public $body = '';
    public $headers = array();

    public function set_header($value) { $this->headers[] = $value; return $this; }
    public function set_status_header($status) { $this->status = (int) $status; return $this; }
    public function set_content_type($type, $charset = null) { return $this; }
    public function set_output($body) { $this->body = (string) $body; return $this; }
}

class AuthTestInput
{
    public function method() { return 'POST'; }
}

class AuthTestDb
{
    public $db_debug = true;
    public $conn_id = true;
    private $mode;
    private $lastError = array('code' => 1062);

    public function __construct($mode) { $this->mode = $mode; }
    public function initialize() { return true; }
    public function trans_begin() { return true; }
    public function trans_rollback() { return true; }
    public function trans_commit() { return true; }
    public function trans_status() { return true; }
    public function error() { return $this->lastError; }

    public function query($sql, $params = array())
    {
        if (strpos($sql, 'INSERT INTO referral_service_nonces') === 0 && $this->mode === 'replay') {
            return false;
        }
        if (strpos($sql, 'SELECT request_count') === 0) {
            return new AuthTestDbResult($this->mode === 'limited' ? 61 : 1);
        }
        return true;
    }
}

class AuthTestDbResult
{
    private $count;
    public function __construct($count) { $this->count = $count; }
    public function row() { return (object) array('request_count' => $this->count); }
}

class CI_Controller
{
    public $load;
    public $config;
    public $input;
    public $output;
    public $db;

    public function __construct()
    {
        $this->load = new ResolverTestLoader();
        $this->config = new ResolverTestConfig($GLOBALS['auth_test_config']);
        $this->input = new AuthTestInput();
        $this->output = new AuthTestOutput();
        $this->db = $GLOBALS['auth_test_db'];
    }
}

require_once dirname(__DIR__) . '/application/controllers/Internal_referrals.php';

$GLOBALS['resolver_test_ci'] = new ResolverTestCi(array('referral' => array(
    'canonical_origin' => 'https://vizaje-nica.com',
    'allowed_hosts' => array('vizaje-nica.com'),
    'catalog_page_id' => 3,
)));

$testCount = 0;
$failures = array();

function check($condition, $name)
{
    global $testCount, $failures;
    $testCount++;
    if (!$condition) {
        $failures[] = $name;
        fwrite(STDERR, "FAIL: {$name}\n");
    } else {
        fwrite(STDOUT, "PASS: {$name}\n");
    }
}

function resolverRequest($target, $type, $locale)
{
    $resolver = new Referral_catalog_resolver();
    return $resolver->resolve((object) array(
        'target' => $target,
        'expectedType' => $type,
        'requestedLocale' => $locale,
    ));
}

function expectResolverError($target, $type, $locale, $status, $code, $name)
{
    try {
        resolverRequest($target, $type, $locale);
        check(false, $name . ' (expected exception)');
    } catch (Referral_catalog_resolver_error $exception) {
        check($exception->httpStatus === $status && $exception->errorCode === $code, $name);
    }
}

try {
    $ruHome = resolverRequest('/ru', 'homepage', 'ru');
    $roHome = resolverRequest('/ro', 'homepage', 'ro');
    check($ruHome['targets']['ru']['path'] === '/ru/' && $ruHome['targets']['ro']['path'] === '/ro/', 'RU homepage canonical paths');
    check($roHome['fallback']['locale'] === 'ro', 'RO homepage fallback locale');

    $ruBrand = resolverRequest('/ru/katalog/anastasia-beverly-hills', 'brand', 'ru');
    $roBrand = resolverRequest('/ro/catalog/anastasia-beverly-hills', 'brand', 'ro');
    check($ruBrand['entity']['id'] === '201' && $roBrand['entity']['id'] === '201', 'brand resolves same stable ID in RU and RO');
    check($ruBrand['targets']['ru']['path'] === '/ru/katalog/anastasia-beverly-hills'
        && $ruBrand['targets']['ro']['path'] === '/ro/catalog/anastasia-beverly-hills', 'brand canonical paths use localized catalog routes');

    $ruCategory = resolverRequest('/ru/katalog/teni', 'category', 'ru');
    $roCategory = resolverRequest('/ro/catalog/farduri', 'category', 'ro');
    check($ruCategory['entity']['id'] === '101' && $roCategory['entity']['id'] === '101', 'category resolves same stable ID in RU and RO');
    check($ruCategory['targets']['ru']['path'] === '/ru/katalog/teni'
        && $ruCategory['targets']['ro']['path'] === '/ro/catalog/farduri', 'category canonical paths are independently localized');

    $ruProduct = resolverRequest('/ru/katalog/teni/111157', 'product', 'ru');
    $roProduct = resolverRequest('/ro/catalog/farduri/produs-111157', 'product', 'ro');
    check($ruProduct['entity']['id'] === '901' && $roProduct['entity']['id'] === '901', 'product resolves same stable ID in RU and RO');
    check($ruProduct['entity']['sourceGuid'] === 'fixture-guid'
        && $ruProduct['targets']['ro']['path'] === '/ro/catalog/farduri/produs-111157', 'product URI and localized canonical URL');
    check(resolverRequest('/ru/katalog/teni/111157', 'product', 'ru')['entity']['id'] !== '111157', 'numeric product URI is never treated as product ID');

    $collision = resolverRequest('/ru/katalog/collision', 'category', 'ru');
    check($collision['entity']['type'] === 'category' && $collision['entity']['id'] === '103', 'visible category wins category/brand slug collision');
    expectResolverError('/ru/katalog/collision', 'brand', 'ru', 409, 'TYPE_MISMATCH', 'colliding brand request returns TYPE_MISMATCH');
    check(resolverRequest('/ru/katalog/hidden-brand-uri', 'brand', 'ru')['entity']['id'] === '203', 'hidden category does not shadow a visible brand');

    expectResolverError('/ru/katalog/no-such-entity', 'category', 'ru', 404, 'ENTITY_NOT_FOUND', 'nonexistent catalog entity returns 404');
    expectResolverError('/ru/katalog/teni', 'product', 'ru', 409, 'TYPE_MISMATCH', 'wrong expected type returns 409');
    expectResolverError('/ru/katalog/ambiguous', 'category', 'ru', 409, 'AMBIGUOUS_TARGET', 'ambiguous category URI returns 409');
    expectResolverError('/ru/katalog/teni', 'category', 'ro', 409, 'LOCALE_MISMATCH', 'category locale mismatch returns 409');
    expectResolverError('/katalog/teni', 'category', 'ru', 422, 'UNSUPPORTED_ROUTE', 'missing locale returns unsupported route');

    $ruOnly = resolverRequest('/ru/katalog/teni/ru-only', 'product', 'ru');
    check($ruOnly['targets']['ru']['available'] === true && $ruOnly['targets']['ro']['available'] === false
        && $ruOnly['fallback']['locale'] === 'ru', 'missing alternate product URI uses available locale fallback');
} catch (Throwable $exception) {
    check(false, 'resolver test setup/execution: ' . get_class($exception) . ' ' . $exception->getMessage());
}

function authRequest($mode, $validSignature)
{
    $secret = str_repeat('test-only-key-', 3);
    $secret = substr($secret, 0, 32);
    $keyId = 'test-key';
    $timestamp = (string) time();
    $rawBody = '';
    $nonce = rtrim(strtr(base64_encode(str_repeat($mode === 'replay' ? "R" : "L", 16)), '+/', '-_'), '=');
    $canonical = implode("\n", array(
        'VP-HMAC-SHA256-V1', $keyId, 'POST', '/internal/v1/referrals/resolve-target',
        $timestamp, $nonce, hash('sha256', $rawBody),
    ));
    $signature = $validSignature ? hash_hmac('sha256', $canonical, $secret) : str_repeat('0', 64);

    $_SERVER = array(
        'REQUEST_URI' => '/internal/v1/referrals/resolve-target',
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_VP_KEY_ID' => $keyId,
        'HTTP_X_VP_TIMESTAMP' => $timestamp,
        'HTTP_X_VP_NONCE' => $nonce,
        'HTTP_X_VP_SIGNATURE' => $signature,
    );
    $GLOBALS['auth_test_db'] = new AuthTestDb($mode);
    $GLOBALS['auth_test_config'] = array('referral' => array(
        'hmac_keyring' => array($keyId => array(
            'principal' => 'vizaje-partners',
            'secretBase64' => base64_encode($secret),
        )),
        'max_clock_skew_seconds' => 60,
        'nonce_retention_seconds' => 900,
        'resolver_requests_per_minute' => 60,
    ));

    $controller = new Internal_referrals();
    $controller->resolve_target();
    return array($controller->output->status, json_decode($controller->output->body, true));
}

try {
    list($invalidStatus, $invalidBody) = authRequest('invalid', false);
    check($invalidStatus === 401 && $invalidBody['error']['code'] === 'SERVICE_AUTH_INVALID', 'invalid HMAC is rejected');

    list($replayStatus, $replayBody) = authRequest('replay', true);
    check($replayStatus === 409 && $replayBody['error']['code'] === 'AUTH_NONCE_REPLAY', 'duplicate nonce is rejected');

    list($limitedStatus, $limitedBody) = authRequest('limited', true);
    check($limitedStatus === 429 && $limitedBody['error']['code'] === 'RATE_LIMITED', 'rate limit response is preserved');
} catch (Throwable $exception) {
    check(false, 'auth test setup/execution: ' . get_class($exception) . ' ' . $exception->getMessage());
}

global $failures;
fwrite(STDOUT, sprintf("%d tests, %d failures\n", $testCount, count($failures)));
exit(count($failures) === 0 ? 0 : 1);
