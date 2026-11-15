<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Private, fail-closed configuration for the independent creator API client. */
class Partners_creator_config_loader
{
    public static function load($envReader = null, $environment = null, $privatePath = null, $documentRoot = null)
    {
        if (!is_callable($envReader)) $envReader = array(__CLASS__, 'readEnvironment');
        if (!is_string($environment) || $environment === '') $environment = defined('ENVIRONMENT') ? ENVIRONMENT : 'production';
        if (!is_string($documentRoot) || $documentRoot === '') $documentRoot = defined('FCPATH') ? FCPATH : '';
        if ($privatePath === null) $privatePath = call_user_func($envReader, 'LEGACY_PARTNERS_PRIVATE_CONFIG', '');

        if (is_string($privatePath) && $privatePath !== '') {
            if (!self::isAbsolute($privatePath)) return self::disabled();
            // An explicitly configured private file is authoritative. If it is
            // missing or unreadable, do not silently fall back to environment keys.
            $resolved = @realpath($privatePath);
            if (!is_string($resolved) || !@is_file($resolved) || @is_link($privatePath)
                || !@is_readable($resolved) || self::insideDocumentRoot($resolved, $documentRoot)) return self::disabled();
            try {
                $settings = self::includePrivate($resolved);
                return self::normalize($settings, $environment);
            } catch (Throwable $error) {
                return self::disabled();
            }
            return self::disabled();
        }

        return self::fromEnvironment($envReader, $environment);
    }

    public static function readEnvironment($name, $default = '')
    {
        $value = getenv($name);
        if ($value !== false) return $value;
        if (isset($_SERVER[$name])) return $_SERVER[$name];
        if (isset($_ENV[$name])) return $_ENV[$name];
        return $default;
    }

    private static function fromEnvironment($env, $environment)
    {
        $hmac = (string) call_user_func($env, 'LEGACY_TO_PARTNERS_HMAC_KEYS', '');
        $encryption = json_decode((string) call_user_func($env, 'LEGACY_PARTNERS_GRANT_ENCRYPTION_KEYS', ''), true);
        return self::normalize(array(
            'enabled' => strtolower(trim((string) call_user_func($env, 'PARTNERS_CREATOR_INTEGRATION_ENABLED', 'false'))) === 'true',
            'api_url' => (string) call_user_func($env, 'PARTNERS_API_URL', ''),
            'partner_origin' => (string) call_user_func($env, 'PARTNERS_WEB_URL', 'https://partners.vizaje-nica.com'),
            'redirect_uri' => (string) call_user_func($env, 'LEGACY_ACCOUNT_LINK_REDIRECT_URI', ''),
            'hmac_keys' => json_decode($hmac, true),
            'grant_encryption_keys' => $encryption,
        ), $environment);
    }

    private static function normalize($settings, $environment)
    {
        if (!is_array($settings) || !array_key_exists('enabled', $settings) || !is_bool($settings['enabled'])) return self::disabled();
        $api = self::normalizeOrigin($settings['api_url'] ?? '', $environment);
        $partner = self::normalizeOrigin($settings['partner_origin'] ?? '', $environment);
        $redirect = self::normalizeRedirect($settings['redirect_uri'] ?? '', $environment);
        try {
            $hmacKeys = Partners_creator_protocol::parseKeyring(json_encode($settings['hmac_keys'] ?? null, JSON_UNESCAPED_SLASHES));
            $encryptionKeys = Partners_creator_protocol::parseEncryptionKeys($settings['grant_encryption_keys'] ?? null);
        } catch (Throwable $error) {
            return self::disabled();
        }
        if ($settings['enabled'] && ($api === '' || $partner === '' || $redirect === '')) return self::disabled();
        if ($settings['enabled'] && (!$hmacKeys || !$encryptionKeys)) return self::disabled();
        return array(
            'enabled' => $settings['enabled'], 'api_origin' => $api, 'partner_origin' => $partner,
            'redirect_uri' => $redirect, 'hmac_keys' => $hmacKeys, 'grant_encryption_keys' => $encryptionKeys,
        );
    }

    private static function normalizeOrigin($value, $environment)
    {
        if (!is_string($value) || trim($value) === '') return '';
        $value = rtrim(trim($value), '/');
        $parts = parse_url($value);
        if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host']) || isset($parts['user']) || isset($parts['pass'])
            || isset($parts['query']) || isset($parts['fragment']) || (isset($parts['path']) && $parts['path'] !== '')) return '';
        $scheme = strtolower($parts['scheme']); $host = strtolower($parts['host']);
        $localHttp = $scheme === 'http' && $environment !== 'production'
            && (in_array($host, array('localhost', '127.0.0.1', '::1'), true) || substr($host, -6) === '.local');
        if ($scheme !== 'https' && !$localHttp) return '';
        return $scheme . '://' . $host . (isset($parts['port']) ? ':' . (int) $parts['port'] : '');
    }

    private static function normalizeRedirect($value, $environment)
    {
        if (!is_string($value)) return '';
        $parts = parse_url(trim($value));
        if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host']) || empty($parts['path'])
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])
            || $parts['path'] !== '/account/creator-link/callback') return '';
        $origin = self::normalizeOrigin($parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . (int) $parts['port'] : ''), $environment);
        if ($origin === '') return '';
        return $origin . $parts['path'];
    }

    private static function includePrivate($path)
    {
        $hadError = false; $hadOutput = false; $value = null;
        set_error_handler(function () use (&$hadError) { $hadError = true; return true; });
        ob_start();
        try { $value = include $path; }
        finally { $out = ob_get_clean(); $hadOutput = is_string($out) && $out !== ''; restore_error_handler(); }
        if ($hadError || $hadOutput) throw new RuntimeException('Private configuration unavailable.');
        return $value;
    }

    private static function isAbsolute($path)
    {
        return is_string($path) && (substr($path, 0, 1) === '/' || preg_match('/^[A-Za-z]:[\\\\\/]/', $path));
    }

    private static function insideDocumentRoot($path, $root)
    {
        if (!is_string($root) || $root === '') return false;
        $realRoot = @realpath($root);
        if (!is_string($realRoot)) return false;
        $prefix = rtrim(str_replace('\\', '/', $realRoot), '/') . '/';
        return strpos(strtolower(str_replace('\\', '/', $path)) . '/', strtolower($prefix)) === 0;
    }

    private static function disabled()
    {
        return array('enabled' => false, 'api_origin' => '', 'partner_origin' => '', 'redirect_uri' => '', 'hmac_keys' => array(), 'grant_encryption_keys' => array());
    }
}
