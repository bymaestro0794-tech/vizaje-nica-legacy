<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Constants extends  BackEndController
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function index()
    {
        $main_page = 'constants';
        $title = lang('Constants');

        $this->load->model('constants_model');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $ru = $this->input->post('ru', FALSE);
            $ro = $this->input->post('ro', FALSE);

            try {
                foreach ($ru as $id => $val) {
                    if (!$this->constants_model->update_ru($id, $val)) {
                        throw new Exception(lang('Error writing data to table').$main_page.' RU');
                    }

                }
                foreach ($ro as $id => $val) {
                    if (!$this->constants_model->update_ro($id, $val)) {
                        throw new Exception(lang('Error writing data to table').$main_page.' RO');
                    }

                }

                $_SESSION['success'] = lang('You have successfully updated the constants');
            } catch (Exception $e) {
                log_message('error', $e->getMessage());
                $errors[] = lang('Error writing data to table') . $e->getMessage();
                $_SESSION['error'] = $errors;
            }
        }

        $constants = $this->constants_model->find();
        $constants_list = [];

        $groupes = [];
        if(!empty($constants)){
            foreach($constants as $element){
                $groupes[] = $element->groupes;
            }

            foreach(array_unique($groupes) as $groupe_name){
                // $arr['groupe'] = $groupe_name;
                $arr = [];
                foreach($constants as $element){
                    if($element->groupes == $groupe_name){
                        $arr[] = $element;
                    }
                }
                $constants_list[$groupe_name] = $arr;
            }
        }

        $data = array(
            'inner_view' => 'dashboard/' . $main_page . '/index',
            'constants' => $constants_list,
            'title' => $title
        );

        $this->load->vars($data);
        $this->load->view('dashboard/index');
    }
}
