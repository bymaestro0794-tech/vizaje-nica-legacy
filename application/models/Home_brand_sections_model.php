<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home_brand_sections_model extends CI_Model
{
	protected $sectionsTable =
		'home_brand_sections';

	protected $productsTable =
		'home_brand_section_products';

	/*
	|--------------------------------------------------------------------------
	| Admin: sections
	|--------------------------------------------------------------------------
	*/

	public function get_admin_sections()
	{
		return $this->db
			->select("
				sections.*,

				(
					SELECT COUNT(*)
					FROM {$this->productsTable}
					WHERE {$this->productsTable}.section_id =
						sections.id
				) AS products_count
			")
			->from(
				$this->sectionsTable
				. ' AS sections'
			)
			->order_by(
				'sections.placement',
				'ASC'
			)
			->order_by(
				'sections.sorder',
				'ASC'
			)
			->order_by(
				'sections.id',
				'DESC'
			)
			->get()
			->result();
	}

	public function get_section($id)
	{
		return $this->db
			->where('id', (int) $id)
			->get($this->sectionsTable)
			->row();
	}

	public function create_section(array $data)
	{
		$placement =
			$this->normalizePlacement(
				$data['placement'] ?? ''
			);

		$maxOrderRow = $this->db
			->select_max('sorder')
			->where(
				'placement',
				$placement
			)
			->get($this->sectionsTable)
			->row();

		$data['placement'] =
			$placement;

		$data['sorder'] =
			!empty($maxOrderRow->sorder)
				? (int) $maxOrderRow->sorder + 1
				: 1;

		$data['isShown'] =
			!empty($data['isShown'])
				? 1
				: 0;

		$inserted = $this->db
			->insert(
				$this->sectionsTable,
				$data
			);

		if (!$inserted) {
			return false;
		}

		return (int) $this->db->insert_id();
	}

	public function update_section(
		$id,
		array $data
	) {
		$id = (int) $id;

		if ($id <= 0) {
			return false;
		}

		if (isset($data['placement'])) {
			$data['placement'] =
				$this->normalizePlacement(
					$data['placement']
				);
		}

		if (array_key_exists(
			'isShown',
			$data
		)) {
			$data['isShown'] =
				!empty($data['isShown'])
					? 1
					: 0;
		}

		return $this->db
			->where('id', $id)
			->update(
				$this->sectionsTable,
				$data
			);
	}

	public function update_sections_order(
		array $orders
	) {
		$this->db->trans_start();

		foreach ($orders as $id => $sorder) {
			$id = (int) $id;

			if ($id <= 0) {
				continue;
			}

			$this->db
				->where('id', $id)
				->update(
					$this->sectionsTable,
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

	public function delete_section($id)
	{
		return $this->db
			->where('id', (int) $id)
			->delete($this->sectionsTable);
	}

	/*
	|--------------------------------------------------------------------------
	| Admin: products
	|--------------------------------------------------------------------------
	*/

	public function get_admin_products(
		$sectionId
	) {
		$sectionId = (int) $sectionId;

		return $this->db
			->select("
				section_products.id,
				section_products.section_id,
				section_products.product_id,
				section_products.sorder,
				section_products.isShown,

				products.SKU,
				products.titleRU,
				products.titleRO,
				products.price,
				products.priceWH,
				products.discount_price,
				products.stock,
				products.isShown
					AS product_isShown,

				(
					SELECT products_img.img
					FROM products_img
					WHERE products_img.product_id =
						products.id
					ORDER BY
						products_img.sorder ASC,
						products_img.id DESC
					LIMIT 1
				) AS img,

				(
					SELECT brands.title
					FROM brands
					WHERE brands.id =
						products.brand_id
					LIMIT 1
				) AS brand_title
			")
			->from(
				$this->productsTable
				. ' AS section_products'
			)
			->join(
				'products',
				'products.id =
					section_products.product_id',
				'left'
			)
			->where(
				'section_products.section_id',
				$sectionId
			)
			->order_by(
				'section_products.sorder',
				'ASC'
			)
			->order_by(
				'section_products.id',
				'DESC'
			)
			->get()
			->result();
	}

	public function add_product(
		$sectionId,
		$productId
	) {
		$sectionId = (int) $sectionId;
		$productId = (int) $productId;

		if (
			$sectionId <= 0
			|| $productId <= 0
		) {
			return [
				'success' => false,
				'message' =>
					'Некорректные данные товара.',
			];
		}

		$sectionExists = $this->db
			->where('id', $sectionId)
			->count_all_results(
				$this->sectionsTable
			);

		if ($sectionExists === 0) {
			return [
				'success' => false,
				'message' =>
					'Брендовая секция не найдена.',
			];
		}

		$productExists = $this->db
			->where('id', $productId)
			->count_all_results('products');

		if ($productExists === 0) {
			return [
				'success' => false,
				'message' =>
					'Товар не найден.',
			];
		}

		$alreadyAdded = $this->db
			->where(
				'section_id',
				$sectionId
			)
			->where(
				'product_id',
				$productId
			)
			->count_all_results(
				$this->productsTable
			);

		if ($alreadyAdded > 0) {
			return [
				'success' => false,
				'message' =>
					'Этот товар уже добавлен в секцию.',
			];
		}

		$maxOrderRow = $this->db
			->select_max('sorder')
			->where(
				'section_id',
				$sectionId
			)
			->get($this->productsTable)
			->row();

		$nextOrder =
			!empty($maxOrderRow->sorder)
				? (int) $maxOrderRow->sorder + 1
				: 1;

		$inserted = $this->db
			->insert(
				$this->productsTable,
				[
					'section_id' =>
						$sectionId,

					'product_id' =>
						$productId,

					'sorder' =>
						$nextOrder,

					'isShown' =>
						1,
				]
			);

		return [
			'success' => (bool) $inserted,

			'message' => $inserted
				? 'Товар добавлен в брендовую секцию.'
				: 'Не удалось добавить товар.',
		];
	}

	public function update_products_order(
		$sectionId,
		array $orders
	) {
		$sectionId = (int) $sectionId;

		$this->db->trans_start();

		foreach ($orders as $id => $sorder) {
			$id = (int) $id;

			if ($id <= 0) {
				continue;
			}

			$this->db
				->where('id', $id)
				->where(
					'section_id',
					$sectionId
				)
				->update(
					$this->productsTable,
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

	public function set_product_visibility(
		$sectionId,
		$id,
		$isShown
	) {
		return $this->db
			->where('id', (int) $id)
			->where(
				'section_id',
				(int) $sectionId
			)
			->update(
				$this->productsTable,
				[
					'isShown' =>
						$isShown ? 1 : 0,
				]
			);
	}

	public function delete_product(
		$sectionId,
		$id
	) {
		return $this->db
			->where('id', (int) $id)
			->where(
				'section_id',
				(int) $sectionId
			)
			->delete($this->productsTable);
	}

	/*
	|--------------------------------------------------------------------------
	| Frontend
	|--------------------------------------------------------------------------
	*/

	public function get_front_section(
		$lang,
		$placement
	) {
		$lang = $this->normalizeLanguage($lang);

		$placement =
			$this->normalizePlacement(
				$placement
			);

		$section = $this->db
			->select("
				id,
				title{$lang} AS title,
				url{$lang} AS url,
				img,
				imgMob,
				placement,
				sorder
			")
			->where(
				'placement',
				$placement
			)
			->where('isShown', 1)
			->order_by('sorder', 'ASC')
			->order_by('id', 'DESC')
			->get($this->sectionsTable)
			->row();

		if (empty($section)) {
			return null;
		}

		$section->products =
			$this->get_front_products(
				$lang,
				$section->id,
				12
			);

		return $section;
	}

	public function get_front_sections(
		$lang,
		$placement
	) {
		$lang = $this->normalizeLanguage($lang);

		$placement =
			$this->normalizePlacement(
				$placement
			);

		$sections = $this->db
			->select("
				id,
				title{$lang} AS title,
				url{$lang} AS url,
				img,
				imgMob,
				placement,
				sorder
			")
			->where(
				'placement',
				$placement
			)
			->where('isShown', 1)
			->order_by('sorder', 'ASC')
			->order_by('id', 'ASC')
			->get($this->sectionsTable)
			->result();

		foreach ($sections as $section) {
			$section->products =
				$this->get_front_products(
					$lang,
					$section->id,
					12
				);
		}

		return $sections;
	}

	public function get_front_products(
		$lang,
		$sectionId,
		$limit = 12
	) {
		$lang =
			$this->normalizeLanguage($lang);

		$sectionId =
			(int) $sectionId;

		$limit = max(
			1,
			min((int) $limit, 30)
		);

		$hasVariableField =
			!empty($_SESSION['isb2b'])
				? "
					EXISTS (
						SELECT 1
						FROM products_variable pv
						WHERE pv.product_id = products.id
							AND pv.isShown = 1
							AND pv.priceWH > 0
					) AS has_variable
				"
				: "
					EXISTS (
						SELECT 1
						FROM products_variable pv
						WHERE pv.product_id = products.id
							AND pv.isShown = 1
							AND pv.price > 0
					) AS has_variable
				";

		$this->db->select("
			products.id,
			products.SKU,
			products.category_id,
			products.brand_id,

			products.title{$lang} AS title,
			products.desc{$lang} AS `desc`,
			products.uri{$lang} AS uri,

			products.price,
			products.priceWH,
			products.discount_price,
			products.sale_percent,

			products.on_stock,
			products.on_stockWH,
			products.volume,
			products.stock,

			products.is_new,
			products.best_selling,
			{$hasVariableField},

			section_products.sorder
				AS home_sorder,

			(
				SELECT products_img.img
				FROM products_img
				WHERE products_img.product_id =
					products.id
				ORDER BY
					products_img.sorder ASC,
					products_img.id DESC
				LIMIT 1
			) AS img,

			(
				SELECT brands.title
				FROM brands
				WHERE brands.id =
					products.brand_id
				LIMIT 1
			) AS brand_title,

			(
				SELECT categories.uri{$lang}
				FROM categories
				WHERE categories.id =
					products.category_id
				LIMIT 1
			) AS cat_uri,

			(
				SELECT categories.title{$lang}
				FROM categories
				WHERE categories.id =
					products.category_id
				LIMIT 1
			) AS cat_title
		");

		$this->db->from('products');

		$this->db->join(
			$this->productsTable
				. ' AS section_products',

			'section_products.product_id =
				products.id',

			'inner'
		);

		$this->db->where(
			'section_products.section_id',
			$sectionId
		);

		$this->db->where(
			'section_products.isShown',
			1
		);

		$this->db->where(
			'products.isShown',
			1
		);

		if (!empty($_SESSION['isb2b'])) {
			$this->db->where(
				'products.priceWH >',
				0
			);
		} else {
			$this->db->where(
				'products.price >',
				0
			);
		}

		$this->db->order_by(
			'section_products.sorder',
			'ASC'
		);

		$this->db->order_by(
			'section_products.id',
			'DESC'
		);

		$this->db->group_by('products.id');
		$this->db->limit($limit);

		return $this->db
			->get()
			->result();
	}

	/*
	|--------------------------------------------------------------------------
	| Helpers
	|--------------------------------------------------------------------------
	*/

	private function normalizeLanguage($lang)
	{
		$lang = strtoupper(
			trim((string) $lang)
		);

		$allowedLanguages = [
			'RU',
			'RO',
			'EN',
		];

		return in_array(
			$lang,
			$allowedLanguages,
			true
		)
			? $lang
			: 'RU';
	}

	private function normalizePlacement(
		$placement
	) {
		$placement = strtolower(
			trim((string) $placement)
		);

		$allowedPlacements = [
			'after_new',
			'after_sale',
		];

		return in_array(
			$placement,
			$allowedPlacements,
			true
		)
			? $placement
			: 'after_new';
	}
}