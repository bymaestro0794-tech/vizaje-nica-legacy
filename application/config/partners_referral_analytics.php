<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/Partners_referral_analytics_config_loader.php';

$partnersAnalyticsSettings = Partners_referral_analytics_config_loader::load(
    '/home/vizajeni/private/partners-referral-analytics.php'
);

$config['partners_referral_analytics'] = array(
    'enabled' => $partnersAnalyticsSettings['enabled'],
    'api_origin' => $partnersAnalyticsSettings['api_origin'],
    'hmac_keys_json' => $partnersAnalyticsSettings['hmac_keys_json'],
    'connect_timeout_ms' => 500,
    'timeout_ms' => 2000,
    'max_request_bytes' => 65536,
    'max_response_bytes' => 8192,
    'max_context_bytes' => 512,
);

unset($partnersAnalyticsSettings);
