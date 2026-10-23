<?php
defined('BASEPATH') or exit('No direct script access allowed');

$registrationPrivacyUrl = '/' . $lclang . '/' . $menu['all'][23]->uri;
?>

<main class="page">
    <section class="account-registration">
        <div class="account-registration__container _container">
            <div class="account-registration__content">
                <h1 class="account-registration__title">
                    <?= htmlspecialchars($page_name, ENT_QUOTES, 'UTF-8') ?>
                </h1>

                <p class="account-registration__intro">
                    <?= $lclang === 'ro'
                        ? 'Creați un cont pentru a urmări comenzile și a gestiona preferințele.'
                        : 'Создайте аккаунт, чтобы отслеживать заказы и управлять настройками.' ?>
                </p>

                <?php if (!empty($error)) : ?>
                    <div class="account-registration__message" role="alert">
                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form action="" method="post" class="account-registration__form">
                    <div class="account-registration__grid">
                        <label class="account-registration__field">
                            <span><?= PHONE_NUMBER ?></span>
                            <input type="text" name="number" class="_phone _mask" inputmode="tel" autocomplete="tel" required>
                        </label>

                        <label class="account-registration__field">
                            <span><?= EMAIL_LOGIN ?></span>
                            <input type="email" name="email" autocomplete="email" required>
                        </label>

                        <label class="account-registration__field">
                            <span><?= NAME ?></span>
                            <input type="text" name="name" autocomplete="given-name">
                        </label>

                        <label class="account-registration__field">
                            <span><?= SURNAME ?></span>
                            <input type="text" name="surname" autocomplete="family-name">
                        </label>

                        <label class="account-registration__field">
                            <span><?= PASSWORD ?></span>
                            <input type="password" name="password" autocomplete="new-password" required>
                        </label>

                        <label class="account-registration__field">
                            <span><?= PASSWORD_CHECK ?></span>
                            <input type="password" name="password_check" autocomplete="new-password" required>
                        </label>
                    </div>

                    <div class="account-registration__checks">
                        <label class="account-registration__check">
                            <input type="checkbox" name="register_check" value="1" required>
                            <span>
                                <?= AGREE_WITH ?>
                                <a href="<?= htmlspecialchars($registrationPrivacyUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">
                                    <?= htmlspecialchars($menu['all'][23]->title, ENT_QUOTES, 'UTF-8') ?>
                                </a>.
                            </span>
                        </label>

                        <label class="account-registration__check">
                            <input type="checkbox" name="register_newsletters" value="1">
                            <span><?= RECEIVE_PROMO_REG ?></span>
                        </label>
                    </div>

                    <button type="submit" class="account-registration__submit">
                        <?= REGISTER ?>
                    </button>
                </form>
            </div>
        </div>
    </section>
</main>
