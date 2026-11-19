<?php
$isRomanian = ($lclang ?? 'ru') === 'ro';
$isClientLoggedIn = !empty($client_info->id);
$applyUrl = (string) ($partners_apply_url ?? ($isRomanian ? '/ro/parteneri/apply' : '/ru/partners/apply'));
$loginUrl = (string) ($partners_login_url ?? ($isRomanian ? '/ro/contul-meu?creator' : '/ru/lichnyiy-kabinet?creator'));
$steps = $isRomanian
    ? array(
        array('01', 'Trimite cererea', 'Spune-ne câteva lucruri despre tine și despre conținutul tău. Echipa noastră va analiza candidatura.'),
        array('02', 'Distribuie recomandări', 'După aprobare, creează linkuri personale către produsele și paginile tale preferate.'),
        array('03', 'Primește recompense', 'Primești comision pentru comenzile finalizate și poți urmări rezultatele în cont.'),
    )
    : array(
        array('01', 'Подайте заявку', 'Расскажите о себе и своём контенте. Команда рассмотрит вашу заявку.'),
        array('02', 'Делитесь рекомендациями', 'После одобрения создавайте персональные ссылки на любимые товары и страницы.'),
        array('03', 'Получайте вознаграждение', 'Получайте комиссию за завершённые заказы и следите за результатами в кабинете.'),
    );
$benefits = $isRomanian
    ? array(
        array('✳', 'Libertate creativă', 'Distribuie recenzii, selecții și ritualuri de beauty în stilul tău.'),
        array('↗', 'Venit din recomandări', 'Transformă recomandările autentice în recompense pentru comenzile finalizate.'),
    )
    : array(
        array('✳', 'Свобода творчества', 'Делитесь обзорами, подборками и ритуалами в своём стиле.'),
        array('↗', 'Доход от рекомендаций', 'Превращайте искренние рекомендации в вознаграждение за завершённые заказы.'),
    );
