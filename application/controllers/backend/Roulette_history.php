<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Roulette_history extends BackEndController
{
	public function __construct()
	{
		parent::__construct(__CLASS__);
		$this->load->model('roulette_model');
		$this->data['title'] = 'История рулетки';
	}

	public function index()
	{
		$filters = array(
			'q' => trim((string) $this->input->get('q', true)),
			'status' => trim((string) $this->input->get('status', true)),
		);
		$allowedStatuses = array('active', 'reserved', 'redeemed', 'expired', 'cancelled');
		if (!in_array($filters['status'], $allowedStatuses, true)) {
			$filters['status'] = '';
		}

		$this->data['migration_required'] = !$this->roulette_model->isGameplayInstalled();
		$this->data['allow_testing_reset'] = $this->isLocalTestingHost();
		$this->data['action_token'] = $this->actionToken();
		$this->data['filters'] = $filters;
		$this->data['objects'] = $this->data['migration_required']
			? array()
			: $this->roulette_model->getAdminSpinHistory($filters);
		$this->data['inner_view'] = 'dashboard/roulette_history/index';

		$this->load->vars($this->data);
		$this->load->view($this->main_layout);
	}

	public function cancel($id = 0)
	{
		check_if_POST();
		if (!$this->isLocalTestingHost()) {
			$_SESSION['error'] = array('Сброс попытки доступен только на локальном DEV-стенде.');
			redirect('/' . ADM_CONTROLLER . '/roulette_history/');
			return;
		}

		$token = (string) $this->input->post('action_token', true);
		if (!hash_equals($this->actionToken(), $token)) {
			$_SESSION['error'] = array('Не удалось подтвердить действие. Обновите страницу и повторите попытку.');
			redirect('/' . ADM_CONTROLLER . '/roulette_history/');
			return;
		}

		$result = $this->roulette_model->cancelActiveSpinForTesting((int) $id);
		if (!empty($result['ok'])) {
			$_SESSION['success'] = 'Приз отменён для теста. История сохранена, а следующая попытка для клиента снова доступна.';
		} elseif (($result['reason'] ?? '') === 'not_cancellable') {
			$_SESSION['error'] = array('Можно отменить только активный приз без привязанного заказа. Резерв оплаты и использованный приз защищены.');
		} else {
			$_SESSION['error'] = array('Не удалось отменить попытку. Обновите журнал и попробуйте ещё раз.');
		}

		redirect('/' . ADM_CONTROLLER . '/roulette_history/');
	}

	private function isLocalTestingHost()
	{
		$host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
		$host = preg_replace('/:\\d+$/', '', $host);
		return in_array($host, array('vizaje-nica.local', 'localhost', '127.0.0.1'), true);
	}

	private function actionToken()
	{
		if (empty($_SESSION['roulette_history_action_token']) || !preg_match('/^[a-f0-9]{64}$/', (string) $_SESSION['roulette_history_action_token'])) {
			$_SESSION['roulette_history_action_token'] = bin2hex(random_bytes(32));
		}
		return (string) $_SESSION['roulette_history_action_token'];
	}
}
