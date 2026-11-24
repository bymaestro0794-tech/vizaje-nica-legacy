<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Creator_feedback extends BackEndController
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
        $this->load->model('creator_feedback_model');
        $this->data['title'] = 'Партнёрская программа — обращения креаторов';
        if (empty($_SESSION['creator_feedback_admin_csrf'])) $_SESSION['creator_feedback_admin_csrf'] = bin2hex(random_bytes(32));
        $this->data['creator_feedback_admin_csrf'] = $_SESSION['creator_feedback_admin_csrf'];
    }

    public function index()
    {
        if (!$this->creator_feedback_model->isAvailable()) show_error('Apply the creator feedback SQL patch first.', 503);
        $status = (string) $this->input->get('status', true);
        $this->data['selected_status'] = in_array($status, array('new', 'in_progress', 'closed'), true) ? $status : '';
        $this->data['messages'] = $this->creator_feedback_model->listMessages($this->data['selected_status']);
        $this->data['inner_view'] = 'dashboard/creator_feedback/index';
        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }

    public function view($id = 0)
    {
        if (!$this->creator_feedback_model->isAvailable()) show_error('Apply the creator feedback SQL patch first.', 503);
        $message = $this->creator_feedback_model->getMessage((int) $id);
        if (!$message) show_404();
        $this->data['feedback_message'] = $message;
        $this->data['inner_view'] = 'dashboard/creator_feedback/view';
        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }

    public function update($id = 0)
    {
        if (strtoupper((string) $this->input->method(true)) !== 'POST') show_error('POST required', 405);
        $expected = (string) ($_SESSION['creator_feedback_admin_csrf'] ?? '');
        $actual = (string) $this->input->post('creator_feedback_admin_csrf', false);
        if ($expected === '' || $actual === '' || !hash_equals($expected, $actual)) show_error('Invalid request token', 403);
        $status = (string) $this->input->post('status', true);
        $note = trim((string) $this->input->post('admin_note', true));
        $saved = mb_strlen($note, 'UTF-8') <= 2000
            && $this->creator_feedback_model->updateMessage((int) $id, $status, $note, (int) ($_SESSION['admin_id'] ?? 0));
        $_SESSION[$saved ? 'success' : 'error'] = $saved ? 'Обращение обновлено.' : 'Не удалось обновить обращение. Проверьте статус и длину заметки.';
        redirect('/' . ADM_CONTROLLER . '/creator_feedback/view/' . (int) $id);
    }
}
