<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Stores_model extends BaseModel
{
    protected $tblname = 'stores';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_stores_onBottom($lang)
    {
        $this->db->select("
            id as id,
            coords as coords,
            title$lang as title,
            ");
        $this->db->where('isShown', 1);
        $this->db->where('onBottom', 1);
        $item = $this->db->get($this->tblname)->result();
        return $item;
    }

    public function get_stores($lang)
    {
        $this->db->select("
            id as id,
            coords as coords,
            phone as phone,
            title$lang as title,
            desc$lang as desc,
            text$lang as text,
            ");
        $this->db->where('isShown', 1);
        $item = $this->db->get($this->tblname)->result();
        return $item;
    }
    public function get_stores_delivery($lang)
    {
        $this->db->select("
            id as id,
            coords as coords,
            phone as phone,
            title$lang as title,
            desc$lang as desc,
            text$lang as text,
            ");
        $this->db->where('delivery', 1);
        $this->db->where('isShown', 1);
        $item = $this->db->get($this->tblname)->result();
        return $item;
    }
}
