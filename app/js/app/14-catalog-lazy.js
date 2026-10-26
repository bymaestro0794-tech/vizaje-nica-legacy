;(function () {
    'use strict'

    var sentinel = document.querySelector('[data-catalog-lazy-sentinel]')
    var body = document.querySelector('.products_catalog .main-catalog__body')

    if (!sentinel || !body || !('IntersectionObserver' in window)) return

    var nextPage = parseInt(sentinel.getAttribute('data-next-page') || '0', 10)
    var totalPages = parseInt(sentinel.getAttribute('data-total-pages') || '0', 10)
    var loading = false
    var observer

    if (!nextPage || !totalPages || nextPage > totalPages) return

    function finish() {
        observer.disconnect()
        sentinel.remove()
    }

    function loadNextPage() {
        if (loading || nextPage > totalPages) return

        loading = true
        sentinel.classList.add('is-loading')

        var url = new URL(window.location.href)
        url.searchParams.set('page', String(nextPage))
        url.searchParams.set('catalog_partial', '1')

        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            },
            credentials: 'same-origin'
        })
            .then(function (response) {
                if (!response.ok) throw new Error('Catalog page request failed')
                return response.text()
            })
            .then(function (html) {
                var template = document.createElement('template')
                template.innerHTML = html

                if (!template.content.children.length) {
                    finish()
                    return
                }

                body.appendChild(template.content)
                nextPage += 1

                if (nextPage > totalPages) finish()
            })
            .catch(function (error) {
                // Keep the normal paginator available if a background request
                // fails; do not turn a lazy-load problem into a broken catalog.
                console.warn('[catalog-lazy]', error)
            })
            .finally(function () {
                loading = false
                sentinel.classList.remove('is-loading')
            })
    }

    observer = new IntersectionObserver(function (entries) {
        if (entries.some(function (entry) { return entry.isIntersecting })) {
            loadNextPage()
        }
    }, { rootMargin: '800px 0px' })

    observer.observe(sentinel)
})()
