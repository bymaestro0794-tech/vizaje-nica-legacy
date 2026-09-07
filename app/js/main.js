

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

$('body').on('submit', '#subscription', function (e) {
    e.preventDefault();
    var lang = $('html').attr('lang');
    let subscribe = $('.subscribe__input').val();
    $.ajax({
        url: '/' + lang + '/subscribe', // путь к обработчику
        type: 'POST', // метод отправки,
        dataType: 'json',
        data: {subscribe: subscribe, lang: lang},
        success: function (data) {
            $('.info_subscription').html(data.info);
        },
        error: function (data) {
            console.log(data); // выводим ошибку в консоль
        }
    });
});

$(".add_w").on("click", function () {
    var product_id = $(this).parent().parent().parent().parent().data('prod_id');
    var button = $(this);
    $.ajax({
        url: '/wishlist_add', // путь к обработчику
        type: 'POST', // метод отправки,
        dataType: 'json',
        data: {product_id: product_id},
        success: function (data) {
            if (data.count) {
                $('.w_count').html(data.count);
            } else {
                $('.w_count').html('0');
            }
        },
        error: function (data) {
            $('#head_wishlist').addClass('color_red');
        }
    });
});
$(".actions-info-card__like").on("click", function () {
    var product_id = $(this).parent().data('prod_id');
    $.ajax({
        url: '/wishlist_add', // путь к обработчику
        type: 'POST', // метод отправки,
        dataType: 'json',
        data: {product_id: product_id},
        success: function (data) {
            if (data.count) {
                $('.w_count').html(data.count);
            } else {
                $('.w_count').html('0');
            }
        },
        error: function (data) {
            $('#head_wishlist').addClass('color_red');
        }
    });
});
$("a.actions-other-products-cart__item._like").on("click", function () {
    var product_id = $(this).parent().data('prod_id');
    $.ajax({
        url: '/wishlist_add', // путь к обработчику
        type: 'POST', // метод отправки,
        dataType: 'json',
        data: {product_id: product_id},
        success: function (data) {
            if (data.count) {
                $('.w_count').html(data.count);
            } else {
                $('.w_count').html('0');
            }
        },
        error: function (data) {
            $('#head_wishlist').addClass('color_red');
        }
    });
});

$('body').on('click', '.add_cart', function (e) {
    // e.preventDefault();
    var button = $(this);
    var product_id = button.parent().parent().parent().parent().data('prod_id');
    var quantity = 1;
    $('#loadBox').removeClass('d_none');

    $.ajax({
        url: '/cart_add', // путь к обработчику
        type: 'POST', // метод отправки,
        dataType: 'json',
        data: {product_id: product_id, quantity: quantity},
        success: function (data) {
            if (data.status == 'ok') {
                $('.actions-main-header__counts').html(data.total_items);
                setTimeout(function run() {
                    $('#loadBox').addClass('d_none');
                }, 500);
            }
        },
        error: function (data) {
            console.log(data); // выводим ошибку в консоль
        }
    });
});
$('body').on('click', '.actions-info-card__cart', function (e) {
    e.preventDefault();
    var button = $(this);
    var product_id = button.parent().data('prod_id');
    var variable = $('input[name="variable"]:checked').val();
    var choose_option = $(this).closest('.card__info').find('.switch-info-cart');
    console.log(choose_option.length);
    if(variable == null && choose_option.length > 0){
        choose_option.addClass('animated shake');
                 setTimeout(function(){
                     choose_option.removeClass('animated shake');
                 }, 1000);
                 $('.select_variation').removeClass('d_none');
                 $('.select_variation').addClass('animated shake');
                 setTimeout(function () {
                     $('.select_variation').addClass('d_none');
                     $('.select_variation').removeClass('animated shake');
                 }, 1500);
    }else{
    var quantity = 1;
    $('#loadBox').removeClass('d_none');
    $.ajax({
        url: '/cart_add', // путь к обработчику
        type: 'POST', // метод отправки,
        dataType: 'json',
        data: {product_id: product_id, quantity: quantity, variable: variable},
        success: function (data) {
            if (data.status == 'ok') {
                $('.actions-main-header__counts').html(data.total_items);
                setTimeout(function run() {
                    $('#loadBox').addClass('d_none');
                }, 500);
            }
        },
        error: function (data) {
            console.log(data); // выводим ошибку в консоль
        }
    });
    }
});

