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
        $this->db->where('isBlog', 0);
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
        $this->db->where('isBlog', 0);
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
        $this->db->where('isBlog', 0);
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
        $this->db->where('isBlog', 0);
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
        $this->db->where('isBlog', 0);
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
        $this->db->where('isBlog', 0);
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
        $this->db->where('isBlog', 0);
        $this->db->where('date <',$this->nextDay);
        return $this->db->get($this->tblname)->row();
    }

    public function get_blog_articles($lang, $offset = 0, $limit = 20)
    {
        if (empty($lang)) {
            return array();
        }

        return $this->db
            ->select("id, title$lang AS title, uri$lang AS uri, desc$lang AS `desc`, date, img")
            ->where('isShown', 1)
            ->where('isBlog', 1)
            ->where('date <', $this->nextDay)
            ->order_by('date DESC, id DESC')
            ->limit((int) $limit, (int) $offset)
            ->get($this->tblname)
            ->result();
    }

    public function get_blog_article_count()
    {
        return (int) $this->db
            ->where('isShown', 1)
            ->where('isBlog', 1)
            ->where('date <', $this->nextDay)
            ->count_all_results($this->tblname);
    }

    public function get_blog_article_data($lang, $uri)
    {
        if (empty($lang) || empty($uri)) {
            return false;
        }

        return $this->db
            ->select("id, date, title$lang AS title, desc$lang AS `desc`, text$lang AS text,
                seoTitle$lang AS seo_title, seoKeywords$lang AS seo_keywords,
                seoDesc$lang AS seo_desc, tags$lang AS tags,
                uri$lang AS uri, uriRO, uriRU, isShown, isBlog, sorder, img")
            ->where("uri$lang", $uri)
            ->where('isShown', 1)
            ->where('isBlog', 1)
            ->where('date <', $this->nextDay)
            ->get($this->tblname)
            ->row();
    }

    public function get_blog_related_articles($lang, $articleId, $limit = 4)
    {
        if (empty($lang) || (int) $articleId <= 0) {
            return array();
        }

        return $this->db
            ->select("id, title$lang AS title, uri$lang AS uri, desc$lang AS `desc`, date, img")
            ->where('isShown', 1)
            ->where('isBlog', 1)
            ->where('id !=', (int) $articleId)
            ->where('date <', $this->nextDay)
            ->order_by('date DESC, id DESC')
            ->limit(max(1, min(8, (int) $limit)))
            ->get($this->tblname)
            ->result();
    }

    public function get_blog_like_count($articleId)
    {
        return (int) $this->db
            ->where('article_id', (int) $articleId)
            ->count_all_results('article_likes');
    }

    public function has_blog_like($articleId, $visitorKey)
    {
        return $this->db
            ->where('article_id', (int) $articleId)
            ->where('visitor_key', $visitorKey)
            ->count_all_results('article_likes') > 0;
    }

    public function toggle_blog_like($articleId, $visitorKey)
    {
        $query = $this->db
            ->where('article_id', (int) $articleId)
            ->where('visitor_key', $visitorKey)
            ->get('article_likes');

        if ($query->num_rows() > 0) {
            $this->db
                ->where('article_id', (int) $articleId)
                ->where('visitor_key', $visitorKey)
                ->delete('article_likes');
            $liked = false;
        } else {
            $this->db->insert('article_likes', array(
                'article_id' => (int) $articleId,
                'visitor_key' => $visitorKey,
                'created_at' => date('Y-m-d H:i:s'),
            ));
            $liked = $this->db->affected_rows() > 0;
        }

        return array(
            'liked' => $liked,
            'count' => $this->get_blog_like_count($articleId),
        );
    }
}
