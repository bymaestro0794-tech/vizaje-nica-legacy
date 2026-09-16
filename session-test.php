<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

header('Content-Type: text/plain; charset=utf-8');

echo 'PHP: ' . PHP_VERSION . PHP_EOL;
echo 'Session status before: ' . session_status() . PHP_EOL;
echo 'Save handler: ' . ini_get('session.save_handler') . PHP_EOL;
echo 'Save path: ' . session_save_path() . PHP_EOL;
echo 'Path exists: ' . (is_dir(session_save_path()) ? 'yes' : 'no') . PHP_EOL;
echo 'Path writable: ' . (is_writable(session_save_path()) ? 'yes' : 'no') . PHP_EOL;
echo PHP_EOL;

$result = session_start();

echo 'session_start: ' . ($result ? 'success' : 'failed') . PHP_EOL;
echo 'Session status after: ' . session_status() . PHP_EOL;
echo 'Session ID: ' . session_id() . PHP_EOL;

$_SESSION['test'] = 'ok';

session_write_close();