<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Product_imports extends BackEndController
{
	public function __construct()
	{
		parent::__construct(__CLASS__);
		$this->data['title'] = 'Изменения импорта товаров';
	}

	public function index()
	{
		$action = $this->input->get('action', true);
		$action = in_array($action, array('added', 'updated'), true) ? $action : '';
		$status = $this->input->get('status', true);
		$status = in_array($status, array('new', 'edited'), true) ? $status : 'new';
		$search = trim((string) $this->input->get('q', true));
		$brandId = max(0, (int) $this->input->get('brand_id', true));
		$page = max(1, (int) $this->input->get('page', true));
		$perPage = 50;

		if (!$this->db->table_exists('product_import_changes')) {
			$this->data['import_changes'] = array();
			$this->data['migration_required'] = true;
			$this->data['status_column_missing'] = false;
			$this->setViewData($action, $status, $search, $brandId, 1, $perPage, 0);
			return $this->renderIndex();
		}

		if (!$this->db->field_exists('status', 'product_import_changes')) {
			$this->data['import_changes'] = array();
			$this->data['migration_required'] = false;
			$this->data['status_column_missing'] = true;
			$this->setViewData($action, $status, $search, $brandId, 1, $perPage, 0);
			return $this->renderIndex();
		}

		$this->data['migration_required'] = false;
		$this->data['status_column_missing'] = false;
		$this->load->model('brands_model');
		$this->data['brands'] = $this->brands_model->find('title ASC');

		$this->applyFilters($action, $status, $search, $brandId);
		$total = $this->db->count_all_results();
		$totalPages = max(1, (int) ceil($total / $perPage));
		$page = min($page, $totalPages);

		$this->applyFilters($action, $status, $search, $brandId);
		$rows = $this->db
			->select('pic.id, pic.product_id, pic.product_guid, pic.action, pic.changed_fields, pic.created_at, pic.status, p.titleRU, p.titleRO, p.SKU, p.brand_id, b.title AS brand_title', false)
			->join('brands b', 'b.id = p.brand_id', 'left')
			->order_by('pic.created_at', 'DESC')
			->order_by('pic.id', 'DESC')
			->limit($perPage, ($page - 1) * $perPage)
			->get()
			->result_array();

		foreach ($rows as &$row) {
			$decoded = json_decode($row['changed_fields'], true);
			$row['changed_fields'] = is_array($decoded) ? $decoded : array();
		}
		unset($row);

		$this->data['import_changes'] = $rows;
		$this->setViewData($action, $status, $search, $brandId, $page, $perPage, $total);
		$this->data['total_pages'] = $totalPages;
		$this->data['brands'] = isset($this->data['brands']) ? $this->data['brands'] : array();
		$this->renderIndex();
	}

	public function remove($id = 0)
	{
		check_if_POST();
		$id = (int) $id;
		if ($id > 0 && $this->db->table_exists('product_import_changes')) {
			$this->db->where('id', $id)->delete('product_import_changes');
			$_SESSION['success'] = 'Запись импорта удалена.';
		}
		redirect($this->indexUrlFromRequest());
	}

	public function clear_processed()
	{
		check_if_POST();
		if (!$this->db->table_exists('product_import_changes') || !$this->db->field_exists('status', 'product_import_changes')) {
			$_SESSION['error'] = array('Журнал импорта не настроен.');
			redirect('/' . ADM_CONTROLLER . '/product_imports?status=edited');
			return;
		}

		$action = $this->input->post('action', true);
		$action = in_array($action, array('added', 'updated'), true) ? $action : '';
		$search = trim((string) $this->input->post('q', true));
		$brandId = max(0, (int) $this->input->post('brand_id', true));

		$this->applyFilters($action, 'edited', $search, $brandId);
		$ids = $this->db->select('pic.id')->get()->result_array();
		$ids = array_map('intval', array_column($ids, 'id'));

		if (!empty($ids)) {
			$this->db->where_in('id', $ids)->delete('product_import_changes');
			$_SESSION['success'] = count($ids) . ' обработанных записей удалено из журнала проверок. Товары в каталоге не затронуты.';
		} else {
			$_SESSION['success'] = 'Подходящих обработанных записей нет. Товары в каталоге не затронуты.';
		}

		$params = array('status' => 'edited');
		if ($action !== '') $params['action'] = $action;
		if ($brandId > 0) $params['brand_id'] = $brandId;
		if ($search !== '') $params['q'] = $search;
		redirect('/' . ADM_CONTROLLER . '/product_imports?' . http_build_query($params));
	}

	private function applyFilters($action, $status, $search, $brandId)
	{
		$this->db
			->from('product_import_changes pic')
			->join('products p', 'p.id = pic.product_id', 'left');

		if ($action !== '') $this->db->where('pic.action', $action);
		$this->db->where('pic.status', $status);
		if ($brandId > 0) $this->db->where('p.brand_id', $brandId);

		if ($search !== '') {
			$this->db
				->group_start()
				->like('pic.product_guid', $search)
				->or_like('p.SKU', $search)
				->or_like('p.titleRU', $search)
				->or_like('p.titleRO', $search)
				->group_end();
		}
	}

	private function setViewData($action, $status, $search, $brandId, $page, $perPage, $total)
	{
		$this->data['filter_action'] = $action;
		$this->data['filter_status'] = $status;
		$this->data['filter_search'] = $search;
		$this->data['filter_brand_id'] = $brandId;
		$this->data['current_page'] = $page;
		$this->data['per_page'] = $perPage;
		$this->data['total_rows'] = $total;
		$this->data['total_pages'] = max(1, (int) ceil($total / $perPage));
	}

	private function renderIndex()
	{
		$this->data['inner_view'] = $this->folder . 'index';
		$this->load->vars($this->data);
		$this->load->view($this->main_layout);
	}

	private function indexUrlFromRequest()
	{
		$params = array();
		$allowed = array('status', 'action', 'brand_id', 'q', 'page');
		foreach ($allowed as $key) {
			$value = $this->input->post($key, true);
			if ($value !== null && $value !== '') $params[$key] = $value;
		}
		$url = '/' . ADM_CONTROLLER . '/product_imports';
		return empty($params) ? $url : $url . '?' . http_build_query($params);
	}
}
