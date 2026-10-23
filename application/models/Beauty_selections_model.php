<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Beauty_selections_model extends BaseModel
{
    protected $tblname = 'beauty_selections';

    public function get_public($lang)
    {
        $lang = strtoupper((string) $lang);

        return $this->db
            ->select("id, title{$lang} AS title, uri{$lang} AS uri, desc{$lang} AS `desc`, img{$lang} AS img")
            ->where('isShown', 1)
            ->order_by('sorder ASC, id DESC')
            ->get($this->tblname)
            ->result();
    }

    public function get_public_by_uri($lang, $uri)
    {
        $lang = strtoupper((string) $lang);

        return $this->db
            ->select("id, title{$lang} AS title, uri{$lang} AS uri, desc{$lang} AS `desc`, text{$lang} AS text,
                img{$lang} AS img, seoTitle{$lang} AS seo_title, seoKeywords{$lang} AS seo_keywords,
                seoDesc{$lang} AS seo_desc, uriRU, uriRO")
            ->where('isShown', 1)
            ->where("uri{$lang}", $uri)
            ->get($this->tblname)
            ->row();
    }

    public function get_product_ids($selectionId)
    {
        return $this->db
            ->select('product_id')
            ->where('selection_id', (int) $selectionId)
            ->order_by('sorder ASC, id ASC')
            ->get('beauty_selection_products')
            ->result_array();
    }

    public function replace_products($selectionId, $productIds)
    {
        $selectionId = (int) $selectionId;
        if (!is_array($productIds)) {
            $productIds = preg_split('/[,\s]+/', (string) $productIds, -1, PREG_SPLIT_NO_EMPTY);
        }
        $productIds = array_values(array_unique(array_filter(array_map('intval', (array) $productIds))));
        if (!empty($productIds)) {
            $validIds = array_column(
                $this->db->select('id')->where_in('id', $productIds)->get('products')->result_array(),
                'id'
            );
            $productIds = array_values(array_intersect($productIds, array_map('intval', $validIds)));
        }

        $this->db->trans_start();
        $this->db->where('selection_id', $selectionId)->delete('beauty_selection_products');

        foreach ($productIds as $order => $productId) {
            $this->db->insert('beauty_selection_products', array(
                'selection_id' => $selectionId,
                'product_id' => $productId,
                'sorder' => $order + 1,
            ));
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }
}