?>
<main class="partners-landing">
    <section class="partners-landing__hero" id="hero" aria-labelledby="partners-landing-title">
        <div class="partners-landing__hero-inner">
            <p class="partners-landing__hero-badge"><?= $isRomanian ? 'PROGRAM DE PARTENERIAT' : 'ПАРТНЁРСКАЯ ПРОГРАММА' ?></p>
            <h1 id="partners-landing-title" class="partners-landing__hero-title">
                <span class="partners-landing__hero-line"><?= $isRomanian ? 'Recomandă' : 'Рекомендуйте' ?></span>
                <span class="partners-landing__hero-line">
                    <img src="/app/img/partners-landing/hero-product-01.png" alt="" width="76" height="76" loading="eager">
                    <span>Vizaje-Nica</span>
                </span>
                <span class="partners-landing__hero-line">
                    <span><?= $isRomanian ? 'și câștigă' : 'и получайте' ?></span>
                    <img src="/app/img/partners-landing/hero-product-02.png" alt="" width="76" height="76" loading="eager">
                </span>
                <span class="partners-landing__hero-line">
                    <?= $isRomanian ? 'recompense' : 'вознаграждение' ?>
                    <img src="/app/img/partners-landing/hero-product-03.png" alt="" width="76" height="76" loading="eager">
                </span>
            </h1>
            <p class="partners-landing__hero-lead"><?= $isRomanian
                ? 'Distribuie produsele tale preferate, inspiră-ți comunitatea și câștigă alături de Vizaje-Nica.'
                : 'Делитесь любимыми товарами, вдохновляйте свою аудиторию и зарабатывайте вместе с Vizaje-Nica.' ?></p>
            <div class="partners-landing__actions">
                <a class="partners-landing__button partners-landing__button--primary" href="<?= htmlspecialchars($applyUrl, ENT_QUOTES, 'UTF-8') ?>"<?= !$isClientLoggedIn ? ' data-auth-trigger="login" data-auth-return-url="' . htmlspecialchars($applyUrl, ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                    <?= $isRomanian ? 'Devino partener' : 'Стать партнёром' ?><span aria-hidden="true">↗</span>
                </a>
                <a class="partners-landing__button partners-landing__button--secondary" href="#how-it-works">
                    <?= $isRomanian ? 'Cum funcționează' : 'Как это работает' ?>
                </a>
            </div>
            <p class="partners-landing__login"><?= $isRomanian ? 'Ai deja un cont de creator?' : 'Уже креатор?' ?>
                <a href="<?= htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8') ?>"<?= !$isClientLoggedIn ? ' data-auth-trigger="login" data-auth-return-url="' . htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8') . '"' : '' ?>><?= $isRomanian ? 'Deschide contul Vizaje-Nica' : 'Открыть кабинет Vizaje-Nica' ?></a>
            </p>
        </div>
    </section>

    <section class="partners-landing__steps" id="how-it-works" aria-labelledby="partners-steps-title">
        <div class="partners-landing__section-heading partners-landing__section-heading--center">
            <p class="partners-landing__eyebrow"><?= $isRomanian ? 'SIMPLU ȘI CLAR' : 'ПРОСТО И ПОНЯТНО' ?></p>
            <h2 id="partners-steps-title"><?= $isRomanian ? 'Cum funcționează programul' : 'Как работает программа' ?></h2>
        </div>
        <div class="partners-landing__steps-grid">
            <?php foreach ($steps as $index => $step) : ?>
                <article class="partners-landing__step">
                    <div class="partners-landing__step-visual" aria-hidden="true">
                        <span class="partners-landing__step-icon partners-landing__step-icon--<?= (int) $index ?>"></span>
                    </div>
                    <p class="partners-landing__step-number"><?= htmlspecialchars($step[0], ENT_QUOTES, 'UTF-8') ?></p>
                    <h3><?= htmlspecialchars($step[1], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="partners-landing__step-copy"><?= htmlspecialchars($step[2], ENT_QUOTES, 'UTF-8') ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="partners-landing__community" id="community" aria-labelledby="partners-community-title">
        <div class="partners-landing__community-copy">
            <p class="partners-landing__eyebrow"><?= $isRomanian ? 'COMUNITATEA CREATORILOR' : 'СООБЩЕСТВО КРЕАТОРОВ' ?></p>
            <h2 id="partners-community-title">
                <?= $isRomanian ? 'Conținutul tău.' : 'Ваш контент.' ?><br>
                <span><?= $isRomanian ? 'Stilul tău.' : 'Ваш стиль.' ?></span>
            </h2>
            <p class="partners-landing__community-lead"><?= $isRomanian
                ? 'Creează conținut despre produsele Vizaje-Nica preferate, inspiră-ți publicul și crește împreună cu noi.'
                : 'Создавайте контент о любимых продуктах Vizaje-Nica, вдохновляйте аудиторию и развивайтесь вместе с нами.' ?></p>
            <div class="partners-landing__benefits">
                <?php foreach ($benefits as $benefit) : ?>
                    <article class="partners-landing__benefit">
                        <span class="partners-landing__benefit-icon" aria-hidden="true"><?= htmlspecialchars($benefit[0], ENT_QUOTES, 'UTF-8') ?></span>
                        <div>
                            <h3><?= htmlspecialchars($benefit[1], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p><?= htmlspecialchars($benefit[2], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="partners-landing__community-gallery" aria-label="<?= $isRomanian ? 'Idei de conținut' : 'Идеи для контента' ?>">
            <figure><img src="/app/img/partners-landing/community-beauty.jpg" alt="<?= $isRomanian ? 'Produse beauty' : 'Бьюти-продукты' ?>" loading="lazy"><figcaption><?= $isRomanian ? 'FRUMUSEȚE' : 'BEAUTY' ?></figcaption></figure>
            <figure><img src="/app/img/partners-landing/community-ugc.png" alt="UGC" loading="lazy"><figcaption>UGC</figcaption></figure>
            <figure><img src="/app/img/partners-landing/community-lifestyle.png" alt="LIFESTYLE" loading="lazy"><figcaption><?= $isRomanian ? 'STIL DE VIAȚĂ' : 'LIFESTYLE' ?></figcaption></figure>
        </div>
    </section>

    <section class="partners-landing__cta" id="apply" aria-labelledby="partners-cta-title">
        <p class="partners-landing__eyebrow"><?= $isRomanian ? 'DEVINO PARTENER' : 'СТАНЬТЕ ПАРТНЁРОМ' ?></p>
        <h2 id="partners-cta-title"><?= $isRomanian
            ? 'Dezvoltă-ți conținutul împreună cu Vizaje-Nica'
            : 'Развивайте свой контент вместе с Vizaje-Nica' ?></h2>
        <p><?= $isRomanian
            ? 'Alătură-te programului, creează recomandări autentice și primește recompense pentru comenzile finalizate.'
            : 'Присоединяйтесь к программе, создавайте искренние рекомендации и получайте вознаграждение за завершённые заказы.' ?></p>
        <div class="partners-landing__actions partners-landing__actions--center">
            <a class="partners-landing__button partners-landing__button--primary" href="<?= htmlspecialchars($applyUrl, ENT_QUOTES, 'UTF-8') ?>"<?= !$isClientLoggedIn ? ' data-auth-trigger="login" data-auth-return-url="' . htmlspecialchars($applyUrl, ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                <?= $isRomanian ? 'Trimite cererea' : 'Подать заявку' ?><span aria-hidden="true">↗</span>
            </a>
            <a class="partners-landing__button partners-landing__button--secondary" href="#how-it-works">
                <?= $isRomanian ? 'Află mai multe' : 'Узнать больше' ?><span aria-hidden="true">→</span>
            </a>
        </div>
    </section>
</main>
