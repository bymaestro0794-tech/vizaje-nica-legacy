<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Site_themes extends BackEndController
{
	public function __construct()
	{
		parent::__construct(__CLASS__);
		$this->load->model('site_themes_model');
		$this->data['title'] = 'Атмосфера сайта';
	}

	public function index()
	{
		$this->data['migration_required'] = !$this->site_themes_model->isInstalled();
		$this->data['theme_presets'] = $this->site_themes_model->getThemePresets();
		$this->data['active_theme_key'] = $this->site_themes_model->getActiveThemeKey();
		$this->data['inner_view'] = 'dashboard/site_themes/index';

		$this->load->vars($this->data);
		$this->load->view($this->main_layout);
	}

	public function save()
	{
		check_if_POST();

		$themeKey = trim((string) $this->input->post('theme_key', true));

		try {
			if (!$this->site_themes_model->isInstalled()) {
				throw new Exception('Сначала примените SQL-патч database/patches/2026-10-21-create-site-theme-settings.sql.');
			}

			if (!$this->site_themes_model->saveActiveThemeKey($themeKey)) {
				throw new Exception('Выберите одну из подготовленных тем.');
			}

			$_SESSION['success'] = 'Атмосфера сайта сохранена.';
		} catch (Exception $e) {
			log_message('error', 'Site theme save error: ' . $e->getMessage());
			$_SESSION['error'] = array($e->getMessage());
		}

		redirect('/' . ADM_CONTROLLER . '/site_themes/');
	}
}
