-- Add useful, localized selection guidance and FAQs to the 12 priority categories.
-- Existing category copy is preserved; FAQ blocks are appended only once.
-- The introductory paragraph is used only when the category has no SEO text yet.

UPDATE `categories`
SET `seoTextRU` = CONCAT(
    COALESCE(NULLIF(TRIM(`seoTextRU`), ''), '<p>Чтобы выбрать тональное средство, сначала определите желаемое покрытие и финиш, а затем подберите оттенок и подтон. Сравнивайте варианты при естественном освещении и учитывайте привычный уход за кожей.</p>'),
    '<section class="category-seo__faq" data-seo-faq="priority-v1"><h2>Как выбрать тональное средство</h2><details class="category-seo__faq-item"><summary>Как подобрать оттенок тонального средства?</summary><p>Сравните несколько близких оттенков по линии челюсти при естественном освещении. Учитывайте подтон кожи и то, как средство выглядит после нанесения, а не только цвет во флаконе.</p></details><details class="category-seo__faq-item"><summary>Как выбрать покрытие и финиш?</summary><p>Ориентируйтесь на желаемый результат: лёгкое покрытие сохраняет естественный вид кожи, более плотное помогает выровнять тон. Матовый, сияющий или естественный финиш выбирайте с учётом типа кожи и личных предпочтений.</p></details></section>'
)
WHERE `id` = 19
  AND (`seoTextRU` IS NULL OR `seoTextRU` NOT LIKE '%data-seo-faq="priority-v1"%');

UPDATE `categories`
SET `seoTextRO` = CONCAT(
    COALESCE(NULLIF(TRIM(`seoTextRO`), ''), '<p>Pentru a alege fondul de ten, începe cu nivelul de acoperire și efectul dorit, apoi potrivește nuanța și subtonul. Compară variantele la lumină naturală și ține cont de rutina ta de îngrijire.</p>'),
    '<section class="category-seo__faq" data-seo-faq="priority-v1"><h2>Cum alegi fondul de ten</h2><details class="category-seo__faq-item"><summary>Cum aleg nuanța potrivită?</summary><p>Compară câteva nuanțe apropiate pe linia maxilarului, la lumină naturală. Ține cont de subtonul pielii și de aspectul produsului după aplicare, nu doar de culoarea din ambalaj.</p></details><details class="category-seo__faq-item"><summary>Cum aleg acoperirea și efectul?</summary><p>Pornește de la rezultatul dorit: o acoperire lejeră păstrează un aspect natural, iar una mai mare ajută la uniformizarea nuanței. Alege efectul mat, luminos sau natural în funcție de tipul pielii și preferințe.</p></details></section>'
)
WHERE `id` = 19
  AND (`seoTextRO` IS NULL OR `seoTextRO` NOT LIKE '%data-seo-faq="priority-v1"%');

UPDATE `categories`
SET `seoTextRU` = CONCAT(
    COALESCE(NULLIF(TRIM(`seoTextRU`), ''), '<p>Выбирая тушь, подумайте, какого эффекта хотите добиться: подчеркнуть длину, добавить объём или разделить ресницы. Сравните форму щёточки и тип формулы в описании конкретного продукта.</p>'),
    '<section class="category-seo__faq" data-seo-faq="priority-v1"><h2>Как выбрать тушь для ресниц</h2><details class="category-seo__faq-item"><summary>На что влияет форма щёточки?</summary><p>Форма и размер щёточки влияют на удобство нанесения и распределение туши. Для коротких или нижних ресниц многим удобнее небольшая щёточка, а для быстрого нанесения — более широкая. Ориентируйтесь на свои привычки и описание товара.</p></details><details class="category-seo__faq-item"><summary>Когда выбирать водостойкую тушь?</summary><p>Водостойкая формула может быть удобна в условиях влаги или долгого дня. Проверьте способ снятия в инструкции к продукту и подберите средство для демакияжа, которое подходит вашим ресницам и коже вокруг глаз.</p></details></section>'
)
WHERE `id` = 67
  AND (`seoTextRU` IS NULL OR `seoTextRU` NOT LIKE '%data-seo-faq="priority-v1"%');

