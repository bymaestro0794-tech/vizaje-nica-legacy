;(function (window, document, $) {
    'use strict'

    var lastPendingKey = ''
    var isOpening = false

    function getLanguage() {
        var value = String(document.documentElement.lang || '').toLowerCase()
        return value.indexOf('ro') === 0 ? 'ro' : 'ru'
    }

    function handle(promotions) {
        if (!Array.isArray(promotions) || !promotions.length) {
            lastPendingKey = ''
            return
        }

        var item = promotions[0] || {}
        var promotionId = Number(item.promotion_id) || 0
        var productId = Number(item.product_id) || 0
        var key = promotionId + ':' + productId

        if (!promotionId || !productId || isOpening || key === lastPendingKey) {
            return
        }

        if (!window.VNQuickView || typeof window.VNQuickView.open !== 'function') {
            return
        }

        lastPendingKey = key
        isOpening = true
        window.VNQuickView.open(productId, {
            promotionId: promotionId,
            title: item.title || '',
            progressMode: item.progress_mode || 'time',
            dateTo: item.date_to || '',
            remainingStock: Number(item.remaining_stock) || 0
        })

        window.setTimeout(function () {
            isOpening = false
        }, 350)
    }

    window.VNGiftPromotions = {
        handle: handle
    }

    if ($) {
        $(document).ajaxComplete(function (_event, xhr, settings) {
            var url = String(settings && settings.url || '')
            if (!/\/cart_add$|\/cart\/update_cart$|\/cart\/delete$|\/gift\/promotion\/add$/.test(url)) {
                return
            }

            var response = xhr.responseJSON
            if (!response) {
                try {
                    response = JSON.parse(xhr.responseText || '{}')
                } catch (error) {
                    response = null
                }
            }

            if (response && response.gift_promotions) {
                handle(response.gift_promotions)
            }
        })
    }
}(window, document, window.jQuery))
