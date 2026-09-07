<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Features extends CI_Controller
{
    public function add_type() {
        check_if_POST();
        $response['status'] = 'fail';
        $post = $this->input->post();

        if(isset($post['name']) && !empty($post['name'])) {
            $this->load->model('shop_type_model');
            $sorder = $this->shop_type_model->select_max_sorder();
            if($this->shop_type_model->put(array('name' => $post['name'], 'sorder' => ($sorder ? $sorder->sorder + 1 : 0)))) {
                $response['id'] = $this->db->insert_id();
                $response['name'] = $post['name'];
                $response['status'] = 'ok';
            }
        }

        echo json_encode($response);
    }

    public function change_type() {
        check_if_POST();
        $response['status'] = 'fail';
        $post = $this->input->post();

        if(isset($post['name']) && isset($post['id']) && !empty($post['name'])) {
            $this->load->model('shop_type_model');

            if($this->shop_type_model->update(array('name' => $post['name']), $post['id'])) {
                $response['id'] = $post['id'];
                $response['name'] = $post['name'];
                $response['status'] = 'ok';
            }
        }

        echo json_encode($response);
    }

    public function sort_type_menu() {
        check_if_POST();

        $post = $this->input->post();
        $response['status'] = 'fail';
        if(isset($post['sorder'])) {
            $this->load->model('shop_type_model');

            $sorder = array();
            foreach($post['sorder'] as $key => $value) {
                $sorder[$value] = $key;
            }

            $result = $this->shop_type_model->update_sorder($sorder);
            if($result) {
                $response['status'] = 'ok';
            }
        }

        echo json_encode($response);
    }

    public function add_feature() {
        check_if_POST();
        $response['status'] = 'fail';
        $post = $this->input->post();
 
        if(isset($post['type_id']) && !empty($post['type_id'])) {
            $this->load->model('shop_feature_model');

            $max_sorder = $this->shop_feature_model->select_max_sorder();
            $post['sorder'] = $max_sorder->sorder + 1;

            if($this->shop_feature_model->put($post)) {
                $response['id'] = $this->db->insert_id();
                $response['data'] = $post;
                $response['status'] = 'ok';
            }
        }

        echo json_encode($response);
    }

    public function sort_feature() {
        check_if_POST();
        $response['status'] = 'fail';
        $post = $this->input->post();

        if(isset($post['sorder'])) {
            $this->load->model('shop_feature_model');

            $sorder = array();
            foreach($post['sorder'] as $key => $value) {
                $sorder[$value] = $key;
            }

            $result = $this->shop_feature_model->update_sorder($sorder);
            if($result) {
                $response['status'] = 'ok';
            }
        }

        echo json_encode($response);
    }

    public function get_feature() {
        check_if_POST();
        $response['status'] = 'fail';
        $post = $this->input->post();

        if(isset($post['feature_id'])) {
            $this->load->model('shop_feature_model');
            $result = $this->shop_feature_model->find_first($post['feature_id']);
//            $result = $this->shop_feature_model->order_by('id DESC')->get('shop_feature')->result();
            if($result) {
                $response['status'] = 'ok';
                $response['feature'] = $result;
            }
        }

        echo json_encode($response);
    }

    public function change_feature() {
        check_if_POST();
        $response['status'] = 'fail';
        $post = $this->input->post();

        if(isset($post['feature_id'])) {
            $feature_id = $post['feature_id']; unset($post['feature_id']);
            $this->load->model('shop_feature_model');
            if($this->shop_feature_model->update($post, $feature_id)) {
                $response['status'] = 'ok';
                $response['name'] = $post['name_RU'];
                $response['type'] = $post['type'];
                $response['id'] = $feature_id;
            }
        }

        echo json_encode($response);
    }

    public function delete_feature() {
        check_if_POST();
        $response['status'] = 'fail';
        $post = $this->input->post();

        if(isset($post['id'])) {
            $this->load->model('shop_feature_model');
            $this->load->model('shop_feature_values_model');

            if($this->shop_feature_model->delete($post['id'])) {
                if($this->shop_feature_values_model->deleteByField('feature_id', $post['id'])) {
                    $response['status'] = 'ok';
                }
            }
        }

        echo json_encode($response);
    }

    public function delete_feature_value() {
        check_if_POST();
        $response['status'] = 'fail';
        $post = $this->input->post();

        if(isset($post['id'])) {
            $this->load->model('shop_feature_values_model');

            if($this->shop_feature_values_model->delete($post['id'])) {
                $response['status'] = 'ok';
            }
        }

        echo json_encode($response);
    }

    public function delete_type() {
        check_if_POST();
        $response['status'] = 'fail';
        $post = $this->input->post();

        if(isset($post['id'])) {
            $this->load->model('shop_type_model');
            $this->load->model('shop_feature_model');
            $this->load->model('shop_feature_values_model');

            $features = $this->shop_feature_model->getByField('type_id', $post['id'], true, 'sorder', 'ASC', null, 'array');

            if($features) {
                foreach($features as $feature) {
                    $this->shop_feature_model->delete($feature['id']);
                    $this->shop_feature_values_model->deleteByField('feature_id', $feature['id']);
                }
            }

            if($this->shop_type_model->delete($post['id'])) {
                $response['status'] = 'ok';
            }
        }

        echo json_encode($response);
    }

    public function add_feature_value() {
        check_if_POST();
        $response['status'] = 'fail';
        $post = $this->input->post();

        if(isset($post['feature_id']) && !empty($post['feature_id'])) {
            $this->load->model('shop_feature_values_model');
            $this->load->model('shop_feature_model');

            $response['feature'] = $this->shop_feature_model->find_first($post['feature_id']);

            $max_sorder = $this->shop_feature_values_model->max_sorder($post['feature_id']);
            $post['sorder'] = $max_sorder->sorder + 1;

            if($this->shop_feature_values_model->put($post)) {
                $response['id'] = $this->db->insert_id();
                $response['data'] = $post;
                $response['status'] = 'ok';
            }
        }

        echo json_encode($response);
    }

    public function get_feature_value() {
        check_if_POST();
        $response['status'] = 'fail';
        $post = $this->input->post();

        if(isset($post['value_id'])) {
            $this->load->model('shop_feature_values_model');
            if($result = $this->shop_feature_values_model->find_first($post['value_id'])) {
                $response['status'] = 'ok';
                $response['value'] = $result;
            }
        }

        echo json_encode($response);
    }

    public function change_feature_value() {
        check_if_POST();
        $response['status'] = 'fail';
        $post = $this->input->post();

        if(isset($post['value_id'])) {
            $value_id = $post['value_id']; unset($post['value_id']);
            $this->load->model('shop_feature_values_model');
            if($this->shop_feature_values_model->update($post, $value_id)) {
                $response['status'] = 'ok';
                $response['name'] = $post['name_RO'];
                $response['id'] = $value_id;
            }
        }

        echo json_encode($response);
    }

    public function sort_feature_values() {
        check_if_POST();
        $response['status'] = 'fail';
        $post = $this->input->post();

        if(isset($post['sorder_val'])) {
            $this->load->model('shop_feature_values_model');

            $sorder = array();
            foreach($post['sorder_val'] as $key => $value) {
                $sorder[$value] = $key;
            }

            $result = $this->shop_feature_values_model->update_sorder($sorder);
            if($result) {
                $response['status'] = 'ok';
            }
        }

        echo json_encode($response);
    }
}