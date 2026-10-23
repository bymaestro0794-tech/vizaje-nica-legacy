(function () {
    'use strict';

    var page = document.querySelector('[data-blog-article]');
    if (!page) return;

    var progress = page.querySelector('[data-reading-progress]');
    var progressBar = page.querySelector('[data-reading-progress-bar]');
    var readingContent = page.matches('[data-reading-content]')
        ? page
        : page.querySelector('[data-reading-content]');
    var header = document.querySelector('.header[data-site-header]');
    var actions = page.querySelector('.blog-article-actions');
    var actionsOffsetTop = actions
        ? actions.getBoundingClientRect().top + window.pageYOffset
        : 0;

    function getVisibleHeaderBottom() {
        if (!header) return 0;

        if (window.innerWidth <= 991 && header.classList.contains('is-scrolling-down')) {
            return 0;
        }

        return Math.max(0, Math.round(header.getBoundingClientRect().bottom));
    }

    function updateProgress() {
        if (header && progress) {
            progress.style.top = getVisibleHeaderBottom() + 'px';
        }

        if (!readingContent || !progress || !progressBar) return;
        var rect = readingContent.getBoundingClientRect();
        var start = rect.top + window.pageYOffset;
        var total = Math.max(1, readingContent.offsetHeight - window.innerHeight);
        var value = Math.max(0, Math.min(100, ((window.pageYOffset - start) / total) * 100));
        progressBar.style.transform = 'scaleX(' + (value / 100) + ')';
        progress.setAttribute('aria-valuenow', String(Math.round(value)));
    }

    function updateFloatingActions() {
        if (!actions) return;

        var headerHeightVariable = window.innerWidth <= 991
            ? '--header-mobile-height'
            : '--header-desktop-height';
        var headerHeight = parseFloat(
            window.getComputedStyle(document.documentElement).getPropertyValue(headerHeightVariable)
        ) || (header ? header.getBoundingClientRect().height : 0);
        var threshold = actionsOffsetTop - headerHeight;
        actions.classList.toggle('is-floating', window.pageYOffset >= threshold);
    }

    var updateScheduled = false;
    function scheduleScrollUpdate() {
        if (updateScheduled) return;
        updateScheduled = true;

        window.requestAnimationFrame(function () {
            updateProgress();
            updateFloatingActions();
            updateScheduled = false;
        });
    }

    window.addEventListener('scroll', scheduleScrollUpdate, { passive: true });
    window.addEventListener('resize', function () {
        if (actions) {
            var wasFloating = actions.classList.contains('is-floating');
            if (wasFloating) actions.classList.remove('is-floating');
            actionsOffsetTop = actions.getBoundingClientRect().top + window.pageYOffset;
            if (wasFloating) actions.classList.add('is-floating');
        }
        scheduleScrollUpdate();
    });

    if (header && window.MutationObserver) {
        var headerObserver = new MutationObserver(scheduleScrollUpdate);
        headerObserver.observe(header, { attributes: true, attributeFilter: ['class'] });
        header.addEventListener('transitionend', scheduleScrollUpdate);
    }

    scheduleScrollUpdate();

    var likeButton = page.querySelector('[data-article-like]');
    if (likeButton) {
        likeButton.addEventListener('click', function () {
            if (likeButton.disabled) return;
            likeButton.disabled = true;

            var body = new URLSearchParams();
            var csrfName = likeButton.getAttribute('data-csrf-name');
            var csrfHash = likeButton.getAttribute('data-csrf-hash');
            if (csrfName && csrfHash) body.set(csrfName, csrfHash);

            fetch(likeButton.getAttribute('data-endpoint'), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: body.toString()
            })
                .then(function (response) {
                    if (!response.ok) throw new Error('Like request failed');
                    return response.json();
                })
                .then(function (result) {
                    likeButton.classList.toggle('is-liked', !!result.liked);
                    likeButton.setAttribute('aria-pressed', result.liked ? 'true' : 'false');
                    likeButton.querySelector('[data-like-count]').textContent = result.count;
                })
                .catch(function () {
                    showToast(page, page.getAttribute('data-lang') === 'ro'
                        ? 'Nu s-a putut salva aprecierea. Încercați din nou.'
                        : 'Не удалось сохранить отметку. Попробуйте ещё раз.');
                })
                .finally(function () {
                    likeButton.disabled = false;
                });
        });
    }

    var shareButton = page.querySelector('[data-article-share]');
    if (shareButton) {
        shareButton.addEventListener('click', function () {
            var shareData = {
                title: document.title,
                text: page.querySelector('h1') ? page.querySelector('h1').textContent.trim() : document.title,
                url: window.location.href
            };

            if (navigator.share) {
                navigator.share(shareData).catch(function () {});
                return;
            }

            copyShareUrl(shareData.url);
        });
    }

    function copyShareUrl(url) {
        var copiedMessage = page.getAttribute('data-lang') === 'ro'
            ? 'Link copiat'
            : 'Ссылка скопирована';

        function copyWithoutClipboardAPI() {
            var previousFocus = document.activeElement;
            var field = document.createElement('textarea');
            field.value = url;
            field.setAttribute('readonly', '');
            field.setAttribute('aria-hidden', 'true');
            field.style.cssText = 'position:fixed;top:0;left:-9999px;opacity:0;';
            document.body.appendChild(field);
            field.select();
            var copied = false;
            try {
                copied = document.execCommand('copy');
            } catch (error) {
                copied = false;
            }
            field.remove();
            if (previousFocus && previousFocus.focus) previousFocus.focus({ preventScroll: true });
            showToast(page, copied ? copiedMessage : url);
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url)
                .then(function () { showToast(page, copiedMessage); })
                .catch(copyWithoutClipboardAPI);
        } else {
            copyWithoutClipboardAPI();
        }
    }

    function showToast(root, message) {
        var toast = root.querySelector('[data-share-toast]');
        if (!toast) return;
        toast.textContent = message;
        toast.classList.add('is-visible');
        window.clearTimeout(toast._hideTimer);
        toast._hideTimer = window.setTimeout(function () {
            toast.classList.remove('is-visible');
        }, 2400);
    }
}());
