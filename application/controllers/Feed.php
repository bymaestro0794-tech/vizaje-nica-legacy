<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Feed extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Facebook Feed
     * @param string $lang ro|ru
     */
    public function facebook($lang = 'ro')
    {
        // Корректируем язык
        $lang = ($lang == 'ru') ? 'ru' : 'ro';



        // Загружаем товары
        $products = $this->db
            ->select("
        p.id,
        p.SKU,
        p.title{$lang} AS title,
        p.text{$lang} AS description,
        p.price,
        p.discount_price,
        p.volume,
        p.stock,
        p.on_stock,
        p.category_id,
        p.brand_id,
        p.sex,
        p.uri{$lang} AS uri,
        
        c.title{$lang} AS category_title,
        c.uri{$lang} AS category_uri
    ")
            ->from('products p')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->where('p.stock >', 0)
            ->where('p.isShown', 1)
            ->where('c.isShown', 1)
            ->get()
            ->result_array();


        // Загружаем картинки (основная)
        $images = $this->db
            ->select("
                product_id,
                img
            ")
            ->from('products_img')
            ->where('isShown', 1)
            ->order_by('sorder', 'ASC')
            ->get()
            ->result_array();

        // Индексируем картинки по товару
        $imgIndex = [];
        foreach ($images as $img) {
            if (!isset($imgIndex[$img['product_id']])) {
                $imgIndex[$img['product_id']] = $img['img'];
            }
        }

        // Склеиваем изображения
        foreach ($products as &$p) {
            $p['main_image'] = isset($imgIndex[$p['id']])
                ? base_url('public/products/' . $imgIndex[$p['id']])
                : '';
        }


        // Загружаем Бренды (основная)
        $brands = $this->db
            ->select("
                title,
                id
            ")
            ->from('brands')
            ->where('isShown', 1)
            ->order_by('sorder', 'ASC')
            ->get()
            ->result_array();

        // Индексируем картинки по товару
        $brandsIndex = [];
        foreach ($brands as $brand) {
            if (!isset($brandsIndex[$brand['id']])) {
                $brandsIndex[$brand['id']] = $brand['title'];
            }
        }

        // Склеиваем изображения
        foreach ($products as &$p) {
            if (!empty($p['brand_id']) && !empty($brandsIndex[$p['brand_id']])){
                $p['brand'] = $brandsIndex[$p['brand_id']];
            }
        }
 
        // Генерация XML будет ниже
        $xml = $this->generateXML($products, $lang);

        // Отправляем XML
        header("Content-Type: application/xml; charset=UTF-8");
        echo $xml;
    }

    private function generateXML($products, $lang)
    {

        // Начало XML
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">' . "\n";
        $xml .= "  <channel>\n";
        $xml .= "    <title>Vizaje Nica</title>\n";
        $xml .= "    <link>https://vizaje-nica.com</link>\n";
        $xml .= "    <description>Product feed</description>\n\n";



        foreach ($products as $p) {

            if ($lang == 'ro'){
                switch ($p['sex']) {
                    case 'Женский':
                        $sex = 'Femeie';
                        break;
                    case 'Мужской':
                        $sex = 'Bărbat';
                        break;
                    case 'Унисекс':
                        $sex = 'Unisex';
                        break;
                }
            } else {
               $sex =  $p['sex'];
            }

            if (!empty($p['main_image'])) {
                // Название
                $title = trim($p['title']);

                // Описание без HTML
                $description = trim(strip_tags($p['description']));
                if (empty($description)){
                    $description = $title.' '.$p['category_title'];
                }

                // Полная ссылка на товар
                $url = base_url($lang . '/catalog/' . $p['category_uri'] . '/' . $p['uri']);

                // Основное фото
                $image = $p['main_image'];
                $filePath = $_SERVER['DOCUMENT_ROOT'] . parse_url($image, PHP_URL_PATH);
                if (file_exists($filePath)) {
                    $sizeMB = round(filesize($filePath) / 1024 / 1024, 2);
                    if ($sizeMB > 8) {
                        continue;
                    }
                } else {
                    continue;
                }



                // Бренд
                $brand = !empty($p['brand'])?$p['brand']: 'Vizaje-nica';


                // Наличие
                $availability = ($p['stock'] > 0 ? 'in stock' : 'out of stock');

                // Цена
                $price = number_format($p['price'], 2, '.', '');

                // Скидочная цена (если есть)
                $salePrice = (!empty($p['discount_price']) && $p['discount_price'] > 0)
                    ? number_format($p['discount_price'], 2, '.', '') . " MDL"
                    : null;

                $xml .= "    <item>\n";
                $xml .= "        <g:id><![CDATA[" . $p['id'] . "]]></g:id>\n";
                $xml .= "        <g:title><![CDATA[" . $title . "]]></g:title>\n";
                $xml .= "        <g:description><![CDATA[" . $description . "]]></g:description>\n";
                $xml .= "        <g:link><![CDATA[" . $url . "]]></g:link>\n";
                $xml .= "        <g:image_link><![CDATA[" . $image . "]]></g:image_link>\n";
                $xml .= "        <g:brand><![CDATA[" . $brand . "]]></g:brand>\n";
                $xml .= "        <g:sex><![CDATA[" . $sex . "]]></g:sex>\n";
                $xml .= "        <g:condition><![CDATA[new]]></g:condition>\n";
                $xml .= "        <g:availability><![CDATA[" . $availability . "]]></g:availability>\n";
                $xml .= "        <g:price><![CDATA[" . $price . " MDL]]></g:price>\n";

                // Скидка
                if ($salePrice) {
                    $xml .= "        <g:sale_price><![CDATA[" . $salePrice . "]]></g:sale_price>\n";
                }

                $xml .= "        <g:mpn><![CDATA[" . $p['SKU'] . "]]></g:mpn>\n";

                $xml .= "    </item>\n\n";

            }
        }

        // Конец XML
        $xml .= "  </channel>\n";
        $xml .= "</rss>";


        return $xml;
    }

    public function multisearch()
    {
        $lang = $this->input->get('lang', true);

        $lang = $lang === 'ru'
            ? 'ru'
            : 'ro';

        $upLang = strtoupper($lang);

        $escapeXml = static function ($value) {
            return htmlspecialchars(
                (string) $value,
                ENT_XML1 | ENT_QUOTES,
                'UTF-8'
            );
        };

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<yml_catalog>' . "\n";
        $xml .= '  <shop>' . "\n";
        $xml .= '    <name>Vizaje Nica</name>' . "\n";
        $xml .= '    <url>https://vizaje-nica.com</url>' . "\n";
        $xml .= '    <currencies>' . "\n";
        $xml .= '      <currency id="MDL" rate="1"/>' . "\n";
        $xml .= '    </currencies>' . "\n";

        /*
        * Brands
        */
        $brands = $this->db
            ->select('id, title')
            ->from('brands')
            ->where('isShown', 1)
            ->get()
            ->result_array();

        $brandsIndex = array();

        foreach ($brands as $brand) {
            $brandsIndex[(int) $brand['id']] = (string) $brand['title'];
        }

        /*
        * Categories
        */
        $categories = $this->db
            ->select("
                id,
                parent_id AS parentId,
                title{$upLang} AS title,
                uri{$upLang} AS uri,
                sorder AS ordering
            ", false)
            ->from('categories')
            ->where('isShown', 1)
            ->get()
            ->result_array();

        $categoriesIndex = array();

        $xml .= '    <categories>' . "\n";

        foreach ($categories as $category) {
            $categoryId = (int) $category['id'];
            $parentId = (int) $category['parentId'];
            $categoryUri = trim((string) $category['uri']);
            $categoryTitle = trim((string) $category['title']);
            $ordering = (int) $category['ordering'];

            if ($categoryUri === '' || $categoryTitle === '') {
                continue;
            }

            $categoriesIndex[$categoryId] = $categoryUri;

            $categoryUrl =
                'https://vizaje-nica.com/'
                . $lang
                . '/catalog/'
                . $categoryUri;

            $xml .= '      <category'
                . ' id="' . $categoryId . '"'
                . ' sordering="' . $ordering . '"'
                . ' parentId="' . $parentId . '"'
                . ' uri="' . $escapeXml($categoryUrl) . '"'
                . '>'
                . $escapeXml($categoryTitle)
                . '</category>'
                . "\n";
        }

        $xml .= '    </categories>' . "\n";

        /*
        * Products
        *
        * Подзапрос возвращает только одно изображение товара.
        */
        $products = $this->db
            ->select("
                products.id,
                products.guid,
                products.sku,
                products.brand_id AS brandId,
                products.title{$upLang} AS title,
                products.text{$upLang} AS text,
                products.price,
                products.discount_price,
                products.category_id,
                products.stock,
                products.uri{$upLang} AS uri,
                (
                    SELECT product_image.img
                    FROM products_img product_image
                    WHERE product_image.product_id = products.id
                        AND product_image.isShown = 1
                    ORDER BY
                        product_image.sorder ASC,
                        product_image.id ASC
                    LIMIT 1
                ) AS img
            ", false)
            ->from('products')
            ->where('products.isShown', 1)
            ->get()
            ->result_array();

        $xml .= '    <offers>' . "\n";

        $usedOfferIds = array();

        foreach ($products as $product) {
            $productId = (int) $product['id'];

              /*
                * Offer ID должен быть уникальным не только внутри одного фида,
                * но и между RU и RO фидами.
                */
             $offerId = $lang . '-' . $productId;
             if (
                    $productId <= 0
                    || isset($usedOfferIds[$offerId])
            ) {
                continue;
            }

            $categoryId = (int) $product['category_id'];
            $categoryUri = $categoriesIndex[$categoryId] ?? '';

            if ($categoryUri === '') {
                continue;
            }

            $imageName = trim((string) $product['img']);

            if ($imageName === '') {
                continue;
            }

            $guid = trim((string) $product['guid']);

            if ($guid === '') {
                continue;
            }

            $title = trim((string) $product['title']);

            if ($title === '') {
                continue;
            }

            $usedOfferIds[$offerId] = true;

            $brandId = (int) $product['brandId'];

            $brandTitle = $brandsIndex[$brandId]
                ?? 'Vizaje-Nica';

            $productUrl =
                'https://vizaje-nica.com/'
                . $lang
                . '/catalog/'
                . $categoryUri
                . '/'
                . $guid;

            $imageUrl =
                'https://vizaje-nica.com/public/products/'
                . ltrim($imageName, '/');

            $regularPrice = (float) $product['price'];
            $discountPrice = (float) $product['discount_price'];

            $hasDiscount =
                $discountPrice > 0
                && $discountPrice < $regularPrice;

            $currentPrice = $hasDiscount
                ? $discountPrice
                : $regularPrice;

            $oldPrice = $hasDiscount
                ? $regularPrice
                : 0;

            $isAvailable = (int) $product['stock'] > 0;

            $xml .= '      <offer'
                . ' id="' . $offerId . '"'
                . ' available="' . ($isAvailable ? 'true' : 'false') . '"'
                . '>'
                . "\n";

            $xml .= '        <currencyId>MDL</currencyId>' . "\n";

            $xml .= '        <name>'
                . $escapeXml($title)
                . '</name>'
                . "\n";

            $xml .= '        <price>'
                . number_format(
                    $currentPrice,
                    2,
                    '.',
                    ''
                )
                . '</price>'
                . "\n";

            $xml .= '        <oldprice>'
                . number_format(
                    $oldPrice,
                    2,
                    '.',
                    ''
                )
                . '</oldprice>'
                . "\n";

            $xml .= '        <categoryId>'
                . $categoryId
                . '</categoryId>'
                . "\n";

            $xml .= '        <url>'
                . $escapeXml($productUrl)
                . '</url>'
                . "\n";

            $xml .= '        <picture>'
                . $escapeXml($imageUrl)
                . '</picture>'
                . "\n";

            $xml .= '        <vendor>'
                . $escapeXml($brandTitle)
                . '</vendor>'
                . "\n";

            $xml .= '        <vendorCode>'
                . $escapeXml($product['sku'])
                . '</vendorCode>'
                . "\n";

            $xml .= '      </offer>' . "\n";
        }

        $xml .= '    </offers>' . "\n";
        $xml .= '  </shop>' . "\n";
        $xml .= '</yml_catalog>' . "\n";

        $this->output
            ->set_content_type('application/xml', 'UTF-8')
            ->set_output($xml);
    }
}
