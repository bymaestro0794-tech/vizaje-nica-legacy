<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Creator_payouts extends FrontEndController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('creator_payouts_model');
    }

    public function request()
    {
        if (strtoupper((string) $this->input->method(true)) !== 'POST') show_error('POST required', 405);
        $client = $this->data['client_info'] ?? null;
        if (!$client) { show_error('Authentication required', 401); return; }
        if (session_status() !== PHP_SESSION_ACTIVE || !$this->validCsrf()) show_error('Invalid request token', 403);
        $amountRaw = trim((string) $this->input->post('amount_mdl', true));
        $comment = trim((string) $this->input->post('comment', true));
        if (!preg_match('/^[0-9]{1,9}(?:\.[0-9]{1,2})?$/D', $amountRaw)
            || (float) $amountRaw <= 0 || mb_strlen($comment, 'UTF-8') > 1000) {
            $this->redirectAccount('invalid');
            return;
        }
        $result = $this->creator_payouts_model->submit((int) $client->id, (float) $amountRaw, $comment);
        $allowed = array('created', 'pending_exists', 'not_creator', 'unavailable', 'error', 'insufficient_balance');
        $this->redirectAccount(in_array($result, $allowed, true) ? $result : 'error');
    }

    private function validCsrf()
    {
        $expected = (string) ($_SESSION['creator_payout_csrf'] ?? '');
        $actual = (string) $this->input->post('creator_payout_csrf', false);
        return $expected !== '' && $actual !== '' && hash_equals($expected, $actual);
    }

    private function redirectAccount($notice)
    {
        $uri = $this->data['menu']['all'][15]->uri ?? ($this->lclang === 'ro' ? 'contul-meu' : 'lichnyiy-kabinet');
        redirect('/' . $this->lclang . '/' . $uri . '?creator&payout=' . rawurlencode($notice));
    }
}
