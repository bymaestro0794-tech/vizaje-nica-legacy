<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Offers extends BackEndController {
    protected $add;
    protected $index_title;
    protected $title;
    protected $success_add;
    protected $success_update_order;
    protected $success_edit;
    protected $success_delete;

    public function __construct() {
        parent::__construct(__CLASS__);

        $this->index_title = lang('Offers');
        $this->add = lang('Add');
        $this->success_update_order = lang('You have successfully updated display order!');
        $this->success_add = lang('You have successfully added an object');
        $this->success_edit = lang('You have successfully updated the object');
        $this->success_delete = lang('You have successfully deleted the object');

        $this->data['title'] = $this->index_title;
        $this->data['add'] = $this->add;
        $this->load->model('offers_model');
        $this->load->model('products_model');
    }

    public function index() {
        init_load_img($this->main_page);

        $objects = $this->offers_model->find();

        // Подарочная акция может быть привязана к странице скидки. Не делаем
        // join обязательным: старые установки без gift_promotions должны
        // продолжать открывать обычный список скидок.
        $giftOfferCounts = array();
        if ($this->db->table_exists('gift_promotions')) {
            $giftRows = $this->db
                ->select('offer_id, COUNT(*) AS gift_promotion_count')
                ->where('offer_id IS NOT NULL', null, false)
                ->group_by('offer_id')
                ->get('gift_promotions')
                ->result();

            foreach ($giftRows as $giftRow) {
                $giftOfferCounts[(int) $giftRow->offer_id] = (int) $giftRow->gift_promotion_count;
            }
        }

        foreach ((array) $objects as $object) {
            $object->gift_promotion_count = $giftOfferCounts[(int) $object->id] ?? 0;
        }

        $this->data['inner_view'] = $this->index_view;
        $this->data['objects'] = $objects;

        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }

    public function put() {
        check_if_POST();

        $post = array();

        init_load_img($this->main_page);

        try {
            foreach ($_POST as $index => $item) {
                $post[$index] = $this->input->post($index, TRUE);
            }

            if (!isset($post['is_online']) || !in_array((string) $post['is_online'], array('0', '1', '2'), true)) {
                throw new Exception('Выберите формат акции: онлайн, офлайн или оба канала.');
            }
            $post['is_online'] = (int) $post['is_online'];
            $progressMode = $this->normalizeProgressMode($post['progress_mode'] ?? 'time');
            if ($progressMode === null) {
                unset($post['progress_mode']);
            } else {
                $post['progress_mode'] = $progressMode;
            }

            foreach(language(true) as $lang){
                $post['uri'.strtoupper($lang)] = (!empty($post['title'.strtoupper($lang)])) ? transliteration($post['title'.strtoupper($lang)]) : '';
            }

            if (!empty($_FILES['imgRU']['name'])) {
                $this->upload->do_upload('imgRU');
                $file_data = $this->upload->data();
                $file = $file_data['file_name'];
                if(verify_img_extension($file_data['file_ext']))  $post['imgRU'] = $file;
            }
            if (!empty($_FILES['imgBRU']['name'])) {
                $this->upload->do_upload('imgBRU');
                $file_data = $this->upload->data();
                $file = $file_data['file_name'];
                if(verify_img_extension($file_data['file_ext']))  $post['imgBRU'] = $file;
            }
            if (!empty($_FILES['imgRO']['name'])) {
                $this->upload->do_upload('imgRO');
                $file_data = $this->upload->data();
                $file = $file_data['file_name'];
                if(verify_img_extension($file_data['file_ext']))  $post['imgRO'] = $file;
            }
            if (!empty($_FILES['imgBRO']['name'])) {
                $this->upload->do_upload('imgBRO');
                $file_data = $this->upload->data();
                $file = $file_data['file_name'];
                if(verify_img_extension($file_data['file_ext']))  $post['imgBRO'] = $file;
            }

            if (!($id = $this->offers_model->put($post))) {
                throw new Exception(lang('Error writing data to table') . $this->main_page);
            }

            $_SESSION['success'] = $this->success_add;
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            $errors[] = lang('Exception thrown') . $e->getMessage();
            $_SESSION['error'] = $errors;
        }

        redirect($this->path);
    }

    public function update_order() {
        check_if_POST();

        $this->load->model('offers_model');

        try {
            $post = $this->input->post('so');

            if (empty($post) || !is_array($post)) {
                throw new Exception(lang('Error in received data!'));
            }

            if (!$this->offers_model->update_sorder($post)) {
                throw new Exception(lang('Error writing data to table') . $this->main_page);
            }

            $_SESSION['success'] = $this->success_update_order;
        } catch (Exception $e) {
            $errors[] = lang('Exception thrown') . $e->getMessage();
            $_SESSION['error'] = $errors;
        }

        redirect($this->path);
    }

    public function item($id = 0) {
        $id = (int)$id;

        init_load_img($this->main_page);
        $item = $this->offers_model->find_first($id);
        if (empty($item)) throw_on_404();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                if (!empty($_POST['sorder'])){
                    foreach ($_POST['sorder'] as $key => $value){
                        $update_sort[] = array(
                            'id' => $key,
                            'sorder' => $value,
                        );
                    }
                    $this->db->update_batch('offers_products', $update_sort, 'id');
                }

                unset($_POST['add_products_alt']);
                unset($_POST['sorder']);

                foreach ($_POST as $index => $post_data) {
                    $post[$index] = $this->input->post($index, TRUE);
                }

                if (!isset($post['is_online']) || !in_array((string) $post['is_online'], array('0', '1', '2'), true)) {
                    throw new Exception('Выберите формат акции: онлайн, офлайн или оба канала.');
                }
                $post['is_online'] = (int) $post['is_online'];
                $progressMode = $this->normalizeProgressMode($post['progress_mode'] ?? 'time');
                if ($progressMode === null) {
                    unset($post['progress_mode']);
                } else {
                    $post['progress_mode'] = $progressMode;
                }


                if (!empty($_FILES['imgRU']['name'])) {
                    unlink_files($this->main_page, $item->imgRU);
                    $this->upload->do_upload('imgRU');
                    $file_data = $this->upload->data();
                    $file = $file_data['file_name'];
                    if(verify_img_extension($file_data['file_ext']))  $post['imgRU'] = $file;
                }
                if (!empty($_FILES['imgBRU']['name'])) {
                    unlink_files($this->main_page, $item->imgBRU);
                    $this->upload->do_upload('imgBRU');
                    $file_data = $this->upload->data();
                    $file = $file_data['file_name'];
                    if(verify_img_extension($file_data['file_ext']))  $post['imgBRU'] = $file;
                }
                if (!empty($_FILES['imgRO']['name'])) {
                    unlink_files($this->main_page, $item->imgRO);
                    $this->upload->do_upload('imgRO');
                    $file_data = $this->upload->data();
                    $file = $file_data['file_name'];
                    if(verify_img_extension($file_data['file_ext']))  $post['imgRO'] = $file;
                }
                if (!empty($_FILES['imgBRO']['name'])) {
                    unlink_files($this->main_page, $item->imgBRO);
                    $this->upload->do_upload('imgBRO');
                    $file_data = $this->upload->data();
                    $file = $file_data['file_name'];
                    if(verify_img_extension($file_data['file_ext']))  $post['imgBRO'] = $file;
                }

                if (!$this->offers_model->update($post, $id)) {
                    throw new Exception(lang('Error writing data to table') . $this->main_page);
                }

                $_SESSION['success'] = $this->success_edit;
            } catch (Exception $e) {
                log_message('error', $e->getMessage());
                $errors[] = lang('Exception thrown') . $e->getMessage();
                $_SESSION['error'] = $errors;
            }

            $item = $this->offers_model->find_first($id);
        }

        $products_alt = $this->offers_model->gat_products_offers($id);
        $this->data['products_alt'] = $products_alt;
        $this->data['offer_brands'] = $this->db
            ->select('b.id, b.title, COUNT(op.id) AS product_count')
            ->from('offers_products op')
            ->join('products p', 'p.id = op.product_id', 'inner')
            ->join('brands b', 'b.id = p.brand_id', 'inner')
            ->where('op.offers_id', $id)
            ->group_by(array('b.id', 'b.title'))
            ->order_by('b.title', 'ASC')
            ->get()
            ->result();
        $this->data['product_brands'] = $this->db
            ->select('id, title')
            ->order_by('title', 'ASC')
            ->get('brands')
            ->result();
        $this->data['product_categories'] = $this->db
            ->select('id, titleRU, titleRO')
            ->order_by('titleRU', 'ASC')
            ->get('categories')
            ->result();

        $this->data['inner_view'] = $this->item_view;
        $this->data['title'] = lang('Edit') . $item->{'title'.get_language_for_admin(true)};
        $this->data['parent_url'] = $this->path;
        $this->data['parent_title'] = $this->index_title;
        $this->data['item'] = $item;

        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }

    private function normalizeProgressMode($value)
    {
        $value = (string) $value;
        if (!in_array($value, array('time', 'stock', 'none'), true)) {
            $value = 'time';
        }

        if (!$this->db->field_exists('progress_mode', 'offers')) {
            return null;
        }

        return $value;
    }

    public function delete($id = false) {
        $id = (int)$id;

        $item = $this->offers_model->find_first($id);
        if (empty($item)) throw_on_404();

        unlink_files($this->main_page, $item->img);

        try {
            if (!$this->offers_model->delete($id)) {
                throw new Exception(lang('Error deleting data from table') . $this->main_page);
            }

            $_SESSION['success'] = $this->success_delete;
        } catch (Exception $e) {
            $errors[] = lang('Exception thrown') . $e->getMessage();
            $_SESSION['error'] = $errors;
        }

        redirect($this->path);
    }

    public function products_alt()
    {
        check_if_POST();
        $offerId = (int) $this->input->post('id', true);
        if ($offerId < 1 || empty($this->offers_model->find_first($offerId))) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(404)
                ->set_output(json_encode(array('message' => 'Акция не найдена.'), JSON_UNESCAPED_UNICODE));
        }
        $product = $this->products_model->product_alt($this->input->post('product', true));
        if (empty($product)) throw_on_404();
        $product_seasonal = $this->db->where('offers_id', $offerId)->where('product_id', $product->id)->get('offers_products')->row();
        if (empty($product_seasonal)) {
            $data = array(
                'product_id' => $product->id,
                'offers_id' => $offerId,
                'title' => $product->title,
                'SKU' => $product->SKU,
                'sorder' => 1,
            );
            $this->db->insert('offers_products', $data);
            $offerProductId = (int) $this->db->insert_id();
            echo '
<table class="table table-bordered table-striped table-hover" id="prodalt' . $offerProductId . '" data-offer-product-row data-product-id="' . (int) $product->id . '" data-brand-id="' . (int) $product->brand_id . '">
    <tbody>
    <tr>
        <td width="50">
            <input type="text" name="sorder[' . $offerProductId . ']" value="0" class="form-control">
        </td>
        <td width="150">
            SKU: ' . htmlspecialchars((string) $product->SKU, ENT_QUOTES, 'UTF-8') . '
        </td>
        <td width="">
            ' . htmlspecialchars((string) $product->title, ENT_QUOTES, 'UTF-8') . '
        </td>
        <td width="100">
            <a onclick="DeleteProdAlt( ' . $offerProductId . ' )"   class="btn btn-xs default btn-editable red-stripe">
                <i class="glyphicon glyphicon-remove-circle"></i> Удалить
            </a>
        </td>
    </tr>
    </tbody>
</table>';
        }
    }

    public function prodalt_delete()
    {
        check_if_POST();

        $offerId = (int) $this->input->post('offer_id', true);
        $offerProductId = (int) $this->input->post('row_id', true);
        if ($offerId < 1 || $offerProductId < 1) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(422)
                ->set_output(json_encode(array('message' => 'Не указан товар акции.'), JSON_UNESCAPED_UNICODE));
        }

        $this->db
            ->where('id', $offerProductId)
            ->where('offers_id', $offerId)
            ->delete('offers_products');

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('status' => 'ok', 'deleted' => (int) $this->db->affected_rows()), JSON_UNESCAPED_UNICODE));
    }

    public function brand_products_delete()
    {
        check_if_POST();

        $offerId = (int) $this->input->post('offer_id', true);
        $brandId = (int) $this->input->post('brand_id', true);
        if ($offerId < 1 || $brandId < 1) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(422)
                ->set_output(json_encode(array('message' => 'Не указан бренд акции.'), JSON_UNESCAPED_UNICODE));
        }

        $productRows = $this->db
            ->select('p.id')
            ->from('offers_products op')
            ->join('products p', 'p.id = op.product_id', 'inner')
            ->where('op.offers_id', $offerId)
            ->where('p.brand_id', $brandId)
            ->get()
            ->result_array();
        $productIds = array_map('intval', array_column($productRows, 'id'));

        if (!empty($productIds)) {
            $this->db
                ->where('offers_id', $offerId)
                ->where_in('product_id', $productIds)
                ->delete('offers_products');
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status' => 'ok',
                'deleted' => (int) $this->db->affected_rows(),
                'product_ids' => $productIds,
            ), JSON_UNESCAPED_UNICODE));
    }

    public function product_lookup()
    {
        $offerId = (int) $this->input->get_post('offer_id', true);
        $query = trim((string) $this->input->get_post('q', true));
        $brandId = (int) $this->input->get_post('brand_id', true);
        $categoryId = (int) $this->input->get_post('category_id', true);

        if ($offerId < 1) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('items' => array()), JSON_UNESCAPED_UNICODE));
        }

        $attachedRows = $this->db
            ->select('product_id')
            ->where('offers_id', $offerId)
            ->get('offers_products')
            ->result_array();
        $attached = array();
        foreach ($attachedRows as $row) {
            $attached[(int) $row['product_id']] = true;
        }

        $this->db
            ->select('p.id, p.SKU, p.brand_id, p.titleRU, p.titleRO, b.title AS brand_title')
            ->from('products p')
            ->join('brands b', 'b.id = p.brand_id', 'left')
            ->where('p.isShown', 1);

        if ($brandId > 0) {
            $this->db->where('p.brand_id', $brandId);
        }
        if ($categoryId > 0) {
            $this->db->where('p.category_id', $categoryId);
        }
        if ($query !== '') {
            $this->db->group_start()
                ->like('p.titleRU', $query)
                ->or_like('p.titleRO', $query)
                ->or_like('p.SKU', $query)
                ->or_where('p.id', ctype_digit($query) ? (int) $query : 0)
                ->group_end();
        }

        $items = $this->db
            ->order_by('p.titleRU', 'ASC')
            ->limit(80)
            ->get()
            ->result();

        $response = array();
        foreach ($items as $item) {
            $response[] = array(
                'id' => (int) $item->id,
                'sku' => (string) $item->SKU,
                'title' => (string) ($item->titleRU ?: $item->titleRO),
                'brand' => (string) ($item->brand_title ?? ''),
                'brand_id' => (int) ($item->brand_id ?? 0),
                'attached' => !empty($attached[(int) $item->id]),
            );
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('items' => $response), JSON_UNESCAPED_UNICODE));
    }

    public function products_bulk_add()
    {
        check_if_POST();

        $offerId = (int) $this->input->post('offer_id', true);
        $productIds = $this->input->post('product_ids');
        $productIds = is_array($productIds) ? array_values(array_unique(array_filter(array_map('intval', $productIds)))) : array();

        if ($offerId < 1 || empty($productIds)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(422)
                ->set_output(json_encode(array('message' => 'Выберите товары.'), JSON_UNESCAPED_UNICODE));
        }

        $products = $this->db
            ->select('id, SKU, brand_id, titleRU, titleRO')
            ->where_in('id', $productIds)
            ->where('isShown', 1)
            ->get('products')
            ->result();

        $maxOrderRow = $this->db
            ->select_max('sorder')
            ->where('offers_id', $offerId)
            ->get('offers_products')
            ->row();
        $nextOrder = !empty($maxOrderRow->sorder) ? (int) $maxOrderRow->sorder + 1 : 1;
        $added = array();

        foreach ($products as $product) {
            $exists = $this->db
                ->where('offers_id', $offerId)
                ->where('product_id', (int) $product->id)
                ->count_all_results('offers_products');
            if ($exists > 0) {
                continue;
            }

            $this->db->insert('offers_products', array(
                'offers_id' => $offerId,
                'product_id' => (int) $product->id,
                'title' => (string) ($product->titleRU ?: $product->titleRO),
                'SKU' => (string) $product->SKU,
                'sorder' => $nextOrder++,
            ));

            $added[] = array(
                'id' => (int) $this->db->insert_id(),
                'product_id' => (int) $product->id,
                'brand_id' => (int) $product->brand_id,
                'sku' => (string) $product->SKU,
                'title' => (string) ($product->titleRU ?: $product->titleRO),
                'sorder' => $nextOrder - 1,
            );
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('items' => $added), JSON_UNESCAPED_UNICODE));
    }

    public function products_bulk_add_all_except_brands()
    {
        check_if_POST();

        $offerId = (int) $this->input->post('offer_id', true);
        $excludedBrandIds = $this->input->post('excluded_brand_ids');
        $excludedBrandIds = is_array($excludedBrandIds)
            ? array_values(array_filter(array_unique(array_map('intval', $excludedBrandIds))))
            : array();

        if ($offerId < 1 || empty($this->offers_model->find_first($offerId))) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(404)
                ->set_output(json_encode(array('message' => 'Акция не найдена.'), JSON_UNESCAPED_UNICODE));
        }

        $this->db
            ->select('id, SKU, titleRU, titleRO, brand_id')
            ->where('isShown', 1);
        if ($this->db->field_exists('isGift', 'products')) {
            $this->db->where('isGift', 0);
        }
        if (!empty($excludedBrandIds)) {
            $this->db->where_not_in('brand_id', $excludedBrandIds);
        }
        $products = $this->db
            ->order_by('id', 'ASC')
            ->get('products')
            ->result();

        $attachedRows = $this->db
            ->select('product_id')
            ->where('offers_id', $offerId)
            ->get('offers_products')
            ->result_array();
        $attached = array();
        foreach ($attachedRows as $row) {
            $attached[(int) $row['product_id']] = true;
        }

        $maxOrderRow = $this->db
            ->select_max('sorder')
            ->where('offers_id', $offerId)
            ->get('offers_products')
            ->row();
        $nextOrder = !empty($maxOrderRow->sorder) ? (int) $maxOrderRow->sorder + 1 : 1;
        $rowsToInsert = array();

        foreach ($products as $product) {
            $productId = (int) $product->id;
            if (isset($attached[$productId])) {
                continue;
            }

            $rowsToInsert[] = array(
                'offers_id' => $offerId,
                'product_id' => $productId,
                'title' => (string) ($product->titleRU ?: $product->titleRO),
                'SKU' => (string) $product->SKU,
                'sorder' => $nextOrder++,
            );
        }

        foreach (array_chunk($rowsToInsert, 500) as $chunk) {
            $this->db->insert_batch('offers_products', $chunk);
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status' => 'ok',
                'added_count' => count($rowsToInsert),
                'excluded_brand_ids' => $excludedBrandIds,
            ), JSON_UNESCAPED_UNICODE));
    }
}
