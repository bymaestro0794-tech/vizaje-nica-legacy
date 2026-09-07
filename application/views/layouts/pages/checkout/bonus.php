<?php
defined('BASEPATH') or exit('No direct script access allowed');

$isRomanian =
	isset($lclang)
	&& $lclang === 'ro';

$bonusEarn =
	isset($bonusTotal)
		? (float) $bonusTotal
		: 0;

$bonusBalanceValue =
	isset($bonusBalance)
		? (float) $bonusBalance
		: 0;

$bonusMax =
	isset($bonusMaxWriteOff)
		? (float) $bonusMaxWriteOff
		: 0;

$canEarn =
	$bonusEarn > 0;

$canWriteOff =
	$bonusBalanceValue > 0
	&& $bonusMax > 0;
?>

<section
	class="vn-checkout-bonus"
	data-checkout-bonus
	data-bonus-balance="<?= htmlspecialchars(
		number_format(
			$bonusBalanceValue,
			2,
			'.',
			''
		),
		ENT_QUOTES,
		'UTF-8'
	) ?>"
	data-bonus-max="<?= htmlspecialchars(
		number_format(
			$bonusMax,
			2,
			'.',
			''
		),
		ENT_QUOTES,
		'UTF-8'
	) ?>"
	data-bonus-earn="<?= htmlspecialchars(
		number_format(
			$bonusEarn,
			2,
			'.',
			''
		),
		ENT_QUOTES,
		'UTF-8'
	) ?>"
>
	<div class="vn-checkout-bonus__header">
		<div>
			<p class="vn-checkout-bonus__eyebrow">
				<?= $isRomanian
					? 'Program de fidelitate'
					: 'Бонусная программа' ?>
			</p>

			<h3 class="vn-checkout-bonus__title">
				<?= $isRomanian
					? 'Bonusuri pentru cumpărături'
					: 'Бонусы за покупку' ?>
			</h3>
		</div>

		<div class="vn-checkout-bonus__balance">
			<span>
				<?= $isRomanian
					? 'Sold'
					: 'Баланс' ?>
			</span>

			<strong>
				<?= number_format(
					$bonusBalanceValue,
					2,
					'.',
					''
				) ?>
			</strong>
		</div>
	</div>

	<?php if (!$canEarn && !$canWriteOff) : ?>

		<div class="vn-checkout-bonus__unavailable">
			<p class="vn-checkout-bonus__unavailable-title">
				<?= $isRomanian
					? 'Bonusurile nu sunt disponibile pentru această comandă'
					: 'Бонусы недоступны для этого заказа' ?>
			</p>

			<p class="vn-checkout-bonus__unavailable-text">
				<?= $isRomanian
					? 'Bonusurile nu se acumulează și nu pot fi utilizate pentru produsele cu reducere sau promoție.'
					: 'Бонусы не начисляются и не списываются на товары со скидкой или применённым промокодом.' ?>
			</p>
		</div>

	<?php else : ?>

		<div class="vn-checkout-bonus__options">

			<?php if ($canEarn) : ?>

				<label class="vn-checkout-bonus-option">
					<input
						type="radio"
						name="bonus"
						value="1"
						class="vn-checkout-bonus-option__input"
						data-bonus-mode="earn"
						checked
					>

					<span class="vn-checkout-bonus-option__content">

						<span class="vn-checkout-bonus-option__icon">
							<img
								src="/app/img/checkout/bonus.png"
								alt=""
								decoding="async"
							>
						</span>

						<span class="vn-checkout-bonus-option__body">
							<strong>
								<?= $isRomanian
									? 'Acumulează'
									: 'Накопить' ?>
							</strong>

							<span>
								<?= $isRomanian
									? 'Veți primi'
									: 'За покупку будет начислено' ?>

								<b>
									+<?= number_format(
										$bonusEarn,
										2,
										'.',
										''
									) ?>
								</b>
							</span>
						</span>

						<span
							class="vn-checkout-bonus-option__check"
							aria-hidden="true"
						>
							<svg
								width="14"
								height="14"
								viewBox="0 0 14 14"
								fill="none"
							>
								<path
									d="M3 7.2L5.55 9.75L11 4.3"
									stroke="currentColor"
									stroke-width="1.5"
									stroke-linecap="round"
									stroke-linejoin="round"
								/>
							</svg>
						</span>

					</span>
				</label>

			<?php endif; ?>

			<?php if ($canWriteOff) : ?>

				<label class="vn-checkout-bonus-option">
					<input
						type="radio"
						name="bonus"
						value="2"
						class="vn-checkout-bonus-option__input"
						data-bonus-mode="writeoff"
						<?= !$canEarn ? 'checked' : '' ?>
					>

					<span class="vn-checkout-bonus-option__content">

						<span class="vn-checkout-bonus-option__icon">
							<img
								src="/app/img/checkout/bonus.png"
								alt=""
								decoding="async"
							>
						</span>

						<span class="vn-checkout-bonus-option__body">
							<strong>
								<?= $isRomanian
									? 'Folosește bonusuri'
									: 'Списать бонусы' ?>
							</strong>

							<span>
								<?= $isRomanian
									? 'Puteți utiliza până la'
									: 'Можно списать до' ?>

								<b>
									<?= number_format(
										$bonusMax,
										0,
										'.',
										' '
									) ?>
								</b>
							</span>
						</span>

						<span
							class="vn-checkout-bonus-option__check"
							aria-hidden="true"
						>
							<svg
								width="14"
								height="14"
								viewBox="0 0 14 14"
								fill="none"
							>
								<path
									d="M3 7.2L5.55 9.75L11 4.3"
									stroke="currentColor"
									stroke-width="1.5"
									stroke-linecap="round"
									stroke-linejoin="round"
								/>
							</svg>
						</span>

					</span>
				</label>

			<?php endif; ?>

		</div>

		<?php if ($canWriteOff) : ?>

			<div
				class="vn-checkout-bonus__writeoff"
				data-bonus-writeoff
				hidden
			>
				<div class="vn-checkout-bonus__writeoff-head">

					<div>
						<span>
							<?= $isRomanian
								? 'Disponibil'
								: 'Доступно' ?>
						</span>

						<strong>
							<?= number_format(
								$bonusBalanceValue,
								2,
								'.',
								''
							) ?>
						</strong>
					</div>

					<div>
						<span>
							<?= $isRomanian
								? 'Maximum'
								: 'Можно списать' ?>
						</span>

						<strong>
							<?= number_format(
								$bonusMax,
								2,
								'.',
								''
							) ?>
						</strong>
					</div>

				</div>

				<div class="vn-checkout-bonus__amount">
					<input
						type="number"
						name="bonus_amount"
						value="0"
						min="0"
						max="<?= (int) floor($bonusMax) ?>"
						step="1"
						inputmode="numeric"
						data-bonus-amount
					>

					<span>
						MDL
					</span>
				</div>

				<input
					type="range"
					min="0"
					max="<?= (int) floor($bonusMax) ?>"
					value="0"
					step="1"
					class="vn-checkout-bonus__range"
					data-bonus-range
				>

				<button
					type="button"
					class="vn-checkout-bonus__max"
					data-bonus-max-button
				>
					<?= $isRomanian
						? 'Folosește maximum'
						: 'Использовать максимум' ?>
				</button>
			</div>

		<?php endif; ?>

	<?php endif; ?>

</section>