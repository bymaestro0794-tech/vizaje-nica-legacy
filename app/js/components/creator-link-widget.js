(function () {
    'use strict';

    const locale = document.documentElement.lang === 'ro' ? 'ro' : 'ru';
    const copy = locale === 'ro' ? {
        loading: 'Se încarcă…', empty: 'Nu există încă date.', unavailable: 'Datele nu sunt disponibile momentan.',
        linksUnavailable: 'Linkurile nu sunt disponibile momentan.',
        created: 'Linkul a fost creat.',
        createFailed: 'Linkul nu a putut fi creat.', copied: 'Copiat', open: 'Deschide', copyLink: 'Copiază',
        qrTitle: 'Codul QR al linkului', qrDescription: 'Deschide sau salvează linkul scurt pentru publicul tău.', downloadPng: 'Descarcă PNG', qrAction: 'Arată codul QR', close: 'Închide', destination: 'Pagina țintă',
        linkErrors: { INVALID_LINK_INPUT: 'Verificați adresa: folosiți o pagină Vizaje-Nica în limba română sau rusă.', LINK_CREATE_FAILED: 'Linkul nu a putut fi salvat. Verificați baza de date și încercați din nou.', LINK_STORAGE_UNAVAILABLE: 'Stocarea linkurilor nu este disponibilă.', CSRF_INVALID: 'Sesiunea a expirat. Reîncărcați pagina.', CREATOR_ACCESS_REQUIRED: 'Este necesar accesul activ de creator.' },
        deleteLink: 'Șterge', deleteConfirm: 'Ștergeți linkul? Nu va mai funcționa, iar istoricul comenzilor și accesărilor va fi păstrat.',
        actionFailed: 'Acțiunea nu a putut fi finalizată.',
        types: { product: 'Produs', category: 'Categorie', brand: 'Brand', homepage: 'Pagina principală' },
        statuses: { active: 'Activ', revoked: 'Revocat', inactive: 'Inactiv' },
    } : {
        loading: 'Загружаем…', empty: 'Данных пока нет.', unavailable: 'Данные сейчас недоступны.',
        linksUnavailable: 'Ссылки сейчас недоступны.',
        created: 'Ссылка создана.',
        createFailed: 'Не удалось создать ссылку.', copied: 'Скопировано', open: 'Открыть', copyLink: 'Копировать',
        qrTitle: 'QR-код ссылки', qrDescription: 'Откройте или сохраните короткую ссылку для своей аудитории.', downloadPng: 'Скачать PNG', qrAction: 'Показать QR-код', close: 'Закрыть', destination: 'Целевая страница',
        linkErrors: { INVALID_LINK_INPUT: 'Проверьте адрес: укажите страницу Vizaje-Nica на русском или румынском языке.', LINK_CREATE_FAILED: 'Не удалось сохранить ссылку. Проверьте базу данных и попробуйте ещё раз.', LINK_STORAGE_UNAVAILABLE: 'Хранилище ссылок недоступно.', CSRF_INVALID: 'Сессия истекла. Обновите страницу.', CREATOR_ACCESS_REQUIRED: 'Нужен активный доступ к кабинету креатора.' },
        deleteLink: 'Удалить', deleteConfirm: 'Удалить ссылку? Она перестанет работать, а история переходов и заказов сохранится.',
        actionFailed: 'Не удалось выполнить действие.',
        types: { product: 'Товар', category: 'Категория', brand: 'Бренд', homepage: 'Главная' },
        statuses: { active: 'Активна', revoked: 'Отозвана', inactive: 'Неактивна' },
    };

    const widget = document.querySelector('[data-creator-link-widget]');
    const dialog = widget && widget.querySelector('[data-creator-widget-dialog]');
    const backdrop = widget && widget.querySelector('[data-creator-widget-backdrop]');
    const closeButton = widget && widget.querySelector('[data-creator-widget-close]');
    const accountRoot = document.querySelector('[data-creator-dashboard-root]');
    const roots = [accountRoot, widget].filter(Boolean);
    let qrModal;
    let widgetCloseTimer;

    function setMessage(node, text, error) {
        if (!node) return;
        node.textContent = text || '';
        node.classList.toggle('creator-account__state--error', Boolean(error));
    }

    function csrf(root) {
        return root ? (root.getAttribute('data-csrf-token') || '') : '';
    }

    async function postForm(url, fields, token) {
        const body = new URLSearchParams(fields);
        body.set('csrfToken', token || '');
        const response = await fetch(url, {
            method: 'POST', credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'Accept': 'application/json' },
            body,
        });
        const result = await response.json();
        if (!response.ok || !result || result.ok !== true) throw new Error((result && result.code) || 'request_failed');
        return result;
    }

    function ensureQrModal() {
        if (qrModal) return qrModal;
        qrModal = document.createElement('div');
        qrModal.className = 'creator-qr-modal';
        qrModal.hidden = true;
        qrModal.innerHTML = '<button class="creator-qr-modal__backdrop" type="button" data-qr-close></button>'
            + '<section class="creator-qr-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="creator-qr-title">'
            + '<header class="creator-qr-modal__header"><div><h2 id="creator-qr-title"></h2><p></p></div><button class="creator-qr-modal__close" type="button" data-qr-close>×</button></header>'
            + '<div class="creator-qr-modal__body"><div class="creator-qr-modal__image" data-qr-image></div><a class="creator-qr-modal__url" data-qr-url target="_blank" rel="noopener noreferrer"></a>'
            + '<button class="creator-qr-modal__download" type="button" data-qr-download></button></div></section>';
        document.body.appendChild(qrModal);
        qrModal.querySelectorAll('[data-qr-close]').forEach((button) => {
            button.setAttribute('aria-label', copy.close);
            button.addEventListener('click', hideQrModal);
        });
        qrModal.querySelector('[data-qr-download]').addEventListener('click', () => {
            const image = qrModal.querySelector('[data-qr-image] canvas, [data-qr-image] img');
            if (!image) return;
            const href = image.tagName === 'CANVAS' ? image.toDataURL('image/png') : image.src;
            const anchor = document.createElement('a');
            anchor.href = href;
            anchor.download = 'vizaje-creator-link-qr.png';
            document.body.appendChild(anchor);
            anchor.click();
            anchor.remove();
        });
        document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && !qrModal.hidden) hideQrModal(); });
        return qrModal;
    }

    function hideQrModal() {
        if (!qrModal) return;
        qrModal.classList.remove('is-open');
        window.setTimeout(() => { if (qrModal && !qrModal.classList.contains('is-open')) qrModal.hidden = true; }, 180);
        closeCreatorWidget();
    }

    function closeCreatorWidget() {
        if (!widget || !dialog || dialog.hidden) return;
        dialog.classList.remove('is-open');
        backdrop.classList.remove('is-open');
        document.documentElement.classList.remove('creator-widget-open');
        const trigger = widget.querySelector('[data-creator-widget-open]');
        window.clearTimeout(widgetCloseTimer);
        widgetCloseTimer = window.setTimeout(() => {
            if (dialog.classList.contains('is-open')) return;
            dialog.hidden = true;
            backdrop.hidden = true;
            if (trigger && !widget.hidden) trigger.focus();
        }, 240);
    }

    function showQrModal(link) {
        if (!window.QRCode || !link || !allowedStoreUrl(link.short_url)) return;
        const modal = ensureQrModal();
        const url = new URL(link.short_url, window.location.origin).href;
        modal.querySelector('#creator-qr-title').textContent = copy.qrTitle;
        modal.querySelector('.creator-qr-modal__header p').textContent = copy.qrDescription;
        modal.querySelector('[data-qr-download]').textContent = copy.downloadPng;
        const urlNode = modal.querySelector('[data-qr-url]');
        urlNode.href = url;
        urlNode.textContent = url;
        const image = modal.querySelector('[data-qr-image]');
        image.replaceChildren();
        new window.QRCode(image, { text: url, width: 280, height: 280, colorDark: '#111111', colorLight: '#ffffff', correctLevel: window.QRCode.CorrectLevel.M });
        modal.hidden = false;
        requestAnimationFrame(() => modal.classList.add('is-open'));
        modal.querySelector('[data-qr-close]').focus();
    }

    function linkErrorMessage(error) {
        return copy.linkErrors[error && error.message] || copy.createFailed;
    }

    function allowedStoreUrl(value) {
        if (typeof value === 'string' && /^\/r\/[A-Za-z0-9_-]{12}$/.test(value)) return true;
        try {
            const url = new URL(value);
            if (url.origin === window.location.origin && (/^\/(ru|ro)(\/|$)/.test(url.pathname)
                || /^\/r\/[A-Za-z0-9_-]{12}$/.test(url.pathname))
                && !url.username && !url.password && !url.hash) return true;
            const hosts = ['vizaje-nica.com', 'www.vizaje-nica.com', 'dev.vizaje-nica.com'];
            const local = url.hostname === 'vizaje-nica.local' || url.hostname === 'localhost';
            return (url.protocol === 'https:' && hosts.includes(url.hostname) && !url.port)
                || (url.protocol === 'http:' && local && (!url.port || url.port === '80'));
        } catch (_) { return false; }
    }

    function fullShortUrl(shortUrl) {
        return new URL(shortUrl, window.location.origin).href;
    }

    function appendDestination(container, targetUrl) {
        if (!targetUrl || !allowedStoreUrl(targetUrl)) return;
        const line = document.createElement('small');
        line.className = 'creator-account__link-destination';
        line.append(document.createTextNode(copy.destination + ': '));
        const destination = document.createElement('a');
        destination.href = targetUrl;
        destination.target = '_blank';
        destination.rel = 'noopener noreferrer';
        destination.textContent = targetUrl;
        line.appendChild(destination);
        container.appendChild(line);
    }

    function makeUuid() {
        if (window.crypto && typeof window.crypto.randomUUID === 'function') return window.crypto.randomUUID();
        const bytes = new Uint8Array(16);
        window.crypto.getRandomValues(bytes);
        bytes[6] = (bytes[6] & 15) | 64;
        bytes[8] = (bytes[8] & 63) | 128;
        const hex = Array.from(bytes, (byte) => byte.toString(16).padStart(2, '0')).join('');
        return `${hex.slice(0, 8)}-${hex.slice(8, 12)}-${hex.slice(12, 16)}-${hex.slice(16, 20)}-${hex.slice(20)}`;
    }

    function setLinkPane(root, state) {
        const table = root.querySelector('[data-creator-links-table]');
        if (table) table.hidden = state !== 'available';
        root.querySelectorAll('[data-creator-links-pane]').forEach((pane) => {
            pane.hidden = pane.getAttribute('data-creator-links-pane') !== state;
        });
        const widgetState = root.querySelector('[data-widget-links-state]');
        if (widgetState) {
            widgetState.hidden = state !== 'error';
            widgetState.textContent = copy.linksUnavailable;
        }
    }

    function renderSafeLinkList(container, links) {
        if (!container) return;
        container.replaceChildren();
        links.forEach((link) => {
            if (!link || !allowedStoreUrl(link.short_url)) return;
            const absoluteShortUrl = fullShortUrl(link.short_url);
            const row = document.createElement('article');
            row.className = 'creator-account__link-card';
            const heading = document.createElement('div'); heading.className = 'creator-account__link-card-heading';
            const identity = document.createElement('div'); identity.className = 'creator-account__link-card-identity';
            const type = document.createElement('span'); type.className = 'creator-account__link-type'; type.textContent = copy.types[link.type] || link.type;
            const title = document.createElement('strong'); title.textContent = link.title || copy.types[link.type] || link.type;
            const status = document.createElement('span'); status.className = 'creator-account__link-status'; status.textContent = copy.statuses[link.status] || link.status || '—';
            identity.append(type, title);
            heading.append(identity, status);
            const destination = document.createElement('div'); destination.className = 'creator-account__link-card-destination';
            const urlLabel = document.createElement('span'); urlLabel.textContent = locale === 'ro' ? 'Link scurt' : 'Короткая ссылка';
            const anchor = document.createElement('a');
            anchor.href = absoluteShortUrl;
            anchor.target = '_blank';
            anchor.rel = 'noopener noreferrer';
            anchor.textContent = absoluteShortUrl;
            destination.append(urlLabel, anchor);
            appendDestination(destination, link.target_url);
            const metrics = document.createElement('div'); metrics.className = 'creator-account__link-card-metrics';
            const addMetric = (label, value) => {
                const item = document.createElement('div'); item.className = 'creator-account__link-metric';
                const caption = document.createElement('span'); caption.textContent = label;
                const amount = document.createElement('strong'); amount.textContent = String(value);
                item.append(caption, amount); metrics.appendChild(item);
            };
            addMetric(locale === 'ro' ? 'Accesări' : 'Переходы', Number.isFinite(Number(link.clicks)) ? Number(link.clicks) : 0);
            addMetric(locale === 'ro' ? 'Comenzi finalizate' : 'Завершённые заказы', Number.isFinite(Number(link.orders)) ? Number(link.orders) : 0);
            const created = document.createElement('span'); created.className = 'creator-account__link-created';
            created.textContent = `${link.locale === 'ro' ? 'RO' : 'RU'} · ${link.created_at || '—'}`;
            const actions = document.createElement('div'); actions.className = 'creator-account__link-actions';
            const open = document.createElement('a'); open.href = absoluteShortUrl; open.target = '_blank'; open.rel = 'noopener noreferrer'; open.textContent = copy.open;
            const copyButton = document.createElement('button'); copyButton.type = 'button'; copyButton.textContent = copy.copyLink; copyButton.setAttribute('data-creator-copy', absoluteShortUrl);
            const qrButton = makeQrButton(absoluteShortUrl);
            actions.append(open, document.createTextNode(' '), copyButton, document.createTextNode(' '), qrButton);
            if (link.id && link.status !== 'revoked') {
                const remove = document.createElement('button'); remove.type = 'button'; remove.className = 'creator-account__link-delete'; remove.textContent = copy.deleteLink;
                remove.setAttribute('data-creator-delete', link.id);
                actions.appendChild(remove);
            }
            row.append(heading, destination, metrics, created, actions);
            container.appendChild(row);
        });
    }

    function makeQrButton(shortUrl) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'creator-account__qr-button';
        button.setAttribute('data-creator-qr', shortUrl);
        button.setAttribute('aria-label', copy.qrAction);
        button.title = copy.qrAction;
        button.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h3v3h-3zM19 14h2v2h-2zM19 19h2v2h-2zM14 19h2v2h-2zM17 17h2v2h-2z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M5.5 5.5h2v2h-2zM16.5 5.5h2v2h-2zM5.5 16.5h2v2h-2z" fill="currentColor"/></svg>';
        return button;
    }

    function updateDashboardUi(root, result) {
        root.setAttribute('data-can-create-links', result.canCreateLinks ? '1' : '0');
        root.querySelectorAll('[data-creator-link-submit]').forEach((button) => {
            button.disabled = !result.canCreateLinks;
        });
    }

    async function loadDashboard(root) {
        if (!root || root.getAttribute('data-dashboard-loading') === '1') return;
        root.setAttribute('data-dashboard-loading', '1');
        setLinkPane(root, 'loading');
        try {
            const response = await fetch(root.getAttribute('data-dashboard-url') || '/account/creator-link/dashboard', {
                credentials: 'same-origin', headers: { 'Accept': 'application/json' }, cache: 'no-store',
            });
            const result = await response.json();
            if (!response.ok || !result || result.ok !== true) throw new Error('dashboard_unavailable');
            updateDashboardUi(root, result);
            if (result.linksAvailable && Array.isArray(result.links)) {
                const rows = root.querySelector('[data-creator-links]');
                if (rows) {
                    renderSafeLinkList(rows, result.links);
                    setLinkPane(root, result.links.length ? 'available' : 'empty');
                }
            } else {
                setLinkPane(root, 'error');
            }
        } catch (_) {
            updateDashboardUi(root, { canCreateLinks: false });
            setLinkPane(root, 'error');
        } finally {
            root.removeAttribute('data-dashboard-loading');
        }
    }

    roots.forEach((root) => {
        root.querySelectorAll('[data-creator-link-form]').forEach((form) => {
            const targetInput = form.querySelector('[data-creator-link-target]');
            const preview = form.querySelector('[data-creator-link-preview]');
            if (targetInput && preview) {
                const updatePreview = () => { preview.textContent = targetInput.value || '—'; };
                targetInput.addEventListener('input', updatePreview);
                updatePreview();
            }
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                const submit = form.querySelector('[data-creator-link-submit]');
                const message = form.querySelector('[data-creator-create-message]');
                if (!submit || submit.disabled) return;
                submit.disabled = true;
                setMessage(message, copy.loading, false);
                const linkFields = {
                    targetUrl: form.querySelector('[data-creator-link-target]')?.value || '',
                    label: form.querySelector('[data-creator-link-title]')?.value || '',
                };
                const payloadFingerprint = JSON.stringify(linkFields);
                if (form.dataset.idempotencyPayload !== payloadFingerprint || !form.dataset.idempotencyKey) {
                    form.dataset.idempotencyKey = makeUuid();
                    form.dataset.idempotencyPayload = payloadFingerprint;
                }
                const editableFields = Array.from(form.querySelectorAll('input, select'));
                editableFields.forEach((field) => { field.disabled = true; });
                try {
                    const result = await postForm(form.getAttribute('data-create-url') || root.getAttribute('data-create-url') || '/account/creator-link/create', {
                        idempotencyKey: form.dataset.idempotencyKey,
                        ...linkFields,
                    }, form.getAttribute('data-csrf-token') || csrf(root));
                    setMessage(message, copy.created, false);
                    const link = result.link;
                    if (link && allowedStoreUrl(link.short_url)) {
                        const absoluteShortUrl = fullShortUrl(link.short_url);
                        const target = document.createElement('a');
                        target.href = absoluteShortUrl; target.target = '_blank'; target.rel = 'noopener noreferrer'; target.textContent = absoluteShortUrl;
                        message.replaceChildren(document.createTextNode(copy.created + ' '), target);
                        appendDestination(message, link.target_url);
                        showQrModal(link);
                    }
                    const title = form.querySelector('[data-creator-link-title]'); if (title) title.value = '';
                    delete form.dataset.idempotencyKey;
                    delete form.dataset.idempotencyPayload;
                    await loadDashboard(root);
                } catch (error) {
                    setMessage(message, linkErrorMessage(error), true);
                } finally {
                    editableFields.forEach((field) => { field.disabled = false; });
                    const canCreate = root.getAttribute('data-can-create-links') === '1';
                    submit.disabled = !canCreate;
                }
            });
        });


        root.addEventListener('click', async (event) => {
            const qrButton = event.target.closest('[data-creator-qr]');
            if (qrButton && root.contains(qrButton)) {
                showQrModal({ short_url: qrButton.getAttribute('data-creator-qr') });
                return;
            }
            const button = event.target.closest('[data-creator-delete]');
            if (button && root.contains(button)) {
                if (!window.confirm(copy.deleteConfirm)) return;
                button.disabled = true;
                try {
                    const prefix = root.getAttribute('data-revoke-prefix') || '/account/creator-link/revoke/';
                    await postForm(prefix + encodeURIComponent(button.getAttribute('data-creator-delete')), {}, csrf(root));
                    await loadDashboard(root);
                } catch (_) { button.disabled = false; setMessage(root.querySelector('[data-creator-action-message]'), copy.actionFailed, true); }
            }
        });
    });

    document.querySelectorAll('[data-payout-form]').forEach((form) => {
        const mode = form.querySelector('[data-payout-mode]');
        const amount = form.querySelector('[data-payout-amount]');
        const range = form.querySelector('[data-payout-range]');
        const slider = form.querySelector('[data-payout-slider]');
        const note = form.querySelector('[data-payout-amount-note]');
        if (!mode || !amount || !range || !slider) return;

        const fullAmount = Number(amount.max);
        const minimum = Number(amount.min) || 0.01;
        const syncRange = (value) => {
            const normalized = Math.min(fullAmount, Math.max(minimum, Number(value) || minimum));
            range.value = normalized.toFixed(2);
            const progress = fullAmount > minimum ? ((normalized - minimum) / (fullAmount - minimum)) * 100 : 100;
            range.style.setProperty('--payout-progress', `${progress}%`);
        };
        const setAmount = (value) => {
            const normalized = Math.min(fullAmount, Math.max(minimum, Number(value) || minimum));
            amount.value = normalized.toFixed(2);
            syncRange(normalized);
        };
        const updateMode = () => {
            const custom = mode.value === 'custom';
            amount.readOnly = !custom;
            slider.hidden = !custom;
            if (note) {
                note.hidden = custom;
                note.textContent = locale === 'ro'
                    ? 'Se va solicita soldul disponibil integral.'
                    : 'Будет запрошен весь доступный баланс.';
            }
            if (!custom) setAmount(fullAmount);
            else setAmount(amount.value || fullAmount);
        };

        mode.addEventListener('change', updateMode);
        range.addEventListener('input', () => setAmount(range.value));
        amount.addEventListener('input', () => {
            if (amount.value === '') return;
            syncRange(amount.value);
        });
        amount.addEventListener('blur', () => setAmount(amount.value));
        updateMode();
    });

    if (widget && dialog && backdrop && closeButton) {
        const closeWidget = closeCreatorWidget;
        document.querySelectorAll('[data-creator-widget-open]').forEach((button) => button.addEventListener('click', (event) => {
            event.preventDefault();
            if (widget.hidden) return;
            window.clearTimeout(widgetCloseTimer);
            dialog.hidden = false; backdrop.hidden = false;
            document.documentElement.classList.add('creator-widget-open');
            requestAnimationFrame(() => {
                dialog.classList.add('is-open');
                backdrop.classList.add('is-open');
            });
            closeButton.focus();
            loadDashboard(widget);
        }));
        closeButton.addEventListener('click', closeWidget);
        backdrop.addEventListener('click', closeWidget);
        document.addEventListener('keydown', (event) => {
            if (dialog.hidden) return;
            if (event.key === 'Escape') closeWidget();
            if (event.key === 'Tab') {
                const focusable = Array.from(dialog.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled])')).filter((element) => element.offsetParent !== null);
                if (!focusable.length) return;
                const first = focusable[0], last = focusable[focusable.length - 1];
                if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
                else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
            }
        });
    }

    const preferenceInputs = Array.from(document.querySelectorAll('[data-creator-preference-toggle]'));
    const preferenceGroups = Array.from(document.querySelectorAll('[data-creator-preferences]'));
    const preferenceText = locale === 'ro'
        ? { saving: 'Se salvează…', saved: 'Setarea a fost salvată.', sessionSaved: 'Setarea este păstrată până la ieșire.', failed: 'Setarea nu a putut fi salvată.' }
        : { saving: 'Сохраняем…', saved: 'Настройка сохранена.', sessionSaved: 'Настройка действует до выхода из аккаунта.', failed: 'Не удалось сохранить настройку.' };
    preferenceInputs.forEach((input) => input.addEventListener('change', async () => {
        const setting = input.getAttribute('data-setting'); if (!setting) return;
        const desired = input.checked;
        const related = preferenceInputs.filter((item) => item.getAttribute('data-setting') === setting);
        const endpoint = preferenceGroups[0] && preferenceGroups[0].getAttribute('data-endpoint') || '/creator/preferences';
        related.forEach((item) => { item.disabled = true; });
        document.querySelectorAll('[data-creator-preferences-status]').forEach((node) => { node.textContent = preferenceText.saving; });
        try {
            const response = await fetch(endpoint, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body: new URLSearchParams({ setting, value: desired ? '1' : '0' }) });
            const result = await response.json();
            if (!response.ok || result.status !== 'ok' || result.setting !== setting || Boolean(result.value) !== desired) throw new Error('preference_failed');
            related.forEach((item) => { item.checked = desired; });
            if (setting === 'widget_enabled' && widget) widget.hidden = !desired;
            const message = result.persistent ? preferenceText.saved : preferenceText.sessionSaved;
            document.querySelectorAll('[data-creator-preferences-status]').forEach((node) => { node.textContent = message; });
        } catch (_) {
            related.forEach((item) => { item.checked = !desired; });
            document.querySelectorAll('[data-creator-preferences-status]').forEach((node) => { node.textContent = preferenceText.failed; });
        } finally { related.forEach((item) => { item.disabled = false; }); }
    }));

    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-creator-copy]');
        if (!button) return;
        const value = button.getAttribute('data-creator-copy') || '';
        try {
            if (navigator.clipboard && window.isSecureContext) await navigator.clipboard.writeText(value);
            else {
                const temporary = document.createElement('textarea'); temporary.value = value; temporary.setAttribute('readonly', 'readonly'); temporary.style.position = 'fixed'; temporary.style.opacity = '0'; document.body.appendChild(temporary); temporary.select();
                const copied = document.execCommand('copy'); temporary.remove(); if (!copied) throw new Error('copy_failed');
            }
            const old = button.textContent; button.textContent = copy.copied; window.setTimeout(() => { button.textContent = old; }, 1400);
        } catch (_) { button.textContent = value; }
    });

    if (accountRoot) loadDashboard(accountRoot);
})();
