<?php
defined('BASEPATH') or exit('No direct script access allowed');
$escape = static function ($value) {
	return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>
<!doctype html>
<html lang="<?= $escape($language) ?>">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="<?= $escape($page_description) ?>">
	<link rel="canonical" href="https://vizaje-nica.com/get-app">
	<title><?= $escape($page_title) ?></title>
	<link rel="stylesheet" href="/app/css/pages/app-download.css">
</head>
<body>
	<main class="app-download" aria-labelledby="app-download-title">
		<div class="app-download__card">
			<img class="app-download__logo" src="/app/img/icons-2/logo-2.svg" alt="Vizaje-Nica">
			<p class="app-download__eyebrow">VIZAJE-NICA</p>
			<h1 id="app-download-title" class="app-download__title"><?= $escape($title) ?></h1>
			<p class="app-download__description"><?= $escape($description) ?></p>

			<div class="app-download__qr-wrap">
				<img class="app-download__qr" src="/app/img/qr-code/vizaje-nica-app.png" alt="QR-код для скачивания приложения Vizaje-Nica">
				<p><?= $escape($qr_caption) ?></p>
			</div>

			<div class="app-download__stores" aria-label="Магазины приложений">
				<a class="app-download__store" href="<?= $escape($app_store_url) ?>" target="_blank" rel="noopener noreferrer">
					<img src="/app/img/icons-2/appsotre-icon.png" alt="" aria-hidden="true">
					<span><?= $escape($app_store) ?></span>
				</a>
				<a class="app-download__store" href="<?= $escape($google_play_url) ?>" target="_blank" rel="noopener noreferrer">
					<img src="/app/img/icons-2/googleplay-icon.png" alt="" aria-hidden="true">
					<span><?= $escape($google_play) ?></span>
				</a>
			</div>
		</div>
	</main>
</body>
</html>
