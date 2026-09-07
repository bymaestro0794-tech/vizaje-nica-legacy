<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Products extends BackEndController
{
    protected $add;
    protected $index_title;
    protected $title;
    protected $success_add;
    protected $success_update_order;
    protected $success_edit;
    protected $success_delete;

    public function __construct()
    {
        parent::__construct(__CLASS__);

        $this->index_title = lang('Products');
        $this->add = lang('Add');
        $this->success_update_order = lang('You have successfully updated display order!');
        $this->success_add = lang('You have successfully added an object');
        $this->success_edit = lang('You have successfully updated the object');
        $this->success_delete = lang('You have successfully deleted the object');

        $this->data['title'] = $this->index_title;
        $this->data['add'] = $this->add;
        $this->load->model('categories_model');
        $this->load->model('brands_model');
        $this->load->model('products_model');
    }

    public function index()
    {
        init_load_img($this->main_page);
        $search_get = $this->input->post_get('query');
        if (!empty($search_get)) {
            $objects = $this->products_model->search_get_products_admin($search_get);
            $this->data['objects'] = $objects;
            $this->data['count'] = 0;
        } else {
            $objects = $this->products_model->inCategory(isset($_GET['cat']) ? $_GET['cat'] : 0)->pagination(40, isset($_GET['page']) ? $_GET['page'] : 1, isset($_GET['xml']) ? $_GET['xml'] : 1);
            $this->data['count'] = $objects['count'];
            $this->data['objects'] = $objects['data'];
        }

        foreach ($this->data['objects'] as $datum)
        {
            $datum->img_variable = 1;

            $this->db->select('
        pv.id,
        pv.SKU,
        pv.price,
        pv.discount_price,
        pv.qty,
        img.img AS image
    ');

            $this->db->from('products_variable pv');

            // первая картинка вариации
            $this->db->join("
        (
            SELECT pvi.variable_id, pvi.img
            FROM products_variable_img pvi
            INNER JOIN (
                SELECT variable_id, MIN(sorder) AS min_sort
                FROM products_variable_img
                WHERE isShown = 1
                GROUP BY variable_id
            ) fi
            ON fi.variable_id = pvi.variable_id
            AND fi.min_sort = pvi.sorder
        ) img
    ", 'img.variable_id = pv.id', 'left', false);

            $this->db->where('pv.product_id', $datum->id);
            $this->db->order_by('pv.sorder', 'ASC');

            $query = $this->db->get();

            $datum->variables = $query->result();

            if (!empty($datum->variables)){
                foreach ($datum->variables as $variable){
                    if (empty($variable->image)){
                        $datum->img_variable = 0;
                    }
                }
            }
        }

// 
        $this->data['inner_view'] = $this->index_view;
        $this->data['categories'] = $this->categories_model->getCategories();
        $this->data['brands'] = $this->brands_model->find('title ASC');
        $this->data['for_list'] = options_categories($this->data['categories']);
        $this->data['all_categories'] = $this->categories_model->find();
        $this->data['categories_json'] = admin_categories_map($this->categories_model->find(), 0);

        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }

    public function put()
    {
        check_if_POST();

        $post = array();

        init_load_img($this->main_page);

        try {
            foreach ($_POST as $index => $item) {
                $post[$index] = $this->input->post($index);
            }

            if (!empty($post['categories'])) {
                $post['category_id'] = $post['categories'][0];
                $prod_category = $post['categories'];
                unset($post['categories']);
            }

            foreach (language(true) as $lang) {
                $post['uri' . strtoupper($lang)] = (!empty($post['title' . strtoupper($lang)])) ? transliteration($post['title' . strtoupper($lang)]) : '';
            }

            $feature_type = $this->db->select('feature_type')->where('id', $post['category_id'])->get('categories')->row();
            if (empty($feature_type)) $post['feature_type'] = 1; else $post['feature_type'] = $feature_type->feature_type;

            if (!empty($_FILES['img']['name'])) {
                $this->upload->do_upload('img');
                $file_data = $this->upload->data();
                $file = $file_data['file_name'];
                if (verify_img_extension($file_data['file_ext'])) $post['img'] = $file;
            }

            if (!$this->products_model->put($post)) {
                throw new Exception(lang('Error writing data to table') . $this->main_page);
            }

            $id = $this->db->insert_id();
            if (isset($_FILES['images'])) {
                $uploadData = [];
                $filesCount = count($_FILES['images']['name']);
                for ($i = 0; $i < $filesCount; $i++) {
                    $_FILES['file']['name'] = $_FILES['images']['name'][$i];
                    $_FILES['file']['type'] = $_FILES['images']['type'][$i];
                    $_FILES['file']['tmp_name'] = $_FILES['images']['tmp_name'][$i];
                    $_FILES['file']['error'] = $_FILES['images']['error'][$i];
                    $_FILES['file']['size'] = $_FILES['images']['size'][$i];

                    // File upload configuration
                    $uploadPath = 'public/products';
                    $config['upload_path'] = $uploadPath;
                    $config['allowed_types'] = 'jpg|jpeg|png';
                    $config['encrypt_name'] = true;

                    /// File size 2MB
                    if (isset($_FILES['file']['size'])) {
                        if ($_FILES['file']['size'] >= 2000000) {
                            throw new Exception('Ошибка загрузки файла: привышен допустимый размер!');
                        }
                    }

                    // Load and initialize upload library
                    $this->load->library('upload', $config);
                    $this->upload->initialize($config);

                    // Upload file to server
                    if ($this->upload->do_upload('file')) {
                        // Uploaded file data
                        $fileData = $this->upload->data();
                        $uploadData[$i]['product_id'] = $id;
                        $uploadData[$i]['img'] = $fileData['file_name'];
                    }
                }

                if (!empty($uploadData)) {
                    $this->db->insert_batch('products_img', $uploadData);
                }
            }

            if (!empty($prod_category)) {
                $this->db
                    ->where('product_id', $id)
                    ->delete('product_categories');

                $data = [];
                foreach ($prod_category as $catId) {
                    $data[] = [
                        'product_id' => $id,
                        'category_id' => (int)$catId
                    ];
                }

                if (!empty($data)) {
                    $this->db->insert_batch('product_categories', $data);
                }
            }

            $_SESSION['success'] = $this->success_add;
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            $errors[] = lang('Exception thrown') . $e->getMessage();
            $_SESSION['error'] = $errors;
        }

        redirect($this->path);
    }

    public function update_order()
    {
        check_if_POST();

        $this->load->model('products_model');

        try {
            $post = $this->input->post('so');

            if (empty($post) || !is_array($post)) {
                throw new Exception(lang('Error in received data!'));
            }

            if (!$this->products_model->update_sorder($post)) {
                throw new Exception(lang('Error writing data to table') . $this->main_page);
            }

            $_SESSION['success'] = $this->success_update_order;
        } catch (Exception $e) {
            $errors[] = lang('Exception thrown') . $e->getMessage();
            $_SESSION['error'] = $errors;
        }

        redirect($this->path);
    }

    public function item($id = 0)
    {
        $id = (int)$id;

        init_load_img($this->main_page);
        $item = $this->products_model->find_first($id);
        if (empty($item)) throw_on_404();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                foreach ($_POST as $index => $post_data) {
                    $post[$index] = $this->input->post($index, TRUE);
                }


                if (!empty($post['categories'])) {
                    $post['category_id'] = $post['categories'][0];
                    $prod_category = $post['categories'];
                    unset($post['categories']);
                }

                if (!empty($prod_category)) {
                    $this->db
                        ->where('product_id', $id)
                        ->delete('product_categories');

                    $data = [];
                    foreach ($prod_category as $catId) {
                        $data[] = [
                            'product_id' => $id,
                            'category_id' => (int)$catId
                        ];
                    }

                    if (!empty($data)) {
                        $this->db->insert_batch('product_categories', $data);
                    }
                }


                $post['updated_at'] = date('Y-m-d');
                if (!empty($_POST['variable'])) {
                    foreach ($_POST['variable'] as $key => $variable) {
                        if (empty($variable['isShown'])) {
                            $variable['isShown'] = 0;
                        }

                        $colorData = $this->normalizeVariantColorData(
                            isset($variable['colors'])
                                ? $variable['colors']
                                : array()
                        );

                        $data = array(
                            'SKU' => isset($variable['SKU'])
                                ? $variable['SKU']
                                : null,

                            'titleRU' => isset($variable['titleRU'])
                                ? $variable['titleRU']
                                : null,

                            'titleRO' => isset($variable['titleRO'])
                                ? $variable['titleRO']
                                : null,

                            'price' => isset($variable['price'])
                                ? $variable['price']
                                : null,

                            'colorRU' => isset($variable['colorRU'])
                                ? $variable['colorRU']
                                : null,

                            'colorRO' => isset($variable['colorRO'])
                                ? $variable['colorRO']
                                : null,

                            'VolumeVar' => isset($variable['VolumeVar'])
                                ? $variable['VolumeVar']
                                : null,

                            'discount_price' => isset($variable['discount_price'])
                                ? $variable['discount_price']
                                : null,

                            'qty' => isset($variable['qty'])
                                ? $variable['qty']
                                : 0,

                            'sorder' => isset($variable['sorder'])
                                ? $variable['sorder']
                                : 0,

                            'color_data' => $colorData,

                            'updated_at' => $post['updated_at'],

                            'isShown' => $variable['isShown']
                        );

                        $this->db->where(
                            'id',
                            (int) $key
                        );

                        $this->db->where(
                            'product_id',
                            $id
                        );

                        $this->db->update(
                            'products_variable',
                            $data
                        );
                    }
                }
                unset($post['variable']);


                if (!empty($_FILES['variable_images'])) {
                    foreach ($_FILES['variable_images']['name'] as $key => $value) {
                        if (isset($value)) {
                            $uploadData = [];
                            $filesCount = count($value);
                            for ($i = 0; $i < $filesCount; $i++) {
                                $_FILES['file']['name'] = $_FILES['variable_images']['name'][$key][$i];
                                $_FILES['file']['type'] = $_FILES['variable_images']['type'][$key][$i];
                                $_FILES['file']['tmp_name'] = $_FILES['variable_images']['tmp_name'][$key][$i];
                                $_FILES['file']['error'] = $_FILES['variable_images']['error'][$key][$i];
                                $_FILES['file']['size'] = $_FILES['variable_images']['size'][$key][$i];

                                // File upload configuration
                                $uploadPath = 'public/products_variable_img';
                                $config['upload_path'] = $uploadPath;
                                $config['allowed_types'] = 'jpg|jpeg|png';
                                $config['encrypt_name'] = true;

                                // Load and initialize upload library
                                $this->load->library('upload', $config);
                                $this->upload->initialize($config);
                                // Upload file to server

                                if ($this->upload->do_upload('file')) {
                                    // Uploaded file data
                                    $fileData = $this->upload->data();
                                    $uploadData[$i]['product_id'] = $id;
                                    $uploadData[$i]['variable_id'] = $key;
                                    $uploadData[$i]['img'] = $fileData['file_name'];
                                }
                            }
                            if (!empty($uploadData)) {
                                $this->db->insert_batch('products_variable_img', $uploadData);
                            }
                        }
                    }
                }
                if (!empty($_POST['variable_images'])) {
                    foreach ($_POST['variable_images'] as $key => $sorder) {
                        $this->db->where('id', $key);
                        $this->db->update('products_variable_img', array('sorder' => $sorder['sorder']));
                    }
                    unset($post['variable_images']);
                }

                $this->db->where('product_id', $id);
                $this->db->delete('products_related');
                if (!empty($post['related_products'])) {
                    foreach ($post['related_products'] as $related_products) {
                        $this->db->insert('products_related', array('product_id' => $id, 'related_id' => $related_products));
                    }
                    unset($post['related_products']);
                }

                $this->db->where('product_id', $id);
                $this->db->delete('products_more');
                if (!empty($post['more_products'])) {
                    foreach ($post['more_products'] as $more_products) {
                        $this->db->insert('products_more', array('product_id' => $id, 'related_id' => $more_products));
                    }
                    unset($post['more_products']);
                }

                $this->db->where('product_id', $id)->delete('products_features');
                if (!empty($post['features'])) {
                    $caract = $post['features'];
                    // if features checkboxes is not empty
                    if (!empty($caract)) {
                        // add new characteristics
                        foreach ($caract as $type => $element) {
                            foreach ($element as $feature_id => $feature_value_id) {
                                foreach ($feature_value_id as $k => $v) {
                                    $this->db->insert('products_features', [
                                        'product_id' => $id,
                                        'category_id' => $post['category_id'],
                                        'feature_id' => $type,
                                        'feature_value_id' => $v,
                                    ]);
                                }
                            }
                        }
                    }
                    unset($post['features']);
                }

                if (isset($post['image_order'])) {
                    foreach ($post['image_order'] as $key => $order) {
                        $image_orders[$key] = [
                            'id' => $key,
                            'sorder' => $order,
                        ];
                    }

                    if (!empty($image_orders)) {
                        $this->db->update_batch('products_img', $image_orders, 'id');
                    }

                    unset($post['image_order']);
                }


                if (isset($_FILES['images'])) {
                    $uploadData = [];
                    $filesCount = count($_FILES['images']['name']);
                    for ($i = 0; $i < $filesCount; $i++) {
                        $_FILES['file']['name'] = $_FILES['images']['name'][$i];
                        $_FILES['file']['type'] = $_FILES['images']['type'][$i];
                        $_FILES['file']['tmp_name'] = $_FILES['images']['tmp_name'][$i];
                        $_FILES['file']['error'] = $_FILES['images']['error'][$i];
                        $_FILES['file']['size'] = $_FILES['images']['size'][$i];

                        // File upload configuration
                        $uploadPath = 'public/products';
                        $config['upload_path'] = $uploadPath;
                        $config['allowed_types'] = 'jpg|jpeg|png';
                        $config['encrypt_name'] = true;

                        // Load and initialize upload library
                        $this->load->library('upload', $config);
                        $this->upload->initialize($config);

                        // Upload file to server
                        if ($this->upload->do_upload('file')) {
                            // Uploaded file data
                            $fileData = $this->upload->data();
                            $uploadData[$i]['product_id'] = $id;
                            $uploadData[$i]['img'] = $fileData['file_name'];
                        }
                    }

                    if (!empty($uploadData)) {
                        $this->db->insert_batch('products_img', $uploadData);
                    }
                }

                if (!$this->products_model->update($post, $id)) {
                    throw new Exception(lang('Error writing data to table') . $this->main_page);
                }

                $_SESSION['success'] = $this->success_edit;
            } catch
            (Exception $e) {
                log_message('error', $e->getMessage());
                $errors[] = lang('Exception thrown') . $e->getMessage();
                $_SESSION['error'] = $errors;
            }

            $item = $this->products_model->find_first($id);
        }

        $item->images = $this->db->where('product_id', $id)->order_by('sorder ASC, id DESC')->get('products_img')->result();
        $item->variable = $this->db->where('product_id', $id)->order_by('sorder ASC, id DESC')->get('products_variable')->result();
        foreach ($item->variable as $value) {
            $value->img = $this->db->where('variable_id', $value->id)->order_by('sorder ASC, id DESC')->get('products_variable_img')->result();
        }



        $this->db->select('category_id');
        $this->db->from('product_categories');
        $this->db->where('product_id', $item->id);

        $query = $this->db->get();
        $result = $query->result_array();

        $this->data['itemCategories'] = array_column($result, 'category_id');

        $this->data['categories'] = $this->categories_model->getCategories();
        $this->data['for_list'] = options_categories($this->data['categories']);

        $this->data['category'] = $this->categories_model->find_first($item->category_id);
        $this->data['brands'] = $this->brands_model->find();
        $caracteristics_list = array();
        if (!empty($item->feature_type)) {
            // get selected product caracteristics
            $selected = $this->db->where('product_id', $id)->get('products_features')->result();
            $this->data['selected_features'] = $selected;

            $caracteristics_list = array();
            $caracteristics = $this->products_model->get_prod_characteristics($item->feature_type);

            if (!empty($caracteristics)) {
                foreach ($caracteristics as $el) {
                    $arr = array();
                    foreach ($caracteristics as $values) {
                        if ($values->value_feature_id == $el->feature_id) {
                            $arr[] = [
                                'value_id' => $values->item_id,
                                'value_name' => $values->item_name,
                                'color' => $values->color,
                            ];
                        }
                    }
                    $caracteristics_list[$el->feature_id] = [
                        'feature_id' => $el->feature_id,
                        'feature_name' => $el->feature_name,
                        'feature_sorder' => $el->feature_sorder,
                        'feature_type' => $el->feature_type,
                        'feature_free_values' => $el->feature_free_values,
                        'feature_values' => $arr
                    ];
                }
            }
        }
        $this->data['caracteristics_list'] = $caracteristics_list;

        $products_related = array();
        $products_related_result = $this->db->where('product_id', $id)->get('products_related')->result();
        if (!empty($products_related_result)) {
            foreach ($products_related_result as $products_related_row) {
                $products_related[$products_related_row->related_id] = $products_related_row->related_id;
            }
        }

        $more_products = array();
        $more_products_result = $this->db->where('product_id', $id)->get('products_more')->result();
        if (!empty($more_products_result)) {
            foreach ($more_products_result as $more_products_row) {
                $more_products[$more_products_row->related_id] = $more_products_row->related_id;
            }
        }

        $products = $this->db->select('id,title' . get_language_for_admin(true) . '')->where('category_id', $item->category_id)->limit(1000)->order_by("title" . get_language_for_admin(true) . " ASC")->get('products')->result();
        $this->data['products'] = $products;

        $this->data['products_related'] = $products_related;
        $this->data['more_products'] = $more_products;


        $this->data['inner_view'] = $this->item_view;
        $this->data['title'] = lang('Edit') . $item->{'title' . get_language_for_admin(true)};
        $this->data['parent_url'] = $this->path;
        $this->data['parent_title'] = $this->index_title;
        $this->data['item'] = $item;

        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }

    public function delete($id = false)
    {
        $id = (int)$id;

        $item = $this->products_model->find_first($id);
        if (empty($item)) throw_on_404();

        try {
            if (!$this->products_model->delete($id)) {
                throw new Exception(lang('Error deleting data from table') . $this->main_page);
            }

            $_SESSION['success'] = $this->success_delete;
        } catch (Exception $e) {
            $errors[] = lang('Exception thrown') . $e->getMessage();
            $_SESSION['error'] = $errors;
        }

        redirect($this->path);
    }
    private function normalizeVariantColorData($colors)
        {
            if (!is_array($colors)) {
                return null;
            }

            $normalizedColors = array();

            foreach ($colors as $color) {
                $color = strtoupper(
                    trim(
                        (string) $color
                    )
                );

                if ($color === '') {
                    continue;
                }

                /*
                * Разрешаем только полный HEX:
                * #AABBCC
                */
                if (!preg_match(
                    '/^#[0-9A-F]{6}$/',
                    $color
                )) {
                    continue;
                }

                if (!in_array(
                    $color,
                    $normalizedColors,
                    true
                )) {
                    $normalizedColors[] = $color;
                }

                /*
                * Для swatch больше шести цветов
                * нам практически не нужно.
                */
                if (count($normalizedColors) >= 6) {
                    break;
                }
            }

            if (empty($normalizedColors)) {
                return null;
            }

            return json_encode(
                array(
                    'colors' => $normalizedColors
                ),
                JSON_UNESCAPED_SLASHES
            );
        }
    public function add_variable()
    {
        check_if_POST();

        $productId = isset($_POST['id'])
            ? (int) $_POST['id']
            : 0;

        if ($productId <= 0) {
            show_error(
                'Invalid product ID',
                400
            );

            return;
        }

        $data = array(
            'product_id' => $productId,
            'color_data' => null,
        );

        $this->db->insert(
            'products_variable',
            $data
        );

        $id = (int) $this->db->insert_id();

        echo '
            <tr id="variable' . $id . '">

                <td width="50">
                    Сортировка

                    <input
                        type="text"
                        class="form-control"
                        value=""
                        name="variable[' . $id . '][sorder]"
                    >
                </td>

                <td width="200">
                    Название RU

                    <input
                        type="text"
                        class="form-control"
                        value=""
                        name="variable[' . $id . '][titleRU]"
                    >
                </td>

                <td width="200">
                    Название RO

                    <input
                        type="text"
                        class="form-control"
                        value=""
                        name="variable[' . $id . '][titleRO]"
                    >
                </td>

                <td>
                    SKU

                    <input
                        type="text"
                        class="form-control"
                        value=""
                        name="variable[' . $id . '][SKU]"
                    >
                </td>

                <td width="200">
                    Цвет RU

                    <input
                        type="text"
                        class="form-control"
                        value=""
                        name="variable[' . $id . '][colorRU]"
                    >
                </td>

                <td width="200">
                    Цвет RO

                    <input
                        type="text"
                        class="form-control"
                        value=""
                        name="variable[' . $id . '][colorRO]"
                    >
                </td>

                <td
                    width="260"
                    class="variant-color-cell"
                    data-variant-color
                    data-variant-id="' . $id . '"
                >
                    <strong>
                        Визуальный цвет
                    </strong>

                    <select
                        class="form-control variant-color-mode"
                        data-variant-color-mode
                        style="margin-top: 6px;"
                    >
                        <option
                            value="none"
                            selected
                        >
                            Без цвета
                        </option>

                        <option value="single">
                            Один цвет
                        </option>

                        <option value="multi">
                            Несколько цветов
                        </option>
                    </select>

                    <div
                        class="variant-color-list"
                        data-variant-color-list
                        style="margin-top: 8px;"
                    ></div>

                    <button
                        type="button"
                        class="btn btn-xs default variant-color-add"
                        data-variant-color-add
                        style="margin-top: 8px;"
                    >
                        <i class="fa fa-plus"></i>
                        Добавить цвет
                    </button>

                    <small
                        class="help-block"
                        style="margin-bottom: 0;"
                    >
                        Максимум 6 цветов
                    </small>
                </td>

                <td>
                    Объём

                    <input
                        type="text"
                        class="form-control"
                        value=""
                        name="variable[' . $id . '][VolumeVar]"
                    >
                </td>

                <td>
                    Цена

                    <input
                        type="text"
                        class="form-control"
                        value=""
                        name="variable[' . $id . '][price]"
                    >
                </td>

                <td>
                    Цена со скидкой

                    <input
                        type="text"
                        class="form-control"
                        value=""
                        name="variable[' . $id . '][discount_price]"
                    >
                </td>

                <td>
                    На складе

                    <input
                        type="text"
                        class="form-control"
                        value=""
                        name="variable[' . $id . '][qty]"
                    >
                </td>

                <td>
                    Активен

                    <input
                        type="checkbox"
                        class="form-control"
                        style="
                            width: 25px;
                            height: 25px;
                        "
                        value="1"
                        name="variable[' . $id . '][isShown]"
                    >
                </td>

                <td width="250">
                    Фото

                    <input
                        type="file"
                        name="variable_images[' . $id . '][]"
                        class="form-control"
                        multiple
                    >
                </td>

                <td width="100">
                    <a
                        onclick="DeleteVariable(' . $id . ')"
                        class="
                            btn
                            btn-xs
                            default
                            btn-editable
                            red-stripe
                        "
                        style="margin-top: 15px;"
                    >
                        <i class="glyphicon glyphicon-remove-circle"></i>
                        Удалить
                    </a>
                </td>

            </tr>
        ';
    }

    public function add_variable_delete()
    {
        check_if_POST();
        $this->db->where('id', $_POST['id']);
        $this->db->delete('products_variable');
    }
}
