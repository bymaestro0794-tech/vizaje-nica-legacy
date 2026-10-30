<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Site_analytics_model extends CI_Model
{
    /** Store only a daily phrase aggregate; no visitor, session, IP, or account key is retained. */
    public function record_search_activity($query, $activity = 'search', $resultCount = null)
    {
        $activityColumns = array(
            'search' => 'search_count',
            'zero_result' => 'zero_result_count',
            'result_click' => 'result_click_count',
            'add_to_cart' => 'add_to_cart_count',
            'result_count' => 'result_count_total',
        );
        if (!isset($activityColumns[$activity])) {
            return false;
        }

        if (!$this->db->table_exists('search_query_stats')) {
            return false;
        }

        $query = strip_tags((string) $query);
        $query = html_entity_decode($query, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $query = trim(preg_replace('/\s+/u', ' ', $query));
        $queryLength = function_exists('mb_strlen') ? mb_strlen($query, 'UTF-8') : strlen($query);
        if ($queryLength < 2 || $queryLength > 80
            || preg_match('/\p{L}/u', $query) !== 1
            || preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', $query)
            || preg_match('/(?<!\d)\+?\d(?:[\s().-]*\d){8,}(?!\d)/u', $query)
        ) {
            return false;
        }

        $query = function_exists('mb_strtolower')
            ? mb_strtolower($query, 'UTF-8')
            : strtolower($query);

        $hasZeroResultCount = $this->db->field_exists('zero_result_count', 'search_query_stats');
        $hasResultClickCount = $this->db->field_exists('result_click_count', 'search_query_stats');
        $hasResultCountTotal = $this->db->field_exists('result_count_total', 'search_query_stats');
        $hasResultCountSamples = $this->db->field_exists('result_count_samples', 'search_query_stats');
        $hasAddToCartCount = $this->db->field_exists('add_to_cart_count', 'search_query_stats');
        $hasBaseActivityColumns = $hasZeroResultCount && $hasResultClickCount;
        $hasConversionColumns = $hasResultCountTotal && $hasResultCountSamples && $hasAddToCartCount;

        if (!$hasBaseActivityColumns) {
            // Keep basic opted-in query counting working until the activity patch is applied.
            if ($activity !== 'search') {
                return false;
            }

            $this->db->query(
                'INSERT INTO search_query_stats (query_date, query_text, search_count, updated_at)
                 VALUES (CURDATE(), ?, 1, NOW())
                 ON DUPLICATE KEY UPDATE
                    search_count = CASE WHEN search_count < 4294967295 THEN search_count + 1 ELSE search_count END,
                    updated_at = NOW()',
                array($query)
            );
        } elseif (!$hasConversionColumns) {
            // Result and click metrics work with the first stats extension; conversion
            // metrics wait for the follow-up columns instead of disabling earlier metrics.
            if (in_array($activity, array('result_count', 'add_to_cart'), true)) {
                return false;
            }

            $increments = array(
                $activity === 'search' ? 1 : 0,
                $activity === 'zero_result' ? 1 : 0,
                $activity === 'result_click' ? 1 : 0,
            );
            $this->db->query(
                'INSERT INTO search_query_stats
                    (query_date, query_text, search_count, zero_result_count, result_click_count, updated_at)
                 VALUES (CURDATE(), ?, ?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE
                    search_count = CASE WHEN search_count < 4294967295 THEN search_count + VALUES(search_count) ELSE search_count END,
                    zero_result_count = CASE WHEN zero_result_count < 4294967295 THEN zero_result_count + VALUES(zero_result_count) ELSE zero_result_count END,
                    result_click_count = CASE WHEN result_click_count < 4294967295 THEN result_click_count + VALUES(result_click_count) ELSE result_click_count END,
                    updated_at = NOW()',
                array_merge(array($query), $increments)
            );
        } else {
            $resultCount = is_numeric($resultCount)
                ? max(0, min(10000000, (int) $resultCount))
                : 0;
            $hasResultCountSample = $activity === 'result_count';
            $increments = array(
                'search_count' => $activity === 'search' ? 1 : 0,
                'zero_result_count' => $activity === 'zero_result' ? 1 : 0,
                'result_click_count' => $activity === 'result_click' ? 1 : 0,
                'result_count_total' => $activity === 'result_count' ? $resultCount : 0,
                'result_count_samples' => $hasResultCountSample ? 1 : 0,
                'add_to_cart_count' => $activity === 'add_to_cart' ? 1 : 0,
            );
            $this->db->query(
                'INSERT INTO search_query_stats
                    (query_date, query_text, search_count, zero_result_count, result_click_count, result_count_total, result_count_samples, add_to_cart_count, updated_at)
                 VALUES (CURDATE(), ?, ?, ?, ?, ?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE
                    search_count = CASE WHEN search_count < 4294967295 THEN search_count + VALUES(search_count) ELSE search_count END,
                    zero_result_count = CASE WHEN zero_result_count < 4294967295 THEN zero_result_count + VALUES(zero_result_count) ELSE zero_result_count END,
                    result_click_count = CASE WHEN result_click_count < 4294967295 THEN result_click_count + VALUES(result_click_count) ELSE result_click_count END,
                    result_count_total = result_count_total + VALUES(result_count_total),
                    result_count_samples = CASE WHEN result_count_samples < 4294967295 THEN result_count_samples + VALUES(result_count_samples) ELSE result_count_samples END,
                    add_to_cart_count = CASE WHEN add_to_cart_count < 4294967295 THEN add_to_cart_count + VALUES(add_to_cart_count) ELSE add_to_cart_count END,
                    updated_at = NOW()',
                array(
                    $query,
                    $increments['search_count'],
                    $increments['zero_result_count'],
                    $increments['result_click_count'],
                    $increments['result_count_total'],
                    $increments['result_count_samples'],
                    $increments['add_to_cart_count'],
                )
            );
        }

        // Keep the aggregate table bounded without running a cleanup on every request.
        if (mt_rand(1, 100) === 1) {
            $this->db->query(
                'DELETE FROM search_query_stats WHERE query_date < DATE_SUB(CURDATE(), INTERVAL 180 DAY)'
            );
        }

        return true;
    }

    public function record_search_query($query)
    {
        return $this->record_search_activity($query, 'search');
    }

    public function get_search_query_stats($days = 30, $limit = 50)
    {
        $days = max(1, min(180, (int) $days));
        $limit = max(1, min(200, (int) $limit));
        if (!$this->db->table_exists('search_query_stats')) {
            return array();
        }

        $hasZeroResultCount = $this->db->field_exists('zero_result_count', 'search_query_stats');
        $hasResultClickCount = $this->db->field_exists('result_click_count', 'search_query_stats');
        if (!$hasZeroResultCount || !$hasResultClickCount) return array();

        $resultCountSelect = $this->db->field_exists('result_count_total', 'search_query_stats')
            && $this->db->field_exists('result_count_samples', 'search_query_stats')
            ? 'SUM(result_count_total) / NULLIF(SUM(result_count_samples), 0) AS average_result_count'
            : '0 AS average_result_count';
        $addToCartSelect = $this->db->field_exists('add_to_cart_count', 'search_query_stats')
            ? 'SUM(add_to_cart_count) AS search_add_to_carts'
            : '0 AS search_add_to_carts';

        return $this->db->query(
            "SELECT query_text,
                    SUM(search_count) AS searches,
                    SUM(zero_result_count) AS zero_results,
                    SUM(result_click_count) AS result_clicks,
                    {$resultCountSelect},
                    {$addToCartSelect}
             FROM search_query_stats
             WHERE query_date >= DATE_SUB(CURDATE(), INTERVAL " . ($days - 1) . " DAY)
             GROUP BY query_text
             ORDER BY zero_results DESC, searches DESC, result_clicks DESC, query_text ASC
             LIMIT {$limit}"
        )->result_array();
    }

    public function get_popular_search_queries($limit = 5, $days = 30)
    {
        $limit = max(1, min(10, (int) $limit));
        $days = max(1, min(180, (int) $days));
        if (!$this->db->table_exists('search_query_stats')) {
            return array();
        }

        $fromDate = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
        $rows = $this->db->query(
            "SELECT query_text, SUM(search_count) AS total_searches, MAX(updated_at) AS last_used
             FROM search_query_stats
             WHERE query_date >= ?
             GROUP BY query_text
             HAVING SUM(search_count) >= 2
             ORDER BY total_searches DESC, last_used DESC
             LIMIT {$limit}",
            array($fromDate)
        )->result();

        return array_values(array_map(static function ($row) {
            return (string) $row->query_text;
        }, $rows));
    }

    public function get_matching_popular_search_queries($query, $limit = 5, $days = 30)
    {
        $limit = max(1, min(10, (int) $limit));
        $days = max(1, min(180, (int) $days));
        if (!$this->db->table_exists('search_query_stats')) {
            return array();
        }

        $query = strip_tags((string) $query);
        $query = html_entity_decode($query, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $query = trim(preg_replace('/\\s+/u', ' ', $query));
        $query = function_exists('mb_strtolower')
            ? mb_strtolower($query, 'UTF-8')
            : strtolower($query);
        $queryLength = function_exists('mb_strlen') ? mb_strlen($query, 'UTF-8') : strlen($query);
        if ($queryLength < 2 || $queryLength > 80) {
            return array();
        }

        $fromDate = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
        $escapedPrefix = $this->db->escape_like_str($query) . '%';
        $sql = "SELECT query_text, SUM(search_count) AS total_searches, MAX(updated_at) AS last_used
                FROM search_query_stats
                WHERE query_date >= ? AND query_text LIKE ? ESCAPE '!'
                GROUP BY query_text
                HAVING SUM(search_count) >= 2
                ORDER BY total_searches DESC, last_used DESC
                LIMIT {$limit}";
        $rows = $this->db->query($sql, array($fromDate, $escapedPrefix))->result();

        return array_values(array_map(static function ($row) {
            return (string) $row->query_text;
        }, $rows));
    }

    public function insert_event(array $event)
    {
        if (isset($event['event_name'], $event['order_id']) && $event['event_name'] === 'purchase' && $event['order_id']) {
            $existingPurchase = $this->db
                ->select('id')
                ->from('analytics_events')
                ->where('event_name', 'purchase')
                ->where('order_id', $event['order_id'])
                ->limit(1)
                ->get()
                ->row_array();

            if ($existingPurchase) {
                return true;
            }
        }

        return $this->db->insert('analytics_events', $event);
    }

    public function get_dashboard($fromDateTime, $toDateTime, $groupBy = 'month', $trafficSource = null, $options = array())
    {
        $groupBy = $groupBy === 'day' ? 'day' : 'month';
        $options = is_array($options) ? $options : array();
        $pageSize = 15;
        $topPagesPage = 1;
        $landingPagesPage = $this->normalise_page(isset($options['landing_pages_page']) ? $options['landing_pages_page'] : 1);

        $topPages = $trafficSource !== null
            ? $this->get_top_pages($fromDateTime, $toDateTime, $trafficSource, $topPagesPage, $pageSize)
            : array(
                'rows' => array(),
                'pagination' => $this->pagination_meta($topPagesPage, $pageSize, 0),
            );

        return array(
            'summary' => $trafficSource === null ? $this->get_summary($fromDateTime, $toDateTime) : array(),
            'periods' => $trafficSource === null ? $this->get_periods($fromDateTime, $toDateTime, $groupBy) : array(),
            'sources' => $trafficSource === null ? $this->get_sources($fromDateTime, $toDateTime) : array(),
            'devices' => $trafficSource === null ? $this->get_devices($fromDateTime, $toDateTime) : array(),
            'top_pages' => $topPages['rows'],
            'top_pages_pagination' => $topPages['pagination'],
        );
    }

    public function get_summary_for_range($fromDateTime, $toDateTime, $trafficSource = null)
    {
        return $this->get_summary($fromDateTime, $toDateTime, $trafficSource);
    }

    private function normalise_page($page)
    {
        return ctype_digit((string) $page) && (int) $page > 0 ? (int) $page : 1;
    }

    private function pagination_meta($page, $pageSize, $total)
    {
        $total = (int) $total;
        $pageSize = max(1, (int) $pageSize);
        $totalPages = $total > 0 ? (int) ceil($total / $pageSize) : 1;

        return array(
            'page' => min(max(1, (int) $page), $totalPages),
            'per_page' => $pageSize,
            'total' => $total,
            'total_pages' => $totalPages,
        );
    }

    private function get_checkout($fromDateTime, $toDateTime, $trafficSource = null)
    {
        $params = array($fromDateTime, $toDateTime);
        $checkoutSource = $this->source_condition($trafficSource, $params);
        $params[] = $fromDateTime;
        $params[] = $toDateTime;
        $purchaseSource = $this->source_condition($trafficSource, $params);

        $sql = "
            SELECT
                COUNT(DISTINCT checkout.session_key) AS checkout_sessions,
                COUNT(DISTINCT purchase.session_key) AS purchase_sessions
            FROM (
                SELECT DISTINCT session_key
                FROM analytics_events
                WHERE event_name = 'begin_checkout'
                  AND created_at >= ? AND created_at < ?
                  {$checkoutSource}
            ) AS checkout
            LEFT JOIN (
                SELECT DISTINCT session_key
                FROM analytics_events
                WHERE event_name = 'purchase'
                  AND created_at >= ? AND created_at < ?
                  {$purchaseSource}
            ) AS purchase ON purchase.session_key = checkout.session_key
        ";

        $row = $this->db->query($sql, $params)->row_array();
        $checkoutSessions = (int) ($row['checkout_sessions'] ?? 0);
        $purchaseSessions = (int) ($row['purchase_sessions'] ?? 0);

        return array(
            'checkout_sessions' => $checkoutSessions,
            'purchase_sessions' => $purchaseSessions,
            'dropoffs' => max(0, $checkoutSessions - $purchaseSessions),
            'conversion_rate' => $checkoutSessions > 0
                ? round(($purchaseSessions / $checkoutSessions) * 100, 2)
                : 0,
        );
    }

    private function get_top_pages($fromDateTime, $toDateTime, $trafficSource = null, $page = 1, $pageSize = 20)
    {
        $whereSql = "
            FROM analytics_events
            WHERE created_at >= ? AND created_at < ?
        ";

        $params = array($fromDateTime, $toDateTime);
        $whereSql .= $this->source_condition($trafficSource, $params);

        $totalRow = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM (
                SELECT COALESCE(NULLIF(page_path, ''), '/') AS page_path
                {$whereSql}
                GROUP BY page_path
             ) AS page_groups",
            $params
        )->row_array();
        $total = (int) ($totalRow['total'] ?? 0);
        $pagination = $this->pagination_meta($page, $pageSize, $total);
        $offset = ($pagination['page'] - 1) * $pagination['per_page'];

        $sql = "
            SELECT
                COALESCE(NULLIF(page_path, ''), '/') AS page_path,
                COUNT(*) AS page_events,
                COUNT(DISTINCT visitor_key) AS visitors,
                COUNT(DISTINCT session_key) AS sessions,
                SUM(event_name = 'view_item') AS product_views,
                SUM(event_name = 'add_to_cart') AS add_to_cart,
                SUM(event_name = 'begin_checkout') AS checkouts
            {$whereSql}
        ";

        $sql .= " GROUP BY page_path
            ORDER BY sessions DESC, page_events DESC, page_path ASC
            LIMIT {$offset}, {$pagination['per_page']}";

        return array(
            'rows' => $this->db->query($sql, $params)->result_array(),
            'pagination' => $pagination,
        );
    }

    private function get_devices($fromDateTime, $toDateTime, $trafficSource = null)
    {
        $sql = "
            SELECT
                COALESCE(NULLIF(device_type, ''), 'unknown') AS device_type,
                COUNT(DISTINCT visitor_key) AS visitors,
                COUNT(DISTINCT session_key) AS sessions,
                SUM(event_name = 'view_item') AS product_views,
                COUNT(DISTINCT CASE WHEN event_name = 'purchase' THEN order_id END) AS purchases,
                COALESCE(SUM(CASE WHEN event_name = 'purchase' THEN event_value ELSE 0 END), 0) AS purchase_revenue
            FROM analytics_events
            WHERE created_at >= ? AND created_at < ?
        ";

        $params = array($fromDateTime, $toDateTime);
        $sql .= $this->source_condition($trafficSource, $params);
        $sql .= $this->purchase_dedupe_condition($fromDateTime, $toDateTime, $params);
        $sql .= " GROUP BY device_type
            ORDER BY sessions DESC, purchases DESC, device_type ASC";

        return $this->db->query($sql, $params)->result_array();
    }

    private function get_landing_pages($fromDateTime, $toDateTime, $trafficSource = null, $page = 1, $pageSize = 20)
    {
        $whereSql = "
            WHERE first.created_at >= ? AND first.created_at < ?
              AND NOT EXISTS (
                  SELECT 1
                  FROM analytics_events AS earlier
                  WHERE earlier.session_key = first.session_key
                    AND (
                        earlier.created_at < first.created_at
                        OR (earlier.created_at = first.created_at AND earlier.id < first.id)
                    )
              )
        ";

        $params = array($fromDateTime, $toDateTime);
        $whereSql .= $this->source_condition($trafficSource, $params, 'first.traffic_source');
        $purchaseParams = array($fromDateTime, $toDateTime);

        $totalRow = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM (
                SELECT COALESCE(NULLIF(first.page_path, ''), '/') AS landing_page
                FROM analytics_events AS first
                {$whereSql}
                GROUP BY landing_page
             ) AS landing_groups",
            $params
        )->row_array();
        $total = (int) ($totalRow['total'] ?? 0);
        $pagination = $this->pagination_meta($page, $pageSize, $total);
        $offset = ($pagination['page'] - 1) * $pagination['per_page'];

        $sql = "
            SELECT
                COALESCE(NULLIF(first.page_path, ''), '/') AS landing_page,
                COUNT(DISTINCT first.visitor_key) AS visitors,
                COUNT(DISTINCT first.session_key) AS sessions,
                COUNT(DISTINCT purchase.order_id) AS purchases,
                COALESCE(SUM(purchase.purchase_value), 0) AS purchase_revenue
            FROM analytics_events AS first
            LEFT JOIN (
                SELECT session_key, order_id, MAX(event_value) AS purchase_value
                FROM analytics_events
                WHERE event_name = 'purchase'
                  AND created_at >= ? AND created_at < ?
                GROUP BY session_key, order_id
            ) AS purchase ON purchase.session_key = first.session_key
            {$whereSql}
        ";

        $params = array_merge($purchaseParams, $params);
        $sql .= " GROUP BY landing_page
            ORDER BY sessions DESC, purchases DESC, landing_page ASC
            LIMIT {$offset}, {$pagination['per_page']}";

        return array(
            'rows' => $this->db->query($sql, $params)->result_array(),
            'pagination' => $pagination,
        );
    }

    private function get_sources($fromDateTime, $toDateTime, $trafficSource = null)
    {
        $sql = "
            SELECT
                COALESCE(NULLIF(traffic_source, ''), 'unknown') AS traffic_source,
                COALESCE(NULLIF(traffic_medium, ''), '') AS traffic_medium,
                COALESCE(NULLIF(traffic_campaign, ''), '') AS traffic_campaign,
                COUNT(DISTINCT visitor_key) AS visitors,
                COUNT(DISTINCT session_key) AS sessions,
                COUNT(DISTINCT CASE WHEN event_name = 'purchase' THEN order_id END) AS purchases,
                COALESCE(SUM(CASE WHEN event_name = 'purchase' THEN event_value ELSE 0 END), 0) AS purchase_revenue
            FROM analytics_events
            WHERE created_at >= ? AND created_at < ?
        ";

        $params = array($fromDateTime, $toDateTime);
        $sql .= $this->source_condition($trafficSource, $params);
        $sql .= $this->purchase_dedupe_condition($fromDateTime, $toDateTime, $params);
        $sql .= " GROUP BY traffic_source, traffic_medium, traffic_campaign
            ORDER BY purchases DESC, sessions DESC, visitors DESC, traffic_source ASC";

        return $this->db->query($sql, $params)->result_array();
    }

    private function get_summary($fromDateTime, $toDateTime, $trafficSource = null)
    {
        $sql = "
            SELECT
                COUNT(*) AS total_events,
                COUNT(DISTINCT visitor_key) AS visitors,
                COUNT(DISTINCT session_key) AS sessions,
                COUNT(DISTINCT CASE WHEN event_name = 'view_item' THEN session_key END) AS product_view_sessions,
                COUNT(DISTINCT CASE WHEN event_name = 'purchase' THEN order_id END) AS purchases,
                COALESCE(SUM(CASE WHEN event_name = 'purchase' THEN event_value ELSE 0 END), 0) AS purchase_revenue
            FROM analytics_events
            WHERE created_at >= ? AND created_at < ?
        ";

        $params = array($fromDateTime, $toDateTime);
        $sql .= $this->source_condition($trafficSource, $params);
        $sql .= $this->purchase_dedupe_condition($fromDateTime, $toDateTime, $params);
        $summary = $this->db->query($sql, $params)->row_array();
        $summary = is_array($summary) ? $summary : array();
        $sessions = (int) ($summary['sessions'] ?? 0);
        $purchases = (int) ($summary['purchases'] ?? 0);

        $summary['conversion_rate'] = $sessions > 0
            ? round(($purchases / $sessions) * 100, 2)
            : 0;

        return $summary;
    }

    private function get_funnel($fromDateTime, $toDateTime, $trafficSource = null)
    {
        $sql = "
            SELECT
                event_name,
                COUNT(*) AS event_count,
                COUNT(DISTINCT session_key) AS sessions
            FROM analytics_events
            WHERE created_at >= ?
              AND created_at < ?
            AND event_name IN (
                  'session_start', 'view_catalog', 'view_item',
                  'add_to_cart', 'view_cart', 'begin_checkout', 'purchase'
              )
        ";

        $params = array($fromDateTime, $toDateTime);
        $sql .= $this->source_condition($trafficSource, $params);
        $sql .= ' GROUP BY event_name';
        $rows = $this->db->query($sql, $params)->result_array();
        $result = array();

        foreach ($rows as $row) {
            $result[$row['event_name']] = $row;
        }

        return $result;
    }

    private function get_periods($fromDateTime, $toDateTime, $groupBy, $trafficSource = null)
    {
        $periodExpression = $groupBy === 'day'
            ? "DATE_FORMAT(created_at, '%Y-%m-%d')"
            : "DATE_FORMAT(created_at, '%Y-%m')";

        $sql = "
            SELECT
                {$periodExpression} AS period_key,
                COUNT(DISTINCT visitor_key) AS visitors,
                COUNT(DISTINCT session_key) AS sessions,
                SUM(event_name = 'view_item') AS product_views,
                SUM(event_name = 'add_to_cart') AS add_to_cart,
                SUM(event_name = 'begin_checkout') AS checkouts,
                COUNT(DISTINCT CASE WHEN event_name = 'purchase' THEN order_id END) AS purchases,
                COALESCE(SUM(CASE WHEN event_name = 'purchase' THEN event_value ELSE 0 END), 0) AS purchase_revenue
            FROM analytics_events
            WHERE created_at >= ? AND created_at < ?
        ";

        $params = array($fromDateTime, $toDateTime);
        $sql .= $this->source_condition($trafficSource, $params);
        $sql .= $this->purchase_dedupe_condition($fromDateTime, $toDateTime, $params);
        $sql .= " GROUP BY period_key
            ORDER BY period_key DESC";

        return $this->db->query($sql, $params)->result_array();
    }

    private function source_condition($trafficSource, array &$params, $column = 'traffic_source')
    {
        if ($trafficSource === null || $trafficSource === '') {
            return '';
        }

        if ($trafficSource === 'unknown') {
            return " AND ({$column} IS NULL OR {$column} = '')";
        }

        $params[] = $trafficSource;
        return " AND {$column} = ?";
    }

    private function purchase_dedupe_condition($fromDateTime, $toDateTime, array &$params)
    {
        $params[] = $fromDateTime;
        $params[] = $toDateTime;

        return " AND (event_name <> 'purchase' OR id = (
            SELECT MIN(duplicate_purchase.id)
            FROM analytics_events AS duplicate_purchase
            WHERE duplicate_purchase.event_name = 'purchase'
              AND duplicate_purchase.order_id = analytics_events.order_id
              AND duplicate_purchase.created_at >= ?
              AND duplicate_purchase.created_at < ?
        ))";
    }
}
