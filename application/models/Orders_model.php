<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Orders_model extends BaseModel
{
    protected $tblname = 'orders';

    public function __construct()
    {
        parent::__construct();
    }

    public function creatorReferralForOrder($orderId)
    {
        $previousDbDebug = $this->db->db_debug;
        try {
            $this->db->db_debug = false;
            if (!$this->db->field_exists('legacy_creator_referral_attribution_id', 'orders')
                || !$this->db->table_exists('legacy_creator_referral_attributions')
                || !$this->db->table_exists('legacy_creator_referral_links')) return null;

            $commissionFields = $this->db->field_exists('creator_commission_percent', 'orders')
                && $this->db->field_exists('creator_commission_amount', 'orders');
            $commissionSelect = $commissionFields
                ? ', o.creator_commission_percent, o.creator_commission_amount'
                : ', 0 AS creator_commission_percent, 0 AS creator_commission_amount';
            return $this->db->query(
                'SELECT c.name, c.surname, l.label, l.locale, l.link_type'
                . $commissionSelect
                . ' FROM orders o'
                . ' INNER JOIN legacy_creator_referral_attributions a ON a.id = o.legacy_creator_referral_attribution_id'
                . ' INNER JOIN legacy_creator_referral_links l ON l.id = a.link_id'
                . ' INNER JOIN clients c ON c.id = l.client_id'
                . ' WHERE o.id = ? LIMIT 1',
                array((int) $orderId)
            )->row_array();
        } catch (Throwable $error) {
            return null;
        } finally {
            $this->db->db_debug = $previousDbDebug;
        }
    }

    public function get_($lang)
    {
        if (empty($lang)) return false;

    }

    public function search_get_orders_admin($search, $get = array())
    {
        $this->db->select("*");
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->or_like('postcode', $search);
            $this->db->or_like('order_id', $search);
            $this->db->group_end();
        }
        
        if (!empty($get['isb2b'])) {
            if ($get['isb2b'] == 1) {
                $this->db->where('isb2b', 1);
            } else {
                $this->db->where('isb2b', 0);
            }
        }
        $this->db->order_by('sorder ASC, id DESC');
        $products = $this->db->get($this->tblname)->result();
        return $products;
    }

    public function listAdminOrders($search = '', $filters = array(), $role = 'admin', $adminId = 0, $view = 'all')
    {
        $this->db->select('o.*, assigned.login AS operator_login');
        $this->db->from('orders o');
        $this->db->join('admins assigned', 'assigned.id = o.operator_admin_id', 'left');

        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('o.postcode', $search);
            $this->db->or_like('o.order_id', $search);
            $this->db->group_end();
        }

        if (isset($filters['isb2b']) && $filters['isb2b'] !== '' && $filters['isb2b'] !== '0') {
            $this->db->where('o.isb2b', $filters['isb2b'] === '1' ? 1 : 0);
        }

        if ($role === 'operator' && $view !== 'all') {
            $this->db->group_start();
            $this->db->where('o.operator_admin_id', (int) $adminId);
            $this->db->or_group_start();
            $this->db->where('o.operator_admin_id', null);
            $this->db->where('o.status', 'new');
            $this->db->group_end();
            $this->db->group_end();
        }

        return $this->db->order_by('o.sorder ASC, o.id DESC', '', false)->get()->result();
    }

    public function findAdminOrder($id)
    {
        return $this->db
            ->select('o.*, assigned.login AS operator_login')
            ->from('orders o')
            ->join('admins assigned', 'assigned.id = o.operator_admin_id', 'left')
            ->where('o.id', (int) $id)
            ->get()
            ->row();
    }

    /**
     * Updates an order while locking assignment changes so two operators
     * cannot claim the same unassigned order at the same time.
     */
    public function saveAdminOrder($id, $data, $adminId, $isOperator)
    {
        $this->db->trans_begin();
        $current = $this->db->query(
            'SELECT `operator_admin_id` FROM `orders` WHERE `id` = ? FOR UPDATE',
            array((int) $id)
        )->row();

        if (empty($current)) {
            $this->db->trans_rollback();
            return 'missing';
        }

        $assignedAdminId = $current->operator_admin_id === null ? 0 : (int) $current->operator_admin_id;
        if ($isOperator && $assignedAdminId !== 0 && $assignedAdminId !== (int) $adminId) {
            $this->db->trans_rollback();
            return 'assigned_to_other';
        }

        if ($isOperator && $assignedAdminId === 0 && isset($data['status']) && $data['status'] === 'progress') {
            $data['operator_admin_id'] = (int) $adminId;
        }

        $updated = $this->db->where('id', (int) $id)->update('orders', $data);
        if (!$updated || $this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_commit();
        return true;
    }

    public function orderProducts($id, $lang)
    {

        $products = $this->db->select('*')->where('order_id', $id)->get('orders_products')->result();

        // Older orders may not have the explicit marker column yet. Resolve
        // the roulette gift from the redeemed spin so the admin still shows
        // the gift correctly for those orders.
        $rouletteGift = null;
        if ($this->db->table_exists('roulette_spins') && $this->db->table_exists('gifts')) {
            $rouletteGift = $this->db
                ->select('g.id, g.product_id, g.variant_id')
                ->from('roulette_spins s')
                ->join('gifts g', 'g.id = CAST(s.reward_payload_snapshot AS UNSIGNED)', 'inner')
                ->where('s.order_id', (int) $id)
                ->where('s.reward_type_snapshot', 'gift')
                ->where_in('s.status', array('reserved', 'redeemed'))
                ->order_by('s.id', 'DESC')
                ->limit(1)
                ->get()
                ->row();
        }

        if (!empty($products)) {
            foreach ($products as $key => $product) {
                $products[$key]->roulette_gift_id = !empty($product->roulette_gift_id)
                    ? (int) $product->roulette_gift_id
                    : 0;
                $products[$key]->gift_promotion_id = !empty($product->gift_promotion_id)
                    ? (int) $product->gift_promotion_id
                    : 0;

                if (
                    $products[$key]->roulette_gift_id <= 0
                    && !empty($rouletteGift)
                    && (int) $product->product_id === (int) $rouletteGift->product_id
                    && (
                        empty($rouletteGift->variant_id)
                        || (int) $product->options === (int) $rouletteGift->variant_id
                    )
                ) {
                    $products[$key]->roulette_gift_id = (int) $rouletteGift->id;
                }

                if (!empty($product->options)){
                    $products[$key]->product = $this->db->select('uri' . $lang . ' AS uri, SKU, title' . $lang . ' AS title,volume,barcode,WarehouseName,
                    (SELECT categories.uri' . $lang . ' FROM categories WHERE categories.id=products.category_id) as cat_uri,
                    (SELECT brands.title FROM brands WHERE brands.id=products.brand_id) as brand_title,')->where('id', $product->product_id)->get('products')->row();
                    $products[$key]->product->variable = $this->db->select('SKU,titleRO, price,priceWH,qtyWH, discount_price,dose, barcode, WarehouseName')->where('id', $product->options)->get('products_variable')->row();
                    $products[$key]->product->variable->img = $this->db->select('id, img')->where('variable_id', $product->options)->get('products_variable_img')->row();
                    if (empty($products[$key]->product->variable->img)){
                        $products[$key]->product->img = $this->db->select('id, img')->where('product_id', $product->product_id)->get('products_img')->row();
                    }
                } else{
                    $products[$key]->product = $this->db->select('uri' . $lang . ' AS uri, SKU, title' . $lang . ' AS title,volume,barcode,WarehouseName,
                    (SELECT categories.uri' . $lang . ' FROM categories WHERE categories.id=products.category_id) as cat_uri,
                    (SELECT brands.title FROM brands WHERE brands.id=products.brand_id) as brand_title')->where('id', $product->product_id)->get('products')->row();
                    $products[$key]->product->img = $this->db->select('id, img')->where('product_id', $product->product_id)->get('products_img')->row();
                }

            }
        }
        return $products;
    }

    public function get_client_orders($client_id,$lang){
        $orders = $this->db->select('id,order_id,client_id,total,delivery,added,delivery_price,status,bonus_plus')->where('client_id', $client_id)->order_by('id DESC')->get($this->tblname)->result();
        if (!empty($orders)){
            foreach ($orders as $order){
                $order->products = $this->orderProducts($order->id,$lang);
            }
        }
        return $orders;
    }

    public function get_client_order_by_id($client_id,$order_id,$lang){
        $order = $this->db->select('*')->where('client_id', $client_id)->where('order_id', $order_id)->get($this->tblname)->row();
        if (!empty($order)){
                $order->products = $this->orderProducts($order->id,$lang);
        }
        return $order;
    }
}
