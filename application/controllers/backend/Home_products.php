<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home_products extends BackEndController
{
	protected $sectionKey = 'hits';

	protected $sections = [
		'hits' => [
			'title' => 'Хиты',
		],
		'new' => [
			'title' => 'Новинки',
		],
		'sale' => [
			'title' => 'Скидки',
		],
	];

	public function __construct()
	{
		parent::__construct(__CLASS__);

		$this->load->model('products_model');
		$this->load->model('home_product_items_model');

		$this->resolveSectionFromGet();

		$this->data['title'] = 'Товары на главной';
		$this->data['section_key'] = $this->sectionKey;
		$this->data['section_title'] =
			$this->sections[$this->sectionKey]['title'];
		$this->data['sections'] = $this->sections;
	}

public function index()
{
	$searchQuery = trim(
		(string) $this->input->get(
			'query',
			true
		)
	);

	$searchProducts = [];

	if ($searchQuery !== '') {
		$searchProducts =
			$this->products_model
				->search_get_products_admin(
					$searchQuery
				);
	}

	$selectedItems =
		$this->home_product_items_model
			->get_admin_items(
				$this->sectionKey
			);

	$selectedProductIds = [];

	foreach ($selectedItems as $selectedItem) {
		$selectedProductIds[] =
			(int) $selectedItem->product_id;
	}

	$this->data['query'] =
		$searchQuery;

	$this->data['search_products'] =
		$searchProducts;

	$this->data['selected_items'] =
		$selectedItems;

	$this->data['selected_product_ids'] =
		$selectedProductIds;

	/*
	|--------------------------------------------------------------------------
	| Production canonical routes
	|--------------------------------------------------------------------------
	|
	| На production сервер удаляет завершающий слеш.
	| Поэтому POST-адреса сразу формируем без него,
	| чтобы не происходил 301 и потеря POST-запроса.
	|
	*/

	$this->data['add_product_path'] =
		'/' . ADM_CONTROLLER
		. '/home_products/add';

	$this->data['update_order_path'] =
		'/' . ADM_CONTROLLER
		. '/home_products/update_order';

	$this->data['delete_product_path'] =
		'/' . ADM_CONTROLLER
		. '/home_products/delete/';

	$this->data['inner_view'] =
		'dashboard/home_products/index';

	$this->load->vars($this->data);
	$this->load->view($this->main_layout);
}

	public function add()
{
	check_if_POST();

	$this->resolveSectionFromPost();

	$productId = (int) $this->input->post(
		'product_id',
		true
	);

	$result =
		$this->home_product_items_model
			->addProduct(
				$this->sectionKey,
				$productId
			);

	if (!empty($result['success'])) {
		$_SESSION['success'] =
			$result['message'];
	} else {
		$_SESSION['error'] = [
			$result['message'],
		];
	}

	$this->redirectToSection();
}

	public function update_order()
	{
		check_if_POST();

		$this->resolveSectionFromPost();

		$orders = $this->input->post('so');

		if (
			empty($orders)
			|| !is_array($orders)
		) {
			$_SESSION['error'] = [
				'Некорректные данные сортировки.',
			];

			$this->redirectToSection();
		}

		$result =
			$this->home_product_items_model
				->updateOrder(
					$this->sectionKey,
					$orders
				);

		if ($result) {
			$_SESSION['success'] =
				'Порядок товаров обновлён.';
		} else {
			$_SESSION['error'] = [
				'Не удалось обновить порядок.',
			];
		}

		$this->redirectToSection();
	}

	public function delete($id = 0)
	{
		$id = (int) $id;

		if ($id <= 0) {
			throw_on_404();
		}

		$this->resolveSectionFromGet();

		$result =
			$this->home_product_items_model
				->deleteItem(
					$this->sectionKey,
					$id
				);

		if ($result) {
			$_SESSION['success'] =
				'Товар удалён из подборки.';
		} else {
			$_SESSION['error'] = [
				'Не удалось удалить товар.',
			];
		}

		$this->redirectToSection();
	}

	private function resolveSectionFromGet()
	{
		$section = strtolower(
			trim(
				(string) $this->input->get(
					'section',
					true
				)
			)
		);

		if (isset($this->sections[$section])) {
			$this->sectionKey = $section;
		}
	}

	private function resolveSectionFromPost()
	{
		$section = strtolower(
			trim(
				(string) $this->input->post(
					'section_key',
					true
				)
			)
		);

		if (isset($this->sections[$section])) {
			$this->sectionKey = $section;
		}
	}

	private function redirectToSection()
{
	redirect(
		'/' . ADM_CONTROLLER
		. '/home_products'
		. '?section='
		. urlencode($this->sectionKey)
	);
}
}