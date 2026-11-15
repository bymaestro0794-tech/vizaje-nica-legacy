<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'libraries/Partners_creator_protocol.php';
require_once APPPATH . 'libraries/Partners_creator_grant_crypto.php';
require_once APPPATH . 'libraries/Partners_creator_client.php';
require_once APPPATH . 'libraries/Partners_creator_dashboard_presenter.php';
require_once APPPATH . 'libraries/Partners_creator_security.php';

/** Same-origin bridge between the authenticated Legacy creator and Partners. */
class Creator_account_linking extends CI_Controller
{
    private $creatorConfig = array();

    public function __construct()
    {
        parent::__construct();
        if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
        $this->load->model('clients_model');
        $this->load->model('partners_creator_model', 'partners_creator_model');
        $this->config->load('partners_creator', true);
        $settings = $this->config->item('partners_creator', 'partners_creator');
        $this->creatorConfig = is_array($settings) ? $settings : array('enabled' => false);
        $this->clientApi = new Partners_creator_client($this->creatorConfig);
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        $this->output->set_header('Pragma: no-cache');
        $this->output->set_header('Referrer-Policy: no-referrer');
        $this->output->set_header('X-Content-Type-Options: nosniff');
    }

    private $clientApi;

    /** POST start: create short-lived PKCE state, then return only the allowlisted authorize URL. */
    public function start()
    {
        if (!$this->requireMethod('POST')) return;
        $client = $this->authenticatedCreator();
        if (!$client) return $this->jsonError(403, 'CREATOR_ACCESS_REQUIRED');
        if (!$this->validCsrf()) return $this->jsonError(403, 'CSRF_INVALID');
        if (!$this->clientApi->enabled()) return $this->jsonError(503, 'INTEGRATION_UNAVAILABLE');
        if (!$this->partners_creator_model->isAvailable()) return $this->jsonError(503, 'LINK_STORAGE_UNAVAILABLE');
        if ($this->partners_creator_model->getForClient($client->id)) return $this->jsonError(409, 'ALREADY_CONNECTED');

        try {
            $verifier = Partners_creator_protocol::randomToken(32);
            $state = Partners_creator_protocol::randomToken(32);
            $challenge = Partners_creator_protocol::pkceChallenge($verifier);
        } catch (Throwable $error) { return $this->jsonError(503, 'INTEGRATION_UNAVAILABLE'); }

        $flow = array(
            'client_id' => (int) $client->id,
            'session_id' => session_id(),
            'state' => $state,
            'verifier' => $verifier,
            'locale' => strtolower((string) $this->input->post('locale', false)) === 'ro' ? 'ro' : 'ru',
            'started_at' => time(),
        );
        $_SESSION['creator_partner_link_flow'] = $flow;
        // Never hold the PHP session lock while waiting for the remote service.
        session_write_close();
        $result = $this->clientApi->initiate($client->id, $challenge, $state);
        $body = $result['body'] ?? array();
        $expires = isset($body['expiresAt']) && is_string($body['expiresAt']) ? strtotime($body['expiresAt']) : false;
        if (empty($result['ok']) || !is_array($body) || empty($body['ticket'])
            || !preg_match('/^[A-Za-z0-9_-]{43}$/', (string) $body['ticket'])
            || empty($body['authorizeUrl']) || !$this->clientApi->isAllowedAuthorizeUrl($body['authorizeUrl'])
            || $expires === false || $expires <= time() || $expires > time() + 360) {
            $this->clearLinkFlowIfMatches($state);
            return $this->jsonError(502, 'PARTNERS_RESPONSE_INVALID');
        }
        // The ticket exists only in this response/redirect URL; no ticket or PKCE value is persisted or logged.
        return $this->json(array('ok' => true, 'authorizeUrl' => $body['authorizeUrl']));
    }

