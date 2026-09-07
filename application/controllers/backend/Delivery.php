<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Delivery extends BackEndController {
    protected $add;
    protected $index_title;
    protected $title;
    protected $success_add;
    protected $success_update_order;
    protected $success_edit;
    protected $success_delete;

    public function __construct() {
        parent::__construct(__CLASS__);

        $this->index_title = lang('Delivery');
        $this->add = lang('Add');
        $this->success_update_order = lang('You have successfully updated display order!');
        $this->success_add = lang('You have successfully added an object');
        $this->success_edit = lang('You have successfully updated the object');
        $this->success_delete = lang('You have successfully deleted the object');

        $this->data['title'] = $this->index_title;
        $this->data['add'] = $this->add;
        $this->load->model('delivery_model');
    }

    public function index() {

        init_load_img($this->main_page);
        $id = 1;
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                foreach ($_POST as $index => $post_data) {
                    $post[$index] = $this->input->post($index, TRUE);
                }
                if (!empty($_FILES['img']['name'])) {
                    $this->upload->do_upload('img');
                    $file_data = $this->upload->data();
                    $file = $file_data['file_name'];
                    $file_types = array('.jpg', '.jpeg', '.gif', '.png');
                    if (in_array(strtolower($file_data['file_ext']), $file_types)) {
                        $post['img'] = $file;
                    }
                }

                if (!$this->delivery_model->update($post, $id)) {
                    throw new Exception('Ошибка записи данных в таблицу: ' . $this->main_page);
                }

                $_SESSION['success'] = $this->success_edit;
            } catch (Exception $e) {
                log_message('error', $e->getMessage());
                $errors[] = 'Выброшено исключение : ' . $e->getMessage();
                $_SESSION['error'] = $errors;
            }
            redirect($this->path);
        }

        $objects = $this->delivery_model->find_first(1);

        $this->data['inner_view'] = $this->index_view;
        $this->data['item'] = $objects;

        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }


}