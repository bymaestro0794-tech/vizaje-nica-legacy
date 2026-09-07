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