UPDATE `categories`
SET `seoTextRO` = CONCAT(
    COALESCE(NULLIF(TRIM(`seoTextRO`), ''), '<p>Când alegi rimelul, gândește-te la efectul dorit: alungire, volum sau separarea genelor. Compară forma periuței și tipul formulei în descrierea fiecărui produs.</p>'),
    '<section class="category-seo__faq" data-seo-faq="priority-v1"><h2>Cum alegi rimelul</h2><details class="category-seo__faq-item"><summary>Ce influențează forma periuței?</summary><p>Forma și dimensiunea periuței influențează aplicarea și distribuirea produsului. Pentru gene scurte sau inferioare poate fi mai comodă o periuță mică, iar pentru aplicare rapidă una mai lată. Alege în funcție de rutina ta și de descrierea produsului.</p></details><details class="category-seo__faq-item"><summary>Când este util rimelul rezistent la apă?</summary><p>O formulă rezistentă la apă poate fi practică în condiții de umezeală sau pentru o zi lungă. Verifică instrucțiunile de îndepărtare și alege un demachiant potrivit genelor și pielii din jurul ochilor.</p></details></section>'
)
WHERE `id` = 67
  AND (`seoTextRO` IS NULL OR `seoTextRO` NOT LIKE '%data-seo-faq="priority-v1"%');

UPDATE `categories`
SET `seoTitleRO` = 'Farduri de pleoape în Chișinău | Vizaje-Nica'
WHERE `id` = 46
  AND `seoTitleRO` LIKE 'eyeliner liquid pencil product shot%';

UPDATE `categories`
SET `seoTextRU` = CONCAT(
    COALESCE(NULLIF(TRIM(`seoTextRU`), ''), '<p>Тени для век помогают создавать как спокойный повседневный макияж, так и выразительные сочетания. При выборе учитывайте оттенки, финиш и формат продукта — отдельный цвет или палетку.</p>'),
    '<section class="category-seo__faq" data-seo-faq="priority-v1"><h2>Как выбрать тени для век</h2><details class="category-seo__faq-item"><summary>Что выбрать: палетку или отдельные тени?</summary><p>Палетка удобна, если нужны сочетающиеся оттенки для разных образов. Одинарные тени подойдут, когда нужен конкретный цвет или хочется дополнить уже имеющуюся палетку.</p></details><details class="category-seo__faq-item"><summary>Как подобрать финиш теней?</summary><p>Матовые оттенки обычно используют для мягкой растушёвки и повседневного макияжа, а сияющие помогают добавить акцент. Смотрите описание и свотчи продукта, если они доступны.</p></details></section>'
)
WHERE `id` = 46
  AND (`seoTextRU` IS NULL OR `seoTextRU` NOT LIKE '%data-seo-faq="priority-v1"%');

UPDATE `categories`
SET `seoTextRO` = CONCAT(
    COALESCE(NULLIF(TRIM(`seoTextRO`), ''), '<p>Fardurile de pleoape permit realizarea atât a unui machiaj discret de zi, cât și a unor combinații expresive. La alegere, ține cont de nuanțe, efect și format: culoare individuală sau paletă.</p>'),
    '<section class="category-seo__faq" data-seo-faq="priority-v1"><h2>Cum alegi fardurile de pleoape</h2><details class="category-seo__faq-item"><summary>Ce aleg: o paletă sau farduri individuale?</summary><p>O paletă este practică atunci când vrei nuanțe care se combină pentru mai multe look-uri. Fardurile individuale sunt potrivite dacă ai nevoie de o culoare anume sau vrei să completezi o paletă existentă.</p></details><details class="category-seo__faq-item"><summary>Cum aleg efectul fardurilor?</summary><p>Nuanțele mate sunt folosite adesea pentru estompare și machiajul de zi, iar cele sidefate pot adăuga un accent. Consultă descrierea produsului și mostrele de culoare, dacă sunt disponibile.</p></details></section>'
)
WHERE `id` = 46
  AND (`seoTextRO` IS NULL OR `seoTextRO` NOT LIKE '%data-seo-faq="priority-v1"%');

