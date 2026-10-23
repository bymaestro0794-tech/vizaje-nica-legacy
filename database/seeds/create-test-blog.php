<?php
/** Local preview only. Re-running preserves the article and editor changes. */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$projectRoot = dirname(__DIR__, 2);
define('BASEPATH', $projectRoot . '/system/');
define('ENVIRONMENT', 'development');
date_default_timezone_set('Europe/Chisinau');
$db = array();
require $projectRoot . '/application/config/database.php';
$connection = $db[$active_group];
if (!in_array($connection['hostname'], array('localhost', '127.0.0.1'), true)
    || $connection['database'] !== 'vizaje_nica_local') {
    echo "This seed only supports the local development database.\n";
    exit(1);
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $link = new mysqli($connection['hostname'], $connection['username'], $connection['password'],
        $connection['database'], isset($connection['port']) ? (int) $connection['port'] : 3306);
    $link->set_charset('utf8mb4');
    $slugRU = 'test-blog-osenniy-byuti-ritual';
    $slugRO = 'test-blog-ritual-de-frumusete';
    $existing = $link->prepare('SELECT id FROM articles WHERE uriRU = ? LIMIT 1');
    $existing->bind_param('s', $slugRU);
    $existing->execute();
    $existingRow = $existing->get_result()->fetch_assoc();
    if ($existingRow) {
        echo 'Test article already exists. ID: ' . $existingRow['id'] . "\n";
        echo 'http://vizaje-nica.local/ru/blog/' . $slugRU . "\n";
        exit;
    }

    foreach (array('isBlog', 'tagsRU', 'tagsRO') as $field) {
        $column = $link->query("SHOW COLUMNS FROM articles LIKE '" . $link->real_escape_string($field) . "'");
        if ($column->num_rows === 0) throw new RuntimeException('Apply the blog SQL patch first.');
    }
    $cover = '9e49a7ff2457537343c1e348117b1d40.png';
    if (!is_file($projectRoot . '/public/articles/' . $cover)) {
        throw new RuntimeException('The local preview cover is missing.');
    }
    $products = $link->query('SELECT id FROM products WHERE id IN (638,2285,5864)')->fetch_all(MYSQLI_ASSOC);
    if (count($products) !== 3) throw new RuntimeException('The preview products are missing.');

    $textRU = <<<'HTML'
<p><strong>Это тестовая статья новой страницы блога.</strong> Здесь можно проверить фотографии, заголовки, товарные вставки, лайк, кнопку «Поделиться» и полосу чтения под шапкой сайта.</p>
<p>Осенью приятно собирать небольшие ритуалы: выбрать любимую текстуру, аккуратно разложить косметику и оставить несколько минут для себя. В этой подборке мы смотрим на три детали, из которых складывается привычная косметичка.</p>
<h2>Спокойный вечер</h2>
<p>Первый раздел — о средстве для снятия макияжа. Товарная карточка ниже связана с настоящим товаром каталога: фотография, название, цена и ссылка берутся из базы, а не вводятся вручную в статье.</p>
<p>Такой блок удобно добавлять после абзаца с описанием продукта. Читатель может открыть карточку товара и узнать больше на привычной странице каталога.</p>
[[ARTICLE_PRODUCT:638]]
<h2>Маленькие инструменты</h2>
<p>Кисти и аксессуары — ещё одна часть косметички. Для этого блока мы специально выбрали кисточку, которой сейчас нет в наличии: ARTDECO Brow Brush for Duo Box.</p>
<p><strong>Проверка отсутствующего товара:</strong> исходная кисть остаётся в статье с пометкой «Нет в наличии», а ниже показываются три доступные похожие кисти. Остатки товаров для теста не менялись.</p>
[[ARTICLE_PRODUCT:2285]]
<h2>Финальный штрих</h2>
<p>Последний блок — бальзам для губ. Он нужен здесь ещё и для проверки нескольких товаров в одной статье: первая карточка не должна мешать появлению второй и третьей.</p>
<p>Обратите внимание на мобильную версию: фотография и текст должны оставаться читаемыми, ссылки — удобными для нажатия, а цена — видимой без горизонтальной прокрутки.</p>
[[ARTICLE_PRODUCT:5864]]
<h2>Что проверить дальше</h2>
<ul><li>Нажмите на сердце: счётчик должен увеличиться, а повторное нажатие — отменить лайк.</li><li>Нажмите «Поделиться»: откроется системное меню или скопируется ссылка.</li><li>Прокрутите статью: полоса под шапкой должна постепенно заполняться.</li><li>Откройте один из товаров и вернитесь к статье.</li></ul>
<p>У недоступного товара показаны похожие продукты, а ниже статьи — другие публикации блога. Эта запись создана для локальной проверки оформления и поведения страницы.</p>
HTML;
    $textRO = <<<'HTML'
<p><strong>Acesta este un articol de test pentru noua pagină a blogului.</strong> Aici poți verifica fotografiile, titlurile, produsele integrate, aprecierea, distribuirea și bara de lectură de sub antet.</p>
<p>Toamna este un moment potrivit pentru mici ritualuri: alegem o textură preferată, aranjăm produsele și păstrăm câteva minute pentru noi. În această selecție privim trei detalii dintr-o trusă de frumusețe.</p>
<h2>O seară liniștită</h2>
<p>Prima secțiune prezintă un demachiant. Cardul de mai jos este legat de un produs real: fotografia, denumirea, prețul și linkul sunt preluate din baza de date.</p>
<p>Poți insera un astfel de card după paragraful despre produs. Cititorul poate deschide pagina din catalog pentru mai multe detalii.</p>
[[ARTICLE_PRODUCT:638]]
<h2>Instrumente mici</h2>
<p>Pentru acest bloc am ales intenționat o pensulă care nu este în stoc: ARTDECO Brow Brush for Duo Box.</p>
<p><strong>Verificarea produsului indisponibil:</strong> pensula originală rămâne în articol cu eticheta „Indisponibil momentan”, iar dedesubt apar trei pensule similare disponibile. Stocurile nu au fost modificate pentru acest test.</p>
[[ARTICLE_PRODUCT:2285]]
<h2>Ultimul detaliu</h2>
<p>Ultimul bloc prezintă un balsam de buze. Verificăm și faptul că toate cele trei produse apar în ordine, fără ca primul card să împiedice afișarea celorlalte.</p>
<p>Pe telefon, fotografia și textul trebuie să rămână lizibile, linkurile ușor de apăsat, iar prețul vizibil fără derulare orizontală.</p>
[[ARTICLE_PRODUCT:5864]]
<h2>Ce mai poți verifica</h2>
<ul><li>Apasă inima pentru a adăuga o apreciere și încă o dată pentru a o anula.</li><li>Apasă „Distribuie” pentru meniul sistemului sau copierea linkului.</li><li>Derulează articolul și urmărește bara de lectură.</li><li>Deschide un produs și revino la articol.</li></ul>
<p>Produsul indisponibil are recomandări similare, iar mai jos apar alte articole din blog. Această publicație a fost creată în baza locală pentru verificarea paginii.</p>
HTML;

    $article = array(
        'date' => '2026-10-09', 'uriRU' => $slugRU, 'uriRO' => $slugRO, 'uriEN' => '',
        'titleRU' => 'Тестовый блог: осенний бьюти-ритуал в трёх деталях',
        'titleRO' => 'Blog de test: ritualul de frumusețe de toamnă în trei detalii', 'titleEN' => '',
        'descRU' => 'Средство для снятия макияжа, кисть и бальзам для губ — проверяем новую страницу статьи и рекомендации.',
        'descRO' => 'Demachiant, pensulă și balsam de buze — verificăm noua pagină a articolului și recomandările.',
        'descEN' => '', 'textRU' => $textRU, 'textRO' => $textRO, 'textEN' => '',
        'seoTitleRU' => 'Тестовая статья блога | Vizaje-Nica', 'seoTitleRO' => 'Articol de test | Vizaje-Nica',
        'seoDescRU' => 'Локальная тестовая статья с товарными блоками, лайками, прогрессом чтения и тремя вариантами для отсутствующего товара.',
        'seoDescRO' => 'Articol local de test cu produse, aprecieri, progres de lectură și trei alternative pentru produsul indisponibil.',
        'tagsRU' => 'тест, уход, косметичка', 'tagsRO' => 'test, îngrijire, frumusețe',
        'menuON' => 0, 'isShown' => 1, 'isBlog' => 1, 'sorder' => 1, 'img' => $cover,
    );
    $fields = array_keys($article);
    $values = array_values($article);
    $sql = 'INSERT INTO articles (`' . implode('`,`', $fields) . '`) VALUES ('
        . implode(',', array_fill(0, count($fields), '?')) . ')';
    $statement = $link->prepare($sql);
    $types = str_repeat('s', count($values));
    $statement->bind_param($types, ...$values);
    $statement->execute();
    $articleId = $link->insert_id;
    echo 'Created test article. ID: ' . $articleId . "\n";
    echo 'http://vizaje-nica.local/ru/blog/' . $slugRU . "\n";
    echo 'http://vizaje-nica.local/ro/blog/' . $slugRO . "\n";
    echo 'http://vizaje-nica.local/cp/articles/item/' . $articleId . "\n";
} catch (Throwable $error) {
    echo 'Test article could not be created: ' . $error->getMessage() . "\n";
    exit(1);
}
