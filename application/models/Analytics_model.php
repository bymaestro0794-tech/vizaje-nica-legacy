<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Read-only sales analytics for the legacy admin.
 *
 * Sales widgets use finished orders only. The order funnel additionally
 * reads the current status of all orders; behaviour analytics lives in a
 * separate first-party event store.
 */
class Analytics_model extends BaseModel
{
    /**
     * Normalises an inclusive YYYY-MM-DD range into a half-open DATETIME range.
     */
    public function normalize_date_range($dateFrom = null, $dateTo = null)
    {
        $from = DateTime::createFromFormat('!Y-m-d', (string) $dateFrom);
        $to = DateTime::createFromFormat('!Y-m-d', (string) $dateTo);

        if (!$from || $from->format('Y-m-d') !== (string) $dateFrom) {
            $from = new DateTime('today');
            $from->modify('-29 days');
        }

        if (!$to || $to->format('Y-m-d') !== (string) $dateTo) {
            $to = new DateTime('today');
        }

        if ($from > $to) {
            $swap = $from;
            $from = $to;
            $to = $swap;
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

    public function get_dashboard($fromDateTime, $toDateTime, $options = array())
    {
        $options = is_array($options) ? $options : array();

        return array(
            'summary' => $this->get_summary(
                $fromDateTime,
                $toDateTime,
                isset($options['brand_id']) ? $options['brand_id'] : null,
                isset($options['order_source']) ? $options['order_source'] : null
            ),
            'top_products' => $this->get_top_products(
                $fromDateTime,
                $toDateTime,
                isset($options['product_sort']) ? $options['product_sort'] : 'quantity',
                isset($options['direction']) ? $options['direction'] : 'desc',
                isset($options['brand_id']) ? $options['brand_id'] : null,
                isset($options['order_source']) ? $options['order_source'] : null
            ),
            'top_brands' => $this->get_top_brands(
                $fromDateTime,
                $toDateTime,
                isset($options['brand_sort']) ? $options['brand_sort'] : 'quantity',
                isset($options['direction']) ? $options['direction'] : 'desc',
                isset($options['brand_id']) ? $options['brand_id'] : null,
                isset($options['order_source']) ? $options['order_source'] : null
            ),
            'top_categories' => $this->get_top_categories(
                $fromDateTime,
                $toDateTime,
                isset($options['category_sort']) ? $options['category_sort'] : 'quantity',
                isset($options['direction']) ? $options['direction'] : 'desc',
                isset($options['brand_id']) ? $options['brand_id'] : null,
                isset($options['order_source']) ? $options['order_source'] : null
            ),
            'period_sales' => $this->get_period_sales(
                $fromDateTime,
                $toDateTime,
                isset($options['group_by']) ? $options['group_by'] : 'month',
                isset($options['brand_id']) ? $options['brand_id'] : null,
                isset($options['order_source']) ? $options['order_source'] : null
            ),
        );
    }

    /**
     * Sales-based suggestions for the frontend search landing state.
     * Uses the same completed-order statistics and default 30-day window as
     * the admin dashboard.
     */
    public function get_header_search_popular($limit = 5)
    {
        $limit = max(1, min(10, (int) $limit));
        $range = $this->normalize_date_range();

        return array(
            'categories' => array_slice(
                $this->get_top_categories(
                    $range['from_datetime'],
                    $range['to_datetime'],
                    'quantity',
                    'desc'
                ),
                0,
                $limit
            ),
            'brands' => array_slice(
                $this->get_top_brands(
                    $range['from_datetime'],
                    $range['to_datetime'],
                    'quantity',
                    'desc'
                ),
                0,
                $limit
            ),
        );
    }

    /**
     * Product IDs ranked by sold quantity for a brand in the last 30 days.
     */
    public function get_header_search_brand_product_ids($brandId, $limit = 8)
    {
        $brandId = (int) $brandId;
        if ($brandId <= 0) {
            return array();
        }

        $limit = max(1, min(50, (int) $limit));
        $range = $this->normalize_date_range();
        $products = $this->get_top_products(
            $range['from_datetime'],
            $range['to_datetime'],
            'quantity',
            'desc',
            $brandId,
            null,
            $limit,
            true
        );

        return array_values(array_filter(array_map(
            static function ($product) {
                return (int) ($product['product_id'] ?? 0);
            },
            array_slice($products, 0, $limit)
        )));
    }

    /**
     * Finds other best-selling brands in the strongest-selling categories of
     * the searched brand. This keeps the search suggestions genuinely related.
     */
    public function get_header_search_related_brand_ids($brandId, $limit = 5)
    {
        $brandId = (int) $brandId;
        if ($brandId <= 0) {
            return array();
        }

        $limit = max(1, min(10, (int) $limit));
        $range = $this->normalize_date_range();
        $lineTotal = $this->money_expression('op.total');
        $sql = "
            SELECT
                p.brand_id,
                SUM(COALESCE(op.qty, 0)) AS sold_quantity,
                SUM({$lineTotal}) AS product_revenue
            FROM orders AS o
            INNER JOIN orders_products AS op ON op.order_id = o.id
            INNER JOIN products AS p ON p.id = op.product_id
            INNER JOIN brands AS b ON b.id = p.brand_id AND b.isShown = 1
            INNER JOIN (
                SELECT
                    p1.category_id,
                    SUM(COALESCE(op1.qty, 0)) AS category_quantity
                FROM orders AS o1
                INNER JOIN orders_products AS op1 ON op1.order_id = o1.id
                INNER JOIN products AS p1 ON p1.id = op1.product_id
                WHERE o1.status = 'finished'
                  AND o1.added >= ?
                  AND o1.added < ?
                  AND p1.brand_id = ?
                  AND p1.isShown = 1
                  AND p1.category_id IS NOT NULL
                GROUP BY p1.category_id
                ORDER BY category_quantity DESC
                LIMIT 5
            ) AS related_categories ON related_categories.category_id = p.category_id
            WHERE o.status = 'finished'
              AND o.added >= ?
              AND o.added < ?
              AND p.isShown = 1
              AND p.brand_id <> ?
            GROUP BY p.brand_id
            ORDER BY sold_quantity DESC, product_revenue DESC, p.brand_id ASC
            LIMIT {$limit}
        ";

        $rows = $this->db->query($sql, array(
            $range['from_datetime'],
            $range['to_datetime'],
            $brandId,
            $range['from_datetime'],
            $range['to_datetime'],
            $brandId,
        ))->result_array();

        return array_values(array_filter(array_map(static function ($row) {
            return (int) ($row['brand_id'] ?? 0);
        }, $rows)));
    }

    public function get_summary_for_range($fromDateTime, $toDateTime, $options = array())
    {
        $options = is_array($options) ? $options : array();
        return $this->get_summary(
            $fromDateTime,
            $toDateTime,
            isset($options['brand_id']) ? $options['brand_id'] : null,
            isset($options['order_source']) ? $options['order_source'] : null
        );
    }

    /**
     * Returns an order-status funnel for orders created in the selected range.
     *
     * This is deliberately a current-status snapshot. The legacy schema does
     * not store an order-status history, so it must not be presented as a
     * historical transition funnel.
     */
    public function get_order_funnel($fromDateTime, $toDateTime, $groupBy = 'month', $orderSource = null)
    {
        $groupBy = $groupBy === 'day' ? 'day' : 'month';
        $sourceCondition = $this->order_source_condition($orderSource);

        $summaryRow = $this->db->query(
            "SELECT
                COUNT(*) AS total_orders,
                SUM(status = 'new') AS new_orders,
                SUM(status = 'progress') AS progress_orders,
                SUM(status = 'finished') AS finished_orders,
                SUM(status = 'canceled') AS canceled_orders
             FROM orders
             WHERE added >= ? AND added < ? {$sourceCondition}",
            array($fromDateTime, $toDateTime)
        )->row_array();

        $summary = array(
            'total_orders' => (int) (isset($summaryRow['total_orders']) ? $summaryRow['total_orders'] : 0),
            'new_orders' => (int) (isset($summaryRow['new_orders']) ? $summaryRow['new_orders'] : 0),
            'progress_orders' => (int) (isset($summaryRow['progress_orders']) ? $summaryRow['progress_orders'] : 0),
            'finished_orders' => (int) (isset($summaryRow['finished_orders']) ? $summaryRow['finished_orders'] : 0),
            'canceled_orders' => (int) (isset($summaryRow['canceled_orders']) ? $summaryRow['canceled_orders'] : 0),
        );

        $summary['finished_conversion'] = $summary['total_orders'] > 0
            ? ($summary['finished_orders'] / $summary['total_orders']) * 100
            : 0;
        $summary['cancellation_rate'] = $summary['total_orders'] > 0
            ? ($summary['canceled_orders'] / $summary['total_orders']) * 100
            : 0;

        $periodExpression = $groupBy === 'day'
            ? 'DATE(added)'
            : "DATE_FORMAT(added, '%Y-%m')";

        $periods = $this->db->query(
            "SELECT
                {$periodExpression} AS period_key,
                COUNT(*) AS total_orders,
                SUM(status = 'new') AS new_orders,
                SUM(status = 'progress') AS progress_orders,
                SUM(status = 'finished') AS finished_orders,
                SUM(status = 'canceled') AS canceled_orders
             FROM orders
             WHERE added >= ? AND added < ? {$sourceCondition}
             GROUP BY period_key
             ORDER BY period_key DESC",
            array($fromDateTime, $toDateTime)
        )->result_array();

        foreach ($periods as &$period) {
            $period['total_orders'] = (int) $period['total_orders'];
            $period['new_orders'] = (int) $period['new_orders'];
            $period['progress_orders'] = (int) $period['progress_orders'];
            $period['finished_orders'] = (int) $period['finished_orders'];
            $period['canceled_orders'] = (int) $period['canceled_orders'];
        }
        unset($period);

        return array(
            'summary' => $summary,
            'periods' => $periods,
        );
    }

    public function get_brands()
    {
        return $this->db
            ->select('id, title')
            ->from('brands')
            ->where('title IS NOT NULL', null, false)
            ->where('title !=', '')
            ->order_by('title', 'ASC')
            ->get()
            ->result_array();
    }

    private function normalize_direction($direction)
    {
        return strtolower((string) $direction) === 'asc' ? 'ASC' : 'DESC';
    }

    private function product_sort_sql($sort)
    {
        $sortMap = array(
            'quantity' => 'sold_quantity',
            'revenue' => 'product_revenue',
            'orders' => 'order_count',
            'title' => 'product_title',
        );

        return isset($sortMap[$sort]) ? $sortMap[$sort] : $sortMap['quantity'];
    }

    private function brand_sort_sql($sort)
    {
        $sortMap = array(
            'quantity' => 'sold_quantity',
            'revenue' => 'product_revenue',
            'orders' => 'order_count',
            'title' => 'brand_title',
        );

        return isset($sortMap[$sort]) ? $sortMap[$sort] : $sortMap['quantity'];
    }

    private function category_sort_sql($sort)
    {
        $sortMap = array(
            'quantity' => 'sold_quantity',
            'revenue' => 'product_revenue',
            'orders' => 'order_count',
            'title' => 'category_title',
        );

        return isset($sortMap[$sort]) ? $sortMap[$sort] : $sortMap['quantity'];
    }

    private function money_expression($column)
    {
        return "CAST(REPLACE(REPLACE(NULLIF({$column}, ''), ' ', ''), ',', '.') AS DECIMAL(12,2))";
    }

    private function get_summary($fromDateTime, $toDateTime, $brandId = null, $orderSource = null)
    {
        $orderTotal = $this->money_expression('o.total');
        $lineTotal = $this->money_expression('op.total');
        $sourceCondition = $this->order_source_condition($orderSource, 'o');

        if ($brandId !== null) {
            $sql = "
                SELECT
                    COUNT(*) AS finished_orders,
                    COALESCE(SUM(s.sold_quantity), 0) AS sold_quantity,
                    COALESCE(SUM(s.product_revenue), 0) AS product_revenue,
                    COALESCE(SUM(s.order_revenue), 0) AS order_revenue,
                    COALESCE(AVG(s.order_revenue), 0) AS average_order_value
                FROM (
                    SELECT
                        o.id,
                        COALESCE(SUM(op.qty), 0) AS sold_quantity,
                        COALESCE(SUM({$lineTotal}), 0) AS product_revenue,
                        {$orderTotal} AS order_revenue
                    FROM orders AS o
                    INNER JOIN orders_products AS op ON op.order_id = o.id
                    INNER JOIN products AS p ON p.id = op.product_id
                    WHERE o.status = 'finished'
                      AND o.added >= ?
                      AND o.added < ?
                      {$sourceCondition}
                      AND p.brand_id = ?
                    GROUP BY o.id, o.total
                ) AS s
            ";

            $row = $this->db->query($sql, array($fromDateTime, $toDateTime, $brandId))->row_array();

            return $row ?: array(
                'finished_orders' => 0,
                'sold_quantity' => 0,
                'product_revenue' => 0,
                'order_revenue' => 0,
                'average_order_value' => 0,
            );
        }

        $sql = "
            SELECT
                COUNT(*) AS finished_orders,
                COALESCE(SUM(s.sold_quantity), 0) AS sold_quantity,
                COALESCE(SUM(s.product_revenue), 0) AS product_revenue,
                COALESCE(SUM(s.order_revenue), 0) AS order_revenue,
                COALESCE(AVG(s.order_revenue), 0) AS average_order_value
            FROM (
                SELECT
                    o.id,
                    COALESCE(SUM(op.qty), 0) AS sold_quantity,
                    COALESCE(SUM({$lineTotal}), 0) AS product_revenue,
                    {$orderTotal} AS order_revenue
                FROM orders AS o
                LEFT JOIN orders_products AS op ON op.order_id = o.id
                WHERE o.status = 'finished'
                  AND o.added >= ?
                  AND o.added < ?
                  {$sourceCondition}
                GROUP BY o.id, o.total
            ) AS s
        ";

        $row = $this->db->query($sql, array($fromDateTime, $toDateTime))->row_array();

        return $row ?: array(
            'finished_orders' => 0,
            'sold_quantity' => 0,
            'product_revenue' => 0,
            'order_revenue' => 0,
            'average_order_value' => 0,
        );
    }

    private function get_top_products($fromDateTime, $toDateTime, $sort, $direction, $brandId = null, $orderSource = null, $limit = 10, $stockFirst = false)
    {
        $lineTotal = $this->money_expression('op.total');
        $sortSql = $this->product_sort_sql($sort);
        $directionSql = $this->normalize_direction($direction);
        $brandCondition = $this->order_source_condition($orderSource, 'o');
        $params = array($fromDateTime, $toDateTime);
        $limit = max(1, min(100, (int) $limit));

        $availableStockCondition = !empty($_SESSION['isb2b'])
            ? "p.on_stockWH > 0 OR EXISTS (
                SELECT 1
                FROM products_variable pv_available
                WHERE pv_available.product_id = p.id
                  AND pv_available.isShown = 1
                  AND pv_available.priceWH > 0
                  AND pv_available.qtyWH > 0
            )"
            : "p.on_stock > 0 OR EXISTS (
                SELECT 1
                FROM products_variable pv_available
                WHERE pv_available.product_id = p.id
                  AND pv_available.isShown = 1
                  AND pv_available.price > 0
                  AND pv_available.qty > 0
            )";
        $stockOrderSql = $stockFirst
            ? "CASE WHEN ({$availableStockCondition}) THEN 1 ELSE 0 END DESC, "
            : '';

        if ($brandId !== null) {
            $brandCondition = ' AND p.brand_id = ?';
            $params[] = $brandId;
        }

        $sql = "
            SELECT
                p.id AS product_id,
                COALESCE(NULLIF(p.titleRU, ''), NULLIF(p.titleRO, ''), CONCAT('#', p.id)) AS product_title,
                p.brand_id,
                COALESCE(NULLIF(b.title, ''), CONCAT('Brand #', p.brand_id)) AS brand_title,
                SUM(COALESCE(op.qty, 0)) AS sold_quantity,
                SUM({$lineTotal}) AS product_revenue,
                COUNT(DISTINCT o.id) AS order_count
            FROM orders AS o
            INNER JOIN orders_products AS op ON op.order_id = o.id
            INNER JOIN products AS p ON p.id = op.product_id
            LEFT JOIN brands AS b ON b.id = p.brand_id
            WHERE o.status = 'finished'
              AND o.added >= ?
              AND o.added < ?
              {$brandCondition}
            GROUP BY p.id, p.titleRU, p.titleRO, p.brand_id, b.title
            ORDER BY {$stockOrderSql}{$sortSql} {$directionSql}, product_revenue DESC, product_title ASC
            LIMIT {$limit}
        ";

        return $this->db->query($sql, $params)->result_array();
    }

