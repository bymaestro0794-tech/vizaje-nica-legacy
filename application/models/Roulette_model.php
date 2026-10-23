<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Roulette_model extends CI_Model
{
	public function isInstalled()
	{
		return $this->db->table_exists('roulette_settings')
			&& $this->db->table_exists('roulette_prizes');
	}

	public function isGameplayInstalled()
	{
		if (!$this->isInstalled()) {
			return false;
		}

		return $this->db->field_exists('is_enabled', 'roulette_settings')
			&& $this->db->field_exists('is_visible', 'roulette_settings')
			&& $this->db->field_exists('spin_interval_hours', 'roulette_settings')
			&& $this->db->table_exists('roulette_account_state')
			&& $this->db->table_exists('roulette_spins')
			&& $this->db->field_exists('reward_payload_snapshot', 'roulette_spins')
			&& $this->db->table_exists('roulette_payment_reservations');
	}

	public function getSettings()
	{
		return $this->db->where('id', 1)->get('roulette_settings')->row_array();
	}

	public function getPrizes()
	{
		return $this->db
			->order_by('sort_order', 'ASC')
			->order_by('id', 'ASC')
			->get('roulette_prizes')
			->result_array();
	}

	public function getAdminSpinHistory(array $filters = array())
	{
		if (!$this->isGameplayInstalled()) {
			return array();
		}

		$search = trim((string) ($filters['q'] ?? ''));
		$status = trim((string) ($filters['status'] ?? ''));

		$this->db->select("s.id, s.client_id, s.prize_code_snapshot, s.reward_value_snapshot,
			s.titleRU_snapshot, s.spun_at, s.expires_at, s.status, s.order_id, s.used_at,
			c.name AS client_name, c.surname AS client_surname, c.email AS client_email, c.phone AS client_phone,
			o.status AS order_status, o.payment AS order_payment, o.pay_success AS order_pay_success,
			o.promo_discount AS order_promo_discount, r.status AS reservation_status, r.reserved_until", false);
		$this->db->from('roulette_spins s');
		$this->db->join('clients c', 'c.id = s.client_id', 'left');
		$this->db->join('orders o', 'o.id = s.order_id', 'left');
		$this->db->join(
			'roulette_payment_reservations r',
			'r.id = (SELECT MAX(r2.id) FROM roulette_payment_reservations r2 WHERE r2.spin_id = s.id)',
			'left',
			false
		);

		if ($status !== '') {
			$this->db->where('s.status', $status);
		}
		if ($search !== '') {
			$this->db->group_start();
			$this->db->like('s.id', $search);
			$this->db->or_like('s.client_id', $search);
			$this->db->or_like('s.order_id', $search);
			$this->db->or_like('c.name', $search);
			$this->db->or_like('c.surname', $search);
			$this->db->or_like('c.email', $search);
			$this->db->or_like('c.phone', $search);
			$this->db->group_end();
		}

		return $this->db
			->order_by('s.spun_at', 'DESC')
			->order_by('s.id', 'DESC')
			->limit(500)
			->get()
			->result_array();
	}

	/**
	 * DEV-only admin reset: preserves the spin ledger, removes the active reward
	 * and clears the account cooldown so that the same test account can spin again.
	 * Paid, reserved, and order-bound rewards are intentionally never mutable here.
	 */
	public function cancelActiveSpinForTesting($spinId)
	{
		$spinId = (int) $spinId;
		if (!$this->isGameplayInstalled() || $spinId < 1) {
			return array('ok' => false, 'reason' => 'not_available');
		}

		$this->db->trans_begin();
		try {
			$spin = $this->db->query(
				'SELECT id, client_id, status, order_id FROM roulette_spins WHERE id = ? FOR UPDATE',
				array($spinId)
			)->row_array();
			if (empty($spin)) {
				$this->db->trans_rollback();
				return array('ok' => false, 'reason' => 'not_found');
			}
			if ($spin['status'] !== 'active' || !empty($spin['order_id'])) {
				$this->db->trans_rollback();
				return array('ok' => false, 'reason' => 'not_cancellable');
			}

			$updated = $this->db
				->where('id', $spinId)
				->where('status', 'active')
				->where('order_id IS NULL', null, false)
				->update('roulette_spins', array('status' => 'cancelled'));
			if (!$updated || $this->db->affected_rows() !== 1) {
				throw new RuntimeException('Could not cancel the active roulette spin.');
			}

			$this->db->query(
				'INSERT INTO roulette_account_state (client_id, next_spin_at, created_at, updated_at)
				 VALUES (?, NULL, UTC_TIMESTAMP(), UTC_TIMESTAMP())
				 ON DUPLICATE KEY UPDATE next_spin_at = NULL, updated_at = UTC_TIMESTAMP()',
				array((int) $spin['client_id'])
			);

			if ($this->db->trans_status() === false) {
				throw new RuntimeException('Roulette reset transaction failed.');
			}
			$this->db->trans_commit();
			return array('ok' => true, 'client_id' => (int) $spin['client_id']);
		} catch (Throwable $exception) {
			$this->db->trans_rollback();
			log_message('error', 'Roulette testing reset failed: ' . $exception->getMessage());
			return array('ok' => false, 'reason' => 'failed');
		}
	}

	public function saveConfiguration(array $settings, array $prizes)
	{
		$this->db->trans_begin();

		$settingsSaved = $this->db->where('id', 1)->update('roulette_settings', $settings);
		if ($settingsSaved === false) {
			$this->db->trans_rollback();
			return false;
		}

		foreach ($prizes as $code => $prize) {
			$prizeSaved = $this->db->where('code', $code)->update('roulette_prizes', $prize);
			if ($prizeSaved === false) {
				$this->db->trans_rollback();
				return false;
			}
		}

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
			return false;
		}

		$this->db->trans_commit();
		return true;
	}
}