$('body').on('click', '.quantity__button_plus', function (e) {
    var row = $(this).parent().parent().parent().parent();
    var input = $(this).parent().find('input[name="counts"]');
    var rowid = input.data('rowid');
    var quantity = input.val();
    var lang = $('html').attr('lang');
    $.ajax({
        url: '/cart/update_cart', // путь к обработчику
        type: 'POST', // метод отправки,
        dataType: 'json',
        data: {rowid: rowid, quantity: quantity, lang: lang},
        success: function (data) {
            if (data.status == 'ok') {
                $('.actions-main-header__counts').html(data.total_items);

                $('.items_count').html(data.total_items);
                $('.items_total').html(data.total);
                $('.delivery_price').html(data.delivery_price);
                $('._bonus .list-main-cart__value').html('+' + data.bonuses);

                row.find('.item-total').html(data.total_price);
                row.find('input[name="counts"]').html(data.product_qty);
            }
        },
        error: function (data) {
            console.log(data); // выводим ошибку в консоль
        }
    });
});

$('body').on('click', '.quantity__button_minus', function (e) {
    var row = $(this).parent().parent().parent().parent();
    var input = $(this).parent().find('input[name="counts"]');
    var rowid = input.data('rowid');
    var quantity = input.val();
    var lang = $('html').attr('lang');
    $.ajax({
        url: '/cart/update_cart', // путь к обработчику
        type: 'POST', // метод отправки,
        dataType: 'json',
        data: {rowid: rowid, quantity: quantity, lang: lang},
        success: function (data) {
            if (data.status == 'ok') {
                $('.actions-main-header__counts').html(data.total_items);

                $('.items_count').html(data.total_items);
                $('.items_total').html(data.total);
                $('.delivery_price').html(data.delivery_price);
                $('._bonus .list-main-cart__value').html('+' + data.bonuses);

                row.find('input[name="counts"]').html(data.product_qty);
                row.find('.item-total').html(data.total_price);
            }
        },
        error: function (data) {
            console.log(data); // выводим ошибку в консоль
        }
    });
});

$('body').on('click', '._delete', function (e) {
    e.preventDefault();
    var button = $(this);
    var rowid = button.parent().data('rowid');
    var info = button.parent().data('info');
    var lang = $('html').attr('lang');
    let prompt = window.confirm(info);
    console.log(info);
    if (prompt) {
        $.ajax({
            url: '/cart/delete', // путь к обработчику
            type: 'POST', // метод отправки,
            dataType: 'json',
            data: {rowid: rowid, lang: lang},
            success: function (data) {
                if (data.status == 'ok') {
                    button.parent().parent().parent().remove();
                    $('.actions-main-header__counts').html(data.total_items);
                    $('.items_count').html(data.total_items);
                    $('.delivery_price').html(data.delivery_price);
                    $('.items_total').html(data.total);
                    $('._bonus .list-main-cart__value').html('+' + data.bonuses);
                }
            },
            error: function (data) {
                console.log(data); // выводим ошибку в консоль
            }
        });
    } else {
        return false;
    }
});


$('body').on('click', '.select_shop', function (e) {
    e.preventDefault();
    var button = $(this);
    var shop_id = button.data('shop_id');
    var title = $('#store_' + shop_id).find('.map-order-list__name').text();
    var address = $('#store_' + shop_id).find('.map-order-list__address').text();
    $('.delivery-main-order__description').html(title);
    $('.stores_title').html(title);
    $('.stores_address').html(address);
    $('.stores_id').val(shop_id);
});

$('body').on('click', '.save_address', function (e) {
    e.preventDefault();
    var city = $('.add-address__form').find('input[name="city"]').val();
    var ciporchty = $('.add-address__form').find('input[name="porch"]').val();
    var house = $('.add-address__form').find('input[name="house"]').val();
    var address = $('.add-address__form').find('input[name="address"]').val();
    var apartament = $('.add-address__form').find('input[name="apartament"]').val();
    address = city + '' + address + ' ' + house + ' ' + ciporchty + ' ' + apartament;
    $('.delivery_address').html(address);
    $('.address').val(address);
});