    private function get_top_brands($fromDateTime, $toDateTime, $sort, $direction, $brandId = null, $orderSource = null)
    {
        $lineTotal = $this->money_expression('op.total');
        $sortSql = $this->brand_sort_sql($sort);
        $directionSql = $this->normalize_direction($direction);
        $brandCondition = $this->order_source_condition($orderSource, 'o');
        $params = array($fromDateTime, $toDateTime);

        if ($brandId !== null) {
            $brandCondition = ' AND p.brand_id = ?';
            $params[] = $brandId;
        }

        $sql = "
            SELECT
                p.brand_id,
                COALESCE(NULLIF(b.title, ''), CONCAT('Brand #', p.brand_id)) AS brand_title,
                SUM(COALESCE(op.qty, 0)) AS sold_quantity,
                SUM({$lineTotal}) AS product_revenue,
                COUNT(DISTINCT o.id) AS order_count,
                COUNT(DISTINCT p.id) AS distinct_products
            FROM orders AS o
            INNER JOIN orders_products AS op ON op.order_id = o.id
            INNER JOIN products AS p ON p.id = op.product_id
            LEFT JOIN brands AS b ON b.id = p.brand_id
            WHERE o.status = 'finished'
              AND o.added >= ?
              AND o.added < ?
              {$brandCondition}
            GROUP BY p.brand_id, b.title
            ORDER BY {$sortSql} {$directionSql}, product_revenue DESC, brand_title ASC
            LIMIT 10
        ";

        return $this->db->query($sql, $params)->result_array();
    }