UPDATE `categories`
SET `seoTextRU` = CONCAT(
    COALESCE(NULLIF(TRIM(`seoTextRU`), ''), '<p>Парфюмированную воду выбирают прежде всего по звучанию аромата на коже и личным предпочтениям. Знакомьтесь с нотами в описании и, если возможно, дайте аромату время раскрыться после нанесения.</p>'),
    '<section class="category-seo__faq" data-seo-faq="priority-v1"><h2>Как выбрать парфюмированную воду</h2><details class="category-seo__faq-item"><summary>Как понять, подойдёт ли аромат?</summary><p>Начните с описания нот и парфюмерного направления, которые вам обычно нравятся. При тестировании нанесите аромат на кожу и оцените его не только сразу, но и спустя некоторое время.</p></details><details class="category-seo__faq-item"><summary>Чем ориентироваться при выборе аромата в подарок?</summary><p>Учитывайте ароматы, которыми человек уже пользуется, любимые ноты и повод. Если предпочтения неизвестны, безопаснее выбирать знакомое направление или подарочный формат, если он доступен.</p></details></section>'
)
WHERE `id` = 36
  AND (`seoTextRU` IS NULL OR `seoTextRU` NOT LIKE '%data-seo-faq="priority-v1"%');

UPDATE `categories`
SET `seoTextRO` = CONCAT(
    COALESCE(NULLIF(TRIM(`seoTextRO`), ''), '<p>Apa de parfum se alege în primul rând după felul în care se simte aroma pe piele și după preferințele personale. Citește notele din descriere și, dacă este posibil, lasă parfumul să se dezvolte după aplicare.</p>'),
    '<section class="category-seo__faq" data-seo-faq="priority-v1"><h2>Cum alegi apa de parfum</h2><details class="category-seo__faq-item"><summary>Cum îmi dau seama dacă mi se potrivește aroma?</summary><p>Începe cu notele și familia olfactivă care îți plac de obicei. Dacă testezi parfumul, aplică-l pe piele și evaluează-l nu doar imediat, ci și după un timp.</p></details><details class="category-seo__faq-item"><summary>Cum aleg un parfum cadou?</summary><p>Ține cont de parfumurile pe care persoana le folosește deja, de notele preferate și de ocazie. Dacă nu îi cunoști preferințele, alege o familie olfactivă familiară sau un format cadou, dacă este disponibil.</p></details></section>'
)
WHERE `id` = 36
  AND (`seoTextRO` IS NULL OR `seoTextRO` NOT LIKE '%data-seo-faq="priority-v1"%');

UPDATE `categories`
SET `seoTextRU` = CONCAT(
    COALESCE(NULLIF(TRIM(`seoTextRU`), ''), '<p>Подводки и лайнеры отличаются форматом, гибкостью аппликатора и насыщенностью линии. Выбирайте вариант, которым вам удобно управлять, и сверяйтесь с описанием для уточнения способа нанесения и снятия.</p>'),
    '<section class="category-seo__faq" data-seo-faq="priority-v1"><h2>Как выбрать подводку для глаз</h2><details class="category-seo__faq-item"><summary>Что удобнее: карандаш, фломастер или жидкая подводка?</summary><p>Карандаш подходит для мягкой линии и растушёвки, фломастер помогает контролировать штрих, а жидкая подводка позволяет сделать более чёткий контур. Учитывайте свой опыт и желаемый результат.</p></details><details class="category-seo__faq-item"><summary>Как выбрать цвет подводки?</summary><p>Чёрный и коричневый — универсальные варианты для разных образов. Цветную подводку можно подобрать в тон теням или использовать как самостоятельный акцент.</p></details></section>'
)
WHERE `id` = 66
  AND (`seoTextRU` IS NULL OR `seoTextRU` NOT LIKE '%data-seo-faq="priority-v1"%');

