-- The creator referral is now held in the existing first-party PHP session.
-- No affirmative referral-cookie consent timestamp is recorded.
-- Also documents this processing purpose on the RU/RO privacy and cookie page.
-- Apply after the creator referral attribution table exists.
ALTER TABLE `legacy_creator_referral_attributions`
    MODIFY COLUMN `consented_at` DATETIME(3) NULL DEFAULT NULL;

-- The privacy/cookie policy page is CMS menu row 23 in both locales.
UPDATE `menu`
SET `descRU` = CONCAT(
    COALESCE(`descRU`, ''),
    '<section><h2>Атрибуция заказов по ссылкам креаторов</h2>',
    '<p>Если вы переходите на сайт по персональной ссылке креатора, мы сохраняем идентификатор ссылки в серверной сессии сайта. Это позволяет связать не более одного последующего заказа с соответствующим креатором в течение 7 дней после перехода. Связь используется для расчёта вознаграждения креатора после завершения заказа со статусом «Закрыт».</p>',
    '<p>Срок атрибуции истекает после первого заказа или через 7 дней. Выбор настроек аналитических и рекламных cookies не меняет эту атрибуцию. Если сессия сайта завершится или cookies сайта будут отключены либо удалены, переход может не сохраниться.</p>',
    '<p>Каждый переход по короткой ссылке увеличивает её общий счётчик в кабинете креатора; он не является подсчётом уникальных людей. Идентификатор ссылки не передаётся внешним рекламным или аналитическим платформам. В записи атрибуции не сохраняются IP-адрес и user-agent. После привязки к заказу связь с креатором хранится вместе с данными заказа в соответствии со сроками хранения заказов и расчётов.</p>',
    '<h3>Сведения о сессии и cookies</h3>',
    '<p>Для сохранения сессии сайт использует собственный технический идентификатор сессии. Идентификатор ссылки и срок её действия хранятся на сервере; отдельная рекламная или аналитическая cookie для атрибуции не используется. Настройки аналитических и маркетинговых cookies не отключают эту функцию.</p>',
    '</section><!--creator-attribution-privacy-cookie-ru-v1-->'
)
WHERE `id` = 23
  AND LOCATE('creator-attribution-privacy-cookie-ru-v1', COALESCE(`descRU`, '')) = 0;

UPDATE `menu`
SET `descRO` = CONCAT(
    COALESCE(`descRO`, ''),
    '<section><h2>Atribuirea comenzilor prin linkurile creatorilor</h2>',
    '<p>Dacă accesați site-ul prin linkul personal al unui creator, păstrăm identificatorul linkului în sesiunea serverului. Astfel, cel mult o comandă ulterioară poate fi asociată creatorului respectiv în termen de 7 zile de la accesare. Asocierea este folosită pentru calcularea comisionului creatorului după finalizarea comenzii cu statutul „Închis”.</p>',
    '<p>Perioada de atribuire se încheie după prima comandă sau după 7 zile. Alegerea setărilor pentru cookie-urile de analiză și publicitate nu schimbă această atribuire. Dacă sesiunea site-ului se încheie sau cookie-urile site-ului sunt dezactivate ori șterse, accesarea poate să nu fie păstrată.</p>',
    '<p>Fiecare accesare a linkului scurt mărește contorul total afișat în cabinetul creatorului; acesta nu reprezintă numărul persoanelor unice. Identificatorul linkului nu este transmis platformelor externe de publicitate sau analiză. Înregistrarea atribuirii nu stochează adresa IP sau user-agent-ul. După asocierea cu o comandă, legătura cu creatorul se păstrează împreună cu datele comenzii conform termenelor de păstrare pentru comenzi și decontări.</p>',
    '<h3>Informații despre sesiune și cookie-uri</h3>',
    '<p>Pentru menținerea sesiunii, site-ul folosește propriul identificator tehnic de sesiune. Identificatorul linkului și perioada de atribuire sunt păstrate pe server; pentru atribuire nu se utilizează un cookie separat de publicitate sau analiză. Setările cookie-urilor de analiză și marketing nu dezactivează această funcție.</p>',
    '</section><!--creator-attribution-privacy-cookie-ro-v1-->'
)
WHERE `id` = 23
  AND LOCATE('creator-attribution-privacy-cookie-ro-v1', COALESCE(`descRO`, '')) = 0;
