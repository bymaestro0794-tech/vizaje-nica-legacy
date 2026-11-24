<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Creator_feedback_model extends CI_Model
{
    public function isAvailable()
    {
        return $this->db->table_exists('creator_feedback_messages');
    }

    public function submit($client, $type, $message, $locale)
    {
        if (!$this->isAvailable()) return 'unavailable';
        $clientId = (int) ($client->id ?? 0);
        $creator = $this->db->select('id, is_creator, active')->where('id', $clientId)->limit(1)->get('clients')->row_array();
        if (!$creator || (int) $creator['is_creator'] !== 1 || (int) $creator['active'] !== 1) return 'not_creator';

        $this->db->insert('creator_feedback_messages', array(
            'client_id' => $clientId,
            'name' => trim((string) ($client->name ?? '') . ' ' . (string) ($client->surname ?? '')),
            'email' => trim((string) ($client->email ?? '')),
            'phone' => trim((string) ($client->phone ?? '')),
            'topic' => $type,
            'message' => trim((string) $message),
            'locale' => $locale === 'ro' ? 'ro' : 'ru',
            'status' => 'new',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ));
        return $this->db->affected_rows() === 1 ? 'created' : 'error';
    }

    public function listMessages($status = '')
    {
        if (!$this->isAvailable()) return array();
        if (in_array($status, array('new', 'in_progress', 'closed'), true)) $this->db->where('m.status', $status);
        return $this->db->select('m.*')->from('creator_feedback_messages m')
            ->order_by("FIELD(m.status, 'new', 'in_progress', 'closed')", '', false)
            ->order_by('m.created_at', 'DESC')->get()->result_array();
    }

    public function getMessage($id)
    {
        if (!$this->isAvailable()) return null;
        return $this->db->where('id', (int) $id)->limit(1)->get('creator_feedback_messages')->row_array();
    }

    public function updateMessage($id, $status, $note, $adminId)
    {
        if (!$this->isAvailable() || !in_array($status, array('new', 'in_progress', 'closed'), true)) return false;
        if (!$this->getMessage($id)) return false;
        $this->db->where('id', (int) $id)->update('creator_feedback_messages', array(
            'status' => $status,
            'admin_note' => $note !== '' ? $note : null,
            'reviewer_admin_id' => (int) $adminId,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ));
        return $this->db->trans_status() !== false;
    }
}
