<main class="page contact-page">
    <section class="breacrums">
        <div class="breacrums__container _container">
            <ul class="breacrums__list">
                <li class="breacrums__item">
                    <a
                        href="/<?= $lclang ?>"
                        class="breadcrums__link"
                    >
                        <?= $home_bc_title ?>
                    </a>
                </li>

                <li class="breacrums__item">
                    <p class="breacrums__name">
                        <?= $page_title ?>
                    </p>
                </li>
            </ul>
        </div>
    </section>

    <section class="contacts">
        <div class="contacts__container _container">
            <header class="contacts__header">
                <p class="contacts__eyebrow">
                    Vizaje-Nica
                </p>

                <h1 class="contacts__title">
                    <?= $page_name ?>
                </h1>

                <p class="contacts__intro">
                    <?= $lclang === 'ro'
                        ? 'Suntem aici pentru întrebări despre produse, comenzi, livrare și magazine.'
                        : 'Мы готовы помочь с вопросами о товарах, заказах, доставке и магазинах.' ?>
                </p>
            </header>

            <div class="contacts__layout">
                <div class="contacts__main">
                    <section class="contacts__information">
                        <div class="contacts__section-head">
                            <p class="contacts__section-eyebrow">
                                <?= $lclang === 'ro'
                                    ? 'Informații de contact'
                                    : 'Контактная информация' ?>
                            </p>

                            <h2 class="contacts__section-title">
                                <?= $lclang === 'ro'
                                    ? 'Cum ne puteți contacta'
                                    : 'Как с нами связаться' ?>
                            </h2>
                        </div>

                        <div class="contacts__details">
                            <?php if (!empty(CONT_ADDRESS1)) { ?>
                                <article class="contact-detail">
                                    <div class="contact-detail__icon">
                                        <svg
                                            width="22"
                                            height="22"
                                            viewBox="0 0 22 22"
                                            fill="none"
                                            aria-hidden="true"
                                        >
                                            <path
                                                d="M11 20C11 20 18 13.6 18 7.8C18 3.98 14.87 1 11 1C7.13 1 4 3.98 4 7.8C4 13.6 11 20 11 20Z"
                                                stroke="currentColor"
                                                stroke-width="1.4"
                                            />

                                            <circle
                                                cx="11"
                                                cy="7.8"
                                                r="2.4"
                                                stroke="currentColor"
                                                stroke-width="1.4"
                                            />
                                        </svg>
                                    </div>

                                    <div class="contact-detail__content">
                                        <span class="contact-detail__label">
                                            <?= CONT_ADDRESS ?>
                                        </span>

                                        <p class="contact-detail__value">
                                            <?= CONT_ADDRESS1 ?>
                                        </p>
                                    </div>
                                </article>
                            <?php } ?>

                            <?php if (!empty(CONT_WORKING1)) { ?>
                                <article class="contact-detail">
                                    <div class="contact-detail__icon">
                                        <svg
                                            width="22"
                                            height="22"
                                            viewBox="0 0 22 22"
                                            fill="none"
                                            aria-hidden="true"
                                        >
                                            <circle
                                                cx="11"
                                                cy="11"
                                                r="9"
                                                stroke="currentColor"
                                                stroke-width="1.4"
                                            />

                                            <path
                                                d="M11 6V11.3L14.4 13.3"
                                                stroke="currentColor"
                                                stroke-width="1.4"
                                                stroke-linecap="round"
                                            />
                                        </svg>
                                    </div>

                                    <div class="contact-detail__content">
                                        <span class="contact-detail__label">
                                            <?= CONT_WORKING ?>
                                        </span>

                                        <p class="contact-detail__value">
                                            <?= CONT_WORKING1 ?>
                                        </p>
                                    </div>
                                </article>
                            <?php } ?>

                            <?php if (!empty(CONT_PHONE1)) { ?>
                                <?php
                                    $contactPhone = preg_replace(
                                        '/[^\d+]/',
                                        '',
                                        CONT_PHONE1
                                    );
                                ?>

                                <article class="contact-detail">
                                    <div class="contact-detail__icon">
                                        <svg
                                            width="22"
                                            height="22"
                                            viewBox="0 0 22 22"
                                            fill="none"
                                            aria-hidden="true"
                                        >
                                            <path
                                                d="M6.2 2.5L8.4 6.9L6.8 8.6C7.8 10.8 9.5 12.5 11.7 13.5L13.4 11.9L17.8 14.1V17.2C17.8 18.2 17 19 16 19C8.8 19 3 13.2 3 6C3 5 3.8 4.2 4.8 4.2H6.2V2.5Z"
                                                stroke="currentColor"
                                                stroke-width="1.4"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                    </div>

                                    <div class="contact-detail__content">
                                        <span class="contact-detail__label">
                                            <?= CONT_PHONE ?>
                                        </span>

                                        <a
                                            href="tel:<?= htmlspecialchars(
                                                $contactPhone,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                            class="contact-detail__value contact-detail__value--link"
                                        >
                                            <?= CONT_PHONE1 ?>
                                        </a>
                                    </div>
                                </article>
                            <?php } ?>

                            <?php if (!empty(CONT_EMAIL1)) { ?>
                                <article class="contact-detail">
                                    <div class="contact-detail__icon">
                                        <svg
                                            width="22"
                                            height="22"
                                            viewBox="0 0 22 22"
                                            fill="none"
                                            aria-hidden="true"
                                        >
                                            <rect
                                                x="2.5"
                                                y="4.5"
                                                width="17"
                                                height="13"
                                                rx="1.5"
                                                stroke="currentColor"
                                                stroke-width="1.4"
                                            />

                                            <path
                                                d="M3.5 6L11 12L18.5 6"
                                                stroke="currentColor"
                                                stroke-width="1.4"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                    </div>

                                    <div class="contact-detail__content">
                                        <span class="contact-detail__label">
                                            <?= CONT_EMAIL ?>
                                        </span>

                                        <a
                                            href="mailto:<?= htmlspecialchars(
                                                CONT_EMAIL1,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                            class="contact-detail__value contact-detail__value--link"
                                        >
                                            <?= CONT_EMAIL1 ?>
                                        </a>
                                    </div>
                                </article>
                            <?php } ?>
                        </div>
                    </section>

                    <section class="contacts__form-section">
                        <div class="contacts__section-head">
                            <p class="contacts__section-eyebrow">
                                <?= $lclang === 'ro'
                                    ? 'Mesaj'
                                    : 'Обращение' ?>
                            </p>

                            <h2 class="contacts__section-title">
                                <?= $lclang === 'ro'
                                    ? 'Scrieți-ne'
                                    : 'Напишите нам' ?>
                            </h2>

                            <p class="contacts__section-text">
                                <?= $lclang === 'ro'
                                    ? 'Completați formularul și vă vom răspunde cât mai curând posibil.'
                                    : 'Заполните форму, и мы ответим вам в ближайшее время.' ?>
                            </p>
                        </div>

                        <?php if (!empty($send_seccess)) { ?>
                            <div
                                class="contacts__message contacts__message--success"
                                role="status"
                            >
                                <svg
                                    width="20"
                                    height="20"
                                    viewBox="0 0 20 20"
                                    fill="none"
                                    aria-hidden="true"
                                >
                                    <circle
                                        cx="10"
                                        cy="10"
                                        r="8.5"
                                        stroke="currentColor"
                                        stroke-width="1.4"
                                    />

                                    <path
                                        d="M6.2 10.2L8.7 12.7L13.9 7.5"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>

                                <span>
                                    <?= $send_seccess ?>
                                </span>
                            </div>
                        <?php } ?>

                        <form
                            action=""
                            method="post"
                            class="contact-form"
                        >
                            <div class="contact-form__grid">
                                <div class="contact-form__field">
                                    <label
                                        for="contact-name"
                                        class="contact-form__label"
                                    >
                                        <?= NAME ?>
                                        <span aria-hidden="true">*</span>
                                    </label>

                                    <input
                                        id="contact-name"
                                        type="text"
                                        name="name"
                                        value="<?= !empty($client_info->name)
                                            ? htmlspecialchars(
                                                $client_info->name,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            )
                                            : '' ?>"
                                        class="contact-form__input"
                                        autocomplete="name"
                                        required
                                    >
                                </div>

                                <div class="contact-form__field">
                                    <label
                                        for="contact-phone"
                                        class="contact-form__label"
                                    >
                                        <?= PHONE ?>
                                        <span aria-hidden="true">*</span>
                                    </label>

                                    <input
                                        id="contact-phone"
                                        type="tel"
                                        name="phone"
                                        value="<?= !empty($client_info->phone)
                                            ? htmlspecialchars(
                                                $client_info->phone,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            )
                                            : '' ?>"
                                        class="contact-form__input _phone"
                                        autocomplete="tel"
                                        inputmode="tel"
                                        required
                                    >
                                </div>

                                <div class="contact-form__field contact-form__field--full">
                                    <label
                                        for="contact-email"
                                        class="contact-form__label"
                                    >
                                        <?= EMAIL_LOGIN ?>
                                        <span aria-hidden="true">*</span>
                                    </label>

                                    <input
                                        id="contact-email"
                                        type="email"
                                        name="email"
                                        value="<?= !empty($client_info->email)
                                            ? htmlspecialchars(
                                                $client_info->email,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            )
                                            : '' ?>"
                                        class="contact-form__input"
                                        autocomplete="email"
                                        inputmode="email"
                                        required
                                    >
                                </div>

                                <div class="contact-form__field contact-form__field--full">
                                    <label
                                        for="contact-comments"
                                        class="contact-form__label"
                                    >
                                        <?= COMMENTS ?>
                                    </label>

                                    <textarea
                                        id="contact-comments"
                                        name="comments"
                                        class="contact-form__textarea"
                                        rows="6"
                                    ></textarea>
                                </div>
                            </div>

                            <button
                                type="submit"
                                class="contact-form__submit"
                            >
                                <span><?= SEND ?></span>

                                <svg
                                    width="18"
                                    height="18"
                                    viewBox="0 0 18 18"
                                    fill="none"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M3 9H15M11 5L15 9L11 13"
                                        stroke="currentColor"
                                        stroke-width="1.4"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </button>
                        </form>
                    </section>
                </div>

                <aside class="contacts__aside">
                    <div class="contacts__map">
                        <?= CONT_MAP ?>
                    </div>

                    <div class="contacts__aside-note">
                        <p class="contacts__aside-label">
                            <?= $lclang === 'ro'
                                ? 'Magazine Vizaje-Nica'
                                : 'Магазины Vizaje-Nica' ?>
                        </p>

                        <h2 class="contacts__aside-title">
                            <?= $lclang === 'ro'
                                ? 'Găsiți cel mai apropiat magazin'
                                : 'Найдите ближайший магазин' ?>
                        </h2>

                        <a
                            href="/<?= $lclang ?>/<?= $lclang === 'ro'
                                ? 'magazinele'
                                : 'magazinyi' ?>"
                            class="contacts__stores-link"
                        >
                            <span>
                                <?= $lclang === 'ro'
                                    ? 'Vezi toate magazinele'
                                    : 'Посмотреть все магазины' ?>
                            </span>

                            <svg
                                width="18"
                                height="18"
                                viewBox="0 0 18 18"
                                fill="none"
                                aria-hidden="true"
                            >
                                <path
                                    d="M3 9H15M11 5L15 9L11 13"
                                    stroke="currentColor"
                                    stroke-width="1.4"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </a>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</main>