<!-- BEGIN PAGE HEADER -->
<div class="page-bar">
	<ul class="page-breadcrumb">
		<li>
			<i class="fa fa-home"></i>

			<a href="/<?= ADM_CONTROLLER ?>/menu/">
				<?= lang('Home') ?>
			</a>

			<i class="fa fa-circle"></i>
		</li>

		<li>
			<a href="<?= $parent_url ?>">
				<?= htmlspecialchars(
					$parent_title,
					ENT_QUOTES,
					'UTF-8'
				) ?>
			</a>

			<i class="fa fa-circle"></i>
		</li>

		<li>
			<span>
				<?= htmlspecialchars(
					$title,
					ENT_QUOTES,
					'UTF-8'
				) ?>
			</span>
		</li>
	</ul>
</div>

<h1 class="page-title">
	<?= htmlspecialchars(
		$title,
		ENT_QUOTES,
		'UTF-8'
	) ?>
</h1>

<!-- Messages -->
<?php if (isset($_SESSION['success'])) : ?>
	<div class="alert alert-block alert-success fade in">
		<button
			type="button"
			class="close"
			data-dismiss="alert"
		></button>

		<?= $_SESSION['success'] ?>
	</div>

	<?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])) : ?>
	<div class="alert alert-block alert-danger fade in">
		<button
			type="button"
			class="close"
			data-dismiss="alert"
		></button>

		<?php foreach ((array) $_SESSION['error'] as $error) : ?>
			<?= htmlspecialchars(
				(string) $error,
				ENT_QUOTES,
				'UTF-8'
			) ?>

			<br>
		<?php endforeach; ?>
	</div>

	<?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="row">
	<div class="portlet light">
		<div class="portlet-body">
			<form
				method="post"
				enctype="multipart/form-data"
			>
				<div class="panel-body">
					<div class="table-scrollable">
						<table
							class="
								table
								table-bordered
								table-striped
								table-hover
							"
						>
							<tbody>
								<tr>
									<td width="220">
										Название RU *
									</td>

									<td>
										<input
											type="text"
											name="titleRU"
											class="form-control"
											value="<?= htmlspecialchars(
												(string) $item->titleRU,
												ENT_QUOTES,
												'UTF-8'
											) ?>"
											required
										>
									</td>
								</tr>

								<tr>
									<td width="220">
										Название RO *
									</td>

									<td>
										<input
											type="text"
											name="titleRO"
											class="form-control"
											value="<?= htmlspecialchars(
												(string) $item->titleRO,
												ENT_QUOTES,
												'UTF-8'
											) ?>"
											required
										>
									</td>
								</tr>

								<tr>
									<td width="220">
										Ссылка RU *
									</td>

									<td>
										<input
											type="text"
											name="urlRU"
											class="form-control"
											value="<?= htmlspecialchars(
												(string) $item->urlRU,
												ENT_QUOTES,
												'UTF-8'
											) ?>"
											required
										>
									</td>
								</tr>

								<tr>
									<td width="220">
										Ссылка RO *
									</td>

									<td>
										<input
											type="text"
											name="urlRO"
											class="form-control"
											value="<?= htmlspecialchars(
												(string) $item->urlRO,
												ENT_QUOTES,
												'UTF-8'
											) ?>"
											required
										>
									</td>
								</tr>

								<tr>
									<td width="220">
										Показывать
									</td>

									<td>
										<label
											class="
												mt-checkbox
												mt-checkbox-outline
											"
										>
											<input
												type="checkbox"
												name="isShown"
												value="1"
												<?= !empty($item->isShown)
													? 'checked'
													: '' ?>
											>

											Да

											<span></span>
										</label>
									</td>
								</tr>

								<tr>
									<td width="220">
										Новое изображение
									</td>

									<td>
										<input
											type="file"
											name="img"
											class="form-control"
											accept="image/jpeg,image/png,image/webp"
										>

										<div
											class="note note-warning"
											style="
												margin-top: 10px;
												margin-bottom: 0;
											"
										>
											<p>
												Рекомендуемый размер:
												400 × 400 px.
												При загрузке нового файла
												текущее изображение будет заменено.
											</p>
										</div>

										<?php if (!empty($item->img)) : ?>
											<?php
											$previewImage = newthumbs(
												$item->img,
												'home_categories',
												250,
												250,
												'250x250x1',
												1
											);
											?>

											<br>

											<div
												class="
													mt-element-card
													mt-element-overlay
													item
												"
											>
												<div
													class="
														col-lg-3
														col-md-4
														col-sm-6
														col-xs-12
													"
												>
													<div class="mt-card-item">
														<div
															class="
																mt-card-avatar
																mt-overlay-1
															"
														>
															<img
																src="<?= htmlspecialchars(
																	$previewImage,
																	ENT_QUOTES,
																	'UTF-8'
																) ?>"
																alt=""
															>

															<div class="mt-overlay">
																<ul class="mt-info">
																	<li>
																		<a
																			class="
																				btn
																				red
																				mine_delete_photo
																			"
																			data-table="<?= $table ?>"
																			data-id="<?= (int) $item->id ?>"
																			data-col="img"
																			href="javascript:;"
																		>
																			<i class="fa fa-ban"></i>
																		</a>
																	</li>
																</ul>
															</div>
														</div>
													</div>
												</div>
											</div>
										<?php endif; ?>
									</td>
								</tr>

								<tr>
									<td width="220">&nbsp;</td>

									<td>
										<button
											type="submit"
											class="btn green"
										>
											<i class="fa fa-check"></i>

											<?= lang('Edit') ?>
										</button>

										<a
											href="<?= $parent_url ?>"
											class="btn default"
										>
											Назад
										</a>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>