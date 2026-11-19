<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** First-party creator attribution for one order, limited to seven days. */
class Legacy_creator_attribution_model extends CI_Model
{
    const COOKIE_NAME = 'legacy_creator_referral';
    const TTL_SECONDS = 604800;
    private $table = 'legacy_creator_referral_attributions';

    public function isAvailable()
    {
        $previousDbDebug = $this->db->db_debug;
        try {
            $this->db->db_debug = false;
            return $this->db->table_exists($this->table)
                && $this->db->table_exists('legacy_creator_referral_links')
                && $this->db->field_exists('legacy_creator_referral_attribution_id', 'orders');
        } catch (Throwable $error) {
            return false;
        } finally {
            $this->db->db_debug = $previousDbDebug;
        }
    }

    public function issueForLink($linkId)
    {
        if ((int) $linkId <= 0) return false;
        if (session_status() !== PHP_SESSION_ACTIVE && !@session_start()) return false;
        $oldAttribution = $_SESSION['legacy_creator_referral_attribution'] ?? array();
        $oldToken = is_array($oldAttribution) ? (string) ($oldAttribution['token'] ?? '') : '';
        if ($oldToken === '') $oldToken = (string) ($_COOKIE[self::COOKIE_NAME] ?? '');
        try {
            $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        } catch (Throwable $error) {
            return false;
        }
        $hash = hash('sha256', $token);
        $previousDbDebug = $this->db->db_debug;
        try {
            $this->db->db_debug = false;
            if (!$this->db->table_exists($this->table)) return false;
            $this->withdrawUnusedToken($oldToken);
            $this->cleanupExpired();
            $ok = $this->db->query(
                'INSERT INTO `' . $this->table . '` (link_id, token_hash, expires_at, consented_at, created_at) '
                . 'VALUES (?, ?, DATE_ADD(UTC_TIMESTAMP(3), INTERVAL 7 DAY), NULL, UTC_TIMESTAMP(3))',
                array((int) $linkId, $hash)
            );
            if (!$ok || $this->db->affected_rows() !== 1) return false;
            $_SESSION['legacy_creator_referral_attribution'] = array(
                'token' => $token,
                'expires_at' => time() + self::TTL_SECONDS,
            );
            return true;
        } catch (Throwable $error) {
            return false;
        } finally {
            $this->db->db_debug = $previousDbDebug;
        }
    }

    /** Atomically consume one unexpired attribution for one fully persisted order. */
    public function claimForOrder($orderId, $token, $orderCreatedAtMilliseconds)
    {
        if ((int) $orderId <= 0 || !is_string($token) || !preg_match('/^[A-Za-z0-9_-]{43}$/', $token)
            || (int) $orderCreatedAtMilliseconds <= 0) return 'invalid';
        $previousDbDebug = $this->db->db_debug;
        $this->db->db_debug = false;
        try {
            if (!$this->db->table_exists($this->table)
                || !$this->db->field_exists('legacy_creator_referral_attribution_id', 'orders')) return 'unavailable';
            $this->db->trans_begin();
            $orderCreatedAt = $this->utcDateFromMilliseconds((int) $orderCreatedAtMilliseconds);
            $commissionSupported = $this->db->field_exists('creator_commission_percent', 'clients')
                && $this->db->field_exists('creator_commission_percent', 'orders')
                && $this->db->field_exists('creator_commission_amount', 'orders');
            $commissionSelect = $commissionSupported ? ', c.creator_commission_percent' : '';
            $row = $this->db->query(
                'SELECT a.id, a.link_id, l.client_id, c.active, c.is_creator' . $commissionSelect
                . ' FROM `' . $this->table . '` a JOIN `legacy_creator_referral_links` l ON l.id = a.link_id '
                . 'JOIN `clients` c ON c.id = l.client_id '
                . 'WHERE a.token_hash = ? AND a.order_id IS NULL AND a.expires_at > ? FOR UPDATE',
                array(hash('sha256', $token), $orderCreatedAt)
            )->row_array();
            if (!$row) {
                $this->db->trans_rollback();
                return 'expired';
            }

            $orderTotal = $this->db->query('SELECT total FROM `orders` WHERE id = ? FOR UPDATE', array((int) $orderId))->row_array();
            if (!$orderTotal) throw new RuntimeException('order_not_found');
            $commissionPercent = $commissionSupported && (int) $row['active'] === 1 && (int) $row['is_creator'] === 1
                ? max(0, min(100, (float) $row['creator_commission_percent'])) : 0;
            $commissionAmount = round((float) $orderTotal['total'] * $commissionPercent / 100, 2);
            $orderUpdate = array('legacy_creator_referral_attribution_id' => (int) $row['id']);
            if ($commissionSupported) {
                $orderUpdate['creator_commission_percent'] = number_format($commissionPercent, 2, '.', '');
                $orderUpdate['creator_commission_amount'] = number_format($commissionAmount, 2, '.', '');
            }
            $this->db->where('id', (int) $orderId)
                ->where('legacy_creator_referral_attribution_id IS NULL', null, false)
                ->update('orders', $orderUpdate);
            if ($this->db->affected_rows() !== 1) throw new RuntimeException('order_claim_failed');

            $this->db->where('id', (int) $row['id'])->where('order_id IS NULL', null, false)
                ->update($this->table, array('order_id' => (int) $orderId));
            if ($this->db->affected_rows() !== 1 || $this->db->trans_status() === false) {
                throw new RuntimeException('attribution_claim_failed');
            }
            $this->db->trans_commit();
            return $this->db->trans_status() === false ? 'unavailable' : 'claimed';
        } catch (Throwable $error) {
            $this->db->trans_rollback();
            log_message('error', 'Legacy creator referral claim failed: ' . get_class($error));
            return 'unavailable';
        } finally {
            $this->db->db_debug = $previousDbDebug;
        }
    }

