<?php

class MY_Cart extends CI_Cart
{

    /*
     * For corect work we create table in DB with name 'shop_cart_items' and structure:
     *
         CREATE TABLE `shop_cart_items` (
              `id` int(11) NOT NULL,
              `code` varchar(255) NOT NULL,
              `contact_id` int(11) DEFAULT NULL,
              `rowid` varchar(255) NOT NULL,
              `product_id` int(11) NOT NULL,
              `sku_id` int(11) DEFAULT NULL,
              `name` varchar(255) DEFAULT NULL,
              `create_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `qty` int(11) NOT NULL,
              `price` decimal(15,4) DEFAULT NULL,
              `options` text CHARACTER SET utf8 COLLATE utf8_roman_ci NOT NULL
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;

        ALTER TABLE `shop_cart_items` ADD PRIMARY KEY (`id`);
        ALTER TABLE `shop_cart_items` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
        COMMIT;
     */

    const COOKIE_KEY = 'shopcart';
    protected $tblname = 'shop_cart_items';
    protected $code;
    protected $data;

    public function __construct($params = array())
    {
        //parent::__construct($params);
        $this->CI =& get_instance();

        // Are any config settings being passed manually?  If so, set them
        $config = is_array($params) ? $params : array();

        // Load the Sessions class
//        $this->CI->load->driver('session', $config);

        //get cookie
        $this->CI->load->helper('cookie');
        $this->code = get_cookie(self::COOKIE_KEY);
        $cookie_expire_time = 3600 * 24 * 7;

        //get if is_auth
        $this->_is_logged_in();

        if (!$this->code) {
            if ($this->data['isAuth']) {
                //get last code from cart table of MySQL
                $code = $this->getLastCode($this->data['contact']);
                if ($code) {
                    $this->code = $code;
                }
            }
            if (!$this->code) {
                $this->code = self::generateCode();
            }
            if ($this->code) {
                $cookie = array(
                    'name' => self::COOKIE_KEY,
                    'value' => $this->code,
                    'expire' => $cookie_expire_time
                );

                set_cookie($cookie);
                $this->destroy();
            }
        } else {
            if ($this->data['isAuth']) {
                $code = $this->getLastCode($this->data['contact']);
                if (!$code) {
                    $this->CI->db->where('code', $this->code)->update($this->tblname, array('contact_id' => $this->data['contact']));
                    //$this->destroy();
                } else {
                    if ($code !== $this->code) {
                        // merge guest cart into cart of authorized user
                        $this->mergeCarts($this->code, $code);

                        // replace guest cart code with code of cart of authorized user
                        $cookie = array(
                            'name' => self::COOKIE_KEY,
                            'value' => $code,
                            'expire' => $cookie_expire_time
                        );

                        set_cookie($cookie);
                        //$this->destroy();

                        $this->code = $code;
                    }
                }
            }
        }


        //get cart_contents
        $items = $this->CI->db->where('code', $this->code)->order_by('id ASC')->get($this->tblname)->result_array();
        if ($items) {
            $cart_total = 0;
            $total_items = 0;

            foreach ($items as $item) {
                $item['price'] = round($item['price'], 2);
                $this->_cart_contents[$item['rowid']] = array(
                    'rowid' => $item['rowid'],
                    'code' => $item['code'],
                    'name' => $item['name'],
                    'id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'options' => json_decode($item['options'], true)
                );

                $cart_total += $item['price'] * $item['qty'];
                $total_items += $item['qty'];
//                $total_items += 1;
            }

            $this->_cart_contents['cart_total'] = $cart_total;
            $this->_cart_contents['total_items'] = $total_items;
        } else {
            // No cart exists so we'll set some base values
            $this->_cart_contents = array('cart_total' => 0, 'total_items' => 0);
        }
    }

    public static function generateCode()
    {
        return md5(uniqid(mt_rand() . mt_rand() . mt_rand() . mt_rand(), true));
    }



    protected function getLastCode($contact_id)
    {
        $this->CI->db->select('code');
        $this->CI->db->where('contact_id', $contact_id);
        $this->CI->db->order_by('create_datetime DESC');
        $response = $this->CI->db->get($this->tblname)->row_array();
        return $response ? $response['code'] : null;
    }

