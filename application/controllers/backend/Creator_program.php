<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Creator_program extends BackEndController
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
        $this->load->model('creator_program_model');
        $this->data['title'] = 'Партнёрская программа — заявки';
        if (empty($_SESSION['creator_program_admin_csrf'])) {
            $_SESSION['creator_program_admin_csrf'] = bin2hex(random_bytes(32));
        }
        $this->data['creator_program_admin_csrf'] = $_SESSION['creator_program_admin_csrf'];
    }

    public function index()
    {
        if (!$this->creator_program_model->isAvailable()) show_error('Apply the creator program SQL patch first.', 503);
        $status = (string) $this->input->get('status', true);
        $this->data['selected_status'] = in_array($status, array('pending', 'approved', 'rejected'), true) ? $status : '';
        $this->data['applications'] = $this->creator_program_model->listApplications($this->data['selected_status']);
        $this->data['inner_view'] = 'dashboard/creator_program/index';
        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }

    public function view($id = 0)
    {
        $application = $this->creator_program_model->getApplication((int) $id);
        if (!$application) show_404();
        $this->data['application'] = $application;
        $this->data['email_outbox'] = $this->creator_program_model->getEmailOutbox($id);
        $this->data['inner_view'] = 'dashboard/creator_program/view';
        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }

    public function decide($id = 0)
    {
        if (strtoupper((string) $this->input->method(true)) !== 'POST') show_error('POST required', 405);
        if (!$this->validCsrf()) show_error('Invalid request token', 403);
        $decision = (string) $this->input->post('decision', true);
        $reason = trim((string) $this->input->post('reason', true));
        if (!in_array($decision, array('approved', 'rejected'), true) || mb_strlen($reason, 'UTF-8') > 1000) {
            $_SESSION['error'] = array('Проверьте решение и комментарий.');
            redirect('/' . ADM_CONTROLLER . '/creator_program/view/' . (int) $id);
            return;
        }
        $adminId = (int) ($_SESSION['admin_id'] ?? 0);
        $result = $this->creator_program_model->decide((int) $id, $decision, $reason, $adminId);
        if ($result !== 'decided') {
            $_SESSION['error'] = array($result === 'already_decided' ? 'Заявка уже рассмотрена.' : 'Не удалось сохранить решение. Проверьте SQL-патч.');
            redirect('/' . ADM_CONTROLLER . '/creator_program/view/' . (int) $id);
            return;
        }
        $outbox = $this->creator_program_model->getEmailOutbox($id);
        $application = $this->creator_program_model->getApplication($id);
        $sent = $outbox && $application ? $this->deliverEmail($outbox, $application) : false;
        $_SESSION['success'] = $sent
            ? ($decision === 'approved' ? 'Заявка одобрена, доступ к кабинету включён, письмо отправлено.' : 'Заявка отклонена, письмо отправлено.')
            : 'Решение сохранено. Письмо осталось в очереди или не отправилось; его можно повторить из карточки заявки.';
        redirect('/' . ADM_CONTROLLER . '/creator_program/view/' . (int) $id);
    }

    public function retry_email($outboxId = 0)
    {
        if (strtoupper((string) $this->input->method(true)) !== 'POST') show_error('POST required', 405);
        if (!$this->validCsrf()) show_error('Invalid request token', 403);
        $outbox = $this->creator_program_model->getEmailOutboxById((int) $outboxId);
        if (!$outbox) show_404();
        $application = $this->creator_program_model->getApplication((int) $outbox['application_id']);
        $sent = $application && $this->deliverEmail($outbox, $application);
        $_SESSION[$sent ? 'success' : 'error'] = $sent
            ? 'Письмо отправлено.'
            : array('Не удалось отправить письмо. Проверьте SMTP и повторите попытку.');
        redirect('/' . ADM_CONTROLLER . '/creator_program/view/' . (int) $outbox['application_id']);
    }

    private function deliverEmail(array $outbox, array $application)
    {
        $claimed = $this->creator_program_model->claimEmail((int) $outbox['id']);
        if (!$claimed) return false;
        $this->load->library('Creator_program_mailer');
        try {
            $sent = $this->creator_program_mailer->sendDecision(
                $application,
                $claimed['event_type'],
                (string) ($application['rejection_reason'] ?? '')
            );
        } catch (Throwable $error) {
            log_message('error', 'Creator program decision email failed: ' . get_class($error));
            $sent = false;
        }
        $this->creator_program_model->finishEmail((int) $claimed['id'], $sent);
        return $sent;
    }

    private function validCsrf()
    {
        $expected = (string) ($_SESSION['creator_program_admin_csrf'] ?? '');
        $actual = (string) $this->input->post('creator_program_admin_csrf', false);
        return $expected !== '' && $actual !== '' && hash_equals($expected, $actual);
    }
}