    private function get_top_categories($fromDateTime, $toDateTime, $sort, $direction, $brandId = null, $orderSource = null)
    {
        $lineTotal = $this->money_expression('op.total');
        $sortSql = $this->category_sort_sql($sort);
        $directionSql = $this->normalize_direction($direction);
        $brandCondition = $this->order_source_condition($orderSource, 'o');
        $params = array($fromDateTime, $toDateTime);

        if ($brandId !== null) {
            $brandCondition = ' AND p.brand_id = ?';
            $params[] = $brandId;
        }

        $sql = "
            SELECT
                c.id AS category_id,
                COALESCE(NULLIF(c.titleRU, ''), NULLIF(c.titleRO, ''), CONCAT('#', c.id)) AS category_title,
                SUM(COALESCE(op.qty, 0)) AS sold_quantity,
                SUM({$lineTotal}) AS product_revenue,
                COUNT(DISTINCT o.id) AS order_count
            FROM orders AS o
            INNER JOIN orders_products AS op ON op.order_id = o.id
            INNER JOIN products AS p ON p.id = op.product_id
            INNER JOIN (
                SELECT product_id, category_id
                FROM product_categories
                UNION
                SELECT id AS product_id, category_id
                FROM products
                WHERE category_id IS NOT NULL
            ) AS pc ON pc.product_id = p.id
            INNER JOIN categories AS c ON c.id = pc.category_id
            WHERE o.status = 'finished'
              AND o.added >= ?
              AND o.added < ?
              {$brandCondition}
            GROUP BY c.id, c.titleRU, c.titleRO
            ORDER BY {$sortSql} {$directionSql}, product_revenue DESC, category_title ASC
            LIMIT 10
        ";

        return $this->db->query($sql, $params)->result_array();
    }

