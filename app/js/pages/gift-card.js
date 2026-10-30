(function () {
    'use strict'

    const page = document.querySelector('[data-gift-card-form]')

    if (!page) {
        return
    }

    const amountButtons = Array.from(
        page.querySelectorAll('[data-gift-card-amount]')
    )
    const quantityOutput = page.querySelector('[data-gift-card-quantity]')
    const totalOutput = page.querySelector('[data-gift-card-total]')
    const previewOutput = document.querySelector('[data-gift-card-preview]')
    const cardImage = document.querySelector('[data-gift-card-card-image]')
    const scene = document.querySelector('[data-gift-card-scene]')
    const cardMotion = document.querySelector('.gift-card-page__card-motion')
    const message = page.querySelector('[data-gift-card-message]')
    const submit = page.querySelector('[type="submit"]')
    const cartCounts = Array.from(document.querySelectorAll('[data-gift-card-cart-count]'))
    const productId = Number(page.dataset.productId || 0)
    const confirmationText = page.dataset.confirmationText || 'Добавлено в корзину'
    const errorText = page.dataset.errorText || 'Не удалось добавить карту в корзину.'
    const submitText = page.dataset.submitText || 'Добавить в корзину'
    let amount = Number(amountButtons[0]?.dataset.giftCardAmount || 200)
    let quantity = 1
    let variationId = Number(amountButtons[0]?.dataset.giftCardVariation || 0)

    function formatAmount(value) {
        return value.toLocaleString('ru-RU').replace(/\u00a0/g, ' ') + ' MDL'
    }

    function render() {
        amountButtons.forEach(function (button) {
            const isSelected = Number(button.dataset.giftCardAmount) === amount
            button.classList.toggle('is-selected', isSelected)
            button.setAttribute('aria-pressed', isSelected ? 'true' : 'false')
        })

        if (quantityOutput) {
            quantityOutput.textContent = String(quantity)
        }

        if (totalOutput) {
            totalOutput.textContent = formatAmount(amount * quantity)
        }

        if (previewOutput) {
            previewOutput.textContent = formatAmount(amount)
        }

        const selectedButton = amountButtons.find(function (button) {
            return Number(button.dataset.giftCardAmount) === amount
        })
        const nextImage = selectedButton?.dataset.giftCardImage

        if (cardImage && nextImage && cardImage.getAttribute('src') !== nextImage) {
            cardImage.setAttribute('src', nextImage)

            if (cardMotion) {
                cardMotion.classList.remove('is-changing')
                void cardMotion.offsetWidth
                cardMotion.classList.add('is-changing')
                window.setTimeout(function () {
                    cardMotion.classList.remove('is-changing')
                }, 520)
            }
        }
    }

    amountButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            amount = Number(button.dataset.giftCardAmount)
            variationId = Number(button.dataset.giftCardVariation || 0)
            if (message) {
                message.textContent = ''
            }
            if (submit) {
                submit.classList.remove('is-success')
                submit.textContent = submitText
            }
            render()
        })
    })

    const decrease = page.querySelector('[data-gift-card-decrease]')
    const increase = page.querySelector('[data-gift-card-increase]')

    if (decrease) {
        decrease.addEventListener('click', function () {
            quantity = Math.max(1, quantity - 1)
            render()
        })
    }

    if (increase) {
        increase.addEventListener('click', function () {
            quantity = Math.min(10, quantity + 1)
            render()
        })
    }

    page.addEventListener('submit', function (event) {
        event.preventDefault()

        if (!productId || !variationId || !submit) {
            if (message) {
                message.textContent = errorText
            }
            return
        }

        submit.disabled = true
        submit.classList.add('is-loading')

        fetch('/cart_add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                product_id: String(productId),
                variable: String(variationId),
                quantity: String(quantity)
            })
        })
            .then(function (response) {
                return response.json()
            })
            .then(function (data) {
                if (!data || data.status !== 'ok') {
                    throw new Error(data && data.message ? data.message : errorText)
                }

                cartCounts.forEach(function (count) {
                    const totalItems = Number(data.total_items || 0)
                    count.textContent = String(totalItems)
                    count.hidden = totalItems < 1
                })

                if (typeof window.updateHeaderCartCount === 'function') {
                    window.updateHeaderCartCount(data.total_items || 0)
                }

                if (message) {
                    message.textContent = confirmationText + ': ' + formatAmount(amount) + ' × ' + quantity + '.'
                }

                submit.classList.add('is-success')
                submit.textContent = confirmationText
            })
            .catch(function (error) {
                if (message) {
                    message.textContent = error.message || errorText
                }
            })
            .finally(function () {
                submit.disabled = false
                submit.classList.remove('is-loading')
            })
    })

    if (scene && window.matchMedia('(hover: hover)').matches) {
        scene.addEventListener('pointermove', function (event) {
            const bounds = scene.getBoundingClientRect()
            const x = ((event.clientX - bounds.left) / bounds.width - 0.5) * 2
            const y = ((event.clientY - bounds.top) / bounds.height - 0.5) * 2
            scene.style.setProperty('--gift-card-pointer-x', String(x.toFixed(3)))
            scene.style.setProperty('--gift-card-pointer-y', String(y.toFixed(3)))
        })

        scene.addEventListener('pointerleave', function () {
            scene.style.setProperty('--gift-card-pointer-x', '0')
            scene.style.setProperty('--gift-card-pointer-y', '0')
        })
    }

    render()
})()
