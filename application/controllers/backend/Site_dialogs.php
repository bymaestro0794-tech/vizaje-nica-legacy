<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Site_dialogs extends BackEndController
{
	public function __construct()
	{
		parent::__construct(__CLASS__);
		$this->load->model('site_dialogs_model');
		$this->data['title'] = 'Диалоги сайта';
	}

	public function index()
	{
		$this->data['migration_required'] = !$this->site_dialogs_model->isInstalled();
		$this->data['objects'] = $this->site_dialogs_model->getAdminItems();
		$this->data['inner_view'] = 'dashboard/site_dialogs/index';
		$this->load->vars($this->data);
		$this->load->view($this->main_layout);
	}

	public function item($id = 0)
	{
		$id = (int) $id;
		if (!$this->site_dialogs_model->isInstalled()) {
			$_SESSION['error'] = array('Сначала примените SQL-патч database/patches/2026-11-13-create-site-dialogs.sql.');
			redirect('/' . ADM_CONTROLLER . '/site_dialogs/');
			return;
		}

		$item = $id > 0 ? $this->site_dialogs_model->getById($id) : array();
		if ($id > 0 && empty($item)) {
			show_404();
			return;
		}
		$this->data['item'] = is_array($item) ? $item : array();
		$this->data['inner_view'] = 'dashboard/site_dialogs/item';
		$this->load->vars($this->data);
		$this->load->view($this->main_layout);
	}

	public function save($id = 0)
	{
		check_if_POST();
		$id = (int) $id;

		try {
			if (!$this->site_dialogs_model->isInstalled()) {
				throw new Exception('Сначала примените SQL-патч database/patches/2026-11-13-create-site-dialogs.sql.');
			}

			$existing = $id > 0 ? $this->site_dialogs_model->getById($id) : array();
			if ($id > 0 && empty($existing)) {
				throw new Exception('Диалог не найден.');
			}

			$data = array(
				'title_ru' => trim(strip_tags((string) $this->input->post('title_ru', true))),
				'title_ro' => trim(strip_tags((string) $this->input->post('title_ro', true))),
				'text_ru' => trim((string) $this->input->post('text_ru', true)),
				'text_ro' => trim((string) $this->input->post('text_ro', true)),
				'button_text_ru' => trim(strip_tags((string) $this->input->post('button_text_ru', true))),
				'button_text_ro' => trim(strip_tags((string) $this->input->post('button_text_ro', true))),
				'target_url_ru' => trim((string) $this->input->post('target_url_ru', true)),
				'target_url_ro' => trim((string) $this->input->post('target_url_ro', true)),
				'audience' => trim((string) $this->input->post('audience', true)),
				'display_delay_seconds' => (int) $this->input->post('display_delay_seconds'),
				'priority' => (int) $this->input->post('priority'),
				'is_active' => $this->input->post('is_active') === '1' ? 1 : 0,
			);

			if ($data['title_ru'] === '' || $data['text_ru'] === '') {
				throw new Exception('Заполните русские заголовок и текст.');
			}
			if (!in_array($data['audience'], array('all', 'guest', 'customer'), true)) {
				throw new Exception('Выберите аудиторию показа.');
			}
			if ($data['display_delay_seconds'] < 0 || $data['display_delay_seconds'] > 1800) {
				throw new Exception('Задержка показа должна быть от 0 до 1800 секунд.');
			}
			if ($data['priority'] < -50 || $data['priority'] > 50) {
				throw new Exception('Приоритет должен быть от −50 до 50.');
			}
			foreach (array('ru', 'ro') as $language) {
				if ($data['button_text_' . $language] !== '' && !$this->isSafeTargetUrl($data['target_url_' . $language])) {
					throw new Exception('Для кнопки укажите внутренний адрес сайта, начинающийся с /, либо HTTPS-ссылку на vizaje-nica.com.');
				}
				if ($data['button_text_' . $language] === '') {
					$data['target_url_' . $language] = '';
				}
			}

			$data['starts_at'] = $this->normalizeDateTime($this->input->post('starts_at', true));
			$data['ends_at'] = $this->normalizeDateTime($this->input->post('ends_at', true));
			if (!empty($data['starts_at']) && !empty($data['ends_at']) && $data['ends_at'] < $data['starts_at']) {
				throw new Exception('Дата окончания должна быть позже даты начала.');
			}

			$image = !empty($existing['image']) ? (string) $existing['image'] : '';
			if (!empty($_FILES['image']['name'])) {
				init_load_img('site_dialogs', true, 'jpg|jpeg|png|webp');
				if (!$this->upload->do_upload('image')) {
					throw new Exception(strip_tags($this->upload->display_errors('', '')));
				}
				$upload = $this->upload->data();
				$image = basename((string) $upload['file_name']);
			}
			$data['image'] = $image;

			$saved = $this->site_dialogs_model->saveCampaign($data, $id);
			if (!$saved) {
				throw new Exception('Не удалось сохранить диалог. Проверьте соединение с базой данных.');
			}
			if (!empty($existing['image']) && $image !== $existing['image']) {
				$this->removeImage($existing['image']);
			}
			$_SESSION['success'] = 'Диалог сайта сохранён.';
			redirect('/' . ADM_CONTROLLER . '/site_dialogs/');
			return;
		} catch (Exception $exception) {
			log_message('error', 'Site dialog save error: ' . $exception->getMessage());
			$_SESSION['error'] = array($exception->getMessage());
			redirect('/' . ADM_CONTROLLER . '/site_dialogs/item/' . $id);
		}
	}

	public function toggle($id = 0)
	{
		check_if_POST();
		$id = (int) $id;
		try {
			$item = $this->site_dialogs_model->getById($id);
			if (empty($item) || !$this->site_dialogs_model->setActive($id, $this->input->post('is_active'))) {
				throw new Exception('Не удалось изменить статус диалога.');
			}
			$_SESSION['success'] = 'Статус диалога сохранён.';
		} catch (Exception $exception) {
			$_SESSION['error'] = array($exception->getMessage());
		}
		redirect('/' . ADM_CONTROLLER . '/site_dialogs/');
	}

	public function delete($id = 0)
	{
		check_if_POST();
		$item = $this->site_dialogs_model->deleteCampaign((int) $id);
		if (!empty($item)) {
			$this->removeImage($item['image'] ?? '');
			$_SESSION['success'] = 'Диалог удалён.';
		} else {
			$_SESSION['error'] = array('Не удалось удалить диалог.');
		}
		redirect('/' . ADM_CONTROLLER . '/site_dialogs/');
	}

	private function normalizeDateTime($value)
	{
		$value = trim((string) $value);
		if ($value === '') {
			return null;
		}
		$date = DateTime::createFromFormat('Y-m-d\TH:i', $value);
		$errors = DateTime::getLastErrors();
		if (!$date || (is_array($errors) && ($errors['warning_count'] || $errors['error_count']))) {
			throw new Exception('Проверьте формат даты и времени.');
		}
		return $date->format('Y-m-d H:i:s');
	}

	private function isSafeTargetUrl($url)
	{
		$url = trim((string) $url);
		if ($url === '' || preg_match('/[\x00-\x20\\\\]/', $url)) {
			return false;
		}
		if (strpos($url, '/') === 0) {
			return strpos($url, '//') !== 0;
		}

		$parts = parse_url($url);
		if (!is_array($parts) || strtolower((string) ($parts['scheme'] ?? '')) !== 'https' || empty($parts['host'])) {
			return false;
		}
		$host = strtolower(rtrim((string) $parts['host'], '.'));
		return in_array($host, array('vizaje-nica.com', 'www.vizaje-nica.com', 'dev.vizaje-nica.com'), true)
			&& empty($parts['user']) && empty($parts['pass']) && empty($parts['port']);
	}

	private function removeImage($filename)
	{
		$filename = basename((string) $filename);
		$path = realpath(FCPATH . 'public' . DIRECTORY_SEPARATOR . 'site_dialogs');
		if ($filename === '' || $path === false) {
			return;
		}
		$file = $path . DIRECTORY_SEPARATOR . $filename;
		if (is_file($file)) {
			@unlink($file);
		}
	}
}
