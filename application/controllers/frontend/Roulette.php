<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Roulette extends FrontEndController
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('roulette_game_model');
	}

	public function state()
	{
		if (strtoupper((string) $this->input->method()) !== 'GET') {
			return $this->jsonResponse(405, array('ok' => false, 'error' => 'method_not_allowed'), array('Allow: GET'));
		}

		if (!$this->roulette_game_model->isReady()) {
			return $this->jsonResponse(503, array('ok' => false, 'error' => 'roulette_setup_required'));
		}

		$clientId = $this->authenticatedClientId();
		if ($clientId < 1) {
			return $this->jsonResponse(401, array('ok' => false, 'error' => 'authentication_required'));
		}

		try {
			$data = $this->roulette_game_model->getState($clientId, $this->lclang);
			$data['csrf_token'] = $this->spinCsrfToken();
			return $this->jsonResponse(200, array('ok' => true, 'data' => $data));
		} catch (Throwable $exception) {
			log_message('error', 'Roulette state request failed: ' . $exception->getMessage());
			return $this->jsonResponse(500, array('ok' => false, 'error' => 'state_unavailable'));
		}
	}

	public function spin()
	{
		if (strtoupper((string) $this->input->method()) !== 'POST') {
			return $this->jsonResponse(405, array('ok' => false, 'error' => 'method_not_allowed'), array('Allow: POST'));
		}

		if (!$this->roulette_game_model->isReady()) {
			return $this->jsonResponse(503, array('ok' => false, 'error' => 'roulette_setup_required'));
		}

		$clientId = $this->authenticatedClientId();
		if ($clientId < 1) {
			return $this->jsonResponse(401, array('ok' => false, 'error' => 'authentication_required'));
		}

		$payload = json_decode((string) $this->input->raw_input_stream, true);
		if (!is_array($payload)) {
			return $this->jsonResponse(400, array('ok' => false, 'error' => 'invalid_json'));
		}

		$csrfToken = isset($payload['csrf_token']) ? (string) $payload['csrf_token'] : '';
		$sessionToken = isset($_SESSION['roulette_spin_csrf']) ? (string) $_SESSION['roulette_spin_csrf'] : '';
		if ($sessionToken === '' || $csrfToken === '' || !hash_equals($sessionToken, $csrfToken)) {
			return $this->jsonResponse(403, array('ok' => false, 'error' => 'invalid_csrf_token'));
		}

		$requestId = isset($payload['request_id']) ? strtolower(trim((string) $payload['request_id'])) : '';
		if (!preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[1-5][a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/', $requestId)) {
			return $this->jsonResponse(422, array('ok' => false, 'error' => 'invalid_request_id'));
		}

		$result = $this->roulette_game_model->spin($clientId, $requestId, $this->lclang);
		if (empty($result['ok'])) {
			$status = isset($result['http_status']) ? (int) $result['http_status'] : 500;
			$reason = isset($result['reason']) ? $result['reason'] : 'spin_failed';
			return $this->jsonResponse($status, array('ok' => false, 'error' => $reason));
		}

		return $this->jsonResponse(200, array(
			'ok' => true,
			'replayed' => !empty($result['replayed']),
			'prize' => $result['prize'],
		));
	}

	private function authenticatedClientId()
	{
		if (empty($this->data['client_info']) || empty($this->data['client_info']->id)) {
			return 0;
		}
		return (int) $this->data['client_info']->id;
	}

	private function spinCsrfToken()
	{
		if (
			empty($_SESSION['roulette_spin_csrf'])
			|| !preg_match('/^[a-f0-9]{64}$/', (string) $_SESSION['roulette_spin_csrf'])
		) {
			$_SESSION['roulette_spin_csrf'] = bin2hex(random_bytes(32));
		}
		return (string) $_SESSION['roulette_spin_csrf'];
	}

	private function jsonResponse($status, array $body, array $headers = array())
	{
		$this->output
			->set_status_header((int) $status)
			->set_content_type('application/json', 'UTF-8')
			->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0')
			->set_header('Pragma: no-cache')
			->set_header('Expires: 0');

		foreach ($headers as $header) {
			$this->output->set_header($header);
		}

		$this->output->set_output(json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
		return null;
	}
}
