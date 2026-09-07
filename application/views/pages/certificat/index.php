
<main class="page">
    <?php
        $certificateBuilderCopy = $lclang === 'ru'
            ? array(
                'steps' => array(
                    'Дизайн',
                    'Номинал',
                    'Кому',
                    'Когда',
                    'Оплата',
                ),
                'back' => 'Назад',
                'design_title' => 'Выберите дизайн карты',
                'next' => 'Далее',
                'note' => 'Электронный подарочный сертификат. Для получения и использования сертификата необходимо мобильное приложение Vizaje-Nica.',
                'denomination_title' => 'Выберите номинал карты',
                'denomination_custom' => 'Своя сумма',
                'denomination_custom_hint' => 'От 200 до 20 000 MDL',
                'denomination_custom_placeholder' => 'Введите сумму',
            )
            : array(
                'steps' => array(
                    'Design',
                    'Valoare',
                    'Pentru cine',
                    'Când',
                    'Plată',
                ),
                'back' => 'Înapoi',
                'design_title' => 'Alege designul cardului',
                'next' => 'Continuă',
                'note' => 'Certificat cadou electronic. Pentru a primi și utiliza certificatul este necesară aplicația mobilă Vizaje-Nica.',
                'denomination_title' => 'Alege valoarea cardului',
                'denomination_custom' => 'Sumă proprie',
                'denomination_custom_hint' => 'De la 200 până la 20 000 MDL',
                'denomination_custom_placeholder' => 'Introdu suma',
            );
        ?>
   <section
    class="certificate certificate-builder"
    data-certificate-builder
    data-theme="design-1"
