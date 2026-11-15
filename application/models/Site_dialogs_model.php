<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Site_dialogs_model extends CI_Model
{
	private $table = 'site_dialogs';

	public function isInstalled()
	{
		return $this->db->table_exists($this->table)
			&& $this->db->field_exists('title_ru', $this->table)
			&& $this->db->field_exists('display_delay_seconds', $this->table)
			&& $this->db->field_exists('is_active', $this->table);
	}

	public function getAdminItems()
	{
		if (!$this->isInstalled()) {
			return array();
		}

		return $this->db
			->order_by('priority', 'DESC')
			->order_by('id', 'DESC')
			->get($this->table)
			->result_array();
	}

	public function getById($id)
	{
		if (!$this->isInstalled()) {
			return null;
		}

		return $this->db->where('id', (int) $id)->get($this->table)->row_array();
	}

	public function saveCampaign(array $data, $id = 0)
	{
		if (!$this->isInstalled()) {
			return false;
		}

		if ((int) $id > 0) {
			return $this->db->where('id', (int) $id)->update($this->table, $data);
		}

		return $this->db->insert($this->table, $data) ? (int) $this->db->insert_id() : false;
	}

	public function setActive($id, $active)
	{
		if (!$this->isInstalled()) {
			return false;
		}

		return $this->db
			->where('id', (int) $id)
			->update($this->table, array('is_active' => !empty($active) ? 1 : 0));
	}

	public function deleteCampaign($id)
	{
		if (!$this->isInstalled()) {
			return false;
		}

		$item = $this->getById($id);
		if (empty($item)) {
			return false;
		}

		$this->db->where('id', (int) $id)->delete($this->table);
		return $this->db->affected_rows() > 0 ? $item : false;
	}

	public function getActiveForDisplay($language, $isAuthenticated)
	{
		if (!$this->isInstalled()) {
			return array();
		}

		$now = date('Y-m-d H:i:s');
		$this->db->from($this->table);
		$this->db->where('is_active', 1);
		$this->db->group_start()
			->where('starts_at IS NULL', null, false)
			->or_where('starts_at <=', $now)
			->group_end();
		$this->db->group_start()
			->where('ends_at IS NULL', null, false)
			->or_where('ends_at >=', $now)
			->group_end();
		if ($isAuthenticated) {
			$this->db->where_in('audience', array('all', 'customer'));
		} else {
			$this->db->where_in('audience', array('all', 'guest'));
		}
		$rows = $this->db
			->order_by('priority', 'DESC')
			->order_by('id', 'ASC')
			->limit(10)
			->get()
			->result_array();

		$language = strtolower((string) $language) === 'ro' ? 'ro' : 'ru';
		$localized = array();
		foreach ($rows as $row) {
			$title = trim((string) ($row['title_' . $language] ?? ''));
			$text = trim((string) ($row['text_' . $language] ?? ''));
			if ($title === '' || $text === '') {
				$title = trim((string) ($row['title_ru'] ?? ''));
				$text = trim((string) ($row['text_ru'] ?? ''));
			}
			if ($title === '' || $text === '') {
				continue;
			}

			$buttonText = trim((string) ($row['button_text_' . $language] ?? ''));
			$targetUrl = trim((string) ($row['target_url_' . $language] ?? ''));
			if ($buttonText === '' || $targetUrl === '') {
				$buttonText = '';
				$targetUrl = '';
			}

			$localized[] = array(
				'id' => (int) $row['id'],
				'title' => $title,
				'text' => $text,
				'image' => trim((string) $row['image']),
				'button_text' => $buttonText,
				'target_url' => $targetUrl,
				'delay_ms' => max(0, min(1800, (int) $row['display_delay_seconds'])) * 1000,
				'priority' => (int) $row['priority'],
			);
		}

		return $localized;
	}
}
