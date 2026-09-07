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
			<span><?= htmlspecialchars(
				$title,
				ENT_QUOTES,
				'UTF-8'
			) ?></span>
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

<!-- Add category -->
<div class="row">
	<div class="portlet bordered">
		<div class="accordion" id="home-categories-accordion">
			<div class="panel panel-default">
				<div
					class="panel-heading"
					style="background-color: #ffffff;"
				>
					<h4 class="panel-title">
						<a
							class="accordion-toggle"
							data-toggle="collapse"
							data-parent="#home-categories-accordion"
							href="#home-category-add"
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
					id="home-category-add"
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
											<td width="220">
												Название RU *
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
											<td width="220">
												Название RO *
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
											<td width="220">
												Ссылка RU *
											</td>

											<td>
												<input
													type="text"
													name="urlRU"
													class="form-control"
													placeholder="/ru/catalog/..."
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
													placeholder="/ro/catalog/..."
													required
												>
											</td>
										</tr>

										<tr>
											<td width="220">
												Изображение *
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
														400 × 400 px.
														Формат: JPG, PNG или WebP.
													</p>
												</div>
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
														checked
													>

													Да

													<span></span>
												</label>
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

<!-- Categories list -->
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

									<th width="110">
										Изображение
									</th>

									<th>
										Название RU
									</th>

									<th>
										Название RO
									</th>

									<th width="190">
										Видимость
									</th>

									<th width="250">
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
												min="0"
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
												$previewImage = newthumbs(
													$item->img,
													'home_categories',
													80,
													80,
													'80x80x1',
													1
												);
												?>

												<img
													src="<?= htmlspecialchars(
														$previewImage,
														ENT_QUOTES,
														'UTF-8'
													) ?>"
													alt=""
													width="70"
													height="70"
													style="
														display: block;
														object-fit: cover;
														border-radius: 50%;
													"
												>
											<?php else : ?>
												<span class="text-muted">
													Нет изображения
												</span>
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
										</td>

										<td class="align-middle">
											<?= htmlspecialchars(
												(string) $item->titleRO,
												ENT_QUOTES,
												'UTF-8'
											) ?>
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
		Категории для главной страницы пока не добавлены.
	</div>
<?php endif; ?>