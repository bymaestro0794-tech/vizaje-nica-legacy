<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Site_feedback_model extends CI_Model
{
    public function insert_feedback(array $feedback)
    {
        return $this->db->insert('site_feedback', $feedback);
    }

    public function insert_attachment($feedbackId, array $attachment)
    {
        return $this->db->insert('site_feedback_images', array(
            'feedback_id' => (int) $feedbackId,
            'file_path' => $attachment['file_path'],
            'original_name' => isset($attachment['original_name']) ? $attachment['original_name'] : null,
            'created_at' => date('Y-m-d H:i:s'),
        ));
    }

    public function get_attachments($feedbackId)
    {
        return $this->db
            ->select('id, file_path, original_name')
            ->from('site_feedback_images')
            ->where('feedback_id', (int) $feedbackId)
            ->order_by('id', 'ASC')
            ->get()
            ->result_array();
    }

    public function get_attachment($id)
    {
        return $this->db
            ->select('id, feedback_id, file_path, original_name')
            ->from('site_feedback_images')
            ->where('id', (int) $id)
            ->limit(1)
            ->get()
            ->row_array();
    }

    public function get_feedback_item($id)
    {
        $row = $this->db
            ->from('site_feedback')
            ->where('id', (int) $id)
            ->limit(1)
            ->get()
            ->row_array();

        if (!$row) {
            return null;
        }

        $row['attachments'] = $this->get_attachments($row['id']);
        return $row;
    }

    public function get_feedback($page = 1, $perPage = 30, $status = null, $type = null)
    {
        $page = max(1, (int) $page);
        $perPage = max(1, min(100, (int) $perPage));
        $offset = ($page - 1) * $perPage;

        if (in_array($status, array('new', 'in_progress', 'done', 'rejected'), true)) {
            $this->db->where('status', $status);
        } else {
            $status = null;
        }

        if (in_array($type, array('idea', 'bug', 'question'), true)) {
            $this->db->where('feedback_type', $type);
        } else {
            $type = null;
        }

        $total = (int) $this->db->count_all_results('site_feedback');

        if ($status !== null) {
            $this->db->where('status', $status);
        }
        if ($type !== null) {
            $this->db->where('feedback_type', $type);
        }

        $rows = $this->db
            ->from('site_feedback')
            ->order_by('created_at', 'DESC')
            ->limit($perPage, $offset)
            ->get()
            ->result_array();

        foreach ($rows as &$row) {
            $row['attachments'] = $this->get_attachments($row['id']);
        }
        unset($row);

        return array(
            'rows' => $rows,
            'pagination' => array(
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $total > 0 ? (int) ceil($total / $perPage) : 1,
            ),
        );
    }

    public function update_status($id, $status)
    {
        if (!in_array($status, array('new', 'in_progress', 'done', 'rejected'), true)) {
            return false;
        }

        return $this->db
            ->where('id', (int) $id)
            ->update('site_feedback', array(
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s'),
            ));
    }
}