UPDATE `categories`
SET `seoTextRO` = CONCAT(
    COALESCE(NULLIF(TRIM(`seoTextRO`), ''), '<p>Tușurile și eyeliner-ele diferă prin format, flexibilitatea aplicatorului și intensitatea liniei. Alege varianta pe care o poți controla comod și verifică descrierea pentru modul de aplicare și îndepărtare.</p>'),
    '<section class="category-seo__faq" data-seo-faq="priority-v1"><h2>Cum alegi tușul de ochi</h2><details class="category-seo__faq-item"><summary>Ce aleg: creion, marker sau tuș lichid?</summary><p>Creionul este potrivit pentru o linie mai moale și estompare, markerul oferă control asupra tușei, iar tușul lichid permite un contur mai precis. Ține cont de experiența ta și de efectul dorit.</p></details><details class="category-seo__faq-item"><summary>Cum aleg culoarea eyelinerului?</summary><p>Negrul și maroul sunt opțiuni versatile pentru diferite look-uri. Un eyeliner colorat poate fi asortat cu fardurile sau folosit ca accent separat.</p></details></section>'
)
WHERE `id` = 66
  AND (`seoTextRO` IS NULL OR `seoTextRO` NOT LIKE '%data-seo-faq="priority-v1"%');

UPDATE `categories`
SET `seoTextRU` = CONCAT(
    COALESCE(NULLIF(TRIM(`seoTextRU`), ''), '<p>При выборе помады учитывайте комфортную текстуру, желаемый финиш и оттенок. Для точного подбора цвета ориентируйтесь на свотчи и описание, помня, что отображение может отличаться на разных экранах.</p>'),
    '<section class="category-seo__faq" data-seo-faq="priority-v1"><h2>Как выбрать помаду для губ</h2><details class="category-seo__faq-item"><summary>Как подобрать оттенок помады?</summary><p>Ориентируйтесь на привычные вам оттенки и желаемый образ. Если доступны свотчи, сравните их с цветом кожи и макияжа; учитывайте, что экран может передавать цвет неточно.</p></details><details class="category-seo__faq-item"><summary>Как выбрать финиш и текстуру?</summary><p>Кремовые текстуры обычно ощущаются комфортно, матовые дают более выразительный сухой финиш, а сатиновые сочетают цвет с мягким блеском. Проверьте описание продукта и выбирайте то, что удобно носить.</p></details></section>'
)
WHERE `id` = 23
  AND (`seoTextRU` IS NULL OR `seoTextRU` NOT LIKE '%data-seo-faq="priority-v1"%');

UPDATE `categories`
SET `seoTextRO` = CONCAT(
    COALESCE(NULLIF(TRIM(`seoTextRO`), ''), '<p>Când alegi un ruj, ia în calcul textura confortabilă, efectul dorit și nuanța. Pentru o alegere mai precisă, consultă mostrele și descrierea, ținând cont că afișarea culorii poate varia de la un ecran la altul.</p>'),
    '<section class="category-seo__faq" data-seo-faq="priority-v1"><h2>Cum alegi rujul</h2><details class="category-seo__faq-item"><summary>Cum aleg nuanța rujului?</summary><p>Pornește de la nuanțele pe care le porți de obicei și de la look-ul dorit. Dacă există mostre, compară-le cu tonul pielii și machiajul; reține că ecranul poate reda culoarea diferit.</p></details><details class="category-seo__faq-item"><summary>Cum aleg efectul și textura?</summary><p>Texturile cremoase sunt adesea confortabile, cele mate oferă un efect mai intens și mai puțin lucios, iar cele satinate combină culoarea cu o strălucire discretă. Verifică descrierea și alege ce îți este comod să porți.</p></details></section>'
)
WHERE `id` = 23
  AND (`seoTextRO` IS NULL OR `seoTextRO` NOT LIKE '%data-seo-faq="priority-v1"%');

