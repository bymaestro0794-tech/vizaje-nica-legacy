<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Search_index extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Полная переиндексация
     * /search_index/rebuild
     */
    public function rebuild()
    {
        set_time_limit(0);

        // очищаем таблицу
        $this->db->truncate('product_search');

        // получаем все товары
        $products = $this->db->get('products')->result();

        foreach ($products as $product) {
            $this->indexProduct($product->id);
        }

        echo "Reindex completed: " . count($products);
    }

    /**
     * Обновление одного товара
     * /search_index/update/123
     */
    public function update($product_id = null)
    {
        if (!$product_id) {
            show_404();
        }

        $this->indexProduct($product_id);

        echo "Updated product ID: " . $product_id;
    }

    /**
     * Основная функция индексации
     */
    private function indexProduct($product_id)
    {
        // удаляем старые записи
        $this->db->where('product_id', $product_id)->delete('product_search');

        // получаем товар
        $product = $this->db->where('id', $product_id)->get('products')->row();

        if (!$product) return;

        // --- ОСНОВНОЙ ТОВАР ---
        $title = implode(' ', [
            $product->titleRU,
            $product->titleRO,
            $product->titleEN
        ]);

        $search_text = implode(' ', [
            $product->GUID,
            $product->SKU,
            $product->titleRU,
            $product->titleRO,
            $product->titleEN
        ]);

        $search_text = $this->normalize($search_text);

        $this->db->insert('product_search', [
            'product_id' => $product->id,
            'variable_id' => null,
            'GUID' => $product->GUID,
            'SKU' => $product->SKU,
            'title' => $title,
            'search_text' => $search_text
        ]);

        // --- ВАРИАЦИИ ---
        $variables = $this->db
            ->where('product_id', $product_id)
            ->get('products_variable')
            ->result();

        foreach ($variables as $var) {

            $title = implode(' ', [
                $var->titleRU,
                $var->titleRO,
                $var->titleEN
            ]);

            $color = implode(' ', [
                $var->colorRU,
                $var->colorRO,
                $var->colorEN
            ]);

            $search_text = implode(' ', [
                $product->GUID,
                $var->SKU,
                $var->titleRU,
                $var->titleRO,
                $var->titleEN,
                $var->colorRU,
                $var->colorRO,
                $var->colorEN,
                $var->VolumeVar
            ]);

            $search_text = $this->normalize($search_text);

            $this->db->insert('product_search', [
                'product_id' => $product->id,
                'variable_id' => $var->id,
                'GUID' => $product->GUID,
                'SKU' => $var->SKU,
                'title' => $title,
                'color' => $color,
                'volume' => $var->VolumeVar,
                'search_text' => $search_text
            ]);
        }
    }

    /**
     * Очистка текста
     */
    private function normalize($text)
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^a-z0-9а-яё\s]/iu', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }


    public function build_spell_dictionary()
    {
        set_time_limit(0);

        $DIR = realpath('application') . '/libraries/search/';

        $ru = '';
        $ro = '';
        $en = '';

        $rows = $this->db->select('search_text')->get('product_search')->result();

        foreach ($rows as $row) {

            $text = mb_strtolower($row->search_text);

            // --- RU ---
            preg_match_all('/([а-яё]+)/u', $text, $matches_ru);
            if (!empty($matches_ru[0])) {
                $ru .= implode(' ', $matches_ru[0]) . ' ';
            }

            // --- RO (латиница) ---
            preg_match_all('/([a-z]+)/u', $text, $matches_ro);
            if (!empty($matches_ro[0])) {
                $ro .= implode(' ', $matches_ro[0]) . ' ';
            }

            // --- EN (то же что RO, но можно разделить позже)
            if (!empty($matches_ro[0])) {
                $en .= implode(' ', $matches_ro[0]) . ' ';
            }
        }

        // --- сохраняем файлы ---
        file_put_contents($DIR . 'bigRU.txt', trim($ru));
        file_put_contents($DIR . 'bigRO.txt', trim($ro));
        file_put_contents($DIR . 'bigEN.txt', trim($en));

        // удаляем сериализованные словари (чтобы пересобрались)
        @unlink($DIR . 'serialized_dictionaryRU.txt');
        @unlink($DIR . 'serialized_dictionaryRO.txt');
        @unlink($DIR . 'serialized_dictionaryEN.txt');

        echo "Spell dictionary built successfully!";
    }
}