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

document.addEventListener('alpine:init', () => {
    Alpine.data('toast', () => ({
        toasts: [],
        notify(message, type = 'success', title = '') {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, message, type, title });
            setTimeout(() => this.dismiss(id), 4000);
        },
        dismiss(id) {
            this.toasts = this.toasts.filter((t) => t.id !== id);
        },
    }));

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

// Page loading indicator: shows a top progress bar during page redirects.
(() => {
    const KEY = 'page-loading';

    const markNavigation = () => sessionStorage.setItem(KEY, '1');

    document.addEventListener('click', (e) => {
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0 || e.defaultPrevented) return;

        const anchor = e.target.closest('a[href]');
        if (!anchor) return;

        const href = anchor.getAttribute('href');
        if (!href || href.startsWith('#') || anchor.target === '_blank' || anchor.hasAttribute('download')) return;
        if (href.startsWith('http') && ! href.includes(window.location.hostname)) return;
        if ((anchor.hasAttribute('x-on:click') || anchor.hasAttribute('@click'))) return;

        markNavigation();
    });

    document.addEventListener('submit', (e) => {
        if (! e.defaultPrevented) {
            markNavigation();
        }
    });

    const loader = document.getElementById('page-loader');
    if (loader && sessionStorage.getItem(KEY)) {
        sessionStorage.removeItem(KEY);

        const start = performance.now();
        loader.classList.add('active');

        const finish = () => {
            const remaining = 400 - (performance.now() - start);
            setTimeout(() => loader.classList.remove('active'), Math.max(0, remaining));
        };

        if (document.readyState === 'complete') {
            finish();
        } else {
            window.addEventListener('load', finish);
            setTimeout(finish, 2500);
        }
    }
})();
