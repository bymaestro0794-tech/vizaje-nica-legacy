<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Articles extends BackEndController
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

        $this->index_title = lang('Articles');
        $this->add = lang('Add');
        $this->success_update_order = lang('You have successfully updated display order!');
        $this->success_add = lang('You have successfully added an object');
        $this->success_edit = lang('You have successfully updated the object');
        $this->success_delete = lang('You have successfully deleted the object');

        $this->data['title'] = $this->index_title;
        $this->data['add'] = $this->add;
        $this->load->model('articles_model');
    }

    public function index()
    {
        init_load_img($this->main_page);

        $blogFilter = $this->input->get('blog');
        $filters = array();
        if ($blogFilter === '1' || $blogFilter === '0') {
            $filters['isBlog'] = (int) $blogFilter;
        }
        $objects = $this->articles_model->find('date DESC, id DESC', null, $filters);

        $this->data['inner_view'] = $this->index_view;
        $this->data['objects'] = $objects;
        $this->data['blog_filter'] = $blogFilter;

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
                $post[$index] = $this->input->post($index, TRUE);
            }
            $post['isBlog'] = $this->input->post('isBlog') ? 1 : 0;

            foreach (language(true) as $lang) {
                $post['uri' . strtoupper($lang)] = (!empty($post['title' . strtoupper($lang)])) ? transliteration($post['title' . strtoupper($lang)]) : '';
            }

            if (!empty($_FILES['img']['name'])) {
                $this->upload->do_upload('img');
                $file_data = $this->upload->data();
                $file = $file_data['file_name'];
                if (verify_img_extension($file_data['file_ext'])) $post['img'] = $file;
            }

            if (!$this->articles_model->put($post)) {
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

    public function update_order()
    {
        check_if_POST();

        $this->load->model('articles_model');

        try {
            $post = $this->input->post('so');

            if (empty($post) || !is_array($post)) {
                throw new Exception(lang('Error in received data!'));
            }

            if (!$this->articles_model->update_sorder($post)) {
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
        $item = $this->articles_model->find_first($id);
        if (empty($item)) throw_on_404();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                foreach ($_POST as $index => $post_data) {
                    $post[$index] = $this->input->post($index, TRUE);
                }
                $post['isBlog'] = $this->input->post('isBlog') ? 1 : 0;

                foreach (language(true) as $lang) {
                    $post['uri' . strtoupper($lang)] = (!empty($post['title' . strtoupper($lang)])) ? transliteration($post['title' . strtoupper($lang)]) : '';
                }

                if (!empty($_FILES['img']['name'])) {
                    unlink_files($this->main_page, $item->img);
                    $this->upload->do_upload('img');
                    $file_data = $this->upload->data();
                    $file = $file_data['file_name'];
                    if (verify_img_extension($file_data['file_ext'])) $post['img'] = $file;
                }

                if (!$this->articles_model->update($post, $id)) {
                    throw new Exception(lang('Error writing data to table') . $this->main_page);
                }

                $_SESSION['success'] = $this->success_edit;
            } catch (Exception $e) {
                log_message('error', $e->getMessage());
                $errors[] = lang('Exception thrown') . $e->getMessage();
                $_SESSION['error'] = $errors;
            }

            $item = $this->articles_model->find_first($id);
        }

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

        $item = $this->articles_model->find_first($id);
        if (empty($item)) throw_on_404();

        unlink_files($this->main_page, $item->img);

        try {
            if (!$this->articles_model->delete($id)) {
                throw new Exception(lang('Error deleting data from table') . $this->main_page);
            }

            $_SESSION['success'] = $this->success_delete;
        } catch (Exception $e) {
            $errors[] = lang('Exception thrown') . $e->getMessage();
            $_SESSION['error'] = $errors;
        }

        redirect($this->path);
    }

    public function product_lookup()
    {
        $query = trim((string) $this->input->get('q', TRUE));
        $products = array();

        if (mb_strlen($query, 'UTF-8') >= 2) {
            $this->db->select('id, SKU, titleRU AS title, titleRO AS title_ro, on_stock');
            $this->db->group_start();
            $this->db->like('titleRU', $query)
                ->or_like('titleRO', $query)
                ->or_like('SKU', $query);
            if (ctype_digit($query)) {
                $this->db->or_where('id', (int) $query);
            }
            $this->db->group_end();
            $this->db->where('isShown', 1);
            $this->db->order_by('stock DESC, id DESC');
            $products = $this->db->limit(15)->get('products')->result_array();
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($products));
    }
}
