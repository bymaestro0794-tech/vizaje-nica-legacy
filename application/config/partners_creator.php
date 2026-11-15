<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/Partners_creator_protocol.php';
require_once APPPATH . 'libraries/Partners_creator_config_loader.php';

$config['partners_creator'] = Partners_creator_config_loader::load();
$config['partners_creator']['connect_timeout_ms'] = 800;
$config['partners_creator']['timeout_ms'] = 3500;
$config['partners_creator']['max_request_bytes'] = 16384;
$config['partners_creator']['max_response_bytes'] = 65536;
