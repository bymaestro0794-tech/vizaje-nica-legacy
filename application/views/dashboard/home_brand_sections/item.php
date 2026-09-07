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
									<td width="230">
										Название бренда RU *
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
									<td>
										Название бренда RO *
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
									<td>
										Ссылка бренда RU *
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
									<td>
										Ссылка бренда RO *
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
									<td>
										Позиция на главной
									</td>

									<td>
										<select
											name="placement"
											class="form-control"
											required
										>
											<?php foreach ($placements as $key => $label) : ?>
												<option
													value="<?= htmlspecialchars(
														$key,
														ENT_QUOTES,
														'UTF-8'
													) ?>"
													<?= $item->placement === $key
														? 'selected'
														: '' ?>
												>
													<?= htmlspecialchars(
														$label,
														ENT_QUOTES,
														'UTF-8'
													) ?>
												</option>
											<?php endforeach; ?>
										</select>
									</td>
								</tr>

								<tr>
									<td>
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
									<td>
										Desktop-баннер
									</td>

									<td>
										<input
											type="file"
											name="img"
											class="form-control"
											accept="image/jpeg,image/png,image/webp"
										>

										<?php if (!empty($item->img)) : ?>
											<?php
											$desktopPreview = newthumbs(
												$item->img,
												'home_brand_sections',
												500,
												220,
												'500x220x1',
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
														col-lg-6
														col-md-8
														col-sm-12
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
																	$desktopPreview,
																	ENT_QUOTES,
																	'UTF-8'
																) ?>"
																alt=""
															>

															<div class="mt-overlay">
																<ul class="mt-info">
																	<li>
																	<a
                                                                    	href="<?= htmlspecialchars(
                                                                    		$delete_desktop_image_path,
                                                                    		ENT_QUOTES,
                                                                    		'UTF-8'
                                                                    	) ?>"
                                                                    	class="btn red mine_delete_row"
                                                                    	title="Удалить desktop-баннер"
                                                                    >
                                                                    	<i class="fa fa-trash"></i>
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
									<td>
										Mobile-баннер
									</td>

									<td>
										<input
											type="file"
											name="imgMob"
											class="form-control"
											accept="image/jpeg,image/png,image/webp"
										>

										<?php if (!empty($item->imgMob)) : ?>
											<?php
											$mobilePreview = newthumbs(
												$item->imgMob,
												'home_brand_sections',
												260,
												320,
												'260x320x1',
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
																	$mobilePreview,
																	ENT_QUOTES,
																	'UTF-8'
																) ?>"
																alt=""
															>

															<div class="mt-overlay">
																<ul class="mt-info">
																	<li>
																		<a
                                                                        	href="<?= htmlspecialchars(
                                                                        		$delete_mobile_image_path,
                                                                        		ENT_QUOTES,
                                                                        		'UTF-8'
                                                                        	) ?>"
                                                                        	class="btn red mine_delete_row"
                                                                        	title="Удалить mobile-баннер"
                                                                        >
                                                                        	<i class="fa fa-trash"></i>
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
									<td>&nbsp;</td>

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

										<a
                                        	href="<?= htmlspecialchars(
                                        		$parent_url
                                        			. '/products/'
                                        			. (int) $item->id,
                                        		ENT_QUOTES,
                                        		'UTF-8'
                                        	) ?>"
                                        	class="btn purple"
                                        >
                                        	<i class="fa fa-shopping-bag"></i>
                                        	Управление товарами
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