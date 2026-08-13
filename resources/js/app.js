import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

Alpine.store('theme', {
    dark: false,

    init() {
        const saved = localStorage.getItem('theme');
        if (saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            this.setDark(true);
        }
    },

    setDark(value) {
        this.dark = value;
        document.documentElement.classList.toggle('dark', value);
        localStorage.setItem('theme', value ? 'dark' : 'light');
    },

    toggle() {
        this.setDark(!this.dark);
    },
});

Alpine.store('toasts', {
    items: [],

    notify(message, type = 'success', title = '') {
        const id = Date.now() + Math.random();
        this.items.push({ id, message, type, title });
        setTimeout(() => this.dismiss(id), 4000);
    },

    dismiss(id) {
        this.items = this.items.filter((t) => t.id !== id);
    },
});

/**
 * Global toast helper – callable from anywhere: window.toast('Saved', 'success')
 */
window.toast = (message, type = 'success', title = '') => {
    Alpine.store('toasts')?.notify(message, type, title);
};

document.addEventListener('alpine:init', () => {
    Alpine.data('confirmDialog', () => ({
        open: false,
        title: '',
        message: '',
        confirmText: 'Confirm',
        action: null,
        busy: false,

        ask(action, { title = 'Are you sure?', message = 'This action cannot be undone.', confirmText = 'Confirm' } = {}) {
            this.title = title;
            this.message = message;
            this.confirmText = confirmText;
            this.action = action;
            this.open = true;
        },

        async confirm() {
            this.busy = true;
            try {
                if (typeof this.action === 'function') {
                    await this.action();
                } else if (this.action instanceof HTMLFormElement && this.action.hasAttribute('data-ajax')) {
                    await window.JackAjax.submitForm(this.action);
                } else if (this.action) {
                    this.action.submit();
                }
            } finally {
                this.busy = false;
                this.open = false;
            }
        },

        cancel() {
            this.open = false;
        },
    }));

    Alpine.data('modal', () => ({
        open: false,
        openModal() {
            this.open = true;
            document.body.style.overflow = 'hidden';
        },
        closeModal() {
            this.open = false;
            document.body.style.overflow = '';
        },
        onKeydown(e) {
            if (e.key === 'Escape') {
                this.closeModal();
            }
        },
    }));

    Alpine.data('chart', (element) => ({
        instance: null,
        init() {
            if (!element || !element.dataset.chart) return;
            const config = JSON.parse(element.dataset.chart);
            const defaults = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            usePointStyle: true,
                            color: document.documentElement.classList.contains('dark') ? '#b7b7c0' : '#72727f',
                        },
                    },
                },
                scales: config.scales === false ? undefined : {
                    x: {
                        grid: { color: 'rgba(128,128,140,0.08)' },
                        ticks: {
                            color: document.documentElement.classList.contains('dark') ? '#8f8f9c' : '#8f8f9c',
                        },
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(128,128,140,0.08)' },
                        ticks: {
                            color: document.documentElement.classList.contains('dark') ? '#8f8f9c' : '#8f8f9c',
                        },
                    },
                },
            };
            this.instance = new Chart(this.$el, { ...config, options: { ...defaults, ...config.options } });
        },
    }));

});

Alpine.start();

