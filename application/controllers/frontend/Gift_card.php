<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Gift_card extends FrontEndController
{
    public function index()
    {
        $isRomanian = $this->lclang === 'ro';

        $this->load->model('products_model');

        $productId = 10086;
        $allowedAmounts = array(200, 300, 500, 1000);
        $giftCardOptions = array();
        $variations = $this->products_model->get_product_variable(
            $this->clang,
            $productId,
            true
        );

        foreach ($variations as $variation) {
            $amount = (int) round((float) $variation->price);

            if (!in_array($amount, $allowedAmounts, true)) {
                continue;
            }

            $giftCardOptions[$amount] = array(
                'amount' => $amount,
                'variation_id' => (int) $variation->id,
                'image' => !empty($variation->img[0]->img)
                    ? newthumbs($variation->img[0]->img, 'products_variable_img')
                    : '/app/b2b/img/certificate-main.png',
            );
        }

        foreach ($allowedAmounts as $amount) {
            if (!isset($giftCardOptions[$amount])) {
                $giftCardOptions[$amount] = array(
                    'amount' => $amount,
                    'variation_id' => 0,
                    'image' => '/app/b2b/img/certificate-main.png',
                );
            }
        }

        $this->data['page_title'] = $isRomanian
            ? 'Card cadou Vizaje-Nica'
            : 'Подарочная карта Vizaje-Nica';
        $this->data['page_name'] = $this->data['page_title'];
        $this->data['gift_card_product_id'] = $productId;
        $this->data['gift_card_options'] = array_values($giftCardOptions);
        $this->data['cart_count'] = $this->cart->total_items();
        $this->data['inner_view'] = 'pages/gift_card/index';
        $this->data['home_page'] = 1;
        $this->data['class_page'] = 'gift-card-page';
        $this->data['minimal_page'] = true;
        $this->data['lang_urls'] = array(
            'ru' => 'podarochnaya-karta',
            'ro' => 'card-cadou',
        );

        $this->_render();
    }
}
