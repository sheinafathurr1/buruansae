import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';

/**
 * Modal rincian (dipakai dashboard sektor). Isi modal berupa potongan HTML
 * dari server; tautan bertanda data-modal-nav (mis. pagination) dimuat ulang
 * di dalam modal tanpa berpindah halaman.
 */
Alpine.data('detailModal', () => ({
    open: false,
    loading: false,
    failed: false,
    title: '',
    url: null,
    controller: null,

    show({ url, title }) {
        this.title = title ?? '';
        this.open = true;
        this.load(url);
    },

    close() {
        this.open = false;
        this.controller?.abort();
    },

    async load(url) {
        this.url = url;
        this.loading = true;
        this.failed = false;
        this.controller?.abort();
        this.controller = new AbortController();

        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' },
                signal: this.controller.signal,
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            this.$refs.body.innerHTML = await response.text();
            this.$refs.body.scrollTop = 0;
        } catch (error) {
            if (error.name !== 'AbortError') this.failed = true;
        } finally {
            this.loading = false;
        }
    },

    navigate(event) {
        const link = event.target.closest('a[data-modal-nav]');
        if (!link) return;
        event.preventDefault();
        this.load(link.href);
    },
}));

window.Alpine = Alpine;
Alpine.plugin([collapse, focus]);
Alpine.start();
