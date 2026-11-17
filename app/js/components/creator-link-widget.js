(function () {
    'use strict';

    const locale = document.documentElement.lang === 'ro' ? 'ro' : 'ru';
    const copy = locale === 'ro' ? {
        loading: 'Se încarcă…', empty: 'Nu există încă date.', unavailable: 'Datele nu sunt disponibile momentan.',
        notConfigured: 'Conectarea nu este configurată momentan.', checking: 'Se verifică legătura…', linksUnavailable: 'Linkurile nu sunt disponibile momentan.',
        disconnected: 'Conectează contul Vizaje Partners pentru a administra linkurile.',
        connected: 'Contul Vizaje Partners este conectat.', suspended: 'Accesul creatorului este suspendat.',
        connect: 'Conectează Partners', active: 'Activ', created: 'Linkul a fost creat.',
        createFailed: 'Linkul nu a putut fi creat.', copied: 'Copiat', open: 'Deschide', copyLink: 'Copiază',
        revoke: 'Revocă', revokeConfirm: 'Revoci acest link?', revoked: 'Link revocat.',
        actionFailed: 'Acțiunea nu a putut fi finalizată.', linked: 'Cont deconectat.', unlinkConfirm: 'Deconectezi contul Vizaje Partners?',
        types: { product: 'Produs', category: 'Categorie', brand: 'Brand', homepage: 'Pagina principală' },
        statuses: { active: 'Activ', revoked: 'Revocat', inactive: 'Inactiv' },
    } : {
        loading: 'Загружаем…', empty: 'Данных пока нет.', unavailable: 'Данные сейчас недоступны.',
        notConfigured: 'Подключение сейчас не настроено.', checking: 'Проверяем подключение…', linksUnavailable: 'Ссылки сейчас недоступны.',
        disconnected: 'Свяжите Vizaje Partners, чтобы управлять ссылками.',
        connected: 'Аккаунт Vizaje Partners подключён.', suspended: 'Доступ креатора приостановлен.',
        connect: 'Связать Partners', active: 'Активен', created: 'Ссылка создана.',
        createFailed: 'Не удалось создать ссылку.', copied: 'Скопировано', open: 'Открыть', copyLink: 'Копировать',
        revoke: 'Отозвать', revokeConfirm: 'Отозвать эту ссылку?', revoked: 'Ссылка отозвана.',
        actionFailed: 'Не удалось выполнить действие.', linked: 'Аккаунт отключён.', unlinkConfirm: 'Отключить аккаунт Vizaje Partners?',
        types: { product: 'Товар', category: 'Категория', brand: 'Бренд', homepage: 'Главная' },
        statuses: { active: 'Активна', revoked: 'Отозвана', inactive: 'Неактивна' },
    };

    const widget = document.querySelector('[data-creator-link-widget]');
    const dialog = widget && widget.querySelector('[data-creator-widget-dialog]');
    const backdrop = widget && widget.querySelector('[data-creator-widget-backdrop]');
    const closeButton = widget && widget.querySelector('[data-creator-widget-close]');
    const accountRoot = document.querySelector('[data-creator-dashboard-root]');
    const roots = [accountRoot, widget].filter(Boolean);

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

    function allowedStoreUrl(value) {
        try {
            const url = new URL(value);
            const hosts = ['vizaje-nica.com', 'www.vizaje-nica.com', 'dev.vizaje-nica.com'];
            const local = url.hostname === 'vizaje-nica.local' || url.hostname === 'localhost';
            return (url.protocol === 'https:' && hosts.includes(url.hostname) && !url.port)
                || (url.protocol === 'http:' && local && (!url.port || url.port === '80'));
        } catch (_) { return false; }
    }

    function allowedAuthorizeUrl(value) {
        try {
            const url = new URL(value);
            return url.origin === 'https://partners.vizaje-nica.com'
                && url.pathname === '/link/legacy/authorize'
                && /^\?ticket=[A-Za-z0-9_-]{43}$/.test(url.search)
                && !url.hash;
        } catch (_) { return false; }
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
        const widgetList = root.querySelector('[data-widget-links]');
        if (table) table.hidden = state !== 'available';
        if (widgetList) widgetList.hidden = state !== 'available';
        root.querySelectorAll('[data-creator-links-pane]').forEach((pane) => {
            pane.hidden = pane.getAttribute('data-creator-links-pane') !== state;
        });
        const widgetState = root.querySelector('[data-widget-links-state]');
        if (widgetState) {
            widgetState.hidden = state === 'available';
            widgetState.textContent = state === 'empty' ? copy.empty : state === 'loading' ? copy.loading : copy.linksUnavailable;
        }
    }

    function renderSafeLinkList(container, links, compact) {
        if (!container) return;
        container.replaceChildren();
        links.forEach((link) => {
            if (!link || !allowedStoreUrl(link.short_url)) return;
            const row = document.createElement(compact ? 'p' : 'tr');
            const shortCell = document.createElement(compact ? 'span' : 'td');
            const anchor = document.createElement('a');
            anchor.href = link.short_url;
            anchor.target = '_blank';
            anchor.rel = 'noopener noreferrer';
            anchor.textContent = link.short_url;
            shortCell.appendChild(anchor);

            if (compact) {
                const title = document.createElement('strong');
                title.textContent = link.title || copy.types[link.type] || link.type;
                const actions = document.createElement('span');
                const copyButton = document.createElement('button'); copyButton.type = 'button'; copyButton.textContent = copy.copyLink; copyButton.setAttribute('data-creator-copy', link.short_url);
                actions.appendChild(copyButton);
                if (link.id && link.status !== 'revoked') {
                    const revoke = document.createElement('button'); revoke.type = 'button'; revoke.textContent = copy.revoke;
                    revoke.setAttribute('data-creator-revoke', link.id); actions.append(document.createTextNode(' '), revoke);
                }
                row.append(title, document.createTextNode(' '), shortCell, document.createTextNode(' '), actions);
            } else {
                const type = document.createElement('td'); type.textContent = copy.types[link.type] || link.type;
                const title = document.createElement('td'); title.textContent = link.title || '—';
                const created = document.createElement('td'); created.textContent = link.created_at || '—';
                const status = document.createElement('td'); status.textContent = copy.statuses[link.status] || link.status || '—';
                const actions = document.createElement('td');
                const open = document.createElement('a'); open.href = link.short_url; open.target = '_blank'; open.rel = 'noopener noreferrer'; open.textContent = copy.open;
                const copyButton = document.createElement('button'); copyButton.type = 'button'; copyButton.textContent = copy.copyLink; copyButton.setAttribute('data-creator-copy', link.short_url);
                actions.append(open, document.createTextNode(' '), copyButton);
                if (link.id && link.status !== 'revoked') {
                    const revoke = document.createElement('button'); revoke.type = 'button'; revoke.textContent = copy.revoke;
                    revoke.setAttribute('data-creator-revoke', link.id);
                    actions.append(document.createTextNode(' '), revoke);
                }
                row.append(type, title, shortCell, created, status, actions);
            }
            container.appendChild(row);
        });
    }

    function updateConnectionUi(root, result) {
        root.setAttribute('data-can-create-links', result.canCreateLinks ? '1' : '0');
        const state = root.querySelector('[data-creator-partner-state]');
        if (state) {
            const current = result.state;
            state.setAttribute('data-creator-partner-state', current);
            const label = current === 'active' ? copy.connected : current === 'suspended' ? copy.suspended
                : current === 'not_connected' ? (result.configured === false ? copy.notConfigured : copy.disconnected)
                    : current === 'checking' || current === 'pending_check' ? copy.checking : copy.unavailable;
            const textNode = state.querySelector('[data-creator-partner-state-text]');
            if (textNode) textNode.textContent = label; else state.textContent = label;
        }
        root.querySelectorAll('[data-creator-link-start]').forEach((button) => {
            button.hidden = !result.canConnect;
            button.disabled = !result.canConnect;
            if (result.canConnect) button.textContent = copy.connect;
        });
        root.querySelectorAll('[data-creator-partners-open]').forEach((link) => {
            link.hidden = result.state !== 'active';
        });
        root.querySelectorAll('[data-creator-unlink]').forEach((button) => {
            button.hidden = !result.canUnlink;
            button.disabled = !result.canUnlink;
        });
        root.querySelectorAll('[data-creator-link-submit]').forEach((button) => {
            button.disabled = !result.canCreateLinks;
        });
    }

    async function loadDashboard(root) {
        if (!root || root.getAttribute('data-dashboard-loading') === '1') return;
        root.setAttribute('data-dashboard-loading', '1');
        const initialState = root.querySelector('[data-creator-partner-state]') || root.querySelector('[data-widget-partner-state]');
        if (initialState) {
            initialState.setAttribute('data-creator-partner-state', 'checking');
            const text = initialState.querySelector('[data-creator-partner-state-text]');
            if (text) text.textContent = copy.checking; else initialState.textContent = copy.checking;
        }
        setLinkPane(root, 'loading');
        try {
            const response = await fetch(root.getAttribute('data-dashboard-url') || '/account/creator-link/dashboard', {
                credentials: 'same-origin', headers: { 'Accept': 'application/json' }, cache: 'no-store',
            });
            const result = await response.json();
            if (!response.ok || !result || result.ok !== true) throw new Error('dashboard_unavailable');
            updateConnectionUi(root, result);
            if (result.linksAvailable && Array.isArray(result.links)) {
                const rows = root.querySelector('[data-creator-links]');
                const compact = root.querySelector('[data-widget-links]');
                if (rows) renderSafeLinkList(rows, result.links, false);
                if (compact) renderSafeLinkList(compact, result.links, true);
                setLinkPane(root, result.links.length ? 'available' : 'empty');
            } else {
                setLinkPane(root, result.state === 'active' ? 'error' : 'empty');
            }
        } catch (_) {
            updateConnectionUi(root, { state: 'error', canConnect: false, canCreateLinks: false, canUnlink: false });
            setLinkPane(root, 'error');
        } finally {
            root.removeAttribute('data-dashboard-loading');
        }
    }

    roots.forEach((root) => {
        root.querySelectorAll('[data-creator-link-start]').forEach((button) => {
            button.addEventListener('click', async () => {
                button.disabled = true;
                const message = root.querySelector('[data-creator-action-message]');
                setMessage(message, copy.loading, false);
                try {
                    const result = await postForm(button.getAttribute('data-start-url') || root.getAttribute('data-start-url') || '/account/creator-link/start', {
                        locale: root.getAttribute('data-locale') || locale,
                    }, csrf(root));
                    if (!allowedAuthorizeUrl(result.authorizeUrl)) throw new Error('invalid_authorize_url');
                    window.location.assign(result.authorizeUrl);
                } catch (_) {
                    setMessage(message, copy.actionFailed, true);
                    button.disabled = false;
                }
            });
        });

        root.querySelectorAll('[data-creator-link-form]').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                const submit = form.querySelector('[data-creator-link-submit]');
                const message = form.querySelector('[data-creator-create-message]');
                if (!submit || submit.disabled) return;
                submit.disabled = true;
                setMessage(message, copy.loading, false);
                const linkFields = {
                    linkType: form.querySelector('[data-creator-link-type]')?.value || 'product',
                    targetUrl: form.querySelector('[data-creator-link-target]')?.value || '',
                    requestedLocale: form.querySelector('[data-creator-link-locale]')?.value || form.getAttribute('data-locale') || locale,
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
                        const target = document.createElement('a');
                        target.href = link.short_url; target.target = '_blank'; target.rel = 'noopener noreferrer'; target.textContent = link.short_url;
                        message.replaceChildren(document.createTextNode(copy.created + ' '), target);
                    }
                    const title = form.querySelector('[data-creator-link-title]'); if (title) title.value = '';
                    delete form.dataset.idempotencyKey;
                    delete form.dataset.idempotencyPayload;
                    await loadDashboard(root);
                } catch (_) {
                    setMessage(message, copy.createFailed, true);
                } finally {
                    editableFields.forEach((field) => { field.disabled = false; });
                    const canCreate = root.getAttribute('data-can-create-links') === '1';
                    submit.disabled = !canCreate;
                }
            });
        });

        root.querySelectorAll('[data-creator-unlink]').forEach((button) => {
            button.addEventListener('click', async () => {
                if (!window.confirm(copy.unlinkConfirm)) return;
                button.disabled = true;
                try {
                    await postForm(button.getAttribute('data-unlink-url') || root.getAttribute('data-unlink-url') || '/account/creator-link/unlink', {}, csrf(root));
                    await loadDashboard(root);
                } catch (_) {
                    setMessage(root.querySelector('[data-creator-action-message]'), copy.actionFailed, true);
                    button.disabled = false;
                }
            });
        });

        root.addEventListener('click', async (event) => {
            const button = event.target.closest('[data-creator-revoke]');
            if (button && root.contains(button)) {
                if (!window.confirm(copy.revokeConfirm)) return;
                button.disabled = true;
                try {
                    const prefix = root.getAttribute('data-revoke-prefix') || '/account/creator-link/revoke/';
                    await postForm(prefix + encodeURIComponent(button.getAttribute('data-creator-revoke')), {}, csrf(root));
                    await loadDashboard(root);
                } catch (_) { button.disabled = false; setMessage(root.querySelector('[data-creator-action-message]'), copy.actionFailed, true); }
            }
        });
    });

    if (widget && dialog && backdrop && closeButton) {
        const closeWidget = () => {
            dialog.hidden = true; backdrop.hidden = true;
            document.documentElement.classList.remove('creator-widget-open');
            const trigger = widget.querySelector('[data-creator-widget-open]');
            if (trigger && !widget.hidden) trigger.focus();
        };
        document.querySelectorAll('[data-creator-widget-open]').forEach((button) => button.addEventListener('click', (event) => {
            event.preventDefault();
            if (widget.hidden) return;
            dialog.hidden = false; backdrop.hidden = false;
            document.documentElement.classList.add('creator-widget-open');
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
