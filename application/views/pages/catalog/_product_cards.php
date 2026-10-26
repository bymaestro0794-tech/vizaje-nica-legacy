<?php
defined('BASEPATH') or exit('No direct script access allowed');

$cardOptions = isset($card_options) && is_array($card_options)
    ? $card_options
    : array();

foreach ((array) $products as $product) {
    $this->load->view(
        'layouts/pages/product_main',
        array_merge(array('item' => $product), $cardOptions)
    );
}
