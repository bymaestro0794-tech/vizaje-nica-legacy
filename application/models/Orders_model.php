<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Orders_model extends BaseModel
{
    protected $tblname = 'orders';

    public function __construct()
    {
        parent::__construct();
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
