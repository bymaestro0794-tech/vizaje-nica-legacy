$('body').on('click', '.add_cart:not(.actions-info-card__cart)', function (e) {
	e.preventDefault()

	var button = $(this)
	var product = button.closest('[data-prod_id]')
	var productId = product.data('prod_id')
	var quantity = 1

	var analyticsItem = null

	if (
		window.VNAnalytics &&
		typeof window.VNAnalytics.getCurrentProductItem === 'function'
	) {
		analyticsItem = window.VNAnalytics.getCurrentProductItem()
	}

	if (!productId) {
		console.error('Cart add: productId not found')

		return
	}

	button.prop('disabled', true).addClass('is-loading')

	$.ajax({
		url: '/cart_add',
		type: 'POST',
		dataType: 'json',
		data: {
			product_id: productId,
			quantity: quantity
		},

		success: function (data) {
			if (data && data.status === 'ok') {
				if (analyticsItem && window.VNAnalytics) {
					analyticsItem.quantity = quantity

					window.VNAnalytics.push('add_to_cart', {
						currency: 'MDL',

						value: Number(analyticsItem.price) * quantity,

						items: [analyticsItem]
					})
				}

				updateHeaderCartCount(data.total_items)

				openCartDrawerAfterAdd(data)

				return
			}
		},

		error: function (xhr) {
			console.error('Cart add error:', xhr)

			// showCartErrorNotification('Не удалось связаться с сервером.')
		},

		complete: function () {
			button.prop('disabled', false).removeClass('is-loading')
		}
	})
})

function setProductCartLimitState(button) {
	button.prop('disabled', true).addClass('is-stock-limit').text('Уже в корзине')
}

function showCartLimitNotification(message) {
	if (window.VNNotify && typeof window.VNNotify.toast === 'function') {
		window.VNNotify.toast({
			type: 'warning',
			title: 'Товар уже в корзине',
			message: message || 'В корзине уже находится всё доступное количество.',
			duration: 4500
		})

		return
	}

	console.warn(message)
}

$('body').on('click', '.quantity__button_plus', function (e) {
	var row = $(this).parent().parent().parent().parent()
	var input = $(this).parent().find('input[name="counts"]')
	var rowid = input.data('rowid')
	var quantity = input.val()
	var lang = $('html').attr('lang')
	$.ajax({
		url: '/cart/update_cart', // путь к обработчику
		type: 'POST', // метод отправки,
		dataType: 'json',
		data: { rowid: rowid, quantity: quantity, lang: lang },
		success: function (data) {
			if (data.status == 'ok') {
				$('.actions-main-header__counts').html(data.total_items)

				$('.items_count').html(data.total_items)
				$('.items_total').html(data.total)
				$('.delivery_price').html(data.delivery_price)
				$('._bonus .list-main-cart__value').html('+' + data.bonuses)

				row.find('.item-total').html(data.total_price)
				row.find('input[name="counts"]').html(data.product_qty)
			}
		},
		error: function (data) {
			console.log(data) // выводим ошибку в консоль
		}
	})
})

$('body').on('click', '.quantity__button_minus', function (e) {
	var row = $(this).parent().parent().parent().parent()
	var input = $(this).parent().find('input[name="counts"]')
	var rowid = input.data('rowid')
	var quantity = input.val()
	var lang = $('html').attr('lang')
	$.ajax({
		url: '/cart/update_cart', // путь к обработчику
		type: 'POST', // метод отправки,
		dataType: 'json',
		data: { rowid: rowid, quantity: quantity, lang: lang },
		success: function (data) {
			if (data.status == 'ok') {
				$('.actions-main-header__counts').html(data.total_items)

				$('.items_count').html(data.total_items)
				$('.items_total').html(data.total)
				$('.delivery_price').html(data.delivery_price)
				$('._bonus .list-main-cart__value').html('+' + data.bonuses)

				row.find('input[name="counts"]').html(data.product_qty)
				row.find('.item-total').html(data.total_price)
			}
		},
		error: function (data) {
			console.log(data) // выводим ошибку в консоль
		}
	})
})

