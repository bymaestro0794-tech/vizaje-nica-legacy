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
		$this->data['theme_settings'] = $this->site_themes_model->getThemeSettings();
		$this->data['active_theme_key'] = $this->site_themes_model->getActiveThemeKey($this->data['theme_settings']);
		$this->data['black_friday_settings_installed'] = $this->site_themes_model->isBlackFridaySettingsInstalled();
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

			$blackFridaySettings = array();
			if (in_array($themeKey, array('black_friday', 'red_friday'), true)) {
				if (!$this->site_themes_model->isBlackFridaySettingsInstalled()) {
					throw new Exception('Для тем Black Friday и Red Friday сначала примените database/patches/2026-11-16-black-friday-theme-settings.sql.');
				}

				$timezone = new DateTimeZone('Europe/Bucharest');
				$startsAt = $this->parseCampaignDateTime($this->input->post('black_friday_starts_at', true), $timezone);
				$endsAt = $this->parseCampaignDateTime($this->input->post('black_friday_ends_at', true), $timezone);
				if ($startsAt === null || $endsAt === null || $endsAt <= $startsAt) {
					throw new Exception('Укажите корректные даты начала и окончания акции. Окончание должно быть позже начала.');
				}

				$textRu = trim((string) $this->input->post('black_friday_text_ru', true));
				$textRo = trim((string) $this->input->post('black_friday_text_ro', true));
				if (
					$textRu === ''
					|| $textRo === ''
					|| $this->unicodeLength($textRu) > 255
					|| $this->unicodeLength($textRo) > 255
				) {
					throw new Exception('Введите текст объявления на русском и румынском (до 255 символов каждый).');
				}

				$blackFridaySettings = array(
					'starts_at' => $startsAt->format('Y-m-d H:i:s'),
					'ends_at' => $endsAt->format('Y-m-d H:i:s'),
					'text_ru' => $textRu,
					'text_ro' => $textRo,
				);
			}

			if (!$this->site_themes_model->saveThemeSettings($themeKey, $blackFridaySettings)) {
				throw new Exception('Выберите одну из подготовленных тем.');
			}

			$_SESSION['success'] = in_array($themeKey, array('black_friday', 'red_friday'), true)
				? 'Тема и настройки таймера сохранены.'
				: 'Атмосфера сайта сохранена.';
		} catch (Exception $e) {
			log_message('error', 'Site theme save error: ' . $e->getMessage());
			$_SESSION['error'] = array($e->getMessage());
		}

		redirect('/' . ADM_CONTROLLER . '/site_themes/');
	}

	private function parseCampaignDateTime($value, DateTimeZone $timezone)
	{
		$value = trim((string) $value);
		if ($value === '') {
			return null;
		}

		$date = DateTime::createFromFormat('!Y-m-d\\TH:i', $value, $timezone);
		$errors = DateTime::getLastErrors();
		if (
			$date === false
			|| ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
			|| $date->format('Y-m-d\\TH:i') !== $value
		) {
			return null;
		}

		return $date;
	}

	private function unicodeLength($value)
	{
		$length = preg_match_all('/./us', (string) $value, $matches);
		return $length === false ? PHP_INT_MAX : $length;
	}
}
