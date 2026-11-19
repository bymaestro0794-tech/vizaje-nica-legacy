<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Creator_payouts_model extends CI_Model
{
    public function isAvailable()
    {
        return $this->db->table_exists('creator_payout_requests')
            && $this->db->table_exists('legacy_creator_referral_attributions')
            && $this->db->table_exists('legacy_creator_referral_links')
            && $this->db->field_exists('is_creator', 'clients')
            && $this->db->field_exists('legacy_creator_referral_attribution_id', 'orders')
            && $this->db->field_exists('creator_commission_amount', 'orders')
            && $this->db->field_exists('expected_payment_date', 'creator_payout_requests');
    }

    public function balanceForClient($clientId)
    {
        if (!$this->isAvailable()) return 0.0;
        $earned = $this->db->query(
            'SELECT COALESCE(SUM(o.creator_commission_amount), 0) AS amount '
            . 'FROM orders o JOIN legacy_creator_referral_attributions a '
            . 'ON a.id = o.legacy_creator_referral_attribution_id '
            . 'JOIN legacy_creator_referral_links l ON l.id = a.link_id '
            . 'WHERE l.client_id = ? AND o.status = ?', array((int) $clientId, 'finished')
        )->row_array();
        $reserved = $this->db->query(
            'SELECT COALESCE(SUM(amount_mdl), 0) AS amount FROM creator_payout_requests '
            . 'WHERE client_id = ? AND status IN (?, ?, ?)', array((int) $clientId, 'pending', 'approved', 'paid')
        )->row_array();
        return max(0, round((float) ($earned['amount'] ?? 0) - (float) ($reserved['amount'] ?? 0), 2));
    }

    public function listForClient($clientId)
    {
        if (!$this->db->table_exists('creator_payout_requests')) return array();
        return $this->db->where('client_id', (int) $clientId)
            ->order_by('created_at', 'DESC')->limit(50)
            ->get('creator_payout_requests')->result_array();
    }

    public function submit($clientId, $amount, $comment)
    {
        if (!$this->isAvailable()) return 'unavailable';
        $this->db->trans_begin();
        try {
            $client = $this->db->query('SELECT id, is_creator, active FROM clients WHERE id = ? FOR UPDATE', array((int) $clientId))->row_array();
            if (!$client || (int) $client['active'] !== 1 || (int) $client['is_creator'] !== 1) {
                $this->db->trans_rollback();
                return 'not_creator';
            }
            $pending = $this->db->where('client_id', (int) $clientId)->where('status', 'pending')
                ->limit(1)->get('creator_payout_requests')->row_array();
            if ($pending) {
                $this->db->trans_rollback();
                return 'pending_exists';
            }
            $amount = round((float) $amount, 2);
            if ($amount <= 0 || $amount > $this->balanceForClient($clientId)) {
                $this->db->trans_rollback();
                return 'insufficient_balance';
            }
            $nextMonth = new DateTimeImmutable('first day of next month');
            $expectedPaymentDate = $nextMonth->setDate((int) $nextMonth->format('Y'), (int) $nextMonth->format('n'), 15)->format('Y-m-d');
            $this->db->insert('creator_payout_requests', array(
                'client_id' => (int) $clientId,
                'amount_mdl' => number_format($amount, 2, '.', ''),
                'comment' => $comment !== '' ? $comment : null,
                'expected_payment_date' => $expectedPaymentDate,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ));
            if ($this->db->affected_rows() !== 1 || $this->db->trans_status() === false) throw new RuntimeException('insert_failed');
            $this->db->trans_commit();
            return 'created';
        } catch (Throwable $error) {
            $this->db->trans_rollback();
            log_message('error', 'Creator payout request submission failed: ' . get_class($error));
            return 'error';
        }
    }

    public function listRequests($status = '')
    {
        if (!$this->db->table_exists('creator_payout_requests')) return array();
        $this->db->select('r.*, c.name, c.surname, c.email, c.phone, c.sex')
            ->from('creator_payout_requests r')->join('clients c', 'c.id = r.client_id', 'left');
        if (in_array($status, array('pending', 'approved', 'rejected', 'paid'), true)) $this->db->where('r.status', $status);
        return $this->db->order_by("FIELD(r.status, 'pending', 'approved', 'rejected', 'paid')", '', false)
            ->order_by('r.created_at', 'DESC')->get()->result_array();
    }

    public function getRequest($id)
    {
        if (!$this->db->table_exists('creator_payout_requests')) return null;
        return $this->db->select('r.*, c.name, c.surname, c.email, c.phone, c.sex')
            ->from('creator_payout_requests r')->join('clients c', 'c.id = r.client_id', 'left')
            ->where('r.id', (int) $id)->limit(1)->get()->row_array();
    }

    public function decide($id, $decision, $note, $adminId)
    {
        if (!$this->db->table_exists('creator_payout_requests')) return 'unavailable';
        $this->db->trans_begin();
        try {
            $request = $this->db->query('SELECT id, status FROM creator_payout_requests WHERE id = ? FOR UPDATE', array((int) $id))->row_array();
            if (!$request) { $this->db->trans_rollback(); return 'not_found'; }
            if ($request['status'] !== 'pending') { $this->db->trans_rollback(); return 'already_decided'; }
            $this->db->where('id', (int) $id)->where('status', 'pending')->update('creator_payout_requests', array(
                'status' => $decision,
                'admin_comment' => $note !== '' ? $note : null,
                'reviewer_admin_id' => (int) $adminId,
                'reviewed_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ));
            if ($this->db->affected_rows() !== 1 || $this->db->trans_status() === false) throw new RuntimeException('decision_failed');
            $this->db->trans_commit();
            return 'decided';
        } catch (Throwable $error) {
            $this->db->trans_rollback();
            log_message('error', 'Creator payout request decision failed: ' . get_class($error));
            return 'error';
        }
    }

    public function markPaid($id, $adminId, $note = '')
    {
        if (!$this->db->table_exists('creator_payout_requests')) return 'unavailable';
        $this->db->where('id', (int) $id)->where('status', 'approved')->update('creator_payout_requests', array(
            'status' => 'paid',
            'admin_comment' => $note !== '' ? $note : null,
            'reviewer_admin_id' => (int) $adminId,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ));
        return $this->db->affected_rows() === 1 ? 'paid' : 'already_decided';
    }
}
