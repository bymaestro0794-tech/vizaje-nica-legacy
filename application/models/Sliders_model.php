<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sliders_model extends BaseModel
{
    protected $tblname = 'sliders';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_sliders_home($lang)
    {
        $this->db->select("
            id as id,
            img as img,
            imgMob as imgMob,
            youtube as youtube,
            ColorText as ColorText,
            ColorButton as ColorButton,
            ColorButtonText as ColorButtonText,
            title$lang as title,
            desc$lang as desc,
            info$lang as info,
            button$lang as button,
            uri$lang as uri,
            ");
        $this->db->where('isShown', 1);
        $this->db->order_by("sorder ASC, id DESC");
        $item = $this->db->get($this->tblname)->result();
        return $item;
    }
}
