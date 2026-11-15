<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Whitelists only fields needed by the Legacy creator UI; never forwards API bodies. */
class Partners_creator_dashboard_presenter
{
    public static function analytics($body)
    {
        if (!is_array($body)) return array('available' => false, 'empty' => false, 'metrics' => array());
        $source = isset($body['summary']) && is_array($body['summary']) ? $body['summary'] : $body;
        $aliases = array(
            'clicks' => array('clicks', 'totalClicks', 'linkClicks'),
            'add_to_cart' => array('addToCart', 'addToCartEvents', 'cartAdditions'),
            'orders' => array('ordersCreated', 'createdOrders', 'orders'),
            'confirmed_orders' => array('ordersConfirmed', 'confirmedOrders'),
            'sales_mdl' => array('confirmedSalesMdl', 'confirmedSalesMDL', 'confirmedSales', 'salesMdl'),
            'conversion' => array('conversion', 'conversionRate'),
        );
        $metrics = array(); $valid = 0; $nonzero = false;
        foreach ($aliases as $name => $keys) {
            $value = self::firstScalar($source, $keys);
            if ($value !== null) {
                $metrics[$name] = $value;
                $valid++;
                if ((float) $value != 0.0) $nonzero = true;
            }
        }
        // Some versions place aggregate counters in a `totals` object.
        if ($valid === 0 && isset($body['totals']) && is_array($body['totals'])) return self::analytics(array('summary' => $body['totals']));
        if ($valid !== count($aliases)) return array('available' => false, 'empty' => false, 'metrics' => array());
        return array('available' => true, 'empty' => !$nonzero, 'metrics' => $metrics);
    }

    public static function links($body)
    {
        if (!is_array($body) || !isset($body['links']) || !is_array($body['links'])) return array('available' => false, 'links' => array());
        $result = array();
        foreach ($body['links'] as $item) {
            if (!is_array($item)) return array('available' => false, 'links' => array());
            $id = self::firstScalar($item, array('id', 'linkId'));
            $type = self::firstScalar($item, array('linkType', 'type'));
            $short = self::firstScalar($item, array('shortUrl', 'short_url'));
            $target = self::firstScalar($item, array('targetUrl', 'target_url', 'target'));
            if (!is_string($id) && !is_int($id)) return array('available' => false, 'links' => array());
            if (!in_array($type, array('homepage', 'product', 'category', 'brand'), true)) return array('available' => false, 'links' => array());
            if (!is_string($short) || !self::isSafeLinkUrl($short)) return array('available' => false, 'links' => array());
            $result[] = array(
                'id' => (string) $id,
                'type' => $type,
                'title' => self::firstString($item, array('label', 'title', 'name')),
                'short_url' => $short,
                'target_url' => is_string($target) && self::isSafeLinkUrl($target) ? $target : '',
                'created_at' => self::firstString($item, array('createdAt', 'created_at')),
                'status' => self::firstString($item, array('status')),
                'clicks' => self::firstScalar($item, array('clicks', 'clickCount')),
                'orders' => self::firstScalar($item, array('orders', 'ordersCount')),
            );
        }
        return array('available' => true, 'links' => $result);
    }

    public static function createdLink($body)
    {
        if (!is_array($body) || !isset($body['link']) || !is_array($body['link'])) return null;
        $presented = self::links(array('links' => array($body['link'])));
        return !empty($presented['available']) ? $presented['links'][0] : null;
    }

    private static function firstScalar($array, $keys)
    {
        foreach ($keys as $key) if (array_key_exists($key, $array) && (is_string($array[$key]) || is_int($array[$key]) || is_float($array[$key]))) return $array[$key];
        return null;
    }

    private static function firstString($array, $keys)
    {
        $value = self::firstScalar($array, $keys);
        return $value === null ? '' : (string) $value;
    }

    private static function isSafeLinkUrl($value)
    {
        if (!is_string($value) || strlen($value) > 2048) return false;
        $parts = parse_url($value);
        if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment'])) return false;
        $scheme = strtolower($parts['scheme']); $host = strtolower($parts['host']);
        $port = isset($parts['port']) ? (int) $parts['port'] : null;
        $productionHost = in_array($host, array('vizaje-nica.com', 'www.vizaje-nica.com', 'dev.vizaje-nica.com'), true);
        $localHost = in_array($host, array('vizaje-nica.local', 'localhost'), true);
        return ($scheme === 'https' && $port === null && $productionHost)
            || ($scheme === 'http' && $localHost && ($port === null || $port === 80));
    }
}
