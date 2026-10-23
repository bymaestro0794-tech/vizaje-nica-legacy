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
				'description' => 'Зимний сине-фиолетовый градиент и праздничный акцент.',
				'preview' => 'Ледяной синий + ночной индиго',
			),
			'halloween' => array(
				'name' => 'Хэллоуин',
				'description' => 'Тёплый оранжевый акцент и небольшие тематические детали.',
				'preview' => 'Оранжевый + графитовый',
			),
		);
	}

	public function isInstalled()
	{
		return $this->db->table_exists('site_theme_settings');
	}

	public function getActiveThemeKey()
	{
		$presets = $this->getThemePresets();

		if (!$this->isInstalled()) {
			return 'default';
		}

		$row = $this->db
			->select('theme_key')
			->where('id', 1)
			->get('site_theme_settings')
			->row();

		$themeKey = !empty($row) && !empty($row->theme_key)
			? trim((string) $row->theme_key)
			: 'default';

		return isset($presets[$themeKey]) ? $themeKey : 'default';
	}

	public function saveActiveThemeKey($themeKey)
	{
		$presets = $this->getThemePresets();
		$themeKey = trim((string) $themeKey);

		if (!$this->isInstalled() || !isset($presets[$themeKey])) {
			return false;
		}

		return $this->db
			->where('id', 1)
			->update('site_theme_settings', array(
				'theme_key' => $themeKey,
				'updated_at' => gmdate('Y-m-d H:i:s'),
			));
	}
}
