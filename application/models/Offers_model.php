<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Offers_model extends BaseModel
{
    protected $tblname = 'offers';

    public function __construct()
    {
        parent::__construct();
    }

    public function gat_products_offers($id)
    {
        if (empty($id)) {
            return false;
        }
        $this->db->select("
            id as id,
            offers_id as offers_id,
            product_id as product_id,
            title as title,
            SKU as SKU,
            sorder as sorder,
        ");
        $this->db->where('offers_id', $id);
        $this->db->order_by('sorder ASC, id DESC');
        return $this->db->get('offers_products')->result();
    }

    public function get_offers_data_by_uri($lang, $uri)
    {
        if (empty($lang)) {
            return false;
        }
        $this->db->select("
            id,
            date_from,
            date_to,
            title$lang as title,
            desc$lang as desc,
            location$lang as location,
            uri$lang as uri,
            uriRU as uriRU,
            uriRO as uriRO,
            uriEN as uriEN,
            seoTitle$lang as seo_title,
            seoKeywords$lang as seo_keywords,
            seoDesc$lang as seo_desc,
            img$lang as img,
            imgB$lang as imgB,
            view_home,
            date_from,
            date_to,
        ");
        $this->db->where('isShown', 1);
        $this->db->where("uri$lang", $uri);
        return $this->db->get($this->tblname)->row();
    }

    public function get_offers_data_by_id($lang, $id)
    {
        if (empty($lang)) {
            return false;
        }
        $this->db->select("
            id,
            date_from,
            date_to,
            title$lang as title,
            desc$lang as desc,
            location$lang as location,
            uri$lang as uri,
            uriRU as uriRU,
            uriRO as uriRO,
            uriEN as uriEN,
            seoTitle$lang as seo_title,
            seoKeywords$lang as seo_keywords,
            seoDesc$lang as seo_desc,
                     img$lang as img,
            imgB$lang as imgB,
            view_home,
            date_from,
            date_to,
        ");
        $this->db->where('isShown', 1);
        $this->db->where("id", $id);
        return $this->db->get($this->tblname)->row();
    }

    public function get_offers_home($lang = false)
    {
        if (empty($lang)) {
            return false;
        }
        $this->db->select("
            id,
            date_from,
            date_to,
            title$lang as title,
            desc$lang as desc,
            uri$lang as uri,
                     img$lang as img,
            imgB$lang as imgB,
            view_home
        ");
        $this->db->where('date_from <=', date('Y-m-d'));
        $this->db->where('date_to >=', date('Y-m-d'));
        $this->db->where('isShown', 1);
        $this->db->where('view_home', 1);
        $this->db->order_by('sorder ASC, date_to DESC, id DESC');
        $offers = $this->db->get($this->tblname)->result();
        return $offers;
    }

    public function get_offers($lang = false)
    {
        if (empty($lang)) {
            return false;
        }
        $this->db->select("
            id,
            date_from,
            date_to,
            title$lang as title,
            desc$lang as desc,
            uri$lang as uri,
                     img$lang as img,
            imgB$lang as imgB,
            view_home
        ");
        $this->db->where('isShown', 1);
        $this->db->order_by('sorder ASC, date_to DESC, id DESC');
        $offers = $this->db->get($this->tblname)->result();
        return $offers;
    }

    public function get_offers_active($lang = false)
    {
        if (empty($lang)) {
            return false;
        }
        $this->db->select("
            id,
            date_from,
            date_to,
            title$lang as title,
            desc$lang as desc,
            uri$lang as uri,
                     img$lang as img,
            imgB$lang as imgB,
            view_home,
            date_from,
            date_to,
        ");
        $this->db->where('date_from <', date('Y-m-d'));
        $this->db->where('date_to >', date('Y-m-d'));
        $this->db->order_by('sorder ASC, date_to DESC,  id DESC');
        $offers = $this->db->get($this->tblname)->result();
        if (!empty($offers)) {
            foreach ($offers as $offer) {
                if (!empty($offer->view_home)) {
                    $offers_products = $this->db->select('id,product_id')->where('offers_id', $offer->id)->offset(0)->limit(6)->order_by('sorder ASC, id DESC')->get('offers_products')->result();
                    if (!empty($offers_products)) {
                        foreach ($offers_products as $value) {
                            $products_id[] = $value->product_id;
                        }
                        $offer->products = $this->products_offers($lang, $products_id);
                    }
                }
            }
        }
        return $offers;
    }

    public function products_offers($lang, $products_id)
    {
        $this->db->select("
            id, 
            SKU, 
            category_id, 
            title$lang as title,
            desc$lang as desc,
            uri$lang as uri,
            price,  
            priceWH
            discount_price, 
            on_stock,
            on_stockWH,
            is_new, 
             (SELECT brands.title FROM brands WHERE brands.id=products.brand_id) as brand_title,
             (SELECT products_img.img FROM products_img WHERE products_img.product_id=products.id LIMIT 1) as img,
             (SELECT categories.uri$lang FROM categories WHERE categories.id=products.category_id) as cat_uri,
             (SELECT categories.title$lang FROM categories WHERE categories.id=products.category_id) as cat_title, 
        ");
        $this->db->where_in('id', $products_id);
        $this->db->where('isShown', 1);
        $this->db->where('on_stock >', 0);
        return $this->db->get('products')->result();
    }
}