<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Partners_creator_model extends CI_Model
{
    private $table = 'creator_partner_links';

    public function isAvailable()
    {
        try { return $this->db->table_exists($this->table); }
        catch (Throwable $error) { return false; }
    }

    public function getForClient($clientId)
    {
        if ((int) $clientId <= 0 || !$this->isAvailable()) return null;
        try {
            return $this->db->where('client_id', (int) $clientId)->limit(1)->get($this->table)->row_array() ?: null;
        } catch (Throwable $error) { return null; }
    }

    public function saveLink($clientId, $partnerId, $creatorId, $status, $scopes, $grant, $encryptionKeys)
    {
        if ((int) $clientId <= 0 || !self::isUuid($partnerId) || !preg_match('/^CRE-[0-9]{6}$/', (string) $creatorId)
            || !in_array($status, array('active', 'suspended', 'banned'), true) || !is_array($scopes) || !is_array($encryptionKeys)) return false;
        try {
            $encrypted = Partners_creator_grant_crypto::encrypt($grant, $encryptionKeys);
            $parts = explode('.', $encrypted);
            $now = date('Y-m-d H:i:s');
            $row = array(
                'client_id' => (int) $clientId,
                'partner_id' => strtolower($partnerId),
                'creator_id' => $creatorId,
                'account_type' => 'creator',
                'partner_status' => $status,
                'scopes' => json_encode(array_values($scopes), JSON_UNESCAPED_SLASHES),
                'grant_encrypted' => $encrypted,
                'grant_key_id' => isset($parts[1]) ? $parts[1] : '',
                'status_checked_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            );
            // Do not replace an existing identity mapping on a race or a different Partner.
            if (!$this->db->insert($this->table, $row)) return false;
            return true;
        } catch (Throwable $error) { return false; }
    }

    public function updateStatus($clientId, $status, $creatorId = null)
    {
        if ((int) $clientId <= 0 || !in_array($status, array('active', 'suspended', 'banned'), true) || !$this->isAvailable()) return false;
        $data = array('partner_status' => $status, 'status_checked_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'));
        if (is_string($creatorId) && preg_match('/^CRE-[0-9]{6}$/', $creatorId)) $data['creator_id'] = $creatorId;
        try { return $this->db->where('client_id', (int) $clientId)->update($this->table, $data); }
        catch (Throwable $error) { return false; }
    }

    public function deleteForClient($clientId)
    {
        if ((int) $clientId <= 0 || !$this->isAvailable()) return false;
        try { return $this->db->where('client_id', (int) $clientId)->delete($this->table); }
        catch (Throwable $error) { return false; }
    }

    public function grantToken($row, $encryptionKeys)
    {
        if (!is_array($row) || empty($row['grant_encrypted']) || !is_array($encryptionKeys)) return false;
        try { return Partners_creator_grant_crypto::decrypt($row['grant_encrypted'], $encryptionKeys); }
        catch (Throwable $error) { return false; }
    }

    public static function isUuid($value)
    {
        return is_string($value) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value) === 1;
    }
}
