<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Site_analytics_model extends CI_Model
{
    public function insert_event(array $event)
    {
        return $this->db->insert('analytics_events', $event);
    }

    public function get_dashboard($fromDateTime, $toDateTime, $groupBy = 'month', $trafficSource = null)
    {
        $groupBy = $groupBy === 'day' ? 'day' : 'month';

        return array(
            'summary' => $this->get_summary($fromDateTime, $toDateTime, $trafficSource),
            'checkout' => $this->get_checkout($fromDateTime, $toDateTime, $trafficSource),
            'funnel' => $this->get_funnel($fromDateTime, $toDateTime, $trafficSource),
            'sources' => $this->get_sources($fromDateTime, $toDateTime, $trafficSource),
            'devices' => $this->get_devices($fromDateTime, $toDateTime, $trafficSource),
            'top_pages' => $this->get_top_pages($fromDateTime, $toDateTime, $trafficSource),
            'landing_pages' => $this->get_landing_pages($fromDateTime, $toDateTime, $trafficSource),
            'periods' => $this->get_periods($fromDateTime, $toDateTime, $groupBy, $trafficSource),
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

    private function get_top_pages($fromDateTime, $toDateTime, $trafficSource = null)
    {
        $sql = "
            SELECT
                COALESCE(NULLIF(page_path, ''), '/') AS page_path,
                COUNT(*) AS page_events,
                COUNT(DISTINCT visitor_key) AS visitors,
                COUNT(DISTINCT session_key) AS sessions,
                SUM(event_name = 'view_item') AS product_views,
                SUM(event_name = 'add_to_cart') AS add_to_cart,
                SUM(event_name = 'begin_checkout') AS checkouts
            FROM analytics_events
            WHERE created_at >= ? AND created_at < ?
        ";

        $params = array($fromDateTime, $toDateTime);
        $sql .= $this->source_condition($trafficSource, $params);
        $sql .= " GROUP BY page_path
            ORDER BY sessions DESC, page_events DESC, page_path ASC
            LIMIT 50";

        return $this->db->query($sql, $params)->result_array();
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
        $sql .= " GROUP BY device_type
            ORDER BY sessions DESC, purchases DESC, device_type ASC";

        return $this->db->query($sql, $params)->result_array();
    }

    private function get_landing_pages($fromDateTime, $toDateTime, $trafficSource = null)
    {
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

        $params = array($fromDateTime, $toDateTime, $fromDateTime, $toDateTime);
        $sql .= $this->source_condition($trafficSource, $params, 'first.traffic_source');
        $sql .= " GROUP BY landing_page
            ORDER BY sessions DESC, purchases DESC, landing_page ASC
            LIMIT 50";

        return $this->db->query($sql, $params)->result_array();
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
}
