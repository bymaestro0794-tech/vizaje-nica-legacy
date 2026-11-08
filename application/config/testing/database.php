<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Isolated developer/staging database connection.
 *
 * This file is selected only when CI_ENV=testing. It intentionally does not
 * fall back to the default or app connection from config/database.php.
 */
$devDatabaseEnv = static function ($key, $default = '') {
    $value = getenv($key);
    if ($value !== false) {
        return $value;
    }

    if (isset($_SERVER[$key])) {
        return $_SERVER[$key];
    }

    if (isset($_ENV[$key])) {
        return $_ENV[$key];
    }

    return $default;
};

$active_group = 'default';
$query_builder = TRUE;

$db['default'] = array(
    'dsn' => '',
    'hostname' => $devDatabaseEnv('VP_DEV_DB_HOST', '127.0.0.1'),
    'username' => $devDatabaseEnv('VP_DEV_DB_USER'),
    'password' => $devDatabaseEnv('VP_DEV_DB_PASSWORD'),
    'database' => $devDatabaseEnv('VP_DEV_DB_NAME'),
    'port' => (int) $devDatabaseEnv('VP_DEV_DB_PORT', '3306'),
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => FALSE,
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8',
    'dbcollat' => 'utf8_general_ci',
    'swap_pre' => '',
    'encrypt' => FALSE,
    'compress' => FALSE,
    'stricton' => FALSE,
    'failover' => array(),
    'save_queries' => FALSE,
);
