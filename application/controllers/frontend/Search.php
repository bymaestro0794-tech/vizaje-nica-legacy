<?php
defined('BASEPATH') or exit('No direct script access allowed');

use JasonGrimes\Paginator;

class Search extends FrontEndController
{
    private $page_id;
    private $per_page;

    public function __construct()
    {
        parent:: __construct();
        $this->page_id = 21;
        $this->per_page = 32;
        $this->load->model('products_model');
        $this->load->model('brands_model');
        $this->load->model('search_model');
        $this->load->model('categories_model');
        $this->load->model('analytics_model');
        $this->load->model('site_analytics_model');
        $this->load->library('spellcorrector');
        $this->load->library('meilisearch_client');

    }

    private function _load_main_page($page)
    {
        $this->data['page_title'] = (!empty($page->title)) ? $page->title : "";
        $this->data['page_name'] = $page->title;
        $this->data['text_for_layout'] = (!empty($page->text)) ? $page->text : $page->title;
        $this->data['keywords_for_layout'] = (!empty($page->seo_keywords)) ? $page->seo_keywords : "";
        $this->data['description_for_layout'] = (!empty($page->seo_desc)) ? $page->seo_desc : "";
        $this->data['otitle'] = $page->seo_title;
        $this->data['breadcrumbs'] = $this->breadcrumbs;
        $this->data['class_page'] = '_webp';
    }

    private function loadOGImgData($page = false)
    {
        if (empty($page)) throw_on_404();

        if (!empty($page->img)) {
            $this->data['og_img'] = newthumbs($page->img, 'menu', 500, 300, 'og500x300x1', 1);
            $this->data['og_img_width'] = 500;
            $this->data['og_img_height'] = 300;
        } else {
            $this->data['og_img'] = newthumbs('og_img.png', 'i', 214, 51, 'og214x51x1', 1);
            $this->data['og_img_width'] = 214;
            $this->data['og_img_height'] = 51;
        }
    }

    public function index()
    {
        $page = $this->menu_model->get_page_data_by_id($this->clang, $this->page_id);
        if (empty($page)) throw_on_404();

        $searchInput = $this->input->get('search', true);
        $search = is_scalar($searchInput)
            ? trim(preg_replace('/\s+/u', ' ', (string) $searchInput))
            : '';
        $_GET['search'] = $search;
        $pageInput = $_GET['page'] ?? 1;
        $paginationNr = is_scalar($pageInput)
            ? filter_var($pageInput, FILTER_VALIDATE_INT)
            : false;
        $paginationNr = $paginationNr !== false && $paginationNr > 0 ? (int) $paginationNr : 1;
        $start = ($paginationNr - 1) * $this->per_page;
        $filters = $this->getSearchFilterState();
        $hasActiveFilters = $this->hasActiveSearchFilters($filters);
        $isFilterCountRequest = isset($_GET['filter_count'])
            && is_scalar($_GET['filter_count'])
            && (string) $_GET['filter_count'] === '1';
        $sortInput = $this->input->get('sort', true);
        $sort = is_scalar($sortInput) && in_array((string) $sortInput, array('1', '2', '3', '4', '5'), true)
            ? (string) $sortInput
            : '';
        $trackSearchQuery = !$isFilterCountRequest
            && $search !== ''
            && $paginationNr === 1
            && $sort === ''
            && !$hasActiveFilters;

        if ($trackSearchQuery && $this->hasSearchAnalyticsConsent()) {
            $this->site_analytics_model->record_search_activity($search, 'search');
        }

        if ($trackSearchQuery) {
            $navigationTarget = $this->getExactSearchNavigationTarget($search);
            if ($navigationTarget !== false) {
                redirect($navigationTarget, 'location', 302);
                return;
            }
        }

        $products = array();
        $count = 0;
        $facets = array();
        $searchSource = 'legacy';
        $searchControlsAvailable = false;
        $this->data['search_corrected_query'] = '';

        $canUseMeilisearch = $search !== ''
            && $this->meilisearch_client->isConfigured()
            && empty($_SESSION['isb2b']);

        if ($isFilterCountRequest) {
            $countResult = $canUseMeilisearch
                ? $this->searchFullPageWithMeilisearch($search, 1, $sort, $filters)
                : false;
            if ($countResult === false) {
                $this->output
                    ->set_status_header(503)
                    ->set_content_type('application/json', 'utf-8')
                    ->set_output(json_encode(array('ok' => false, 'count' => 0)));
                return;
            }

            $countResponse = (array) $countResult['response'];
            $filteredCount = (int) ($countResponse['totalHits'] ?? ($countResponse['estimatedTotalHits'] ?? 0));
            $this->output
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode(array('ok' => true, 'count' => $filteredCount)));
            return;
        }

        $meilisearchResult = $canUseMeilisearch
            ? $this->searchFullPageWithMeilisearch($search, $paginationNr, $sort, $filters)
            : false;

        if ($meilisearchResult !== false) {
            $response = $meilisearchResult['response'];
            $hits = (array) ($response['hits'] ?? array());
            $productIds = array_values(array_unique(array_filter(array_map(
                static function ($hit) {
                    return isset($hit['id']) ? (int) $hit['id'] : 0;
                },
                $hits
            ))));
            $products = empty($productIds)
                ? array()
                : $this->products_model->get_search_productsID(
                    $this->clang,
                    $productIds,
                    true,
                    0,
                    $this->per_page
                );
            $count = (int) ($response['totalHits'] ?? ($response['estimatedTotalHits'] ?? 0));
            $facets = (array) ($response['facetDistribution'] ?? array());
            $searchSource = 'meilisearch';
            $searchControlsAvailable = true;
            $this->data['search_corrected_query'] = $meilisearchResult['query'] !== $search
                ? $meilisearchResult['query']
                : '';
            if (empty($products)) {
                $this->data['search_corrected_query'] = '';
            }

            if ($count === 0 && !$hasActiveFilters) {
                $legacyResult = $this->getLegacySearchPage($search, $paginationNr, $sort);
                $products = $legacyResult['products'];
                $count = $legacyResult['count'];
                if ($count > 0) {
                    $searchSource = 'legacy';
                    $searchControlsAvailable = false;
                }
            }

            $spellingSuggestion = $this->getValidatedSearchSpellingSuggestion(
                (string) ($meilisearchResult['query'] ?? $search),
                $filters,
                $count
            );
            if ($spellingSuggestion !== '') {
                $this->data['search_corrected_query'] = $spellingSuggestion;
            }
        } elseif ($hasActiveFilters) {
            // Do not silently show unfiltered SQL results when requested filters
            // could not be evaluated by Meilisearch.
            $this->data['search_error'] = $this->clang === 'RO'
                ? 'Filtrele de căutare sunt temporar indisponibile. Încearcă din nou puțin mai târziu.'
                : 'Фильтры поиска временно недоступны. Попробуйте ещё раз чуть позже.';
        } elseif ($search !== '') {
            $legacyResult = $this->getLegacySearchPage($search, $paginationNr, $sort);
            $products = $legacyResult['products'];
            $count = $legacyResult['count'];
        }

