<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function redirect_double_slash()
{
    $uri = $_SERVER['REQUEST_URI'];
    if (strpos($uri, '//') !== false) {
        $clean = preg_replace('#/{2,}#', '/', $uri);
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $clean);
        exit;
    }
}
