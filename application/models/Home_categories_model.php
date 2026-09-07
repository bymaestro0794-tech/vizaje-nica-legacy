<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home_categories_model extends CI_Model
{
	protected $table = 'home_categories';

	public function get_admin_items()
	{
		return $this->db
			->order_by('sorder', 'ASC')
			->order_by('id', 'DESC')
			->get($this->table)
			->result();
	}

	public function get_item($id)
	{
		return $this->db
			->where('id', (int) $id)
			->get($this->table)
			->row();
	}

	public function create(array $data)
	{
		$maxOrderRow = $this->db
			->select_max('sorder')
			->get($this->table)
			->row();

		$data['sorder'] = !empty($maxOrderRow->sorder)
			? (int) $maxOrderRow->sorder + 1
			: 1;

		$data['isShown'] = !empty($data['isShown'])
			? 1
			: 0;

		return $this->db->insert(
			$this->table,
			$data
		);
	}

	public function updateItem($id, array $data)
	{
		$data['isShown'] = !empty($data['isShown'])
			? 1
			: 0;

		return $this->db
			->where('id', (int) $id)
			->update(
				$this->table,
				$data
			);
	}

	public function updateOrder(array $orders)
	{
		$this->db->trans_start();

		foreach ($orders as $id => $sorder) {
			$id = (int) $id;

			if ($id <= 0) {
				continue;
			}

			$this->db
				->where('id', $id)
				->update(
					$this->table,
					[
						'sorder' => max(
							0,
							(int) $sorder
						),
					]
				);
		}

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	public function deleteItem($id)
	{
		return $this->db
			->where('id', (int) $id)
			->delete($this->table);
	}

	public function get_front_items($lang = 'RU')
	{
		$lang = strtoupper(
			trim((string) $lang)
		);

		if (!in_array($lang, ['RU', 'RO'], true)) {
			$lang = 'RU';
		}

		return $this->db
			->select("
				id,
				title{$lang} AS title,
				url{$lang} AS url,
				img,
				sorder
			")
			->where('isShown', 1)
			->order_by('sorder', 'ASC')
			->order_by('id', 'ASC')
			->get($this->table)
			->result();
	}
}