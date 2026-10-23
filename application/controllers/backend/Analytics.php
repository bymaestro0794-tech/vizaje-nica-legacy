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
        $orderSource = $this->input->get('order_source', true);

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
            $rangeStart = new DateTime($range['date_from']);
            $rangeEnd = new DateTime($range['date_to']);
            $rangeDays = (int) $rangeStart->diff($rangeEnd)->days;
            $groupBy = $rangeDays <= 90 ? 'day' : 'month';
        }

        $orderSource = in_array($orderSource, array('web', 'app'), true) ? $orderSource : '';

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
        $this->data['order_source'] = $orderSource;
        $previousRange = $this->previous_range($range);
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
                'order_source' => $orderSource,
            )
        );
        $this->data['previous_analytics'] = array('summary' => $this->analytics_model->get_summary_for_range(
            $previousRange['from_datetime'],
            $previousRange['to_datetime'],
            array(
                'brand_id' => $brandId,
                'order_source' => $orderSource,
            )
        ));

        $this->data['inner_view'] = $this->index_view;
        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }

    public function funnel()
    {
        redirect('/' . ADM_CONTROLLER . '/analytics', 'location', 302);
    }

    public function site()
    {
        $range = $this->analytics_model->normalize_date_range(
            $this->input->get('date_from', true),
            $this->input->get('date_to', true)
        );

        $groupBy = $this->input->get('group_by', true);
        if (!in_array($groupBy, array('day', 'month'), true)) {
            $rangeStart = new DateTime($range['date_from']);
            $rangeEnd = new DateTime($range['date_to']);
            $rangeDays = (int) $rangeStart->diff($rangeEnd)->days;
            $groupBy = $rangeDays <= 90 ? 'day' : 'month';
        }

        $trafficSource = $this->input->get('traffic_source', true);
        $trafficSource = preg_match('/^[a-z0-9_.-]{1,80}$/i', (string) $trafficSource)
            ? strtolower((string) $trafficSource)
            : null;

        $topPagesPage = $this->input->get('top_pages_page', true);
        $landingPagesPage = $this->input->get('landing_pages_page', true);
        $topPagesPage = ctype_digit((string) $topPagesPage) && (int) $topPagesPage > 0
            ? (int) $topPagesPage
            : 1;
        $landingPagesPage = ctype_digit((string) $landingPagesPage) && (int) $landingPagesPage > 0
            ? (int) $landingPagesPage
            : 1;

        $this->data['title'] = 'Аналитика сайта';
        $this->data['date_from'] = $range['date_from'];
        $this->data['date_to'] = $range['date_to'];
        $this->data['group_by'] = $groupBy;
        $this->data['traffic_source'] = $trafficSource;
        $previousRange = $this->previous_range($range);
        $this->data['top_pages_page'] = $topPagesPage;
        $this->data['landing_pages_page'] = $landingPagesPage;
        $this->data['site_analytics'] = $this->site_analytics_model->get_dashboard(
            $range['from_datetime'],
            $range['to_datetime'],
            $groupBy,
            $trafficSource,
            array(
                'top_pages_page' => $topPagesPage,
                'landing_pages_page' => $landingPagesPage,
            )
        );
        $this->data['previous_site_analytics'] = $trafficSource === null
            ? array('summary' => $this->site_analytics_model->get_summary_for_range(
                $previousRange['from_datetime'],
                $previousRange['to_datetime']
            ))
            : array('summary' => array());
        $this->data['inner_view'] = $this->folder . 'site';
        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }

    private function previous_range(array $range)
    {
        $from = new DateTime($range['date_from']);
        $to = new DateTime($range['date_to']);
        $days = (int) $from->diff($to)->days + 1;
        $previousTo = clone $from;
        $previousTo->modify('-1 day');
        $previousFrom = clone $previousTo;
        $previousFrom->modify('-' . ($days - 1) . ' days');
        $previousToExclusive = clone $previousTo;
        $previousToExclusive->modify('+1 day');

        return array(
            'date_from' => $previousFrom->format('Y-m-d'),
            'date_to' => $previousTo->format('Y-m-d'),
            'from_datetime' => $previousFrom->format('Y-m-d 00:00:00'),
            'to_datetime' => $previousToExclusive->format('Y-m-d 00:00:00'),
        );
    }
}
