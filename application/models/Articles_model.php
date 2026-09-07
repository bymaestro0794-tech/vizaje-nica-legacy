<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Articles_model extends BaseModel
{
    protected $tblname = 'articles';
    protected $nextDay = '';

    public function __construct()
    {
        parent::__construct();

        $date = new DateTime();
        $date->modify('+1 day');
        $this->nextDay = $date->format('Y-m-d');
    }

    public function get_menu($lang = false)
    {
        if (empty($lang)) {
            return false;
        }

        $this->db->select("
            id as id,
            title$lang as title, 
            desc$lang as desc, 
            uri$lang as uri, 
            date as date,
            img as img,
        ");
        $this->db->where('isShown', 1);
        $this->db->where('menuON', 1);
        $this->db->where('date <', $this->nextDay);
        $this->db->order_by('date DESC, id DESC');
        return $this->db->get($this->tblname)->result();
    }

    public function get_articles($lang = false)
    {
        if (empty($lang)) {
            return false;
        }

        $this->db->select("
            id as id,
            title$lang as title, 
            desc$lang as desc, 
            uri$lang as uri, 
            date as date,
            img as img,
        ");
        $this->db->where('isShown', 1);
        $this->db->where('date <',$this->nextDay);
        $this->db->order_by('date DESC, id DESC');
        return $this->db->get($this->tblname)->result();
    }

    public function get_articles_home($lang = false)
    {
        if (empty($lang)) {
            return false;
        }

        $this->db->select("
            id as id,
            title$lang as title, 
            uri$lang as uri, 
            date as date,
            img as img,
        ");
        $this->db->where('isShown', 1);
        $this->db->where('date <',$this->nextDay);
        $this->db->offset(0);
        $this->db->limit(4);
        $this->db->order_by('date DESC, id DESC');
        return $this->db->get($this->tblname)->result();
    }

    public function get_all_articles()
    {
        $this->db->select("id");
        $this->db->where('isShown', 1);
        $this->db->where('date <',$this->nextDay);
        return $this->db->get($this->tblname)->result();
    }

    public function get_pag_articles($lang, $offset, $limit, $sort = '')
    {
        if (empty($lang)) {
            return false;
        }
        $this->db->select("
            id as id,
            title$lang as title, 
            uri$lang as uri, 
            desc$lang as desc,
            date as date,
            img as img,
        ");
        $this->db->where('isShown', 1);
        $this->db->where('date <',$this->nextDay);
        $this->db->offset($offset);
        $this->db->limit($limit);

        $this->db->order_by('date DESC, id DESC');

        $articles = $this->db->get($this->tblname)->result();

        return $articles;
    }

    public function get_articles_more($lang = false)
    {
        if (empty($lang)) {
            return false;
        }

        $this->db->select("
            id as id,
            title$lang as title, 
            uri$lang as uri, 
            desc$lang as desc,
            date as date,
            img as img,
        ");
        $this->db->where('isShown', 1);
        $this->db->where('date <',$this->nextDay);
        $this->db->offset(0);
        $this->db->limit(2);
        $this->db->order_by('date DESC, id DESC');
        return $this->db->get($this->tblname)->result();
    }

    public function get_article_data($lang = false, $uri = false)
    {
        if (empty($uri) || empty($lang)) {
            return false;
        }

        $this->db->select("
            id as id,
            date as date,
            title$lang as title,
            desc$lang as desc,
            text$lang as text,
            textL$lang as textL,
            seoTitle$lang as seo_title,
            seoKeywords$lang as seo_keywords,
            seoDesc$lang as seo_desc,
            uri$lang as uri,
            uriRO as uriRO,
            uriRU as uriRU,
            uriEN as uriEN,
            isShown as isShown,
            sorder as sorder, 
            img as img
        ");
        $this->db->where("uri$lang", $uri);
        $this->db->where('isShown', 1);
        $this->db->where('date <',$this->nextDay);
        return $this->db->get($this->tblname)->row();
    }
}
