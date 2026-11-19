<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Creator_program_model extends CI_Model
{
    public function isAvailable()
    {
        return $this->db->table_exists('creator_program_applications')
            && $this->db->table_exists('creator_program_email_outbox')
            && $this->db->field_exists('sex', 'creator_program_applications');
    }

    public function getForClient($clientId)
    {
        if (!$this->db->table_exists('creator_program_applications')) return null;
        return $this->db->where('client_id', (int) $clientId)
            ->order_by('id', 'DESC')->limit(1)
            ->get('creator_program_applications')->row_array();
    }

    public function submitApplication($client, array $input, $locale)
    {
        if (!$this->isAvailable() || !$this->db->field_exists('is_creator', 'clients')) return 'unavailable';
        $clientId = (int) $client->id;
        $this->db->trans_begin();
        try {
            // Serialize submissions for one Legacy account, including concurrent requests.
            $lockedClient = $this->db->query('SELECT id FROM clients WHERE id = ? FOR UPDATE', array($clientId))->row();
            if (!$lockedClient) throw new RuntimeException('client_missing');
            $active = $this->db->where('client_id', $clientId)
                ->where_in('status', array('pending', 'approved'))
                ->limit(1)->get('creator_program_applications')->row();
            if ($active) {
                $this->db->trans_commit();
                return 'already_exists';
            }

            $now = date('Y-m-d H:i:s');
            $this->db->insert('creator_program_applications', array(
                'client_id' => $clientId,
                'full_name' => trim((string) $client->name . ' ' . (string) ($client->surname ?? '')),
                'email' => trim((string) $client->email),
                'phone' => trim((string) $client->phone),
                'date_of_birth' => $input['date_of_birth'],
                'sex' => $input['sex'] !== '' ? (int) $input['sex'] : null,
                'audience_size' => $input['audience_size'],
                'partnership_format' => $input['partnership_format'],
                'instagram' => $input['instagram'] !== '' ? $input['instagram'] : null,
                'tiktok' => $input['tiktok'] !== '' ? $input['tiktok'] : null,
                'other_social' => $input['other_social'] !== '' ? $input['other_social'] : null,
                'content_description' => $input['content_description'],
                'partnership_reason' => $input['partnership_reason'],
                'consent_at' => $now,
                'locale' => $locale,
                'status' => 'pending',
                'created_at' => $now,
                'updated_at' => $now,
            ));
            if ($this->db->affected_rows() !== 1) throw new RuntimeException('application_insert_failed');
            $this->db->trans_commit();
            return 'created';
        } catch (Throwable $error) {
            $this->db->trans_rollback();
            log_message('error', 'Creator application submission failed: ' . get_class($error));
            return 'error';
        }
    }

    public function listApplications($status = '')
    {
        if (!$this->isAvailable()) return array();
        $this->db->select('a.*, c.is_creator')
            ->from('creator_program_applications a')
            ->join('clients c', 'c.id = a.client_id', 'left');
        if (in_array($status, array('pending', 'approved', 'rejected'), true)) $this->db->where('a.status', $status);
        return $this->db->order_by("FIELD(a.status, 'pending', 'approved', 'rejected')", '', false)
            ->order_by('a.created_at', 'DESC')->get()->result_array();
    }

    public function getApplication($id)
    {
        if (!$this->isAvailable()) return null;
        return $this->db->select('a.*, c.is_creator')->from('creator_program_applications a')
            ->join('clients c', 'c.id = a.client_id', 'left')
            ->where('a.id', (int) $id)->limit(1)->get()->row_array();
    }

    public function decide($id, $decision, $reason, $adminId)
    {
        if (!$this->isAvailable() || !$this->db->field_exists('is_creator', 'clients')) return 'unavailable';
        $this->db->trans_begin();
        try {
            $application = $this->db->query(
                'SELECT * FROM creator_program_applications WHERE id = ? FOR UPDATE',
                array((int) $id)
            )->row_array();
            if (!$application) {
                $this->db->trans_rollback();
                return 'not_found';
            }
            if ($application['status'] !== 'pending') {
                $this->db->trans_rollback();
                return 'already_decided';
            }
            $now = date('Y-m-d H:i:s');
            $this->db->where('id', (int) $id)->update('creator_program_applications', array(
                'status' => $decision,
                'reviewer_admin_id' => (int) $adminId,
                'reviewed_at' => $now,
                'rejection_reason' => $decision === 'rejected' ? $reason : null,
                'updated_at' => $now,
            ));
            // Approval enables the existing Legacy creator mode; rejection never revokes a pre-existing role.
            if ($decision === 'approved') {
                $this->db->where('id', (int) $application['client_id'])->update('clients', array('is_creator' => 1));
            }
            $this->db->insert('creator_program_email_outbox', array(
                'application_id' => (int) $id,
                'event_type' => $decision,
                'status' => 'queued',
                'created_at' => $now,
                'updated_at' => $now,
            ));
            if ($this->db->trans_status() === false) throw new RuntimeException('decision_transaction_failed');
            $this->db->trans_commit();
            return 'decided';
        } catch (Throwable $error) {
            $this->db->trans_rollback();
            log_message('error', 'Creator application decision failed: ' . get_class($error));
            return 'error';
        }
    }

    public function getEmailOutbox($applicationId)
    {
        if (!$this->db->table_exists('creator_program_email_outbox')) return null;
        $this->recoverStaleEmailClaim($this->db->where('application_id', (int) $applicationId)
            ->order_by('id', 'DESC')->limit(1)->get('creator_program_email_outbox')->row_array());
        return $this->db->where('application_id', (int) $applicationId)->order_by('id', 'DESC')->limit(1)
            ->get('creator_program_email_outbox')->row_array();
    }

    public function getEmailOutboxById($id)
    {
        if (!$this->db->table_exists('creator_program_email_outbox')) return null;
        $this->recoverStaleEmailClaim($this->db->where('id', (int) $id)->limit(1)
            ->get('creator_program_email_outbox')->row_array());
        return $this->db->where('id', (int) $id)->limit(1)
            ->get('creator_program_email_outbox')->row_array();
    }

    private function recoverStaleEmailClaim($outbox)
    {
        if (!$outbox || $outbox['status'] !== 'sending' || empty($outbox['last_attempt_at'])
            || strtotime($outbox['last_attempt_at']) > time() - 900) {
            return;
        }
        // A PHP worker may have died after claiming the row. Make it retryable after 15 minutes.
        $this->db->where('id', (int) $outbox['id'])
            ->where('status', 'sending')
            ->where('last_attempt_at', $outbox['last_attempt_at'])
            ->update('creator_program_email_outbox', array(
                'status' => 'failed',
                'last_error' => 'STALE_DELIVERY_ATTEMPT',
                'updated_at' => date('Y-m-d H:i:s'),
            ));
    }

    public function claimEmail($outboxId)
    {
        $now = date('Y-m-d H:i:s');
        $this->db->set('attempt_count', 'attempt_count + 1', false)
            ->set('status', 'sending')
            ->set('last_attempt_at', $now)
            ->set('last_error', null)
            ->set('updated_at', $now)
            ->where('id', (int) $outboxId)
            ->where_in('status', array('queued', 'failed'))
            ->update('creator_program_email_outbox');
        if ($this->db->affected_rows() !== 1) return null;
        return $this->db->where('id', (int) $outboxId)->get('creator_program_email_outbox')->row_array();
    }

    public function finishEmail($outboxId, $success)
    {
        $now = date('Y-m-d H:i:s');
        return $this->db->where('id', (int) $outboxId)->update('creator_program_email_outbox', array(
            'status' => $success ? 'sent' : 'failed',
            'sent_at' => $success ? $now : null,
            'last_error' => $success ? null : 'SMTP_DELIVERY_FAILED',
            'updated_at' => $now,
        ));
    }
}
