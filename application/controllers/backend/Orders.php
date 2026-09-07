<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Orders extends BackEndController
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

        $this->index_title = lang('Orders');
        $this->add = lang('Add');
        $this->success_update_order = lang('You have successfully updated display order!');
        $this->success_add = lang('You have successfully added an object');
        $this->success_edit = lang('You have successfully updated the object');
        $this->success_delete = lang('You have successfully deleted the object');

        $this->data['title'] = $this->index_title;
        $this->data['add'] = $this->add;
        $this->load->model('orders_model');
        $this->load->model('stores_model');
    }

    public function index()
    {
        init_load_img($this->main_page);
        $search_get = $this->input->post_get('query');
        $get = $this->input->get(null, true);
        if (!empty($search_get) && !empty($get)) {
            $objects = $this->orders_model->search_get_orders_admin($search_get, $get);
        } else if (!empty($get)) {
            $objects = $this->orders_model->search_get_orders_admin(array(), $get);
        } else if (!empty($search_get)) {
            $objects = $this->orders_model->search_get_orders_admin($search_get);
        } else {
            $objects = $this->orders_model->find();
        }

        $this->data['inner_view'] = $this->index_view;
        $this->data['objects'] = $objects;
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

            if (!$this->orders_model->put($post)) {
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

        try {
            $post = $this->input->post('so');

            if (empty($post) || !is_array($post)) {
                throw new Exception(lang('Error writing data to table'));
            }

            if (!$this->orders_model->update_sorder($post)) {
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

        $item = $this->orders_model->find_first($id);
        if (empty($item)) throw_on_404();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                foreach ($_POST as $index => $post_data) {
                    $post[$index] = $this->input->post($index, TRUE);
                }
                $post['updated'] = date('Y-m-d H:i:s', strtotime('+2 hours'));
                if (isset($post['products'])) {
                    foreach ($post['products'] as $key => $product) {
                        if ($product['price'] == 0)
                            $this->db->where('id', $key)->delete('orders_products');
                        else
                            $products[] = ['id' => $key, 'qty' => $product['qty'], 'total' => $product['qty'] * $product['price']];
                    }

                    $this->db->update_batch('orders_products', $products, 'id');
                    $this->db->where('id', $id)->update('orders', ['updated' => date('Y-m-d H:i:s')]);
                }

                unset($post['products']);

                if (!empty($post))
                    if (!$this->orders_model->update($post, $id)) {
                        throw new Exception(lang('Error writing data to table') . $this->main_page);
                    }

                $_SESSION['success'] = $this->success_edit;
            } catch (Exception $e) {
                log_message('error', $e->getMessage());
                $errors[] = lang('Exception thrown') . $e->getMessage();
                $_SESSION['error'] = $errors;
            }

            $item = $this->orders_model->find_first($id);

        }

        $this->data['inner_view'] = $this->item_view;
        $this->data['title'] = 'Order #' . $item->order_id;
        $this->data['parent_url'] = $this->path;
        $this->data['parent_title'] = $this->index_title;
        $this->data['item'] = $item;
        $this->data['stores'] = $this->stores_model->get_stores('RU');
        $this->data['products'] = $this->orders_model->orderProducts($item->id, 'RU');

        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }

    public function delete($id = false)
    {
        $id = (int)$id;

        $item = $this->orders_model->find_first($id);

        if (empty($item)) throw_on_404();

        try {
            if (!$this->orders_model->delete($id)) {
                throw new Exception(lang('Error writing data to table') . $this->main_page);
            }
            $_SESSION['success'] = $this->success_delete;
        } catch (Exception $e) {
            $errors[] = lang('Exception thrown') . $e->getMessage();
            $_SESSION['error'] = $errors;
        }

        redirect($this->path);
    }

}