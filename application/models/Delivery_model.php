<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Delivery_model extends BaseModel {
    protected $tblname = 'delivery';

    public function __construct() {
        parent::__construct();
    }

    public function get_info($lang){
        if (empty($lang)) return false;
        $this->db->select("
            id as id,
            title1$lang as title, 
            descL1$lang as descL1, 
            descR1$lang as descR1, 
            note1$lang as note, 
            text1$lang as text, 
             title2$lang as title2, 
            descL2$lang as descL2, 
            descR2$lang as descR2,
             title3$lang as title3, 
            descL3$lang as descL3, 
            descR3$lang as descR3,
             title4$lang as title4, 
            descL4$lang as descL4, 
            descR4$lang as descR4,
        ");
        $this->db->where('isShown', 1);
        return $this->db->get($this->tblname)->row();
    }
}