UPDATE `categories`
SET `seoTextRU` = CONCAT(
    COALESCE(NULLIF(TRIM(`seoTextRU`), ''), '<p>Корректор или консилер выбирают по зоне нанесения, желаемому покрытию и текстуре. Для области под глазами и для точечной коррекции могут быть удобны разные оттенки и формулы.</p>'),
    '<section class="category-seo__faq" data-seo-faq="priority-v1"><h2>Как выбрать корректор и консилер</h2><details class="category-seo__faq-item"><summary>Как подобрать оттенок для области под глазами?</summary><p>Для естественного результата выбирайте оттенок, близкий к тону кожи или немного светлее. Слишком светлый оттенок может подчеркнуть текстуру, поэтому проверяйте результат при естественном освещении.</p></details><details class="category-seo__faq-item"><summary>Можно ли одним консилером корректировать разные зоны?</summary><p>Можно, если его текстура и покрытие подходят каждой зоне. Для точечной маскировки и области под глазами часто выбирают разные оттенки или плотность, поэтому ориентируйтесь на описание продукта и личный комфорт.</p></details></section>'
)
WHERE `id` = 48
  AND (`seoTextRU` IS NULL OR `seoTextRU` NOT LIKE '%data-seo-faq="priority-v1"%');

UPDATE `categories`
SET `seoTextRO` = CONCAT(
    COALESCE(NULLIF(TRIM(`seoTextRO`), ''), '<p>Corectorul sau concealerul se alege în funcție de zona de aplicare, acoperirea dorită și textură. Pentru zona de sub ochi și pentru corectarea punctuală pot fi potrivite nuanțe și formule diferite.</p>'),
    '<section class="category-seo__faq" data-seo-faq="priority-v1"><h2>Cum alegi corectorul și concealerul</h2><details class="category-seo__faq-item"><summary>Cum aleg nuanța pentru zona de sub ochi?</summary><p>Pentru un rezultat natural, alege o nuanță apropiată de tonul pielii sau puțin mai deschisă. O nuanță prea deschisă poate evidenția textura, așa că verifică rezultatul la lumină naturală.</p></details><details class="category-seo__faq-item"><summary>Pot folosi același concealer pentru zone diferite?</summary><p>Da, dacă textura și acoperirea sunt potrivite pentru fiecare zonă. Pentru corectarea punctuală și zona de sub ochi se aleg uneori nuanțe sau densități diferite; consultă descrierea și alege ce se simte confortabil.</p></details></section>'
)
WHERE `id` = 48
  AND (`seoTextRO` IS NULL OR `seoTextRO` NOT LIKE '%data-seo-faq="priority-v1"%');

UPDATE `categories`
SET `seoTextRU` = CONCAT(
    COALESCE(NULLIF(TRIM(`seoTextRU`), ''), '<p>Блеск для губ можно носить самостоятельно или сочетать с помадой. При выборе учитывайте оттенок, степень сияния и текстуру, а эффект увеличения объёма ищите только в описании конкретного продукта.</p>'),
    '<section class="category-seo__faq" data-seo-faq="priority-v1"><h2>Как выбрать блеск для губ</h2><details class="category-seo__faq-item"><summary>Можно ли наносить блеск поверх помады?</summary><p>Да. Прозрачный или близкий по оттенку блеск можно нанести поверх помады, чтобы изменить финиш. Для аккуратного результата распределите небольшое количество средства и не растирайте его по губам.</p></details><details class="category-seo__faq-item"><summary>Как выбрать блеск с эффектом объёма?</summary><p>Проверьте описание и состав конкретного блеска: свойства и ощущения у таких средств различаются. Если кожа губ чувствительная, сначала ознакомьтесь с предупреждениями производителя.</p></details></section>'
)
WHERE `id` = 117
  AND (`seoTextRU` IS NULL OR `seoTextRU` NOT LIKE '%data-seo-faq="priority-v1"%');

