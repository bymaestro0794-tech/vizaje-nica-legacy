<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/
$hook['pre_system'][] = array(
    'class' => '',
    'function' => 'register_autoloader',
    'filename' => 'Auto_load.php',
    'filepath' => 'hooks'
);

$hook['pre_controller'][] = array(
    'class' => '',
    'function' => 'redirect_double_slash',
    'filename' => 'Redirect_double_slash.php',
    'filepath' => 'hooks'
);
