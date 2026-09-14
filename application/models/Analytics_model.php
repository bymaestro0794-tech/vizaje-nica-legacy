<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Read-only sales analytics for the legacy admin.
 *
 * This model intentionally uses finished orders only. Behaviour analytics
 * will be added later through a separate first-party event store.
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
                isset($options['brand_id']) ? $options['brand_id'] : null
            ),
            'top_products' => $this->get_top_products(
                $fromDateTime,
                $toDateTime,
                isset($options['product_sort']) ? $options['product_sort'] : 'quantity',
                isset($options['direction']) ? $options['direction'] : 'desc',
                isset($options['brand_id']) ? $options['brand_id'] : null
            ),
            'top_brands' => $this->get_top_brands(
                $fromDateTime,
                $toDateTime,
                isset($options['brand_sort']) ? $options['brand_sort'] : 'quantity',
                isset($options['direction']) ? $options['direction'] : 'desc',
                isset($options['brand_id']) ? $options['brand_id'] : null
            ),
            'top_categories' => $this->get_top_categories(
                $fromDateTime,
                $toDateTime,
                isset($options['category_sort']) ? $options['category_sort'] : 'quantity',
                isset($options['direction']) ? $options['direction'] : 'desc',
                isset($options['brand_id']) ? $options['brand_id'] : null
            ),
            'period_sales' => $this->get_period_sales(
                $fromDateTime,
                $toDateTime,
                isset($options['group_by']) ? $options['group_by'] : 'month',
                isset($options['brand_id']) ? $options['brand_id'] : null
            ),
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

    private function get_summary($fromDateTime, $toDateTime, $brandId = null)
    {
        $orderTotal = $this->money_expression('o.total');
        $lineTotal = $this->money_expression('op.total');

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

    private function get_top_products($fromDateTime, $toDateTime, $sort, $direction, $brandId = null)
    {
        $lineTotal = $this->money_expression('op.total');
        $sortSql = $this->product_sort_sql($sort);
        $directionSql = $this->normalize_direction($direction);
        $brandCondition = '';
        $params = array($fromDateTime, $toDateTime);

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
            ORDER BY {$sortSql} {$directionSql}, product_revenue DESC, product_title ASC
            LIMIT 10
        ";

        return $this->db->query($sql, $params)->result_array();
    }

    private function get_top_brands($fromDateTime, $toDateTime, $sort, $direction, $brandId = null)
    {
        $lineTotal = $this->money_expression('op.total');
        $sortSql = $this->brand_sort_sql($sort);
        $directionSql = $this->normalize_direction($direction);
        $brandCondition = '';
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

    private function get_top_categories($fromDateTime, $toDateTime, $sort, $direction, $brandId = null)
    {
        $lineTotal = $this->money_expression('op.total');
        $sortSql = $this->category_sort_sql($sort);
        $directionSql = $this->normalize_direction($direction);
        $brandCondition = '';
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

    private function get_period_sales($fromDateTime, $toDateTime, $groupBy, $brandId = null)
    {
        $lineTotal = $this->money_expression('op.total');
        $orderTotal = $this->money_expression('o.total');

        $groupBy = $groupBy === 'day' ? 'day' : 'month';
        $dateExpression = $groupBy === 'day'
            ? 'DATE(o.added)'
            : "DATE_FORMAT(o.added, '%Y-%m')";
        $orderBrandCondition = '';
        $orderParams = array($fromDateTime, $toDateTime);
        $itemBrandJoin = '';
        $itemBrandCondition = '';
        $itemParams = array($fromDateTime, $toDateTime);

        if ($brandId !== null) {
            $orderBrandCondition = "
                  AND EXISTS (
                      SELECT 1
                      FROM orders_products AS op_filter
                      INNER JOIN products AS p_filter ON p_filter.id = op_filter.product_id
                      WHERE op_filter.order_id = o.id
                        AND p_filter.brand_id = ?
                  )";
            $orderParams[] = $brandId;
            $itemBrandJoin = ' INNER JOIN products AS p ON p.id = op.product_id';
            $itemBrandCondition = ' AND p.brand_id = ?';
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
}