UPDATE `categories`
SET `seoTextRO` = CONCAT(
    COALESCE(NULLIF(TRIM(`seoTextRO`), ''), '<p>Luc gloss poate fi purtat singur sau împreună cu rujul. La alegere, ține cont de nuanță, intensitatea luciului și textură; efectul de volum trebuie verificat în descrierea produsului.</p>'),
    '<section class="category-seo__faq" data-seo-faq="priority-v1"><h2>Cum alegi luciul de buze</h2><details class="category-seo__faq-item"><summary>Pot aplica gloss peste ruj?</summary><p>Da. Un gloss transparent sau într-o nuanță apropiată poate fi aplicat peste ruj pentru a schimba efectul. Pentru un rezultat îngrijit, aplică o cantitate mică și distribuie produsul fără să freci buzele.</p></details><details class="category-seo__faq-item"><summary>Cum aleg un gloss cu efect de volum?</summary><p>Verifică descrierea și ingredientele produsului, deoarece formulele și senzațiile pot fi diferite. Dacă ai pielea buzelor sensibilă, citește mai întâi avertismentele producătorului.</p></details></section>'
)
WHERE `id` = 117
  AND (`seoTextRO` IS NULL OR `seoTextRO` NOT LIKE '%data-seo-faq="priority-v1"%');

UPDATE `categories`
SET `seoTextRU` = CONCAT(
    COALESCE(NULLIF(TRIM(`seoTextRU`), ''), '<p>Бронзер добавляет коже тёплый оттенок, а средство для контуринга помогает визуально подчеркнуть рельеф лица. Выбирайте текстуру и цвет с учётом тона кожи и желаемого эффекта.</p>'),
    '<section class="category-seo__faq" data-seo-faq="priority-v1"><h2>Бронзер и контуринг: в чём разница</h2><details class="category-seo__faq-item"><summary>Чем бронзер отличается от средства для контуринга?</summary><p>Бронзер обычно выбирают для тёплого оттенка и эффекта загара, а контуринг — для создания теней и визуального подчёркивания черт лица. Ориентируйтесь на оттенок и назначение в описании.</p></details><details class="category-seo__faq-item"><summary>Что выбрать: кремовую или сухую текстуру?</summary><p>Кремовые продукты обычно наносят на подготовленную кожу до пудры, а сухие — кистью поверх базового макияжа. Выбирайте формат, который удобен в вашей привычной последовательности нанесения.</p></details></section>'
)
WHERE `id` = 68
  AND (`seoTextRU` IS NULL OR `seoTextRU` NOT LIKE '%data-seo-faq="priority-v1"%');

UPDATE `categories`
SET `seoTextRO` = CONCAT(
    COALESCE(NULLIF(TRIM(`seoTextRO`), ''), '<p>Bronzerul oferă pielii o nuanță mai caldă, iar produsul de contouring ajută la evidențierea vizuală a trăsăturilor. Alege textura și culoarea în funcție de tonul pielii și efectul dorit.</p>'),
    '<section class="category-seo__faq" data-seo-faq="priority-v1"><h2>Bronzer și contouring: care este diferența?</h2><details class="category-seo__faq-item"><summary>Prin ce diferă bronzerul de produsul pentru contouring?</summary><p>Bronzerul este ales de obicei pentru un efect cald, asemănător bronzului, iar contouringul creează umbre și evidențiază vizual trăsăturile feței. Verifică nuanța și scopul produsului în descriere.</p></details><details class="category-seo__faq-item"><summary>Ce aleg: textură cremă sau pudră?</summary><p>Produsele cremoase se aplică de obicei pe pielea pregătită, înainte de pudră, iar cele pudră cu o pensulă peste machiajul de bază. Alege formatul potrivit rutinei tale de aplicare.</p></details></section>'
)
WHERE `id` = 68
  AND (`seoTextRO` IS NULL OR `seoTextRO` NOT LIKE '%data-seo-faq="priority-v1"%');