        $this->data['search'] = $search;
        $this->data['search_track_zero_result'] = $search !== ''
            && $count === 0
            && !$hasActiveFilters;
        $this->data['search_sort'] = $sort;
        $this->data['search_source'] = $searchSource;
        $this->data['search_controls_available'] = $searchControlsAvailable;
        $this->data['search_filters'] = $filters;
        $this->data['search_facets'] = $facets;
        $this->data['search_filter_brands'] = array();
        $this->data['search_filter_categories'] = array();
        $this->data['search_filter_sex'] = (array) ($facets['sex'] ?? array());

        if ($searchControlsAvailable) {
            $brandFacetCounts = (array) ($facets['brand_id'] ?? array());
            $categoryFacetCounts = (array) ($facets['category_id'] ?? array());
            $brandFacetIds = array_values(array_unique(array_merge(
                array_map('intval', array_keys($brandFacetCounts)),
                $filters['brands']
            )));
            $categoryFacetIds = array_values(array_unique(array_merge(
                array_map('intval', array_keys($categoryFacetCounts)),
                $filters['categories']
            )));
            $this->data['search_filter_brands'] = $this->getSearchFilterOptions(
                $this->brands_model->get_brands_by_ids($brandFacetIds),
                $brandFacetCounts,
                $filters['brands']
            );
            $this->data['search_filter_categories'] = $this->getSearchFilterOptions(
                $this->categories_model->get_categories_by_ids(
                    $this->clang,
                    $categoryFacetIds
                ),
                $categoryFacetCounts,
                $filters['categories']
            );
        }

        $uriPath = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $paginationQuery = array('search' => $search);
        if ($sort !== '') $paginationQuery['sort'] = $sort;
        if (!empty($filters['brands'])) $paginationQuery['brand'] = $filters['brands'];
        if (!empty($filters['categories'])) $paginationQuery['category'] = $filters['categories'];
        if (!empty($filters['sex'])) $paginationQuery['sex'] = $filters['sex'];
        if ($filters['price_min'] !== null) $paginationQuery['price_min'] = $filters['price_min'];
        if ($filters['price_max'] !== null) $paginationQuery['price_max'] = $filters['price_max'];
        if ($filters['in_stock']) $paginationQuery['in_stock'] = 1;
        if ($filters['discounted']) $paginationQuery['discounted'] = 1;
        $queryString = http_build_query($paginationQuery);
        $urlPattern = $uriPath . '?' . $queryString . '&page=(:num)';
        $paginator = new Paginator(
            $count,
            $this->per_page,
            $paginationNr,
            $urlPattern
        );
        $paginator->setMaxPagesToShow(5);
        $this->data['page_url'] = '/' . $this->uri1 . '/' . $this->uri2;
        $this->data['paginator'] = $paginator;

        $this->data['products'] = $products;
        $this->data['products_count'] = $count;
        if ($trackSearchQuery && $this->hasSearchAnalyticsConsent()) {
            $this->site_analytics_model->record_search_activity($search, 'result_count', $count);
        }
        $this->data['search_fallback_products'] = $search !== ''
            && $count === 0
            && !$hasActiveFilters
            ? $this->getDailySearchDiscountProducts(10)
            : array();
        $this->data['show_from'] = $start;
        $this->data['show_to'] = min($count, $start + count($products));

        $this->data['brands'] = $this->brands_model->get_brands();

        $this->data['lang_urls'] = array(
            'ro' => '/' . $page->uriRO.'search=' . $_GET['search'] ,
            'ru' => '/' . $page->uriRU.'search=' . $_GET['search'] ,
        );
        $this->data['inner_view'] = 'pages/catalog/search';
        $this->loadOGImgData($page);
        $this->_load_main_page($page);

