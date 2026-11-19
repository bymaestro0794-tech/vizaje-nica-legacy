<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'libraries/Partners_creator_security.php';

/** Local creator link management and privacy-minimal click counting. */
class Creator_referrals extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
        $this->load->model('clients_model');
        $this->load->model('legacy_creator_links_model');
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        $this->output->set_header('X-Content-Type-Options: nosniff');
        $this->output->set_header('Referrer-Policy: no-referrer');
    }

    public function dashboard()
    {
        if (strtoupper((string) $this->input->method(true)) !== 'GET') return $this->jsonError(405, 'METHOD_NOT_ALLOWED');
        $client = $this->authenticatedCreator();
        if (!$client) return $this->jsonError(403, 'CREATOR_ACCESS_REQUIRED');
        if (!$this->legacy_creator_links_model->isAvailable()) return $this->jsonError(503, 'LINK_STORAGE_UNAVAILABLE');
        $links = array();
        foreach ($this->legacy_creator_links_model->listForClient($client->id) as $row) $links[] = $this->legacy_creator_links_model->present($row);
        return $this->json(array('ok' => true, 'state' => 'active', 'configured' => true, 'canConnect' => false,
            'canCreateLinks' => true, 'canUnlink' => false, 'linksAvailable' => true, 'links' => $links, 'analyticsAvailable' => false));
    }

    public function create_link()
    {
        if (!$this->requirePost()) return;
        $client = $this->authenticatedCreator();
        if (!$client) return $this->jsonError(403, 'CREATOR_ACCESS_REQUIRED');
        if (!$this->validCsrf((int) $client->id)) return $this->jsonError(403, 'CSRF_INVALID');
        $target = trim((string) $this->input->post('targetUrl', false));
        $label = trim((string) $this->input->post('label', false));
        $locale = $this->detectTargetLocale($target);
        $type = $this->detectLinkType($target);
        if ($locale === '' || !$this->isAllowedLegacyTarget($target)
            || !preg_match('//u', $label) || mb_strlen($label, 'UTF-8') > 120) {
            return $this->jsonError(422, 'INVALID_LINK_INPUT');
        }
        $row = $this->legacy_creator_links_model->create((int) $client->id, $label, $type, $locale, $target);
        if (!$row) return $this->jsonError(503, 'LINK_CREATE_FAILED');
        return $this->json(array('ok' => true, 'link' => $this->legacy_creator_links_model->present($row)));
    }

    public function revoke_link($id = 0)
    {
        if (!$this->requirePost()) return;
        $client = $this->authenticatedCreator();
        if (!$client) return $this->jsonError(403, 'CREATOR_ACCESS_REQUIRED');
        if (!$this->validCsrf((int) $client->id)) return $this->jsonError(403, 'CSRF_INVALID');
        if (!$this->legacy_creator_links_model->revoke((int) $client->id, (int) $id)) return $this->jsonError(404, 'LINK_NOT_FOUND');
        return $this->json(array('ok' => true));
    }

    public function go($code = '')
    {
        $row = $this->legacy_creator_links_model->registerClickAndGetTarget((string) $code);
        if (!$row || !$this->isAllowedLegacyTarget((string) $row['target_url'])) show_404();
        $this->load->model('legacy_creator_attribution_model');
        $this->legacy_creator_attribution_model->issueForLink((int) $row['id']);
        redirect((string) $row['target_url'], 'location', 302);
    }

    private function authenticatedCreator()
    {
        if (empty($_SESSION['user_id'])) return false;
        $client = $this->clients_model->get_client((int) $_SESSION['user_id']);
        if (!$client || empty($client->active) || empty($client->is_creator)
            || !Partners_creator_security::isAuthenticatedLegacySession($_SESSION, $client)) return false;
        return $client;
    }

    private function validCsrf($clientId)
    {
        return Partners_creator_security::isValidCsrfToken($_SESSION['creator_partner_csrf'] ?? '',
            (string) $this->input->post('csrfToken', false), $_SESSION['creator_partner_csrf_client_id'] ?? 0, $clientId);
    }

    private function isAllowedLegacyTarget($url)
    {
        if (Partners_creator_security::isAllowedTarget($url)) return true;
        $parts = parse_url((string) $url);
        if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host']) || empty($parts['path'])
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment'])) return false;
        $host = strtolower((string) $parts['host']);
        $base = parse_url((string) $this->config->item('base_url'));
        if (!is_array($base) || empty($base['scheme']) || empty($base['host'])) return false;
        $baseHost = strtolower((string) $base['host']);
        $targetPort = (int) ($parts['port'] ?? (($parts['scheme'] ?? '') === 'https' ? 443 : 80));
        $basePort = (int) ($base['port'] ?? (($base['scheme'] ?? '') === 'https' ? 443 : 80));
        $localHosts = array('localhost', '127.0.0.1');
        return in_array($host, $localHosts, true)
            && in_array($baseHost, $localHosts, true)
            && $host === $baseHost
            && strtolower((string) $parts['scheme']) === strtolower((string) $base['scheme'])
            && $targetPort === $basePort
            && preg_match('#^/(ru|ro)(/|$)#', (string) $parts['path']) === 1;
    }

    private function detectTargetLocale($url)
    {
        $parts = parse_url((string) $url);
        if (!is_array($parts)) return '';
        $segments = array_values(array_filter(explode('/', trim((string) ($parts['path'] ?? ''), '/')), 'strlen'));
        $locale = strtolower((string) ($segments[0] ?? ''));
        return in_array($locale, array('ru', 'ro'), true) ? $locale : '';
    }

    private function detectLinkType($url)
    {
        $parts = parse_url((string) $url);
        $segments = is_array($parts)
            ? array_values(array_filter(explode('/', trim((string) ($parts['path'] ?? ''), '/')), 'strlen'))
            : array();
        array_shift($segments); // ru/ro prefix
        if (!$segments) return 'homepage';
        $section = strtolower((string) $segments[0]);
        if (in_array($section, array('brands', 'brendyi'), true)) return 'brand';
        if (in_array($section, array('catalog', 'katalog'), true)) {
            return count($segments) >= 3 ? 'product' : 'category';
        }
        return 'homepage';
    }

    private function requirePost()
    {
        if (strtoupper((string) $this->input->method(true)) === 'POST') return true;
        $this->output->set_header('Allow: POST');
        $this->jsonError(405, 'METHOD_NOT_ALLOWED');
        return false;
    }

    private function jsonError($status, $code)
    {
        return $this->output->set_status_header((int) $status)->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode(array('ok' => false, 'code' => $code), JSON_UNESCAPED_SLASHES));
    }

    private function json(array $payload)
    {
        return $this->output->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
