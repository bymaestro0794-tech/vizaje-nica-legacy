<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Beauty_selections extends BackEndController
{
    private $model;

    public function __construct()
    {
        parent::__construct(__CLASS__);
        $this->load->model('beauty_selections_model');
        $this->model =& $this->beauty_selections_model;
        $this->load->model('products_model');
        $this->data['title'] = 'Бьюти-подборки';
        $this->data['add'] = 'Добавить подборку';
    }

    public function index()
    {
        $this->data['objects'] = $this->model->find('sorder ASC, id DESC');
        $this->data['inner_view'] = $this->index_view;
        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }

    public function put()
    {
        check_if_POST();
        init_load_img($this->main_page, true, 'jpg|jpeg|gif|png|webp|avif');

        try {
            $post = $this->read_post();
            $post['created_at'] = date('Y-m-d H:i:s');
            $post['updated_at'] = date('Y-m-d H:i:s');
            $post = $this->upload_images($post, array());

            if (!$this->model->put($post)) {
                throw new Exception('Не удалось создать подборку.');
            }

            $id = $this->db->insert_id();
            $this->model->replace_products($id, $this->input->post('product_ids'));
            $_SESSION['success'] = 'Подборка создана.';
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            $_SESSION['error'] = array($e->getMessage());
        }

        redirect($this->path);
    }

    public function item($id = 0)
    {
        $id = (int) $id;
        $item = $this->model->find_first($id);
        if (empty($item)) {
            throw_on_404();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            init_load_img($this->main_page, true, 'jpg|jpeg|gif|png|webp|avif');
            try {
                $post = $this->read_post();
                $post['updated_at'] = date('Y-m-d H:i:s');
                $post = $this->upload_images($post, $item);

                if (!$this->model->update($post, $id)) {
                    throw new Exception('Не удалось обновить подборку.');
                }
                $this->model->replace_products($id, $this->input->post('product_ids'));
                $_SESSION['success'] = 'Подборка обновлена.';
                $item = $this->model->find_first($id);
            } catch (Exception $e) {
                log_message('error', $e->getMessage());
                $_SESSION['error'] = array($e->getMessage());
            }
        }

        $productIds = array_column($this->model->get_product_ids($id), 'product_id');
        $products = empty($productIds)
            ? array()
            : $this->db->select('id, SKU, titleRU, titleRO')->where_in('id', $productIds)->get('products')->result();

        $this->data['item'] = $item;
        $this->data['products'] = $products;
        $this->data['product_ids'] = $productIds;
        $this->data['inner_view'] = $this->item_view;
        $this->data['title'] = 'Редактировать: ' . ($item->titleRU ?: $item->titleRO);
        $this->data['parent_url'] = $this->path;
        $this->data['parent_title'] = 'Бьюти-подборки';
        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }

    public function update_order()
    {
        check_if_POST();
        $this->model->update_sorder($this->input->post('so'));
        $_SESSION['success'] = 'Порядок обновлён.';
        redirect($this->path);
    }

    public function delete($id = 0)
    {
        $id = (int) $id;
        $item = $this->model->find_first($id);
        if (empty($item)) {
            throw_on_404();
        }

        $this->model->delete($id);
        $_SESSION['success'] = 'Подборка удалена.';
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
                ->group_end()
                ->where('isShown', 1)
                ->order_by('stock DESC, id DESC');
            $products = $this->db->limit(20)->get('products')->result_array();
        }

        $this->output->set_content_type('application/json')->set_output(json_encode($products));
    }

    private function read_post()
    {
        $post = array('isShown' => $this->input->post('isShown') ? 1 : 0);
        foreach (array('titleRU', 'titleRO', 'descRU', 'descRO', 'textRU', 'textRO', 'seoTitleRU', 'seoTitleRO', 'seoKeywordsRU', 'seoKeywordsRO', 'seoDescRU', 'seoDescRO') as $field) {
            $post[$field] = $this->input->post($field, true);
        }
        foreach (array('RU', 'RO') as $lang) {
            $title = trim((string) $post['title' . $lang]);
            $post['uri' . $lang] = transliteration($title);
            if ($post['uri' . $lang] === '') {
                throw new Exception('Заполните название на обоих языках.');
            }
        }
        $post['sorder'] = (int) $this->input->post('sorder');
        return $post;
    }

    private function upload_images($post, $item)
    {
        foreach (array('RU', 'RO') as $lang) {
            $field = 'img' . $lang;
            if (!empty($_FILES[$field]['name'])) {
                $this->upload->do_upload($field);
                $file = $this->upload->data();
                if (verify_img_extension($file['file_ext'])) {
                    $post[$field] = $file['file_name'];
                    if (!empty($item->{$field})) {
                        unlink_files($this->main_page, $item->{$field});
                    }
                }
            }
        }
        return $post;
    }
}