// ---------------------------------------------------------------------------
// JackAjax: global AJAX engine for toasts, table actions, filters, pagination
// and polling. All handlers are delegated at the document level so they keep
// working after sections of the page are re-rendered via fetch.
// ---------------------------------------------------------------------------
window.JackAjax = (() => {
    const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

    async function request(method, url, data = null, { headers = {}, silent = false, defaultMessage = null } = {}) {
        const finalHeaders = { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken(), ...headers };
        let body = null;
        if (data !== null && data !== undefined) {
            if (data instanceof FormData) {
                body = data;
            } else {
                finalHeaders['Content-Type'] = 'application/json';
                body = JSON.stringify(data);
            }
        }

        let res;
        try {
            res = await fetch(url, { method, headers: finalHeaders, body, credentials: 'same-origin', redirect: 'follow' });
        } catch (err) {
            if (!silent) toast('Network error. Please try again.', 'error');
            throw err;
        }

        if (res.status === 401) {
            if (!silent) toast('Your session has expired. Please log in again.', 'error');
            setTimeout(() => window.location.reload(), 1200);
            throw new Error('Unauthenticated');
        }

        if (res.status === 419) {
            if (!silent) toast('Your session has expired. Please refresh the page.', 'error');
            throw new Error('CSRF token mismatch');
        }

        const ct = res.headers.get('content-type') || '';

        if (res.status === 422 && ct.includes('application/json')) {
            const body = await res.json();
            const msg = body.message
                || (body.errors ? Object.values(body.errors).flat().join(' ') : null)
                || 'Please correct the highlighted errors.';
            if (!silent) toast(msg, 'error');
            const err = new Error(msg);
            err.validation = true;
            err.data = body;
            throw err;
        }

        if (ct.includes('application/json')) {
            const body = await res.json();
            if (!res.ok) {
                if (!silent) toast(body.message || 'Request failed.', 'error');
                const err = new Error(body.message || 'Request failed');
                err.response = { status: res.status, data: body };
                throw err;
            }
            if (!silent) {
                if (body.ok === false) {
                    toast(body.message || 'Action failed.', 'error');
                } else if (body.error) {
                    toast(body.error, 'error');
                } else if (body.message) {
                    toast(body.message, body.success === false ? 'error' : 'success');
                } else if (defaultMessage) {
                    toast(defaultMessage, 'success');
                }
            }
            return body;
        }

        if (!res.ok) {
            if (!silent) toast(`Request failed (${res.status}).`, 'error');
            throw new Error(`HTTP ${res.status}`);
        }

        return { ok: true };
    }

    /**
     * Submit a form over AJAX. Serializes the native FormData, fires the
     * request, toasts the response and refreshes the targeted section.
     */
    async function submitForm(form, { defaultMessage = null } = {}) {
        const data = new FormData(form);
        const method = (form.getAttribute('method') || 'POST').toUpperCase();
        await request(method, form.action, data, { defaultMessage });
        if (form.hasAttribute('data-ajax-reset')) form.reset();
        if (form.hasAttribute('data-ajax-dispatch')) {
            const evt = form.getAttribute('data-ajax-dispatch');
            const val = form.getAttribute('data-ajax-dispatch-value') || '';
            window.dispatchEvent(new CustomEvent(evt, { detail: val }));
        }
        await refreshAfter(form);
    }

    /**
     * After an in-place action, refresh the relevant section of the page.
     * - form[data-refresh="page"]     → full reload (fallback)
     * - form[data-refresh="#sel"]     → swap those container(s)
     * - form[data-refresh="off"]      → do nothing
     * - otherwise                     → refresh the closest [data-ajax-table]
     */
    async function refreshAfter(source) {
        const explicit = source.dataset.refresh;
        if (explicit === 'off' || explicit === 'none') return;
        if (explicit === 'page') {
            window.location.reload();
            return;
        }
        if (explicit) {
            return swapContainers(explicit);
        }
        const container = source.closest('[data-ajax-table]');
        if (container) {
            const id = container.getAttribute('data-ajax-table');
            return swapContainers(`[data-ajax-table="${id}"]`);
        }
    }

    /**
     * Fetch the current URL and swap the DOM of the matching container(s).
     * Used for AJAX pagination, filters and post-action refreshes.
     */
    async function swapContainers(selector) {
        const selectors = selector.split(',').map((s) => s.trim()).filter(Boolean);
        const targets = selectors.flatMap((s) => Array.from(document.querySelectorAll(s)));
        if (targets.length === 0) return;

        let html;
        try {
            const res = await fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
            if (!res.ok) return;
            html = await res.text();
        } catch (err) {
            return;
        }

        const doc = new DOMParser().parseFromString(html, 'text/html');
        for (const selector of selectors) {
            const oldEl = document.querySelector(selector);
            const newEl = doc.querySelector(selector);
            if (oldEl && newEl) {
                oldEl.replaceWith(newEl);
                if (window.Alpine && typeof Alpine.initTree === 'function') {
                    Alpine.initTree(newEl);
                }
            }
        }
    }

    // --- Delegated event handlers ------------------------------------------

    // AJAX form submit (data-ajax) – e.g. in-table status/delete actions.
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!(form instanceof HTMLFormElement) || e.defaultPrevented) return;
        if (!form.hasAttribute('data-ajax')) return;
        e.preventDefault();
        submitForm(form).catch(() => {});
    });

    // AJAX filter form (data-ajax-filter) – GET, swaps the target table.
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!(form instanceof HTMLFormElement) || e.defaultPrevented) return;
        if (!form.hasAttribute('data-ajax-filter')) return;
        e.preventDefault();

        const target = form.dataset.target || '[data-ajax-table]';
        const url = new URL(form.action);
        const params = new URLSearchParams(new FormData(form));
        params.forEach((value, key) => { if (value === '') params.delete(key); });
        url.search = params.toString();
        history.replaceState({}, '', url.href);
        swapContainers(target);
    });

    // "Clear" link inside an AJAX filter area.
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a[data-ajax-clear]');
        if (!link || e.defaultPrevented) return;
        e.preventDefault();
        const target = link.dataset.target || '[data-ajax-table]';
        const form = link.closest('form[data-ajax-filter]');
        if (form) form.reset();
        history.replaceState({}, '', link.href);
        swapContainers(target);
    });

    // Standalone AJAX actions (data-ajax-url) – buttons/links without a form.
    document.addEventListener('click', async (e) => {
        const el = e.target.closest('[data-ajax-url]');
        if (!el || e.defaultPrevented) return;
        e.preventDefault();
        const method = (el.dataset.ajaxMethod || 'POST').toUpperCase();
        const confirmText = el.dataset.ajaxConfirm;
        const action = () => request(method, el.dataset.ajaxUrl, null, {
            defaultMessage: el.dataset.ajaxMessage || null,
        }).then(() => refreshAfter(el));

        if (confirmText) {
            window.dispatchEvent(new CustomEvent('confirm-ask', {
                detail: { action, options: { title: el.dataset.ajaxConfirmTitle || 'Are you sure?', message: confirmText, confirmText: el.dataset.ajaxConfirmButton || 'Confirm' } },
            }));
            return;
        }

        try {
            await action();
        } catch (err) { /* toasts already shown */ }
    });

    // AJAX pagination links (data-ajax-page) inside a [data-ajax-table].
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a[data-ajax-page]');
        if (!link || e.defaultPrevented) return;
        const container = link.closest('[data-ajax-table]');
        if (!container) return;
        e.preventDefault();
        const id = container.getAttribute('data-ajax-table');
        history.replaceState({}, '', link.href);
        swapContainers(`[data-ajax-table="${id}"]`);
    });

    // Generic AJAX navigation links (data-ajax-link) – same-page filters/cards.
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a[data-ajax-link]');
        if (!link || e.defaultPrevented) return;
        const target = link.dataset.target || '[data-ajax-table]';
        e.preventDefault();
        history.replaceState({}, '', link.href);
        swapContainers(target);
    });

    // --- Real-time polling --------------------------------------------------
    // Elements with data-poll="N" are re-fetched (and swapped) at most once
    // every N seconds. Set data-polled-at on refresh to keep the interval.
    setInterval(() => {
        document.querySelectorAll('[data-poll]').forEach((el) => {
            const seconds = parseInt(el.dataset.poll, 10) || 30;
            const last = parseInt(el.dataset.polledAt || '0', 10);
            if (Date.now() - last < seconds * 1000) return;
            el.dataset.polledAt = Date.now();
            const id = el.getAttribute('data-ajax-table');
            const selector = id ? `[data-ajax-table="${id}"]` : '[data-ajax-table]';
            swapContainers(selector).then(() => {
                const fresh = document.querySelector(selector);
                if (fresh) fresh.dataset.polledAt = Date.now();
            });
        });
    }, 10000);

    return { request, submitForm, swapContainers, refreshAfter };
})();

