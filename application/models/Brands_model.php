<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Brands_model extends BaseModel
{
    protected $tblname = 'brands';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_brands()
    {
        $this->db->select("
            id as id,
            title as title, 
            uri as uri, 
            img as img,
        ");
        $this->db->where('isShown', 1);
        $item =  $this->db->get($this->tblname)->result();
        return $item;
    }

    public function get_brands_home()
    {
        $this->db->select("
            id as id,
            title as title, 
            uri as uri, 
            img as img,
        ");
        $this->db->where('isShown', 1);
        $this->db->offset(0);
        $this->db->limit(10);
        $item =  $this->db->get($this->tblname)->result();
        return $item;
    }

    public function get_brands_menu()
    {
        $this->db->select("
            id as id,
            title as title, 
            uri as uri, 
            img as img,
        ");
        $this->db->where('isShown', 1);
        $this->db->offset(0);
        $this->db->limit(10);
        $item =  $this->db->get($this->tblname)->result();
        return $item;
    }

    public function get_brands_collections()
    {
        $this->db->select("
            $this->tblname.id as id,
            $this->tblname.title as title, 
            $this->tblname.uri as uri,  
        ");
        $this->db->where("$this->tblname.isShown", 1);
        $this->db->join('collections', "collections.brand_id = $this->tblname.id");
        $this->db->group_by("$this->tblname.id");
        $item =  $this->db->get($this->tblname)->result();
        return $item;
    }

    public function get_brands_products($br_id)
    {
        $this->db->select("
            id as id,
            title as title, 
            uri as uri, 
            img as img,
        ");
        $this->db->where('isShown', 1);
        $this->db->where_in('id', $br_id);
        $item =  $this->db->get($this->tblname)->result();
        return $item;
    }

    public function get_brand_by_uri($lang,$uri)
    {
        if (empty($lang)) return false;

        $this->db->select("
            id as id,
            title as title, 
            h1$lang as h1_title,
            breadcrumbTitle$lang as breadcrumb_title,
            text$lang as text, 
            uri as uri, 
            img as img, 
            seoTitle$lang as seo_title,
            seoKeywords$lang as seo_keywords,
            seoDesc$lang as seo_desc,
            seoText$lang as seo_text,
        ");
        $this->db->where('isShown', 1);
        $this->db->where("uri", $uri);
        $item =  $this->db->get($this->tblname)->row();
        return $item;
    }

    public function get_brand_by_id($lang,$id)
    {
        if (empty($lang)) return false;

        $this->db->select("
            id as id,
            title as title, 
            h1$lang as h1_title,
            breadcrumbTitle$lang as breadcrumb_title,
            text$lang as text,
            uri as uri, 
            img as img,
            seoTitle$lang as seo_title,
            seoKeywords$lang as seo_keywords,
            seoDesc$lang as seo_desc,
            seoText$lang as seo_text,
        ");
        $this->db->where('isShown', 1);
        $this->db->where("id", $id);
        $item =  $this->db->get($this->tblname)->row();
        return $item;
    }

    public function get_all_brands()
    {
        $this->db->select("id");
        $this->db->where('isShown', 1);
        return $this->db->get($this->tblname)->result();
    }

    public function get_brands_category($products)
    {
        if (empty($products)) return false;
        $br_id = array();
        $where_in = 'WHERE products.id IN (';
        foreach ($products as $key => $product){
            if ($key == 0){
                $where_in .= "'$product->id'";
            } else {
                $where_in .= ",'$product->id'";
            }
            $br_id[$product->brand_id] = $product->brand_id;
        }
        $where_in .= ")";
        $this->db->select("id as id,
            title as title, 
            uri as uri, 
            img as img,
            (SELECT COUNT(*) FROM products $where_in AND products.isShown=1 AND products.brand_id=$this->tblname.id) as count");
        $this->db->where('isShown', 1);
        $this->db->where_in('id', $br_id);
        $this->db->order_by("title ASC");
        return $this->db->get($this->tblname)->result();
    }

    public function get_pag_brands($lang)
    {
        if (empty($lang)) {
            return false;
        }
        $this->db->select("
            id as id,
            title as title, 
            uri as uri, 
            img as img,
        ");
        $this->db->where('isShown', 1);
//        $this->db->where('GUID >', 0);
        $this->db->order_by("title ASC, sorder ASC, id DESC");

        $brands = $this->db->get($this->tblname)->result();

        return $brands;
    }
    public function get_pag_brands_search($lang, $search)
    {
        if (empty($lang)) {
            return false;
        }
        $this->db->select("
            id as id,
            title as title, 
            uri as uri, 
            img as img,
        ");
        $this->db->group_start();
        $this->db->or_like('title', $search);
        $this->db->group_end();
        $this->db->where('isShown', 1);
//        $this->db->where('GUID >', 0);
        $this->db->order_by("title ASC, sorder ASC, id DESC");

        $brands = $this->db->get($this->tblname)->result();

        return $brands;
    }

    public function get_search_brands($lang, $search)
    {
        $this->db->select("
            id as id,
            title as title, 
            uri as uri
        ");
        $this->db->group_start();
        $this->db->or_like('title', $search);
        $this->db->group_end();
        $this->db->where('isShown', 1);
        $this->db->offset(0);
        $this->db->limit(10);
        $this->db->order_by("sorder ASC, id DESC");
        return $this->db->get($this->tblname)->result();
    }
    
    public function get_niche_sale_brands(
        array $brandTitles
    ) {
        $brandTitles = array_values(
            array_filter(
                array_map(
                    'trim',
                    $brandTitles
                )
            )
        );

        if (empty($brandTitles)) {
            return array();
        }

        $this->db->select("
            id,
            title,
            uri,
            img
        ");

        $this->db->where(
            'isShown',
            1
        );

        $this->db->where_in(
            'title',
            $brandTitles
        );

        $brands = $this->db
            ->get($this->tblname)
            ->result();

        /*
        * Сохраняем порядок из конфигурации лендинга,
        * а не алфавитный порядок базы.
        */
        $brandOrder = array_flip(
            $brandTitles
        );

        usort(
            $brands,
            static function (
                $firstBrand,
                $secondBrand
            ) use (
                $brandOrder
            ) {
                $firstPosition = isset(
                    $brandOrder[$firstBrand->title]
                )
                    ? $brandOrder[$firstBrand->title]
                    : PHP_INT_MAX;

                $secondPosition = isset(
                    $brandOrder[$secondBrand->title]
                )
                    ? $brandOrder[$secondBrand->title]
                    : PHP_INT_MAX;

                return $firstPosition <=> $secondPosition;
            }
        );

        return $brands;
    }
}