$('body').on('click', '.order_send', function (e) {
    var scroll = false;
    var delivery = $('input[name="delivery"]:checked').val();
    if (!delivery) {
        $('.delivery_section').addClass('error');
        if (!scroll) {
            scroll = '.delivery_section';
        }
    } else {
        $('.delivery_section').removeClass('error');
    }

    var phone = $('input[name="phone"]').val().split('');
    phone = phone[7];
    if (phone == 0) {
        $('input[name="phone"]').val('+(373) ');
        if (!scroll) {
            scroll = '.info-order';
        }
    }

    var pay = $('input[name="pay"]:checked').val();
    if (!pay) {
        $('.pay_section').addClass('error');
        if (!scroll) {
            scroll = '.pay_section';
        }
    } else {
        $('.pay_section').removeClass('error');
    }

    var check_on = $('input[name="checkbox"]:checked').val();
    if (!check_on) {
        $('.check_on').addClass('error');
        if (!scroll) {
            scroll = '.check_on';
        }
    } else {
        $('.check_on').removeClass('error');
    }

    if (scroll) {
        scrollTO(scroll);
    }

    function scrollTO(element) {
        $('html, body').animate({
            scrollTop: $(element).offset().top - 150 // класс объекта к которому приезжаем
        }, 500);
    };

    if (!delivery || !pay || !check_on) {
        return false;
    }
});
$('body').on('change', 'input[name="delivery"]', function (e) {
    var delivery = $('input[name="delivery"]:checked').val();
    if (delivery == 2) {
        var del_info = $('.delivery-info').data('del');
        $('.delivery-info').html(del_info);
        $('.address_delivery_block').show();
        $('._total span.sidebar-order__value').html($('._total span.sidebar-order__value').data('del'));
        $('._total.bonus_minus_total span.sidebar-order__value').html($('._total.bonus_minus_total span.sidebar-order__value').data('del'));
        $('input[name="city"]').prop('required', true);
        $('input[name="address"]').prop('required', true);
        $('input[name="house"]').prop('required', true);
        $('input[name="porch"]').prop('porch', true);
    } else if (delivery == 3) {
        var del_info = $('.delivery-info').data('delday');
        $('.delivery-info').html(del_info);
        $('._total span.sidebar-order__value').html($('._total span.sidebar-order__value').data('delday'));
        $('._total.bonus_minus_total span.sidebar-order__value').html($('._total.bonus_minus_total span.sidebar-order__value').data('delday'));
        $('.address_delivery_block').show();
        $('input[name="city"]').prop('required', true);
        $('input[name="address"]').prop('required', true);
        $('input[name="house"]').prop('required', true);
        $('input[name="porch"]').prop('porch', true);
    } else {
        var del_info = $('.delivery-info').data('free');
        $('.delivery-info').html(del_info);
        $('._total span.sidebar-order__value').html($('._products span.sidebar-order__value').text());
        $('._total.bonus_minus_total span.sidebar-order__value').html($('._total.bonus_minus_total span.sidebar-order__value').data('free'));
        $('.address_delivery_block').hide();
        $('input[name="city"]').prop('required', false);
        $('input[name="address"]').prop('required', false);
        $('input[name="house"]').prop('required', false);
        $('input[name="porch"]').prop('porch', false);
    }
});

$('body').on('change', 'input.bonus_plus', function (e) {
    $('.sidebar-order__row._total').show();
    $('.bonus_minus_total').hide();
    $('.bonus_minus_info').hide();
    $('.sidebar-order__row._bonus').show();
});
$('body').on('change', 'input.bonus_minus', function (e) {
    $('.sidebar-order__row._total').hide();
    $('.bonus_minus_total').show();
    $('.bonus_minus_info').show();
    $('.sidebar-order__row._bonus').hide();
});

$('body').on('submit', '.register_form', function (e) {
    e.preventDefault();
    var lang = $('html').attr('lang');
    let data = $(this).serialize();
    if (data) {
        $.ajax({
            url: '/' + lang + '/registration_form', // путь к обработчику
            type: 'POST', // метод отправки,
            dataType: 'json',
            data: data,
            success: function (data) {
                $('.success_login').html();
                $('.error_login').html();
                if (data.success) {
                    $('.success_login').html(data.success);
                }
                if (data.error) {
                    $('.error_login').html(data.error);
                }
                if (data.code_auth){
                        $('.popup_register').removeClass('_active');
                        $('.popup_logincode ').addClass('_active');
                        $('.login_formcode input[name="number"]').parent().addClass('_focus');
                        $('.login_formcode input[name="number"]').addClass('_focus');
                        $('.login_formcode input[name="number"]').attr('readonly', true);
                        $('.sms-cod').show();
                        $('.error_login').html('');
                        $('.login_formcode input[name="number"]').val(data.number);
                }
            },
            error: function (data) {
                console.log(data); // выводим ошибку в консоль
            }
        });
    } else {
        return false;
    }
});