UPDATE `categories`
SET `seoTextRU` = CONCAT(
    COALESCE(NULLIF(TRIM(`seoTextRU`), ''), '<p>Пудра помогает завершить макияж и придать коже выбранный финиш. При выборе учитывайте формат — рассыпчатый или компактный, желаемую степень матирования и удобство нанесения.</p>'),
    '<section class="category-seo__faq" data-seo-faq="priority-v1"><h2>Как выбрать пудру для лица</h2><details class="category-seo__faq-item"><summary>Чем рассыпчатая пудра отличается от компактной?</summary><p>Рассыпчатую пудру часто используют для лёгкого фиксирующего слоя дома, а компактную удобно брать с собой. Смотрите описание: финиш и назначение зависят от конкретной формулы.</p></details><details class="category-seo__faq-item"><summary>Как наносить пудру, чтобы макияж выглядел естественно?</summary><p>Наберите небольшое количество продукта на подходящую кисть или спонж, стряхните излишек и распределяйте тонким слоем. При необходимости добавляйте продукт постепенно.</p></details></section>'
)
WHERE `id` = 60
  AND (`seoTextRU` IS NULL OR `seoTextRU` NOT LIKE '%data-seo-faq="priority-v1"%');

UPDATE `categories`
SET `seoTextRO` = CONCAT(
    COALESCE(NULLIF(TRIM(`seoTextRO`), ''), '<p>Pudra ajută la finisarea machiajului și oferă pielii efectul dorit. La alegere, ia în calcul formatul — liber sau compact, nivelul de matifiere și cât de comod este produsul de aplicat.</p>'),
    '<section class="category-seo__faq" data-seo-faq="priority-v1"><h2>Cum alegi pudra pentru față</h2><details class="category-seo__faq-item"><summary>Care este diferența dintre pudra liberă și cea compactă?</summary><p>Pudra liberă este folosită adesea pentru un strat lejer acasă, iar cea compactă este practică pentru geantă. Consultă descrierea: efectul și scopul depind de formula fiecărui produs.</p></details><details class="category-seo__faq-item"><summary>Cum aplic pudra pentru un aspect natural?</summary><p>Ia o cantitate mică pe o pensulă sau un burete potrivit, îndepărtează excesul și aplică un strat subțire. Dacă este nevoie, adaugă produs treptat.</p></details></section>'
)
WHERE `id` = 60
  AND (`seoTextRO` IS NULL OR `seoTextRO` NOT LIKE '%data-seo-faq="priority-v1"%');

UPDATE `categories`
SET `seoTextRU` = CONCAT(
    COALESCE(NULLIF(TRIM(`seoTextRU`), ''), '<p>Нишевая парфюмерия предлагает разные композиции и парфюмерные направления. Чтобы найти подходящий аромат, изучайте ноты и описание, а при возможности тестируйте его на коже и оценивайте спустя время.</p>'),
    '<section class="category-seo__faq" data-seo-faq="priority-v1"><h2>О нишевой парфюмерии</h2><details class="category-seo__faq-item"><summary>Чем нишевые ароматы отличаются по выбору?</summary><p>В этой категории встречаются разные парфюмерные направления, поэтому ориентируйтесь на ноты и характер композиции, а не только на название категории. Описание продукта поможет понять, какие аккорды в нём заявлены.</p></details><details class="category-seo__faq-item"><summary>Как тестировать новый аромат?</summary><p>Если есть возможность, нанесите аромат на кожу и дайте ему время раскрыться. Не оценивайте его только по первому впечатлению и избегайте одновременного тестирования многих ароматов.</p></details></section>'
)
WHERE `id` = 148
  AND (`seoTextRU` IS NULL OR `seoTextRU` NOT LIKE '%data-seo-faq="priority-v1"%');

