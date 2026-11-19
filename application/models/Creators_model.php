<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Creators_model extends CI_Model
{
    public function listCreators($search = '', $state = 'all', $limit = 200)
    {
        if (!$this->db->field_exists('is_creator', 'clients')) return array();

        $this->db->select('id, name, surname, email, phone, active, is_creator');
        if ($this->db->field_exists('creator_commission_percent', 'clients')) {
            $this->db->select('creator_commission_percent');
        }
        if ($this->db->field_exists('creator_b2c_mode', 'clients')) {
            $this->db->select('creator_b2c_mode');
        }
        if ($this->db->field_exists('creator_widget_enabled', 'clients')) {
            $this->db->select('creator_widget_enabled');
        }
        $this->db->from('clients')->where('is_creator', 1);

        if ($state === 'active') $this->db->where('active', 1);
        if ($state === 'inactive') $this->db->where('active <>', 1);

        $search = trim((string) $search);
        if ($search !== '') {
            $this->db->group_start();
            if (ctype_digit($search)) {
                $this->db->where('id', (int) $search)->or_like('name', $search);
            } else {
                $this->db->like('name', $search);
            }
            $this->db->or_like('surname', $search)
                ->or_like('email', $search)
                ->or_like('phone', $search)
                ->group_end();
        }

        return $this->db->order_by('id', 'DESC')
            ->limit(max(1, min(500, (int) $limit)))
            ->get()->result_array();
    }
}
