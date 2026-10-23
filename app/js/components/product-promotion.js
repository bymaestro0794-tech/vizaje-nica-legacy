(function () {
    'use strict';

    var promotion = document.querySelector('[data-product-promotion]');
    if (!promotion) {
        return;
    }

    var timer = promotion.querySelector('[data-product-promotion-timer]');
    var deadline = Date.parse(promotion.getAttribute('data-product-promotion-deadline') || '');

    if (!timer || Number.isNaN(deadline)) {
        return;
    }

    var update = function () {
        var remaining = Math.max(0, deadline - Date.now());
        var totalSeconds = Math.floor(remaining / 1000);
        var days = Math.floor(totalSeconds / 86400);
        var hours = Math.floor((totalSeconds % 86400) / 3600);
        var minutes = Math.floor((totalSeconds % 3600) / 60);
        var seconds = totalSeconds % 60;
        var pad = function (value) { return String(value).padStart(2, '0'); };

        timer.textContent = days > 0
            ? days + ' д ' + pad(hours) + ':' + pad(minutes) + ':' + pad(seconds)
            : pad(hours) + ':' + pad(minutes) + ':' + pad(seconds);

        if (remaining <= 0) {
            window.clearInterval(interval);
        }
    };

    update();
    var interval = window.setInterval(update, 1000);
}());
