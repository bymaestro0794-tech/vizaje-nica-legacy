<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Gifts_model extends BaseModel
{
    protected $tblname = 'gifts';

    public function get_admin()
    {
        return $this->db
            ->select('g.*, p.titleRU AS product_title_ru, p.titleRO AS product_title_ro, p.SKU AS product_sku')
            ->from('gifts g')
            ->join('products p', 'p.id = g.product_id', 'left')
            ->order_by('g.sorder ASC, g.id DESC')
            ->get()
            ->result();
    }

    public function get_enabled()
    {
        $query = $this->db
            ->where('is_enabled', 1);
        if ($this->db->field_exists('show_on_site', $this->tblname)) {
            $query->where('show_on_site', 1);
        }

        return $query
            ->order_by('sorder ASC, id DESC')
            ->get($this->tblname)
            ->result();
    }

    public function find_first_admin($id)
    {
        return $this->db
            ->select('g.*, p.titleRU AS product_title_ru, p.titleRO AS product_title_ro, p.SKU AS product_sku')
            ->from('gifts g')
            ->join('products p', 'p.id = g.product_id', 'left')
            ->where('g.id', (int) $id)
            ->get()
            ->row();
    }

    public function find_enabled($id)
    {
        $query = $this->db
            ->where('id', (int) $id)
            ->where('is_enabled', 1);
        if ($this->db->field_exists('show_on_site', $this->tblname)) {
            $query->where('show_on_site', 1);
        }

        return $query->get($this->tblname)
            ->row();
    }

    public function get_product_variants($productId)
    {
        return $this->db
            ->select('id, SKU, titleRU, titleRO, colorRU, colorRO, VolumeVar')
            ->where('product_id', (int) $productId)
            ->where('isShown', 1)
            ->order_by('sorder ASC, id DESC')
            ->get('products_variable')
            ->result();
    }
}