$('body').on('submit', '.login_formcode', function (e) {
    e.preventDefault();
    var lang = $('html').attr('lang');
    let data = $(this).serialize();
    if (data) {
        $.ajax({
            url: '/' + lang + '/login_form_code', // путь к обработчику
            type: 'POST', // метод отправки,
            dataType: 'json',
            data: data,
            success: function (data) {
                if (data.success) {
                    window.location.href = "/ro/contul-meu";
                    // location.reload();
                }
                if (data.error) {
                    $('.error_login').html(data.error);
                }
                if (data.code_auth){
                    $('.login_form input[name="number"]').parent().addClass('_focus');
                    $('.login_form input[name="number"]').addClass('_focus');
                    $('.login_form input[name="number"]').attr('readonly', true);
                    $('.sms-cod').show();
                    $('.login_form input[name="number"]').val(data.number);
                }
            },
            error: function (data) {
                console.log(data); // выводим ошибку в консоль
            }
        });
    } else {
        return false;
    }
});

$('body').on('submit', '.login_form', function (e) {
    e.preventDefault();
    var lang = $('html').attr('lang');
    let data = $(this).serialize();
    if (data) {
        $.ajax({
            url: '/' + lang + '/login_form', // путь к обработчику
            type: 'POST', // метод отправки,
            dataType: 'json',
            data: data,
            success: function (data) {
                if (data.success) {
                    location.reload();
                }
                if (data.error) {
                    $('.error_login').html(data.error);
                }
                if (data.code_auth){
                    $('.login_form input[name="number"]').parent().addClass('_focus');
                    $('.login_form input[name="number"]').addClass('_focus');
                    $('.login_form input[name="number"]').attr('readonly', true);
                    $('.sms-cod').show();
                    $('.login_form input[name="number"]').val(data.number);
                }
            },
            error: function (data) {
                console.log(data); // выводим ошибку в консоль
            }
        });
    } else {
        return false;
    }
});

$('body').on('submit', '.reset_form', function (e) {
    e.preventDefault();
    var lang = $('html').attr('lang');
    let data = $(this).serialize();
    if (data) {
        $.ajax({
            url: '/' + lang + '/reset_form', // путь к обработчику
            type: 'POST', // метод отправки,
            dataType: 'json',
            data: data,
            success: function (data) {
                if (data.success) {
                    $('.success_reset').html(data.success);
                    $('.error_reset').html('');
                }
                if (data.error) {
                    $('.error_reset').html(data.error);
                }
            },
            error: function (data) {
                console.log(data); // выводим ошибку в консоль
            }
        });
    } else {
        return false;
    }
});

$('body').on('click', '.login_info span.span_login', function (e) {
    $('.popup.popup_login.login').addClass('_active');
});
$('body').on('click', '.login_info span.span_reg', function (e) {
    $('.popup.popup_register.login').addClass('_active');
});
$('body').on('click', '.login_info_ch span.span_login', function (e) {
    $('.popup.popup_login.login').addClass('_active');
});
$('body').on('click', '.login_info_ch span.span_reg', function (e) {
    $('.popup.popup_register.login').addClass('_active');
});

$('body').on('click', '.register_form button.under-login__btn._sumbit', function (e) {
    if (!($('input[name="register_check"]').is(':checked'))) {
        $('input[name="register_check"]').parent().addClass('error');
    } else {
        $('input[name="register_check"]').parent().removeClass('error');
    }
});

$('input[name="phone"]').on("keyup", function () {
    var error_val = $(this).val().split('');
    error_val = error_val[7];
    if (error_val == 0) {
        $(this).val('+(373) ');
    }
});

$('body').on('click', '.cookie_button button', function (e) {
    e.preventDefault();
    $.ajax({
        url: '/cookie_on', // путь к обработчику
        type: 'POST', // метод отправки
        dataType: 'json',
        data: {cookie_on: 1},
        success: function (data) {
            $('.cookie_on').remove();
        },
        error: function (data) {
            console.log(data); // выводим ошибку в консоль
        }
    });
});
