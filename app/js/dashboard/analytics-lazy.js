(function () {
    'use strict';

    var root = document.querySelector('[data-analytics-lazy]');
    if (!root) return;

    var content = root.querySelector('[data-analytics-content]');
    var loading = root.querySelector('[data-analytics-loading]');
    var error = root.querySelector('[data-analytics-error]');
    var rangeLabel = document.querySelector('[data-analytics-range-label]');
    var currentPeriod = root.dataset.period || 'current_month';
    var endpoint = (root.dataset.url || '').split('?')[0];
    var requestVersion = 0;

    function setPeriodButtons(period) {
        document.querySelectorAll('[data-analytics-period]').forEach(function (button) {
            var active = button.dataset.analyticsPeriod === period;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
    }

    function updatePageUrl(period) {
        if (!window.history || !window.history.replaceState) return;
        var url = new URL(window.location.href);
        url.searchParams.set('period', period);
        window.history.replaceState({}, '', url.toString());
    }

    function loadAnalytics(period) {
        if (!endpoint) return;
        var requestVersionForCall = ++requestVersion;
        currentPeriod = period;
        root.dataset.period = period;
        root.classList.add('is-loading');
        loading.hidden = false;
        error.hidden = true;
        setPeriodButtons(period);

        fetch(endpoint + '?period=' + encodeURIComponent(period), {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (response) {
                if (!response.ok) throw new Error('Analytics request failed');
                return response.json();
            })
            .then(function (result) {
                if (requestVersionForCall !== requestVersion) return;
                if (!result || result.ok !== true || typeof result.html !== 'string') {
                    throw new Error('Invalid analytics response');
                }
                content.innerHTML = result.html;
                if (rangeLabel && result.range_label) rangeLabel.textContent = result.range_label;
                error.hidden = true;
                updatePageUrl(period);
            })
            .catch(function () {
                if (requestVersionForCall === requestVersion) error.hidden = false;
            })
            .finally(function () {
                if (requestVersionForCall !== requestVersion) return;
                root.classList.remove('is-loading');
                loading.hidden = true;
            });
    }

    document.querySelectorAll('[data-analytics-period]').forEach(function (button) {
        button.addEventListener('click', function () {
            var nextPeriod = button.dataset.analyticsPeriod;
            if (nextPeriod && nextPeriod !== currentPeriod) loadAnalytics(nextPeriod);
        });
    });

    var retryButton = root.querySelector('[data-analytics-retry]');
    if (retryButton) {
        retryButton.addEventListener('click', function () {
            loadAnalytics(currentPeriod);
        });
    }

    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
            if (!entries.some(function (entry) { return entry.isIntersecting; })) return;
            observer.disconnect();
            loadAnalytics(currentPeriod);
        }, { rootMargin: '120px 0px' });
        observer.observe(root);
    } else {
        loadAnalytics(currentPeriod);
    }
})();
