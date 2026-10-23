<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Roulette extends BackEndController
{
	private $prizeCodes = array(
		'discount_15',
		'free_shipping',
		'bonus_100',
		'gift',
		'surprise',
		'promo_code',
		'discount_10',
	);

	private $supportedRewardTypes = array('discount_percent', 'free_shipping', 'gift');

	private $rewardTypeOptions = array(
		'discount_percent' => 'Скидка, %',
		'free_shipping' => 'Бесплатная доставка',
		'bonus_points' => 'Бонусные баллы',
		'gift' => 'Подарок в корзину',
		'promo_code' => 'Промокод',
		'surprise' => 'Сюрприз',
	);

	private $prizeTypes = array(
		'discount_15' => 'discount_percent',
		'discount_10' => 'discount_percent',
		'free_shipping' => 'free_shipping',
		'bonus_100' => 'bonus_points',
		'gift' => 'gift',
		'surprise' => 'surprise',
		'promo_code' => 'promo_code',
	);

	public function __construct()
	{
		parent::__construct(__CLASS__);
		$this->load->model('roulette_model');
		$this->load->model('gifts_model');
		$this->data['title'] = 'Рулетка удачи';
	}

	public function index()
	{
		$this->data['migration_required'] = !$this->roulette_model->isGameplayInstalled();
		$this->data['settings'] = array();
		$this->data['prizes'] = array();
		$this->data['gifts'] = array();
		if ($this->db->table_exists('gifts')) {
			$this->data['gifts'] = $this->gifts_model->get_enabled();
		}

		if (!$this->data['migration_required']) {
			$this->data['settings'] = $this->roulette_model->getSettings();
			$this->data['prizes'] = $this->roulette_model->getPrizes();
		}

		$this->data['prize_codes'] = $this->prizeCodes;
		$this->data['inner_view'] = 'dashboard/roulette/index';

		$this->load->vars($this->data);
		$this->load->view($this->main_layout);
	}

	public function save()
	{
		check_if_POST();

		try {
			if (!$this->roulette_model->isGameplayInstalled()) {
				throw new Exception('Сначала примените SQL-патчи рулетки из database/patches.');
			}

			$settingsPost = $this->input->post('settings', true);
			$prizesPost = $this->input->post('prizes', true);
			$settingsPost = is_array($settingsPost) ? $settingsPost : array();
			$prizesPost = is_array($prizesPost) ? $prizesPost : array();

			$settings = array(
				'titleRU' => trim((string) ($settingsPost['titleRU'] ?? '')),
				'titleRO' => trim((string) ($settingsPost['titleRO'] ?? '')),
				'descriptionRU' => trim((string) ($settingsPost['descriptionRU'] ?? '')),
				'descriptionRO' => trim((string) ($settingsPost['descriptionRO'] ?? '')),
				'rulesRU' => trim((string) ($settingsPost['rulesRU'] ?? '')),
				'rulesRO' => trim((string) ($settingsPost['rulesRO'] ?? '')),
				'is_visible' => !empty($settingsPost['is_visible']) ? 1 : 0,
				'is_enabled' => !empty($settingsPost['is_enabled']) ? 1 : 0,
				// The public rule is fixed: one spin per calendar week, Monday 00:00.
				'spin_interval_hours' => 168,
				'updated_at' => gmdate('Y-m-d H:i:s'),
			);

			if ($settings['titleRU'] === '' || $settings['titleRO'] === '') {
				throw new Exception('Заполните заголовок рулетки на русском и румынском языках.');
			}

			$normalizedPrizes = array();
			$enabledCount = 0;
			$probabilityTotal = 0;
			$existingPrizes = array();
			foreach ($this->roulette_model->getPrizes() as $existingPrize) {
				$existingPrizes[$existingPrize['code']] = $existingPrize;
			}

			foreach ($this->prizeCodes as $code) {
				if (
					!isset($existingPrizes[$code])
					|| !isset($prizesPost[$code])
					|| !is_array($prizesPost[$code])
				) {
					throw new Exception('Не удалось получить настройки всех секторов. Обновите страницу и повторите попытку.');
				}

				$row = $prizesPost[$code];
				$titleRU = trim((string) ($row['titleRU'] ?? ''));
				$titleRO = trim((string) ($row['titleRO'] ?? ''));
				$enabled = !empty($row['is_enabled']) ? 1 : 0;
				$valueRaw = trim((string) ($row['reward_value'] ?? ''));
				$payloadRaw = trim((string) ($row['reward_payload'] ?? ''));
				$chanceRaw = trim((string) ($row['probability'] ?? ''));

				if ($titleRU === '' || $titleRO === '') {
					throw new Exception('У каждого сектора должно быть название на русском и румынском языках.');
				}

				$rewardType = trim((string) ($row['reward_type'] ?? ''));
				if ($rewardType === '') {
					$rewardType = $existingPrizes[$code]['reward_type']
						?? ($this->prizeTypes[$code] ?? 'discount_percent');
				}
				if (!isset($this->rewardTypeOptions[$rewardType])) {
					throw new Exception('Выберите корректную категорию выигрыша для каждого сектора.');
				}
				if ($rewardType === 'gift' && $payloadRaw !== '') {
					if (!ctype_digit($payloadRaw) || (int) $payloadRaw < 1 || !$this->db->table_exists('gifts') || empty($this->gifts_model->find_enabled((int) $payloadRaw))) {
						throw new Exception('Выберите существующий активный подарок для сектора рулетки.');
					}
					$payloadRaw = (string) (int) $payloadRaw;
				}
				if ($enabled && $rewardType === 'gift' && $payloadRaw === '') {
					throw new Exception('Для каждого включённого сектора с подарком выберите подарок.');
				}

				$isSupportedType = in_array($rewardType, $this->supportedRewardTypes, true);
				$enabled = $isSupportedType ? $enabled : 0;
				$value = null;
				if ($rewardType === 'discount_percent' && $valueRaw !== '') {
					if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $valueRaw) || (float) $valueRaw <= 0 || (float) $valueRaw > 100) {
						throw new Exception('Размер процентной скидки должен быть больше 0 и не превышать 100%.');
					}
					$value = number_format((float) $valueRaw, 2, '.', '');
				} elseif ($rewardType === 'bonus_points' && $valueRaw !== '') {
					if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $valueRaw) || (float) $valueRaw <= 0 || (float) $valueRaw > 1000000) {
						throw new Exception('Количество бонусных баллов должно быть больше 0 и не превышать 1 000 000.');
					}
					$value = number_format((float) $valueRaw, 2, '.', '');
				}

				$probability = null;
				if ($chanceRaw !== '') {
					if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $chanceRaw) || (float) $chanceRaw < 0 || (float) $chanceRaw > 100) {
						throw new Exception('Вероятность сектора должна быть от 0 до 100% с точностью до двух знаков.');
					}
					$probability = number_format((float) $chanceRaw, 2, '.', '');
					if ($enabled) {
						if ((float) $probability <= 0) {
							throw new Exception('У включённого сектора вероятность должна быть больше 0%.');
						}
						$probabilityTotal += (int) round((float) $probability * 100);
					}
				}

				if ($enabled) {
					if ($rewardType === 'discount_percent' && $value === null) {
						throw new Exception('Для каждого включённого сектора со скидкой задайте размер скидки.');
					}
					if ($probability === null) {
						throw new Exception('Для каждого включённого сектора задайте вероятность.');
					}
					$enabledCount++;
				}

				$normalizedPrizes[$code] = array(
					'titleRU' => $titleRU,
					'titleRO' => $titleRO,
					'reward_type' => $rewardType,
					'reward_value' => $value,
					// An empty payload is intentional: it lets the admin clear an old gift/code.
					'reward_payload' => $payloadRaw !== '' ? $payloadRaw : null,
					'probability' => $probability,
					'is_enabled' => $enabled,
					'updated_at' => gmdate('Y-m-d H:i:s'),
				);
			}

			if ($enabledCount > 0 && $probabilityTotal !== 10000) {
				throw new Exception('Сумма вероятностей всех включённых секторов должна быть ровно 100%.');
			}
			if (!empty($settings['is_enabled'])) {
				if ($enabledCount < 1 || $probabilityTotal !== 10000) {
					throw new Exception('Чтобы включить рулетку, активируйте поддерживаемые призы с суммарной вероятностью 100%.');
				}
			}

			if (!$this->roulette_model->saveConfiguration($settings, $normalizedPrizes)) {
				throw new Exception('Не удалось сохранить настройки рулетки.');
			}

			$_SESSION['success'] = 'Настройки рулетки сохранены. Доступны скидки, бесплатная доставка и подарки из каталога.';
		} catch (Exception $e) {
			log_message('error', 'Roulette settings save error: ' . $e->getMessage());
			$_SESSION['error'] = array($e->getMessage());
		}

		redirect($this->path);
	}
}