    protected function mergeCarts($src_code, $dst_code)
    {
        $src_cart_items = $this->CI->db->where('code', $src_code)->get($this->tblname)->result_array();
        $dst_cart_items = $this->CI->db->where('code', $dst_code)->get($this->tblname)->result_array();

        $contact_id = null;
        if ($dst_cart_items) {
            $cart_item = reset($dst_cart_items);
            $contact_id = $cart_item['contact_id'];
        }

        $new_dst_cart_items = $this->mergeCartItems($src_cart_items, $dst_cart_items, $dst_code, $contact_id);

        $this->CI->db->delete($this->tblname, array('code' => $src_code));
        $this->CI->db->delete($this->tblname, array('code' => $dst_code));
        if ($new_dst_cart_items) {
            $this->CI->db->insert_batch($this->tblname, $new_dst_cart_items);
        }
    }

    protected function mergeCartItems($src_cart_items, $dst_cart_items, $new_cart_code, $new_contact_id)
    {
        // Found equals items and update quantity

        foreach ($dst_cart_items as &$dst_cart_item) {
            foreach ($src_cart_items as $src_cart_item_id => $src_cart_item) {
                if (!$this->isCartItemsEqual($src_cart_item, $dst_cart_item)) {
                    continue;
                }
                // update quantity of sku
                $quantity = $src_cart_item['qty'];
                $dst_cart_item['qty'] += $quantity;

                //unset this item
                unset($src_cart_items[$src_cart_item_id]);
            }
        }
        unset($dst_cart_item);

        // Add items to dst cart
        foreach ($src_cart_items as $src_cart_item) {
            $dst_cart_items[$src_cart_item['id']] = $src_cart_item;
        }

        // Update cart code and contact ID
        foreach ($dst_cart_items as $key => &$cart_item) {
            $cart_item['code'] = $new_cart_code;
            $cart_item['contact_id'] = $new_contact_id;
            $cart_item['create_datetime'] = date('Y-m-d H:i:s');
            unset($dst_cart_items[$key]['id']);
        }
        unset($cart_item);

        return $dst_cart_items;
    }

    protected function isCartItemsEqual($item1, $item2)
    {
        if (!is_array($item1) || !is_array($item2)) {
            return false;
        }

        if ($item1['sku_id'] !== $item2['sku_id']) {
            return false;
        }

        if ($item1['product_id'] !== $item2['product_id']) {
            return false;
        }
        return true;
    }

    protected function _insert($item = array())
    {

        if (!is_array($item) || count($item) === 0) {
            log_message('error', 'The insert method must be passed an array containing data.');
            return false;
        }

        if (!isset($item['id'], $item['qty'], $item['price'], $item['name'])) {
            log_message('error', 'The cart array must contain a product ID, quantity, price, and name.');
            return false;
        }

        $item['qty'] = (float)$item['qty'];
        if ($item['qty'] == 0) {
            return false;
        }
        if (!preg_match('/^[' . $this->product_id_rules . ']+$/i', $item['id'])) {
            log_message('error', 'Invalid product ID.  The product ID can only contain alpha-numeric characters, dashes, and underscores');
            return false;
        }

        if ($this->product_name_safe && !preg_match('/^[' . $this->product_name_rules . ']+$/i' . (UTF8_ENABLED ? 'u' : ''), $item['name'])) {
            log_message('error', 'An invalid name was submitted as the product name: ' . $item['name'] . ' The name can only contain alpha-numeric characters, dashes, underscores, colons, and spaces');
            return false;
        }


        $item['price'] = (float)$item['price'];

        if (isset($item['options'])) {
            $rowid = md5($item['id'] . serialize($item['options']));
        } else {
            $rowid = md5($item['id']);
        }

        $old_quantity = isset($this->_cart_contents[$rowid]['qty']) ? (int)$this->_cart_contents[$rowid]['qty'] : 0;

        // Re-create the entry, just to make sure our index contains only the data from this submission
        $item['rowid'] = $rowid;
        $item['qty'] += $old_quantity;
        $this->_cart_contents[$rowid] = $item;

        if (isset($item['options']['sku_id'])) {
            $item['sku_id'] = $item['options']['sku_id'];
        }
        $item['contact_id'] = $this->data['isAuth'] ? $this->data['contact'] : null;
        $item['create_datetime'] = date('Y-m-d H:i:s');
        $item['code'] = $this->code;
        $item['options'] = isset($item['options']) ? json_encode($item['options']) : '';
        $item['product_id'] = $item['id'];
        unset($item['id']);

        //add to database
        $row = $this->CI->db->select('id')->where('rowid', $item['rowid'])->where('code', $item['code'])->get($this->tblname)->row_array();
        if ($row) {
            $this->CI->db->where('id', $row['id'])->update($this->tblname, $item);
        } else {
            $this->CI->db->insert($this->tblname, $item);
        }

        return $rowid;
    }

