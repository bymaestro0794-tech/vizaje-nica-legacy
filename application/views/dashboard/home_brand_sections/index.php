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
	<div class="portlet bordered">
		<div class="accordion" id="brand-sections-accordion">
			<div class="panel panel-default">
				<div
					class="panel-heading"
					style="background-color: #ffffff;"
				>
					<h4 class="panel-title">
						<a
							class="accordion-toggle"
							data-toggle="collapse"
							data-parent="#brand-sections-accordion"
							href="#brand-section-add"
						>
							<i class="fa fa-plus"></i>

							<?= htmlspecialchars(
								$add,
								ENT_QUOTES,
								'UTF-8'
							) ?>
						</a>
					</h4>
				</div>

				<div
					id="brand-section-add"
					class="panel-collapse collapse"
				>
					<form
						action="<?= $a_path ?>"
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
													placeholder="/ru/brands/garnier"
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
													placeholder="/ro/brands/garnier"
													required
												>
											</td>
										</tr>

										<tr>
											<td>
												Позиция на главной *
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
												Desktop-баннер *
											</td>

											<td>
												<input
													type="file"
													name="img"
													class="form-control"
													accept="image/jpeg,image/png,image/webp"
													required
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
														1600 × 620 px.
													</p>
												</div>
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

												<div
													class="note note-warning"
													style="
														margin-top: 10px;
														margin-bottom: 0;
													"
												>
													<p>
														Рекомендуемый размер:
														720 × 900 px.
													</p>
												</div>
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
														checked
													>

													Да

													<span></span>
												</label>
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

													<?= lang('Add') ?>
												</button>
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
	</div>
</div>

<?php if (!empty($objects)) : ?>
	<div class="row">
		<div class="portlet light">
			<div class="portlet-body">
				<form
					action="<?= $o_path ?>"
					method="post"
				>
					<div class="table-scrollable">
						<table
							class="
								table
								table-bordered
								table-striped
								table-hover
							"
						>
							<thead>
								<tr>
									<th width="90">
										<?= lang('Sorting') ?>
									</th>

									<th width="130">
										Баннер
									</th>

									<th>
										Бренд
									</th>

									<th width="170">
										Позиция
									</th>

									<th width="110">
										Товаров
									</th>

									<th width="180">
										Видимость
									</th>

									<th width="360">
										<?= lang('Action') ?>
									</th>
								</tr>
							</thead>

							<tbody>
								<?php foreach ($objects as $item) : ?>
									<tr>
										<td class="align-middle">
											<input
												type="text"
												name="so[<?= (int) $item->id ?>]"
												value="<?= (int) $item->sorder ?>"
												class="
													form-control
													text-center
													sorder
												"
												onkeyup="
													this.value =
														this.value.replace(
															/[^\d]/g,
															''
														);
												"
											>
										</td>

										<td class="align-middle">
											<?php if (!empty($item->img)) : ?>
												<?php
												$preview = newthumbs(
													$item->img,
													'home_brand_sections',
													120,
													70,
													'120x70x1',
													1
												);
												?>

												<img
													src="<?= htmlspecialchars(
														$preview,
														ENT_QUOTES,
														'UTF-8'
													) ?>"
													alt=""
													width="120"
													height="70"
													style="
														object-fit: cover;
														display: block;
													"
												>
											<?php endif; ?>
										</td>

										<td class="align-middle">
											<a
												href="<?= $e_path
													. (int) $item->id
													. '/' ?>"
												style="font-weight: 700;"
											>
												<?= htmlspecialchars(
													(string) $item->titleRU,
													ENT_QUOTES,
													'UTF-8'
												) ?>
											</a>

											<br>

											<small class="text-muted">
												<?= htmlspecialchars(
													(string) $item->titleRO,
													ENT_QUOTES,
													'UTF-8'
												) ?>
											</small>
										</td>

										<td class="align-middle">
											<?= htmlspecialchars(
												$placements[$item->placement]
													?? $item->placement,
												ENT_QUOTES,
												'UTF-8'
											) ?>
										</td>

										<td class="align-middle">
											<span class="label label-info">
												<?= (int) $item->products_count ?>
											</span>
										</td>

										<td class="align-middle">
											<?php
											$checked = !empty($item->isShown)
												? 'checked'
												: '';
											?>

											<label
												class="
													mt-checkbox
													mt-checkbox-outline
												"
											>
												<input
													type="checkbox"
													value="<?= (int) $item->id ?>"
													data-col="isShown"
													data-table="<?= $table ?>"
													class="mine_change_check"
													<?= $checked ?>
												>

												<?= lang('Show on site') ?>

												<span></span>
											</label>
										</td>

										<td class="align-middle">
										<a
                                            	href="<?= $products_path
                                            		. (int) $item->id ?>"
                                            	class="btn purple"
                                            >
                                            	<i class="fa fa-shopping-bag"></i>
                                            	Товары
                                            </a>

											<a
												href="<?= $e_path
													. (int) $item->id
													. '/' ?>"
												class="btn blue"
											>
												<i class="fa fa-pencil"></i>

												<?= lang('Edit') ?>
											</a>

											<a
												href="<?= $del_path
													. (int) $item->id
													. '/' ?>"
												class="
													btn
													red
													mine_delete_row
												"
											>
												<i class="fa fa-trash"></i>

												<?= lang('Delete') ?>
											</a>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>

					<button
						type="submit"
						class="btn green"
					>
						<i class="fa fa-check"></i>

						<?= lang('Refresh order') ?>
					</button>
				</form>
			</div>
		</div>
	</div>
<?php else : ?>
	<div class="alert alert-info">
		Брендовые секции ещё не добавлены.
	</div>
<?php endif; ?>