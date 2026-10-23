<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use JasonGrimes\Paginator;

class Articles extends FrontEndController
{
    private $page_id;
    private $per_page;

    public function __construct() {
        parent::__construct();
        $this->page_id = 37;
        $this->per_page = 20;
        $this->load->model('articles_model');
    }

    private function _init_seo_data($page) {
        $this->data['page_title'] = (!empty($page->seo_title)) ? $page->seo_title : "";
        $this->data['page_name'] = $page->title;
        $this->data['text_for_layout'] = $page->text;
        $this->data['keywords_for_layout'] = (!empty($page->seo_keywords)) ? $page->seo_keywords : "";
        $this->data['description_for_layout'] = (!empty($page->seo_desc)) ? $page->seo_desc : "";
        $this->data['otitle'] = $page->seo_title;
        $this->data['breadcrumbs'] = $this->breadcrumbs;
    }

    private function loadOGImgData($page, $dir = 'menu') {
        if (!empty($page->img)) {
            $this->data['og_img'] = newthumbs($page->img, $dir, 500, 300, 'og500x300x1', 1);
            $this->data['og_img_width'] = 500;
            $this->data['og_img_height'] = 300;
        } else {
            $this->data['og_img'] = '/public/i/no_image_214_51_og214x51x1.jpg';
            $this->data['og_img_width'] = 214;
            $this->data['og_img_height'] = 51;
        }
    }

    public function index() {
        $page = $this->menu_model->get_page_data_by_id($this->clang, $this->page_id);
        if (empty($page)) throw_on_404();

        $this->breadcrumbs[] = $this->_generate_bc_data($page->title, $page->uri);

        $articles = $this->articles_model->get_articles($this->clang);
        $this->data['articles'] = $articles;
        $this->data['articles_base_path'] = '/' . $this->lclang . '/' . $page->uri;

        $pagination_nr = @$_GET['page'];
        if (empty($pagination_nr) || $pagination_nr < 1) $pagination_nr = 1;
        $start = ($pagination_nr - 1) * $this->per_page;
        $articles_all = $this->articles_model->get_all_articles();
        $articles = $this->articles_model->get_pag_articles($this->clang, $start, $this->per_page);
        $count = count($articles_all);


        $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
        $string = '';

        $urlPattern = $uri_parts[0] . '?' . $string . 'page=(:num)';
        $paginator = new Paginator(
            $count,
            $this->per_page,
            $pagination_nr,
            $urlPattern
        );
        $paginator->setMaxPagesToShow(5);
        $this->data['page_url'] = '/' . $this->uri1 . '/' . $this->uri2 . '/' . $this->uri3;
        $this->data['paginator'] = $paginator;
        $this->data['articles'] = $articles;
        $this->data['products_count'] = $count;
        $this->data['show_from'] = $start;
        $this->data['show_to'] = $start + $this->per_page;

        $this->data['lang_urls'] = array(
            'ro' => '' . $page->uriRO,
            'ru' => '' . $page->uriRU,
        );

        $this->loadOGImgData($page);
        $this->_init_seo_data($page);

        $this->data['inner_view'] = 'pages/articles/index';
        $this->data['page'] = $page;

        $this->_render();
    }

    public function blog_index()
    {
        $page = $this->menu_model->get_page_data_by_id($this->clang, $this->page_id);
        if (empty($page)) throw_on_404();

        $pageNumber = (int) $this->input->get('page');
        $pageNumber = max(1, $pageNumber);
        $start = ($pageNumber - 1) * $this->per_page;
        $count = $this->articles_model->get_blog_article_count();
        $articles = $this->articles_model->get_blog_articles(
            $this->clang,
            $start,
            $this->per_page
        );

        $urlPattern = '/' . $this->lclang . '/blog?page=(:num)';
        $paginator = new Paginator($count, $this->per_page, $pageNumber, $urlPattern);
        $paginator->setMaxPagesToShow(5);

        $title = $this->lclang === 'ro' ? 'Blog' : 'Блог';
        $this->data['page_title'] = $title . ' | Vizaje-Nica';
        $this->data['page_name'] = $title;
        $this->data['text_for_layout'] = $title;
        $this->data['keywords_for_layout'] = '';
        $this->data['description_for_layout'] = $title . ' Vizaje-Nica';
        $this->data['otitle'] = $title . ' | Vizaje-Nica';
        $this->data['breadcrumbs'][] = $this->_generate_bc_data($title, '/' . $this->lclang . '/blog');
        $this->data['lang_urls'] = array('ro' => array('blog'), 'ru' => array('blog'));
        $this->data['articles'] = $articles;
        $this->data['articles_base_path'] = '/' . $this->lclang . '/blog';
        $this->data['paginator'] = $paginator;
        $this->data['products_count'] = $count;
        $this->data['show_from'] = $start;
        $this->data['show_to'] = min($start + $this->per_page, $count);
        $this->data['page'] = $page;
        $this->data['inner_view'] = 'pages/articles/index';
        $this->loadOGImgData($page);
        $this->_render();
    }