    /** GET callback: state and session are checked before one-time exchange. */
    public function callback()
    {
        if (!$this->requireMethod('GET')) return;
        $client = $this->authenticatedCreator();
        $flow = $_SESSION['creator_partner_link_flow'] ?? null;
        $code = $this->input->get('code', false);
        $state = $this->input->get('state', false);
        $valid = $client && Partners_creator_security::isAuthenticatedCreatorSession($_SESSION, $client)
            && Partners_creator_security::isValidCallback($flow, $client->id, session_id(), $state, $code);
        if (!$valid) {
            // A stray callback must not cancel a different in-progress link attempt.
            if (is_array($flow) && (($state !== null && is_string($state) && isset($flow['state']) && hash_equals((string) $flow['state'], $state)) || !$client)) {
                unset($_SESSION['creator_partner_link_flow']);
            }
            return $this->redirectAccount('callback_invalid', is_array($flow) ? ($flow['locale'] ?? null) : null);
        }
        // Consume state before network I/O to make parallel/replayed callbacks one-shot.
        unset($_SESSION['creator_partner_link_flow']);
        $locale = $flow['locale'] ?? 'ru';
        if (!$this->clientApi->enabled() || !$this->partners_creator_model->isAvailable()) {
            return $this->redirectAccount('unavailable', $locale);
        }
        session_write_close();
        $result = $this->clientApi->exchange($code, $client->id, (string) $flow['verifier']);
        $body = $result['body'] ?? array();
        if (empty($result['ok']) || !Partners_creator_security::isValidExchangeResponse($body)) return $this->redirectAccount('exchange', $locale);
        if ($body['status'] !== 'active') return $this->redirectAccount('inactive', $locale);
        if (!$this->partners_creator_model->saveLink(
            $client->id, $body['partnerId'], $body['creatorId'], $body['status'], $body['scopes'],
            $body['grantToken'], $this->creatorConfig['grant_encryption_keys'] ?? array()
        )) return $this->redirectAccount('storage', $locale);

        return $this->redirectAccount('connected', $locale);
    }

    /** GET same-origin dashboard data; grant remains server-side. */
    public function dashboard()
    {
        if (!$this->requireMethod('GET')) return;
        $client = $this->authenticatedCreator();
        if (!$client) return $this->jsonError(403, 'CREATOR_ACCESS_REQUIRED');
        if (!$this->clientApi->enabled() || !$this->partners_creator_model->isAvailable()) {
            return $this->json(array('ok' => true, 'state' => 'not_connected', 'configured' => $this->clientApi->enabled(), 'canConnect' => false, 'canCreateLinks' => false, 'canUnlink' => false, 'linksAvailable' => false, 'analyticsAvailable' => false));
        }
        $row = $this->partners_creator_model->getForClient($client->id);
        if (!$row) return $this->json(array('ok' => true, 'state' => 'not_connected', 'configured' => true, 'canConnect' => true, 'canCreateLinks' => false, 'canUnlink' => false, 'linksAvailable' => false, 'analyticsAvailable' => false));
        $grant = $this->partners_creator_model->grantToken($row, $this->creatorConfig['grant_encryption_keys'] ?? array());
        if (!is_string($grant)) return $this->json(array('ok' => true, 'state' => 'error', 'configured' => true, 'canConnect' => false, 'canCreateLinks' => false, 'canUnlink' => false, 'creatorId' => (string) $row['creator_id'], 'linksAvailable' => false, 'analyticsAvailable' => false));

        // The remote status/analytics/link requests must not serialize storefront requests on this session.
        session_write_close();
        $statusResult = $this->clientApi->status($grant, $client->id);
        $status = $statusResult['body'] ?? array();
        if (empty($statusResult['ok'])) {
            if (($statusResult['error'] ?? '') === 'ACCOUNT_LINK_GRANT_INVALID') {
                if (!$this->partners_creator_model->deleteForClient($client->id)) {
                    return $this->json(array('ok' => true, 'state' => 'error', 'configured' => true, 'canConnect' => false, 'canCreateLinks' => false, 'canUnlink' => false, 'creatorId' => (string) $row['creator_id'], 'linksAvailable' => false, 'analyticsAvailable' => false));
                }
                return $this->json(array('ok' => true, 'state' => 'not_connected', 'configured' => true, 'canConnect' => true, 'canCreateLinks' => false, 'canUnlink' => false, 'linksAvailable' => false, 'analyticsAvailable' => false));
            }
            return $this->json(array('ok' => true, 'state' => 'error', 'configured' => true, 'canConnect' => false, 'canCreateLinks' => false, 'canUnlink' => false, 'creatorId' => (string) $row['creator_id'], 'linksAvailable' => false, 'analyticsAvailable' => false));
        }
        if (!Partners_creator_security::isValidStatusResponse($status, $row)) return $this->json(array('ok' => true, 'state' => 'error', 'configured' => true, 'canConnect' => false, 'canCreateLinks' => false, 'canUnlink' => false, 'creatorId' => (string) $row['creator_id'], 'linksAvailable' => false, 'analyticsAvailable' => false));
        $this->partners_creator_model->updateStatus($client->id, $status['status'], $status['creatorId']);
        if ($status['status'] !== 'active') return $this->json(array('ok' => true, 'state' => 'suspended', 'configured' => true, 'canConnect' => false, 'canCreateLinks' => false, 'canUnlink' => in_array('account:unlink', json_decode((string) ($row['scopes'] ?? ''), true) ?: array(), true), 'creatorId' => $status['creatorId'], 'partnerStatus' => $status['status'], 'linksAvailable' => false, 'analyticsAvailable' => false));

        $scopes = json_decode((string) ($row['scopes'] ?? ''), true);
        $hasAnalytics = is_array($scopes) && in_array('analytics:read', $scopes, true);
        $hasLinks = is_array($scopes) && in_array('links:read', $scopes, true);
        $canCreateLinks = is_array($scopes) && in_array('links:write', $scopes, true);
        $canUnlink = is_array($scopes) && in_array('account:unlink', $scopes, true);
        $analyticsResult = $hasAnalytics ? $this->clientApi->analytics($grant, $client->id) : array('ok' => false);
        $linkResult = $hasLinks ? $this->clientApi->listLinks($grant, $client->id) : array('ok' => false);
        $analytics = !empty($analyticsResult['ok']) ? Partners_creator_dashboard_presenter::analytics($analyticsResult['body']) : array('available' => false, 'empty' => false, 'metrics' => array());
        $links = !empty($linkResult['ok']) ? Partners_creator_dashboard_presenter::links($linkResult['body']) : array('available' => false, 'links' => array());
        return $this->json(array(
            'ok' => true, 'state' => 'active', 'configured' => true, 'creatorId' => $status['creatorId'],
            'canConnect' => false, 'canCreateLinks' => $canCreateLinks, 'canUnlink' => $canUnlink,
            'analyticsAvailable' => !empty($analytics['available']), 'analyticsEmpty' => !empty($analytics['empty']),
            'metrics' => $analytics['metrics'], 'linksAvailable' => !empty($links['available']), 'links' => $links['links'],
        ));
    }

