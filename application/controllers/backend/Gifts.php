<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Gifts extends BackEndController
{
    private $model;

    public function __construct()
    {
        parent::__construct(__CLASS__);
        $this->load->model('gifts_model');
        $this->model =& $this->gifts_model;
        $this->data['title'] = 'Подарки';
        $this->data['add'] = 'Добавить подарок';
    }

    public function index()
    {
        $this->data['migration_required'] = !$this->db->table_exists('gifts')
            || !$this->db->field_exists('show_on_site', 'gifts');
        $this->data['objects'] = $this->data['migration_required'] ? array() : $this->model->get_admin();
        $this->data['inner_view'] = 'dashboard/gifts/index';
        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }

    public function put()
    {
        check_if_POST();
        try {
            $this->ensure_table();
            $post = $this->read_post();
            $now = date('Y-m-d H:i:s');
            $post['created_at'] = $now;
            $post['updated_at'] = $now;
            if (!$this->model->put($post)) {
                throw new Exception('Не удалось создать подарок.');
            }
            $_SESSION['success'] = 'Подарок создан.';
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            $_SESSION['error'] = array($e->getMessage());
        }
        redirect($this->path);
    }

    public function item($id = 0)
    {
        $this->ensure_table();
        $id = (int) $id;
        $item = $this->model->find_first_admin($id);
        if (empty($item)) {
            throw_on_404();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $post = $this->read_post();
                $post['updated_at'] = date('Y-m-d H:i:s');
                if (!$this->model->update($post, $id)) {
                    throw new Exception('Не удалось обновить подарок.');
                }
                $_SESSION['success'] = 'Подарок обновлён.';
                $item = $this->model->find_first_admin($id);
            } catch (Exception $e) {
                log_message('error', $e->getMessage());
                $_SESSION['error'] = array($e->getMessage());
            }
        }

        $variants = empty($item->product_id) ? array() : $this->model->get_product_variants($item->product_id);
        $this->data['item'] = $item;
        $this->data['variants'] = $variants;
        $this->data['inner_view'] = 'dashboard/gifts/item';
        $this->data['title'] = 'Редактировать: ' . ($item->titleRU ?: $item->titleRO);
        $this->data['parent_url'] = $this->path;
        $this->data['parent_title'] = 'Подарки';
        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }

    public function delete($id = 0)
    {
        $this->ensure_table();
        $id = (int) $id;
        if (empty($this->model->find_first_admin($id))) {
            throw_on_404();
        }
        $this->model->delete($id);
        $_SESSION['success'] = 'Подарок удалён.';
        redirect($this->path);
    }

    public function update_order()
    {
        check_if_POST();
        $this->ensure_table();
        $this->model->update_sorder($this->input->post('so'));
        $_SESSION['success'] = 'Порядок обновлён.';
        redirect($this->path);
    }

    public function product_lookup()
    {
        $query = trim((string) $this->input->get('q', true));
        $products = array();
        if (mb_strlen($query, 'UTF-8') >= 2) {
            $this->db->select('id, SKU, titleRU, titleRO')
                ->group_start()
                ->like('titleRU', $query)
                ->or_like('titleRO', $query)
                ->or_like('SKU', $query)
                ->or_like('id', $query)
                ->group_end()
                ->group_start()
                    ->where('isShown', 1);
            if ($this->db->field_exists('isGift', 'products')) {
                $this->db->or_where('isGift', 1);
            }
            $products = $this->db
                ->group_end()
                ->order_by('stock DESC, id DESC')
                ->limit(20)
                ->get('products')
                ->result_array();
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($products));
    }

    public function variant_lookup($productId = 0)
    {
        $this->ensure_table();
        $variants = $this->model->get_product_variants((int) $productId);
        $this->output->set_content_type('application/json')->set_output(json_encode($variants));
    }

    private function ensure_table()
    {
        if (!$this->db->table_exists('gifts')) {
            throw new Exception('Сначала примените SQL-патч database/patches/2026-10-23-create-gifts.sql.');
        }
        if (!$this->db->field_exists('show_on_site', 'gifts')) {
            throw new Exception('Сначала примените SQL-патч database/patches/2026-10-23-gift-visibility.sql.');
        }
    }

    private function read_post()
    {
        $titleRU = trim((string) $this->input->post('titleRU', true));
        $titleRO = trim((string) $this->input->post('titleRO', true));
        if ($titleRU === '' || $titleRO === '') {
            throw new Exception('Заполните название подарка на русском и румынском языках.');
        }

        $productId = (int) $this->input->post('product_id');
        $this->db->where('id', $productId)->group_start()->where('isShown', 1);
        if ($this->db->field_exists('isGift', 'products')) {
            $this->db->or_where('isGift', 1);
        }
        $productExists = $this->db->group_end()->count_all_results('products');
        if ($productId < 1 || !$productExists) {
            throw new Exception('Выберите опубликованный или отмеченный как подарок товар.');
        }

        $variantId = (int) $this->input->post('variant_id');
        if ($variantId > 0 && !$this->db->where('id', $variantId)->where('product_id', $productId)->where('isShown', 1)->count_all_results('products_variable')) {
            throw new Exception('Выбранный вариант товара не принадлежит этому товару.');
        }

        return array(
            'titleRU' => $titleRU,
            'titleRO' => $titleRO,
            'descriptionRU' => $this->input->post('descriptionRU', true),
            'descriptionRO' => $this->input->post('descriptionRO', true),
            'product_id' => $productId,
            'variant_id' => $variantId > 0 ? $variantId : null,
            'is_enabled' => $this->input->post('is_enabled') ? 1 : 0,
            'show_on_site' => $this->input->post('show_on_site') ? 1 : 0,
            'sorder' => (int) $this->input->post('sorder'),
        );
    }
}
