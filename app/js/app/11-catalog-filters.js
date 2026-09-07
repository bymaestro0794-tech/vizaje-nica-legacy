function filter_products() {
    var lang = $('html').attr('lang');
    var sorder = $('select[name="sorder"]').val();
    var category_id = $('input[name="category_id"]').val();
    var price_min = $('input[name="price-start"]').val();
    var price_max = $('input[name="price-end"]').val();

    var url = 'filter_category';
    var get_brands = '';
    $('input[name="brands"]:checkbox:checked').each(function () {
        get_brands = get_brands + '&brand%5B%5D=' + $(this).val();
    });

    var get_sex = '';
    $('input[name="sex"]:checkbox:checked').each(function () {
        get_sex = get_sex + '&sex%5B%5D=' + $(this).val();
    });

    var get_categories = '';

    var brands_filter = $('input[name="brands_filter"]').val();
    if (brands_filter) {
        url = 'filter_brands';
        $('input[name="category"]:checkbox:checked').each(function () {
            get_categories = get_categories + '&category%5B%5D=' + $(this).val();
        });

        sorder = $('select[name="sorder_brand"]').val();
        get_brands = '&brand%5B%5D=' + $('input[name="brands"]').val();
    }

    var bests_filter = $('input[name="bests_filter"]').val();
    if(bests_filter) {
        url = 'filter_bests';
        $('input[name="category"]:checkbox:checked').each(function () {
            get_categories = get_categories + '&category%5B%5D=' + $(this).val();
        });
    }


    sorder = 'sort=' + sorder;
    if (category_id) category_id = '&category_id=' + category_id; else category_id = '';
    if (price_min) price_min = '&price_min=' + price_min; else price_min = '';
    if (price_max) price_max = '&price_max=' + price_max; else price_max = '';

    var filter = sorder + category_id + price_min + price_max + get_brands + get_categories + get_sex;
    history.pushState({}, ' ', '?' + filter);

    $.ajax({
        url: '/' + lang + '/products/' + url + '?' + filter, // путь к обработчику
        type: 'POST', // метод отправки
        dataType: 'json',
        data: {filter: filter},
        success: function (data) {
            $('.products_catalog').html(data.html);
            $('.head-main-catalog__counts').html(data.count);
            $('.head-catalog__counts').html(data.count);
        },
        error: function (data) {
            console.log(data); // выводим ошибку в консоль
        }
    });
    return false;
}

$('body.catalog_page').on('click', '.head-main-catalog__select .select .select__options .select__option', function (e) {
    filter_products();
});
$('body.catalog_page').on('click', '.category_sorder .select .select__options .select__option', function (e) {
    filter_products();
});
$('body.catalog_page').on('change', 'input[name="brands"]', function (e) {
    filter_products();
});
$('body.catalog_page').on('change', 'input[name="sex"]', function (e) {
    filter_products();
});
$('body.catalog_page').on('change', 'input[name="category"]', function (e) {
    filter_products();
});
$('body.catalog_page').on('change', 'input[name="price-start"], input[name="price-end"]', function (e) {
    filter_products();
});
$('body.catalog_page').on('click', '.noUi-handle', function (e) {
    filter_products();
});

function filter_offers_products() {
    var lang = $('html').attr('lang');
    var sorder = $('select[name="sorder"]').val();
    var offer_id = $('input[name="offer_id"]').val();
    var price_min = $('input[name="price-start"]').val();
    var price_max = $('input[name="price-end"]').val();
    var get_brands = '';
    $('input[name="brands"]:checkbox:checked').each(function () {
        get_brands = get_brands + '&brand%5B%5D=' + $(this).val();
    });
    sorder = 'sort=' + sorder;
    if (offer_id) offer_id = '&offer_id=' + offer_id; else offer_id = '';
    if (price_min) price_min = '&price_min=' + price_min; else price_min = '';
    if (price_max) price_max = '&price_max=' + price_max; else price_max = '';

    var filter = sorder + offer_id + price_min + price_max + get_brands;
    history.pushState({}, ' ', '?' + filter);

    $.ajax({
        url: '/' + lang + '/products/filter_offer?' + filter, // путь к обработчику
        type: 'POST', // метод отправки
        dataType: 'json',
        data: {filter: filter},
        success: function (data) {
            $('.products_catalog').html(data.html);
            $('.head-main-catalog__counts').html(data.count);
            $('.head-catalog__counts').html(data.count);
        },
        error: function (data) {
            console.log(data); // выводим ошибку в консоль
        }
    });
    return false;
}

$('body.offers').on('click', '.head-main-catalog__select .select .select__options .select__option', function (e) {
    filter_offers_products();
});
$('body.offers').on('change', 'input[name="brands"]', function (e) {
    filter_offers_products();
});
$('body.offers').on('change', 'input[name="price-start"], input[name="price-end"]', function (e) {
    filter_offers_products();
});
$('body.offers').on('click', '.noUi-handle', function (e) {
    filter_offers_products();
});



/* Niche sale brand drawer */
;(function () {
	'use strict'

	var drawer = document.querySelector('[data-niche-brand-drawer]')

	var openButton = document.querySelector('[data-niche-brand-open]')

	if (!drawer || !openButton) {
		return
	}

	var closeButtons = drawer.querySelectorAll('[data-niche-brand-close]')

	var lastFocusedElement = null

	function openDrawer() {
		lastFocusedElement = document.activeElement

		drawer.classList.add('is-open')
		drawer.setAttribute('aria-hidden', 'false')
		openButton.setAttribute('aria-expanded', 'true')

		document.body.classList.add('niche-sale-drawer-open')

		var firstOption = drawer.querySelector('.niche-sale__drawer-option')

		if (firstOption) {
			firstOption.focus()
		}
	}

	function closeDrawer() {
		drawer.classList.remove('is-open')
		drawer.setAttribute('aria-hidden', 'true')
		openButton.setAttribute('aria-expanded', 'false')

		document.body.classList.remove('niche-sale-drawer-open')

		if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
			lastFocusedElement.focus()
		}
	}

	openButton.addEventListener('click', function () {
		if (drawer.classList.contains('is-open')) {
			closeDrawer()
		} else {
			openDrawer()
		}
	})

	closeButtons.forEach(function (button) {
		button.addEventListener('click', closeDrawer)
	})

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape' && drawer.classList.contains('is-open')) {
			closeDrawer()
		}
	})
})()


