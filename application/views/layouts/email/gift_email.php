<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:v="urn:schemas-microsoft-com:vml"
	xmlns:o="urn:schemas-microsoft-com:office:office"
>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">

	<title>Vizaje-Nica</title>
</head>

<body
	style="
		margin: 0;
		padding: 0;
		background-color: #f8f8f8;
		font-family: Arial, Helvetica, sans-serif;
		color: #111111;
	"
>

<table
	role="presentation"
	width="100%"
	cellspacing="0"
	cellpadding="0"
	border="0"
	style="width: 100%; background-color: #f8f8f8;"
>
	<tr>
		<td align="center" style="padding: 32px 12px;">

			<table
				role="presentation"
				width="600"
				cellspacing="0"
				cellpadding="0"
				border="0"
				style="
					width: 100%;
					max-width: 600px;
					background-color: #ffffff;
					border: 1px solid #e5e5e5;
					border-radius: 20px;
					overflow: hidden;
				"
			>

				<!-- HEADER -->
				<tr>
					<td
						align="center"
						style="
							padding: 28px 24px 22px;
							border-bottom: 1px solid #eeeeee;
						"
					>
						<img
							src="https://vizaje-nica.com/dist/img/footer_logo_2.png"
							alt="Vizaje-Nica"
							width="170"
							style="
								display: block;
								width: 170px;
								max-width: 100%;
								height: auto;
								border: 0;
							"
						>
					</td>
				</tr>

				<!-- INTRO -->
				<tr>
					<td style="padding: 40px 40px 0;">

						<div
							style="
								margin-bottom: 10px;
								font-size: 11px;
								line-height: 1.4;
								letter-spacing: 0.08em;
								text-transform: uppercase;
								color: #8b8b8b;
							"
						>
							Vizaje-Nica
						</div>

						<h1
							style="
								margin: 0;
								font-size: 32px;
								line-height: 1.08;
								font-weight: 400;
								letter-spacing: -0.03em;
								color: #111111;
							"
						>
							<?= str_replace(
                                '{price}',
                                $giftCardInfoPrice,
                                GIFT_CARD_EMAIL_TEXT1
                            ) ?>
						</h1>

					</td>
				</tr>

				<!-- CARD -->
				<tr>
					<td align="center" style="padding: 34px 40px 0;">

						<table
							role="presentation"
							width="100%"
							cellspacing="0"
							cellpadding="0"
							border="0"
							style="
								width: 100%;
								max-width: 430px;
								border-collapse: separate;
							"
						>
							<tr>
								<td
									align="center"
									background="https://vizaje-nica.com/public/gift_cards/<?= $giftCardImg ?>"
									style="
										height: 260px;
										border-radius: 18px;
										background-color: #f2f2f2;
										background-image:
											url('https://vizaje-nica.com/public/gift_cards/<?= $giftCardImg ?>');
										background-position: center;
										background-repeat: no-repeat;
										background-size: cover;
										overflow: hidden;
									"
								>

									<table
										role="presentation"
										width="100%"
										height="260"
										cellspacing="0"
										cellpadding="0"
										border="0"
									>
										<tr>
											<td
												align="center"
												valign="top"
												style="padding: 24px 24px 0;"
											>

												<?php if ($giftCardShowIcon == 1): ?>
													<img
														src="https://vizaje-nica.com/dist/img/gift_card_logo.png"
														alt="Vizaje-Nica"
														style="
															display: block;
															max-width: 76px;
															height: auto;
															margin: 0 auto;
														"
													>
												<?php endif; ?>

											</td>
										</tr>

										<tr>
											<td
												align="center"
												valign="middle"
												style="
													padding: 0 24px;
													color: <?= $giftCardTextColor ?>;
												"
											>
												<?php if ($giftCardShowName == 1): ?>
													<div
														style="
															font-size: 30px;
															line-height: 1.1;
															font-family: Georgia, 'Times New Roman', serif;
															color: <?= $giftCardTextColor ?>;
														"
													>
														<?= $giftCardTitle ?>
													</div>
												<?php endif; ?>
											</td>
										</tr>

										<tr>
											<td
												align="left"
												valign="bottom"
												style="padding: 0 24px 24px;"
											>
												<div
													style="
														margin-bottom: 5px;
														font-size: 11px;
														line-height: 1.3;
														text-transform: uppercase;
														letter-spacing: 0.06em;
														color: <?= $giftCardTextColor ?>;
														opacity: 0.72;
													"
												>
													<?= BALANCE ?>
												</div>

												<div
													style="
														font-size: 22px;
														line-height: 1;
														font-weight: 700;
														color: <?= $giftCardTextColor ?>;
													"
												>
													<?= $giftCardInfoPrice ?> <?= CURRENCY ?>
												</div>
											</td>
										</tr>
									</table>

								</td>
							</tr>
						</table>

					</td>
				</tr>

				<!-- AMOUNT -->
				<tr>
					<td align="center" style="padding: 30px 40px 0;">

						<div
							style="
								font-size: 12px;
								line-height: 1.4;
								text-transform: uppercase;
								letter-spacing: 0.08em;
								color: #8b8b8b;
							"
						>
							<?= BALANCE ?>
						</div>

						<div
							style="
								margin-top: 6px;
								font-size: 30px;
								line-height: 1;
								font-weight: 400;
								letter-spacing: -0.03em;
								color: #111111;
							"
						>
							<?= $giftCardInfoPrice ?> <?= CURRENCY ?>
						</div>

					</td>
				</tr>

				<!-- BARCODE -->
				<tr>
					<td align="center" style="padding: 32px 40px 0;">

						<table
							role="presentation"
							cellspacing="0"
							cellpadding="0"
							border="0"
							style="
								width: 100%;
								max-width: 360px;
								background-color: #f8f8f8;
								border: 1px solid #eeeeee;
								border-radius: 14px;
							"
						>
							<tr>
								<td align="center" style="padding: 24px;">

									<img
										src="<?= $giftsBarcodePath ?>"
										alt="Barcode"
										style="
											display: block;
											max-width: 260px;
											width: 100%;
											height: auto;
											border: 0;
											margin: 0 auto;
										"
									>

								</td>
							</tr>
						</table>

					</td>
				</tr>

				<!-- INFO -->
				<tr>
					<td
						style="
							padding: 32px 40px 0;
							font-size: 15px;
							line-height: 1.6;
							color: #666666;
						"
					>
						<?= GIFT_CARD_EMAIL_TEXT2 ?>
					</td>
				</tr>

				<!-- APP NOTE -->
                <tr>
                    <td style="padding: 34px 40px 0;">

                        <table
                            role="presentation"
                            width="100%"
                            cellspacing="0"
                            cellpadding="0"
                            border="0"
                            style="
                                width: 100%;
                                background-color: #f8f8f8;
                                border: 1px solid #eeeeee;
                                border-radius: 14px;
                            "
                        >
                            <tr>
                                <td style="padding: 24px;">

                                    <div
                                        style="
                                            margin-bottom: 7px;
                                            font-size: 16px;
                                            line-height: 1.3;
                                            font-weight: 700;
                                            color: #111111;
                                        "
                                    >
                                        Vizaje-Nica App
                                    </div>

                                    <div
                                        style="
                                            margin-bottom: 20px;
                                            font-size: 13px;
                                            line-height: 1.55;
                                            color: #777777;
                                        "
                                    >
                                        <?php if ($lclang === 'ru'): ?>
                                            Для получения и использования электронного подарочного сертификата необходимо приложение Vizaje-Nica.
                                        <?php else: ?>
                                            Pentru a primi și utiliza certificatul cadou electronic este necesară aplicația Vizaje-Nica.
                                        <?php endif; ?>
                                    </div>

                                    <table
                                        role="presentation"
                                        width="100%"
                                        cellspacing="0"
                                        cellpadding="0"
                                        border="0"
                                    >
                                        <tr>

                                            <!-- APP STORE -->
                                            <td
                                                width="50%"
                                                valign="middle"
                                                style="padding-right: 6px;"
                                            >
                                                <a
                                                    href="https://apps.apple.com/md/app/vizaje-nica/id1606198313"
                                                    target="_blank"
                                                    style="
                                                        display: block;
                                                        padding: 15px 12px;
                                                        border: 1px solid #d8d8d8;
                                                        text-align: center;
                                                        text-decoration: none;
                                                        color: #111111;
                                                        background-color: #ffffff;
                                                    "
                                                >
                                                    <table
                                                        role="presentation"
                                                        cellspacing="0"
                                                        cellpadding="0"
                                                        border="0"
                                                        align="center"
                                                    >
                                                        <tr>
                                                            <td
                                                                valign="middle"
                                                                style="padding-right: 9px;"
                                                            >
                                                                <img
                                                                    src="https://vizaje-nica.com/app/img/icons-2/appsotre-icon.png"
                                                                    alt=""
                                                                    width="24"
                                                                    style="
                                                                        display: block;
                                                                        width: 24px;
                                                                        height: auto;
                                                                        border: 0;
                                                                    "
                                                                >
                                                            </td>

                                                            <td
                                                                valign="middle"
                                                                style="
                                                                    font-family: Arial, Helvetica, sans-serif;
                                                                    font-size: 13px;
                                                                    font-weight: 600;
                                                                    color: #111111;
                                                                    white-space: nowrap;
                                                                "
                                                            >
                                                                App Store
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </a>
                                            </td>

                                            <!-- GOOGLE PLAY -->
                                            <td
                                                width="50%"
                                                valign="middle"
                                                style="padding-left: 6px;"
                                            >
                                                <a
                                                    href="https://play.google.com/store/apps/details?id=com.nicavizaje.project"
                                                    target="_blank"
                                                    style="
                                                        display: block;
                                                        padding: 15px 12px;
                                                        border: 1px solid #d8d8d8;
                                                        text-align: center;
                                                        text-decoration: none;
                                                        color: #111111;
                                                        background-color: #ffffff;
                                                    "
                                                >
                                                    <table
                                                        role="presentation"
                                                        cellspacing="0"
                                                        cellpadding="0"
                                                        border="0"
                                                        align="center"
                                                    >
                                                        <tr>
                                                            <td
                                                                valign="middle"
                                                                style="padding-right: 9px;"
                                                            >
                                                                <img
                                                                    src="https://vizaje-nica.com/app/img/icons-2/googleplay-icon.png"
                                                                    alt=""
                                                                    width="24"
                                                                    style="
                                                                        display: block;
                                                                        width: 24px;
                                                                        height: auto;
                                                                        border: 0;
                                                                    "
                                                                >
                                                            </td>

                                                            <td
                                                                valign="middle"
                                                                style="
                                                                    font-family: Arial, Helvetica, sans-serif;
                                                                    font-size: 13px;
                                                                    font-weight: 600;
                                                                    color: #111111;
                                                                    white-space: nowrap;
                                                                "
                                                            >
                                                                Google Play
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </a>
                                            </td>

                                        </tr>
                                    </table>

                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>

				<!-- FOOTER -->
				<tr>
					<td style="padding: 40px;">

						<table
							role="presentation"
							width="100%"
							cellspacing="0"
							cellpadding="0"
							border="0"
							style="
								width: 100%;
								border-top: 1px solid #eeeeee;
							"
						>
							<tr>

								<td
									align="left"
									valign="middle"
									style="padding-top: 22px;"
								>
									<img
										src="https://vizaje-nica.com/dist/img/footer_logo_2.png"
										alt="Vizaje-Nica"
										width="138"
										style="
											display: block;
											width: 138px;
											height: auto;
											border: 0;
										"
									>
								</td>

								<td
									align="right"
									valign="middle"
									style="padding-top: 22px;"
								>

									<a
										href="<?= SOCIAL_FACEBOOK_LINK ?>"
										target="_blank"
										style="
											display: inline-block;
											text-decoration: none;
											margin-right: 14px;
										"
									>
										<img
											src="https://vizaje-nica.com/dist/img/facebook_icon.png"
											alt="Facebook"
											width="24"
											height="24"
											style="
												display: block;
												width: 24px;
												height: 24px;
												border: 0;
											"
										>
									</a>

									<a
										href="<?= SOCIAL_INSTAGRAM_LINK ?>"
										target="_blank"
										style="
											display: inline-block;
											text-decoration: none;
										"
									>
										<img
											src="https://vizaje-nica.com/dist/img/instagram_icon.png"
											alt="Instagram"
											width="24"
											height="24"
											style="
												display: block;
												width: 24px;
												height: 24px;
												border: 0;
											"
										>
									</a>

								</td>

							</tr>
						</table>

					</td>
				</tr>

			</table>

		</td>
	</tr>
</table>

</body>
</html>