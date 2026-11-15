<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Loads Referral Analytics settings shared by HTTP requests and CLI workers.
 * The caller supplies the private path; it is never read from request data.
 */
class Partners_referral_analytics_config_loader
{
    public static function load($privatePath, $envReader = null, $environment = null, $documentRoot = null)
    {
        if (!is_callable($envReader)) {
            $envReader = array(__CLASS__, 'readEnvironment');
        }
        if (!is_string($environment) || $environment === '') {
            $environment = defined('ENVIRONMENT') ? ENVIRONMENT : 'production';
        }
        if (!is_string($documentRoot) || $documentRoot === '') {
            $documentRoot = defined('FCPATH') ? FCPATH : '';
        }

        $settings = self::fromEnvironment($envReader, $environment);

        if (!self::isAbsolutePath($privatePath)) {
            return $settings;
        }

        $exists = @file_exists($privatePath);
        if (!$exists) {
            // Preserve deployments that still provide the existing environment variables.
            return $settings;
        }

        $resolvedPath = @realpath($privatePath);
        if (!is_string($resolvedPath)
            || !@is_file($resolvedPath)
            || @is_link($privatePath)
            || !@is_readable($resolvedPath)
            || self::isInsideDocumentRoot($resolvedPath, $documentRoot)) {
            return self::disabledSettings();
        }

        try {
            $privateSettings = self::includePrivateFile($resolvedPath);
            return self::fromPrivateFile($privateSettings, $environment);
        } catch (Throwable $exception) {
            // Configuration errors must never expose paths, key IDs, or secret material.
            return self::disabledSettings();
        }
    }

    public static function readEnvironment($name, $default = '')
    {
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
    }

    private static function fromEnvironment($envReader, $environment)
    {
        $enabledValue = strtolower(trim((string) call_user_func(
            $envReader,
            'PARTNERS_REFERRAL_ANALYTICS_ENABLED',
            'false'
        )));
        $apiUrl = self::normalizeApiUrl(
            call_user_func($envReader, 'PARTNERS_REFERRAL_ANALYTICS_API_URL', ''),
            $environment
        );

        $keysJson = (string) call_user_func($envReader, 'LEGACY_ANALYTICS_HMAC_KEYS', '');
        $enabled = $enabledValue === 'true';
        if ($enabled && ($apiUrl === '' || !self::isValidKeyring($keysJson))) {
            return self::disabledSettings();
        }

        return array(
            'enabled' => $enabled,
            'api_origin' => $apiUrl,
            'hmac_keys_json' => $keysJson,
        );
    }

    private static function fromPrivateFile($privateSettings, $environment)
    {
        if (!is_array($privateSettings)
            || !array_key_exists('enabled', $privateSettings)
            || !is_bool($privateSettings['enabled'])
            || !array_key_exists('api_url', $privateSettings)
            || !is_string($privateSettings['api_url'])
            || !array_key_exists('hmac_keys', $privateSettings)
            || !is_array($privateSettings['hmac_keys'])) {
            return self::disabledSettings();
        }

        $apiUrl = self::normalizeApiUrl($privateSettings['api_url'], $environment);
        $keysJson = json_encode($privateSettings['hmac_keys'], JSON_UNESCAPED_SLASHES);
        if (!is_string($keysJson)) {
            return self::disabledSettings();
        }

        if ($privateSettings['enabled']) {
            if ($apiUrl === '' || !self::isValidKeyring($keysJson)) {
                return self::disabledSettings();
            }
        } elseif ($privateSettings['hmac_keys'] !== array() && !self::isValidKeyring($keysJson)) {
            return self::disabledSettings();
        }

        return array(
            'enabled' => $privateSettings['enabled'],
            'api_origin' => $apiUrl,
            'hmac_keys_json' => $keysJson,
        );
    }

    private static function includePrivateFile($path)
    {
        $hadError = false;
        $hadOutput = false;
        $value = null;

        set_error_handler(function () use (&$hadError) {
            // Do not leak filesystem paths or PHP warnings into a response or log.
            $hadError = true;
            return true;
        });
        ob_start();
        try {
            $value = include $path;
        } finally {
            $output = ob_get_clean();
            $hadOutput = is_string($output) && $output !== '';
            restore_error_handler();
        }

        if ($hadOutput || $hadError) {
            throw new RuntimeException('Private analytics configuration could not be loaded.');
        }

        return $value;
    }

    private static function normalizeApiUrl($value, $environment)
    {
        $apiUrl = is_string($value) ? rtrim(trim($value), '/') : '';
        if ($apiUrl === '') {
            return '';
        }

        $parsed = parse_url($apiUrl);
        if (!is_array($parsed)
            || !isset($parsed['scheme'], $parsed['host'])
            || isset($parsed['user'], $parsed['pass'], $parsed['query'], $parsed['fragment'])
            || (isset($parsed['path']) && $parsed['path'] !== '' && $parsed['path'] !== '/')) {
            return '';
        }

        $scheme = strtolower($parsed['scheme']);
        $host = strtolower($parsed['host']);
        $isHttps = $scheme === 'https';
        $isLocalHttp = $scheme === 'http'
            && $environment !== 'production'
            && in_array($host, array('localhost', '127.0.0.1', '::1'), true);
        if (!$isHttps && !$isLocalHttp) {
            return '';
        }

        return $scheme . '://' . $host . (isset($parsed['port']) ? ':' . (int) $parsed['port'] : '');
    }

    private static function isValidKeyring($keysJson)
    {
        $protocolPath = defined('APPPATH') ? APPPATH . 'libraries/Partners_referral_analytics_protocol.php' : '';
        if ($protocolPath === '' || !is_file($protocolPath)) {
            return false;
        }

        require_once $protocolPath;
        if (!class_exists('Partners_referral_analytics_protocol')) {
            return false;
        }

        try {
            Partners_referral_analytics_protocol::parseKeyring($keysJson);
            return true;
        } catch (Throwable $exception) {
            return false;
        }
    }

    private static function isInsideDocumentRoot($path, $documentRoot)
    {
        if (!is_string($documentRoot) || trim($documentRoot) === '') {
            return false;
        }

        $root = @realpath($documentRoot);
        if (!is_string($root)) {
            return false;
        }

        $root = rtrim(str_replace('\\', '/', $root), '/') . '/';
        $candidate = str_replace('\\', '/', $path);

        return stripos($candidate, $root) === 0;
    }

    private static function isAbsolutePath($path)
    {
        return is_string($path)
            && ($path !== '')
            && ($path[0] === '/' || preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1);
    }

    private static function disabledSettings()
    {
        return array(
            'enabled' => false,
            'api_origin' => '',
            'hmac_keys_json' => '',
        );
    }
}
