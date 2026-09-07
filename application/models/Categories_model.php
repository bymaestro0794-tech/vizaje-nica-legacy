<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Categories_model extends BaseModel
{
    protected $tblname = 'categories';

    public function __construct()
    {
        parent::__construct();
    }
    public function getCategories($lang = 'RO')
    {
        return $this->db->select(['id', 'title' . $lang . ' AS title', 'parent_id', 'uri' . $lang . ' AS uri', 'parent_id', 'step'])->where(['isShown' => 1])->order_by('sorder ASC, id DESC')->get($this->tblname)->result();
    }

    public function get_category_parents()
    {
        $this->db->select("id as id, titleRU as titleRU, titleRO as titleRO, parent_id as parent_id");
        $this->db->where('isShown', 1);
        $this->db->where('step', 1);
        $this->db->order_by('sorder ASC, id DESC');
        $category_parents =  $this->db->get($this->tblname)->result();
        foreach ($category_parents as $category_parent) {
            $this->db->select("id as id, titleRU as titleRU, titleRO as titleRO, parent_id as parent_id");
            $this->db->where('parent_id', $category_parent->id);
            $this->db->where('isShown', 1);
            $this->db->where('step', 2);
            $this->db->order_by('sorder ASC, id DESC');
            $category_parent->children =  $this->db->get($this->tblname)->result();
        }

        return $category_parents;
    }

    public function get_category_menu($lang)
    {
        if (empty($lang)) return false;
        $this->db->select("            
            id as id,
            parent_id as parent_id,
            step as step,
            title$lang as title,
            uri$lang as uri,
            imgNav as imgNav,  
            imgNavHover as imgNavHover,
            (SELECT products.id FROM products JOIN products_img ON products_img.product_id = products.id WHERE products.category_id=$this->tblname.id AND products.isShown = 1 AND products.price > 0 ORDER BY id DESC LIMIT 1) as products,
            ");
        $this->db->where('isShown', 1);
        $this->db->order_by('sorder ASC, id DESC');
        $categories = $this->db->get($this->tblname)->result();

        $categoryIds = array_column($categories, 'id');

        $bannersByCategory = [];

        if (!empty($categoryIds)) {
            $banners = $this->db
                ->select("
            category_id,
            id,
            title$lang AS title,
            desc$lang AS `desc`,
            uri$lang AS uri,
            img
        ")
                ->where_in('category_id', $categoryIds)
                ->order_by('category_id', 'ASC')
                ->order_by('id', 'ASC')
                ->get('category_banner')
                ->result();

            foreach ($banners as $banner) {
                $bannersByCategory[$banner->category_id][] = $banner;
            }
        }

        foreach ($categories as $category) {
            $categoryBanners = $bannersByCategory[$category->id] ?? [];

            $category->banner = $categoryBanners[0] ?? null;
            $category->banner2 = !empty($categoryBanners)
                ? $categoryBanners[count($categoryBanners) - 1]
                : null;
        }

        // foreach ($categories as $key => $category) {
        //     $banners = $this->db
        //         ->select("id as id,title$lang as title, desc$lang as desc, uri$lang as uri, img as img ")
        //         ->where('category_id', $category->id)
        //         ->order_by('id', 'ASC')
        //         ->get('category_banner')
        //         ->result();

        //     $categories[$key]->banner  = $banners[0] ?? null;
        //     $categories[$key]->banner2 = !empty($banners) ? $banners[count($banners) - 1] : null;
        // }

        $nav_categories = array();
        foreach ($categories as $category) {
            if ($category->parent_id == 0) {
                $nav_categories[$category->id] = $category;
                foreach ($categories as $category_ch) {
                    if ($category->id == $category_ch->parent_id) {
                        $nav_categories[$category->id]->children[$category_ch->id] = $category_ch;
                        foreach ($categories as $category_c) {
                            if ($category_ch->id == $category_c->parent_id) {
                                if (!empty($category_c->products)) {
                                    $nav_categories[$category->id]->children[$category_ch->id]->children[$category_c->id] = $category_c;
                                }
                            }
                        }
                        if (empty($nav_categories[$category->id]->children[$category_ch->id]->children) && empty($category_ch->products)) {
                            unset($nav_categories[$category->id]->children[$category_ch->id]);
                        }
                    }
                }
                if (empty($nav_categories[$category->id]->children) && empty($category->products)) {
                    unset($nav_categories[$category->id]);
                }
            }
        }
        return $nav_categories;
    }

    public function get_category_home($lang)
    {
        if (empty($lang)) return false;
        $this->db->select("            
            id as id,
            parent_id as parent_id,
            step as step,
            title$lang as title,
            uri$lang as uri,
            img as img,  
            imgNavHover as imgNavHover,  
            ");
        $this->db->where('isShown', 1);
        $this->db->where('top_menu', 1);
        $this->db->offset(0);
        $this->db->limit(6);
        $this->db->order_by('sorder ASC, id DESC');
        return $this->db->get($this->tblname)->result();
    }

    public function menu_trainings_categories($lang)
    {
        if (empty($lang)) return false;

        $this->db->select("
            id as id,
            title$lang as title, 
            uri$lang as uri, 
            img as img, 
        ");
        $this->db->where('isShown', 1);
        $this->db->order_by('sorder ASC, ID DESC');
        $trainings_categories =  $this->db->get($this->tblname)->result();
        foreach ($trainings_categories as $item) {
            $item->count = $this->db->where('category_id', $item->id)->where('isShown', 1)->from('trainings')->count_all_results();
        }
        return $trainings_categories;
    }

    public function get_category_by_id($lang, $id)
    {
        if (empty($lang)) return false;

        $this->db->select("
            id as id,
            title$lang as title, 
            h1$lang as h1_title,
            breadcrumbTitle$lang as breadcrumb_title,
            desc$lang as desc, 
            uri$lang as uri, 
            uriRO as uriRO, 
            uriRU as uriRU,
            uriEN as uriEN,  
            img as img, 
            step as step, 
            parent_id as parent_id, 
            feature_type as feature_type, 
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

    public function get_category_by_uri($lang, $uri)
    {
        if (empty($lang)) return false;

        $this->db->select("
            id as id,
            title$lang as title, 
            h1$lang as h1_title,
            breadcrumbTitle$lang as breadcrumb_title,
            desc$lang as desc, 
            uri$lang as uri, 
            uriRO as uriRO, 
            uriRU as uriRU,
            uriEN as uriEN, 
            img as img, 
            step as step, 
            wallpaper as wallpaper, 
            parent_id as parent_id, 
            feature_type as feature_type, 
            seoTitle$lang as seo_title,
            seoKeywords$lang as seo_keywords,
            seoDesc$lang as seo_desc,
            seoText$lang as seo_text,
        ");
        $this->db->where('isShown', 1);
        $this->db->where("uri$lang", $uri);
        $item =  $this->db->get($this->tblname)->row();

        return $item;
    }

    public function get_category_children($lang, $id)
    {
        if (empty($lang)) return false;

        $this->db->select("
            id as id,
            title$lang as title, 
            uri$lang as uri,
            parent_id as parent_id,
            img as img,
            (SELECT COUNT(*) FROM products WHERE products.category_id=$this->tblname.id AND products.isShown=1) as count
        ");
        $this->db->where('isShown', 1);
        $this->db->where("parent_id", $id);
        $items =  $this->db->get($this->tblname)->result();
        return $items;
    }

    public function get_search_categories($lang, $search)
    {
        $this->db->select("
            id as id,
            title$lang as title, 
            uri$lang as uri, 
            img as img, 
        ");
        $this->db->group_start();
        $this->db->or_like('titleRU', $search);
        $this->db->or_like('titleRO', $search);
        $this->db->group_end();
        $this->db->where('isShown', 1);
        $this->db->offset(0);
        $this->db->limit(10);
        $this->db->order_by("sorder ASC, id DESC");
        return $this->db->get($this->tblname)->result();
    }

    public function get_category_ids($lang, $brand_id, $ids)
    {
        if (empty($ids)) return false;

        $this->db->select("
        id AS id,
        uri{$lang} AS uri,
        title{$lang} AS title,
        (
            SELECT COUNT(p.id)
            FROM products p
            WHERE p.brand_id = {$brand_id}
              AND p.category_id = {$this->tblname}.id
              AND p.price > 0
              AND p.isShown = 1
              AND EXISTS (
                  SELECT 1
                  FROM products_img pi
                  WHERE pi.product_id = p.id
              )
        ) AS count
    ", false);

        $this->db->where_in('id', $ids);
        $this->db->where('isShown', 1);
        $this->db->order_by("sorder ASC, id DESC");

        return $this->db->get($this->tblname)->result();
    }
}
