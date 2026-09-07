<?php

$successCopy = $lclang === 'ru'
	? array(
		'status' => 'Оплата прошла успешно',

		'title_line_1' => 'Спасибо',
		'title_line_2' => 'за покупку',

		'description' =>
			'Подарочный сертификат успешно оплачен. Он будет доступен получателю в приложении Vizaje-Nica.',

		'app_title' => 'Откройте сертификат в приложении',

		'app_text' =>
			'Для получения и использования электронного подарочного сертификата необходимо приложение Vizaje-Nica.',

		'next_label' => 'Что дальше',

		'ready_title' => 'Сертификат уже готов',

		'instant_text' =>
			'Если выбрана отправка сразу, сертификат появится у получателя после подтверждения платежа.',

		'scheduled_text' =>
			'Если была установлена дата и время, отправка произойдёт в выбранный момент.',

		'certificates_button' => 'К сертификатам',

		'home_button' => 'На главную',

		'app_store' => 'App Store',
		'google_play' => 'Google Play',

		'app_store_qr_alt' => 'QR-код для App Store',
		'google_play_qr_alt' => 'QR-код для Google Play',
	)
	: array(
		'status' => 'Plata a fost efectuată cu succes',

		'title_line_1' => 'Mulțumim',
		'title_line_2' => 'pentru cumpărătură',

		'description' =>
			'Certificatul cadou a fost achitat cu succes. Acesta va fi disponibil destinatarului în aplicația Vizaje-Nica.',

		'app_title' => 'Deschide certificatul în aplicație',

		'app_text' =>
			'Pentru a primi și utiliza certificatul cadou electronic este necesară aplicația Vizaje-Nica.',

		'next_label' => 'Ce urmează',

		'ready_title' => 'Certificatul este pregătit',

		'instant_text' =>
			'Dacă a fost selectată expedierea imediată, certificatul va apărea la destinatar după confirmarea plății.',

		'scheduled_text' =>
			'Dacă a fost setată o dată și o oră, certificatul va fi trimis la momentul ales.',

		'certificates_button' => 'La certificate',

		'home_button' => 'Pagina principală',

		'app_store' => 'App Store',
		'google_play' => 'Google Play',

		'app_store_qr_alt' => 'Cod QR pentru App Store',
		'google_play_qr_alt' => 'Cod QR pentru Google Play',
	);

?>

