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
        $period = $this->normalize_period($this->input->get('period', true));
        $range = $this->period_range($period);

        $this->data['period'] = $period;
        $this->data['period_label'] = $this->period_label($period);
        $this->data['range_label'] = $range['date_from'] . ' — ' . $range['date_to'];
        $this->data['analytics_data_url'] = '/' . ADM_CONTROLLER . '/analytics/data?period=' . rawurlencode($period);

        $this->data['inner_view'] = $this->index_view;
        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }

    /** Load sales tables separately so the admin shell can render first. */
    public function data()
    {
        $period = $this->normalize_period($this->input->get('period', true));
        $range = $this->period_range($period);
        $from = new DateTime($range['date_from']);
        $to = new DateTime($range['date_to']);
        $groupBy = (int) $from->diff($to)->days <= 90 ? 'day' : 'month';

        $analytics = $this->analytics_model->get_popular_sales_dashboard(
            $range['from_datetime'],
            $range['to_datetime'],
            $groupBy
        );

        $html = $this->load->view('dashboard/analytics/_sales_data', array(
            'analytics' => $analytics,
            'group_by' => $groupBy,
        ), true);

        $this->output
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode(array(
                'ok' => true,
                'html' => $html,
                'range_label' => $range['date_from'] . ' — ' . $range['date_to'],
            ), JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE));
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

    private function normalize_period($period)
    {
        $allowed = array('current_month', 'previous_month', 'last_3_months', 'year');
        return in_array($period, $allowed, true) ? $period : 'current_month';
    }

    private function period_range($period)
    {
        $today = new DateTime('today');
        $from = clone $today;
        $to = clone $today;

        if ($period === 'previous_month') {
            $from->modify('first day of this month')->modify('-1 month');
            $to = clone $from;
            $to->modify('last day of this month');
        } elseif ($period === 'last_3_months') {
            $from->modify('first day of this month')->modify('-2 months');
        } elseif ($period === 'year') {
            $from->setDate((int) $today->format('Y'), 1, 1);
        } else {
            $from->modify('first day of this month');
        }

        $toExclusive = clone $to;
        $toExclusive->modify('+1 day');

        return array(
            'date_from' => $from->format('Y-m-d'),
            'date_to' => $to->format('Y-m-d'),
            'from_datetime' => $from->format('Y-m-d 00:00:00'),
            'to_datetime' => $toExclusive->format('Y-m-d 00:00:00'),
        );
    }

    private function period_label($period)
    {
        $labels = array(
            'current_month' => 'Этот месяц',
            'previous_month' => 'Предыдущий месяц',
            'last_3_months' => 'Последние 3 месяца',
            'year' => 'Год',
        );
        return isset($labels[$period]) ? $labels[$period] : $labels['current_month'];
    }
}