    private function get_period_sales($fromDateTime, $toDateTime, $groupBy, $brandId = null, $orderSource = null)
    {
        $lineTotal = $this->money_expression('op.total');
        $orderTotal = $this->money_expression('o.total');

        $groupBy = $groupBy === 'day' ? 'day' : 'month';
        $dateExpression = $groupBy === 'day'
            ? 'DATE(o.added)'
            : "DATE_FORMAT(o.added, '%Y-%m')";
        $orderBrandCondition = $this->order_source_condition($orderSource, 'o');
        $orderParams = array($fromDateTime, $toDateTime);
        $itemBrandJoin = '';
        $itemBrandCondition = $this->order_source_condition($orderSource, 'o');
        $itemParams = array($fromDateTime, $toDateTime);

        if ($brandId !== null) {
            $orderBrandCondition .= "
                  AND EXISTS (
                      SELECT 1
                      FROM orders_products AS op_filter
                      INNER JOIN products AS p_filter ON p_filter.id = op_filter.product_id
                      WHERE op_filter.order_id = o.id
                        AND p_filter.brand_id = ?
                  )";
            $orderParams[] = $brandId;
            $itemBrandJoin = ' INNER JOIN products AS p ON p.id = op.product_id';
            $itemBrandCondition .= ' AND p.brand_id = ?';
            $itemParams[] = $brandId;
        }

        $sql = "
            SELECT
                d.period_key,
                d.finished_orders,
                COALESCE(i.sold_quantity, 0) AS sold_quantity,
                COALESCE(i.product_revenue, 0) AS product_revenue,
                d.order_revenue
            FROM (
                SELECT
                    {$dateExpression} AS period_key,
                    COUNT(*) AS finished_orders,
                    SUM({$orderTotal}) AS order_revenue
                FROM orders AS o
                WHERE o.status = 'finished'
                  AND o.added >= ?
                  AND o.added < ?
                  {$orderBrandCondition}
                GROUP BY {$dateExpression}
            ) AS d
            LEFT JOIN (
                SELECT
                    {$dateExpression} AS period_key,
                    SUM(COALESCE(op.qty, 0)) AS sold_quantity,
                    SUM({$lineTotal}) AS product_revenue
                FROM orders AS o
                INNER JOIN orders_products AS op ON op.order_id = o.id
                {$itemBrandJoin}
                WHERE o.status = 'finished'
                  AND o.added >= ?
                  AND o.added < ?
                  {$itemBrandCondition}
                GROUP BY {$dateExpression}
            ) AS i ON i.period_key = d.period_key
            ORDER BY d.period_key ASC
        ";

        return $this->db->query(
            $sql,
            array_merge($orderParams, $itemParams)
        )->result_array();
    }

    private function order_source_condition($orderSource, $alias = '')
    {
        if (!in_array($orderSource, array('web', 'app'), true)) {
            return '';
        }

        $column = $alias !== '' ? $alias . '.order_source' : 'order_source';
        return " AND {$column} = '" . $orderSource . "'";
    }
}