    public function create_link()
    {
        if (!$this->requireMethod('POST')) return;
        $client = $this->authenticatedCreator();
        if (!$client) return $this->jsonError(403, 'CREATOR_ACCESS_REQUIRED');
        if (!$this->validCsrf()) return $this->jsonError(403, 'CSRF_INVALID');
        $context = $this->linkedGrant($client->id, 'links:write');
        if (!$context) return $this->jsonError(409, 'ACCOUNT_NOT_CONNECTED');
        $idempotency = trim((string) $this->input->post('idempotencyKey', false));
        $type = trim((string) $this->input->post('linkType', false));
        $locale = strtolower(trim((string) $this->input->post('requestedLocale', false)));
        $target = trim((string) $this->input->post('targetUrl', false));
        $label = trim((string) $this->input->post('label', false));
        $labelLength = function_exists('mb_strlen') ? mb_strlen($label, 'UTF-8') : strlen($label);
        if (!Partners_creator_model::isUuid($idempotency) || !in_array($type, array('homepage', 'product', 'category', 'brand'), true)
            || !in_array($locale, array('ru', 'ro'), true) || !Partners_creator_security::isAllowedTarget($target)
            || !preg_match('//u', $label) || $labelLength > 120) {
            return $this->jsonError(422, 'INVALID_LINK_INPUT');
        }
        $link = array('linkType' => $type, 'targetUrl' => $target, 'requestedLocale' => $locale);
        if ($label !== '') $link['label'] = $label;
        $result = $this->clientApi->createLink($context['grant'], $client->id, $idempotency, $link);
        if (empty($result['ok'])) return $this->apiError($result);
        $presented = Partners_creator_dashboard_presenter::createdLink($result['body']);
        if (!$presented) return $this->jsonError(502, 'PARTNERS_RESPONSE_INVALID');
        $presented['idempotent'] = !empty($result['body']['idempotent']);
        return $this->json(array('ok' => true, 'link' => $presented));
    }

    public function revoke_link($linkId = '')
    {
        if (!$this->requireMethod('POST')) return;
        $client = $this->authenticatedCreator();
        if (!$client) return $this->jsonError(403, 'CREATOR_ACCESS_REQUIRED');
        if (!$this->validCsrf()) return $this->jsonError(403, 'CSRF_INVALID');
        $context = $this->linkedGrant($client->id, 'links:write');
        if (!$context) return $this->jsonError(409, 'ACCOUNT_NOT_CONNECTED');
        $result = $this->clientApi->revokeLink($context['grant'], $client->id, (string) $linkId);
        if (empty($result['ok'])) return $this->apiError($result);
        return $this->json(array('ok' => true));
    }

    public function unlink()
    {
        if (!$this->requireMethod('POST')) return;
        $client = $this->authenticatedCreator();
        if (!$client) return $this->jsonError(403, 'CREATOR_ACCESS_REQUIRED');
        if (!$this->validCsrf()) return $this->jsonError(403, 'CSRF_INVALID');
        // A suspended creator must still be able to revoke their account link.
        $context = $this->linkedGrant($client->id, 'account:unlink', true);
        if (!$context) return $this->jsonError(409, 'ACCOUNT_NOT_CONNECTED');
        $result = $this->clientApi->unlink($context['grant'], $client->id);
        if (empty($result['ok'])) return $this->apiError($result);
        if (!$this->partners_creator_model->deleteForClient($client->id)) return $this->jsonError(503, 'LOCAL_UNLINK_PENDING');
        return $this->json(array('ok' => true));
    }

