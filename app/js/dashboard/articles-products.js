(function () {
    'use strict';

    document.querySelectorAll('.article-product-picker').forEach(function (picker) {
        var search = picker.querySelector('[data-product-search]');
        var results = picker.querySelector('[data-product-results]');
        var language = picker.querySelector('[data-product-language]');
        var insertButton = picker.querySelector('[data-insert-product]');
        var selectedProduct = null;
        var timer = null;

        if (!search || !results || !language || !insertButton) return;

        search.addEventListener('input', function () {
            window.clearTimeout(timer);
            selectedProduct = null;
            insertButton.disabled = true;
            results.textContent = '';

            var query = search.value.trim();
            if (query.length < 2) return;

            timer = window.setTimeout(function () {
                fetch(picker.getAttribute('data-endpoint') + '?q=' + encodeURIComponent(query), {
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(function (response) {
                        if (!response.ok) throw new Error('Product search failed');
                        return response.json();
                    })
                    .then(function (products) {
                        results.textContent = '';
                        if (!products.length) {
                            results.textContent = 'Ничего не найдено';
                            return;
                        }

                        products.forEach(function (product) {
                            var button = document.createElement('button');
                            button.type = 'button';
                            button.className = 'btn btn-default';
                            button.style.margin = '0 6px 6px 0';
                            button.textContent = product.title + ' · ' + product.SKU + ' · #' + product.id;
                            button.addEventListener('click', function () {
                                selectedProduct = product;
                                insertButton.disabled = false;
                                results.querySelectorAll('button').forEach(function (item) {
                                    item.classList.remove('blue');
                                });
                                button.classList.add('blue');
                            });
                            results.appendChild(button);
                        });
                    })
                    .catch(function () {
                        results.textContent = 'Не удалось найти товар. Обновите страницу и попробуйте снова.';
                    });
            }, 250);
        });

        insertButton.addEventListener('click', function () {
            if (!selectedProduct) return;

            var textareaId = 'text' + language.value;
            var token = '[[ARTICLE_PRODUCT:' + selectedProduct.id + ']]';
            var editor = window.CKEDITOR && window.CKEDITOR.instances
                ? window.CKEDITOR.instances[textareaId]
                : null;

            if (editor) {
                editor.focus();
                editor.insertText(token);
                editor.fire('change');
                return;
            }

            var textarea = document.getElementById(textareaId);
            if (!textarea) return;

            var start = textarea.selectionStart;
            var end = textarea.selectionEnd;
            textarea.value = textarea.value.slice(0, start) + token + textarea.value.slice(end);
            textarea.selectionStart = textarea.selectionEnd = start + token.length;
            textarea.focus();
        });
    });
}());
