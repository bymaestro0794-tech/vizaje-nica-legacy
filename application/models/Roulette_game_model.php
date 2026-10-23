<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Roulette_game_model extends CI_Model
{
	public function isReady()
	{
		$this->load->model('roulette_model');
		return $this->roulette_model->isGameplayInstalled();
	}

	public function getState($clientId, $language = 'ru')
	{
		$clientId = (int) $clientId;
		$language = strtolower((string) $language) === 'ro' ? 'ro' : 'ru';
		if ($this->isReady()) {
			$this->reconcileExpiredReservations($clientId);
		}
		$now = $this->databaseUtcNow();

		$this->expireSpins($clientId);
		$account = $this->db->where('client_id', $clientId)
			->get('roulette_account_state')->row_array();
		$outstandingSpin = $this->getOutstandingSpin($clientId);
		$activeSpin = !empty($outstandingSpin) && $outstandingSpin['status'] === 'active'
			? $outstandingSpin
			: null;
		$paymentPending = !empty($outstandingSpin) && $outstandingSpin['status'] === 'reserved';
		$firstQualifiedOrderAt = $this->getFirstQualifiedOrderAt($clientId);
		$qualified = $firstQualifiedOrderAt !== null;
		$weeklyWindow = $this->getWeeklyWindow($now);
		$weeklyWindowOpen = $qualified && $this->isWeeklyWindowOpen($firstQualifiedOrderAt, $weeklyWindow);
		$weeklySpin = $weeklyWindowOpen
			? $this->getSpinInWindow($clientId, $weeklyWindow['start'], $weeklyWindow['end'])
			: null;
		$configuration = $this->getConfiguration(false);

		$reason = null;
		if ($paymentPending) {
			$reason = 'payment_pending';
		} elseif (!$qualified && empty($activeSpin)) {
			$reason = 'completed_order_required';
		} elseif (!empty($activeSpin)) {
			$reason = 'active_prize';
		} elseif (empty($configuration['visible'])) {
			$reason = 'roulette_hidden';
		} elseif (empty($configuration['enabled'])) {
			$reason = 'roulette_disabled';
		} elseif (!$configuration['valid']) {
			$reason = 'prizes_not_configured';
		} elseif (!$weeklyWindowOpen) {
			$reason = 'weekly_window_not_started';
		} elseif (!empty($weeklySpin)) {
			$reason = 'weekly_spin_used';
		}

		$nextSpinAt = null;
		if ($reason === 'weekly_window_not_started') {
			$nextSpinAt = !empty($weeklyWindow['before_reset'])
				? $weeklyWindow['end']
				: $weeklyWindow['start'];
		} elseif (!empty($weeklySpin)) {
			$nextSpinAt = $weeklyWindow['end'];
		} elseif (!empty($account['next_spin_at']) && $account['next_spin_at'] > $now) {
			// Keep compatibility with old rows while the first weekly period is migrated.
			$nextSpinAt = $account['next_spin_at'];
		}

		return array(
			'eligible' => $qualified,
			'visible' => !empty($configuration['visible']),
			'can_spin' => $qualified && $weeklyWindowOpen && empty($outstandingSpin) && empty($weeklySpin) && $reason === null,
			'reason' => $reason,
			'server_time' => $this->toIso8601($now),
			'weekly_window_start' => $this->toIso8601($weeklyWindow['start']),
			'weekly_window_end' => $this->toIso8601($weeklyWindow['end']),
			'weekly_spin_used' => !empty($weeklySpin),
			'settings' => $this->formatSettings($configuration, $language),
			'prizes' => $this->formatAvailablePrizes($configuration, $language),
			'next_spin_at' => $this->toIso8601($nextSpinAt),
			'active_prize' => !empty($activeSpin)
				? $this->formatSpin($activeSpin, $language)
				: null,
			'payment_pending' => $paymentPending,
		);
	}

	public function spin($clientId, $requestId, $language = 'ru')
	{
		$clientId = (int) $clientId;
		$language = strtolower((string) $language) === 'ro' ? 'ro' : 'ru';
		$requestId = strtolower(trim((string) $requestId));

		if ($clientId < 1 || !preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[1-5][a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/', $requestId)) {
			return array('ok' => false, 'http_status' => 422, 'reason' => 'invalid_request_id');
		}
		if (!$this->isReady()) {
			return array('ok' => false, 'http_status' => 503, 'reason' => 'roulette_setup_required');
		}

		$this->db->trans_begin();
		try {
			$this->db->query(
				'INSERT IGNORE INTO roulette_account_state (client_id, created_at, updated_at) VALUES (?, UTC_TIMESTAMP(), UTC_TIMESTAMP())',
				array($clientId)
			);

			$account = $this->db->query(
				' SELECT client_id, next_spin_at
				  FROM roulette_account_state
			  WHERE client_id = ?
			  FOR UPDATE',
				array($clientId)
			)->row_array();
			if (empty($account)) {
				throw new RuntimeException('Could not lock the roulette account row.');
			}

			$previousRequest = $this->db->where('client_id', $clientId)
				->where('request_id', $requestId)
				->get('roulette_spins')->row_array();
			if (!empty($previousRequest)) {
				$this->db->trans_commit();
				return array(
					'ok' => true,
					'replayed' => true,
					'prize' => $this->formatSpin($previousRequest, $language),
				);
			}

			$this->expireSpins($clientId);
			$now = $this->databaseUtcNow();
			$errorReason = null;
			$activeSpin = $this->getOutstandingSpin($clientId);
			$firstQualifiedOrderAt = $this->getFirstQualifiedOrderAt($clientId);
			$weeklyWindow = $this->getWeeklyWindow($now);
			$weeklyWindowOpen = $firstQualifiedOrderAt !== null
				&& $this->isWeeklyWindowOpen($firstQualifiedOrderAt, $weeklyWindow);
			$weeklySpin = $weeklyWindowOpen
				? $this->getSpinInWindow($clientId, $weeklyWindow['start'], $weeklyWindow['end'])
				: null;
			$configuration = $this->getConfiguration(true);

			if ($firstQualifiedOrderAt === null) {
				$errorReason = 'completed_order_required';
			} elseif (!empty($activeSpin)) {
				$errorReason = $activeSpin['status'] === 'reserved' ? 'payment_pending' : 'active_prize';
			} elseif (empty($configuration['visible'])) {
				$errorReason = 'roulette_hidden';
			} elseif (empty($configuration['enabled'])) {
				$errorReason = 'roulette_disabled';
			} elseif (!$configuration['valid']) {
				$errorReason = 'prizes_not_configured';
			} elseif (!$weeklyWindowOpen) {
				$errorReason = 'weekly_window_not_started';
			} elseif (!empty($weeklySpin)) {
				$errorReason = 'weekly_spin_used';
			}

			if ($errorReason !== null) {
				if ($this->db->trans_status() === false) {
					throw new RuntimeException('Database transaction failed while validating a spin.');
				}
				$this->db->trans_commit();
				return array('ok' => false, 'http_status' => 409, 'reason' => $errorReason);
			}

			$prize = $this->choosePrize($configuration['prizes']);
			$spunAt = new DateTimeImmutable($now, new DateTimeZone('UTC'));
			$expiresAt = $spunAt->modify('+24 hours');
			$spunAtValue = $spunAt->format('Y-m-d H:i:s');
			$expiresAtValue = $expiresAt->format('Y-m-d H:i:s');
			$nextSpinAtValue = $weeklyWindow['end'];

			$this->db->insert('roulette_spins', array(
				'client_id' => $clientId,
				'prize_id' => (int) $prize['id'],
				'request_id' => $requestId,
				'prize_code_snapshot' => $prize['code'],
				'reward_type_snapshot' => $prize['reward_type'],
				'reward_value_snapshot' => $prize['reward_value'],
				'reward_payload_snapshot' => !empty($prize['reward_payload']) ? $prize['reward_payload'] : null,
				'titleRU_snapshot' => $prize['titleRU'],
				'titleRO_snapshot' => $prize['titleRO'],
				'probability_snapshot' => $prize['probability'],
				'spun_at' => $spunAtValue,
				'expires_at' => $expiresAtValue,
				'status' => 'active',
				'created_at' => $spunAtValue,
			));

			$spinId = (int) $this->db->insert_id();
			$this->db->where('client_id', $clientId)->update('roulette_account_state', array(
				'next_spin_at' => $nextSpinAtValue,
				'updated_at' => $spunAtValue,
			));

			if ($spinId < 1 || $this->db->trans_status() === false) {
				throw new RuntimeException('Could not persist the roulette spin.');
			}

			$spin = $this->db->where('id', $spinId)->get('roulette_spins')->row_array();
			$this->db->trans_commit();

			if ($this->db->trans_status() === false || empty($spin)) {
				throw new RuntimeException('Could not finalize the roulette spin transaction.');
			}

			return array(
				'ok' => true,
				'replayed' => false,
				'prize' => $this->formatSpin($spin, $language),
			);
		} catch (Throwable $exception) {
			$this->db->trans_rollback();
			log_message('error', 'Roulette spin failed: ' . $exception->getMessage());
			return array('ok' => false, 'http_status' => 500, 'reason' => 'spin_failed');
		}
	}

	/** Bind the already-calculated reward to the order inside the caller's transaction. */
	public function bindRewardToOrder($clientId, $spinId, $orderId, $isPaynet)
	{
		$clientId = (int) $clientId;
		$spinId = (int) $spinId;
		$orderId = (int) $orderId;
		if (!$this->isReady() || $clientId < 1 || $spinId < 1 || $orderId < 1) {
			return false;
		}

		$spin = $this->db->query(
			'SELECT id, client_id, status, order_id, expires_at, reward_type_snapshot, reward_payload_snapshot, prize_code_snapshot
			 FROM roulette_spins WHERE id = ? AND client_id = ? FOR UPDATE',
			array($spinId, $clientId)
		)->row_array();
		if (empty($spin)) {
			return false;
		}

		if ((int) $spin['order_id'] === $orderId && in_array($spin['status'], array('reserved', 'redeemed'), true)) {
			return true;
		}

		if (
			$spin['status'] !== 'active'
			|| !empty($spin['order_id'])
			|| $spin['expires_at'] <= $this->databaseUtcNow()
			|| !in_array($spin['reward_type_snapshot'], array('discount_percent', 'free_shipping', 'gift'), true)
			|| ($spin['reward_type_snapshot'] === 'gift' && (!ctype_digit((string) $spin['reward_payload_snapshot']) || (int) $spin['reward_payload_snapshot'] < 1))
		) {
			return false;
		}

		$this->db->where('id', $spinId)->where('client_id', $clientId)->where('status', 'active')
			->where('order_id IS NULL', null, false)->update('roulette_spins', array(
				'status' => $isPaynet ? 'reserved' : 'redeemed',
				'order_id' => $orderId,
				'used_at' => $isPaynet ? null : $this->databaseUtcNow(),
			));

		return $this->db->affected_rows() === 1;
	}

	/** Return the catalog product that must be added as a free gift to this order. */
	public function getGiftRewardForOrder($clientId, $spinId, $orderId)
	{
		if (!$this->isReady() || !$this->db->table_exists('gifts')) {
			return null;
		}

		$spin = $this->db->select('reward_type_snapshot, reward_payload_snapshot, status, order_id')
			->where('id', (int) $spinId)
			->where('client_id', (int) $clientId)
			->where('order_id', (int) $orderId)
			->where_in('status', array('reserved', 'redeemed'))
			->get('roulette_spins')
			->row_array();
		if (empty($spin) || $spin['reward_type_snapshot'] !== 'gift') {
			return null;
		}

		$gift = $this->db->select('g.id, g.titleRU, g.titleRO, g.product_id, g.variant_id, p.SKU, p.titleRU AS product_title_ru, p.titleRO AS product_title_ro')
			->from('gifts g')
			->join('products p', 'p.id = g.product_id', 'inner')
			->where('g.id', (int) $spin['reward_payload_snapshot'])
			->where('p.isShown', 1)
			->get()
			->row_array();

		return !empty($gift) ? $gift : false;
	}

	/** Create one fixed Paynet reservation; retries reuse its ExternalID and deadline. */
	public function createOrReusePaymentReservation($clientId, $spinId, $orderId, $externalId)
	{
		$clientId = (int) $clientId;
		$spinId = (int) $spinId;
		$orderId = (int) $orderId;
		$externalId = trim((string) $externalId);
		if (!$this->isReady() || $clientId < 1 || $spinId < 1 || $orderId < 1 || $externalId === '') {
			return array('ok' => false, 'reason' => 'invalid_reservation');
		}

		$this->db->trans_begin();
		try {
			$spin = $this->db->query(
				"SELECT * FROM roulette_spins
				 WHERE id = ? AND client_id = ? AND status = 'reserved' AND order_id = ?
				 FOR UPDATE",
				array($spinId, $clientId, $orderId)
			)->row_array();
			if (empty($spin)) {
				$this->db->trans_rollback();
				return array('ok' => false, 'reason' => 'reward_not_reserved');
			}

			$existing = $this->db->query(
				"SELECT paynet_external_id, reserved_until
				 FROM roulette_payment_reservations
				 WHERE spin_id = ? AND order_id = ? AND status = 'pending'
				   AND reserved_until > UTC_TIMESTAMP()
				 ORDER BY id ASC LIMIT 1 FOR UPDATE",
				array($spinId, $orderId)
			)->row_array();
			if (!empty($existing)) {
				$this->db->trans_commit();
				return array(
					'ok' => true,
					'external_id' => (string) $existing['paynet_external_id'],
					'reserved_until' => (string) $existing['reserved_until'],
					'paynet_expiry' => $this->formatPaynetExpiry($existing['reserved_until']),
					'reused' => true,
				);
			}

			$now = $this->databaseUtcNow();
			if ($spin['expires_at'] <= $now) {
				$this->db->where('id', $spinId)->where('status', 'reserved')->update('roulette_spins', array(
					'status' => 'expired', 'order_id' => null,
				));
				$this->db->trans_commit();
				return array('ok' => false, 'reason' => 'reward_expired');
			}

			$nowTimestamp = strtotime($now . ' UTC');
			$rewardDeadline = strtotime($spin['expires_at'] . ' UTC') + 600;
			$reservedUntil = gmdate('Y-m-d H:i:s', min($nowTimestamp + 600, $rewardDeadline));
			$this->db->insert('roulette_payment_reservations', array(
				'spin_id' => $spinId,
				'order_id' => $orderId,
				'paynet_external_id' => $externalId,
				'created_at' => $now,
				'reserved_until' => $reservedUntil,
				'status' => 'pending',
			));

			if ($this->db->trans_status() === false) {
				throw new RuntimeException('Could not create Paynet reward reservation.');
			}
			$this->db->trans_commit();
			return array(
				'ok' => true,
				'external_id' => $externalId,
				'reserved_until' => $reservedUntil,
				'paynet_expiry' => $this->formatPaynetExpiry($reservedUntil),
				'reused' => false,
			);
		} catch (Throwable $exception) {
			$this->db->trans_rollback();
			log_message('error', 'Roulette Paynet reservation failed: ' . $exception->getMessage());
			return array('ok' => false, 'reason' => 'reservation_failed');
		}
	}

	public function confirmPaynetReservation($externalId)
	{
		if (!$this->isReady()) {
			return false;
		}
		$this->db->trans_begin();
		try {
			$reservation = $this->db->query(
				'SELECT * FROM roulette_payment_reservations WHERE paynet_external_id = ? FOR UPDATE',
				array(trim((string) $externalId))
			)->row_array();
			if (empty($reservation)) {
				$this->db->trans_rollback();
				return false;
			}

			if ($reservation['status'] === 'paid') {
				$this->db->trans_commit();
				return true;
			}

			$this->db->where('id', (int) $reservation['id'])->update('roulette_payment_reservations', array(
				'status' => 'paid', 'resolved_at' => $this->databaseUtcNow(),
			));
			$this->db->where('id', (int) $reservation['spin_id'])->where('order_id', (int) $reservation['order_id'])
				->update('roulette_spins', array('status' => 'redeemed', 'used_at' => $this->databaseUtcNow()));
			$this->db->where('id', (int) $reservation['order_id'])->update('orders', array('pay_success' => 1));

			if ($this->db->trans_status() === false) {
				throw new RuntimeException('Could not confirm the roulette reward payment.');
			}
			$this->db->trans_commit();
			return true;
		} catch (Throwable $exception) {
			$this->db->trans_rollback();
			log_message('error', 'Roulette payment confirmation failed: ' . $exception->getMessage());
			return false;
		}
	}

	public function failPaynetReservation($externalId, $force = false)
	{
		if (!$this->isReady()) {
			return false;
		}
		$reservation = $this->db->where('paynet_external_id', trim((string) $externalId))
			->where('status', 'pending')->get('roulette_payment_reservations')->row_array();
		if (empty($reservation)) {
			return false;
		}
		if (!$force && $reservation['reserved_until'] > $this->databaseUtcNow()) {
			return false;
		}
		return $this->releaseReservation((int) $reservation['id']);
	}

	/** Resolve expired forms against Paynet before releasing their reward. */
	public function reconcileExpiredReservations($clientId = null)
	{
		if (!$this->isReady()) {
			return;
		}

		$sql = "SELECT r.id, r.paynet_external_id
			FROM roulette_payment_reservations r
			JOIN roulette_spins s ON s.id = r.spin_id
			WHERE r.status = 'pending' AND r.reserved_until <= UTC_TIMESTAMP()";
		$params = array();
		if ($clientId !== null) {
			$sql .= ' AND s.client_id = ?';
			$params[] = (int) $clientId;
		}
		$sql .= ' ORDER BY r.id ASC LIMIT 5';

		$reservations = $this->db->query($sql, $params)->result_array();
		foreach ($reservations as $reservation) {
			$status = $this->fetchPaynetStatus($reservation['paynet_external_id']);
			if ($status === 4) {
				$this->confirmPaynetReservation($reservation['paynet_external_id']);
			} elseif ($status !== null) {
				$this->releaseReservation((int) $reservation['id']);
			}
		}

		$this->db->query(
			"UPDATE roulette_spins s
			 JOIN orders o ON o.id = s.order_id
			 LEFT JOIN roulette_payment_reservations r ON r.spin_id = s.id AND r.status = 'pending'
			 SET s.status = IF(s.expires_at > UTC_TIMESTAMP(), 'active', 'expired'),
			     s.order_id = NULL
			 WHERE s.status = 'reserved' AND o.payment = 2
			   AND (o.ExternalID IS NULL OR o.ExternalID = '')
			   AND o.added <= UTC_TIMESTAMP() - INTERVAL 10 MINUTE
			   AND r.id IS NULL"
		);
	}

	private function releaseReservation($reservationId)
	{
		$this->db->trans_begin();
		try {
			$reservation = $this->db->query(
				'SELECT * FROM roulette_payment_reservations WHERE id = ? FOR UPDATE',
				array((int) $reservationId)
			)->row_array();
			if (empty($reservation) || $reservation['status'] !== 'pending') {
				$this->db->trans_commit();
				return false;
			}

			$now = $this->databaseUtcNow();
			$spin = $this->db->query(
				'SELECT id, status, order_id, expires_at FROM roulette_spins WHERE id = ? FOR UPDATE',
				array((int) $reservation['spin_id'])
			)->row_array();
			$this->db->where('id', (int) $reservationId)->update('roulette_payment_reservations', array(
				'status' => 'expired', 'resolved_at' => $now,
			));
			if (!empty($spin) && $spin['status'] === 'reserved' && (int) $spin['order_id'] === (int) $reservation['order_id']) {
				$this->db->where('id', (int) $spin['id'])->update('roulette_spins', array(
					'status' => $spin['expires_at'] > $now ? 'active' : 'expired',
					'order_id' => null,
					'used_at' => null,
				));
			}

			if ($this->db->trans_status() === false) {
				throw new RuntimeException('Could not release the roulette reward reservation.');
			}
			$this->db->trans_commit();
			return true;
		} catch (Throwable $exception) {
			$this->db->trans_rollback();
			log_message('error', 'Roulette reservation release failed: ' . $exception->getMessage());
			return false;
		}
	}

	private function fetchPaynetStatus($externalId)
	{
		$apiFile = realpath(APPPATH . 'libraries/paynet/paynet/PaynetAPI.php');
		$configFile = realpath(APPPATH . 'libraries/paynet/paynet/PaynetConfig.php');
		if (!$apiFile || !$configFile) {
			return null;
		}
		require_once $apiFile;
		require_once $configFile;

		try {
			$api = new PaynetEcomAPI(MERCHANT_CODE, MERCHANT_SEC_KEY, MERCHANT_USER, MERCHANT_USER_PASS);
			$result = $api->PaymentGet((string) $externalId);
			if (empty($result) || !$result->IsOk() || !is_array($result->Data)) {
				return null;
			}
			$data = $result->Data;
			if (isset($data['status']) && is_numeric($data['status'])) {
				return (int) $data['status'];
			}
			if (isset($data[0]['Status']) && is_numeric($data[0]['Status'])) {
				return (int) $data[0]['Status'];
			}
		} catch (Throwable $exception) {
			log_message('error', 'Could not verify expired Paynet reservation: ' . $exception->getMessage());
		}
		return null;
	}

	private function formatPaynetExpiry($utcValue)
	{
		$date = DateTimeImmutable::createFromFormat(
			'!Y-m-d H:i:s',
			(string) $utcValue,
			new DateTimeZone('UTC')
		);
		if (!$date) {
			return null;
		}
		return $date->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('Y-m-d\\TH:i:s');
	}

	private function getFirstQualifiedOrderAt($clientId)
	{
		$row = $this->db->query(
			"SELECT MIN(added) AS first_order_at
			 FROM orders
			 WHERE client_id = ?
			   AND status = 'finished'
			   AND (payment IS NULL OR payment <> 2 OR COALESCE(pay_success, 0) = 1)",
			array((int) $clientId)
		)->row_array();

		$value = trim((string) ($row['first_order_at'] ?? ''));
		return $value !== '' ? $value : null;
	}

	/**
	 * The weekly period is a calendar period, not a rolling 168-hour cooldown:
	 * Monday 00:00 through the following Monday 00:00 in the shop timezone.
	 */
	private function getWeeklyWindow($utcNow)
	{
		$utc = DateTimeImmutable::createFromFormat(
			'!Y-m-d H:i:s',
			(string) $utcNow,
			new DateTimeZone('UTC')
		);
		if (!$utc) {
			$utc = new DateTimeImmutable('now', new DateTimeZone('UTC'));
		}

		$local = $utc->setTimezone(new DateTimeZone('Europe/Bucharest'));
		$daysSinceMonday = (int) $local->format('N') - 1;
		$calendarMonday = $local
			->setTime(0, 0, 0)
			->modify('-' . $daysSinceMonday . ' days');
		$beforeMondayReset = $local < $calendarMonday;
		$startLocal = $beforeMondayReset ? $calendarMonday->modify('-7 days') : $calendarMonday;
		$endLocal = $startLocal->modify('+7 days');

		return array(
			'start' => $startLocal->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'),
			'end' => $endLocal->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'),
			'before_reset' => $beforeMondayReset,
		);
	}

	private function isWeeklyWindowOpen($firstOrderAt, array $weeklyWindow)
	{
		$firstOrder = DateTimeImmutable::createFromFormat(
			'!Y-m-d H:i:s',
			(string) $firstOrderAt,
			new DateTimeZone('UTC')
		);
		$windowStart = DateTimeImmutable::createFromFormat(
			'!Y-m-d H:i:s',
			(string) $weeklyWindow['start'],
			new DateTimeZone('UTC')
		);

		if (!$firstOrder || !$windowStart) {
			return false;
		}

		// A first purchase made before Monday 00:00 waits for that reset.
		return empty($weeklyWindow['before_reset']) || $firstOrder <= $windowStart;
	}

	private function getSpinInWindow($clientId, $start, $end)
	{
		return $this->db->query(
			"SELECT * FROM roulette_spins
			 WHERE client_id = ?
			   AND spun_at >= ?
			   AND spun_at < ?
			   AND status <> 'cancelled'
			 ORDER BY id DESC LIMIT 1",
			array((int) $clientId, (string) $start, (string) $end)
		)->row_array();
	}

	private function getConfiguration($lockRows)
	{
		if (!$this->isReady()) {
			return array('enabled' => false, 'interval_hours' => null, 'valid' => false, 'prizes' => array());
		}

		$settingsQuery = 'SELECT is_visible, is_enabled, spin_interval_hours, titleRU, titleRO, descriptionRU, descriptionRO, rulesRU, rulesRO FROM roulette_settings WHERE id = 1';
		if ($lockRows) {
			$settingsQuery .= ' FOR UPDATE';
		}
		$settings = $this->db->query($settingsQuery)->row_array();
		if (empty($settings)) {
			return array('enabled' => false, 'interval_hours' => null, 'valid' => false, 'prizes' => array());
		}

		if ($lockRows) {
			$prizes = $this->db->query(
				'SELECT id, code, titleRU, titleRO, reward_type, reward_value, reward_payload, probability
				 FROM roulette_prizes
				 WHERE is_enabled = 1
				 ORDER BY id ASC
				 FOR UPDATE'
			)->result_array();
		} else {
			$prizes = $this->db->where('is_enabled', 1)
				->order_by('id', 'ASC')
				->get('roulette_prizes')
				->result_array();
		}

		$probabilityTotal = 0;
		$valid = count($prizes) > 0;
		foreach ($prizes as &$prize) {
			$value = isset($prize['reward_value']) ? (float) $prize['reward_value'] : 0;
			$probability = isset($prize['probability']) ? (float) $prize['probability'] : 0;
			$probabilityBasisPoints = (int) round($probability * 100);
			$isDiscountPrize = $prize['reward_type'] === 'discount_percent';
			$isFreeShippingPrize = $prize['reward_type'] === 'free_shipping';
			$isGiftPrize = $prize['reward_type'] === 'gift';
			$giftValid = false;
			if ($isGiftPrize && $this->db->table_exists('gifts') && ctype_digit((string) ($prize['reward_payload'] ?? ''))) {
				$giftQuery = $this->db->where('id', (int) $prize['reward_payload'])->where('is_enabled', 1);
				if ($this->db->field_exists('show_on_site', 'gifts')) {
					$giftQuery->where('show_on_site', 1);
				}
				$giftValid = !empty($giftQuery->get('gifts')->row());
			}

			if (
				(!$isDiscountPrize && !$isFreeShippingPrize && !$isGiftPrize)
				|| ($isDiscountPrize && ($value <= 0 || $value > 100))
				|| ($isGiftPrize && !$giftValid)
				|| $probability <= 0
				|| $probability > 100
				|| $probabilityBasisPoints < 1
				|| trim((string) $prize['titleRU']) === ''
				|| trim((string) $prize['titleRO']) === ''
			) {
				$valid = false;
			}
			$prize['probability_basis_points'] = $probabilityBasisPoints;
			$probabilityTotal += $probabilityBasisPoints;
		}
		unset($prize);

		if ($probabilityTotal !== 10000) {
			$valid = false;
		}

		return array(
			'visible' => (int) $settings['is_visible'] === 1,
			'enabled' => (int) $settings['is_enabled'] === 1,
			// Kept in the response for compatibility with the existing frontend;
			// the actual eligibility check is the fixed Monday 00:00 period above.
			'interval_hours' => 168,
			'settings' => $settings,
			'valid' => $valid,
			'prizes' => $prizes,
		);
	}

	private function formatSettings(array $configuration, $language)
	{
		$settings = isset($configuration['settings']) && is_array($configuration['settings'])
			? $configuration['settings']
			: array();
		$languageSuffix = strtolower((string) $language) === 'ro' ? 'RO' : 'RU';

		return array(
			'title' => isset($settings['title' . $languageSuffix]) ? (string) $settings['title' . $languageSuffix] : '',
			'description' => isset($settings['description' . $languageSuffix]) ? (string) $settings['description' . $languageSuffix] : '',
			'rules' => isset($settings['rules' . $languageSuffix]) ? (string) $settings['rules' . $languageSuffix] : '',
		);
	}

	private function formatAvailablePrizes(array $configuration, $language)
	{
		if (empty($configuration['valid']) || empty($configuration['prizes'])) {
			return array();
		}

		$languageSuffix = strtolower((string) $language) === 'ro' ? 'RO' : 'RU';
		$formatted = array();
		foreach ($configuration['prizes'] as $prize) {
			$formatted[] = array(
				'code' => (string) $prize['code'],
				'type' => (string) $prize['reward_type'],
				'title' => (string) $prize['title' . $languageSuffix],
				'value' => (float) $prize['reward_value'],
				'probability' => (float) $prize['probability'],
			);
		}

		return $formatted;
	}

	private function choosePrize(array $prizes)
	{
		$draw = random_int(1, 10000);
		$cumulative = 0;
		foreach ($prizes as $prize) {
			$cumulative += (int) $prize['probability_basis_points'];
			if ($draw <= $cumulative) {
				return $prize;
			}
		}

		throw new RuntimeException('No roulette prize matched the weighted draw.');
	}

	private function getActiveSpin($clientId)
	{
		return $this->db->query(
			"SELECT * FROM roulette_spins
			 WHERE client_id = ? AND status = 'active' AND order_id IS NULL AND expires_at > UTC_TIMESTAMP()
			 ORDER BY id DESC LIMIT 1",
			array((int) $clientId)
		)->row_array();
	}

	private function getOutstandingSpin($clientId)
	{
		return $this->db->query(
			"SELECT * FROM roulette_spins
			 WHERE client_id = ?
			   AND ((status = 'active' AND order_id IS NULL AND expires_at > UTC_TIMESTAMP())
			     OR status = 'reserved')
			 ORDER BY id DESC LIMIT 1",
			array((int) $clientId)
		)->row_array();
	}

	private function expireSpins($clientId)
	{
		$this->db->query(
			"UPDATE roulette_spins
			 SET status = 'expired'
			 WHERE client_id = ? AND status = 'active' AND expires_at <= UTC_TIMESTAMP()",
			array((int) $clientId)
		);
	}

	private function databaseUtcNow()
	{
		$row = $this->db->query('SELECT UTC_TIMESTAMP() AS utc_now')->row_array();
		return !empty($row['utc_now']) ? $row['utc_now'] : gmdate('Y-m-d H:i:s');
	}

	private function toIso8601($value)
	{
		if (empty($value)) {
			return null;
		}
		$date = DateTimeImmutable::createFromFormat(
			'!Y-m-d H:i:s',
			$value,
			new DateTimeZone('UTC')
		);
		return $date ? $date->format(DateTime::ATOM) : null;
	}

	private function formatSpin(array $spin, $language)
	{
		$titleField = strtolower((string) $language) === 'ro' ? 'titleRO_snapshot' : 'titleRU_snapshot';
		return array(
			'id' => (int) $spin['id'],
			'code' => $spin['prize_code_snapshot'],
			'type' => $spin['reward_type_snapshot'],
			'value' => (float) $spin['reward_value_snapshot'],
			'gift_id' => $spin['reward_type_snapshot'] === 'gift' ? (int) $spin['reward_payload_snapshot'] : null,
			'title' => $spin[$titleField],
			'spun_at' => $this->toIso8601($spin['spun_at']),
			'expires_at' => $this->toIso8601($spin['expires_at']),
			'status' => $spin['status'],
		);
	}
}