    private function linkedGrant($clientId, $requiredScope, $allowInactive = false)
    {
        if (!$this->clientApi->enabled() || !$this->partners_creator_model->isAvailable()) return false;
        $row = $this->partners_creator_model->getForClient($clientId);
        if (!$row || ($row['account_type'] ?? '') !== 'creator') return false;
        $scopes = json_decode((string) ($row['scopes'] ?? ''), true);
        if (!is_array($scopes) || !in_array($requiredScope, $scopes, true)) return false;
        $grant = $this->partners_creator_model->grantToken($row, $this->creatorConfig['grant_encryption_keys'] ?? array());
        if (!is_string($grant)) return false;

        // Mutations are authorized against the current Partner status, not a stale local flag.
        session_write_close();
        $statusResult = $this->clientApi->status($grant, $clientId);
        if (empty($statusResult['ok'])) {
            if (($statusResult['error'] ?? '') === 'ACCOUNT_LINK_GRANT_INVALID') $this->partners_creator_model->deleteForClient($clientId);
            return false;
        }
        $status = $statusResult['body'] ?? array();
        if (!Partners_creator_security::isValidStatusResponse($status, $row)) return false;
        $this->partners_creator_model->updateStatus($clientId, $status['status'], $status['creatorId']);
        if (!$allowInactive && $status['status'] !== 'active') return false;
        return array('grant' => $grant, 'row' => $row);
    }

    private function authenticatedCreator()
    {
        if (empty($_SESSION['user_id'])) return false;
        $client = $this->clients_model->get_client((int) $_SESSION['user_id']);
        return Partners_creator_security::isAuthenticatedCreatorSession($_SESSION, $client) ? $client : false;
    }

    private function validCsrf()
    {
        $expected = $_SESSION['creator_partner_csrf'] ?? '';
        $actual = (string) $this->input->post('csrfToken', false);
        return is_string($expected) && preg_match('/^[a-f0-9]{64}$/', $expected) === 1
            && (string) ($_SESSION['creator_partner_csrf_client_id'] ?? '') === (string) (int) $_SESSION['user_id']
            && $actual !== '' && hash_equals($expected, $actual);
    }

    private function apiError($result)
    {
        $code = (string) ($result['error'] ?? 'PARTNERS_ERROR');
        $allowed = array('PARTNER_NOT_ACTIVE', 'ACCOUNT_LINK_SCOPE_REQUIRED', 'ACCOUNT_LINK_GRANT_INVALID', 'REFERRAL_LINK_NOT_FOUND', 'IDEMPOTENCY_CONFLICT', 'INVALID_DESTINATION', 'FEATURE_DISABLED', 'SERVICE_AUTH_INVALID', 'SERVICE_TIMESTAMP_INVALID', 'AUTH_NONCE_REPLAY');
        $code = in_array($code, $allowed, true) ? $code : 'PARTNERS_UNAVAILABLE';
        $status = (int) ($result['status'] ?? 0);
        if ($status < 400 || $status > 599) $status = 502;
        return $this->jsonError($status, $code);
    }

    private function requireMethod($method)
    {
        if (strtoupper((string) $this->input->method(true)) === $method) return true;
        $this->output->set_header('Allow: ' . $method);
        $this->jsonError(405, 'METHOD_NOT_ALLOWED');
        return false;
    }

    private function jsonError($status, $code)
    {
        return $this->output->set_status_header((int) $status)->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode(array('ok' => false, 'code' => (string) $code), JSON_UNESCAPED_UNICODE));
    }

    private function json($payload)
    {
        return $this->output->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function redirectAccount($notice, $locale = null)
    {
        $allowedNotices = array('callback_invalid', 'unavailable', 'exchange', 'inactive', 'storage', 'connected');
        if (!in_array($notice, $allowedNotices, true)) $notice = 'callback_invalid';
        if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['creator_partner_notice'] = $notice;
            if ($locale === null) $locale = $_SESSION['lang'] ?? 'ru';
            session_write_close();
        }
        $lang = strtolower((string) $locale);
        if (!in_array($lang, array('ru', 'ro'), true)) $lang = 'ru';
        $slug = $lang === 'ro' ? 'contul-meu' : 'lichnyiy-kabinet';
        return redirect('/' . $lang . '/' . $slug, 'location', 303);
    }

    private function clearLinkFlowIfMatches($state)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
        if (session_status() !== PHP_SESSION_ACTIVE) return;
        $flow = $_SESSION['creator_partner_link_flow'] ?? null;
        if (is_array($flow) && is_string($flow['state'] ?? null) && is_string($state)
            && hash_equals($flow['state'], $state)) unset($_SESSION['creator_partner_link_flow']);
        session_write_close();
    }
}
