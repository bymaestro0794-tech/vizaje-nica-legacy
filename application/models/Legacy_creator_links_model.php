<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Legacy_creator_links_model extends CI_Model
{
    private $table = 'legacy_creator_referral_links';

    public function isAvailable()
    {
        $previousDbDebug = $this->db->db_debug;
        try {
            $this->db->db_debug = false;
            return $this->db->table_exists($this->table);
        } catch (Throwable $error) {
            return false;
        } finally {
            $this->db->db_debug = $previousDbDebug;
        }
    }

    public function listForClient($clientId)
    {
        if (!$this->isAvailable()) return array();
        $previousDbDebug = $this->db->db_debug;
        try {
            $this->db->db_debug = false;
            if (!$this->db->table_exists('legacy_creator_referral_attributions')
                || !$this->db->field_exists('legacy_creator_referral_attribution_id', 'orders')) {
                return $this->db->where('client_id', (int) $clientId)->where('status', 'active')->order_by('id', 'DESC')->limit(100)
                    ->get($this->table)->result_array();
            }
            return $this->db->query(
                'SELECT l.*, (SELECT COUNT(*) FROM orders o '
                . 'INNER JOIN legacy_creator_referral_attributions a ON a.id = o.legacy_creator_referral_attribution_id '
                . 'WHERE a.link_id = l.id AND o.status = ?) AS finished_orders '
                . 'FROM `' . $this->table . '` l WHERE l.client_id = ? AND l.status = ? ORDER BY l.id DESC LIMIT 100',
                array('finished', (int) $clientId, 'active')
            )->result_array();
        } catch (Throwable $error) {
            return array();
        } finally {
            $this->db->db_debug = $previousDbDebug;
        }
    }

    public function create($clientId, $label, $type, $locale, $targetUrl)
    {
        if (!$this->isAvailable()) return false;
        $previousDbDebug = $this->db->db_debug;
        try {
            $this->db->db_debug = false;
            for ($attempt = 0; $attempt < 3; $attempt++) {
                $slug = rtrim(strtr(base64_encode(random_bytes(9)), '+/', '-_'), '=');
                $now = date('Y-m-d H:i:s');
                $inserted = $this->db->insert($this->table, array(
                    'client_id' => (int) $clientId,
                    'code' => $slug,
                    'label' => $label !== '' ? $label : null,
                    'link_type' => $type,
                    'locale' => $locale,
                    'target_url' => $targetUrl,
                    'status' => 'active',
                    'click_count' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ));
                $newId = (int) $this->db->insert_id();
                if ($inserted && $this->db->affected_rows() === 1 && $newId > 0) {
                    return $this->db->where('id', $newId)->get($this->table)->row_array();
                }
            }
        } catch (Throwable $error) {
            return false;
        } finally {
            $this->db->db_debug = $previousDbDebug;
        }
        return false;
    }

    public function revoke($clientId, $id)
    {
        if (!$this->isAvailable()) return false;
        $previousDbDebug = $this->db->db_debug;
        try {
            $this->db->db_debug = false;
            $this->db->where('id', (int) $id)->where('client_id', (int) $clientId)->where('status', 'active')
                ->update($this->table, array('status' => 'revoked', 'updated_at' => date('Y-m-d H:i:s')));
            return $this->db->affected_rows() === 1;
        } catch (Throwable $error) {
            return false;
        } finally {
            $this->db->db_debug = $previousDbDebug;
        }
    }

    public function registerClickAndGetTarget($code)
    {
        if (!$this->isAvailable() || !is_string($code) || !preg_match('/^[A-Za-z0-9_-]{12}$/', $code)) return null;
        $previousDbDebug = $this->db->db_debug;
        try {
            $this->db->db_debug = false;
            $this->db->set('click_count', 'click_count + 1', false)->set('updated_at', date('Y-m-d H:i:s'))
                ->where('code', $code)->where('status', 'active')->update($this->table);
            if ($this->db->affected_rows() !== 1) return null;
            return $this->db->select('id, target_url')->where('code', $code)->where('status', 'active')->limit(1)
                ->get($this->table)->row_array();
        } catch (Throwable $error) {
            return null;
        } finally {
            $this->db->db_debug = $previousDbDebug;
        }
    }

    public function present(array $row)
    {
        return array(
            'id' => (string) (int) $row['id'],
            'type' => (string) $row['link_type'],
            'title' => (string) ($row['label'] ?? ''),
            'short_url' => '/r/' . rawurlencode((string) $row['code']),
            'target_url' => (string) $row['target_url'],
            'locale' => (string) $row['locale'],
            'created_at' => (string) $row['created_at'],
            'status' => (string) $row['status'],
            'clicks' => (int) $row['click_count'],
            'orders' => (int) ($row['finished_orders'] ?? 0),
        );
    }
}