>
    <h1 class="certificate-builder__seo-title">
        <?= $page_name ?>
    </h1>

    <div class="certificate-builder__layout">

        <!-- =====================================================
             SIDEBAR
             ===================================================== -->

        <aside
            class="certificate-builder__sidebar"
            aria-label="<?= CERTIFICATE_STEP ?> 1 <?= CERTIFICATE_FROM ?> 5"
        >
            <div
                class="certificate-builder__step-number"
                data-builder-step-number
            >
                01
            </div>

            <div
                class="certificate-builder__mobile-nav"
                data-certificate-mobile-nav
                data-step-0="<?= htmlspecialchars(
                    $certificateBuilderCopy['steps'][0],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                data-step-1="<?= htmlspecialchars(
                    $certificateBuilderCopy['steps'][1],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                data-step-2="<?= htmlspecialchars(
                    $certificateBuilderCopy['steps'][2],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                data-step-3="<?= htmlspecialchars(
                    $certificateBuilderCopy['steps'][3],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                data-step-4="<?= htmlspecialchars(
                    $certificateBuilderCopy['steps'][4],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >
                <button
                    type="button"
                    class="certificate-builder__mobile-back"
                    data-certificate-mobile-back
                    aria-label="<?= $certificateBuilderCopy['back'] ?>"
                >
                    <svg
                        width="18"
                        height="18"
                        viewBox="0 0 18 18"
                        fill="none"
                        aria-hidden="true"
                    >
                        <path
                            d="M11.5 3L5.5 9L11.5 15"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </button>

                <span
                    class="certificate-builder__mobile-title"
                    data-certificate-mobile-title
                >
                    <?= $certificateBuilderCopy['steps'][0] ?>
                </span>
            </div>

            <nav
                class="certificate-builder__steps"
                aria-label="<?= $page_name ?>"
            >
                <?php foreach ($certificateBuilderCopy['steps'] as $index => $stepLabel): ?>
                    <div
                        class="certificate-builder__step-item <?= $index === 0 ? '_active' : '' ?>"
                        data-builder-step-item="<?= $index ?>"
                    >
                        <span class="certificate-builder__step-marker"></span>

                       <span class="certificate-builder__step-label">
                            <span class="certificate-builder__step-index">
                                <?= $index + 1 ?>
                            </span>

                            <span class="certificate-builder__step-text">
                                <?= $stepLabel ?>
                            </span>
                        </span>
                    </div>
                <?php endforeach; ?>
            </nav>
        </aside>


        <!-- =====================================================
             WORKSPACE
             ===================================================== -->

        <div class="certificate-builder__workspace">
             <button
                        type="button"
                        class="certificate-builder__back certificate__back"
                        aria-label="<?= CERTIFICATE_BACK ?>"
                    >
                        <svg
                            width="20"
                            height="20"
                            viewBox="0 0 20 20"
                            fill="none"
                            aria-hidden="true"
                        >
                            <path
                                d="M17 10H3M8 5L3 10L8 15"
                                stroke="currentColor"
                                stroke-width="1.4"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>

                        <span><?= CERTIFICATE_BACK ?></span>
                    </button>
            <!--
                Пока сохраняем старые элементы в DOM,
                потому что legacy wizard всё ещё ищет их.
            -->

            <div class="certificate__top-info certificate-builder__legacy-state">
                

                <div class="certificate__step step-certificate">
                    <span class="step-certificate__main">
                        <span class="step-certificate__text">
                            <?= CERTIFICATE_STEP ?>&nbsp;
                        </span>

                        <span class="step-certificate__current">1</span>

                        <?= CERTIFICATE_FROM ?>

                        <span class="step-certificate__total">5</span>
                    </span>

                    <div class="step-certificate__progressbar">
                        <div class="step-certificate__progress"></div>
                    </div>
                </div>
            </div>


            <form
                method="post"
                class="certificate__blocks certificate-builder__form"
                id="certificate-form"
            >

                <!-- =================================================
                     STEP 1 — DESIGN
                     ================================================= -->

                <div
                    class="
                        certificate__block
                        certificate-builder__step
                        certificate-builder__step--design
                        _active
                    "
                    data-certificate-step="0"
                >
                    <div class="certificate-builder__scene">

                        <header class="certificate-builder__intro">
                            <h2 class="certificate-builder__heading">
                                <?= $certificateBuilderCopy['design_title'] ?>
                            </h2>
                        </header>


                        <!-- =========================================
                             DESIGN STAGE
                             ========================================= -->

                        <div
                            class="certificate-design"
                            data-certificate-slider
                        >
                            <div class="certificate-design__stage">

                                <div
                                    class="certificate-design__track"
                                    data-certificate-slider-track
                                >
                                    <?php foreach (
                                        $certificates
                                        as $index => $certificate
                                    ): ?>

                                        <?php
                                        $certificateImage =
                                            'https://app.vizaje-nica.com/public/gift_cards/'
                                            . $certificate->img;

                                        $certificateTheme =
                                            'design-' . ($index + 1);
                                        ?>

                                        <label
                                            class="certificate-design__slide"
                                            data-certificate-slide
                                            data-certificate-index="<?= $index ?>"
                                            data-certificate-theme="<?= $certificateTheme ?>"
                                        >
                                            <input
                                                type="radio"
                                                name="design-certificate"
                                                value="<?= $certificate->id ?>"
                                                data-value="<?= $certificateImage ?>"
                                                class="certificate-design__input"
                                            >

                                            <span class="certificate-design__media">
                                                <span
                                                    class="certificate-design__tilt"
                                                    data-certificate-tilt
                                                >
                                                    <img
                                                        src="<?= $certificateImage ?>"
                                                        alt="<?= htmlspecialchars(
                                                            $certificate->nameRO,
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ) ?>"
                                                        loading="<?= $index === 0
                                                            ? 'eager'
                                                            : 'lazy' ?>"
                                                        decoding="async"
                                                    >
                                                    <span
                                                        class="certificate-design__glass"
                                                        data-certificate-glass
                                                        aria-hidden="true"
                                                    ></span>
                                                </span>
                                            </span>
                                        </label>

                                    <?php endforeach; ?>
                                </div>


                                <?php if (count($certificates) > 1): ?>

                                    <button
                                        type="button"
                                        class="
                                            certificate-design__arrow
                                            certificate-design__arrow--prev
                                        "
                                        data-certificate-slider-prev
                                        aria-label="Previous certificate design"
                                    >
                                        <svg
                                            width="22"
                                            height="22"
                                            viewBox="0 0 22 22"
                                            fill="none"
                                            aria-hidden="true"
                                        >
                                            <path
                                                d="M14 4L7 11L14 18"
                                                stroke="currentColor"
                                                stroke-width="1.4"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                    </button>

                                    <button
                                        type="button"
                                        class="
                                            certificate-design__arrow
                                            certificate-design__arrow--next
                                        "
                                        data-certificate-slider-next
                                        aria-label="Next certificate design"
                                    >
                                        <svg
                                            width="22"
                                            height="22"
                                            viewBox="0 0 22 22"
                                            fill="none"
                                            aria-hidden="true"
                                        >
                                            <path
                                                d="M8 4L15 11L8 18"
                                                stroke="currentColor"
                                                stroke-width="1.4"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                    </button>

                                <?php endif; ?>

                            </div>


                            <!-- =====================================
                                 ARC SELECTOR
                                 ===================================== -->

                            <?php if (count($certificates) > 1): ?>

                                <div
                                    class="certificate-selector"
                                    data-certificate-selector
                                >
                                    <svg
                                        class="certificate-selector__curve"
                                        viewBox="0 0 720 130"
                                        preserveAspectRatio="none"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="M20 112 Q360 8 700 112"
                                            fill="none"
                                            vector-effect="non-scaling-stroke"
                                        />
                                    </svg>

                                    <div class="certificate-selector__items">

                                        <?php foreach (
                                            $certificates
                                            as $index => $certificate
                                        ): ?>

                                            <button
                                                type="button"
                                                class="certificate-selector__item"
                                                data-certificate-slider-dot="<?= $index ?>"
                                                data-certificate-selector-item="<?= $index ?>"
                                                aria-label="Design <?= $index + 1 ?>"
                                            >
                                                <span
                                                    class="certificate-selector__number"
                                                >
                                                    <?= str_pad(
                                                        (string) ($index + 1),
                                                        2,
                                                        '0',
                                                        STR_PAD_LEFT
                                                    ) ?>
                                                </span>

                                                <span
                                                    class="certificate-selector__dot"
                                                ></span>
                                            </button>

                                        <?php endforeach; ?>

                                    </div>
                                </div>

                            <?php endif; ?>
                            <p class="certificate-builder__note">
                                <?= $certificateBuilderCopy['note'] ?>
                            </p>
                        </div>


                        <!-- =========================================
                             NEXT
                             ========================================= -->
                        
                        <div class="certificate-builder__action">
                            <button
                                type="button"
                                class="
                                    certificate-builder__next
                                    _next-step
                                "
                            >
                                <span>
                                    <?= $certificateBuilderCopy['next'] ?>
                                </span>

                                <svg
                                    width="20"
                                    height="20"
                                    viewBox="0 0 20 20"
                                    fill="none"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M3 10H17M12 5L17 10L12 15"
                                        stroke="currentColor"
                                        stroke-width="1.4"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </button>
                        </div>

                    </div>
                </div>


                <!-- =================================================
                     STEP 2
                     ОСТАВЬ ТВОЙ ТЕКУЩИЙ BLOCK 2 ЗДЕСЬ БЕЗ ИЗМЕНЕНИЙ
                     ================================================= -->
                    
                    <div
                        class="
                            certificate__block
                            certificate-builder__step
                            certificate-builder__step--denomination
                        "
                        data-certificate-step="1"
                    >
                        <div class="certificate-denomination">

                            <!-- =============================================
                                LEFT — SELECTED CERTIFICATE
                                ============================================= -->

                            <div class="certificate-denomination__visual">

                                <div
                                    class="certificate-denomination__aura"
                                    aria-hidden="true"
                                ></div>

                                <div class="certificate-denomination__card">
                                    <div
                                        class="certificate-denomination__card-inner"
                                        data-denomination-card-tilt
                                    >
                                        <img
                                            src=""
                                            alt=""
                                            class="certificate-denomination__card-image"
                                            data-denomination-card-image
                                        >

                                        <span
                                            class="certificate-denomination__glass"
                                            data-denomination-card-glass
                                            aria-hidden="true"
                                        ></span>
                                    </div>
                                </div>

                            </div>


                            <!-- =============================================
                                RIGHT — AMOUNT
                                ============================================= -->

                            <div class="certificate-denomination__panel">

                                <header class="certificate-denomination__header">
                                    <h2 class="certificate-denomination__title">
                                        <?= $certificateBuilderCopy['denomination_title'] ?>
                                    </h2>
                                </header>


                                <div class="certificate-denomination__content">

                                    <!-- =====================================
                                        OPTIONS
                                        ===================================== -->

                                    <div
                                        class="certificate-denomination__selector"
                                        data-denomination-selector
                                    >

                                        <svg
                                            class="certificate-denomination__curve"
                                            viewBox="0 0 90 520"
                                            preserveAspectRatio="none"
                                            aria-hidden="true"
                                        >
                                            <path
                                                d="M60 5 C15 120, 15 400, 60 515"
                                                fill="none"
                                                vector-effect="non-scaling-stroke"
                                            />
                                        </svg>


                                        <div class="certificate-denomination__options">

                                            <!-- Custom -->

                                            <div
                                                class="certificate-denomination__option certificate-denomination__option--custom"
                                                data-denomination-option
                                                data-denomination-custom-option
                                            >
                                                <button
                                                    type="button"
                                                    class="certificate-denomination__option-button"
                                                    data-denomination-custom-trigger
                                                >
                                                    <span
                                                        class="certificate-denomination__marker"
                                                    ></span>

                                                    <span
                                                        class="certificate-denomination__option-copy"
                                                    >
                                                        <strong>
                                                            <?= $certificateBuilderCopy['denomination_custom'] ?>
                                                        </strong>

                                                        <small>
                                                            <?= $certificateBuilderCopy['denomination_custom_hint'] ?>
                                                        </small>
                                                    </span>
                                                </button>


                                                <div
                                                    class="certificate-denomination__custom"
                                                    data-denomination-custom
                                                >
                                                    <div
                                                        class="certificate-denomination__custom-field"
                                                    >
                                                        <input
                                                            type="text"
                                                            inputmode="numeric"
                                                            autocomplete="off"
                                                            class="certificate-denomination__custom-input"
                                                            data-denomination-custom-input
                                                            placeholder="<?= htmlspecialchars(
                                                                $certificateBuilderCopy['denomination_custom_placeholder'],
                                                                ENT_QUOTES,
                                                                'UTF-8'
                                                            ) ?>"
                                                        >

                                                        <span>MDL</span>
                                                    </div>
                                                </div>
                                            </div>


                                            <?php
                                            $denominationPresets = array(
                                                200,
                                                300,
                                                500,
                                                1000,
                                                1500,
                                                2000,
                                                3000,
                                                5000,
                                                10000,
                                                20000,
                                            );
                                            ?>

                                            <?php foreach ($denominationPresets as $amount): ?>

                                                <button
                                                    type="button"
                                                    class="certificate-denomination__option"
                                                    data-denomination-option
                                                    data-denomination-value="<?= $amount ?>"
                                                >
                                                    <span
                                                        class="certificate-denomination__marker"
                                                    ></span>

                                                    <span
                                                        class="certificate-denomination__option-label"
                                                    >
                                                        <?= number_format($amount, 0, '.', ' ') ?>
                                                        MDL
                                                    </span>
                                                </button>

                                            <?php endforeach; ?>

                                        </div>
                                    </div>


                                    <!-- =====================================
                                        LIVE VALUE
                                        ===================================== -->

                                    <div class="certificate-denomination__result">

                                        <div
                                            class="certificate-denomination__value"
                                            data-denomination-display
                                            aria-live="polite"
                                        >
                                            2 000 MDL
                                        </div>

                                    </div>

                                </div>


                                <!-- =========================================
                                    CANONICAL BACKEND VALUE
                                    ========================================= -->

                                <div class="denomination-certificate__item">
                                    <input
                                        type="hidden"
                                        name="denomination"
                                        value="2000"
                                        data-denomination-input
                                    >
                                </div>

                            </div>


                            <!-- =============================================
                                NEXT
                                ============================================= -->

                            <div class="certificate-builder__action">
                                <button
                                    type="button"
                                    class="
                                        certificate-builder__next
                                        _next-step
                                    "
                                >
                                    <span>
                                        <?= $certificateBuilderCopy['next'] ?>
                                    </span>

                                    <svg
                                        width="20"
                                        height="20"
                                        viewBox="0 0 20 20"
                                        fill="none"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="M3 10H17M12 5L17 10L12 15"
                                            stroke="currentColor"
                                            stroke-width="1.4"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </button>
                            </div>

                        </div>
                    </div>
                    <div
                        class="
                            certificate__block
                            certificate-builder__step
                            certificate-builder__step--recipient
                        "
                        data-certificate-step="2"
                    >
                        <div class="certificate-recipient">

                            <header class="certificate-recipient__header">
                                <h2 class="certificate-recipient__title">
                                    Для кого сертификат?
                                </h2>

                                <p class="certificate-recipient__description">
                                    Укажите данные получателя и добавьте личное поздравление.
                                </p>
                            </header>

                            <div class="certificate-recipient__form">

                                <div class="certificate-recipient__group">
                                    <div class="certificate-recipient__group-head">
                                        <h3 class="certificate-recipient__group-title">
                                            Поздравление
                                        </h3>
                                    </div>

                                    <div class="certificate-recipient__row">
                                        <div class="certificate-recipient__field data-certificate__item">
                                            <label
                                                for="certificate-recipient-name"
                                                class="certificate-recipient__label"
                                            >
                                                Кому
                                            </label>

                                            <input
                                                id="certificate-recipient-name"
                                                autocomplete="off"
                                                type="text"
                                                name="from"
                                                data-value="<?= CERTIFICATE_STEP3_PH_TO ?>"
                                                class="certificate-recipient__input input"
                                            >
                                        </div>

                                        <div class="certificate-recipient__field data-certificate__item">
                                            <label
                                                for="certificate-sender-name"
                                                class="certificate-recipient__label"
                                            >
                                                От
                                            </label>

                                            <input
                                                id="certificate-sender-name"
                                                autocomplete="off"
                                                type="text"
                                                name="to"
                                                data-value="<?= CERTIFICATE_STEP3_PH_FROM ?>"
                                                class="certificate-recipient__input input"
                                            >
                                        </div>
                                    </div>

                                    <div
                                        class="
                                            certificate-recipient__field
                                            certificate-recipient__field--message
                                            data-certificate__item
                                        "
                                    >
                                        <label
                                            for="certificate-message"
                                            class="certificate-recipient__label"
                                        >
                                            Сообщение
                                        </label>

                                        <textarea
                                            id="certificate-message"
                                            autocomplete="off"
                                            name="text"
                                            data-value="<?= CERTIFICATE_STEP3_PH_TEXT ?>"
                                            class="certificate-recipient__textarea input"
                                            rows="4"
                                        ></textarea>
                                    </div>
                                </div>


                                <div class="certificate-recipient__group">
                                    <div class="certificate-recipient__group-head">
                                        <h3 class="certificate-recipient__group-title">
                                            Контакты получателя
                                        </h3>

                                        <p class="certificate-recipient__group-description">
                                            На эти контакты будет отправлен электронный сертификат.
                                        </p>
                                    </div>

                                    <div class="certificate-recipient__row">
                                        <div class="certificate-recipient__field data-certificate__item">
                                            <label
                                                for="certificate-recipient-phone"
                                                class="certificate-recipient__label"
                                            >
                                                Телефон получателя
                                            </label>

                                            <input
                                                id="certificate-recipient-phone"
                                                autocomplete="off"
                                                type="text"
                                                name="phone"
                                                data-value="<?= CERTIFICATE_STEP4_LABEL_PHONE ?>"
                                                class="certificate-recipient__input input _phone"
                                            >
                                        </div>

                                        <div class="certificate-recipient__field data-certificate__item">
                                            <label
                                                for="certificate-recipient-email"
                                                class="certificate-recipient__label"
                                            >
                                                Email получателя (необязательно)
                                            </label>

                                            <input
                                                id="certificate-recipient-email"
                                                autocomplete="off"
                                                type="text"
                                                name="email"
                                                data-value="<?= CERTIFICATE_STEP4_LABEL_EMAIL ?>"
                                                class="certificate-recipient__input input"
                                            >
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="certificate-recipient__footer">
                                <button
                                    type="button"
                                    class="certificate-builder__next _next-step"
                                >
                                    <span>Далее</span>

                                    <svg
                                        width="20"
                                        height="20"
                                        viewBox="0 0 20 20"
                                        fill="none"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="M3 10H17M12 5L17 10L12 15"
                                            stroke="currentColor"
                                            stroke-width="1.4"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </button>
                            </div>

                        </div>
                    </div>
                    <div
                        class="
                            certificate__block
                            certificate-builder__step
                            certificate-builder__step--delivery
                        "
                        data-certificate-step="3"
                    >
                        <div class="certificate-delivery">

                            <header class="certificate-delivery__header">
                                <h2 class="certificate-delivery__title">
                                    <?= $lclang === 'ru'
                                        ? 'Когда отправить сертификат?'
                                        : 'Când trimitem certificatul?' ?>
                                </h2>

                                <p class="certificate-delivery__description">
                                    <?= $lclang === 'ru'
                                        ? 'Выберите момент отправки электронного сертификата получателю.'
                                        : 'Alege momentul în care certificatul electronic va fi trimis destinatarului.' ?>
                                </p>
                            </header>


                            <div class="certificate-delivery__content">

                                <!-- ==========================================
                                    DELIVERY TIME
                                    ========================================== -->

                                <section class="certificate-delivery__section">

                                    <div class="certificate-delivery__section-head">
                                        <h3 class="certificate-delivery__section-title">
                                            <?= $lclang === 'ru'
                                                ? 'Время отправки'
                                                : 'Momentul expedierii' ?>
                                        </h3>
                                    </div>


                                    <div class="certificate-delivery__options">

                                        <label class="certificate-delivery__option">
                                            <input
                                                type="radio"
                                                name="time-send"
                                                value="inst"
                                                checked
                                                class="
                                                    certificate-delivery__radio
                                                    time-send-certificate__radio
                                                "
                                            >

                                            <span class="certificate-delivery__option-marker"></span>

                                            <span class="certificate-delivery__option-content">
                                                <strong>
                                                    <?= CERTIFICATE_STEP5_OPTION_NOW ?>
                                                </strong>

                                                <small>
                                                    <?= $lclang === 'ru'
                                                        ? 'Сертификат будет отправлен сразу после оплаты.'
                                                        : 'Certificatul va fi trimis imediat după plată.' ?>
                                                </small>
                                            </span>
                                        </label>


                                        <label class="certificate-delivery__option">
                                            <input
                                                type="radio"
                                                name="time-send"
                                                value="set"
                                                class="
                                                    certificate-delivery__radio
                                                    time-send-certificate__radio
                                                "
                                            >

                                            <span class="certificate-delivery__option-marker"></span>

                                            <span class="certificate-delivery__option-content">
                                                <strong>
                                                    <?= CERTIFICATE_STEP5_OPTION_SET ?>
                                                </strong>

                                                <small>
                                                    <?= $lclang === 'ru'
                                                        ? 'Выберите дату и время отправки.'
                                                        : 'Alege data și ora expedierii.' ?>
                                                </small>
                                            </span>
                                        </label>

                                    </div>


                                    <!-- ======================================
                                        SCHEDULE
                                        Keep legacy class for current JS
                                        ====================================== -->

                                    <div
                                        class="
                                            certificate-delivery__schedule
                                            time-send-certificate__set
                                        "
                                    >
                                        <div class="certificate-delivery__field">
                                            <label
                                                for="certificate-send-date"
                                                class="certificate-delivery__label"
                                            >
                                                <?= CERTIFICATE_STEP5_LABEL_DATE ?>
                                            </label>

                                            <input
                                                id="certificate-send-date"
                                                autocomplete="off"
                                                type="text"
                                                name="date"
                                                data-value="<?= CERTIFICATE_STEP5_LABEL_DATE ?>"
                                                readonly
                                                class="
                                                    certificate-delivery__input
                                                    input
                                                    _date
                                                "
                                            >
                                        </div>


                                        <div class="certificate-delivery__field">
                                            <label
                                                for="certificate-send-time"
                                                class="certificate-delivery__label"
                                            >
                                                <?= CERTIFICATE_STEP5_LABEL_TIME ?>
                                            </label>

                                            <input
                                                id="certificate-send-time"
                                                autocomplete="off"
                                                type="time"
                                                name="time"
                                                placeholder="<?= CERTIFICATE_STEP5_LABEL_TIME ?>"
                                                data-value="<?= CERTIFICATE_STEP5_LABEL_TIME ?>"
                                                class="
                                                    certificate-delivery__input
                                                    input
                                                    _time
                                                "
                                            >
                                        </div>
                                    </div>

                                </section>


                                <!-- ==========================================
                                    RECEIPT
                                    ========================================== -->

                                <section class="certificate-delivery__section">

                                    <div class="certificate-delivery__section-head">
                                        <h3 class="certificate-delivery__section-title">
                                            <?= CERTIFICATE_STEP6_TITLE ?>
                                        </h3>

                                        <p class="certificate-delivery__section-description">
                                            <?= $lclang === 'ru'
                                                ? 'Укажите email, на который отправить чек об оплате.'
                                                : 'Indică emailul la care să fie trimis bonul de plată.' ?>
                                        </p>
                                    </div>


                                    <div class="certificate-delivery__field">
                                        <label
                                            for="certificate-receipt-email"
                                            class="certificate-delivery__label"
                                        >
                                            <?= CERTIFICATE_STEP6_LABEL_EMAIL ?>
                                        </label>

                                        <input
                                            id="certificate-receipt-email"
                                            type="text"
                                            name="cec_email"
                                            data-value="<?= CERTIFICATE_STEP6_LABEL_EMAIL ?>"
                                            class="certificate-delivery__input input"
                                        >
                                    </div>


                                    <p class="certificate-delivery__warning">
                                        <?= CERTIFICATE_STEP6_TEXT_WARNING ?>
                                    </p>

                                </section>

                            </div>


                            <div class="certificate-delivery__footer">
                                <button
                                    type="button"
                                    class="certificate-builder__next _next-step"
                                >
                                    <span>
                                        <?= $certificateBuilderCopy['next'] ?>
                                    </span>

                                    <svg
                                        width="20"
                                        height="20"
                                        viewBox="0 0 20 20"
                                        fill="none"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="M3 10H17M12 5L17 10L12 15"
                                            stroke="currentColor"
                                            stroke-width="1.4"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </button>
                            </div>

                        </div>
                    </div>
                    <div
                    class="
                        certificate__block
                        certificate-builder__step
                        certificate-builder__step--review
                    "
                    data-certificate-step="4"
                >
                    <div class="certificate-review">

                        <header class="certificate-review__header">
                            <h2 class="certificate-review__title">
                                <?= $lclang === 'ru'
                                    ? 'Проверьте данные'
                                    : 'Verifică datele' ?>
                            </h2>

                            <p class="certificate-review__description">
                                <?= $lclang === 'ru'
                                    ? 'Убедитесь, что всё указано правильно перед оплатой.'
                                    : 'Verifică dacă toate datele sunt corecte înainte de plată.' ?>
                            </p>
                        </header>


                        <div class="certificate-review__layout">

                            <!-- =========================================
                                LEFT — REVIEW
                                ========================================= -->

                            <div class="certificate-review__details">

                                <section class="certificate-review__section">
                                    <h3 class="certificate-review__section-title">
                                        <?= $lclang === 'ru'
                                            ? 'Получатель'
                                            : 'Destinatar' ?>
                                    </h3>

                                    <div class="certificate-review__rows">

                                        <div class="certificate-review__row">
                                            <span class="certificate-review__label">
                                                <?= $lclang === 'ru'
                                                    ? 'Имя'
                                                    : 'Nume' ?>
                                            </span>

                                            <strong
                                                class="certificate-review__value"
                                                id="cer-to"
                                            ></strong>
                                        </div>


                                        <div class="certificate-review__row">
                                            <span class="certificate-review__label">
                                                <?= $lclang === 'ru'
                                                    ? 'Телефон'
                                                    : 'Telefon' ?>
                                            </span>

                                            <strong
                                                class="certificate-review__value"
                                                id="cer-phone"
                                            ></strong>

                                            <p class="certificate-review__hint">
                                                <?= $lclang === 'ru'
                                                    ? 'На этот номер будет отправлен сертификат.'
                                                    : 'Certificatul va fi trimis la acest număr.' ?>
                                            </p>
                                        </div>

                                    </div>
                                </section>


                                <section class="certificate-review__section">
                                    <h3 class="certificate-review__section-title">
                                        <?= $lclang === 'ru'
                                            ? 'Поздравление'
                                            : 'Mesaj' ?>
                                    </h3>

                                    <p
                                        class="
                                            certificate-review__message
                                            certificate-review__value
                                        "
                                        id="cer-text"
                                    ></p>

                                    <div class="certificate-review__row">
                                        <span class="certificate-review__label">
                                            <?= $lclang === 'ru'
                                                ? 'От кого'
                                                : 'De la' ?>
                                        </span>

                                        <strong
                                            class="certificate-review__value"
                                            id="cer-from"
                                        ></strong>
                                    </div>
                                </section>


                                <section class="certificate-review__section">
                                    <h3 class="certificate-review__section-title">
                                        <?= $lclang === 'ru'
                                            ? 'Чек об оплате'
                                            : 'Bon de plată' ?>
                                    </h3>

                                    <div class="certificate-review__row">
                                        <span class="certificate-review__label">
                                            Email
                                        </span>

                                        <strong
                                            class="certificate-review__value"
                                            id="cer-email"
                                        ></strong>
                                    </div>

                                    <p class="certificate-review__hint">
                                        <?= $lclang === 'ru'
                                            ? 'Чек будет отправлен на этот email.'
                                            : 'Bonul va fi trimis la această adresă de email.' ?>
                                    </p>
                                </section>

                            </div>


                            <!-- =========================================
                                RIGHT — ORDER / PAYMENT
                                ========================================= -->

                            <aside class="certificate-review__summary">

                                <div class="certificate-review__card-preview">
                                    <img
                                        id="cer-card"
                                        src=""
                                        alt=""
                                    >
                                </div>


                                <div class="certificate-review__summary-head">
                                    <h3 class="certificate-review__summary-title">
                                        <?= $lclang === 'ru'
                                            ? 'Ваш заказ'
                                            : 'Comanda ta' ?>
                                    </h3>

                                    <p class="certificate-review__summary-subtitle">
                                        <?= $lclang === 'ru'
                                            ? 'Электронный подарочный сертификат'
                                            : 'Certificat cadou electronic' ?>
                                    </p>
                                </div>


                                <div class="certificate-review__summary-rows">

                                    <div class="certificate-review__summary-row">
                                        <span>
                                            <?= $lclang === 'ru'
                                                ? 'Сертификат'
                                                : 'Certificat' ?>
                                        </span>

                                        <strong>1</strong>
                                    </div>


                                    <div class="certificate-review__summary-row">
                                        <span>
                                            <?= $lclang === 'ru'
                                                ? 'Доставка'
                                                : 'Livrare' ?>
                                        </span>

                                        <strong>
                                            <?= $lclang === 'ru'
                                                ? 'Электронная'
                                                : 'Electronică' ?>
                                        </strong>
                                    </div>

                                </div>


                                <div class="certificate-review__total">
                                    <span>
                                        <?= $lclang === 'ru'
                                            ? 'Итого'
                                            : 'Total' ?>
                                    </span>

                                    <strong id="cer-value"></strong>
                                </div>


                                <!-- =====================================
                                    PAYMENT
                                    ===================================== -->

                                <div class="certificate-review__payment">

                                    <div class="certificate-review__payment-head">
                                        <span class="certificate-review__payment-label">
                                            <?= $lclang === 'ru'
                                                ? 'Онлайн-оплата'
                                                : 'Plată online' ?>
                                        </span>

                                        <p>
                                            <?= $lclang === 'ru'
                                                ? 'Доступные способы оплаты'
                                                : 'Metode de plată disponibile' ?>
                                        </p>
                                    </div>


                                    <div class="certificate-review__payment-methods">

                                        <div class="certificate-review__payment-method">
                                            <img
                                                src="/app/img/checkout/visa.png"
                                                alt="Visa"
                                            >
                                        </div>

                                        <div class="certificate-review__payment-method">
                                            <img
                                                src="/app/img/checkout/mastercard.png"
                                                alt="Mastercard"
                                            >
                                        </div>

                                        <div class="certificate-review__payment-method">
                                            <img
                                                src="/app/img/checkout/apple-pay.png"
                                                alt="Apple Pay"
                                            >
                                        </div>

                                        <div class="certificate-review__payment-method">
                                            <img
                                                src="/app/img/checkout/google-pay.png"
                                                alt="Google Pay"
                                            >
                                        </div>

                                    </div>


                                    <p class="certificate-review__secure">
                                        <svg
                                            width="14"
                                            height="14"
                                            viewBox="0 0 14 14"
                                            fill="none"
                                            aria-hidden="true"
                                        >
                                            <path
                                                d="M3.5 6V4.5C3.5 2.57 5.07 1 7 1C8.93 1 10.5 2.57 10.5 4.5V6"
                                                stroke="currentColor"
                                                stroke-width="1.2"
                                                stroke-linecap="round"
                                            />

                                            <rect
                                                x="2"
                                                y="6"
                                                width="10"
                                                height="7"
                                                rx="1.5"
                                                stroke="currentColor"
                                                stroke-width="1.2"
                                            />
                                        </svg>

                                        <?= $lclang === 'ru'
                                            ? 'Безопасная и защищённая оплата'
                                            : 'Plată sigură și protejată' ?>
                                    </p>

                                </div>


                                <button
                                    type="submit"
                                    class="certificate-review__submit"
                                >
                                    <span>
                                        <?= $lclang === 'ru'
                                            ? 'Подтвердить и оплатить'
                                            : 'Confirmă și achită' ?>
                                    </span>

                                    <svg
                                        width="18"
                                        height="18"
                                        viewBox="0 0 18 18"
                                        fill="none"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="M3 9H15M10 4L15 9L10 14"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </button>

                            </aside>

                        </div>

                    </div>
                </div>
                </form>
            </div>
        </div>
    </section>
    <script>
        document.getElementById('certificate-form').addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });
    </script>
</main>