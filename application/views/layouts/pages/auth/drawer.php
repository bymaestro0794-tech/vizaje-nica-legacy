<?php
defined('BASEPATH') or exit('No direct script access allowed');

$lclang = isset($lclang)
	? strtolower(trim((string) $lclang))
	: 'ru';

$isRomanian = $lclang === 'ro';

$menuItems = !empty($menu['all'])
	? $menu['all']
	: [];

$getMenuValue = static function (
	array $items,
	$index,
	$field,
	$fallback = ''
) {
	if (
		isset($items[$index])
		&& isset($items[$index]->{$field})
	) {
		return trim(
			(string) $items[$index]->{$field}
		);
	}

	return $fallback;
};

$buildLocalizedUrl = static function (
	$language,
	$uri
) {
	$language = trim(
		(string) $language,
		'/'
	);

	$uri = trim(
		(string) $uri,
		'/'
	);

	if ($uri === '') {
		return '/' . $language;
	}

	return '/'
		. $language
		. '/'
		. $uri;
};

$loginTitle = $getMenuValue(
	$menuItems,
	12,
	'title',
	$isRomanian ? 'Intrare' : 'Войти'
);

$loginUri = $getMenuValue(
	$menuItems,
	12,
	'uri'
);

$registerTitle = $getMenuValue(
	$menuItems,
	27,
	'title',
	$isRomanian
		? 'Înregistrare'
		: 'Регистрация'
);

$registerUri = $getMenuValue(
	$menuItems,
	27,
	'uri'
);

$forgotTitle = $getMenuValue(
	$menuItems,
	14,
	'title',
	$isRomanian
		? 'Resetarea parolei'
		: 'Восстановление пароля'
);

$forgotUri = $getMenuValue(
	$menuItems,
	14,
	'uri'
);

$privacyTitle = $getMenuValue(
	$menuItems,
	22,
	'title',
	$isRomanian
		? 'Politica de confidențialitate'
		: 'Политика конфиденциальности'
);

$privacyUri = $getMenuValue(
	$menuItems,
	22,
	'uri'
);

$authData = [
	'lclang' => $lclang,
	'isRomanian' => $isRomanian,

	'loginTitle' => $loginTitle,
	'registerTitle' => $registerTitle,
	'forgotTitle' => $forgotTitle,

	'loginAction' =>
		'/' . $lclang . '/login_form',

	'registerAction' =>
		'/' . $lclang . '/registration_form',

	'forgotAction' =>
		'/' . $lclang . '/reset_form',

	'codeAction' =>
		'/' . $lclang . '/login_form_code',

	'privacyTitle' => $privacyTitle,
	'privacyUrl' => $buildLocalizedUrl(
		$lclang,
		$privacyUri
	),
];

$authScreens = [
	'login',
	'register',
	'forgot',
	'forgot-sent',
	'sms-code',
	'register-success',
];
?>

<div
	class="auth-drawer"
	data-auth-drawer
	aria-hidden="true"
>
	<button
		type="button"
		class="auth-drawer__overlay"
		data-auth-close
		aria-label="<?= $isRomanian
			? 'Închide'
			: 'Закрыть' ?>"
	></button>

	<aside
		class="auth-drawer__panel"
		role="dialog"
		aria-modal="true"
		aria-label="<?= $isRomanian
			? 'Autentificare'
			: 'Авторизация' ?>"
	>
		<button
			type="button"
			class="auth-drawer__close"
			data-auth-close
			aria-label="<?= $isRomanian
				? 'Închide'
				: 'Закрыть' ?>"
		>
			<span></span>
			<span></span>
		</button>

		<div class="auth-drawer__content">
			<?php foreach ($authScreens as $screen) : ?>
				<?php
				$this->load->view(
					'layouts/pages/auth/screens/' . $screen,
					$authData
				);
				?>
			<?php endforeach; ?>
		</div>
	</aside>
</div>