    public function insert($items = array())
    {
        if (!is_array($items) || count($items) === 0) {
            log_message('error', 'The insert method must be passed an array containing data.');
            return false;
        }

        $update_cart_contents = false;
        if (isset($items['id'])) {
            if (($rowid = $this->_insert($items))) {
                $update_cart_contents = true;
            }
        } else {
            foreach ($items as $val) {
                if (is_array($val) && isset($val['id'])) {
                    if (!$this->_insert($val)) {
                        return false;
                    } else {
                        $update_cart_contents = true;
                    }
                }
            }
        }

        if ($update_cart_contents) {
            $this->_update_cart_contents();
            return true;
        }

        return false;
    }

    protected function _update($items = array())
    {
        if (!isset($items['rowid'], $this->_cart_contents[$items['rowid']])) {
            return false;
        }

        if (isset($items['qty'])) {
            $items['qty'] = (float)$items['qty'];
            if ($items['qty'] == 0) {
                unset($this->_cart_contents[$items['rowid']]);

                return true;
            }
        }

        $keys = array_intersect(array_keys($this->_cart_contents[$items['rowid']]), array_keys($items));
        if (isset($items['price'])) {
            $items['price'] = (float)$items['price'];
        }

        $to_update = array();
        foreach (array_diff($keys, array('id', 'name')) as $key) {
            $this->_cart_contents[$items['rowid']][$key] = $items[$key];
            $to_update[$key] = $items[$key];
        }

        if ($to_update) {
            $this->CI->db->where('rowid', $items['rowid'])->where('code', $this->code)->update($this->tblname, $to_update);
        }

        return true;
    }

    public function update($items = array())
    {
        if (!is_array($items) || count($items) === 0) {
            return false;
        }

        $update_cart_contents = false;
        if (isset($items['rowid'])) {
            if ($this->_update($items) === true) {
                $update_cart_contents = true;
            }
        } else {
            foreach ($items as $val) {
                if (is_array($val) && isset($val['rowid'])) {
                    if ($this->_update($val) === true) {
                        $update_cart_contents = true;
                    }
                }
            }
        }

        if ($update_cart_contents === true) {
            $this->_update_cart_contents();
            return true;
        }

        return false;
    }

    protected function _update_cart_contents()
    {
        $this->_cart_contents['total_items'] = $this->_cart_contents['cart_total'] = 0;
        foreach ($this->_cart_contents as $key => $val) {
            if (!is_array($val) || !isset($val['price'], $val['qty'])) {
                continue;
            }

            $this->_cart_contents['cart_total'] += ($val['price'] * $val['qty']);
            $this->_cart_contents['total_items'] += $val['qty'];
//            $this->_cart_contents['total_items'] += 1;
            $this->_cart_contents[$key]['subtotal'] = ($this->_cart_contents[$key]['price'] * $this->_cart_contents[$key]['qty']);
        }

        if (count($this->_cart_contents) <= 2) {
            $this->CI->db->delete($this->tblname, array('code' => $this->code));
            return false;
        }

        return true;
    }

    public function remove($rowid)
    {
        unset($this->_cart_contents[$rowid]);
        $this->CI->db->delete($this->tblname, array('code' => $this->code, 'rowid' => $rowid));
        $this->_update_cart_contents();
        return TRUE;
    }

    public function destroy()
    {
        $this->_cart_contents = array('cart_total' => 0, 'total_items' => 0);
        $this->CI->db->delete($this->tblname, array('code' => $this->code));
    }

    protected function _is_logged_in()
    {
        $this->data['isAuth'] = false;

        $response['result'] = @$_SESSION['user_id'];
        if (!empty($response['result'])) {
            $login_id = $response['result'];
        }

        if (isset($login_id) && !empty($login_id)) {
            $this->data['isAuth'] = true;
            $this->data['contact'] = $login_id;
        }
    }
}