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
		$this->data['filters'] = $filters;
		$this->data['objects'] = $this->data['migration_required']
			? array()
			: $this->welcome_offers_model->getAdminItems($filters);
		$this->data['inner_view'] = 'dashboard/welcome_offers/index';

		$this->load->vars($this->data);
		$this->load->view($this->main_layout);
	}
}
