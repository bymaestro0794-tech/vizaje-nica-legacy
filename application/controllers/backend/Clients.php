<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Clients extends BackEndController
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

        $this->index_title = lang('Menu');
        $this->add = lang('Add');
        $this->success_update_order = lang('You have successfully updated display order!');
        $this->success_add = lang('You have successfully added an object');
        $this->success_edit = lang('You have successfully updated the object');
        $this->success_delete = lang('You have successfully deleted the object');

        $this->data['title'] = $this->index_title;
        $this->data['add'] = $this->add;
        $this->load->model('clients_model');
    }

    public function index()
    {
        init_load_img($this->main_page);


        if(isset($_GET['search']) && !empty($_GET['search'])){
            $objects = $this->clients_model->get_clients_active($_GET['search']);
            $this->data['objects'] = $objects;
            $this->data['count'] = 0;
        } else {
            $objects = $this->clients_model->pagination(40, isset($_GET['page']) ? $_GET['page'] : 1, isset($_GET['xml']) ? $_GET['xml'] : 1);
            $this->data['count'] = $objects['count'];
            $this->data['objects'] = $objects['data'];
        }
//
//        if(isset($_GET['search']) && !empty($_GET['search'])){
//            $objects = $this->clients_model->get_clients_active($_GET['search']);
//        } else{
//            $objects = $this->clients_model->find_active();
//        }

        $this->data['inner_view'] = $this->index_view;
//        $this->data['objects'] = $objects;

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


            if (!empty($_FILES['img']['name'])) {
                $this->upload->do_upload('img');
                $file_data = $this->upload->data();
                $file = $file_data['file_name'];

                $file_types = array('.jpg', '.jpeg', '.gif', '.png');

                if (in_array(strtolower($file_data['file_ext']), $file_types)) {
                    $post['img'] = $file;
                }
            }

            $id = $this->clients_model->put($post);
            if (!$id) {
                throw new Exception('Ошибка записи данных в таблицу: ' . $this->main_page);
            }
            $this->db->insert('clients_addresses',array('client_id'=>$id));

            $_SESSION['success'] = $this->success_add;
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            $errors[] = 'Выброшено исключение : ' . $e->getMessage();
            $_SESSION['error'] = $errors;
        }

        redirect($this->path);
    }

    public function update_order()
    {
        check_if_POST();

        $this->load->model('clients_model');

        try {
            $post = $this->input->post('so');

            if (empty($post) || !is_array($post)) {
                throw new Exception('Ошибка в полученных данных!');
            }

            if (!$this->clients_model->update_sorder($post)) {
                throw new Exception('Ошибка записи данных в таблицу: ' . $this->main_page);
            }

            $_SESSION['success'] = $this->success_update_order;
        } catch (Exception $e) {
            $errors[] = 'Выброшено исключение : ' . $e->getMessage();
            $_SESSION['error'] = $errors;
        }

        redirect($this->path);
    }

    public function item($id = 0)
    {
        $id = (int)$id;
        init_load_img($this->main_page);
        $item = $this->clients_model->find_first($id);
        if (empty($item)) throw_on_404();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                foreach ($_POST as $index => $post_data) {
                    $post[$index] = $this->input->post($index);
                }

                if (!empty($post['password'])){
                    $post['password'] = sha1($post["password"]);
                } else {
                    unset($post['password']);
                }

                if (!$this->clients_model->update($post, $id)) {
                    throw new Exception('Ошибка записи данных в таблицу: ' . $this->main_page);
                }

                $_SESSION['success'] = $this->success_edit;
            } catch (Exception $e) {
                log_message('error', $e->getMessage());
                $errors[] = 'Выброшено исключение : ' . $e->getMessage();
                $_SESSION['error'] = $errors;
            }

            $item = $this->clients_model->find_first($id);
        }

        $this->load->model(
            'promocodes_model'
        );

        $promocodeEvents =
            $this->promocodes_model
                ->getClientEvents(
                    $id
                );

        $this->data['inner_view'] = $this->item_view;
        $this->data['title'] = 'Редактирование ' . $item->name;
        $this->data['parent_url'] = $this->path;
        $this->data['parent_title'] = $this->index_title;
        $this->data['item'] = $item;

        $this->data['promocode_events'] =
            $promocodeEvents;

        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }

    public function delete($id = false)
    {
        $id = (int)$id;

        $item = $this->clients_model->find_first($id);

        if (empty($item)) throw_on_404();

        try {
            if (!empty($item->img)) {
                $fileList = recDirSearch($_SERVER['DOCUMENT_ROOT'] . '/public/clients/', $item->img);
            }

            if (!empty($fileList)) {
                foreach ($fileList as $file) {
                    unlink($file);
                }
            }
            if (!$this->clients_model->delete($id)) {
                throw new Exception('Ошибка удаления данных из таблицы: ' . $this->main_page);
            }
            $_SESSION['success'] = $this->success_delete;
        } catch (Exception $e) {
            $errors[] = 'Выброшено исключение : ' . $e->getMessage();
            $_SESSION['error'] = $errors;
        }

        redirect($this->path);
    }
}
