<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shop_feature_model extends BaseModel
{
    protected $tblname = 'shop_feature';
    protected $tblname_values = 'shop_feature_values';

    public function __construct()
    {
        parent::__construct();
    }

    public function features($type = 'all', $with_values = false) {
        if(is_null($type)) return array();

        if(is_numeric($type)) {
            $this->db->where('type_id', $type);
        }
        // $this->db->order_by('is_brand DESC');
         $this->db->order_by('sorder ASC, id ASC');
//         $this->db->order_by('id ASC');
        $result = $this->db->get($this->tblname)->result_array();

        $features = array();
        if($result) {
            foreach($result as $res) {
                $features[$res['id']] = $res;
                if($with_values) {
                    $this->db->where('feature_id', $res['id']);
                    $this->db->order_by('sorder ASC, id ASC');
                    $features[$res['id']]['values'] = $this->db->get($this->tblname_values)->result_array();
                }
            }
        }

        return $features;
    }

    public function getColumns($columns, $lang = 'RU') {
        $this->db->where_in('id', $columns);
        $this->db->order_by('sorder ASC');
        $result = $this->db->get($this->tblname)->result_array();

        $features = array();
        if($result) {
            foreach($result as $res) {
                $features[$res['id']] = $res['name_'.$lang];
            }
        }

        return $features;
    }

    public function get_shop_feature_category($lang = 'RU', $feature_type, $category_id) {

        $this->db->select("id,name_$lang as name, type");
        $this->db->where('type_id', $feature_type);
        $this->db->order_by('sorder ASC');
        $result = $this->db->get($this->tblname)->result_array();

        $features = array();
        if($result) {
            foreach($result as $res) {
                $this->db->select("id,name_$lang as name,color,
                (SELECT COUNT(id) FROM products_features WHERE products_features.feature_value_id = shop_feature_values.id AND products_features.category_id = $category_id) as count" );
                $this->db->where('feature_id', $res['id']);
                $this->db->order_by('sorder ASC');
                $feature_values = $this->db->get('shop_feature_values')->result_array();


                $features[$res['id']]['name'] = $res['name'];
                $features[$res['id']]['type'] = $res['type'];
                $features[$res['id']]['value'] = $feature_values;
            }
        }

        return $features;
    }
}
