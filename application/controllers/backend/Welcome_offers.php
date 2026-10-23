<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Welcome_offers extends BackEndController
{
	public function __construct()
	{
		parent::__construct(__CLASS__);
		$this->load->model('welcome_offers_model');
		$this->data['title'] = 'Скидка после регистрации −18%';
	}

	public function index()
	{
		$filters = array(
			'q' => trim((string) $this->input->get('q', true)),
			'state' => trim((string) $this->input->get('state', true)),
		);
		$allowedStates = array('active', 'used', 'expired');
		if (!in_array($filters['state'], $allowedStates, true)) {
			$filters['state'] = '';
		}

		$this->data['migration_required'] = !$this->welcome_offers_model->isInstalled();
		$this->data['welcome_promo'] = $this->data['migration_required']
			? null
			: $this->welcome_offers_model->getCampaign();
		$this->data['filters'] = $filters;
		$this->data['objects'] = $this->data['migration_required']
			? array()
			: $this->welcome_offers_model->getAdminItems($filters);
		$this->data['inner_view'] = 'dashboard/welcome_offers/index';

		$this->load->vars($this->data);
		$this->load->view($this->main_layout);
	}

	public function toggle_visibility()
	{
		check_if_POST();
		try {
			if (!$this->welcome_offers_model->isInstalled() || empty($this->welcome_offers_model->getCampaign())) {
				throw new Exception('Сначала примените SQL-патчи приветственной скидки и убедитесь, что существует WELCOME18.');
			}

			if (!$this->welcome_offers_model->setCampaignVisible($this->input->post('show_on_site'))) {
				throw new Exception('Не удалось изменить видимость скидки WELCOME18.');
			}
			$this->load->driver('cache', array(
				'adapter' => 'file',
				'backup' => 'dummy',
				'key_prefix' => 'vn_frontend_',
			));
			$this->cache->delete('welcome_promo_18');
			$_SESSION['success'] = 'Настройка показа скидки после регистрации сохранена.';
		} catch (Exception $e) {
			log_message('error', $e->getMessage());
			$_SESSION['error'] = array($e->getMessage());
		}
		redirect($this->path);
	}
}