    public function blog_item()
    {
        $page = $this->menu_model->get_page_data_by_id($this->clang, $this->page_id);
        if (empty($page)) throw_on_404();

        $article = $this->articles_model->get_blog_article_data($this->clang, $this->uri3);
        if (empty($article)) throw_on_404();

        $this->load->model('products_model');
        $content = isset($article->text) ? (string) $article->text : '';
        preg_match_all('/\\[\\[ARTICLE_PRODUCT:(\\d+)\\]\\]/', $content, $matches);
        $productIds = array_slice(
            array_values(array_unique(array_map('intval', $matches[1]))),
            0,
            8
        );
        $productEmbeds = array();

        foreach ($productIds as $productId) {
            $sourceProduct = $this->products_model->get_product_by_id_for_quick_view(
                $this->clang,
                $productId
            );

            if (empty($sourceProduct)) {
                continue;
            }

            $isB2B = !empty($_SESSION['isb2b']);
            $stock = $isB2B
                ? (int) ($sourceProduct->effective_stock_wholesale ?? $sourceProduct->on_stockWH ?? 0)
                : (int) ($sourceProduct->effective_stock ?? $sourceProduct->on_stock ?? 0);
            $isAvailable = isset($sourceProduct->has_available_stock)
                ? (int) $sourceProduct->has_available_stock > 0
                : $stock > 0;
            $recommendations = array();
            $titleKeywords = array();
            if (preg_match('/кист|brush|pensul|perii/iu', (string) ($sourceProduct->cat_title ?? ''))
                && preg_match('/кист|brush|pensul/iu', (string) $sourceProduct->title)) {
                $titleKeywords = array('brush', 'кист', 'pensul');
            }

            if (!$isAvailable) {
                $recommendations = $this->products_model->get_similar_products(
                    $this->clang,
                    $sourceProduct,
                    3,
                    $titleKeywords
                );
            }

            if (!empty($sourceProduct->img)) {
                $productEmbeds[$productId] = $this->load->view(
                    'pages/articles/product_embed',
                    array(
                        'product' => $sourceProduct,
                        'is_unavailable' => !$isAvailable,
                        'similar_products' => !$isAvailable ? $recommendations : array(),
                        'lclang' => $this->lclang,
                        'product_category_uri' => $this->data['menu']['all'][3]->uri,
                        'menu' => $this->data['menu'],
                    ),
                    true
                );
            }
        }

        $article->text = preg_replace_callback(
            '/\\[\\[ARTICLE_PRODUCT:(\\d+)\\]\\]/',
            function ($match) use ($productEmbeds) {
                $id = (int) $match[1];
                return isset($productEmbeds[$id]) ? $productEmbeds[$id] : '';
            },
            $content
        );

        $this->breadcrumbs[] = $this->_generate_bc_data(
            $this->lclang === 'ro' ? 'Blog' : 'Блог',
            '/' . $this->lclang . '/blog'
        );
        $this->breadcrumbs[] = $this->_generate_bc_data($article->title);
        $this->data['article'] = $article;
        $this->data['page_name'] = $this->lclang === 'ro' ? 'Blog' : 'Блог';
        $this->data['article_likes'] = $this->articles_model->get_blog_like_count($article->id);
        $visitorKey = hash_hmac(
            'sha256',
            (string) session_id(),
            (string) $this->config->item('encryption_key')
        );
        $this->data['article_liked'] = $this->articles_model->has_blog_like($article->id, $visitorKey);
        $this->data['article_related'] = $this->articles_model->get_blog_related_articles(
            $this->clang,
            $article->id,
            4
        );
        $this->data['articles_base_path'] = '/' . $this->lclang . '/blog';
        $this->data['lang_urls'] = array(
            'ro' => array('blog', $article->uriRO),
            'ru' => array('blog', $article->uriRU),
        );
        $this->loadOGImgData($article, 'articles');
        $this->_init_seo_data($article);
        $this->data['page_title'] = !empty($article->seo_title)
            ? $article->seo_title
            : $article->title . ' | ' . ($this->lclang === 'ro' ? 'Blog' : 'Блог') . ' | Vizaje-Nica';
        $this->data['description_for_layout'] = !empty($article->seo_desc)
            ? $article->seo_desc
            : trim(preg_replace('/\\s+/u', ' ', strip_tags((string) $article->desc)));
        $this->data['keywords_for_layout'] = !empty($article->seo_keywords)
            ? $article->seo_keywords
            : (string) $article->tags;
        $this->data['otitle'] = $this->data['page_title'];
        $this->data['inner_view'] = 'pages/articles/blog_item';
        $this->data['page'] = $page;
        $this->_render();
    }

    public function blog_like($articleId = 0)
    {
        check_if_POST();
        $articleId = (int) $articleId;
        $exists = $this->db
            ->where('id', $articleId)
            ->where('isShown', 1)
            ->where('isBlog', 1)
            ->count_all_results('articles');

        if (!$exists) {
            show_404();
        }

        $visitorKey = hash_hmac(
            'sha256',
            (string) session_id(),
            (string) $this->config->item('encryption_key')
        );
        $result = $this->articles_model->toggle_blog_like($articleId, $visitorKey);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }

    public function items() {
        $page = $this->menu_model->get_page_data_by_id($this->clang, $this->page_id);
        if (empty($page)) throw_on_404();

        $article = $this->articles_model->get_article_data($this->clang, $this->uri3);
        if (empty($article)) throw_on_404();

        $this->breadcrumbs[] = $this->_generate_bc_data($page->title, $page->uri);

        $articles = $this->articles_model->get_articles_more($this->clang);
        $this->data['articles'] = $articles;

        $this->data['lang_urls'] = array(
            'ro' => '' . $page->uriRO . '/' . $article->uriRO,
            'ru' => '' . $page->uriRU . '/' . $article->uriRU,
        );

        $this->loadOGImgData($article);
        $this->_init_seo_data($article);

        $this->data['inner_view'] = 'pages/articles/items';
        $this->data['page'] = $page;
        $this->data['article'] = $article;

        $this->_render();
    }
}
