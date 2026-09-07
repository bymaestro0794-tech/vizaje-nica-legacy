<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Banners_model extends BaseModel
{
    protected $tblname = 'banners';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_banners_home($lang)
    {
        $this->db->select("
            id as id,
            img as img,
            imgMob as imgMob, 
            ColorText as ColorText,
            ColorButton as ColorButton,
            ColorButtonText as ColorButtonText,
            title$lang as title, 
            uri$lang as uri,
            ");
        $this->db->where('isShown', 1);
        $this->db->order_by("sorder ASC, id DESC");
        $item = $this->db->get($this->tblname)->result();
        return $item;
    }
}