UPDATE `categories`
SET `seoTextRO` = CONCAT(
    COALESCE(NULLIF(TRIM(`seoTextRO`), ''), '<p>Parfumeria de nișă include compoziții și familii olfactive diverse. Pentru a găsi o aromă potrivită, citește notele și descrierea, iar dacă este posibil testeaz-o pe piele și evalueaz-o după un timp.</p>'),
    '<section class="category-seo__faq" data-seo-faq="priority-v1"><h2>Despre parfumeria de nișă</h2><details class="category-seo__faq-item"><summary>Ce diferențiază aromele de nișă?</summary><p>Categoria include direcții olfactive variate, așa că alege după note și caracterul compoziției, nu doar după denumirea categoriei. Descrierea produsului te ajută să înțelegi ce acorduri sunt menționate.</p></details><details class="category-seo__faq-item"><summary>Cum testez un parfum nou?</summary><p>Dacă ai ocazia, aplică parfumul pe piele și lasă-l să se dezvolte. Nu îl evalua doar după prima impresie și evită să testezi multe arome în același timp.</p></details></section>'
)
WHERE `id` = 148
  AND (`seoTextRO` IS NULL OR `seoTextRO` NOT LIKE '%data-seo-faq="priority-v1"%');

UPDATE `categories`
SET `seoTextRU` = CONCAT(
    COALESCE(NULLIF(TRIM(`seoTextRU`), ''), '<p>Кисть выбирают под конкретную задачу: нанесение тона, пудры, румян или теней. Обращайте внимание на форму, размер и описание инструмента, а также регулярно очищайте кисти подходящим способом.</p>'),
    '<section class="category-seo__faq" data-seo-faq="priority-v1"><h2>Как выбрать кисть для макияжа</h2><details class="category-seo__faq-item"><summary>Как понять, для какого продукта предназначена кисть?</summary><p>Проверьте форму и назначение в карточке: плоские, округлые и скошенные кисти решают разные задачи. Для точного выбора сопоставьте описание инструмента с продуктом и способом нанесения, которыми вы пользуетесь.</p></details><details class="category-seo__faq-item"><summary>Как ухаживать за кистями?</summary><p>Очищайте кисти регулярно, используя подходящее средство и способ, рекомендованные производителем. После мытья удалите лишнюю влагу и дайте ворсу полностью высохнуть.</p></details></section>'
)
WHERE `id` = 21
  AND (`seoTextRU` IS NULL OR `seoTextRU` NOT LIKE '%data-seo-faq="priority-v1"%');

UPDATE `categories`
SET `seoTextRO` = CONCAT(
    COALESCE(NULLIF(TRIM(`seoTextRO`), ''), '<p>Pensula se alege în funcție de sarcină: aplicarea fondului de ten, pudrei, blushului sau fardurilor. Ține cont de formă, dimensiune și descrierea instrumentului și curăță pensulele în mod regulat.</p>'),
    '<section class="category-seo__faq" data-seo-faq="priority-v1"><h2>Cum alegi pensulele de machiaj</h2><details class="category-seo__faq-item"><summary>Cum aflu pentru ce produs este potrivită o pensulă?</summary><p>Verifică forma și destinația din fișa produsului: pensulele plate, rotunjite și oblice au utilizări diferite. Compară descrierea instrumentului cu produsul și metoda de aplicare pe care le folosești.</p></details><details class="category-seo__faq-item"><summary>Cum îngrijesc pensulele?</summary><p>Curăță pensulele regulat cu un produs și o metodă potrivite, conform recomandărilor producătorului. După spălare, îndepărtează excesul de apă și lasă perii să se usuce complet.</p></details></section>'
)
WHERE `id` = 21
  AND (`seoTextRO` IS NULL OR `seoTextRO` NOT LIKE '%data-seo-faq="priority-v1"%');
