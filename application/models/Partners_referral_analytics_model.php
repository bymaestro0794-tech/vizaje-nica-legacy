<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Partners_referral_analytics_model extends CI_Model
{
    const SESSION_KEY = 'partners_referral_analytics_attribution';
    const OUTBOX_TABLE = 'partners_referral_analytics_outbox';

    private $client;
    private $protocol;
    private $settings = array();

    public function __construct()
    {
        parent::__construct();
        $this->load->config('partners_referral_analytics');
        $this->settings = (array) $this->config->item('partners_referral_analytics');
        $this->load->library('Partners_referral_analytics_protocol');
        $this->protocol = new Partners_referral_analytics_protocol();
        $this->load->library('Partners_referral_analytics_client');
        $this->client = $this->partners_referral_analytics_client;
    }

    public function isEnabled()
    {
        return !empty($this->settings['enabled']);
    }

    /** Save only a validated opaque context returned by the authenticated exchange. */
    public function storeAttributionContext($context, $expiresAt = null)
    {
        $context = is_string($context) ? trim($context) : '';
        if (!$this->isEnabled() || !$this->hasConsent('marketing')) {
            return false;
        }
        if (!$this->hasActiveSessionStorage()) {
            $this->logHandoffDiagnostic('HANDOFF_SESSION_UNAVAILABLE');
            return false;
        }
        if (!Partners_referral_analytics_protocol::isAttributionContext($context)
            || strlen($context) > (int) $this->setting('max_context_bytes', 512)) {
            return false;
        }

        $now = time();
        $expires = $expiresAt === null ? $now + 604800 : (int) $expiresAt;
        if ($expires <= $now || $expires > $now + 604800) {
            return false;
        }

        $_SESSION[self::SESSION_KEY] = array(
            'context' => $context,
            'expires_at' => $expires,
        );

        return true;
    }

    /** Exchange a one-time Partners token; never persist the handoff token. */
    public function exchangeAttributionHandoff($handoffToken)
    {
        if (!$this->isEnabled() || !$this->hasConsent('marketing')) {
            return false;
        }

        if (!is_string($handoffToken) || !preg_match('/^[A-Za-z0-9_-]{40,}$/', $handoffToken)) {
            $this->logHandoffDiagnostic('HANDOFF_TOKEN_INVALID');
            return false;
        }
        if (!$this->hasActiveSessionStorage()) {
            $this->logHandoffDiagnostic('HANDOFF_SESSION_UNAVAILABLE');
            return false;
        }

        try {
            $result = $this->client->postJson(
                Partners_referral_analytics_protocol::ATTRIBUTION_EXCHANGE_PATH,
                array('handoffToken' => $handoffToken)
            );
        } catch (Throwable $exception) {
            $this->logHandoffDiagnostic('HANDOFF_PARTNERS_UNAVAILABLE');
            return false;
        }
        if (empty($result['ok']) || empty($result['response']) || !is_array($result['response'])) {
            $this->logHandoffDiagnostic('HANDOFF_PARTNERS_UNAVAILABLE');
            return false;
        }

        $response = $result['response'];
        if (count($response) !== 2
            || array_diff(array('attributionContext', 'expiresAt'), array_keys($response))
            || !isset($response['attributionContext'], $response['expiresAt'])
            || !is_string($response['expiresAt'])
            || !Partners_referral_analytics_protocol::isAttributionContext($response['attributionContext'])) {
            $this->logHandoffDiagnostic('HANDOFF_RESPONSE_INVALID');
            return false;
        }

        $expiresTimestamp = Partners_referral_analytics_protocol::isoUtcTimestamp($response['expiresAt']);
        if ($expiresTimestamp === false) {
            $this->logHandoffDiagnostic('HANDOFF_RESPONSE_INVALID');
            return false;
        }

        if (!$this->storeAttributionContext($response['attributionContext'], $expiresTimestamp)) {
            $this->logHandoffDiagnostic('HANDOFF_CONTEXT_NOT_STORED');
            return false;
        }

        return true;
    }

    public function currentAttributionContext()
    {
        if (!$this->hasConsent('marketing')) {
            unset($_SESSION[self::SESSION_KEY]);
            return null;
        }

        if (!isset($_SESSION[self::SESSION_KEY]) || !is_array($_SESSION[self::SESSION_KEY])) {
            return null;
        }

        $stored = $_SESSION[self::SESSION_KEY];
        if (empty($stored['context']) || empty($stored['expires_at']) || (int) $stored['expires_at'] <= time()) {
            unset($_SESSION[self::SESSION_KEY]);
            return null;
        }

        $context = (string) $stored['context'];
        if (!Partners_referral_analytics_protocol::isAttributionContext($context)
            || strlen($context) > (int) $this->setting('max_context_bytes', 512)) {
            unset($_SESSION[self::SESSION_KEY]);
            return null;
        }

        return $context;
    }

    /**
     * Persist attribution with the order row where the additive SQL patch exists.
     * Returns false when the patch is not installed; checkout must continue either way.
     */
    public function addAttributionToOrderData(array &$orderData)
    {
        $context = $this->currentAttributionContext();
        if ($context === null || !$this->orderAnalyticsColumnsExist()) {
            return false;
        }

        $orderData['partners_referral_context'] = $context;
        $orderData['partners_referral_items_complete'] = 0;
        return true;
    }

    /** Mark the order snapshot complete only after every item insert succeeded. */
    public function markOrderItemsComplete($orderId)
    {
        if ((int) $orderId <= 0 || !$this->orderAnalyticsColumnsExist()) {
            return false;
        }

        $previousDbDebug = $this->db->db_debug;
        try {
            $this->db->db_debug = false;
            $this->db->where('id', (int) $orderId)
                ->where('partners_referral_context IS NOT NULL', null, false)
                ->update('orders', array('partners_referral_items_complete' => 1));
            if ($this->db->affected_rows() === 1) {
                return true;
            }
            $row = $this->db->select('partners_referral_items_complete')
                ->where('id', (int) $orderId)->get('orders')->row();
            return !empty($row) && (int) $row->partners_referral_items_complete === 1;
        } catch (Throwable $exception) {
            return false;
        } finally {
            $this->db->db_debug = $previousDbDebug;
        }
    }

    public function recordOrderCreated($orderId)
    {
        $bundle = $this->getOrderBundle($orderId);
        if (empty($bundle)) {
            return false;
        }

        return $this->queueOrderEvent(
            $bundle,
            'new',
            'order.created',
            (string) $bundle['order']->added
        );
    }

    public function recordOrderPaid($orderId, $paidAt = null)
    {
        $bundle = $this->getOrderBundle($orderId);
        if (empty($bundle)
            || (string) $bundle['order']->status === 'canceled'
            || (int) $bundle['order']->pay_success !== 1) {
            return false;
        }

        $paidAt = is_string($paidAt) && $paidAt !== ''
            ? $paidAt
            : (string) $bundle['order']->partners_referral_paid_at;
        if ($paidAt === '') {
            return false;
        }

        return $this->queueOrderEvent($bundle, 'paid', 'order.updated', $paidAt);
    }

    public function recordOrderStatusChange($orderId, $oldStatus, $newStatus)
    {
        $oldStatus = strtolower(trim((string) $oldStatus));
        $newStatus = strtolower(trim((string) $newStatus));
        if ($oldStatus === $newStatus || $newStatus === 'new') {
            return false;
        }

        $eventType = Partners_referral_analytics_protocol::orderEventTypeForStatus($newStatus);
        if ($eventType === null) {
            return false;
        }

        $status = ($newStatus === 'canceled') ? 'cancelled' : $newStatus;
        $bundle = $this->getOrderBundle($orderId);
        if (empty($bundle)) {
            return false;
        }
        $order = $bundle['order'];
        if (Partners_referral_analytics_protocol::isOrderStatusRegression($oldStatus, $newStatus)
            || ($newStatus === 'progress'
                && ((int) $order->pay_success === 1 || !empty($order->partners_referral_paid_at)))
            || ($newStatus === 'paid'
                && ((int) $order->pay_success !== 1 || empty($order->partners_referral_paid_at)))) {
            return false;
        }

        return $this->queueOrderEvent(
            $bundle,
            $status,
            $eventType,
            (string) $bundle['order']->updated
        );
    }

    /** Rebuild any missing order events from saved Legacy snapshots. */
    public function reconcileOrderEvents($limit = 100)
    {
        $result = array('scanned' => 0, 'reconciled' => 0, 'error' => null);
        if (!$this->isEnabled()) {
            return $result;
        }

        if (!$this->tableExists()) {
            $result['error'] = 'OUTBOX_TABLE_UNAVAILABLE';
            return $result;
        }
        if (!$this->orderAnalyticsColumnsExist()) {
            $result['error'] = 'ORDER_ANALYTICS_SCHEMA_MISSING';
            return $result;
        }

        $limit = max(1, min(500, (int) $limit));
        $previousDbDebug = $this->db->db_debug;
        try {
            $this->db->db_debug = false;
            $orders = $this->db->select('id,status,pay_success,partners_referral_paid_at,updated')
                ->from('orders')
                ->where('partners_referral_context IS NOT NULL', null, false)
                ->where('partners_referral_items_complete', 1)
                ->group_start()
                    ->where('partners_referral_reconciled_at IS NULL', null, false)
                    ->or_where('partners_referral_reconciled_state IS NULL', null, false)
                    ->or_where("partners_referral_reconciled_state <> CONCAT_WS('|', status, pay_success, COALESCE(DATE_FORMAT(partners_referral_paid_at, '%Y-%m-%d %H:%i:%s'), ''))", null, false)
                ->group_end()
                ->order_by('id', 'ASC')
                ->limit($limit)
                ->get()
                ->result();

            foreach ($orders as $order) {
                $result['scanned']++;
                $reconciledState = $this->reconcileOneOrder((int) $order->id);
                if ($reconciledState === false) {
                    continue;
                }

                $this->db->where('id', (int) $order->id)->update('orders', array(
                    'partners_referral_reconciled_at' => date('Y-m-d H:i:s'),
                    'partners_referral_reconciled_state' => $reconciledState,
                ));
                if ($this->db->affected_rows() === 1) {
                    $result['reconciled']++;
                }
            }
        } catch (Throwable $exception) {
            // Keep exception details private; the CLI worker receives a safe code.
            $result['error'] = 'DATABASE_OPERATION_FAILED';
        } finally {
            $this->db->db_debug = $previousDbDebug;
        }

        return $result;
    }

    private function reconcileOneOrder($orderId)
    {
        $bundle = $this->getOrderBundle($orderId);
        if (empty($bundle)) {
            return false;
        }

        $order = $bundle['order'];
        $events = array();
        $events[] = array('new', 'order.created', (string) $order->added);
        $events = array_merge($events, Partners_referral_analytics_protocol::reconciledOrderTransitions(
            $order->status,
            $order->pay_success,
            $order->partners_referral_paid_at,
            $order->updated
        ));

        foreach ($events as $event) {
            if (!$this->queueOrderEvent($bundle, $event[0], $event[1], $event[2])) {
                return false;
            }
        }
        return $this->orderReconciliationState($order);
    }

    private function queueOrderEvent(array $bundle, $status, $eventType, $occurredAt)
    {
        if (!$this->isEnabled() || empty($bundle['order']) || empty($bundle['items'])) {
            return false;
        }

        $order = $bundle['order'];
        $internalId = (int) $order->id;
        $context = (string) $order->partners_referral_context;
        if ($internalId <= 0 || (int) $order->partners_referral_items_complete !== 1
            || !Partners_referral_analytics_protocol::isAttributionContext($context)) {
            return false;
        }

        $items = array();
        foreach ($bundle['items'] as $item) {
            $productId = (int) $item->product_id;
            $quantity = (int) $item->qty;
            $unitPrice = $this->moneyString($item->price);
            $lineTotal = $this->moneyString($item->total);
            if ($productId <= 0 || (float) $item->qty !== (float) $quantity
                || $quantity < 1 || $quantity > 99 || $unitPrice === null || $lineTotal === null) {
                return false;
            }

            $items[] = array(
                'legacyProductId' => (string) $productId,
                'quantity' => $quantity,
                'unitPrice' => $unitPrice,
                'lineTotal' => $lineTotal,
            );
        }
        if (count($items) < 1 || count($items) > 100) {
            return false;
        }

        $amount = $this->moneyString(
            (float) $order->total
            + (float) $order->delivery_price
            - (float) $order->bonus_minus
        );
        if ($amount === null) {
            return false;
        }

        $status = strtolower((string) $status);
        $validTypes = array(
            'order.created' => 'new',
            'order.confirmed' => 'confirmed',
            'order.cancelled' => 'cancelled',
            'order.updated' => array('progress', 'paid', 'finished'),
        );
        if (!isset($validTypes[$eventType])
            || (is_array($validTypes[$eventType])
                ? !in_array($status, $validTypes[$eventType], true)
                : $status !== $validTypes[$eventType])) {
            return false;
        }

        $orderKey = 'vizaje-nica-order-' . $internalId;
        $eventId = Partners_referral_analytics_protocol::stableUuid(
            'vizaje-nica:order:' . $internalId . ':' . $status
        );
        $payload = array(
            'eventId' => $eventId,
            'eventVersion' => 'v1',
            'eventType' => $eventType,
            'orderId' => $orderKey,
            'attributionContext' => $context,
            'status' => $status,
            'amount' => $amount,
            'currency' => 'MDL',
            'occurredAt' => $this->orderEventOccurredAt($order, $status, $occurredAt),
            'items' => $items,
        );

        return $this->enqueuePayload(
            Partners_referral_analytics_protocol::ORDER_EVENTS_PATH,
            $payload,
            'order',
            $orderKey
        );
    }

    private function getOrderBundle($orderId)
    {
        if (!$this->isEnabled() || (int) $orderId <= 0 || !$this->orderAnalyticsColumnsExist()) {
            return null;
        }

        $previousDbDebug = $this->db->db_debug;
        try {
            $this->db->db_debug = false;
            $order = $this->db->select(
                'id,status,total,delivery_price,bonus_minus,added,updated,pay_success,'
                . 'partners_referral_context,partners_referral_items_complete,partners_referral_paid_at'
            )->where('id', (int) $orderId)->get('orders')->row();
            if (empty($order) || empty($order->partners_referral_context)
                || (int) $order->partners_referral_items_complete !== 1) {
                return null;
            }

            $items = $this->db->select('product_id,qty,price,total')
                ->where('order_id', (int) $orderId)
                ->get('orders_products')
                ->result();
            if (empty($items) || count($items) > 100) {
                return null;
            }

            return array('order' => $order, 'items' => $items);
        } catch (Throwable $exception) {
            return null;
        } finally {
            $this->db->db_debug = $previousDbDebug;
        }
    }

    private function moneyString($value)
    {
        if (!is_numeric($value) || (float) $value < 0) {
            return null;
        }
        return number_format(round((float) $value, 2), 2, '.', '');
    }

    private function orderEventOccurredAt($order, $status, $occurredAt)
    {
        $timestamp = Partners_referral_analytics_protocol::databaseDateTimestamp((string) $occurredAt);
        $createdAt = Partners_referral_analytics_protocol::databaseDateTimestamp((string) $order->added);
        $now = time();
        if ($timestamp === false) {
            $timestamp = $now;
        }
        if ($timestamp > $now + 30) {
            $timestamp = $now;
        }
        if ($createdAt !== false && $status !== 'new') {
            $timestamp = max($timestamp, $createdAt + 1);
        }
        if ($status !== 'new' && $status !== 'paid' && !empty($order->partners_referral_paid_at)) {
            $paidAt = Partners_referral_analytics_protocol::databaseDateTimestamp((string) $order->partners_referral_paid_at);
            if ($paidAt !== false) {
                $timestamp = max($timestamp, $paidAt + 1);
            }
        }
        // Allow at most the one-second ordering adjustment above; Partners
        // accepts order events up to five minutes in the future.
        $timestamp = min($timestamp, $now + 1);
        return Partners_referral_analytics_protocol::isoUtc($timestamp);
    }

    private function orderReconciliationState($order)
    {
        return implode('|', array(
            (string) $order->status,
            (string) ((int) $order->pay_success),
            empty($order->partners_referral_paid_at) ? '' : (string) $order->partners_referral_paid_at,
        ));
    }

    private function orderAnalyticsColumnsExist()
    {
        try {
            foreach (array(
                'partners_referral_context',
                'partners_referral_items_complete',
                'partners_referral_paid_at',
                'partners_referral_reconciled_at',
                'partners_referral_reconciled_state',
            ) as $column) {
                if (!$this->db->field_exists($column, 'orders')) {
                    return false;
                }
            }
            return true;
        } catch (Throwable $exception) {
            return false;
        }
    }

    /** Queue only the contract-defined add_to_cart event, after a saved cart mutation. */
    public function recordAddToCart($productId, $quantity)
    {
        if (!$this->isEnabled() || !$this->hasAnalyticsConsent()) {
            return false;
        }

        $context = $this->currentAttributionContext();
        if ($context === null) {
            return false;
        }

        try {
            $eventId = Partners_referral_analytics_protocol::uuidV4();
            $payload = Partners_referral_analytics_protocol::addToCartPayload(
                $eventId,
                $context,
                (int) $productId,
                (int) $quantity,
                Partners_referral_analytics_protocol::isoUtc(time())
            );

            return $this->enqueuePayload(
                Partners_referral_analytics_protocol::EVENTS_PATH,
                $payload,
                'cart_add',
                (string) $eventId
            );
        } catch (Throwable $exception) {
            // Analytics is best-effort and must never change the cart response.
            return false;
        }
    }

    /** Store an already contract-compliant event for asynchronous delivery. */
    public function enqueuePayload($endpointPath, array $payload, $aggregateType = null, $aggregateId = null)
    {
        if (!$this->isEnabled()
            || !in_array($endpointPath, array(
                Partners_referral_analytics_protocol::EVENTS_PATH,
                Partners_referral_analytics_protocol::ORDER_EVENTS_PATH,
            ), true)
            || Partners_referral_analytics_protocol::payloadContainsPiiFields($payload)) {
            return false;
        }

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($json) || strlen($json) > (int) $this->setting('max_request_bytes', 65536)) {
            return false;
        }

        $eventId = isset($payload['eventId']) && Partners_referral_analytics_protocol::isUuid($payload['eventId'])
            ? strtolower($payload['eventId'])
            : null;
        if ($eventId === null) {
            return false;
        }

        $previousDbDebug = $this->db->db_debug;
        try {
            // A broken/missing optional outbox must not turn a successful cart
            // save into a framework-level database error page.
            $this->db->db_debug = false;
            if (!$this->db->table_exists(self::OUTBOX_TABLE)) {
                return false;
            }

            $this->db->insert(self::OUTBOX_TABLE, array(
                'event_id' => $eventId,
                'event_name' => isset($payload['eventName'])
                    ? substr((string) $payload['eventName'], 0, 64)
                    : (isset($payload['eventType']) ? substr((string) $payload['eventType'], 0, 64) : 'order_event'),
                'endpoint_path' => $endpointPath,
                'aggregate_type' => $aggregateType !== null ? substr((string) $aggregateType, 0, 32) : null,
                'aggregate_id' => $aggregateId !== null ? substr((string) $aggregateId, 0, 96) : null,
                'payload' => $json,
                'status' => 'pending',
                'attempts' => 0,
                'next_attempt_at' => gmdate('Y-m-d H:i:s'),
                'created_at' => gmdate('Y-m-d H:i:s'),
            ));

            if ($this->db->affected_rows() === 1) {
                return true;
            }

            // A deterministic order event may already be queued. Treat an
            // identical saved payload as success; a changed payload is not.
            $existing = $this->db->where('event_id', $eventId)->get(self::OUTBOX_TABLE)->row();
            return !empty($existing)
                && hash_equals((string) $existing->payload, $json)
                && (string) $existing->endpoint_path === $endpointPath;
        } catch (Throwable $exception) {
            // Duplicate event IDs and temporary DB failures are intentionally quiet.
            return false;
        } finally {
            $this->db->db_debug = $previousDbDebug;
        }
    }

    public function deliverPending($limit = 25)
    {
        $result = array('delivered' => 0, 'retry' => 0, 'failed' => 0, 'skipped' => 0, 'error' => null);
        if (!$this->isEnabled()) {
            return $result;
        }
        if (!$this->client->isReady()) {
            $result['error'] = 'ANALYTICS_CLIENT_NOT_CONFIGURED';
            return $result;
        }
        if (!$this->tableExists()) {
            $result['error'] = 'OUTBOX_TABLE_UNAVAILABLE';
            return $result;
        }

        $limit = max(1, min(100, (int) $limit));
        $previousDbDebug = $this->db->db_debug;
        try {
            $this->db->db_debug = false;
            for ($i = 0; $i < $limit; $i++) {
                $row = $this->claimNext();
                if (empty($row)) {
                    break;
                }

                $payload = json_decode((string) $row->payload, true);
                if (!is_array($payload) || Partners_referral_analytics_protocol::payloadContainsPiiFields($payload)) {
                    $this->markFailed($row, 'INVALID_STORED_PAYLOAD');
                    $result['failed']++;
                    $result['skipped']++;
                    continue;
                }

                $delivery = $this->client->postRawJson($row->endpoint_path, (string) $row->payload);
                if (!empty($delivery['ok'])) {
                    $this->db->where('id', (int) $row->id)->update(self::OUTBOX_TABLE, array(
                        'status' => 'delivered',
                        'attempts' => (int) $row->attempts + 1,
                        'locked_at' => null,
                        'delivered_at' => gmdate('Y-m-d H:i:s'),
                        'last_error_code' => null,
                    ));
                    $result['delivered']++;
                } else {
                    $code = isset($delivery['code']) ? $delivery['code'] : 'DELIVERY_FAILED';
                    if ($this->isRetryableDelivery($delivery)) {
                        $this->scheduleRetry($row, $code);
                        $result['retry']++;
                    } else {
                        $this->markFailed($row, $code);
                        $result['failed']++;
                    }
                }
            }
        } catch (Throwable $exception) {
            // Keep exception details private; report a stable code to CLI only.
            $result['error'] = 'DATABASE_OPERATION_FAILED';
        } finally {
            $this->db->db_debug = $previousDbDebug;
        }

        return $result;
    }

    private function claimNext()
    {
        $this->db->trans_begin();
        $query = $this->db->query(
            "SELECT id, event_id, endpoint_path, payload, attempts FROM `" . self::OUTBOX_TABLE . "` "
            . "WHERE ((status = 'pending' AND next_attempt_at <= UTC_TIMESTAMP()) "
            . "OR (status = 'processing' AND locked_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL 10 MINUTE))) "
            . "AND (aggregate_type IS NULL OR aggregate_type <> 'order' OR event_name = 'order.created' "
            . "OR (EXISTS (SELECT 1 FROM `" . self::OUTBOX_TABLE . "` AS created "
            . "WHERE created.aggregate_type = 'order' AND created.aggregate_id = `" . self::OUTBOX_TABLE . "`.aggregate_id "
            . "AND created.event_name = 'order.created' AND created.status = 'delivered') "
            . "AND NOT EXISTS (SELECT 1 FROM `" . self::OUTBOX_TABLE . "` AS prior "
            . "WHERE prior.aggregate_type = 'order' AND prior.aggregate_id = `" . self::OUTBOX_TABLE . "`.aggregate_id "
            . "AND prior.id < `" . self::OUTBOX_TABLE . "`.id AND prior.status <> 'delivered'))) "
            . "ORDER BY id ASC LIMIT 1 FOR UPDATE"
        );
        $row = $query ? $query->row() : null;
        if (empty($row)) {
            $this->db->trans_commit();
            return null;
        }

        $this->db->where('id', (int) $row->id)->update(self::OUTBOX_TABLE, array(
            'status' => 'processing',
            'locked_at' => gmdate('Y-m-d H:i:s'),
        ));
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return null;
        }
        $this->db->trans_commit();

        return $row;
    }

    private function scheduleRetry($row, $errorCode)
    {
        $attempt = (int) $row->attempts + 1;
        $delay = Partners_referral_analytics_protocol::retryDelaySeconds($attempt);
        $errorCode = preg_match('/^[A-Z0-9_]{1,64}$/', (string) $errorCode)
            ? (string) $errorCode
            : 'DELIVERY_FAILED';

        $this->db->where('id', (int) $row->id)->update(self::OUTBOX_TABLE, array(
            'status' => 'pending',
            'attempts' => $attempt,
            'next_attempt_at' => gmdate('Y-m-d H:i:s', time() + $delay),
            'locked_at' => null,
            'last_error_code' => $errorCode,
        ));
    }

    private function markFailed($row, $errorCode)
    {
        $errorCode = preg_match('/^[A-Z0-9_]{1,64}$/', (string) $errorCode)
            ? (string) $errorCode
            : 'DELIVERY_FAILED';
        $this->db->where('id', (int) $row->id)->update(self::OUTBOX_TABLE, array(
            'status' => 'failed',
            'attempts' => (int) $row->attempts + 1,
            'locked_at' => null,
            'last_error_code' => $errorCode,
        ));
    }

    private function isRetryableDelivery(array $delivery)
    {
        $status = isset($delivery['http_status']) ? (int) $delivery['http_status'] : 0;
        return $status === 0
            || in_array($status, array(408, 425, 429), true)
            || ($status >= 500 && $status <= 599);
    }

    private function hasAnalyticsConsent()
    {
        return $this->hasConsent('analytics');
    }

    private function hasActiveSessionStorage()
    {
        return function_exists('session_status')
            && session_status() === PHP_SESSION_ACTIVE
            && isset($_SESSION)
            && is_array($_SESSION);
    }

    /** Emit only a fixed diagnostic category; never include request or response data. */
    private function logHandoffDiagnostic($code)
    {
        $allowedCodes = array(
            'HANDOFF_TOKEN_INVALID',
            'HANDOFF_SESSION_UNAVAILABLE',
            'HANDOFF_PARTNERS_UNAVAILABLE',
            'HANDOFF_RESPONSE_INVALID',
            'HANDOFF_CONTEXT_NOT_STORED',
        );
        if (in_array($code, $allowedCodes, true) && function_exists('log_message')) {
            log_message('error', 'Partners referral handoff: ' . $code);
        }
    }

    private function hasConsent($category)
    {
        $raw = isset($_COOKIE['cookie_consent']) ? (string) $_COOKIE['cookie_consent'] : '';
        $decoded = json_decode(rawurldecode($raw), true);
        if (!is_array($decoded)) {
            $decoded = json_decode($raw, true);
        }

        return is_array($decoded)
            && isset($decoded[$category])
            && $decoded[$category] === true;
    }

    private function tableExists()
    {
        try {
            return $this->db->table_exists(self::OUTBOX_TABLE);
        } catch (Throwable $exception) {
            return false;
        }
    }

    private function setting($key, $default)
    {
        return isset($this->settings[$key]) ? $this->settings[$key] : $default;
    }
}
