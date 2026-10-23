<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Site_support extends BackEndController
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
        $this->load->model('site_support_model');
        $this->data['title'] = 'Поддержка сайта';
    }

    public function index()
    {
        $this->data['migration_required'] = !$this->site_support_model->isInstalled();
        $this->data['support_settings'] = $this->site_support_model->getSettings();
        $this->data['inner_view'] = 'dashboard/site_support/index';

        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }

    public function save()
    {
        check_if_POST();

        try {
            $result = $this->site_support_model->saveSettings(
                $this->input->post('is_enabled', true),
                $this->input->post('telegram_contact', true)
            );

            if (empty($result['success'])) {
                throw new Exception((string) ($result['error'] ?? 'Не удалось сохранить настройку поддержки.'));
            }

            $_SESSION['success'] = 'Настройка поддержки сохранена.';
        } catch (Exception $e) {
            log_message('error', 'Site support save error: ' . $e->getMessage());
            $_SESSION['error'] = array($e->getMessage());
        }

        redirect('/' . ADM_CONTROLLER . '/site_support/');
    }
}
