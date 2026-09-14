<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Analytics extends CI_Controller
{
    private $allowedEvents = array(
        'session_start',
        'view_catalog',
        'view_item',
        'add_to_cart',
        'remove_from_cart',
        'cart_updated',
        'view_cart',
        'begin_checkout',
        'order_submitted',
        'purchase',
    );

    public function __construct()
    {
        parent::__construct();
        $this->load->model('site_analytics_model');
    }

    public function event()
    {
        if (strtoupper($this->input->method()) !== 'POST') {
            return $this->respond(array('error' => 'POST required'), 405);
        }

        if (!$this->hasAnalyticsConsent()) {
            return $this->respond(array('accepted' => false), 204);
        }

        if ($this->requestSizeIsTooLarge()) {
            return $this->respond(array('error' => 'Payload too large'), 413);
        }

        $payload = $this->readPayload();
        $event = $this->validatePayload($payload);

        if ($event === false) {
            return $this->respond(array('error' => 'Invalid analytics event'), 422);
        }

        if (!$this->site_analytics_model->insert_event($event)) {
            $dbError = $this->db->error();
            log_message('error', 'Site analytics event insert failed: ' . json_encode($dbError));

            $message = 'Event was not saved';
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development' && !empty($dbError['message'])) {
                $message = $dbError['message'];
            }

            return $this->respond(array('error' => $message), 500);
        }

        return $this->respond(array('accepted' => true), 201);
    }

    private function hasAnalyticsConsent()
    {
        $raw = (string) $this->input->cookie('cookie_consent', true);
        $decoded = json_decode(rawurldecode($raw), true);

        if (!is_array($decoded)) {
            $decoded = json_decode($raw, true);
        }

        return is_array($decoded) && !empty($decoded['analytics']);
    }

    private function requestSizeIsTooLarge()
    {
        return isset($_SERVER['CONTENT_LENGTH']) && (int) $_SERVER['CONTENT_LENGTH'] > 16384;
    }

    private function readPayload()
    {
        $raw = trim((string) $this->input->raw_input_stream);

        if ($raw !== '') {
            $json = json_decode($raw, true);

            if (is_array($json)) {
                return $json;
            }
        }

        $post = $this->input->post(NULL, true);
        return is_array($post) ? $post : array();
    }

    private function validatePayload(array $payload)
    {
        $eventName = isset($payload['event_name']) ? trim((string) $payload['event_name']) : '';
        $eventUid = isset($payload['event_uid']) ? trim((string) $payload['event_uid']) : '';
        $visitorKey = isset($payload['visitor_key']) ? trim((string) $payload['visitor_key']) : '';
        $sessionKey = isset($payload['session_key']) ? trim((string) $payload['session_key']) : '';

        if (!in_array($eventName, $this->allowedEvents, true)) {
            return false;
        }

        if (!preg_match('/^[a-f0-9-]{36}$/i', $eventUid)) {
            return false;
        }

        if (!$this->isSafeKey($visitorKey) || !$this->isSafeKey($sessionKey)) {
            return false;
        }

        $event = array(
            'event_uid' => $eventUid,
            'event_name' => $eventName,
            'visitor_key' => $visitorKey,
            'session_key' => $sessionKey,
            'cart_key' => $this->nullableKey($payload, 'cart_key'),
            'product_id' => $this->nullablePositiveInt($payload, 'product_id'),
            'category_id' => $this->nullablePositiveInt($payload, 'category_id'),
            'order_id' => $this->nullablePositiveInt($payload, 'order_id'),
            'quantity' => $this->nullableUnsignedInt($payload, 'quantity', 100000),
            'cart_items_count' => $this->nullableUnsignedInt($payload, 'cart_items_count', 100000),
            'cart_value' => $this->nullableMoney($payload, 'cart_value'),
            'event_value' => $this->nullableMoney($payload, 'event_value'),
            'currency' => $this->nullableCurrency($payload),
            'page_path' => $this->nullablePath($payload),
            'consent_version' => 'v1',
            'created_at' => date('Y-m-d H:i:s'),
        );

        if ($event['cart_key'] === false || $event['product_id'] === false || $event['category_id'] === false || $event['order_id'] === false || $event['quantity'] === false || $event['cart_items_count'] === false || $event['cart_value'] === false || $event['event_value'] === false || $event['currency'] === false || $event['page_path'] === false) {
            return false;
        }

        return $event;
    }

    private function isSafeKey($value)
    {
        return (bool) preg_match('/^[A-Za-z0-9_-]{16,64}$/', $value);
    }

    private function nullableKey(array $payload, $field)
    {
        if (!isset($payload[$field]) || $payload[$field] === '') {
            return null;
        }

        $value = trim((string) $payload[$field]);
        return $this->isSafeKey($value) ? $value : false;
    }

    private function nullablePositiveInt(array $payload, $field)
    {
        if (!isset($payload[$field]) || $payload[$field] === '') {
            return null;
        }

        $value = filter_var($payload[$field], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
        return $value === false ? false : $value;
    }

    private function nullableUnsignedInt(array $payload, $field, $max)
    {
        if (!isset($payload[$field]) || $payload[$field] === '') {
            return null;
        }

        $value = filter_var($payload[$field], FILTER_VALIDATE_INT, array('options' => array('min_range' => 0, 'max_range' => $max)));
        return $value === false ? false : $value;
    }

    private function nullableMoney(array $payload, $field)
    {
        if (!isset($payload[$field]) || $payload[$field] === '') {
            return null;
        }

        $value = trim((string) $payload[$field]);
        return preg_match('/^\d{1,10}(\.\d{1,2})?$/', $value) ? $value : false;
    }

    private function nullableCurrency(array $payload)
    {
        if (!isset($payload['currency']) || $payload['currency'] === '') {
            return null;
        }

        $value = strtoupper(trim((string) $payload['currency']));
        return preg_match('/^[A-Z]{3}$/', $value) ? $value : false;
    }

    private function nullablePath(array $payload)
    {
        if (!isset($payload['page_path']) || $payload['page_path'] === '') {
            return null;
        }

        $path = trim((string) $payload['page_path']);
        $path = (string) parse_url($path, PHP_URL_PATH);

        if ($path === '' || strlen($path) > 255 || strpos($path, '/') !== 0) {
            return false;
        }

        return $path;
    }

    private function respond(array $body, $status)
    {
        return $this->output
            ->set_status_header($status)
            ->set_content_type('application/json', 'UTF-8')
            ->set_output($status === 204 ? '' : json_encode($body));
    }
}
