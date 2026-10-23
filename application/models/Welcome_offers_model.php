<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Welcome_offers_model extends BaseModel
{
	public function isInstalled()
	{
		return $this->db->table_exists('clients')
			&& $this->db->field_exists('welcome_offer_started_at', 'clients')
			&& $this->db->field_exists('welcome_offer_expires_at', 'clients')
			&& $this->db->table_exists('promocodes')
			&& $this->db->table_exists('promocode_usages')
			&& $this->db->table_exists('orders');
	}

	public function getAdminItems(array $filters = array())
	{
		if (!$this->isInstalled()) {
			return array();
		}

		$search = trim((string) ($filters['q'] ?? ''));
		$state = trim((string) ($filters['state'] ?? ''));

		$this->db->select("\n\t\t\tc.id AS client_id,\n\t\t\tc.name AS client_name,\n\t\t\tc.surname AS client_surname,\n\t\t\tc.email AS client_email,\n\t\t\tc.phone AS client_phone,\n\t\t\tc.welcome_offer_started_at,\n\t\t\tc.welcome_offer_expires_at,\n\t\t\tpu.id AS usage_id,\n\t\t\tpu.created_at AS used_at,\n\t\t\tpu.order_id,\n\t\t\to.status AS order_status,\n\t\t\to.payment AS order_payment,\n\t\t\to.pay_success AS order_pay_success\n\t\t", false);
		$this->db->from('clients c');
		$this->db->join("promocodes p", "UPPER(p.code) = 'WELCOME18'", 'left', false);
		$this->db->join('promocode_usages pu', 'pu.client_id = c.id AND pu.promocode_id = p.id', 'left', false);
		$this->db->join('orders o', 'o.id = pu.order_id', 'left');
		$this->db->where('c.welcome_offer_started_at IS NOT NULL', null, false);

		if ($search !== '') {
			$this->db->group_start();
			$this->db->like('c.id', $search);
			$this->db->or_like('c.name', $search);
			$this->db->or_like('c.surname', $search);
			$this->db->or_like('c.email', $search);
			$this->db->or_like('c.phone', $search);
			$this->db->or_like('pu.order_id', $search);
			$this->db->group_end();
		}

		if ($state === 'used') {
			$this->db->where('pu.id IS NOT NULL', null, false);
		} elseif ($state === 'active') {
			$this->db->where('pu.id IS NULL', null, false);
			$this->db->where('c.welcome_offer_expires_at > UTC_TIMESTAMP()', null, false);
		} elseif ($state === 'expired') {
			$this->db->where('pu.id IS NULL', null, false);
			$this->db->where('c.welcome_offer_expires_at <= UTC_TIMESTAMP()', null, false);
		}

		return $this->db
			->order_by('c.welcome_offer_started_at', 'DESC')
			->order_by('pu.id', 'DESC')
			->limit(500)
			->get()
			->result_array();
	}
}
