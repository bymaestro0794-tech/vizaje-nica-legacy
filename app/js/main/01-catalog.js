

$('body').on('click', '.sorder_category .select__options .select__option', function (e) {
    e.preventDefault();
    let lang = $('html').attr('lang');
    var sort = $('#sorder_catalog').val();
    var page = $('input[name="page_catalog"]').val();
    var category_id = $('input[name="category_id"]').val();
    sort = 'sort=' + sort
    page = '&page=' + page;
    var filter = sort + page;
    history.pushState({}, ' ', '?' + filter);
    $.ajax({
        url: '/' + lang + '/products/category-parent?' + filter, // путь к обработчику
        type: 'POST', // метод отправки
        dataType: 'html',
        data: {category_id: category_id},
        success: function (data) {
            $('.products_items').html(data);
        },
        error: function (data) {
            console.log(data); // выводим ошибку в консоль
        }
    });
    return false;
});


$('body.catalog_page').on('click', '.brands_sorder .select .select__options .select__option', function (e) {
    e.preventDefault();
    let lang = $('html').attr('lang');
    var sort = $('select[name="sorder_brand"]').val();
    var brand_id = $('input[name="brand_id"]').val();
    var get_categories = '';
    let categories = [];
    $('input[name="category"]:checkbox:checked').each(function() {
        get_categories = get_categories + '&category%5B%5D=' + $(this).val();
        categories.push($(this).val());
    });

    sort = 'sort=' + sort + get_categories;
    var filter = sort;
    history.pushState({}, ' ', '?' + filter);
    $.ajax({
        url: '/' + lang + '/products/brands?' + filter, // путь к обработчику
        type: 'POST', // метод отправки
        dataType: 'json',
        data: {brand_id: brand_id, categories:categories},
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
});

$('body .wishlist').on('click', '.select .select__options .select__option', function (e) {
    e.preventDefault();
    let lang = $('html').attr('lang');
    var sort = $('select[name="sorder"]').val();
    sort = 'sort=' + sort
    var filter = sort;
    history.pushState({}, ' ', '?' + filter);
    $.ajax({
        url: '/' + lang + '/wishlist?' + filter, // путь к обработчику
        type: 'POST', // метод отправки
        dataType: 'json',
        data: {sort: sort},
        success: function (data) {
            $('.products_items').html(data.html);
        },
        error: function (data) {
            console.log(data); // выводим ошибку в консоль
        }
    });
    return false;
});

