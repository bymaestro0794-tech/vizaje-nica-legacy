<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Site_themes_model extends CI_Model
{
	public function getThemePresets()
	{
		return array(
			'default' => array(
				'name' => 'Обычная тема',
				'description' => 'Светлый небесно-голубой градиент с мягким свечением.',
				'preview' => 'Небесно-голубой + лазурный',
			),
			'new_year' => array(
				'name' => 'Новый год',
				'description' => 'Ночное зимнее небо, мягкий снег и ледяные акценты.',
				'preview' => 'Ночной синий + лёд и шампань',
			),
			'halloween' => array(
				'name' => 'Хэллоуин',
				'description' => 'Тёплый оранжевый акцент и небольшие тематические детали.',
				'preview' => 'Оранжевый + графитовый',
			),
			'black_friday' => array(
				'name' => 'Black Friday',
				'description' => 'Чёрная верхняя плашка, жёлтые акценты и таймер акции.',
				'preview' => 'Чёрный + #FFE600',
			),
			'red_friday' => array(
				'name' => 'Red Friday',
				'description' => 'Оформление Black Friday с насыщенными красными акцентами.',
				'preview' => 'Чёрный + красный',
			),
		);
	}

	public function isInstalled()
	{
		return $this->db->table_exists('site_theme_settings');
	}

	public function isBlackFridaySettingsInstalled()
	{
		if (!$this->isInstalled()) {
			return false;
		}

		$fieldNames = array();
		foreach ((array) $this->db->field_data('site_theme_settings') as $field) {
			if (isset($field->name)) {
				$fieldNames[$field->name] = true;
			}
		}

		foreach (array(
			'black_friday_starts_at',
			'black_friday_ends_at',
			'black_friday_text_ru',
			'black_friday_text_ro',
		) as $field) {
			if (!isset($fieldNames[$field])) {
				return false;
			}
		}

		return true;
	}

	public function getThemeSettings()
	{
		$settings = array(
			'theme_key' => 'default',
			'black_friday_starts_at' => '',
			'black_friday_ends_at' => '',
			'black_friday_text_ru' => '',
			'black_friday_text_ro' => '',
		);

		if (!$this->isInstalled()) {
			return $settings;
		}

		$row = $this->db
			->where('id', 1)
			->get('site_theme_settings')
			->row();

		if (empty($row)) {
			return $settings;
		}

		foreach (array_keys($settings) as $field) {
			if (isset($row->$field)) {
				$settings[$field] = (string) $row->$field;
			}
		}

		return $settings;
	}

	public function getActiveThemeKey(array $settings = null)
	{
		$presets = $this->getThemePresets();
		$settings = $settings === null ? $this->getThemeSettings() : $settings;
		$themeKey = trim((string) ($settings['theme_key'] ?? 'default'));

		return isset($presets[$themeKey]) ? $themeKey : 'default';
	}

	public function getActiveBlackFridayCampaign(array $settings = null)
	{
		$settings = $settings === null ? $this->getThemeSettings() : $settings;
		if (!in_array(($settings['theme_key'] ?? ''), array('black_friday', 'red_friday'), true)) {
			return null;
		}
		if (!$this->isBlackFridaySettingsInstalled()) {
			return null;
		}

		$timezone = new DateTimeZone('Europe/Bucharest');
		$startsAt = $this->parseStoredDateTime($settings['black_friday_starts_at'], $timezone);
		$endsAt = $this->parseStoredDateTime($settings['black_friday_ends_at'], $timezone);
		$now = new DateTime('now', $timezone);
		$textRu = trim($settings['black_friday_text_ru']);
		$textRo = trim($settings['black_friday_text_ro']);

		if (
			$startsAt === null
			|| $endsAt === null
			|| $textRu === ''
			|| $textRo === ''
			|| $endsAt <= $startsAt
			|| $now < $startsAt
			|| $now >= $endsAt
		) {
			return null;
		}

		return array(
			'text_ru' => $textRu,
			'text_ro' => $textRo,
			'starts_at' => $startsAt->getTimestamp(),
			'ends_at' => $endsAt->getTimestamp(),
		);
	}

	private function parseStoredDateTime($value, DateTimeZone $timezone)
	{
		$value = trim((string) $value);
		if ($value === '') {
			return null;
		}

		$date = DateTime::createFromFormat('!Y-m-d H:i:s', $value, $timezone);
		$errors = DateTime::getLastErrors();
		if (
			$date === false
			|| ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
		) {
			return null;
		}

		return $date;
	}

	public function saveActiveThemeKey($themeKey)
	{
		return $this->saveThemeSettings($themeKey);
	}

	public function saveThemeSettings($themeKey, array $blackFridaySettings = array())
	{
		$presets = $this->getThemePresets();
		$themeKey = trim((string) $themeKey);

		if (!$this->isInstalled() || !isset($presets[$themeKey])) {
			return false;
		}

		$update = array(
			'theme_key' => $themeKey,
			'updated_at' => gmdate('Y-m-d H:i:s'),
		);

		if (in_array($themeKey, array('black_friday', 'red_friday'), true)) {
			if (!$this->isBlackFridaySettingsInstalled()) {
				return false;
			}

			foreach (array('starts_at', 'ends_at', 'text_ru', 'text_ro') as $field) {
				if (!isset($blackFridaySettings[$field]) || trim((string) $blackFridaySettings[$field]) === '') {
					return false;
				}
			}

			$update['black_friday_starts_at'] = $blackFridaySettings['starts_at'];
			$update['black_friday_ends_at'] = $blackFridaySettings['ends_at'];
			$update['black_friday_text_ru'] = trim((string) $blackFridaySettings['text_ru']);
			$update['black_friday_text_ro'] = trim((string) $blackFridaySettings['text_ro']);
		}

		return $this->db
			->where('id', 1)
			->update('site_theme_settings', $update);
	}
}
