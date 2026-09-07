<main class="page">

	<section class="account-reset">
		<div class="account-reset__container _container">

			<div class="account-reset__content">

				<div class="account-reset__header">
					<h1 class="account-reset__title">
						<?= $page_name ?>
					</h1>

					<p class="account-reset__description">
						<?= $lclang === 'ro'
							? 'Creați o parolă nouă pentru contul dumneavoastră.'
							: 'Создайте новый пароль для вашего аккаунта.' ?>
					</p>
				</div>

				<form
					action=""
					method="post"
					class="account-reset__form"
				>

					<?php if (!empty($error)) : ?>
						<div
							class="account-reset__message account-reset__message--error"
							role="alert"
						>
							<?= $error ?>
						</div>
					<?php endif; ?>

					<div class="account-reset__field">
						<label
							for="resetPassword"
							class="account-reset__label"
						>
							<?= PASSWORD_NEW ?>
						</label>

						<div class="account-reset__input-wrapper">
							<input
								id="resetPassword"
								type="password"
								name="password"
								class="account-reset__input"
								autocomplete="new-password"
								required
							>

							<button
								type="button"
								class="account-reset__password-toggle"
								data-reset-password-toggle
								aria-controls="resetPassword"
								aria-label="<?= $lclang === 'ro'
									? 'Afișați parola'
									: 'Показать пароль' ?>"
							>
								<svg
									width="20"
									height="20"
									viewBox="0 0 24 24"
									fill="none"
									aria-hidden="true"
								>
									<path
										d="M2.5 12C4.5 8.5 7.65 6.5 12 6.5C16.35 6.5 19.5 8.5 21.5 12C19.5 15.5 16.35 17.5 12 17.5C7.65 17.5 4.5 15.5 2.5 12Z"
										stroke="currentColor"
										stroke-width="1.5"
									/>

									<circle
										cx="12"
										cy="12"
										r="2.5"
										stroke="currentColor"
										stroke-width="1.5"
									/>
								</svg>
							</button>
						</div>
					</div>

					<div class="account-reset__field">
						<label
							for="resetPasswordCheck"
							class="account-reset__label"
						>
							<?= $lclang === 'ro'
								? 'Repetați parola'
								: 'Повторите пароль' ?>
						</label>

						<div class="account-reset__input-wrapper">
							<input
								id="resetPasswordCheck"
								type="password"
								name="password_check"
								class="account-reset__input"
								autocomplete="new-password"
								required
							>

							<button
								type="button"
								class="account-reset__password-toggle"
								data-reset-password-toggle
								aria-controls="resetPasswordCheck"
								aria-label="<?= $lclang === 'ro'
									? 'Afișați parola'
									: 'Показать пароль' ?>"
							>
								<svg
									width="20"
									height="20"
									viewBox="0 0 24 24"
									fill="none"
									aria-hidden="true"
								>
									<path
										d="M2.5 12C4.5 8.5 7.65 6.5 12 6.5C16.35 6.5 19.5 8.5 21.5 12C19.5 15.5 16.35 17.5 12 17.5C7.65 17.5 4.5 15.5 2.5 12Z"
										stroke="currentColor"
										stroke-width="1.5"
									/>

									<circle
										cx="12"
										cy="12"
										r="2.5"
										stroke="currentColor"
										stroke-width="1.5"
									/>
								</svg>
							</button>
						</div>
					</div>

					<button
						type="submit"
						class="account-reset__submit"
					>
						<?= CHANGE ?>
					</button>

				</form>

			</div>

		</div>
	</section>

</main>