$('.add_w').on('click', function () {
	var product_id = $(this).parent().parent().parent().parent().data('prod_id')
	var button = $(this)
	$.ajax({
		url: '/wishlist_add', // путь к обработчику
		type: 'POST', // метод отправки,
		dataType: 'json',
		data: { product_id: product_id },
		success: function (data) {
			if (data.count) {
				$('.w_count').html(data.count)
			} else {
				$('.w_count').html('0')
			}
		},
		error: function (data) {
			$('#head_wishlist').addClass('color_red')
		}
	})
})
$('body').on('click', '.actions-info-card__like', function (event) {
	event.preventDefault()

	const button = $(this)

	const productId =
		Number(button.attr('data-product-id')) ||
		Number(button.attr('data-prod_id'))

	if (!productId) {
		console.error('Wishlist: product ID not found')

		showWishlistError('Не удалось определить товар.')

		return
	}

	if (button.hasClass('is-loading')) {
		return
	}

	const wasLiked = button.hasClass('_liked')

	button.prop('disabled', true).addClass('is-loading')

	$.ajax({
		url: '/wishlist_add',
		type: 'POST',
		dataType: 'json',

		data: {
			product_id: productId
		},

		success: function (data) {
			if (!data) {
				showWishlistError('Сервер не подтвердил изменение избранного.')

				return
			}

			/*
			 * Текущий endpoint, судя по старому JS,
			 * гарантированно возвращает count.
			 *
			 * Если backend также возвращает liked/status,
			 * используем его. Иначе считаем, что endpoint
			 * переключает состояние.
			 */
			const isLiked =
				typeof data.liked !== 'undefined' ? Boolean(data.liked) : !wasLiked

			button.toggleClass('_liked', isLiked)

			button.attr('aria-pressed', isLiked ? 'true' : 'false')

			button.attr(
				'aria-label',
				isLiked ? 'Удалить из избранного' : 'Добавить в избранное'
			)

			updateWishlistCount(data.count)

			if (window.VNNotify && typeof window.VNNotify.toast === 'function') {
				window.VNNotify.toast({
					type: 'success',
					title: isLiked ? 'Добавлено в избранное' : 'Удалено из избранного',
					message: '',
					duration: 2800
				})
			}
		},

		error: function (xhr) {
			console.error('Wishlist request failed:', xhr)

			showWishlistError('Не удалось изменить избранное.')
		},

		complete: function () {
			button.prop('disabled', false).removeClass('is-loading')
		}
	})
})

function updateWishlistCount(count) {
	const normalizedCount = Math.max(0, Number(count) || 0)

	$('.w_count').text(normalizedCount)

	$('.w_count').toggleClass('d_none', normalizedCount <= 0)
}

function showWishlistError(message) {
	if (window.VNNotify && typeof window.VNNotify.toast === 'function') {
		window.VNNotify.toast({
			type: 'error',
			title: 'Не удалось изменить избранное',
			message: message || 'Попробуйте повторить действие.',
			duration: 5000
		})

		return
	}

	console.error(message)
}
$('a.actions-other-products-cart__item._like').on('click', function () {
	var product_id = $(this).parent().data('prod_id')
	$.ajax({
		url: '/wishlist_add', // путь к обработчику
		type: 'POST', // метод отправки,
		dataType: 'json',
		data: { product_id: product_id },
		success: function (data) {
			if (data.count) {
				$('.w_count').html(data.count)
			} else {
				$('.w_count').html('0')
			}
		},
		error: function (data) {
			$('#head_wishlist').addClass('color_red')
		}
	})
})
