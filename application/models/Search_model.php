<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Search_model extends CI_Model
{
    /**
     * Основной поиск ID товаров через FULLTEXT
     */
    public function search_ids($query, $limit = 100)
    {
        $query = $this->prepare_query($query);

        if (empty($query)) {
            return [];
        }

        $sql = "
            SELECT product_id
            FROM product_search
            WHERE MATCH(title, SKU, GUID, color, volume, search_text)
            AGAINST (? IN BOOLEAN MODE)
            LIMIT ?
        ";

        $result = $this->db->query($sql, [$query, (int)$limit])->result();

        return $this->extract_ids($result);
    }


    /**
     * Более мягкий fallback (LIKE поиск)
     */
    public function search_ids_like($query, $limit = 100)
    {
        $query = $this->prepare_query($query);

        if (empty($query)) {
            return [];
        }

        $this->db->select('product_id');

        $this->db->group_start();
        $this->db->like('title', $query);
        $this->db->or_like('SKU', $query);
        $this->db->or_like('GUID', $query);
        $this->db->or_like('search_text', $query);
        $this->db->group_end();

        $this->db->limit($limit);

        $result = $this->db->get('product_search')->result();

        return $this->extract_ids($result);
    }


    /**
     * Универсальный поиск (умный fallback chain)
     */
    public function smart_search_ids($query)
    {
        $ids = $this->search_ids($query);

        if (!empty($ids)) {
            return $ids;
        }

        return $this->search_ids_like($query);
    }


    /**
     * Подготовка запроса (очистка + boolean mode boost)
     */
    private function prepare_query($query)
    {
        $query = trim(mb_strtolower($query));

        if (empty($query)) {
            return '';
        }

        // чистка мусора
        $query = preg_replace('/[^a-zа-яё0-9\s]/iu', ' ', $query);
        $query = preg_replace('/\s+/', ' ', $query);

        $words = explode(' ', $query);

        $prepared = [];

        foreach ($words as $word) {
            if (mb_strlen($word) < 2) continue;

            // BOOST: обязательное совпадение + wildcard
            $prepared[] = '+' . $word . '*';
        }

        return implode(' ', $prepared);
    }


    /**
     * Извлечение ID из результата
     */
    private function extract_ids($result)
    {
        $ids = [];

        foreach ($result as $row) {
            $ids[] = (int)$row->product_id;
        }

        return array_values(array_unique($ids));
    }
}