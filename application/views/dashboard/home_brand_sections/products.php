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
		<div class="portlet-title">
			<div class="caption">
				<i class="fa fa-search"></i>
				Поиск товара
			</div>
		</div>

		<div class="portlet-body">
			<form
            	method="get"
            	action="<?= htmlspecialchars(
            		$products_page_path,
            		ENT_QUOTES,
            		'UTF-8'
            	) ?>"
            >
				<div class="input-group">
					<input
						type="text"
						name="query"
						class="form-control"
						value="<?= htmlspecialchars(
							(string) $query,
							ENT_QUOTES,
							'UTF-8'
						) ?>"
						placeholder="
							ID, SKU или название товара
						"
					>

					<span class="input-group-btn">
						<button
							type="submit"
							class="btn blue"
						>
							<i class="fa fa-search"></i>

							Найти
						</button>
					</span>
				</div>
			</form>
		</div>
	</div>
</div>

<?php if ($query !== '') : ?>
	<div class="row">
		<div class="portlet light">
			<div class="portlet-title">
				<div class="caption">
					Результаты поиска
				</div>
			</div>

			<div class="portlet-body">
				<?php if (!empty($search_products)) : ?>
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
										ID
									</th>

									<th width="110">
										Изображение
									</th>

									<th>
										Товар
									</th>

									<th width="140">
										SKU
									</th>

									<th width="160">
										Цена
									</th>

									<th width="150">
										Действие
									</th>
								</tr>
							</thead>

							<tbody>
								<?php foreach ($search_products as $product) : ?>
									<?php
									$productId = (int) $product->id;

									$isSelected = in_array(
										$productId,
										$selected_product_ids,
										true
									);
									?>

									<tr>
										<td class="align-middle">
											<?= $productId ?>
										</td>

										<td class="align-middle">
											<?php if (!empty($product->img)) : ?>
												<?php
												$productPreview = newthumbs(
													$product->img,
													'products',
													80,
													80,
													'80x80x1',
													1
												);
												?>

												<img
													src="<?= htmlspecialchars(
														$productPreview,
														ENT_QUOTES,
														'UTF-8'
													) ?>"
													alt=""
													width="70"
													height="70"
													style="
														object-fit: contain;
														display: block;
													"
												>
											<?php endif; ?>
										</td>

										<td class="align-middle">
											<strong>
												<?= htmlspecialchars(
													(string) (
														$product->titleRU
														?? ''
													),
													ENT_QUOTES,
													'UTF-8'
												) ?>
											</strong>

											<?php if (!empty($product->brand_title)) : ?>
												<br>

												<small class="text-muted">
													<?= htmlspecialchars(
														(string) $product->brand_title,
														ENT_QUOTES,
														'UTF-8'
													) ?>
												</small>
											<?php endif; ?>
										</td>

										<td class="align-middle">
											<?= htmlspecialchars(
												(string) (
													$product->SKU
													?? ''
												),
												ENT_QUOTES,
												'UTF-8'
											) ?>
										</td>

										<td class="align-middle">
											<?= number_format(
												(float) (
													$product->price
													?? 0
												),
												2,
												'.',
												' '
											) ?>
										</td>

										<td class="align-middle">
											<?php if ($isSelected) : ?>
												<span class="label label-success">
													Уже добавлен
												</span>
											<?php else : ?>
												<form
                                                	action="<?= htmlspecialchars(
                                                		$add_product_path,
                                                		ENT_QUOTES,
                                                		'UTF-8'
                                                	) ?>"
                                                	method="post"
                                                >
                                                	<input
                                                		type="hidden"
                                                		name="product_id"
                                                		value="<?= $productId ?>"
                                                	>
                                                
                                                	<button
                                                		type="submit"
                                                		class="btn green"
                                                	>
                                                		<i class="fa fa-plus"></i>
                                                		Добавить
                                                	</button>
                                                </form>
											<?php endif; ?>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php else : ?>
					<div class="alert alert-warning">
						Товары не найдены.
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
<?php endif; ?>

<div class="row">
	<div class="portlet light">
		<div class="portlet-title">
			<div class="caption">
				<i class="fa fa-shopping-bag"></i>

				Выбранные товары
			</div>
		</div>

		<div class="portlet-body">
			<?php if (!empty($selected_items)) : ?>
				<form
                	action="<?= htmlspecialchars(
                		$update_products_order_path,
                		ENT_QUOTES,
                		'UTF-8'
                	) ?>"
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
										Порядок
									</th>

									<th width="100">
										Фото
									</th>

									<th>
										Товар
									</th>

									<th width="140">
										SKU
									</th>

									<th width="150">
										Цена
									</th>

									<th width="170">
										Статус товара
									</th>

									<th width="150">
										Действие
									</th>
								</tr>
							</thead>

							<tbody>
								<?php foreach ($selected_items as $item) : ?>
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
												$itemPreview = newthumbs(
													$item->img,
													'products',
													80,
													80,
													'80x80x1',
													1
												);
												?>

												<img
													src="<?= htmlspecialchars(
														$itemPreview,
														ENT_QUOTES,
														'UTF-8'
													) ?>"
													alt=""
													width="70"
													height="70"
													style="
														object-fit: contain;
														display: block;
													"
												>
											<?php endif; ?>
										</td>

										<td class="align-middle">
											<strong>
												<?= htmlspecialchars(
													(string) $item->titleRU,
													ENT_QUOTES,
													'UTF-8'
												) ?>
											</strong>

											<?php if (!empty($item->brand_title)) : ?>
												<br>

												<small class="text-muted">
													<?= htmlspecialchars(
														(string) $item->brand_title,
														ENT_QUOTES,
														'UTF-8'
													) ?>
												</small>
											<?php endif; ?>
										</td>

										<td class="align-middle">
											<?= htmlspecialchars(
												(string) $item->SKU,
												ENT_QUOTES,
												'UTF-8'
											) ?>
										</td>

										<td class="align-middle">
											<?= number_format(
												(float) $item->price,
												2,
												'.',
												' '
											) ?>
										</td>

										<td class="align-middle">
											<?php if (!empty($item->product_isShown)) : ?>
												<span class="label label-success">
													Активен
												</span>
											<?php else : ?>
												<span class="label label-danger">
													Скрыт в каталоге
												</span>
											<?php endif; ?>
										</td>

										<td class="align-middle">
											<a
                                            	href="<?= htmlspecialchars(
                                            		$delete_product_path
                                            			. (int) $item->id,
                                            		ENT_QUOTES,
                                            		'UTF-8'
                                            	) ?>"
                                            	class="
                                            		btn
                                            		red
                                            		mine_delete_row
                                            	"
                                            >
                                            	<i class="fa fa-trash"></i>
                                            	Удалить
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

						Обновить порядок
					</button>
				</form>
			<?php else : ?>
				<div class="alert alert-info">
					В эту брендовую секцию пока не добавлены товары.
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>