<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Gift_promotions extends BackEndController
{
    private $model;

    public function __construct()
    {
        parent::__construct(__CLASS__);
        $this->load->model('gift_promotions_model');
        $this->load->model('gifts_model');
        $this->model =& $this->gift_promotions_model;
        $this->data['title'] = 'Подарочные акции';
        $this->data['add'] = 'Добавить подарочную акцию';
    }

    public function index()
    {
        $this->data['migration_required'] = !$this->db->table_exists('gift_promotions')
            || !$this->db->table_exists('gift_promotion_brands')
            || !$this->db->table_exists('gifts');
        $this->data['objects'] = $this->data['migration_required'] ? array() : $this->model->get_admin();
        $this->data['inner_view'] = 'dashboard/gift_promotions/index';
        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }

    public function put()
    {
        check_if_POST();
        try {
            $this->ensure_tables();
            $post = $this->read_post();
            $brandIds = $post['brand_ids'];
            unset($post['brand_ids']);
            $now = date('Y-m-d H:i:s');
            $post['created_at'] = $now;
            $post['updated_at'] = $now;
            if (!$this->model->put($post)) {
                throw new Exception('Не удалось создать подарочную акцию.');
            }
            $this->model->replace_brands($this->db->insert_id(), $brandIds);
            $_SESSION['success'] = 'Подарочная акция создана.';
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            $_SESSION['error'] = array($e->getMessage());
        }
        redirect($this->path);
    }

    public function item($id = 0)
    {
        $this->ensure_tables();
        $id = (int) $id;
        $item = $id > 0 ? $this->model->find_first_admin($id) : (object) array(
            'id' => 0,
            'titleRU' => '',
            'titleRO' => '',
            'descriptionRU' => '',
            'descriptionRO' => '',
            'condition_qty' => 2,
            'is_online' => 1,
            'progress_mode' => 'time',
            'is_enabled' => 1,
            'show_on_site' => 1,
            'sorder' => 0,
        );
        if ($id > 0 && empty($item)) {
            throw_on_404();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $post = $this->read_post();
                $brandIds = $post['brand_ids'];
                unset($post['brand_ids']);
                $post['updated_at'] = date('Y-m-d H:i:s');
                if ($id < 1) {
                    throw new Exception('Для новой акции используйте форму создания.');
                }
                if (!$this->model->update($post, $id)) {
                    throw new Exception('Не удалось обновить подарочную акцию.');
                }
                $this->model->replace_brands($id, $brandIds);
                $_SESSION['success'] = 'Подарочная акция обновлена.';
                $item = $this->model->find_first_admin($id);
            } catch (Exception $e) {
                log_message('error', $e->getMessage());
                $_SESSION['error'] = array($e->getMessage());
            }
        }

        $this->data['item'] = $item;
        $this->data['selected_brands'] = $id > 0 ? $this->model->get_brand_ids($id) : array();
        $this->data['brands'] = $this->db->select('id, title')->order_by('title ASC')->get('brands')->result();
        $this->data['gifts'] = $this->gifts_model->get_admin();
        $this->data['offers'] = $this->db->select('id, titleRU, titleRO')->where('isShown', 1)->order_by('titleRU ASC')->get('offers')->result();
        $this->data['inner_view'] = 'dashboard/gift_promotions/item';
        $this->data['title'] = 'Редактировать подарочную акцию';
        $this->data['parent_url'] = $this->path;
        $this->data['parent_title'] = 'Подарочные акции';
        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }

    public function delete($id = 0)
    {
        $this->ensure_tables();
        $id = (int) $id;
        if (empty($this->model->find_first($id))) {
            throw_on_404();
        }
        $this->db->delete('gift_promotion_brands', array('promotion_id' => $id));
        $this->model->delete($id);
        $_SESSION['success'] = 'Подарочная акция удалена.';
        redirect($this->path);
    }

    public function update_order()
    {
        check_if_POST();
        $this->ensure_tables();
        $this->model->update_sorder($this->input->post('so'));
        $_SESSION['success'] = 'Порядок обновлён.';
        redirect($this->path);
    }

    private function ensure_tables()
    {
        if (!$this->db->table_exists('gift_promotions') || !$this->db->table_exists('gift_promotion_brands')) {
            throw new Exception('Сначала примените SQL-патч database/patches/2026-10-23-create-gift-promotions.sql.');
        }
        if (!$this->db->table_exists('gifts')) {
            throw new Exception('Сначала примените SQL-патч database/patches/2026-10-23-create-gifts.sql.');
        }
    }

    private function read_post()
    {
        $titleRU = trim((string) $this->input->post('titleRU', true));
        $titleRO = trim((string) $this->input->post('titleRO', true));
        $giftId = (int) $this->input->post('gift_id');
        $brandIds = array_filter(array_map('intval', (array) $this->input->post('brand_ids')));
        $channel = (string) $this->input->post('is_online', true);

        if ($titleRU === '' || $titleRO === '') {
            throw new Exception('Заполните название акции на русском и румынском языках.');
        }
        if (count($brandIds) < 1) {
            throw new Exception('Выберите хотя бы один бренд-условие.');
        }
        if ($giftId < 1 || empty($this->gifts_model->find_first_admin($giftId))) {
            throw new Exception('Выберите подарок из каталога подарков.');
        }
        if (!in_array($channel, array('0', '1', '2'), true)) {
            throw new Exception('Выберите формат акции: онлайн, офлайн или оба канала.');
        }

        $dateFrom = $this->normalizeOptionalDate($this->input->post('date_from', true), 'начала');
        $dateTo = $this->normalizeOptionalDate($this->input->post('date_to', true), 'окончания');
        if ($dateFrom !== null && $dateTo !== null && $dateFrom > $dateTo) {
            throw new Exception('Дата начала не может быть позже даты окончания.');
        }

        $result = array(
            'titleRU' => $titleRU,
            'titleRO' => $titleRO,
            'descriptionRU' => $this->input->post('descriptionRU', true),
            'descriptionRO' => $this->input->post('descriptionRO', true),
            'gift_id' => $giftId,
            'offer_id' => (int) $this->input->post('offer_id') ?: null,
            'condition_qty' => max(1, (int) $this->input->post('condition_qty')),
            'is_enabled' => $this->input->post('is_enabled') ? 1 : 0,
            'show_on_site' => $this->input->post('show_on_site') ? 1 : 0,
            'is_online' => (int) $channel,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'sorder' => (int) $this->input->post('sorder'),
            'brand_ids' => $brandIds,
        );

        $progressMode = $this->normalizeProgressMode($this->input->post('progress_mode', true));
        if ($progressMode !== null) {
            $result['progress_mode'] = $progressMode;
        }

        return $result;
    }

    private function normalizeOptionalDate($value, $label)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $date = DateTime::createFromFormat('!Y-m-d', $value);
        if (!$date || $date->format('Y-m-d') !== $value) {
            throw new Exception('Проверьте дату ' . $label . ' акции.');
        }

        return $value;
    }

    private function normalizeProgressMode($value)
    {
        $value = (string) $value;
        if (!in_array($value, array('time', 'stock', 'none'), true)) {
            $value = 'time';
        }

        if (!$this->db->field_exists('progress_mode', 'gift_promotions')) {
            return null;
        }

        return $value;
    }
}
