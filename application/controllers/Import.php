<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Import extends CI_Controller
{
    private $productImportTrackingEnabled = false;

    public function __construct()
    {
        parent::__construct();

    }

    private function productImportSnapshot($product, array $categoryIds, array $categoryNames, array $brandNames)
    {
        $categories = array();
        foreach ($categoryIds as $categoryId) {
            $categoryId = (int) $categoryId;
            if ($categoryId > 0) {
                $categories[] = isset($categoryNames[$categoryId])
                    ? $categoryNames[$categoryId]
                    : 'Категория #' . $categoryId;
            }
        }
        $categories = array_values(array_unique($categories));
        sort($categories, SORT_NATURAL | SORT_FLAG_CASE);

        $brandId = isset($product->brand_id) ? (int) $product->brand_id : 0;
        $brandTitle = isset($product->brand_title)
            ? (string) $product->brand_title
            : (isset($brandNames[$brandId]) ? $brandNames[$brandId] : '');

        return array(
            'titleRU' => isset($product->titleRU) ? trim((string) $product->titleRU) : '',
            'titleRO' => isset($product->titleRO) ? trim((string) $product->titleRO) : '',
            'SKU' => isset($product->SKU) ? trim((string) $product->SKU) : '',
            'categories' => $categories,
            'brand' => trim($brandTitle),
            'volume' => isset($product->volume) ? trim((string) $product->volume) : '',
            'barcode' => isset($product->barcode) ? trim((string) $product->barcode) : '',
        );
    }

    private function productImportDiff(array $before, array $after)
    {
        $labels = array(
            'titleRU' => 'Название RU',
            'titleRO' => 'Название RO',
            'SKU' => 'Артикул',
            'categories' => 'Категории',
            'brand' => 'Бренд',
            'volume' => 'Объём',
            'barcode' => 'Штрихкод',
        );
        $changes = array();

        foreach ($labels as $field => $label) {
            $oldValue = isset($before[$field]) ? $before[$field] : '';
            $newValue = isset($after[$field]) ? $after[$field] : '';

            if (is_array($oldValue) || is_array($newValue)) {
                $oldValue = array_values((array) $oldValue);
                $newValue = array_values((array) $newValue);
                if ($oldValue === $newValue) {
                    continue;
                }
            } else {
                $oldValue = trim((string) $oldValue);
                $newValue = trim((string) $newValue);
                if ($oldValue === $newValue) {
                    continue;
                }
            }

            $changes[$field] = array(
                'label' => $label,
                'before' => $oldValue,
                'after' => $newValue,
            );
        }

        return $changes;
    }

    private function saveProductImportChange($productId, $guid, $action, array $changes)
    {
        if (!$this->productImportTrackingEnabled) {
            return;
        }

        if ($action === 'updated' && empty($changes)) {
            return;
        }

        $this->db->insert('product_import_changes', array(
            'product_id' => (int) $productId,
            'product_guid' => (string) $guid,
            'action' => $action,
            'changed_fields' => json_encode($changes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_at' => date('Y-m-d H:i:s'),
        ));
    }

    private function addSeoFieldsFromFeed(array $data, $value)
    {
        $seoMap = [
            'SeoHeaderRU' => 'seoTitleRU',
            'SeoHeaderRO' => 'seoTitleRO',
            'SeoDescriptionRU' => 'seoDescRU',
            'SeoDescriptionRO' => 'seoDescRO',
        ];

        foreach ($seoMap as $sourceField => $targetField) {
            if (!isset($value->$sourceField)) {
                continue;
            }

            $fieldValue = trim((string)$value->$sourceField);
            if ($fieldValue === '') {
                continue;
            }

            $data[$targetField] = $fieldValue;
        }

        return $data;
    }

    public function import()
    {
        $this->productImportTrackingEnabled = $this->db->table_exists('product_import_changes');
        if (!$this->productImportTrackingEnabled) {
            log_message('error', 'Product import change tracking is disabled: apply the product_import_changes SQL patch.');
        }

        $file = realpath('public') . '/import/products.json';
        if (file_exists($file)) {
            $array = json_decode(file_get_contents($file));
        }

        $part = (int)($_GET['page'] ?? 1); // 1–5
        $parts = 1;

        $total = count($array);
        $limit = ceil($total / $parts);
        $offset = ($part - 1) * $limit;

        $currentChunk = array_slice($array, $offset, $limit);

        dump([
            'part' => $part,
            'from' => $offset,
            'to' => $offset + count($currentChunk),
        ], true);

        $categories = [];
        $categoryNames = [];

        // RO
        $query = $this->db->select('id, titleRO')
            ->where('titleRO IS NOT NULL', null, false)
            ->get('categories')
            ->result();

        foreach ($query as $row) {
            $categories[trim($row->titleRO)] = $row->id;
            $categoryNames[(int) $row->id] = trim((string) $row->titleRO);
        }

        // RU
        $query = $this->db->select('id, titleRU')
            ->where('titleRU IS NOT NULL', null, false)
            ->get('categories')
            ->result();

        foreach ($query as $row) {
            $categories[trim($row->titleRU)] = $row->id;
            if (empty($categoryNames[(int) $row->id])) {
                $categoryNames[(int) $row->id] = trim((string) $row->titleRU);
            }
        }

        $brands = [];
        $brandNames = [];

        $query = $this->db->select('id, title')->get('brands')->result();
        foreach ($query as $row) {
            $brands[trim($row->title)] = $row->id;
            $brandNames[(int) $row->id] = trim((string) $row->title);
        }

        $products = [];

        $query = $this->db
            ->select('id, GUID, SKU, volume, barcode, category_id, brand_id, titleRU, titleRO')
            ->get('products')
            ->result();
        foreach ($query as $row) {
            $products[$row->GUID] = $row;
        }

        $productCategories = [];
        $query = $this->db
            ->select('product_id, category_id')
            ->get('product_categories')
            ->result();
        foreach ($query as $row) {
            $productCategories[(int) $row->product_id][] = (int) $row->category_id;
        }

		$replacedPendingNewProductChanges = false;

        foreach ($currentChunk as $value) {


            if (empty($value->ShortDescriptionRus)) {
                continue;
            }


            // категория 1
            $category = $categories[$value->NomenclatureCategory1] ?? 0;

            // категория 2
            $category2 = 0;
            if (!empty($value->NomenclatureCategory2)) {
                $category2 = $categories[$value->NomenclatureCategory2] ?? 0;
            }

            // бренд
            $brandTitle = trim($value->Brand);
            if (!isset($brands[$brandTitle])) {
                $this->db->insert('brands', [
                    'title' => $brandTitle,
                    'uri' => transliteration($brandTitle),
                ]);
                $brands[$brandTitle] = $this->db->insert_id(); // кешируем
                $brandNames[(int) $brands[$brandTitle]] = $brandTitle;
            }
            $brandId = $brands[$brandTitle];
            $incomingCategoryIds = array_values(array_unique(array_filter(array(
                (int) $category,
                (int) $category2,
            ))));
            $incomingSku = !empty($value->VariativeNomenclature)
                ? $value->VariativeNomenclature
                : $value->ID_nom;
            $sourceProduct = (object) array(
                'titleRU' => $value->ShortDescriptionRus,
                'titleRO' => $value->ShortDescriptionMD,
                'SKU' => $incomingSku,
                'brand_id' => $brandId,
                'brand_title' => $brandTitle,
                'volume' => $value->VolumeVar,
                'barcode' => $value->Barcode,
            );
            $sourceSnapshot = $this->productImportSnapshot(
                $sourceProduct,
                $incomingCategoryIds,
                $categoryNames,
                $brandNames
            );


            if (!isset($products[$value->ID_nom])) {

                $VariativeNomenclature = !empty($value->VariativeNomenclature) ? $value->VariativeNomenclature : '';
                $value->SKU = $incomingSku;

                $titleRU = $value->ShortDescriptionRus;
                $titleRO = $value->ShortDescriptionMD;
                $titleEN = $value->ShortDescriptionMD;
                $data = array(
                    'GUID' => $value->ID_nom,
                    'SKU' => $value->SKU,
                    'volume' => $value->VolumeVar,
                    'price' => $value->Price,
                    'priceWH' => $value->PriceWH,
                    'barcode' => $value->Barcode,
                    'category_id' => $category,
                    'brand_id' => $brandId,
                    'variations' => $VariativeNomenclature,
                    'on_stock' => intval($value->QuantityBalance) + intval($value->BalMall),
                    'on_stockWH' => $value->QuantityBalance,
                    'titleRU' => $titleRU,
                    'titleRO' => $titleRO,
                    'titleEN' => $titleEN,
                    'WarehouseName' => $value->WarehouseName,
                    'sex' => $value->Sex,
                    'textRU' => $value->FullDescriptionRus,
                    'textRO' => $value->FullDescriptionMD,
                    'textEN' => $value->FullDescriptionMD,
                    'instructionRU' => $value->InstructionRU,
                    'instructionRO' => $value->InstructionMD,
                    'componentsRU' => $value->componentsRu,
                    'componentsRO' => $value->componentsMD,
                    'uriRU' => transliteration($value->ID_nom),
                    'uriRO' => transliteration($value->ID_nom),
                    'uriEN' => transliteration($value->ID_nom),
                    'isShown' => 1,
                );
                $data = $this->addSeoFieldsFromFeed($data, $value);
                $this->db->insert('products', $data);
                $id_prod =$this->db->insert_id();
                $data_cat = array();

                if (!empty($category)) {
                    $data_cat[] = [
                        'product_id' => $id_prod,
                        'category_id' => (int)$category
                    ];
                }
                if (!empty($category2)) {
                    $data_cat[] = [
                        'product_id' => $id_prod,
                        'category_id' => (int)$category2
                    ];
                }


                if (!empty($data_cat)) {
                    $this->db->insert_batch('product_categories', $data_cat);
                }

				if (
					!$replacedPendingNewProductChanges
					&& $this->productImportTrackingEnabled
					&& $this->db->field_exists('status', 'product_import_changes')
				) {
					// Keep only the latest import's unreviewed new-product entries.
					// This removes journal rows only; product records remain untouched.
					$this->db
						->where('action', 'added')
						->where('status', 'new')
						->delete('product_import_changes');
					$replacedPendingNewProductChanges = true;
				}

                $this->saveProductImportChange(
                    $id_prod,
                    $value->ID_nom,
                    'added',
                    $this->productImportDiff(array(), $sourceSnapshot)
                );
                $products[$value->ID_nom] = (object) array_merge($data, array('id' => $id_prod));
                $productCategories[(int) $id_prod] = $incomingCategoryIds;

            } else {

                $existingProduct = $products[$value->ID_nom];
                $productId = (int) $existingProduct->id;
                $existingCategoryIds = isset($productCategories[$productId])
                    ? $productCategories[$productId]
                    : array();
                if (empty($existingCategoryIds) && !empty($existingProduct->category_id)) {
                    $existingCategoryIds[] = (int) $existingProduct->category_id;
                }
                $beforeSnapshot = $this->productImportSnapshot(
                    $existingProduct,
                    $existingCategoryIds,
                    $categoryNames,
                    $brandNames
                );

                $titleRU = $value->ShortDescriptionRus;
                $titleRO = $value->ShortDescriptionMD;
                $titleEN = $value->ShortDescriptionMD;
                $data = array(
                    'price' => $value->Price,
                    'priceWH' => $value->PriceWH,
                    'volume' => $value->VolumeVar,
                    'barcode' => $value->Barcode,
                    'on_stock' => intval($value->QuantityBalance) + intval($value->BalMall),
                    'on_stockWH' => $value->QuantityBalance,
                    'titleRU' => $titleRU,
                    'titleRO' => $titleRO,
                    'titleEN' => $titleEN,
                    'sex' => $value->Sex,
                    'WarehouseName' => $value->WarehouseName,
                    'textRU' => $value->FullDescriptionRus,
                    'textRO' => $value->FullDescriptionMD,
                    'textEN' => $value->FullDescriptionMD,
                    'instructionRU' => $value->InstructionRU,
                    'instructionRO' => $value->InstructionMD,
                    'componentsRU' => $value->componentsRu,
                    'componentsRO' => $value->componentsMD,
                );
                $data = $this->addSeoFieldsFromFeed($data, $value);
                $this->db->where('id', $productId);
                $this->db->update('products', $data);


                $data_cat = array();

                if (!empty($category)) {
                    $data_cat[] = [
                        'product_id' => $productId,
                        'category_id' => (int)$category
                    ];
                }
                if (!empty($category2) && $category != $category2) {
                    $data_cat[] = [
                        'product_id' => $productId,
                        'category_id' => (int)$category2
                    ];
                }

                if (!empty($data_cat)) {

                    $this->db
                        ->where('product_id', $productId)
                        ->delete('product_categories');

                    $this->db->insert_batch('product_categories', $data_cat);
                    $productCategories[$productId] = $incomingCategoryIds;
                } else {
                    $sourceSnapshot['categories'] = $beforeSnapshot['categories'];
                }

                $this->saveProductImportChange(
                    $productId,
                    $value->ID_nom,
                    'updated',
                    $this->productImportDiff($beforeSnapshot, $sourceSnapshot)
                );
                $existingProduct->titleRU = $titleRU;
                $existingProduct->titleRO = $titleRO;
                $existingProduct->volume = $value->VolumeVar;
                $existingProduct->barcode = $value->Barcode;
            }

        }

        echo 'success';
    }

    public function update_seo()
    {
        $file = realpath('public') . '/import/JobVN.json';
        if (file_exists($file)) {
            $array = json_decode(file_get_contents($file));
        }
//
//        $products = $this->db->select('GUID, id')->get('products')->rwsult();

        $data = array();;
        foreach ($array as $value) {
            dump($value->SeoHeaderRO . '<br>', true);
            if (!empty($value->ID_nom) && !empty($value->SeoHeaderRO)) {
                $data[] = array(
                    'GUID' => $value->ID_nom,
                    'seoTitleRU' => $value->SeoHeaderRU,
                    'seoTitleRO' => $value->SeoHeaderRO,
                    'seoKeywordsRU' => $value->SeoKeyWordsRU,
                    'seoKeywordsRO' => $value->SeoKeyWordsRO,
                    'seoDescRU' => $value->SeoDescriptionRU,
                    'seoDescRO' => $value->SeoDescriptionRO,
                );
            }
        }
        dump($data);
    }

    public function import_variable()
    {

        $file = realpath('public') . '/import/products.json';
        if (file_exists($file)) {
            $array = json_decode(file_get_contents($file));
        }

        foreach ($array as $value) {
            if (!empty($value->VariativeNomenclature)) {
                $this->db->select('id')->where('SKU', $value->ID_nom);
                $products_variable = $this->db->get('products_variable')->row();

                if (empty($products_variable)) {
                    $this->db->select('id')->where('SKU', $value->VariativeNomenclature);
                    $product = $this->db->get('products')->row();
                    if (!empty($product)) $product = $product->id; else {
                        $product = 0;
                    }
                    $titleRU = $value->VariativeName;
                    $titleRO = $value->VariativeNameMD;
                    $titleEN = $value->VariativeNameMD;
                    $data = array(
                        'SKU' => $value->ID_nom,
                        'priceWH' => $value->PriceWH,
                        'qty' => intval($value->QuantityBalance) + intval($value->BalMall),
                        'color' => $value->ColorVarMD,
                        'titleRU' => $titleRU,
                        'titleRO' => $titleRO,
                        'titleEN' => $titleEN,
                        'WarehouseName' => $value->WarehouseName,
                        'colorRU' => $value->ColorVar,
                        'colorRO' => $value->ColorVarMD,
                        'colorEN' => $value->ColorVarMD,
                        'VolumeVar' => $value->VolumeVar,
                        'isShown' => 1,
                        'product_id' => $product,
                        'qtyWH' => $value->QuantityBalance,
                        'barcode' => $value->Barcode
                    );
                    $this->db->insert('products_variable', $data);
                } else {
                    $this->db->select('id')->where('SKU', $value->VariativeNomenclature);
                    $product = $this->db->get('products')->row();
                    if (!empty($product)) $product = $product->id; else {
                        $product = 1;
                    }
                    $titleRU = $value->VariativeName;
                    $titleRO = $value->VariativeNameMD;
                    $titleEN = $value->VariativeNameMD;
                    $data = array(
                        'SKU' => $value->ID_nom,
                        'price' => $value->Price,
                        'priceWH' => $value->PriceWH,
                        'qty' => intval($value->QuantityBalance) + intval($value->BalMall),
                        'color' => $value->ColorVarMD,
                        'titleRU' => $titleRU,
                        'titleRO' => $titleRO,
                        'titleEN' => $titleEN,
                        'WarehouseName' => $value->WarehouseName,
                        'colorRU' => $value->ColorVar,
                        'colorRO' => $value->ColorVarMD,
                        'colorEN' => $value->ColorVarMD,
                        'VolumeVar' => $value->VolumeVar,
                        'isShown' => 1,
                        'product_id' => $product,
                        'qtyWH' => $value->QuantityBalance,
                        'barcode' => $value->Barcode
                    );
                    $this->db->where('id', $products_variable->id);
                    $this->db->update('products_variable', $data);
                }
            }
        }

        echo 'success';
    }

    public function import_categories_off()
    {
        $file = realpath('') . '/products.json';
        if (file_exists($file)) {
            $array = json_decode(file_get_contents($file));
        }
        $categories = array();
        foreach ($array as $value) {
            if (!empty($value->ShortDescriptionRus)) {
                $categories[$value->NomenclatureCategory1Parent2 . $value->NomenclatureCategory1Parent . $value->NomenclatureCategory1] = array(
                    'NomenclatureCategory1' => $value->NomenclatureCategory1,
                    'NomenclatureCategory1Parent1' => $value->NomenclatureCategory1Parent,
                    'NomenclatureCategory1Parent2' => $value->NomenclatureCategory1Parent2,
                );
            }
        }

        foreach ($categories as $value) {
            if (!empty($value['NomenclatureCategory1Parent2'])) {
                $this->db->select('id')->where('titleRO', $value['NomenclatureCategory1Parent2']);
                $category = $this->db->get('categories')->row();
                if (empty($category)) {
                    $data = array(
                        'titleRU' => $value['NomenclatureCategory1Parent2'],
                        'titleRO' => $value['NomenclatureCategory1Parent2'],
                        'titleEN' => $value['NomenclatureCategory1Parent2'],
                        'uriRU' => transliteration($value['NomenclatureCategory1Parent2']),
                        'uriRO' => transliteration($value['NomenclatureCategory1Parent2']),
                        'uriEN' => transliteration($value['NomenclatureCategory1Parent2']),
                        'parent_id' => 0,
                        'step' => 1,
                    );
                    $this->db->insert('categories', $data);
                    $category = $this->db->insert_id();
                } else {
                    $category = $category->id;
                }
            }
            if (!empty($value['NomenclatureCategory1Parent1'])) {
                $this->db->select('id')->where('titleRO', $value['NomenclatureCategory1Parent1']);
                $category2 = $this->db->get('categories')->row();
                if (empty($category2)) {
                    $step = 2;
                    if (empty($category)) {
                        $category = 0;
                        $step = 1;
                    }
                    $data = array(
                        'titleRU' => $value['NomenclatureCategory1Parent1'],
                        'titleRO' => $value['NomenclatureCategory1Parent1'],
                        'titleEN' => $value['NomenclatureCategory1Parent1'],
                        'uriRU' => transliteration($value['NomenclatureCategory1Parent1']),
                        'uriRO' => transliteration($value['NomenclatureCategory1Parent1']),
                        'uriEN' => transliteration($value['NomenclatureCategory1Parent1']),
                        'parent_id' => $category,
                        'step' => $step,
                    );
                    $this->db->insert('categories', $data);
                    $category2 = $this->db->insert_id();
                } else {
                    $category2 = $category2->id;
                }
            }
            if (!empty($value['NomenclatureCategory1Parent1'])) {
                $this->db->select('id')->where('titleRO', $value['NomenclatureCategory1']);
                $category3 = $this->db->get('categories')->row();
                if (empty($category3)) {
                    $step = 3;
                    if (empty($category)) {
                        $step = 2;
                    }
                    if (empty($category2)) {
                        $category2 = 0;
                        $step = 1;
                    }
                    $data = array(
                        'titleRU' => $value['NomenclatureCategory1'],
                        'titleRO' => $value['NomenclatureCategory1'],
                        'titleEN' => $value['NomenclatureCategory1'],
                        'uriRU' => transliteration($value['NomenclatureCategory1']),
                        'uriRO' => transliteration($value['NomenclatureCategory1']),
                        'uriEN' => transliteration($value['NomenclatureCategory1']),
                        'parent_id' => $category2,
                        'step' => $step,
                    );
                    $this->db->insert('categories', $data);
                }
            }
        }

        echo 'success';
    }

    public function brands_product_off()
    {
        $brands = $this->db->select('id')->get('brands')->result();
        foreach ($brands as $brand) {
            $count = $this->db->where('brand_id', $brand->id)->where('price >', 0)->where('isShown', 1)->count_all_results('products');
            $this->db->where('id', $brand->id);
            $this->db->update('brands', array('GUID' => $count));
        }
        echo 'success';
    }

    public function update_stock()
    {
        $products = $this->db->select('id,on_stock')->get('products')->result();
        $upd_array = array();
        foreach ($products as $product) {

            if (!empty($product->on_stock)) {
                $upd_array[] = array(
                    'id' => $product->id,
                    'stock' => 1,
                );
            } else {
                $products_variable = $this->db->select('id,qty,qtyWH')->where('product_id', $product->id)->get('products_variable')->result();
                if (!empty($products_variable)) {
                    foreach ($products_variable as $value) {
                        if (!empty($value->qty)) {
                            $stock = 1;
                            break;
                        } else {
                            $stock = 0;
                        }

                        if (!empty($value->qtyWH)) {
                            $stock_wh = 1;
                            break;
                        } else {
                            $stock_wh = 0;
                        }
                    }
                    $upd_array[] = array(
                        'id' => $product->id,
                        'stock' => $stock,
                        'on_stock' => $stock,
                        'on_stockWH' => $stock_wh
                    );
                } else {
                    $upd_array[] = array(
                        'id' => $product->id,
                        'stock' => 0,
                        'on_stock' => 0,
                        'on_stockWH' => 0
                    );
                }
            }
        }

        $this->db->update_batch('products', $upd_array, 'id');
    }

    public function update_url()
    {
        $products = $this->db->select('id,uriRO,uriRU,uriEN')->get('products')->result();
        $array_url = array();
        foreach ($products as $product) {
            $array_url[$product->uriRO][] = $product;
        }
        foreach ($array_url as $value) {
            if (count($value) > 1) {
                foreach ($value as $key => $item) {
                    if ($key != 0) {
                        $this->db->where('id', $item->id)->update('products', array('uriRO' => $item->uriRO . '-' . $item->id, 'uriRU' => $item->uriRU . '-' . $item->id, 'uriEN' => $item->uriEN . '-' . $item->id));
                    }
                }
            }
        }

        $products = $this->db->select('id,uriRO,uriRU,uriEN')->get('categories')->result();
        $array_url = array();
        foreach ($products as $product) {
            $array_url[$product->uriRO][] = $product;
        }
        foreach ($array_url as $value) {
            if (count($value) > 1) {
                foreach ($value as $key => $item) {
                    if ($key != 0) {
                        $this->db->where('id', $item->id)->update('categories', array('uriRO' => $item->uriRO . '-' . $item->id, 'uriRU' => $item->uriRU . '-' . $item->id, 'uriEN' => $item->uriEN . '-' . $item->id));
                    }
                }
            }
        }
    }

    public function update_qty()
    {
        $this->db->select('id,on_stock,on_stockWH')->where('isShown', 1);
        if ($this->db->field_exists('isGift', 'products')) {
            $this->db->or_where('isGift', 1);
        }
        $products = $this->db->get('products')->result();

        foreach ($products as $product) {
            $products_variable = $this->db->select('qty,qtyWH')->where('isShown', 1)->where('product_id', $product->id)->get('products_variable')->result();
            if (!empty($products_variable)) {
                foreach ($products_variable as $value) {
                    $product->on_stock = $product->on_stock + $value->qty;
                    $product->on_stockWH = $product->on_stockWH + $value->qtyWH;
                }
            }
            $this->db->where('id', $product->id)->update('products', array('on_stock' => $product->on_stock, 'on_stockWH' => $product->on_stockWH));
        }
    }

    public function update_price()
    {
        $file = realpath('public') . '/import/pricevn.json';
        if (file_exists($file)) {
            $array = json_decode(file_get_contents($file));
        }


        if (!empty($array)) {
            foreach ($array as $value) {
                if (!empty($value->ID_nom)) {
                    $this->db->select('id')->where('GUID', $value->ID_nom);
                    $product = $this->db->get('products')->row();
                    if (!empty($product)) {
                        $data = array(
                            'price' => $value->Price,
                            'discount_price' => $value->DiscountPrice,
                        );
                        $this->db->where('id', $product->id);
                        $this->db->update('products', $data);
                    }

                    $this->db->select('id')->where('SKU', $value->ID_nom);
                    $products_variable = $this->db->get('products_variable')->row();
                    if (!empty($products_variable)) {
                        $data = array(
                            'price' => $value->Price,
                            'discount_price' => $value->DiscountPrice,
                        );
                        $this->db->where('id', $products_variable->id);
                        $this->db->update('products_variable', $data);
                    }
                }
            }
            echo 'success';
        }
    }

    public function update_sale_mdl()
    {
        $products = $this->db->select('id, 	price, discount_price, sale_mdl')->get('products')->result();
        if (!empty($products)) {
            foreach ($products as $product) {
                $sale_mdl = 0;
                $sale_percent = 0;
                if (!empty($product->discount_price)) {
                    $sale_mdl = $product->price - $product->discount_price;

                    // скидка в процентах
                    $sale_percent = round(
                        (($product->price - $product->discount_price) / $product->price) * 100
                    );
                }
                $data = array(
                    'sale_mdl' => $sale_mdl,
                    'sale_percent' => $sale_percent,
                );
                $this->db->where('id', $product->id);
                $this->db->update('products', $data);
            }
            echo 'success';
        }
    }

    public function update_search_tag()
    {
        $products = $this->db->select('id, search_teg')->where('isShown', 1)->get('products')->result();
        if (!empty($products)) {
            foreach ($products as $product) {
                $search_teg = '';

                $products_variable = $this->db->select('id, SKU, color')->where('product_id', $product->id)->get('products_variable')->result();
                if (!empty($products_variable)) {
                    foreach ($products_variable as $item) {
                        $search_teg .= ' ' . $item->SKU . ' ' . $item->color;
                    }
                }


                $data = array(
                    'search_teg' => $search_teg,
                );
                $this->db->where('id', $product->id);
                $this->db->update('products', $data);
            }
            echo 'success';
        }
    }

}
