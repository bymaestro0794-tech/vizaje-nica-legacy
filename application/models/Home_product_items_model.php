<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home_product_items_model extends CI_Model
{
    protected $table = 'home_product_items';

    public function get_admin_items($sectionKey)
    {
        $sectionKey = $this->normalizeSectionKey($sectionKey);

        return $this->db
            ->select("
                home_items.id,
                home_items.section_key,
                home_items.product_id,
                home_items.sorder,
                home_items.isShown,

                products.SKU,
                products.titleRU,
                products.titleRO,
                products.price,
                products.discount_price,
                products.stock,

                (
                    SELECT products_img.img
                    FROM products_img
                    WHERE products_img.product_id = products.id
                    ORDER BY products_img.sorder ASC,
                             products_img.id DESC
                    LIMIT 1
                ) AS img,

                (
                    SELECT brands.title
                    FROM brands
                    WHERE brands.id = products.brand_id
                    LIMIT 1
                ) AS brand_title
            ")
            ->from($this->table . ' AS home_items')
            ->join(
                'products',
                'products.id = home_items.product_id',
                'left'
            )
            ->where('home_items.section_key', $sectionKey)
            ->order_by('home_items.sorder', 'ASC')
            ->order_by('home_items.id', 'DESC')
            ->get()
            ->result();
    }

public function addProduct($sectionKey, $productId)
{
	$sectionKey = $this->normalizeSectionKey($sectionKey);
	$productId = (int) $productId;

	if ($productId <= 0) {
		return [
			'success' => false,
			'message' => 'Некорректный ID товара.',
		];
	}

	$productExists = $this->db
		->where('id', $productId)
		->count_all_results('products');

	if ($productExists === 0) {
		return [
			'success' => false,
			'message' => 'Товар не найден.',
		];
	}

	$alreadyAdded = $this->db
		->where('section_key', $sectionKey)
		->where('product_id', $productId)
		->count_all_results($this->table);

	if ($alreadyAdded > 0) {
		return [
			'success' => false,
			'message' => 'Этот товар уже добавлен в выбранную секцию.',
		];
	}

	$maxOrderRow = $this->db
		->select_max('sorder')
		->where('section_key', $sectionKey)
		->get($this->table)
		->row();

	$nextOrder = !empty($maxOrderRow->sorder)
		? (int) $maxOrderRow->sorder + 1
		: 1;

	$inserted = $this->db->insert(
		$this->table,
		[
			'section_key' => $sectionKey,
			'product_id' => $productId,
			'sorder' => $nextOrder,
			'isShown' => 1,
		]
	);

	return [
		'success' => (bool) $inserted,
		'message' => $inserted
			? 'Товар добавлен в подборку.'
			: 'Не удалось добавить товар.',
	];
}

    public function updateOrder($sectionKey, array $orders)
    {
        $sectionKey = $this->normalizeSectionKey($sectionKey);

        $this->db->trans_start();

        foreach ($orders as $id => $sorder) {
            $id = (int) $id;

            if ($id <= 0) {
                continue;
            }

            $this->db
                ->where('id', $id)
                ->where('section_key', $sectionKey)
                ->update(
                    $this->table,
                    [
                        'sorder' => max(0, (int) $sorder),
                    ]
                );
        }

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    public function setVisibility(
        $sectionKey,
        $id,
        $isShown
    ) {
        $sectionKey = $this->normalizeSectionKey($sectionKey);

        return $this->db
            ->where('id', (int) $id)
            ->where('section_key', $sectionKey)
            ->update(
                $this->table,
                [
                    'isShown' => $isShown ? 1 : 0,
                ]
            );
    }

    public function deleteItem($sectionKey, $id)
    {
        $sectionKey = $this->normalizeSectionKey($sectionKey);

        return $this->db
            ->where('id', (int) $id)
            ->where('section_key', $sectionKey)
            ->delete($this->table);
    }

    private function normalizeSectionKey($sectionKey)
    {
        $sectionKey = strtolower(
            trim((string) $sectionKey)
        );

        $allowedSections = [
            'hits',
            'new',
            'sale',
            'recommended',
        ];

        return in_array(
            $sectionKey,
            $allowedSections,
            true
        )
            ? $sectionKey
            : 'hits';
    }

    public function get_products_home_section(
	$lang,
	$sectionKey = 'hits',
	$limit = 12
) {
	if (empty($lang)) {
		return [];
	}

	$allowedLanguages = ['RU', 'RO', 'EN'];

	$lang = strtoupper(trim((string) $lang));

	if (!in_array($lang, $allowedLanguages, true)) {
		$lang = 'RU';
	}

	$sectionKey = strtolower(trim((string) $sectionKey));
	$limit = max(1, min((int) $limit, 30));

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

    home_items.sorder AS home_sorder,

		(
			SELECT products_img.img
			FROM products_img
			WHERE products_img.product_id = products.id
			ORDER BY
				products_img.sorder ASC,
				products_img.id DESC
			LIMIT 1
		) AS img,

		(
			SELECT brands.title
			FROM brands
			WHERE brands.id = products.brand_id
			LIMIT 1
		) AS brand_title,

		(
			SELECT categories.uri{$lang}
			FROM categories
			WHERE categories.id = products.category_id
			LIMIT 1
		) AS cat_uri,

		(
			SELECT categories.title{$lang}
			FROM categories
			WHERE categories.id = products.category_id
			LIMIT 1
		) AS cat_title
	");

	$this->db->from('products');

	$this->db->join(
		'home_product_items AS home_items',
		'home_items.product_id = products.id',
		'inner'
	);

	$this->db->where(
		'home_items.section_key',
		$sectionKey
	);

	$this->db->where(
		'home_items.isShown',
		1
	);

	$this->db->where(
		'products.isShown',
		1
	);

	if (!empty($_SESSION['isb2b'])) {
		$this->db->where('products.priceWH >', 0);
	} else {
		$this->db->where('products.price >', 0);
	}

	$this->db->order_by(
		'home_items.sorder',
		'ASC'
	);

	$this->db->order_by(
		'home_items.id',
		'DESC'
	);

	$this->db->group_by('products.id');
	$this->db->limit($limit);

	return $this->db
		->get()
		->result();
}
}