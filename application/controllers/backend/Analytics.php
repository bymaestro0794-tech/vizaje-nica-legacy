<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Analytics extends BackEndController
{
    public function __construct()
    {
        parent::__construct(__CLASS__);

        $this->data['title'] = 'Аналитика продаж';
        $this->load->model('analytics_model');
        $this->load->model('site_analytics_model');
    }

    public function index()
    {
        $range = $this->analytics_model->normalize_date_range(
            $this->input->get('date_from', true),
            $this->input->get('date_to', true)
        );

        $productSort = $this->input->get('product_sort', true);
        $brandSort = $this->input->get('brand_sort', true);
        $categorySort = $this->input->get('category_sort', true);
        $direction = $this->input->get('direction', true);
        $groupBy = $this->input->get('group_by', true);
        $brandId = $this->input->get('brand_id', true);

        $productSorts = array('quantity', 'revenue', 'orders', 'title');
        $brandSorts = array('quantity', 'revenue', 'orders', 'title');
        $categorySorts = array('quantity', 'revenue', 'orders', 'title');

        if (!in_array($productSort, $productSorts, true)) {
            $productSort = 'quantity';
        }

        if (!in_array($brandSort, $brandSorts, true)) {
            $brandSort = 'quantity';
        }

        if (!in_array($categorySort, $categorySorts, true)) {
            $categorySort = 'quantity';
        }

        if (!in_array($direction, array('asc', 'desc'), true)) {
            $direction = 'desc';
        }

        if (!in_array($groupBy, array('day', 'month'), true)) {
            $groupBy = 'month';
        }

        $brandId = ctype_digit((string) $brandId) && (int) $brandId > 0
            ? (int) $brandId
            : null;

        $this->data['date_from'] = $range['date_from'];
        $this->data['date_to'] = $range['date_to'];
        $this->data['product_sort'] = $productSort;
        $this->data['brand_sort'] = $brandSort;
        $this->data['category_sort'] = $categorySort;
        $this->data['direction'] = $direction;
        $this->data['group_by'] = $groupBy;
        $this->data['brand_id'] = $brandId;
        $this->data['brands'] = $this->analytics_model->get_brands();
        $this->data['analytics'] = $this->analytics_model->get_dashboard(
            $range['from_datetime'],
            $range['to_datetime'],
            array(
                'product_sort' => $productSort,
                'brand_sort' => $brandSort,
                'category_sort' => $categorySort,
                'direction' => $direction,
                'group_by' => $groupBy,
                'brand_id' => $brandId,
            )
        );

        $this->data['inner_view'] = $this->index_view;
        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }

    public function funnel()
    {
        $range = $this->analytics_model->normalize_date_range(
            $this->input->get('date_from', true),
            $this->input->get('date_to', true)
        );

        $groupBy = $this->input->get('group_by', true);
        $groupBy = in_array($groupBy, array('day', 'month'), true) ? $groupBy : 'month';

        $this->data['title'] = 'Воронка продаж';
        $this->data['date_from'] = $range['date_from'];
        $this->data['date_to'] = $range['date_to'];
        $this->data['group_by'] = $groupBy;
        $this->data['order_funnel'] = $this->analytics_model->get_order_funnel(
            $range['from_datetime'],
            $range['to_datetime'],
            $groupBy
        );
        $this->data['inner_view'] = $this->folder . 'funnel';
        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }

    public function site()
    {
        $range = $this->analytics_model->normalize_date_range(
            $this->input->get('date_from', true),
            $this->input->get('date_to', true)
        );

        $groupBy = $this->input->get('group_by', true);
        $groupBy = in_array($groupBy, array('day', 'month'), true) ? $groupBy : 'month';

        $trafficSource = $this->input->get('traffic_source', true);
        $allowedTrafficSources = array(
            'instagram', 'google', 'facebook', 'tiktok', 'youtube',
            'vk', 'yandex', 'bing', 'duckduckgo', 'direct', 'referral', 'unknown',
        );
        $trafficSource = in_array($trafficSource, $allowedTrafficSources, true)
            ? $trafficSource
            : null;

        $this->data['title'] = 'Аналитика сайта';
        $this->data['date_from'] = $range['date_from'];
        $this->data['date_to'] = $range['date_to'];
        $this->data['group_by'] = $groupBy;
        $this->data['traffic_source'] = $trafficSource;
        $this->data['site_analytics'] = $this->site_analytics_model->get_dashboard(
            $range['from_datetime'],
            $range['to_datetime'],
            $groupBy,
            $trafficSource
        );
        $this->data['inner_view'] = $this->folder . 'site';
        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }
}
