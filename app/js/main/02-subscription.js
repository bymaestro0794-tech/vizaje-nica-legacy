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