// Page loading indicator: full-screen overlay shown while a page loads and during redirects.
(() => {
    const KEY = 'page-loading';

    const loader = document.getElementById('page-loader');
    if (!loader) return;

    const show = () => loader.classList.remove('hidden');

    const markNavigation = () => {
        sessionStorage.setItem(KEY, '1');
        show();
    };

    document.addEventListener('click', (e) => {
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0 || e.defaultPrevented) return;

        const anchor = e.target.closest('a[href]');
        if (!anchor) return;

        const href = anchor.getAttribute('href');
        if (!href || href.startsWith('#') || anchor.target === '_blank' || anchor.hasAttribute('download')) return;
        if (href.startsWith('http') && ! href.includes(window.location.hostname)) return;
        if (anchor.hasAttribute('x-on:click') || anchor.hasAttribute('@click')) return;

        markNavigation();
    });

    document.addEventListener('submit', (e) => {
        if (! e.defaultPrevented) {
            markNavigation();
        }
    });

    const start = performance.now();
    const wasRedirect = sessionStorage.getItem(KEY);
    if (wasRedirect) {
        sessionStorage.removeItem(KEY);
    }

    const finish = () => {
        const wait = Math.max(0, (wasRedirect ? 350 : 500) - (performance.now() - start));
        setTimeout(() => loader.classList.add('hidden'), wait);
    };

    if (document.readyState === 'complete') {
        finish();
    } else {
        window.addEventListener('load', finish);
        setTimeout(finish, 2500);
    }
})();
