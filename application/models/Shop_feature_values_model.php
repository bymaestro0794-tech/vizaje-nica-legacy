<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shop_feature_values_model extends BaseModel
{
    protected $tblname = 'shop_feature_values';

    public function __construct()
    {
        parent::__construct();
    }

    public function max_sorder($feature_id) {
        $this->db->where('feature_id', $feature_id);
        $this->db->select_max('sorder');
        return $this->db->get($this->tblname)->row();
    }
}