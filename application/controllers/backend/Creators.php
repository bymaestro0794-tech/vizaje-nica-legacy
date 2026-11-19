<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Creators extends BackEndController
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
        $this->load->model('creators_model');
        $this->data['title'] = 'Креаторы';
    }

    public function index()
    {
        $state = (string) $this->input->get('state', true);
        $this->data['selected_state'] = in_array($state, array('all', 'active', 'inactive'), true) ? $state : 'all';
        $this->data['search'] = trim((string) $this->input->get('search', true));
        $this->data['creators'] = $this->creators_model->listCreators(
            $this->data['search'],
            $this->data['selected_state']
        );
        $this->data['creator_flag_supported'] = $this->db->field_exists('is_creator', 'clients');
        $this->data['inner_view'] = 'dashboard/creators/index';
        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }
}
