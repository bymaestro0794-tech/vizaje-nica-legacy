<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * A private, local-only surface for trying HTML/CSS ideas without loading or
 * changing the storefront layout.
 */
class Design_lab extends CI_Controller
{
    public function index($view = 'dev/design_lab')
    {
        $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
        $host = preg_replace('/:\d+$/', '', $host);
        $remoteAddress = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        $allowedHosts = array(
            'vizaje-nica.local',
            'localhost',
            '127.0.0.1',
            '::1',
        );

        if (
            !in_array($host, $allowedHosts, true)
            || !in_array($remoteAddress, array('127.0.0.1', '::1'), true)
        ) {
            show_404();
            return;
        }

        $this->output
            ->set_header('X-Robots-Tag: noindex, nofollow, noarchive')
            ->set_header('Cache-Control: no-store, private');

        $this->load->model('products_model');
        $products = $this->products_model->get_products_pag_new_products('RU', 0, 10);
        $saleProducts = $this->products_model->get_products_home_sale('RU');
        $bestSellerProducts = $this->products_model->get_products_home_order('RU');

        $this->load->model('home_brand_sections_model');
        $brandSections = array_merge(
            $this->home_brand_sections_model->get_front_sections('RU', 'after_new'),
            $this->home_brand_sections_model->get_front_sections('RU', 'after_sale')
        );

        if (!in_array($view, array('dev/design_lab', 'dev/design_lab_catalog'), true)) {
            $view = 'dev/design_lab';
        }

        $this->load->view($view, array(
            'products' => is_array($products) ? $products : array(),
            'saleProducts' => is_array($saleProducts) ? $saleProducts : array(),
            'bestSellerProducts' => is_array($bestSellerProducts) ? $bestSellerProducts : array(),
            'brandSections' => is_array($brandSections) ? $brandSections : array(),
        ));
    }

    public function catalog()
    {
        $this->index('dev/design_lab_catalog');
    }
}
