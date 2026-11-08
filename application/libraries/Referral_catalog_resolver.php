<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Referral_catalog_resolver
{
    private $CI;
    private $settings;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->config('referral');
        $this->settings = $this->CI->config->item('referral');
        $this->CI->load->model('products_model');
        $this->CI->load->model('categories_model');
        $this->CI->load->model('brands_model');
        $this->CI->load->model('menu_model');
    }

    public function resolve($request)
    {
        $locale = $request->requestedLocale;
        $expectedType = $request->expectedType;
        $target = $this->parseTarget($request->target);
        $route = $this->inferRoute($target['segments']);

        if ($route['type'] === 'homepage') {
            if ($expectedType !== 'homepage') {
                $this->fail(409, 'TYPE_MISMATCH', 'The URL does not match the requested entity type.', false);
            }
            return $this->homepageResponse($locale);
        }

        if ($route['type'] === 'catalog_leaf') {
            $inputLocale = $route['locale'];
            if (!$this->getPage($this->settings['catalog_page_id'], $inputLocale)) {
                $this->fail(410, 'ENTITY_UNAVAILABLE', 'The requested catalog page is unavailable.', false);
            }

            $entity = $this->resolveCatalogLeaf($route['segments'][2], $inputLocale, $expectedType);
            if ($entity['type'] !== 'brand' && $inputLocale !== $locale) {
                $this->fail(409, 'LOCALE_MISMATCH', 'The URL language does not match the requested language.', false);
            }

            return $entity['type'] === 'category'
                ? $this->categoryResponse($entity['record'], $locale)
                : $this->resolveBrand($entity['record'], $locale);
        }

        if ($route['type'] !== $expectedType) {
            $this->fail(409, 'TYPE_MISMATCH', 'The URL does not match the requested entity type.', false);
        }

        if ($route['locale'] !== $locale) {
            $this->fail(409, 'LOCALE_MISMATCH', 'The URL language does not match the requested language.', false);
        }

        $inputLocale = $route['locale'];
        if (!$this->getPage($this->settings['catalog_page_id'], $inputLocale)) {
            $this->fail(410, 'ENTITY_UNAVAILABLE', 'The requested catalog page is unavailable.', false);
        }

        $categoryUri = $route['segments'][2];
        $category = $this->findCategory($categoryUri, $inputLocale);

        if ($route['type'] === 'category') {
            return $this->categoryResponse($category, $locale);
        }

        return $this->resolveProduct($category, $route['segments'][3], $inputLocale, $locale);
    }

    private function parseTarget($target)
    {
        if (!is_string($target) || $target === '' || strlen($target) > 2048 || trim($target) !== $target) {
            $this->fail(400, 'INVALID_TARGET', 'The target URL is invalid.', false);
        }

        $canonicalOrigin = isset($this->settings['canonical_origin'])
            ? rtrim((string) $this->settings['canonical_origin'], '/')
            : '';
        $canonical = $canonicalOrigin !== '' ? parse_url($canonicalOrigin) : false;
        $canonicalScheme = is_array($canonical) && isset($canonical['scheme'])
            ? strtolower((string) $canonical['scheme'])
            : '';
        $canonicalPort = is_array($canonical) && isset($canonical['port'])
            ? (int) $canonical['port']
            : null;
        $canonicalDefaultPort = $canonicalPort === null
            || ($canonicalScheme === 'https' && $canonicalPort === 443)
            || ($canonicalScheme === 'http' && $canonicalPort === 80);
        if (!is_array($canonical)
            || !in_array($canonicalScheme, array('http', 'https'), true)
            || empty($canonical['host'])
            || isset($canonical['user'])
            || isset($canonical['pass'])
            || isset($canonical['query'])
            || isset($canonical['fragment'])
            || !$canonicalDefaultPort
            || (isset($canonical['path']) && $canonical['path'] !== '' && $canonical['path'] !== '/')
            || !in_array(strtolower(rtrim($canonical['host'], '.')), (array) $this->settings['allowed_hosts'], true)
            || (ENVIRONMENT === 'production' && $canonicalScheme !== 'https')) {
            $this->fail(503, 'LEGACY_TEMPORARILY_UNAVAILABLE', 'The resolver is not configured.', true);
        }

        $isRelative = isset($target[0]) && $target[0] === '/';
        if ($isRelative) {
            if (substr($target, 0, 2) === '//') {
                $this->fail(400, 'INVALID_TARGET', 'The target URL is invalid.', false);
            }
            $path = $target;
        } else {
            $parts = parse_url($target);
            if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
                $this->fail(400, 'INVALID_TARGET', 'The target URL is invalid.', false);
            }
            if (isset($parts['user']) || isset($parts['pass']) || array_key_exists('query', $parts) || array_key_exists('fragment', $parts)) {
                $this->fail(400, 'INVALID_TARGET', 'The target URL contains a forbidden component.', false);
            }

            $scheme = strtolower($parts['scheme']);
            $host = strtolower(rtrim($parts['host'], '.'));
            $port = isset($parts['port']) ? (int) $parts['port'] : null;
            $defaultPort = ($scheme === 'https' && $port === 443)
                || ($scheme === 'http' && $port === 80);
            if ($port !== null && !$defaultPort) {
                $this->fail(400, 'INVALID_TARGET', 'The target URL uses a forbidden port.', false);
            }
            if (!in_array($host, (array) $this->settings['allowed_hosts'], true)) {
                $this->fail(400, 'HOST_NOT_ALLOWED', 'The target host is not an approved Vizaje-Nica host.', false);
            }
            if ($scheme !== 'https'
                && (ENVIRONMENT === 'production'
                    || $scheme !== $canonicalScheme)) {
                $this->fail(400, 'INVALID_TARGET', 'The target URL must use HTTPS.', false);
            }
            if ($scheme !== 'http' && $scheme !== 'https') {
                $this->fail(400, 'INVALID_TARGET', 'The target URL scheme is not supported.', false);
            }
            $path = isset($parts['path']) && $parts['path'] !== '' ? $parts['path'] : '/';
        }

        if ($path === '' || $path[0] !== '/' || strpos($path, '\\') !== false
            || preg_match('/[\x00-\x20\x7f]/', $path)
            || preg_match('/%(?![0-9a-f]{2})/i', $path)
            || preg_match('/%(?:2f|5c)/i', $path)) {
            $this->fail(400, 'INVALID_TARGET', 'The target path is invalid.', false);
        }

        $decodedPath = rawurldecode($path);
        if (strpos($decodedPath, '\\') !== false
            || strpos($decodedPath, '?') !== false
            || strpos($decodedPath, '#') !== false
            || preg_match('/[\x00-\x20\x7f]/', $decodedPath)
            || preg_match('/%(?:[0-9a-f]{2})/i', $decodedPath)) {
            $this->fail(400, 'INVALID_TARGET', 'The target path contains an invalid encoded component.', false);
        }

        if ($decodedPath !== '/' && strpos($decodedPath, '//') !== false) {
            $this->fail(400, 'INVALID_TARGET', 'The target path is invalid.', false);
        }

        $segments = $decodedPath === '/' ? array() : explode('/', trim($decodedPath, '/'));
        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                $this->fail(400, 'INVALID_TARGET', 'The target path contains an invalid segment.', false);
            }
        }

        return array('path' => $decodedPath, 'segments' => $segments);
    }

    private function inferRoute($segments)
    {
        if (count($segments) === 0) {
            return array('type' => 'homepage', 'locale' => null, 'segments' => $segments);
        }

        if (!in_array($segments[0], array('ru', 'ro'), true)) {
            $this->fail(422, 'UNSUPPORTED_ROUTE', 'The target route is not supported.', false);
        }
        $locale = $segments[0];

        if (count($segments) === 1) {
            return array('type' => 'homepage', 'locale' => $locale, 'segments' => $segments);
        }

        if (in_array($segments[1], array('katalog', 'catalog'), true)) {
            if (count($segments) === 3) {
                // Legacy routes this shared URL shape through Catalog::category():
                // a visible category is checked first, then Catalog::brands().
                return array('type' => 'catalog_leaf', 'locale' => $locale, 'segments' => $segments);
            }
            if (count($segments) === 4) {
                return array('type' => 'product', 'locale' => $locale, 'segments' => $segments);
            }
        }

        $this->fail(422, 'UNSUPPORTED_ROUTE', 'The target route is not supported.', false);
    }

    /**
     * Resolve the shared /{locale}/{catalog-uri}/{slug} route in the same
     * order as Catalog::category(): visible category first, then brand.
     */
    private function resolveCatalogLeaf($uri, $locale, $expectedType)
    {
        $categoryRows = $this->CI->categories_model->find_referral_route_categories($locale, $uri);
        $visibleCategories = array_values(array_filter($categoryRows, static function ($row) {
            return isset($row->isShown) && (int) $row->isShown === 1;
        }));

        if (count($visibleCategories) > 1) {
            $this->fail(409, 'AMBIGUOUS_TARGET', 'The target route matches multiple categories.', false);
        }
        if (count($visibleCategories) === 1) {
            if ($expectedType !== 'category') {
                $this->fail(409, 'TYPE_MISMATCH', 'The public route resolves to a category, not the requested type.', false);
            }
            return array('type' => 'category', 'record' => $visibleCategories[0]);
        }

        // Hidden categories do not shadow brands: the public category lookup
        // filters isShown=1 and falls through to Catalog::brands() otherwise.
        $brandRows = $this->CI->brands_model->find_referral_route_brands($uri);
        $visibleBrands = array_values(array_filter($brandRows, static function ($row) {
            return isset($row->isShown) && (int) $row->isShown === 1;
        }));

        if (count($visibleBrands) > 1) {
            $this->fail(409, 'AMBIGUOUS_TARGET', 'The target route matches multiple brands.', false);
        }
        if (count($visibleBrands) === 1) {
            if ($expectedType !== 'brand') {
                $this->fail(409, 'TYPE_MISMATCH', 'The public route resolves to a brand, not the requested type.', false);
            }
            return array('type' => 'brand', 'record' => $visibleBrands[0]);
        }

        if (count($categoryRows) > 0 || count($brandRows) > 0) {
            $this->fail(410, 'ENTITY_UNAVAILABLE', 'The catalog entity is not currently available.', false);
        }
        $this->fail(404, 'ENTITY_NOT_FOUND', 'No catalog entity matches this URL.', false);
    }

    private function findCategory($categoryUri, $locale)
    {
        $rows = $this->CI->categories_model->find_referral_route_categories($locale, $categoryUri);
        $visible = array_values(array_filter($rows, static function ($row) {
            return isset($row->isShown) && (int) $row->isShown === 1;
        }));

        if (count($visible) > 1) {
            $this->fail(409, 'AMBIGUOUS_TARGET', 'The target route matches multiple categories.', false);
        }
        if (count($visible) === 0) {
            if (count($rows) > 0) {
                $this->fail(410, 'ENTITY_UNAVAILABLE', 'The category is not currently available.', false);
            }
            $this->fail(404, 'ENTITY_NOT_FOUND', 'No catalog entity matches this URL.', false);
        }

        return $visible[0];
    }

    private function resolveProduct($category, $productUri, $inputLocale, $requestedLocale)
    {
        $rows = $this->CI->products_model->find_referral_route_products(
            $category->id,
            $inputLocale,
            $productUri
        );
        $visible = array_values(array_filter($rows, static function ($row) {
            return isset($row->isShown) && (int) $row->isShown === 1
                && (!isset($row->is_gift) || (int) $row->is_gift !== 1);
        }));

        if (count($visible) > 1) {
            $this->fail(409, 'AMBIGUOUS_TARGET', 'The target route matches multiple products.', false);
        }
        if (count($visible) === 0) {
            if (count($rows) > 0) {
                $this->fail(410, 'ENTITY_UNAVAILABLE', 'The product is not currently available.', false);
            }
            $this->fail(404, 'ENTITY_NOT_FOUND', 'No catalog entity matches this URL.', false);
        }

        $product = $visible[0];
        $categoryTargets = $this->localizedTargets(
            $category,
            isset($product->uriRU) ? $product->uriRU : null,
            isset($product->uriRO) ? $product->uriRO : null,
            true
        );

        return $this->success(
            array(
                'type' => 'product',
                'id' => (string) $product->id,
                'sourceGuid' => isset($product->source_guid) && trim((string) $product->source_guid) !== ''
                    ? (string) $product->source_guid
                    : null,
            ),
            $requestedLocale,
            $categoryTargets
        );
    }

    private function categoryResponse($category, $requestedLocale)
    {
        $targets = $this->localizedTargets($category, null, null);
        return $this->success(
            array('type' => 'category', 'id' => (string) $category->id),
            $requestedLocale,
            $targets
        );
    }

    private function resolveBrand($brand, $requestedLocale)
    {
        $paths = array();
        foreach (array('ru', 'ro') as $locale) {
            $page = $this->getPage($this->settings['catalog_page_id'], $locale);
            $pageUri = $page ? $this->pageUri($page, $locale) : null;
            if ($pageUri === null || !$this->validSegment($brand->uri)) {
                $paths[$locale] = $this->unavailableTarget();
                continue;
            }
            $paths[$locale] = $this->availableTarget(
                $this->joinPath($locale, $pageUri, $brand->uri)
            );
        }

        return $this->success(
            array('type' => 'brand', 'id' => (string) $brand->id),
            $requestedLocale,
            $paths
        );
    }

    private function homepageResponse($requestedLocale)
    {
        $targets = array(
            'ru' => $this->availableTarget('/ru/'),
            'ro' => $this->availableTarget('/ro/'),
        );
        return $this->success(
            array('type' => 'homepage', 'id' => 'main'),
            $requestedLocale,
            $targets
        );
    }

    /**
     * Build independent locale paths from current Legacy records.
     * $uriRu/$uriRo are null for category targets and localized product URIs otherwise.
     */
    private function localizedTargets($category, $uriRu, $uriRo, $isProduct = false)
    {
        $targets = array();
        foreach (array('ru' => $uriRu, 'ro' => $uriRo) as $locale => $entityUri) {
            $page = $this->getPage($this->settings['catalog_page_id'], $locale);
            $pageUri = $page ? $this->pageUri($page, $locale) : null;
            $categoryUri = $locale === 'ru' ? $category->uriRU : $category->uriRO;

            if ($pageUri === null || !$this->validSegment($categoryUri)
                || ($isProduct && ($entityUri === null || !$this->validSegment($entityUri)))
                || (!$isProduct && $entityUri !== null && !$this->validSegment($entityUri))) {
                $targets[$locale] = $this->unavailableTarget();
                continue;
            }

            $path = $isProduct
                ? $this->joinPath($locale, $pageUri, $categoryUri, $entityUri)
                : $this->joinPath($locale, $pageUri, $categoryUri);
            $targets[$locale] = $this->availableTarget($path);
        }
        return $targets;
    }

    private function success($entity, $requestedLocale, $targets)
    {
        foreach (array('ru', 'ro') as $locale) {
            if (!isset($targets[$locale])) {
                $targets[$locale] = $this->unavailableTarget();
            }
        }

        if (!$targets[$requestedLocale]['available']) {
            $fallbackLocale = $requestedLocale === 'ru' ? 'ro' : 'ru';
            if (!$targets[$fallbackLocale]['available']) {
                $this->fail(422, 'LOCALE_UNAVAILABLE', 'No current language URL is available for this entity.', false);
            }
        } else {
            $fallbackLocale = $requestedLocale;
        }

        return array(
            'contractVersion' => 1,
            'entity' => $entity,
            'requestedLocale' => $requestedLocale,
            'targets' => $targets,
            'fallback' => array(
                'locale' => $fallbackLocale,
                'path' => $targets[$fallbackLocale]['path'],
            ),
            'resolvedAt' => gmdate('Y-m-d\TH:i:s\Z'),
        );
    }

    private function getPage($pageId, $locale)
    {
        return $this->CI->menu_model->get_page_data_by_id(strtoupper($locale), (int) $pageId);
    }

    private function pageUri($page, $locale)
    {
        $field = $locale === 'ru' ? 'uriRU' : 'uriRO';
        $value = isset($page->{$field}) ? trim((string) $page->{$field}, '/') : '';
        return $this->validSegment($value) ? $value : null;
    }

    private function validSegment($segment)
    {
        if (!is_string($segment) || $segment === '' || trim($segment) !== $segment
            || strpos($segment, '/') !== false || strpos($segment, '\\') !== false
            || preg_match('/[\x00-\x20\x7f?#]/', $segment)
            || $segment === '.' || $segment === '..') {
            return false;
        }
        return true;
    }

    private function joinPath()
    {
        $segments = func_get_args();
        $encoded = array();
        foreach ($segments as $segment) {
            $encoded[] = rawurlencode((string) $segment);
        }
        return '/' . implode('/', $encoded);
    }

    private function availableTarget($path)
    {
        return array(
            'available' => true,
            'url' => rtrim($this->settings['canonical_origin'], '/') . $path,
            'path' => $path,
        );
    }

    private function unavailableTarget()
    {
        return array('available' => false, 'url' => null, 'path' => null);
    }

    private function fail($status, $code, $message, $retryable)
    {
        throw new Referral_catalog_resolver_error($status, $code, $message, $retryable);
    }
}

class Referral_catalog_resolver_error extends RuntimeException
{
    public $httpStatus;
    public $errorCode;
    public $safeMessage;
    public $retryable;

    public function __construct($httpStatus, $errorCode, $safeMessage, $retryable)
    {
        parent::__construct($safeMessage);
        $this->httpStatus = (int) $httpStatus;
        $this->errorCode = (string) $errorCode;
        $this->safeMessage = (string) $safeMessage;
        $this->retryable = (bool) $retryable;
    }
}
