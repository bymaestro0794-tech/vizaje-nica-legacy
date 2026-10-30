<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin-only Meilisearch maintenance endpoints.
 *
 * This controller prepares a search projection from the legacy MySQL
 * catalogue. It does not replace the existing Multisearch implementation;
 * the frontend can be switched only after the index has been verified.
 */
class Meilisearch extends BackEndController
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
        $this->load->library('meilisearch_client');
        $this->load->library('meilisearch_catalog_sync');
        $this->load->model('site_analytics_model');
    }

    /**
     * GET /cp/meilisearch
     * Admin page for running and monitoring the initial product indexing.
     */
    public function index()
    {
        $index = htmlspecialchars((string) config_item('meilisearch_index'), ENT_QUOTES, 'UTF-8');
        $searchStatsHtml = $this->renderSearchStats();
        $synonymGroups = $this->getSearchSynonymGroups();
        $synonymGroupsTextarea = htmlspecialchars(implode("\n", array_map(
            static function ($group) {
                return implode(' | ', $group);
            },
            $synonymGroups
        )), ENT_QUOTES, 'UTF-8');
        $synonymStorageReady = $this->db->table_exists('search_synonym_settings');
        $html = '<!doctype html><html lang="ru"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width,initial-scale=1">'
            . '<title>Индексация Meilisearch</title>'
            . '<style>body{font:16px Arial,sans-serif;max-width:760px;margin:40px auto;padding:0 20px;color:#222}'
            . 'button{padding:12px 18px;background:#2879b9;color:#fff;border:0;border-radius:4px;cursor:pointer}'
            . 'button:disabled{opacity:.55;cursor:wait}pre{white-space:pre-wrap;background:#f4f6f8;padding:16px;border-radius:4px;min-height:90px}'
            . '.small{color:#666;font-size:14px}.synonym-groups{box-sizing:border-box;width:100%;min-height:240px;margin:8px 0 12px;padding:10px;font:14px/1.5 monospace}'
            . '</style></head><body>'
            . '<h1>Индексация каталога</h1>'
            . '<p>Индекс: <strong>' . $index . '</strong></p>'
            . '<p class="small">Видимые товары отправляются пакетами по 500. Повторная индексация обновляет документы, не очищая индекс. Кнопка настроек применяет синонимы и правила точного поиска.</p>'
            . '<button id="start" type="button">Запустить индексацию</button>'
            . '<button id="apply-settings" type="button" style="margin-left:8px;background:#555">Применить настройки поиска</button>'
            . '<pre id="status">Готово к запуску.</pre>'
            . '<section style="margin-top:36px"><h2>Синонимы поиска</h2>'
            . '<p class="small">Одна группа на строку. Разделяй варианты символом <code>|</code>: запрос любого варианта будет искать остальные. Например: <code>помада | lipstick | ruj</code>.</p>'
            . (!$synonymStorageReady
                ? '<p class="small">Чтобы сохранять изменения из админки, сначала выполни SQL-патч <code>2026-10-30-create-search-synonym-settings.sql</code>.</p>'
                : '')
            . '<textarea id="synonym-groups" class="synonym-groups" spellcheck="false">' . $synonymGroupsTextarea . '</textarea>'
            . '<button id="save-synonyms" type="button" style="background:#555"' . (!$synonymStorageReady ? ' disabled' : '') . '>Сохранить группы синонимов</button></section>'
            . $searchStatsHtml
            . '<script>
                (function () {
                    var button = document.getElementById("start");
                    var settingsButton = document.getElementById("apply-settings");
                    var synonymsButton = document.getElementById("save-synonyms");
                    var synonymsInput = document.getElementById("synonym-groups");
                    var output = document.getElementById("status");
                    function report(message) { output.textContent += "\\n" + message; }
                    async function waitForTask(taskUid, label) {
                        if (!taskUid) return;
                        var deadline = Date.now() + 300000;
                        while (Date.now() < deadline) {
                            var response = await fetch("/cp/meilisearch/task_status", {
                                method: "POST",
                                credentials: "same-origin",
                                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                                body: new URLSearchParams({ task_uid: String(taskUid) })
                            });
                            if (response.redirected) {
                                throw new Error("Сессия администратора истекла. Войди в админку и открой эту страницу снова.");
                            }
                            var result = await response.json();
                            if (!response.ok || !result.ok) {
                                throw new Error(result.message || ("HTTP " + response.status));
                            }
                            if (result.status === "succeeded") {
                                report(label + ": задача " + taskUid + " завершена.");
                                return;
                            }
                            if (result.status === "failed" || result.status === "canceled") {
                                throw new Error(label + ": " + (result.error || ("задача " + result.status)));
                            }
                            await new Promise(function (resolve) { setTimeout(resolve, 1000); });
                        }
                        throw new Error(label + ": Meilisearch не завершил задачу за 5 минут. Проверь статус позже.");
                    }
                    synonymsButton.addEventListener("click", async function () {
                        synonymsButton.disabled = true;
                        output.textContent = "Сохраняем группы синонимов…";
                        try {
                            var response = await fetch("/cp/meilisearch/synonyms", {
                                method: "POST",
                                credentials: "same-origin",
                                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                                body: new URLSearchParams({ groups: synonymsInput.value })
                            });
                            if (response.redirected) {
                                throw new Error("Сессия администратора истекла. Войди в админку и открой эту страницу снова.");
                            }
                            var result = await response.json();
                            if (!response.ok || !result.ok) {
                                throw new Error(result.message || ("HTTP " + response.status));
                            }
                            report("Сохранено групп: " + result.groups_count + ". Теперь нажми «Применить настройки поиска».");
                        } catch (error) {
                            report("Ошибка: " + error.message);
                        } finally {
                            synonymsButton.disabled = false;
                        }
                    });
                    settingsButton.addEventListener("click", async function () {
                        button.disabled = true;
                        settingsButton.disabled = true;
                        output.textContent = "Применяем настройки поиска…";
                        try {
                            var response = await fetch("/cp/meilisearch/settings", {
                                method: "POST",
                                credentials: "same-origin",
                                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                                body: new URLSearchParams({})
                            });
                            if (response.redirected) {
                                throw new Error("Сессия администратора истекла. Войди в админку и открой эту страницу снова.");
                            }
                            var result = await response.json();
                            if (!response.ok || !result.ok) {
                                throw new Error(result.message || ("HTTP " + response.status));
                            }
                            if (result.task_uid) {
                                report("Настройки приняты. Ожидаю применение…");
                                await waitForTask(result.task_uid, "Настройки поиска");
                            } else {
                                report("Настройки отправлены в Meilisearch.");
                            }
                        } catch (error) {
                            report("Ошибка: " + error.message);
                        } finally {
                            button.disabled = false;
                            settingsButton.disabled = false;
                        }
                    });
                    button.addEventListener("click", async function () {
                        var afterId = 0;
                        var total = 0;
                        var lastTaskUid = null;
                        button.disabled = true;
                        settingsButton.disabled = true;
                        output.textContent = "Индексация запущена…";
                        try {
                            while (true) {
                                var response = await fetch("/cp/meilisearch/reindex", {
                                    method: "POST",
                                    credentials: "same-origin",
                                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                                    body: new URLSearchParams({ after_id: String(afterId), limit: "500" })
                                });
                                if (response.redirected) {
                                    throw new Error("Сессия администратора истекла. Войди в админку и открой эту страницу снова.");
                                }
                                var result = await response.json();
                                if (!response.ok || !result.ok) {
                                    throw new Error(result.message || ("HTTP " + response.status));
                                }
                                total += result.indexed;
                                report("Отправлено товаров: " + total + "; последний ID: " + result.next_after_id);
                                if (result.task && (result.task.taskUid || result.task.uid)) {
                                    lastTaskUid = result.task.taskUid || result.task.uid;
                                }
                                if (result.settings_task && (result.settings_task.taskUid || result.settings_task.uid)) {
                                    lastTaskUid = result.settings_task.taskUid || result.settings_task.uid;
                                }
                                afterId = result.next_after_id;
                                if (!result.has_more) {
                                    report("Все пакеты отправлены. Проверяю завершение очереди Meilisearch…");
                                    await waitForTask(lastTaskUid, "Индексация");
                                    report("Готово. Индексация завершена; отправлено товаров: " + total);
                                    break;
                                }
                            }
                        } catch (error) {
                            report("Ошибка: " + error.message);
                        } finally {
                            button.disabled = false;
                            settingsButton.disabled = false;
                        }
                    });
                }());
            </script></body></html>';

        return $this->output
            ->set_content_type('text/html', 'utf-8')
            ->set_output($html);
    }

    private function renderSearchStats()
    {
        $statsTableReady = $this->db->table_exists('search_query_stats')
            && $this->db->field_exists('zero_result_count', 'search_query_stats')
            && $this->db->field_exists('result_click_count', 'search_query_stats');
        $conversionStatsReady = $statsTableReady
            && $this->db->field_exists('result_count_total', 'search_query_stats')
            && $this->db->field_exists('result_count_samples', 'search_query_stats')
            && $this->db->field_exists('add_to_cart_count', 'search_query_stats');
        $rows = $statsTableReady
            ? $this->site_analytics_model->get_search_query_stats(30, 50)
            : array();
        $html = '<section style="margin-top:36px"><h2>Поисковые запросы за 30 дней</h2>'
            . '<p class="small">Учитываются только при согласии на аналитику. Фразы агрегируются без ID пользователя и товара. «Клики на 100 поисков» могут быть выше 100, если по запросу нажимали несколько товаров.</p>';

        if ($statsTableReady && !$conversionStatsReady) {
            $html .= '<p class="small">Для среднего числа результатов и добавлений в корзину примените SQL-патч <code>2026-10-30-expand-search-query-conversion-stats.sql</code>.</p>';
        }

        if (empty($rows)) {
            $emptyMessage = $statsTableReady
                ? 'Данных пока нет. Они появятся после поисков с включённым согласием на аналитику.'
                : 'Для счётчиков «без результата» и «клики по товарам» примените SQL-патч <code>2026-10-28-expand-search-query-stats.sql</code>.';
            return $html . '<p class="small">' . $emptyMessage . '</p></section>';
        }

        $html .= '<div style="overflow-x:auto"><table style="width:100%;border-collapse:collapse;font-size:13px">'
            . '<thead><tr><th style="text-align:left">Запрос</th><th>Поиски</th><th>Без результата</th><th>Без результата, %</th><th>Среднее число результатов</th><th>Клики по товарам</th><th>Добавления в корзину</th><th>Кликов на 100 поисков</th>'
            . ($conversionStatsReady ? '<th>Добавлений в корзину на 100 поисков</th>' : '')
            . '</tr></thead><tbody>';

        foreach ($rows as $row) {
            $query = htmlspecialchars((string) ($row['query_text'] ?? ''), ENT_QUOTES, 'UTF-8');
            $searches = max(0, (int) ($row['searches'] ?? 0));
            $zeroResults = max(0, (int) ($row['zero_results'] ?? 0));
            $clicks = max(0, (int) ($row['result_clicks'] ?? 0));
            $averageResults = max(0, (float) ($row['average_result_count'] ?? 0));
            $addToCarts = max(0, (int) ($row['search_add_to_carts'] ?? 0));
            $zeroResultRate = $searches > 0 ? round(($zeroResults / $searches) * 100, 1) : 0;
            $clicksPerHundred = $searches > 0 ? round(($clicks / $searches) * 100, 1) : 0;
            $addToCartPerHundred = $searches > 0 ? round(($addToCarts / $searches) * 100, 1) : 0;
            $html .= '<tr><td style="padding:7px 8px;border-top:1px solid #ddd">' . $query . '</td>'
                . '<td style="text-align:center;border-top:1px solid #ddd">' . number_format($searches, 0, '.', ' ') . '</td>'
                . '<td style="text-align:center;border-top:1px solid #ddd">' . number_format($zeroResults, 0, '.', ' ') . '</td>'
                . '<td style="text-align:center;border-top:1px solid #ddd">' . number_format($zeroResultRate, 1, '.', ' ') . '%</td>'
                . '<td style="text-align:center;border-top:1px solid #ddd">' . number_format($averageResults, 1, '.', ' ') . '</td>'
                . '<td style="text-align:center;border-top:1px solid #ddd">' . number_format($clicks, 0, '.', ' ') . '</td>'
                . '<td style="text-align:center;border-top:1px solid #ddd">' . number_format($addToCarts, 0, '.', ' ') . '</td>'
                . '<td style="text-align:center;border-top:1px solid #ddd">' . number_format($clicksPerHundred, 1, '.', ' ') . '</td>'
                . ($conversionStatsReady
                    ? '<td style="text-align:center;border-top:1px solid #ddd">' . number_format($addToCartPerHundred, 1, '.', ' ') . '</td>'
                    : '')
                . '</tr>';
        }

        return $html . '</tbody></table></div></section>';
    }

    /**
     * GET /cp/meilisearch/health
     */
    public function health()
    {
        $health = $this->meilisearch_client->health();

        return $this->json(array(
            'configured' => $this->meilisearch_client->isConfigured(),
            'ok' => $health !== false,
            'health' => $health,
        ), $health === false ? 502 : 200);
    }

    /**
     * POST /cp/meilisearch/settings
     * Apply search relevance settings without re-uploading the product catalog.
     */
    public function settings()
    {
        check_if_POST();

        if (!$this->meilisearch_client->isConfigured()) {
            return $this->json(array(
                'ok' => false,
                'message' => 'MEILISEARCH_URL is not configured.',
            ), 503);
        }

        $index = (string) config_item('meilisearch_index');
        if (!$this->ensureIndex($index)) {
            return $this->json(array(
                'ok' => false,
                'message' => 'Не удалось подготовить индекс в Meilisearch: ' . $this->meilisearch_client->getLastError(),
            ), 502);
        }

        $task = $this->meilisearch_client->updateSettings($index, $this->indexSettings());
        if ($task === false) {
            return $this->json(array(
                'ok' => false,
                'message' => 'Meilisearch не принял настройки: ' . $this->meilisearch_client->getLastError(),
            ), 502);
        }

        return $this->json(array(
            'ok' => true,
            'index' => $index,
            'task_uid' => $task['taskUid'] ?? ($task['uid'] ?? null),
        ));
    }

    /**
     * POST /cp/meilisearch/task_status
     * Read the state of an asynchronous Meilisearch task without exposing API keys.
     */
    public function task_status()
    {
        check_if_POST();

        $taskUid = $this->input->post('task_uid', true);
        if (!is_scalar($taskUid) || !preg_match('/^[0-9]{1,20}$/', (string) $taskUid)) {
            return $this->json(array(
                'ok' => false,
                'message' => 'Некорректный идентификатор задачи Meilisearch.',
            ), 400);
        }

        $task = $this->meilisearch_client->getTask($taskUid);
        if ($task === false) {
            return $this->json(array(
                'ok' => false,
                'message' => 'Не удалось получить статус задачи: ' . $this->meilisearch_client->getLastError(),
            ), 502);
        }

        $errorMessage = isset($task['error']['message'])
            ? substr(trim((string) $task['error']['message']), 0, 300)
            : '';

        return $this->json(array(
            'ok' => true,
            'task_uid' => $task['uid'] ?? (string) $taskUid,
            'status' => (string) ($task['status'] ?? 'unknown'),
            'error' => $errorMessage,
        ));
    }

    /**
     * POST /cp/meilisearch/synonyms
     * Save editable synonym groups for the next settings application.
     */
    public function synonyms()
    {
        check_if_POST();

        if (!$this->db->table_exists('search_synonym_settings')) {
            return $this->json(array(
                'ok' => false,
                'message' => 'Сначала создай таблицу search_synonym_settings SQL-патчем 2026-10-30-create-search-synonym-settings.sql.',
            ), 503);
        }

        $rawGroups = $this->input->post('groups', false);
        if (!is_string($rawGroups) || strlen($rawGroups) > 65536) {
            return $this->json(array(
                'ok' => false,
                'message' => 'Список слишком большой или имеет неверный формат (максимум 64 КБ).',
            ), 400);
        }

        $groups = array();
        foreach (preg_split('/\r\n|\r|\n/', $rawGroups) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $terms = array();
            foreach (explode('|', $line) as $term) {
                $term = trim($term);
                if ($term === '') {
                    continue;
                }

                $length = function_exists('mb_strlen') ? mb_strlen($term, 'UTF-8') : strlen($term);
                if ($length < 2 || $length > 80 || !preg_match('/^[\p{L}\p{N}][\p{L}\p{N}\s.\'’&-]*$/u', $term)) {
                    return $this->json(array(
                        'ok' => false,
                        'message' => 'Недопустимый вариант синонима: ' . $term . '. Используй 2–80 букв/цифр и пробелы; допустимы . ’ & -.',
                    ), 400);
                }

                $normalized = function_exists('mb_strtolower') ? mb_strtolower($term, 'UTF-8') : strtolower($term);
                $terms[$normalized] = $term;
            }

            if (count($terms) < 2) {
                return $this->json(array(
                    'ok' => false,
                    'message' => 'В каждой непустой строке должно быть минимум два разных варианта, разделённых символом |.',
                ), 400);
            }
            if (count($terms) > 12) {
                return $this->json(array(
                    'ok' => false,
                    'message' => 'В одной группе можно указать не больше 12 вариантов.',
                ), 400);
            }

            $groups[] = array_values($terms);
            if (count($groups) > 200) {
                return $this->json(array(
                    'ok' => false,
                    'message' => 'Можно сохранить не больше 200 групп синонимов.',
                ), 400);
            }
        }

        if (empty($groups)) {
            return $this->json(array(
                'ok' => false,
                'message' => 'Добавь хотя бы одну группу синонимов.',
            ), 400);
        }

        $saved = $this->db->replace('search_synonym_settings', array(
            'id' => 1,
            'groups_json' => json_encode($groups, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ));

        if (!$saved) {
            return $this->json(array(
                'ok' => false,
                'message' => 'Не удалось сохранить группы синонимов в базе данных.',
            ), 500);
        }

        return $this->json(array(
            'ok' => true,
            'groups_count' => count($groups),
        ));
    }

    /**
     * POST /cp/meilisearch/reindex
     *
     * Incremental/idempotent batch reindex. The caller may send:
     * - after_id: last product id already processed;
     * - limit: batch size, capped at 1000.
     *
     * The index is never cleared here. This makes the first rollout safe and
     * avoids touching an index that could be shared by another service.
     */
    public function reindex()
    {
        check_if_POST();

        if (!$this->meilisearch_client->isConfigured()) {
            return $this->json(array(
                'ok' => false,
                'message' => 'MEILISEARCH_URL is not configured.',
            ), 503);
        }

        if (!$this->ensureIndex((string) config_item('meilisearch_index'))) {
            return $this->json(array(
                'ok' => false,
                'message' => 'Не удалось подготовить индекс в Meilisearch: ' . $this->meilisearch_client->getLastError(),
            ), 502);
        }

        $afterId = max(0, (int) $this->input->post('after_id', true));
        $limit = (int) $this->input->post('limit', true);
        $limit = $limit > 0 ? min($limit, 1000) : 500;

        $rows = $this->meilisearch_catalog_sync->getProductRows($afterId, $limit);
        $documents = array();

        foreach ($rows as $row) {
            $documents[] = $this->meilisearch_catalog_sync->makeDocument($row);
        }

        if (!empty($documents)) {
            $task = $this->meilisearch_client->updateDocuments(
                (string) config_item('meilisearch_index'),
                $documents,
                'id'
            );

            if ($task === false) {
                return $this->json(array(
                    'ok' => false,
                    'message' => 'Meilisearch did not accept the batch: ' . $this->meilisearch_client->getLastError(),
                ), 502);
            }

            $settingsTask = null;
            if ($afterId === 0) {
                $settingsTask = $this->meilisearch_client->updateSettings(
                    (string) config_item('meilisearch_index'),
                    $this->indexSettings()
                );

                if ($settingsTask === false) {
                    return $this->json(array(
                        'ok' => false,
                        'message' => 'Documents were accepted, but index settings were not accepted.',
                        'task' => $task,
                    ), 502);
                }
            }
        } else {
            $task = null;
            $settingsTask = null;
        }

        $lastId = $afterId;
        if (!empty($rows)) {
            $lastRow = end($rows);
            $lastId = (int) $lastRow->id;
        }

        return $this->json(array(
            'ok' => true,
            'index' => (string) config_item('meilisearch_index'),
            'indexed' => count($documents),
            'after_id' => $afterId,
            'next_after_id' => $lastId,
            'has_more' => count($rows) === $limit,
            'task' => $task,
            'settings_task' => $settingsTask,
        ));
    }

    private function indexSettings()
    {
        $synonyms = $this->buildSynonymMap($this->getSearchSynonymGroups());

        return array(
            'searchableAttributes' => array(
                // Exact identifiers should outrank coincidental matches in names.
                'SKU',
                'barcode',
                'variant_skus',
                'variant_barcodes',
                'titleRU',
                'titleRO',
                'titleEN',
                'brand',
                'categoryRU',
                'categoryRO',
                'category_pathRU',
                'category_pathRO',
                'search_text',
                'GUID',
            ),
            'filterableAttributes' => array(
                'brand_id',
                'category_id',
                'sex',
                'price',
                'current_price',
                'discount_price',
                'has_discount',
                'on_stock',
                'sale_percent',
                'is_new',
                'has_variants',
            ),
            'sortableAttributes' => array(
                'price',
                'current_price',
                'discount_price',
                'sale_percent',
                'sale_mdl',
                'stock',
                'best_selling',
                'has_stock',
                'on_stock',
                'is_new',
                'id',
            ),
            'pagination' => array('maxTotalHits' => 10000),
            'synonyms' => $synonyms,
            'typoTolerance' => array(
                'enabled' => true,
                'minWordSizeForTypos' => array('oneTypo' => 5, 'twoTypos' => 9),
                'disableOnAttributes' => array('SKU', 'variant_skus', 'variant_barcodes', 'barcode', 'GUID'),
            ),
        );
    }

    private function defaultSearchSynonymGroups()
    {
        return array(
            array('тушь', 'тушь для ресниц', 'mascara', 'rimel'),
            array('помада', 'губная помада', 'ruj', 'lipstick'),
            array('clarins', 'кларинс', 'кларанс', 'кларенс'),
            array('double serum', 'двойная сыворотка', 'добле серум'),
            array('double', 'добле'),
            array('serum', 'серум'),
            array('maybelline', 'maybelline new york', 'мейбеллин', 'мейбелин', 'мэйбелин'),
            array('chanel', 'шанель', 'шанел', 'шанэль'),
            array('тональный крем', 'foundation', 'fond de ten'),
            array('пудра', 'powder', 'pudră'),
            array('консилер', 'concealer', 'corector'),
            array('тени', 'тени для век', 'eyeshadow', 'fard de pleoape'),
            array('блеск для губ', 'lip gloss', 'gloss de buze'),
            array('крем для лица', 'face cream', 'cremă de față'),
            array('уход за кожей', 'skincare', 'îngrijirea pielii'),
            array('духи', 'парфюм', 'perfume', 'parfum', 'parfumuri'),
            array('парфюмерная вода', 'eau de parfum', 'edp', 'apă de parfum'),
            array('шампунь', 'shampoo', 'șampon'),
            array('крем для рук', 'hand cream', 'cremă de mâini'),
            array('румяна', 'blush'),
            array('карандаш для глаз', 'eye pencil', 'creion de ochi'),
            array('подводка для глаз', 'eyeliner', 'tuș de ochi')
        );
    }

    private function getSearchSynonymGroups()
    {
        if (!$this->db->table_exists('search_synonym_settings')) {
            return $this->defaultSearchSynonymGroups();
        }

        $row = $this->db->select('groups_json')
            ->from('search_synonym_settings')
            ->where('id', 1)
            ->limit(1)
            ->get()
            ->row_array();

        if (empty($row['groups_json'])) {
            return $this->defaultSearchSynonymGroups();
        }

        $groups = json_decode($row['groups_json'], true);
        if (!is_array($groups)) {
            return $this->defaultSearchSynonymGroups();
        }

        $validGroups = array();
        foreach ($groups as $group) {
            if (!is_array($group)) {
                continue;
            }
            $terms = array();
            foreach ($group as $term) {
                if (!is_string($term)) {
                    continue;
                }
                $term = trim($term);
                if ($term !== '') {
                    $terms[$term] = $term;
                }
            }
            if (count($terms) >= 2) {
                $validGroups[] = array_values($terms);
            }
        }

        return empty($validGroups) ? $this->defaultSearchSynonymGroups() : $validGroups;
    }

    private function buildSynonymMap(array $groups)
    {
        $alternativesByTerm = array();
        foreach ($groups as $group) {
            $terms = array_values(array_unique(array_filter(array_map('trim', $group))));
            foreach ($terms as $term) {
                if (!isset($alternativesByTerm[$term])) {
                    $alternativesByTerm[$term] = array();
                }
                foreach ($terms as $alternative) {
                    if ($alternative !== $term) {
                        $alternativesByTerm[$term][$alternative] = $alternative;
                    }
                }
            }
        }

        foreach ($alternativesByTerm as $term => $alternatives) {
            $alternativesByTerm[$term] = array_values($alternatives);
        }

        return $alternativesByTerm;
    }

    private function ensureIndex($index)
    {
        $indexInfo = $this->meilisearch_client->getIndex($index);
        if ($indexInfo !== false) {
            return true;
        }

        if (strpos($this->meilisearch_client->getLastError(), 'HTTP 404') === false) {
            return false;
        }

        $created = $this->meilisearch_client->createIndex($index, 'id');
        if ($created === false) {
            return false;
        }

        $taskUid = isset($created['taskUid']) ? $created['taskUid'] : (isset($created['uid']) ? $created['uid'] : null);
        if ($taskUid === null) {
            return true;
        }

        for ($attempt = 0; $attempt < 30; $attempt++) {
            $task = $this->meilisearch_client->getTask($taskUid);
            if ($task === false) {
                return false;
            }

            if (isset($task['status']) && $task['status'] === 'succeeded') {
                return true;
            }
            if (isset($task['status']) && $task['status'] === 'failed') {
                return false;
            }

            usleep(200000);
        }

        return $this->meilisearch_client->getIndex($index) !== false;
    }

    private function json(array $payload, $status = 200)
    {
        return $this->output
            ->set_status_header((int) $status)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