<main class="page">

	<style>
		.certificate-success {
			min-height: calc(100vh - 142px);
			background: #f8f8f8;
			padding: 72px 24px 96px;
		}

		.certificate-success__container {
			width: min(1180px, 100%);
			margin: 0 auto;
		}

		.certificate-success__top {
			max-width: 760px;
			margin-bottom: 64px;
		}

		.certificate-success__status {
			display: inline-flex;
			align-items: center;
			gap: 10px;

			margin-bottom: 28px;

			font-size: 12px;
			font-weight: 500;
			letter-spacing: 0.08em;
			text-transform: uppercase;
			color: rgba(17, 17, 17, 0.52);
		}

		.certificate-success__status-icon {
			display: flex;
			align-items: center;
			justify-content: center;

			width: 30px;
			height: 30px;

			border: 1px solid rgba(17, 17, 17, 0.18);
			border-radius: 50%;
		}

		.certificate-success__title {
			max-width: 760px;
			margin: 0;

			font-size: clamp(54px, 6vw, 88px);
			font-weight: 400;
			line-height: 0.94;
			letter-spacing: -0.055em;
			color: #111;
		}

		.certificate-success__description {
			max-width: 620px;
			margin: 26px 0 0;

			font-size: 16px;
			line-height: 1.55;
			color: rgba(17, 17, 17, 0.52);
		}

		.certificate-success__grid {
			display: grid;
			grid-template-columns: minmax(0, 1fr) minmax(360px, 0.72fr);
			gap: 28px;
		}

		.certificate-success__card {
			padding: 42px;

			border: 1px solid rgba(17, 17, 17, 0.1);
			border-radius: 24px;

			background: rgba(255, 255, 255, 0.58);
			backdrop-filter: blur(20px);
		}

		.certificate-success__card-title {
			margin: 0;

			font-size: 28px;
			font-weight: 400;
			letter-spacing: -0.035em;
			color: #111;
		}

		.certificate-success__card-text {
			max-width: 560px;
			margin: 12px 0 0;

			font-size: 14px;
			line-height: 1.55;
			color: rgba(17, 17, 17, 0.5);
		}

		.certificate-success__stores {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 30px;
        }

		.certificate-success__store {
            display: flex;
            align-items: center;
            justify-content: center;

            gap: 10px;

            min-width: 0;
            min-height: 58px;

            padding: 12px 16px;

            overflow: hidden;

            border: 1px solid rgba(17, 17, 17, 0.16);

            font-size: 13px;
            font-weight: 500;

            color: #111;
            background: transparent;

            text-decoration: none;

            transition:
                background-color 0.25s ease,
                color 0.25s ease,
                border-color 0.25s ease;
        }

        .certificate-success__store img {
            display: block;

            width: 26px;
            height: 26px;

            flex: 0 0 26px;

            object-fit: contain;
        }

        .certificate-success__store p,
        .certificate-success__store span {
            margin: 0;

            font-size: 13px;
            line-height: 1;

            white-space: nowrap;
        }

		.certificate-success__store:hover {
			background: #111;
			color: #fff;
		}

		.certificate-success__qr-grid {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 18px;

			margin-top: 34px;
		}

		.certificate-success__qr {
			display: flex;
			flex-direction: column;
			align-items: center;
			gap: 12px;

			padding: 20px;

			border: 1px solid rgba(17, 17, 17, 0.1);
			background: #fff;
		}

		.certificate-success__qr img {
			display: block;

			width: 132px;
			height: 132px;

			object-fit: contain;
		}

		.certificate-success__qr span {
			font-size: 12px;
			color: rgba(17, 17, 17, 0.55);
		}

		.certificate-success__side {
			display: flex;
			flex-direction: column;
			justify-content: space-between;

			min-height: 100%;
		}

		.certificate-success__note-label {
			font-size: 11px;
			font-weight: 500;
			letter-spacing: 0.08em;
			text-transform: uppercase;
			color: rgba(17, 17, 17, 0.42);
		}

		.certificate-success__note-title {
			margin: 12px 0 0;

			font-size: 30px;
			font-weight: 400;
			line-height: 1.08;
			letter-spacing: -0.035em;
		}

		.certificate-success__note-text {
			margin: 14px 0 0;

			font-size: 14px;
			line-height: 1.55;
			color: rgba(17, 17, 17, 0.52);
		}

		.certificate-success__actions {
			display: flex;
			flex-direction: column;
			gap: 10px;

			margin-top: 48px;
		}

		.certificate-success__action {
			display: flex;
			align-items: center;
			justify-content: center;

			min-height: 56px;
			padding: 14px 20px;

			border: 1px solid #111;

			font-size: 12px;
			font-weight: 500;
			letter-spacing: 0.04em;
			text-transform: uppercase;

			text-decoration: none;
		}

		.certificate-success__action--primary {
			background: #111;
			color: #fff;
		}

		.certificate-success__action--secondary {
			background: transparent;
			color: #111;
		}

		@media (max-width: 767.98px) {
			.certificate-success {
				min-height: 0;
				padding: 42px 18px 60px;
			}

			.certificate-success__top {
				margin-bottom: 36px;
			}

			.certificate-success__title {
				font-size: clamp(42px, 13vw, 58px);
			}

			.certificate-success__description {
				font-size: 14px;
			}

			.certificate-success__grid {
				grid-template-columns: 1fr;
			}

			.certificate-success__card {
				padding: 26px 20px;
				border-radius: 18px;
			}

			.certificate-success__stores {
				grid-template-columns: 1fr;
			}

			.certificate-success__qr-grid {
				gap: 10px;
			}

			.certificate-success__qr {
				padding: 14px 8px;
			}

			.certificate-success__qr img {
				width: 112px;
				height: 112px;
			}
		}
	</style>

	<section class="certificate-success">

	<div class="certificate-success__container">

		<div class="certificate-success__top">

			<div class="certificate-success__status">

				<span class="certificate-success__status-icon">
					<svg
						width="14"
						height="14"
						viewBox="0 0 14 14"
						fill="none"
						aria-hidden="true"
					>
						<path
							d="M3 7.3L5.5 9.8L11 4.3"
							stroke="currentColor"
							stroke-width="1.5"
							stroke-linecap="round"
							stroke-linejoin="round"
						/>
					</svg>
				</span>

				<?= $successCopy['status'] ?>

			</div>

			<h1 class="certificate-success__title">
				<?= $successCopy['title_line_1'] ?><br>
				<?= $successCopy['title_line_2'] ?>
			</h1>

			<p class="certificate-success__description">
				<?= $successCopy['description'] ?>
			</p>

		</div>

		<div class="certificate-success__grid">

			<div class="certificate-success__card">

				<h2 class="certificate-success__card-title">
					<?= $successCopy['app_title'] ?>
				</h2>

				<p class="certificate-success__card-text">
					<?= $successCopy['app_text'] ?>
				</p>

				<div class="certificate-success__stores">

					<a
						class="certificate-success__store"
						href="https://apps.apple.com/md/app/vizaje-nica/id1606198313"
						target="_blank"
						rel="noopener noreferrer"
					>
						<img
							src="/app/img/icons-2/appsotre-icon.png"
							alt="app store"
						>

						<p><?= $successCopy['app_store'] ?></p>
					</a>

					<a
						class="certificate-success__store"
						href="https://play.google.com/store/apps/details?id=com.nicavizaje.project&hl=ru"
						target="_blank"
						rel="noopener noreferrer"
					>
						<img
							src="/app/img/icons-2/googleplay-icon.png"
							alt="google play"
						>

						<span><?= $successCopy['google_play'] ?></span>
					</a>

				</div>

				<div class="certificate-success__qr-grid">

					<div class="certificate-success__qr">

						<img
							src="/app/img/qr-code/appstore.png"
							alt="<?= htmlspecialchars(
								$successCopy['app_store_qr_alt'],
								ENT_QUOTES,
								'UTF-8'
							) ?>"
						>

						<span><?= $successCopy['app_store'] ?></span>

					</div>

					<div class="certificate-success__qr">

						<img
							src="/app/img/qr-code/google-play.png"
							alt="<?= htmlspecialchars(
								$successCopy['google_play_qr_alt'],
								ENT_QUOTES,
								'UTF-8'
							) ?>"
						>

						<span><?= $successCopy['google_play'] ?></span>

					</div>

				</div>

			</div>

			<div class="certificate-success__card certificate-success__side">

				<div>

					<div class="certificate-success__note-label">
						<?= $successCopy['next_label'] ?>
					</div>

					<h2 class="certificate-success__note-title">
						<?= $successCopy['ready_title'] ?>
					</h2>

					<p class="certificate-success__note-text">
						<?= $successCopy['instant_text'] ?>
					</p>

					<p class="certificate-success__note-text">
						<?= $successCopy['scheduled_text'] ?>
					</p>

				</div>

				<div class="certificate-success__actions">

					<a
						href="/<?= $lclang ?>/<?= $lclang === 'ru'
							? 'podarochnyie-sertifikatyi'
							: 'certificat-cadou' ?>"
						class="certificate-success__action certificate-success__action--primary"
					>
						<?= $successCopy['certificates_button'] ?>
					</a>

					<a
						href="/<?= $lclang ?>"
						class="certificate-success__action certificate-success__action--secondary"
					>
						<?= $successCopy['home_button'] ?>
					</a>

				</div>

			</div>

		</div>

	</div>

</section>

</main>