    public function withdrawUnusedToken($token)
    {
        if (!is_string($token) || !preg_match('/^[A-Za-z0-9_-]{43}$/', $token)) return false;
        $previousDbDebug = $this->db->db_debug;
        try {
            $this->db->db_debug = false;
            if (!$this->db->table_exists($this->table)) return false;
            return $this->db->where('token_hash', hash('sha256', $token))
                ->where('order_id IS NULL', null, false)->delete($this->table) !== false;
        } catch (Throwable $error) {
            return false;
        } finally {
            $this->db->db_debug = $previousDbDebug;
        }
    }

    public function finishedOrdersForCreator($clientId, $limit = 100)
    {
        if ((int) $clientId <= 0) return array();
        $previousDbDebug = $this->db->db_debug;
        try {
            $this->db->db_debug = false;
            if (!$this->db->table_exists($this->table)
                || !$this->db->table_exists('legacy_creator_referral_links')
                || !$this->db->field_exists('legacy_creator_referral_attribution_id', 'orders')) return array();
            $select = 'o.order_id, o.total, o.updated, o.added, l.label, l.locale';
            $hasCommission = $this->db->field_exists('creator_commission_percent', 'orders')
                && $this->db->field_exists('creator_commission_amount', 'orders');
            if ($hasCommission) $select .= ', o.creator_commission_percent, o.creator_commission_amount';
            $rows = $this->db->select($select)
                ->from('orders o')
                ->join($this->table . ' a', 'a.id = o.legacy_creator_referral_attribution_id', 'inner')
                ->join('legacy_creator_referral_links l', 'l.id = a.link_id', 'inner')
                ->where('l.client_id', (int) $clientId)->where('o.status', 'finished')
                ->order_by('o.updated', 'DESC')->limit(max(1, min(200, (int) $limit)))
                ->get()->result_array();
            foreach ($rows as &$row) {
                if (!$hasCommission) {
                    $row['creator_commission_percent'] = 0;
                    $row['creator_commission_amount'] = 0;
                }
            }
            unset($row);
            return $rows;
        } catch (Throwable $error) {
            return array();
        } finally {
            $this->db->db_debug = $previousDbDebug;
        }
    }

    public function clearCookie()
    {
        unset($_SESSION['legacy_creator_referral_attribution']);
        $this->writeCookie('', time() - 3600);
        unset($_COOKIE[self::COOKIE_NAME]);
    }

    private function cleanupExpired()
    {
        try {
            $this->db->query('DELETE FROM `' . $this->table . '` WHERE order_id IS NULL AND expires_at <= UTC_TIMESTAMP(3) LIMIT 500');
        } catch (Throwable $error) {
            // Expired entries are ineligible even if routine cleanup is unavailable.
        }
    }

    private function writeCookie($value, $expires)
    {
        if (headers_sent()) return false;
        $secure = (bool) $this->config->item('cookie_secure')
            || (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off');
        $result = setcookie(self::COOKIE_NAME, $value, array(
            'expires' => (int) $expires,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ));
        if ($result && $value !== '') $_COOKIE[self::COOKIE_NAME] = $value;
        return $result;
    }

    private function utcDateFromMilliseconds($milliseconds)
    {
        $seconds = (int) floor($milliseconds / 1000);
        $millis = (int) $milliseconds % 1000;
        return gmdate('Y-m-d H:i:s', $seconds) . '.' . str_pad((string) $millis, 3, '0', STR_PAD_LEFT);
    }
}
