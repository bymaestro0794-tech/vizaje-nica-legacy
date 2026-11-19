<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Creator_payouts extends BackEndController
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
        $this->load->model('creator_payouts_model');
        $this->data['title'] = 'Партнёрская программа — запросы на выплату';
        if (empty($_SESSION['creator_payout_admin_csrf'])) $_SESSION['creator_payout_admin_csrf'] = bin2hex(random_bytes(32));
        $this->data['creator_payout_admin_csrf'] = $_SESSION['creator_payout_admin_csrf'];
    }

    public function index()
    {
        if (!$this->creator_payouts_model->isAvailable()) show_error('Apply the creator payout SQL patch first.', 503);
        $status = (string) $this->input->get('status', true);
        $this->data['selected_status'] = in_array($status, array('pending', 'approved', 'rejected', 'paid'), true) ? $status : '';
        $this->data['requests'] = $this->creator_payouts_model->listRequests($this->data['selected_status']);
        $this->data['inner_view'] = 'dashboard/creator_payouts/index';
        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }

    public function view($id = 0)
    {
        $request = $this->creator_payouts_model->getRequest((int) $id);
        if (!$request) show_404();
        $this->data['payout_request'] = $request;
        $this->data['inner_view'] = 'dashboard/creator_payouts/view';
        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }

    public function decide($id = 0)
    {
        if (strtoupper((string) $this->input->method(true)) !== 'POST') show_error('POST required', 405);
        $expected = (string) ($_SESSION['creator_payout_admin_csrf'] ?? '');
        $actual = (string) $this->input->post('creator_payout_admin_csrf', false);
        if ($expected === '' || $actual === '' || !hash_equals($expected, $actual)) show_error('Invalid request token', 403);
        $decision = (string) $this->input->post('decision', true);
        $note = trim((string) $this->input->post('admin_comment', true));
        if (!in_array($decision, array('approved', 'rejected'), true) || mb_strlen($note, 'UTF-8') > 1000) {
            $_SESSION['error'] = array('Проверьте решение и комментарий.');
            redirect('/' . ADM_CONTROLLER . '/creator_payouts/view/' . (int) $id);
            return;
        }
        $result = $this->creator_payouts_model->decide((int) $id, $decision, $note, (int) ($_SESSION['admin_id'] ?? 0));
        if ($result !== 'decided') {
            $_SESSION['error'] = array($result === 'already_decided' ? 'Запрос уже рассмотрен.' : 'Не удалось сохранить решение.');
        } else {
            $_SESSION['success'] = $decision === 'approved'
                ? 'Запрос одобрен. Это решение не выполняет перевод денег.'
                : 'Запрос отклонён.';
        }
        redirect('/' . ADM_CONTROLLER . '/creator_payouts/view/' . (int) $id);
    }

    public function mark_paid($id = 0)
    {
        if (strtoupper((string) $this->input->method(true)) !== 'POST') show_error('POST required', 405);
        $expected = (string) ($_SESSION['creator_payout_admin_csrf'] ?? '');
        $actual = (string) $this->input->post('creator_payout_admin_csrf', false);
        if ($expected === '' || $actual === '' || !hash_equals($expected, $actual)) show_error('Invalid request token', 403);
        $note = trim((string) $this->input->post('admin_comment', true));
        $result = mb_strlen($note, 'UTF-8') <= 1000
            ? $this->creator_payouts_model->markPaid((int) $id, (int) ($_SESSION['admin_id'] ?? 0), $note)
            : 'invalid';
        $_SESSION[$result === 'paid' ? 'success' : 'error'] = $result === 'paid'
            ? 'Запрос отмечен как выплаченный.'
            : ($result === 'invalid' ? 'Комментарий слишком длинный.' : 'Не удалось отметить выплату. Проверьте статус запроса.');
        redirect('/' . ADM_CONTROLLER . '/creator_payouts/view/' . (int) $id);
    }
}