        $this->_render();

    }

    /** Return a stable, daily selection of in-stock products from the discounts catalog. */
    private function getDailySearchDiscountProducts($limit = 10)
    {
        $limit = max(1, min(10, (int) $limit));
        $candidates = (array) $this->products_model->get_header_discount_products(
            $this->clang,
            100,
            false
        );
        $dailySeed = date('Y-m-d');
        usort($candidates, static function ($left, $right) use ($dailySeed) {
            $leftId = (int) ($left->id ?? 0);
            $rightId = (int) ($right->id ?? 0);
            $leftHash = hash('sha256', $dailySeed . '|' . $leftId);
            $rightHash = hash('sha256', $dailySeed . '|' . $rightId);

            return strcmp($leftHash, $rightHash) ?: ($leftId <=> $rightId);
        });

        $productIds = array_values(array_filter(array_map(
            static function ($product) {
                return (int) ($product->id ?? 0);
            },
            array_slice($candidates, 0, $limit)
        )));

        return empty($productIds)
            ? array()
            : (array) $this->products_model->get_search_productsID(
                $this->clang,
                $productIds,
                true,
                0,
                $limit
            );
    }

    private function getSearchFilterState()
    {
        $readIds = static function ($value) {
            if (!is_array($value)) return array();
            $values = array_filter($value, 'is_scalar');
            return array_values(array_unique(array_filter(array_map('intval', $values), static function ($id) {
                return $id > 0;
            })));
        };
        $sexInput = $_GET['sex'] ?? array();
        $sexValues = is_array($sexInput)
            ? array_values(array_intersect(
                array('Женский', 'Мужской', 'Унисекс'),
                array_filter($sexInput, 'is_string')
            ))
            : array();
        $readPrice = static function ($value) {
            if (!is_scalar($value) || !is_numeric($value)) return null;
            $price = (float) $value;
            if (!is_finite($price)) return null;
            return min(10000000, max(0, $price));
        };
        $priceMin = $readPrice($_GET['price_min'] ?? null);
        $priceMax = $readPrice($_GET['price_max'] ?? null);

        if ($priceMin !== null && $priceMax !== null && $priceMin > $priceMax) {
            $swap = $priceMin;
            $priceMin = $priceMax;
            $priceMax = $swap;
        }

        return array(
            'brands' => $readIds($_GET['brand'] ?? array()),
            'categories' => $readIds($_GET['category'] ?? array()),
            'sex' => $sexValues,
            'price_min' => $priceMin,
            'price_max' => $priceMax,
            'in_stock' => isset($_GET['in_stock']) && is_scalar($_GET['in_stock']) && (string) $_GET['in_stock'] === '1',
            'discounted' => isset($_GET['discounted']) && is_scalar($_GET['discounted']) && (string) $_GET['discounted'] === '1',
        );
    }

    private function hasActiveSearchFilters(array $filters)
    {
        return !empty($filters['brands'])
            || !empty($filters['categories'])
            || !empty($filters['sex'])
            || $filters['price_min'] !== null
            || $filters['price_max'] !== null
            || $filters['in_stock']
            || $filters['discounted'];
    }

    private function buildSearchFilterExpression(array $filters)
    {
        $filterParts = array();
        foreach (array('brands' => 'brand_id', 'categories' => 'category_id') as $key => $attribute) {
            if (empty($filters[$key])) continue;
            $terms = array();
            foreach ($filters[$key] as $id) {
                $terms[] = $attribute . ' = ' . (int) $id;
            }
            $filterParts[] = '(' . implode(' OR ', $terms) . ')';
        }

        if (!empty($filters['sex'])) {
            $terms = array();
            foreach ($filters['sex'] as $sex) {
                $terms[] = 'sex = "' . $sex . '"';
            }
            $filterParts[] = '(' . implode(' OR ', $terms) . ')';
        }
        if (($filters['price_min'] ?? null) !== null) {
            $filterParts[] = 'current_price >= ' . number_format((float) $filters['price_min'], 2, '.', '');
        }
        if (($filters['price_max'] ?? null) !== null) {
            $filterParts[] = 'current_price <= ' . number_format((float) $filters['price_max'], 2, '.', '');
        }
        if (!empty($filters['in_stock'])) $filterParts[] = 'on_stock > 0';
        if (!empty($filters['discounted'])) $filterParts[] = 'has_discount = true';

        return implode(' AND ', $filterParts);
    }

    private function getSpellCorrectedSearchQuery($query)
    {
        $query = trim(preg_replace('/\s+/u', ' ', (string) $query));
        if ($query === '') return '';

        $knownCorrection = $this->getKnownSearchSpellingCorrection($query);
        if ($knownCorrection !== '') return $knownCorrection;

        $method = 'correct' . strtoupper((string) $this->lclang);
        if (!method_exists($this->spellcorrector, $method)) return '';

        $parts = preg_split('/(\s+)/u', $query, -1, PREG_SPLIT_DELIM_CAPTURE);
        if (!is_array($parts)) return '';

        foreach ($parts as &$part) {
            if ($part === '' || preg_match('/\s/u', $part) || !preg_match('/^\p{L}{3,}$/u', $part)) {
                continue;
            }
            $corrected = $this->spellcorrector->{$method}($part);
            if (is_string($corrected) && $corrected !== '') {
                $part = $corrected;
            }
        }
        unset($part);

        $correctedQuery = trim(implode('', $parts));
        $normalize = static function ($value) {
            $value = trim(preg_replace('/\s+/u', ' ', (string) $value));
            return function_exists('mb_strtolower')
                ? mb_strtolower($value, 'UTF-8')
                : strtolower($value);
        };

        return $normalize($correctedQuery) !== $normalize($query)
            ? $correctedQuery
            : '';
    }

    private function getValidatedSearchSpellingSuggestion($query, array $filters, $currentHitCount)
    {
        $knownCorrection = $this->getKnownSearchSpellingCorrection($query);
        $candidate = $knownCorrection !== ''
            ? $knownCorrection
            : $this->getSpellCorrectedSearchQuery($query);
        if ($candidate === '') return '';

        // A keyboard-layout conversion is handled separately as a search fallback.
        // Do not label it as a spelling correction (for example, "clarins" -> "сдфкшты").
        $layoutVariant = $this->getSearchLayoutVariant($query);
        $normalizeQuery = static function ($value) {
            $value = trim(preg_replace('/\s+/u', ' ', (string) $value));
            return function_exists('mb_strtolower')
                ? mb_strtolower($value, 'UTF-8')
                : strtolower($value);
        };
        if ($layoutVariant !== '' && $normalizeQuery($candidate) === $normalizeQuery($layoutVariant)) {
            return '';
        }

        $options = array(
            'limit' => 1,
            'attributesToRetrieve' => array('id'),
        );
        $filterExpression = $this->buildSearchFilterExpression($filters);
        if ($filterExpression !== '') $options['filter'] = $filterExpression;

        $response = $this->meilisearch_client->search(
            (string) config_item('meilisearch_index'),
            $candidate,
            $options
        );
        if ($response === false || empty($response['hits'])) return '';

        $candidateIds = array_values(array_unique(array_filter(array_map(
            static function ($hit) {
                return isset($hit['id']) ? (int) $hit['id'] : 0;
            },
            (array) $response['hits']
        ))));
        $visibleCandidateProducts = empty($candidateIds)
            ? array()
            : $this->products_model->get_search_productsID(
            $this->clang,
            $candidateIds,
            true,
            0,
            1
        );
        if (empty($visibleCandidateProducts)) {
            return '';
        }

        $candidateHitCount = (int) ($response['estimatedTotalHits'] ?? ($response['totalHits'] ?? 0));
        return $candidateHitCount > (int) $currentHitCount || $knownCorrection !== ''
            ? $candidate
            : '';
    }

    private function searchFullPageWithMeilisearch($query, $page, $sort, array $filters)
    {
        $options = array(
            'page' => max(1, (int) $page),
            'hitsPerPage' => $this->per_page,
            'facets' => array('brand_id', 'category_id', 'sex'),
            'attributesToRetrieve' => array('id'),
        );
        $filterExpression = $this->buildSearchFilterExpression($filters);
        if ($filterExpression !== '') $options['filter'] = $filterExpression;

        $sortMap = array(
            '1' => array('has_stock:desc', 'best_selling:desc'),
            '2' => array('has_stock:desc', 'current_price:asc'),
            '3' => array('has_stock:desc', 'current_price:desc'),
            '4' => array('has_stock:desc', 'sale_percent:desc', 'sale_mdl:desc'),
            '5' => array('has_stock:desc', 'is_new:desc', 'id:desc'),
        );
        $options['sort'] = isset($sortMap[$sort])
            ? $sortMap[$sort]
            : array('has_stock:desc');

        $response = null;
        $usedQuery = (string) $query;
        foreach ($this->getSearchQueryVariants($query) as $candidateQuery) {
            $candidateResponse = $this->meilisearch_client->search(
                (string) config_item('meilisearch_index'),
                $candidateQuery,
                $options
            );
            if ($candidateResponse === false) return false;

            if ($response === null) {
                $response = $candidateResponse;
            }
            if (!empty($candidateResponse['hits'])) {
                $response = $candidateResponse;
                $usedQuery = $candidateQuery;
                break;
            }
        }

        if ($response === null) return false;

        return array('response' => $response, 'query' => $usedQuery);
    }

    private function getSearchFilterOptions($items, array $counts, array $selectedIds)
    {
        $itemsById = array();
        foreach ((array) $items as $item) {
            $item->count = (int) ($counts[(string) $item->id] ?? 0);
            $item->selected = in_array((int) $item->id, $selectedIds, true);
            $itemsById[(int) $item->id] = $item;
        }

        return array_values($itemsById);
    }

    private function getLegacySearchPage($query, $page, $sort = '')
    {
        if ($query === '') return array('products' => array(), 'count' => 0);

        $start = (max(1, (int) $page) - 1) * $this->per_page;
        $allProducts = $this->products_model->get_all_products_search($query);
        $count = count($allProducts);
        $products = $this->products_model->get_products_pag_search(
            $this->clang,
            $start,
            $this->per_page,
            $query,
            $sort
        );

        if ($count === 0) {
            $productIds = array();
            foreach ($this->getSearchQueryVariants($query) as $candidateQuery) {
                $productIds = $this->search_model->search_ids($candidateQuery);
                if (!empty($productIds)) {
                    if ($candidateQuery !== $query) {
                        $this->data['search_corrected_query'] = $candidateQuery;
                    }
                    break;
                }
            }

            if (empty($productIds)) {
                $spellCorrectedQuery = $this->getSpellCorrectedSearchQuery($query);
                if ($spellCorrectedQuery !== '' && $spellCorrectedQuery !== $query) {
                    $productIds = $this->search_model->search_ids($spellCorrectedQuery);
                    if (!empty($productIds)) $this->data['search_corrected_query'] = $spellCorrectedQuery;
                }
            }

            if (!empty($productIds)) {
                $count = count($productIds);
                $pageIds = array_slice($productIds, $start, $this->per_page);
                $products = $this->products_model->get_search_productsID(
                    $this->clang,
                    $pageIds,
                    true,
                    0,
                    $this->per_page
                );
                if (empty($products)) {
                    $count = 0;
                    $this->data['search_corrected_query'] = '';
                }
            }
        }

        return array('products' => (array) $products, 'count' => (int) $count);
    }

    public function search()
    {
        check_if_POST();

        $query = trim(preg_replace('/\s+/u', ' ', (string) $this->input->post('search', TRUE)));
        $this->data['search'] = $query;
        $this->data['search_corrected_query'] = '';
        $this->data['search_recent_queries'] = $this->normalizeSearchHistory(
            $this->input->post('recent_queries')
        );
        $this->data['popular_categories'] = array();
        $this->data['popular_brands'] = array();
        $this->data['popular_search_queries'] = array();
        $this->data['matching_popular_search_queries'] = array();
        $this->data['search_inline_completion'] = '';
        $this->data['search_related_brands'] = array();
        $this->data['search_fallback_products'] = array();
        $this->data['categories'] = array();
        $this->data['brands_nav'] = array();
        $this->data['products_new'] = array();
        $this->data['search_mode'] = $query === '' ? 'landing' : 'results';

        if ($query === '') {
            $discountCandidates = (array) $this->products_model->get_header_discount_products(
                $this->clang,
                100,
                false
            );
            $dailySeed = date('Y-m-d');
            usort($discountCandidates, static function ($left, $right) use ($dailySeed) {
                $leftId = (int) ($left->id ?? 0);
                $rightId = (int) ($right->id ?? 0);
                $leftHash = hash('sha256', $dailySeed . '|' . $leftId);
                $rightHash = hash('sha256', $dailySeed . '|' . $rightId);

                return strcmp($leftHash, $rightHash) ?: ($leftId <=> $rightId);
            });
            $discountIds = array_values(array_filter(array_map(
                static function ($product) {
                    return (int) ($product->id ?? 0);
                },
                array_slice($discountCandidates, 0, 10)
            )));
            $products = empty($discountIds)
                ? array()
                : $this->products_model->get_search_productsID(
                    $this->clang,
                    $discountIds,
                    true,
                    0,
                    10
                );
            $this->data['search_section_title'] = $this->clang === 'RO'
                ? 'Ofertele zilei'
                : 'Скидки дня';
            $this->data['search_products_source'] = 'sale';

            $this->data['products_new'] = $products;
            $this->setPopularSearchNavigation(0, true);
        } elseif (function_exists('mb_strlen') ? mb_strlen($query, 'UTF-8') >= 2 : strlen($query) >= 2) {
            $autocomplete = $this->getMeilisearchAutocomplete($query);
            if ($autocomplete === false) {
                // Keep the current SQL search as a fallback if Meilisearch is unavailable.
                $products = $this->products_model->get_search_products($this->clang, $query);
                $this->data['categories'] = $this->categories_model->get_search_categories($this->clang, $query);
                $this->data['brands_nav'] = $this->brands_model->get_search_brands($this->clang, $query);
                $hasMatches = !empty($products)
                    || !empty($this->data['categories'])
                    || !empty($this->data['brands_nav']);
                if (!$hasMatches) {
                    foreach ($this->getSearchQueryVariants($query) as $candidateQuery) {
                        if ($candidateQuery === $query) continue;
                        $candidateProducts = $this->products_model->get_search_products($this->clang, $candidateQuery);
                        $candidateCategories = $this->categories_model->get_search_categories($this->clang, $candidateQuery);
                        $candidateBrands = $this->brands_model->get_search_brands($this->clang, $candidateQuery);
                        if (empty($candidateProducts) && empty($candidateCategories) && empty($candidateBrands)) {
                            continue;
                        }

                        $products = $candidateProducts;
                        $this->data['categories'] = $candidateCategories;
                        $this->data['brands_nav'] = $candidateBrands;
                        $this->data['search_corrected_query'] = $candidateQuery;
                        break;
                    }
                }
            } else {
                $products = $autocomplete['products'];
                $this->data['categories'] = $autocomplete['categories'];
                $this->data['brands_nav'] = $autocomplete['brands'];
                $this->data['search_corrected_query'] = $autocomplete['corrected_query'];
            }

            $hasOriginalMatches = !empty($products)
                || !empty($this->data['categories'])
                || !empty($this->data['brands_nav']);
            $layoutQuery = !empty($this->data['search_corrected_query'])
                ? (string) $this->data['search_corrected_query']
                : ($hasOriginalMatches ? '' : $this->getSearchLayoutVariant($query));
            if ($layoutQuery !== '' && $layoutQuery !== $query) {
                if (empty($this->data['categories'])) {
                    $this->data['categories'] = $this->categories_model->get_search_categories($this->clang, $layoutQuery);
                }
                if (empty($this->data['brands_nav'])) {
                    $this->data['brands_nav'] = $this->brands_model->get_search_brands($this->clang, $layoutQuery);
                }
                if (empty($this->data['search_corrected_query'])) {
                    $layoutProducts = $this->products_model->get_search_products($this->clang, $layoutQuery);
                    if (!empty($layoutProducts)) {
                        $products = $layoutProducts;
                        $this->data['search_corrected_query'] = $layoutQuery;
                    }
                }
            }

            $brandSearchQuery = !empty($this->data['search_corrected_query'])
                ? (string) $this->data['search_corrected_query']
                : $query;
            $matchedBrand = $this->findExactSearchBrand($brandSearchQuery, $this->data['brands_nav']);

            if (!empty($matchedBrand)) {
                $this->data['search_corrected_query'] = '';
                $this->setPopularSearchNavigation((int) $matchedBrand->id);
                $relatedBrandIds = $this->analytics_model->get_header_search_related_brand_ids(
                    $matchedBrand->id,
                    10
                );
                $relatedBrands = $this->brands_model->get_brands_by_ids($relatedBrandIds);
                $seenRelatedBrandIds = array_map(static function ($brand) {
                    return (int) $brand->id;
                }, $relatedBrands);

                foreach ((array) $this->data['popular_brands'] as $popularBrand) {
                    $popularBrandId = (int) $popularBrand->id;
                    if (!in_array($popularBrandId, $seenRelatedBrandIds, true)) {
                        $relatedBrands[] = $popularBrand;
                        $seenRelatedBrandIds[] = $popularBrandId;
                    }
                    if (count($relatedBrands) >= 5) {
                        break;
                    }
                }

                $this->data['search_related_brands'] = array_slice($relatedBrands, 0, 5);
                $popularIds = $this->analytics_model->get_header_search_brand_product_ids(
                    $matchedBrand->id,
                    50
                );
                $popularProducts = empty($popularIds)
                    ? array()
                    : $this->products_model->get_search_productsID(
                        $this->clang,
                        $popularIds,
                        true,
                        0,
                        50
                    );

                if (!empty($popularProducts)) {
                    $products = $popularProducts;
                    $this->data['search_section_title'] = ($this->clang === 'RO'
                        ? 'Cele mai vândute de la '
                        : 'Популярное у бренда ') . $matchedBrand->title;
                    $this->data['search_exact_brand_id'] = (int) $matchedBrand->id;
                }
            }

            if (empty($this->data['search_section_title'])) {
                $this->data['search_section_title'] = $this->clang === 'RO'
                    ? 'Rezultate pentru: ' . $query
                    : 'Результаты по запросу: ' . $query;
            }

            if ($this->hasSearchAnalyticsConsent()) {
                $popularQueryPrefix = !empty($this->data['search_corrected_query'])
                    ? (string) $this->data['search_corrected_query']
                    : $query;
                $this->data['matching_popular_search_queries'] = $this->site_analytics_model
                    ->get_matching_popular_search_queries($popularQueryPrefix, 5, 30);
            }

            $completionCandidates = array();
            foreach ((array) $this->data['search_recent_queries'] as $recentQuery) {
                $completionCandidates[] = $recentQuery;
            }
            foreach ((array) $this->data['matching_popular_search_queries'] as $popularQuery) {
                $completionCandidates[] = $popularQuery;
            }
            foreach ((array) $this->data['brands_nav'] as $brand) {
                $completionCandidates[] = $brand->title ?? '';
            }
            foreach ((array) $this->data['categories'] as $category) {
                $completionCandidates[] = $category->title ?? '';
            }
            foreach ((array) $products as $product) {
                $completionCandidates[] = $product->brand_title ?? '';
                $completionCandidates[] = $product->title ?? '';
            }
            $this->data['search_inline_completion'] = $this->getSearchInlineCompletion(
                $query,
                $completionCandidates
            );

            if (empty($products)
                && empty($this->data['categories'])
                && empty($this->data['brands_nav'])
            ) {
                $this->data['search_fallback_products'] = $this->getDailySearchDiscountProducts(10);
            }

            $this->data['products_new'] = array_slice((array) $products, 0, 10);
        }

        $response = $this->load->view('layouts/pages/search', $this->data, true);
        echo $response;
    }

    private function normalizeSearchHistory($history)
    {
        if (!is_array($history)) {
            return array();
        }

        $normalized = array();
        foreach ($history as $query) {
            $query = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $query)));
            $length = function_exists('mb_strlen') ? mb_strlen($query, 'UTF-8') : strlen($query);
            if ($query === '' || $length < 2 || in_array($query, $normalized, true)) {
                continue;
            }

            $normalized[] = function_exists('mb_substr')
                ? mb_substr($query, 0, 80, 'UTF-8')
                : substr($query, 0, 80);
            if (count($normalized) >= 3) {
                break;
            }
        }

        return $normalized;
    }

    private function getSearchInlineCompletion($query, array $candidates)
    {
        $query = trim(preg_replace('/\\s+/u', ' ', strip_tags((string) $query)));
        $queryLower = function_exists('mb_strtolower')
            ? mb_strtolower($query, 'UTF-8')
            : strtolower($query);
        $queryLength = function_exists('mb_strlen')
            ? mb_strlen($queryLower, 'UTF-8')
            : strlen($queryLower);
        if ($queryLength < 2) {
            return '';
        }

        foreach ($candidates as $candidate) {
            $candidate = trim(preg_replace('/\\s+/u', ' ', strip_tags((string) $candidate)));
            $candidateLower = function_exists('mb_strtolower')
                ? mb_strtolower($candidate, 'UTF-8')
                : strtolower($candidate);
            $startsWithQuery = function_exists('mb_strpos')
                ? mb_strpos($candidateLower, $queryLower, 0, 'UTF-8') === 0
                : strpos($candidateLower, $queryLower) === 0;
            $candidateLength = function_exists('mb_strlen')
                ? mb_strlen($candidateLower, 'UTF-8')
                : strlen($candidateLower);
            if (!$startsWithQuery || $candidateLength <= $queryLength) {
                continue;
            }

            return $candidateLower;
        }

        return '';
    }

    private function normalizeProductIds($ids)
    {
        if (!is_array($ids)) {
            return array();
        }

        $normalized = array();
        foreach ($ids as $id) {
            $id = (int) $id;
            if ($id <= 0 || in_array($id, $normalized, true)) {
                continue;
            }
            $normalized[] = $id;
            if (count($normalized) >= 20) {
                break;
            }
        }

        return $normalized;
    }

    private function hasSearchAnalyticsConsent()
    {
        $raw = (string) $this->input->cookie('cookie_consent', true);
        $decoded = json_decode(rawurldecode($raw), true);

        if (!is_array($decoded)) {
            $decoded = json_decode($raw, true);
        }

        return is_array($decoded) && !empty($decoded['analytics']);
    }

    private function findExactSearchBrand($query, $brands)
    {
        $normalize = static function ($value) {
            $value = trim(preg_replace('/\s+/u', ' ', (string) $value));
            return function_exists('mb_strtolower')
                ? mb_strtolower($value, 'UTF-8')
                : strtolower($value);
        };
        $normalizedQuery = $normalize($query);

        foreach ((array) $brands as $brand) {
            if ($normalize($brand->title ?? '') === $normalizedQuery) {
                return $brand;
            }
        }

        return null;
    }

    private function getExactSearchNavigationTarget($query)
    {
        $query = trim(preg_replace('/\\s+/u', ' ', (string) $query));
        if ($query === '') {
            return false;
        }

        $queries = array($query);
        $layoutVariant = $this->getSearchLayoutVariant($query);
        if ($layoutVariant !== '' && $layoutVariant !== $query) {
            $queries[] = $layoutVariant;
        }

        foreach ($queries as $candidateQuery) {
            $brands = (array) $this->brands_model->get_exact_search_brands($candidateQuery);
            $categories = (array) $this->categories_model->get_exact_search_categories(
                $this->clang,
                $candidateQuery
            );
            $matches = array();

            foreach ($brands as $brand) {
                if (!empty($brand->uri)) {
                    $matches[] = $brand;
                }
            }
            foreach ($categories as $category) {
                if (!empty($category->uri)) {
                    $matches[] = $category;
                }
            }

            if (count($matches) !== 1) {
                if (!empty($matches)) {
                    return false;
                }
                continue;
            }

            $catalogUri = trim((string) ($this->data['menu']['all'][3]->uri ?? ''), '/');
            if ($catalogUri === '') {
                $catalogUri = $this->lclang === 'ro' ? 'catalog' : 'katalog';
            }

            return '/' . rawurlencode((string) $this->lclang)
                . '/' . rawurlencode($catalogUri)
                . '/' . rawurlencode(trim((string) $matches[0]->uri, '/'));
        }

        return false;
    }

    private function setPopularSearchNavigation($excludeBrandId = 0, $useCuratedLandingLists = false)
    {
        $this->load->driver('cache', array(
            'adapter' => 'file',
            'backup' => 'dummy',
            'key_prefix' => 'vn_frontend_',
        ));

        $cacheKey = 'header_search_popular_v2_' . strtolower($this->clang);
        $popular = $this->cache->get($cacheKey);
        if (!is_array($popular)) {
            $popular = $this->analytics_model->get_header_search_popular(10);
        }
        if (!isset($popular['search_queries']) || !is_array($popular['search_queries'])) {
            $popular['search_queries'] = $this->site_analytics_model->get_popular_search_queries(5, 30);
        }
        $this->cache->save($cacheKey, $popular, 300);

        $this->data['popular_search_queries'] = array_slice($popular['search_queries'], 0, 3);

        if ($useCuratedLandingLists) {
            $this->data['popular_categories'] = $this->getCuratedInitialSearchCategories();
            $this->data['popular_brands'] = $this->getCuratedInitialSearchBrands();
            return;
        }

        $categoryIds = array_map(static function ($row) {
            return (int) ($row['category_id'] ?? 0);
        }, array_slice((array) ($popular['categories'] ?? array()), 0, 10));
        $brandIds = array_map(static function ($row) {
            return (int) ($row['brand_id'] ?? 0);
        }, array_slice((array) ($popular['brands'] ?? array()), 0, 10));

        $popularCategories = $this->categories_model->get_categories_by_ids(
            $this->clang,
            $categoryIds
        );
        $this->data['popular_categories'] = array_slice($popularCategories, 0, 5);
        $popularBrands = $this->brands_model->get_brands_by_ids($brandIds);

        if ($excludeBrandId > 0) {
            $popularBrands = array_values(array_filter($popularBrands, static function ($brand) use ($excludeBrandId) {
                return (int) $brand->id !== $excludeBrandId;
            }));
        }

        $this->data['popular_brands'] = array_slice($popularBrands, 0, 5);
    }

    private function getCuratedInitialSearchCategories()
    {
        $cacheKey = 'header_search_curated_categories_v1_' . strtolower((string) $this->clang);
        $cachedCategories = $this->cache->get($cacheKey);
        if (is_array($cachedCategories)) {
            return $cachedCategories;
        }

        $titles = array(
            'Тональные средства',
            'Парфюмированная вода для женщин',
            'Тушь',
            'Нишевая парфюмерия',
            'Пудры',
            'Помада для губ',
        );
        $categories = (array) $this->categories_model->get_categories_by_ru_titles(
            $this->clang,
            $titles
        );
        $normalize = static function ($value) {
            $value = mb_strtolower(trim((string) $value), 'UTF-8');
            $value = str_replace('ё', 'е', $value);
            return preg_replace('/[\p{Z}\p{P}\p{S}]+/u', '', $value);
        };
        $categoriesByTitle = array();
        foreach ($categories as $category) {
            $key = $normalize($category->title_ru ?? '');
            if ($key !== '') {
                $categoriesByTitle[$key] = $category;
            }
        }

        $orderedCategories = array();
        foreach ($titles as $title) {
            $key = $normalize($title);
            if (isset($categoriesByTitle[$key])) {
                $orderedCategories[] = $categoriesByTitle[$key];
            }
        }

        $this->cache->save($cacheKey, $orderedCategories, 300);
        return $orderedCategories;
    }

    private function getCuratedInitialSearchBrands()
    {
        $cacheKey = 'header_search_curated_brands_v1_' . strtolower((string) $this->clang);
        $cachedBrands = $this->cache->get($cacheKey);
        if (is_array($cachedBrands)) {
            return $cachedBrands;
        }

        $titles = array('Clarins', 'Pupa', 'MAC', 'Artdeco', 'Divage', 'Clinique', 'Naj Oleari');
        $normalize = static function ($value) {
            $value = trim((string) $value);
            if (function_exists('iconv')) {
                $transliterated = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
                if ($transliterated !== false) {
                    $value = $transliterated;
                }
            }
            return strtolower(preg_replace('/[^a-z0-9]+/i', '', $value));
        };

        $brandLookup = array();
        foreach ((array) $this->brands_model->get_brands() as $brand) {
            $key = $normalize($brand->title ?? '');
            if ($key !== '' && !empty($brand->uri)) {
                $brandLookup[$key] = $brand;
            }
        }

        $orderedBrands = array();
        foreach ($titles as $title) {
            $key = $normalize($title);
            if (isset($brandLookup[$key])) {
                $orderedBrands[] = $brandLookup[$key];
            }
        }

        $this->cache->save($cacheKey, $orderedBrands, 300);
        return $orderedBrands;
    }

    /**
     * Search products and taxonomy facets in one Meilisearch request, then
     * hydrate display data from MySQL to keep current links and product rules.
     *
     * @return array|false False means Meilisearch failed and SQL fallback is needed.
     */
    private function getMeilisearchAutocomplete($query)
    {
        if (!$this->meilisearch_client->isConfigured()) {
            return false;
        }

        $queries = $this->getSearchQueryVariants($query);

        foreach ($queries as $searchQuery) {
            $response = $this->meilisearch_client->search(
                (string) config_item('meilisearch_index'),
                $searchQuery,
                array(
                    'limit' => 30,
                    'attributesToRetrieve' => array('id'),
                    'facets' => array('brand_id', 'category_id'),
                    'sort' => array('has_stock:desc'),
                )
            );

            if ($response === false) {
                return false;
            }

            $facetDistribution = (array) ($response['facetDistribution'] ?? array());
            $brandIds = $this->getAutocompleteFacetIds(
                (array) ($facetDistribution['brand_id'] ?? array())
            );
            $categoryIds = $this->getAutocompleteFacetIds(
                (array) ($facetDistribution['category_id'] ?? array())
            );
            $ids = array();
            foreach ((array) ($response['hits'] ?? array()) as $hit) {
                if (isset($hit['id']) && (int) $hit['id'] > 0) {
                    $ids[] = (int) $hit['id'];
                }
            }

            $ids = array_values(array_unique($ids));

            if ($searchQuery === (string) $query && empty($ids) && empty($brandIds) && empty($categoryIds)) {
                $exactBrands = (array) $this->brands_model->get_exact_search_brands($query);
                $exactCategories = (array) $this->categories_model->get_exact_search_categories(
                    $this->clang,
                    $query
                );
                if (!empty($exactBrands) || !empty($exactCategories)) {
                    return array(
                        'products' => array(),
                        'categories' => $exactCategories,
                        'brands' => $exactBrands,
                        'corrected_query' => '',
                    );
                }
            }

            if (empty($ids) && empty($brandIds) && empty($categoryIds)) {
                continue;
            }

            $products = empty($ids)
                ? array()
                : $this->products_model->get_search_productsID($this->clang, $ids, true);
            $categories = empty($categoryIds)
                ? array()
                : $this->categories_model->get_categories_by_ids($this->clang, $categoryIds);
            $brands = empty($brandIds)
                ? array()
                : $this->brands_model->get_brands_by_ids($brandIds);

            $hasMatches = !empty($products) || !empty($categories) || !empty($brands);
            $correctedQuery = $hasMatches && $searchQuery !== (string) $query
                ? $searchQuery
                : '';
            $spellingSuggestion = $this->getValidatedSearchSpellingSuggestion(
                $searchQuery,
                array(),
                (int) ($response['estimatedTotalHits'] ?? ($response['totalHits'] ?? 0))
            );
            if ($spellingSuggestion !== '') {
                $correctedQuery = $spellingSuggestion;
            }

            return array(
                'products' => (array) $products,
                'categories' => (array) $categories,
                'brands' => (array) $brands,
                'corrected_query' => $correctedQuery,
            );
        }

        $spellingSuggestion = $this->getValidatedSearchSpellingSuggestion(
            (string) $query,
            array(),
            0
        );

        return array(
            'products' => array(),
            'categories' => array(),
            'brands' => array(),
            'corrected_query' => $spellingSuggestion,
        );
    }

    /** Return the most common positive integer facet values in Meili rank order. */
    private function getAutocompleteFacetIds(array $distribution, $limit = 5)
    {
        $counts = array();
        foreach ($distribution as $id => $count) {
            $id = filter_var((string) $id, FILTER_VALIDATE_INT);
            if ($id === false || $id <= 0 || !is_numeric($count)) {
                continue;
            }
            $counts[(int) $id] = (int) $count;
        }

        arsort($counts, SORT_NUMERIC);
        return array_slice(array_keys($counts), 0, max(1, (int) $limit));
    }

    /**
     * Return one likely keyboard-layout/transliteration correction, avoiding
     * multiple sequential Meilisearch requests for every possible conversion.
     */
    private function getSearchLayoutVariant($query)
    {
        $queryLength = function_exists('mb_strlen')
            ? mb_strlen((string) $query, 'UTF-8')
            : strlen((string) $query);
        $hasCyrillic = preg_match('/\p{Cyrillic}/u', (string) $query) === 1;
        $hasLatin = preg_match('/[a-z]/i', (string) $query) === 1;
        $layoutSwitcher = null;

        if ($queryLength >= 3 && $hasCyrillic && !$hasLatin) {
            $layoutSwitcher = 'switcher_en';
        } elseif ($queryLength >= 3 && $hasLatin && !$hasCyrillic) {
            // Prefer phonetic transliteration for common digraphs such as "sh".
            $layoutSwitcher = preg_match('/(?:sh|zh|ch|ya|yu|yo)/i', (string) $query)
                ? 'switcher_syntax_ru'
                : 'switcher_ru';
        }

        if ($layoutSwitcher === null || !function_exists($layoutSwitcher)) {
            return '';
        }

        $variant = trim(preg_replace('/\s+/u', ' ', (string) call_user_func($layoutSwitcher, $query)));
        $normalizedVariant = function_exists('mb_strtolower')
            ? mb_strtolower($variant, 'UTF-8')
            : strtolower($variant);
        $normalizedQuery = function_exists('mb_strtolower')
            ? mb_strtolower(trim((string) $query), 'UTF-8')
            : strtolower(trim((string) $query));

        return $variant !== '' && $normalizedVariant !== $normalizedQuery
            ? $variant
            : '';
    }

    /** Correct a small set of frequent catalogue typos and product-name variants. */
    private function getKnownSearchSpellingCorrection($query)
    {
        $query = trim(preg_replace('/\s+/u', ' ', (string) $query));
        if ($query === '') return '';

        $corrections = array(
            'добле' => 'double',
            'серум' => 'serum',
            'кларанс' => 'clarins',
            'кларенс' => 'clarins',
            'кларинс' => 'clarins',
        );
        $corrected = preg_replace_callback(
            '/\p{L}+/u',
            static function ($matches) use ($corrections) {
                $word = function_exists('mb_strtolower')
                    ? mb_strtolower($matches[0], 'UTF-8')
                    : strtolower($matches[0]);
                return isset($corrections[$word]) ? $corrections[$word] : $matches[0];
            },
            $query
        );
        if (!is_string($corrected)) return '';
        $hasClarinsAlias = preg_match('/(?<!\p{L})clarins(?!\p{L})/iu', $corrected) === 1;
        if ($hasClarinsAlias) {
            $corrected = preg_replace(
                '/(?<!\p{L})двойная\s+сыворотка(?!\p{L})/iu',
                'double serum',
                $corrected
            );
        }
        $corrected = preg_replace('/(?<!\S)(\p{L}+)(?:\s+\1)+(?!\S)/iu', '$1', $corrected);

        $normalize = static function ($value) {
            $value = trim(preg_replace('/\s+/u', ' ', (string) $value));
            return function_exists('mb_strtolower')
                ? mb_strtolower($value, 'UTF-8')
                : strtolower($value);
        };

        return $normalize($corrected) !== $normalize($query) ? $corrected : '';
    }

    /** Original input first, followed by known spelling and keyboard-layout variants. */
    private function getSearchQueryVariants($query)
    {
        $queries = array();
        $baseQueries = array((string) $query);
        $layoutVariant = $this->getSearchLayoutVariant($query);
        if ($layoutVariant !== '') $baseQueries[] = $layoutVariant;

        foreach ($baseQueries as $baseQuery) {
            $baseQuery = trim(preg_replace('/\s+/u', ' ', (string) $baseQuery));
            if ($baseQuery === '') continue;
            $queries[] = $baseQuery;

            $correction = $this->getKnownSearchSpellingCorrection($baseQuery);
            if ($correction !== '') $queries[] = $correction;

            $doubleSerumVariant = preg_replace(
                '/(?<!\p{L})двойная\s+сыворотка(?!\p{L})/iu',
                'double serum',
                $baseQuery
            );
            if (is_string($doubleSerumVariant) && $doubleSerumVariant !== $baseQuery) {
                $queries[] = $doubleSerumVariant;
            }
        }

        $unique = array();
        foreach ($queries as $candidate) {
            $key = function_exists('mb_strtolower')
                ? mb_strtolower($candidate, 'UTF-8')
                : strtolower($candidate);
            if (!isset($unique[$key])) $unique[$key] = $candidate;
        }

        return array_values($unique);
    }

    /**
     * Diagnostic endpoint for checking the Meilisearch connection and index.
     */
    public function meilisearch()
    {
        $query = trim((string) $this->input->get_post('q', true));
        if ($query === '') {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'source' => 'empty',
                    'query' => '',
                    'hits' => array(),
                ), JSON_UNESCAPED_UNICODE));
        }

        $index = (string) config_item('meilisearch_index');
        $response = $this->meilisearch_client->search($index, $query, array(
            'limit' => 20,
            'attributesToRetrieve' => array(
                'id', 'SKU', 'titleRU', 'titleRO', 'brand_id', 'category_id',
                'uriRU', 'uriRO', 'image',
            ),
        ));

        if ($response !== false) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'source' => 'meilisearch',
                    'query' => $query,
                    'estimatedTotalHits' => (int) ($response['estimatedTotalHits'] ?? 0),
                    'hits' => $response['hits'] ?? array(),
                ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        $fallbackProducts = $this->products_model->get_search_products($this->clang, $query);
        $fallbackHits = array();
        foreach ((array) $fallbackProducts as $product) {
            $fallbackHits[] = array(
                'id' => (int) $product->id,
                'SKU' => (string) ($product->SKU ?? ''),
                'title' => (string) ($product->title ?? ''),
                'uri' => (string) ($product->uri ?? ''),
                'brand_title' => (string) ($product->brand_title ?? ''),
            );
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'source' => 'sql_fallback',
                'query' => $query,
                'estimatedTotalHits' => count($fallbackHits),
                'hits' => $fallbackHits,
            ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }


    public function searchSpell()
    {
        check_if_POST();

        $search = $this->input->post('search', TRUE);
        $this->data['search'] = $search;

        if (strlen($search) < 3) {
            $this->data['success'] = 2;
            echo $this->load->view('layouts/pages/search', $this->data, true);
            return;
        }

        // --- 1. Обычный поиск ---
        $products = $this->products_model->get_search_products($this->clang, $search);
        $categories = $this->categories_model->get_search_categories($this->clang, $search);
        $brands_nav = $this->brands_model->get_search_brands($this->clang, $search);

        if (empty($products) && empty($categories) && empty($brands_nav)) {

            // --- 2. Поиск в product_search ---
            $productsID = $this->search_model->search_ids($search);

            // --- 4. Switch раскладки ---
            if (empty($productsID)) {

                $variants = [
                    switcher_ru($search),
                    switcher_en($search),
                    switcher_syntax_ru($search),
                    switcher_syntax_en($search),
                ];

                foreach ($variants as $variant) {
                    if (!$variant) continue;
                    $productsID = $this->search_model->search_ids($variant);
                    if (!empty($productsID)) {
                        break;
                    }
                }
            }

            // --- 3. SpellCorrector ---
            if (empty($productsID)) {
                $words = trim($search);
                $words = explode(" ", $words);
                $count = count($words);
                $correct = " ";
                for ($i = 0; $i < $count; $i++) {
                    $correct .= $this->spellcorrector->{'correct' . $this->lclang}($words[$i]).' ';
                }
                $correct = trim($correct);


                $productsID = $this->search_model->search_ids($correct);
            }

            // --- 4. Switch раскладки ---
            if (empty($productsID)) {

                $variants = [
                    switcher_ru($search),
                    switcher_en($search),
                    switcher_syntax_ru($search),
                    switcher_syntax_en($search),
                ];

                foreach ($variants as $variant) {
                    if (!$variant) continue;

                    $words = trim($variant);
                    $words = explode(" ", $words);
                    $count = count($words);
                    $correct = " ";
                    for ($i = 0; $i < $count; $i++) {
                        $correct .= $this->spellcorrector->{'correct' . $this->lclang}($words[$i]).' ';
                    }
                    $correct = trim($correct);

                    $productsID = $this->search_model->search_ids($correct);

                    if (!empty($productsID)) {
                        break;
                    }
                }
            }


            // --- 5. Получаем товары ---
            if (!empty($productsID)) {
                $products = $this->products_model->get_search_productsID($this->clang, $productsID);
            }
        }

        $this->data['products_new'] = $products;
        $this->data['categories'] = $categories;
        $this->data['brands_nav'] = $brands_nav;

        echo $this->load->view('layouts/pages/search', $this->data, true);
    }
}