$('body').on('click', '._delete', function (e) {
	e.preventDefault()

	var button = $(this)
	var cartRow = button.closest('[data-cart-row]')
	var legacyRow = button.parent().parent().parent()

	var row = cartRow.length ? cartRow : legacyRow

	var rowid =
		button.closest('[data-rowid]').data('rowid') ||
		button.parent().data('rowid')

	var lang = $('html').attr('lang')

	var rowImage = row.find('img').first()
	var rowName = row
		.find('.main-cart__name, .item-main-cart__name, .cart-item__name')
		.first()

	var rowVariant = row
		.find(
			'.main-cart__variation, .item-main-cart__variation, .cart-item__variant'
		)
		.first()

	var rowPrice = row
		.find('.item-total, .main-cart__price, .cart-item__price')
		.first()

	var productData = {
		image:
			row.attr('data-product-image') ||
			rowImage.attr('src') ||
			rowImage.attr('data-src') ||
			'',

		name: row.attr('data-product-name') || rowName.text().trim() || '',

		variant: row.attr('data-product-variant') || rowVariant.text().trim() || '',

		price: row.attr('data-product-price') || rowPrice.text().trim() || ''
	}

	if (!window.VNNotify || typeof window.VNNotify.removeConfirm !== 'function') {
		console.error('VNNotify.removeConfirm не инициализирован')
		return
	}

	window.VNNotify.removeConfirm(productData, function () {
		button.prop('disabled', true).addClass('is-loading')

		$.ajax({
			url: '/cart/delete',
			type: 'POST',
			dataType: 'json',
			data: {
				rowid: rowid,
				lang: lang
			},

			success: function (data) {
				if (data && data.status === 'ok') {
					row.addClass('is-removing')

					setTimeout(function () {
						row.remove()
					}, 280)

					updateHeaderCartCount(data.total_items)

					$('.items_count').html(data.total_items)

					$('.delivery_price').html(data.delivery_price)

					$('.items_total').html(data.total)

					$('._bonus .list-main-cart__value').html('+' + data.bonuses)

					return
				}

				// showCartErrorNotification(
				// 	data && data.message ? data.message : 'Не удалось удалить товар.'
				// )
			},

			error: function (xhr) {
				console.error('Cart delete error:', xhr)

				// showCartErrorNotification('Не удалось связаться с сервером.')
			},

			complete: function () {
				button.prop('disabled', false).removeClass('is-loading')
			}
		})
	})
})
/**
 * Получает информацию о товаре из ближайшего HTML-контейнера.
 */
function getCartProductData(button, variable) {
	var product = button.closest('[data-prod_id]')

	if (!product.length) {
		return {
			image: '',
			brand: '',
			name: '',
			variant: variable || '',
			price: ''
		}
	}

	return {
		image: product.attr('data-product-image') || '',
		brand: product.attr('data-product-brand') || '',
		name: product.attr('data-product-name') || '',
		variant: variable || product.attr('data-product-variant') || '',
		price: product.attr('data-product-price') || ''
	}
}

/**
 * Обновляет количество товаров возле иконки корзины.
 */
function updateHeaderCartCount(totalItems) {
	$('.actions-main-header__counts').html(totalItems)

	/*
	 * Скрываем нулевое значение, если это соответствует текущему дизайну.
	 */
	$('.actions-main-header__counts').toggleClass(
		'd_none',
		Number(totalItems) <= 0
	)
}

/**
 * Показывает новое уведомление после успешного добавления.
 */
// function showCartAddedNotification(product, totalItems) {
// 	if (!window.VNNotify || typeof window.VNNotify.cartAdded !== 'function') {
// 		console.warn('VNNotify.cartAdded не инициализирован')
// 		return
// 	}

// 	window.VNNotify.cartAdded({
// 		image: product.image,
// 		brand: product.brand,
// 		name: product.name,
// 		variant: product.variant,
// 		price: product.price,
// 		count: Number(totalItems) || 0
// 	})
// }

/**
 * Показывает общую ошибку.
 */
// function showCartErrorNotification(message) {
// 	if (window.VNNotify && typeof window.VNNotify.toast === 'function') {
// 		window.VNNotify.toast({
// 			type: 'error',
// 			title: 'Не удалось добавить товар',
// 			message:
// 				message || 'Попробуйте повторить действие через несколько секунд.',
// 			duration: 6000
// 		})

// 		return
// 	}

// 	console.error(message || 'Ошибка добавления товара')
// }
function openCartDrawerAfterAdd(data) {
	console.log('[CART ADD RESPONSE]', data)

	if (window.VNCartDrawer && typeof window.VNCartDrawer.open === 'function') {
		window.VNCartDrawer.open({
			load: true,
			addedRowId: data && data.row_id ? data.row_id : null
		})

		return
	}

	console.warn('VNCartDrawer не инициализирован')
}
