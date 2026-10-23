<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Stable, first-party entry point for the printed Vizaje-Nica application QR.
 * The QR always encodes https://vizaje-nica.com/get-app, not a third-party short link.
 */
class App_download extends CI_Controller
{
	private const APP_STORE_URL = 'https://apps.apple.com/ua/app/vizaje-nica/id1606198313';
	private const GOOGLE_PLAY_URL = 'https://play.google.com/store/apps/details?id=com.nicavizaje.project&hl=ru';

	public function index()
	{
		$userAgent = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
		$showLanding = (string) $this->input->get('preview', true) === '1';
		if (!$showLanding && preg_match('/Android/i', $userAgent)) {
			redirect(self::GOOGLE_PLAY_URL);
			return;
		}
		if (!$showLanding && preg_match('/iPhone|iPad|iPod/i', $userAgent)) {
			redirect(self::APP_STORE_URL);
			return;
		}

		$acceptLanguage = strtolower((string) ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));
		$isRomanian = strpos($acceptLanguage, 'ro') === 0;
		$data = array(
			'language' => $isRomanian ? 'ro' : 'ru',
			'page_title' => $isRomanian ? 'Aplicația Vizaje-Nica' : 'Приложение Vizaje-Nica',
			'page_description' => $isRomanian
				? 'Descarcă aplicația mobilă Vizaje-Nica.'
				: 'Скачайте мобильное приложение Vizaje-Nica.',
			'title' => $isRomanian ? 'Aplicația Vizaje-Nica' : 'Приложение Vizaje-Nica',
			'description' => $isRomanian
				? 'Scanează codul QR cu telefonul sau alege magazinul potrivit pentru dispozitivul tău.'
				: 'Отсканируйте QR-код телефоном или выберите магазин для своего устройства.',
			'qr_caption' => $isRomanian ? 'Scanează pentru a descărca aplicația' : 'Сканируйте, чтобы скачать приложение',
			'app_store' => 'App Store',
			'google_play' => 'Google Play',
			'app_store_url' => self::APP_STORE_URL,
			'google_play_url' => self::GOOGLE_PLAY_URL,
		);

		$this->output->set_header('Cache-Control: no-store, max-age=0');
		$this->output->set_header('X-Content-Type-Options: nosniff');
		$this->load->view('pages/app_download/index', $data);
	}
}
