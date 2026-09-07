<main class="page">
    <section class="order-result order-result--error">
        <div class="order-result__container _container">
            <div class="order-result__card">
                <div class="order-result__icon" aria-hidden="true">
                    <svg
                        width="34"
                        height="34"
                        viewBox="0 0 34 34"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M17 10V18"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                        />

                        <circle
                            cx="17"
                            cy="23.5"
                            r="1.4"
                            fill="currentColor"
                        />

                        <circle
                            cx="17"
                            cy="17"
                            r="15"
                            stroke="currentColor"
                            stroke-width="2"
                        />
                    </svg>
                </div>

                <p class="order-result__eyebrow">
                    <?= $lclang === 'ro'
                        ? 'Comanda nu a fost finalizată'
                        : 'Заказ не был оформлен' ?>
                </p>

                <h1 class="order-result__title">
                    <?= AN_ERROR_OCCURRED ?>
                </h1>

                <p class="order-result__text">
                    <?= COULD_NOT_PROCESS_REQUEST ?>
                </p>

                <div class="order-result__notice">
                    <p>
                        <?= $lclang === 'ro'
                            ? 'Plata nu a fost confirmată. Verificați datele introduse sau încercați din nou.'
                            : 'Оплата не была подтверждена. Проверьте введённые данные или попробуйте ещё раз.' ?>
                    </p>
                </div>

                <div class="order-result__actions">
                    <a
                        href="/<?= $lclang ?>/<?= $lclang === 'ro'
                            ? 'checkout'
                            : 'oformlenie-zakaza' ?>"
                        class="order-result__button order-result__button--primary"
                    >
                        <?= $lclang === 'ro'
                            ? 'Încercați din nou'
                            : 'Попробовать снова' ?>
                    </a>

                    <a
                        href="/<?= $lclang ?>/<?= $menu['all'][3]->uri ?>"
                        class="order-result__button order-result__button--secondary"
                    >
                        <?= BACK_TO_CATALOG ?>
                    </a>
                </div>

                <p class="order-result__support">
                    <?= $lclang === 'ro'
                        ? 'Dacă suma a fost retrasă, dar comanda nu apare, contactați serviciul de suport.'
                        : 'Если деньги списались, но заказ не появился, обратитесь в службу поддержки.' ?>
                </p>

                <a
                    href="/<?= $lclang ?>/<?= $lclang === 'ro'
                        ? 'contacte'
                        : 'kontaktyi' ?>"
                    class="order-result__support-link"
                >
                    <?= $lclang === 'ro'
                        ? 'Contactați-ne'
                        : 'Связаться с нами' ?>
                </a>
            </div>
        </div>
    </section>
</main>