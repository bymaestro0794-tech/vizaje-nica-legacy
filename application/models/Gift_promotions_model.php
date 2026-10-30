<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Gift_promotions_model extends BaseModel
{
    protected $tblname = 'gift_promotions';

    private function progressModeSelect()
    {
        if ($this->db->field_exists('progress_mode', 'gift_promotions')) {
            return "COALESCE(NULLIF(gp.progress_mode, ''), 'time') AS progress_mode";
        }

        return "'time' AS progress_mode";
    }

    public function get_admin()
    {
        return $this->db
            ->select("gp.*, g.titleRU AS gift_title_ru, g.titleRO AS gift_title_ro, g.product_id AS gift_product_id, g.is_enabled AS gift_is_enabled, g.show_on_site AS gift_show_on_site, o.titleRU AS offer_title_ru, CASE WHEN g.variant_id > 0 THEN COALESCE((SELECT pv_gift_fixed.qty FROM products_variable pv_gift_fixed WHERE pv_gift_fixed.id = g.variant_id AND pv_gift_fixed.product_id = g.product_id AND pv_gift_fixed.isShown = 1), 0) ELSE GREATEST(COALESCE(gift_product.on_stock, 0), COALESCE((SELECT SUM(pv_gift_stock.qty) FROM products_variable pv_gift_stock WHERE pv_gift_stock.product_id = g.product_id AND pv_gift_stock.isShown = 1), 0)) END AS remaining_stock", false)
            ->from('gift_promotions gp')
            ->join('gifts g', 'g.id = gp.gift_id', 'left')
            ->join('products gift_product', 'gift_product.id = g.product_id', 'left')
            ->join('offers o', 'o.id = gp.offer_id', 'left')
            ->order_by('gp.sorder ASC, gp.id DESC')
            ->get()
            ->result();
    }

    public function find_first_admin($id)
    {
        return $this->db
            ->select('gp.*, g.titleRU AS gift_title_ru, g.titleRO AS gift_title_ro, g.product_id AS gift_product_id, o.titleRU AS offer_title_ru')
            ->from('gift_promotions gp')
            ->join('gifts g', 'g.id = gp.gift_id', 'left')
            ->join('offers o', 'o.id = gp.offer_id', 'left')
            ->where('gp.id', (int) $id)
            ->get()
            ->row();
    }

    public function get_brands($promotionId)
    {
        return $this->db
            ->select('b.id, b.title')
            ->from('gift_promotion_brands gpb')
            ->join('brands b', 'b.id = gpb.brand_id', 'inner')
            ->where('gpb.promotion_id', (int) $promotionId)
            ->order_by('b.title ASC')
            ->get()
            ->result();
    }

    public function get_brand_ids($promotionId)
    {
        $rows = $this->db
            ->select('brand_id')
            ->where('promotion_id', (int) $promotionId)
            ->get('gift_promotion_brands')
            ->result();

        return array_map(function ($row) {
            return (int) $row->brand_id;
        }, $rows);
    }

    public function get_active_for_frontend($language = 'ru')
    {
        $language = strtolower(trim((string) $language));
        $language = in_array($language, array('ru', 'ro'), true) ? $language : 'ru';
        $today = date('Y-m-d');

        $rows = $this->db
            ->select('gp.*, ' . $this->progressModeSelect() . ', gp.title' . strtoupper($language) . ' AS title, gp.description' . strtoupper($language) . ' AS description, g.product_id, g.variant_id, g.title' . strtoupper($language) . ' AS gift_title, g.show_on_site AS gift_show_on_site, g.is_enabled AS gift_is_enabled, o.uri' . strtoupper($language) . ' AS offer_uri, CASE WHEN g.variant_id > 0 THEN COALESCE((SELECT pv_gift_fixed.qty FROM products_variable pv_gift_fixed WHERE pv_gift_fixed.id = g.variant_id AND pv_gift_fixed.product_id = g.product_id AND pv_gift_fixed.isShown = 1), 0) ELSE GREATEST(COALESCE(gift_product.on_stock, 0), COALESCE((SELECT SUM(pv_gift_stock.qty) FROM products_variable pv_gift_stock WHERE pv_gift_stock.product_id = g.product_id AND pv_gift_stock.isShown = 1), 0)) END AS remaining_stock')
            ->from('gift_promotions gp')
            ->join('gifts g', 'g.id = gp.gift_id', 'inner')
            ->join('products gift_product', 'gift_product.id = g.product_id', 'inner')
            ->join('offers o', 'o.id = gp.offer_id', 'left')
            ->where('gp.is_enabled', 1)
            ->where('gp.show_on_site', 1)
            ->where_in('gp.is_online', array(1, 2))
            ->where('g.is_enabled', 1)
            ->where('g.show_on_site', 1)
            ->group_start()
                ->where('g.variant_id >', 0)
                ->where("EXISTS (
                    SELECT 1
                    FROM products_variable gift_variant
                    WHERE gift_variant.id = g.variant_id
                      AND gift_variant.product_id = g.product_id
                      AND gift_variant.isShown = 1
                      AND gift_variant.qty > 0
                )", null, false)
                ->or_group_start()
                    ->where('g.variant_id IS NULL', null, false)
                    ->group_start()
                        ->where('gift_product.on_stock >', 0)
                        ->or_where("EXISTS (
                            SELECT 1
                            FROM products_variable gift_variant_available
                            WHERE gift_variant_available.product_id = g.product_id
                              AND gift_variant_available.isShown = 1
                              AND gift_variant_available.qty > 0
                        )", null, false)
                    ->group_end()
                ->group_end()
            ->group_end()
            ->group_start()
                ->where('gp.date_from <=', $today)
                ->or_where('gp.date_from IS NULL', null, false)
                ->or_where('gp.date_from', '')
                ->or_where('gp.date_from', '0000-00-00')
            ->group_end()
            ->group_start()
                ->where('gp.date_to >=', $today)
                ->or_where('gp.date_to IS NULL', null, false)
                ->or_where('gp.date_to', '')
                ->or_where('gp.date_to', '0000-00-00')
            ->group_end()
            ->order_by('gp.sorder ASC, gp.id DESC')
            ->get()
            ->result();

        foreach ($rows as $row) {
            $row->brand_ids = $this->get_brand_ids($row->id);
        }

        return $rows;
    }

    public function find_frontend($id, $language = 'ru')
    {
        foreach ($this->get_active_for_frontend($language) as $row) {
            if ((int) $row->id === (int) $id) {
                return $row;
            }
        }

        return null;
    }

    public function replace_brands($promotionId, $brandIds)
    {
        $promotionId = (int) $promotionId;
        $this->db->delete('gift_promotion_brands', array('promotion_id' => $promotionId));

        foreach (array_unique(array_map('intval', (array) $brandIds)) as $brandId) {
            if ($brandId > 0) {
                $this->db->insert('gift_promotion_brands', array(
                    'promotion_id' => $promotionId,
                    'brand_id' => $brandId,
                ));
            }
        }

        return true;
    }
}
