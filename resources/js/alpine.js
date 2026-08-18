/**
 * Alpine.js — self-hosted (no CDN) untuk halaman admin DLH.
 *
 * Task 4: hilangkan dependency 4 script dari cdn.jsdelivr.net di setiap halaman
 * admin (full-page-reload, jadi 4 request eksternal berulang per navigasi).
 *
 * Catatan penting soal double-init:
 *  - Halaman publik memakai Alpine yang DIBUNDEL Livewire (`@livewireScripts`).
 *    Karena itu Alpine hanya di-bootstrap pada halaman yang menandai dirinya
 *    dengan atribut `data-alpine-bootstrap` di <body> (manifest admin & login).
 *  - `window.Alpine` diisi agar inline `<script>` lama yang memakai
 *    `document.addEventListener('alpine:init', () => Alpine.store(...))`
 *    tetap bekerja (contoh: store 'petaSidebar' di admin/peta).
 */
window.dlhTrapFocus = function dlhTrapFocus(event, container) {
    if (event.key !== 'Tab' || !container) return;

    const selector = [
        'a[href]:not([tabindex="-1"])',
        'button:not([disabled]):not([tabindex="-1"])',
        'input:not([disabled]):not([type="hidden"]):not([tabindex="-1"])',
        'select:not([disabled]):not([tabindex="-1"])',
        'textarea:not([disabled]):not([tabindex="-1"])',
        '[tabindex]:not([tabindex="-1"])',
    ].join(',');
    const focusable = Array.from(container.querySelectorAll(selector))
        .filter((element) => !element.hasAttribute('hidden') && element.getClientRects().length > 0);

    if (!focusable.length) {
        event.preventDefault();
        container.focus?.();
        return;
    }

    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
};

function registerAlpineExtensions(Alpine) {
    if (window.__dlhAlpineExtensionsRegistered) return;
    window.__dlhAlpineExtensionsRegistered = true;

    // Store 'sidebar' — dipindahkan dari inline script layouts/admin.blade.php.
    // Terdaftar lewat event `alpine:init` seperti sebelumnya.
    Alpine.store('sidebar', {
        collapsed: localStorage.getItem('sidebar-collapsed') === 'true',
        toggle() {
            this.collapsed = !this.collapsed;
            localStorage.setItem('sidebar-collapsed', this.collapsed);
        },
    });

    /**
     * Widget progres backup/restore latar belakang.
     * Didftarkan LANGSUNG di sini (sebelum Alpine.start) agar komponen
     * x-data="backupProgressWidget" sudah terdefinisi saat Alpine memindai
     * DOM — tidak bergantung lagi pada urutan listener 'alpine:init' antar-modul
     * bundle. Logika komponen sebelumnya berada di resources/js/admin.js.
     * URL endpoint dibaca dari data-attribute elemen (di-render server-side).
     */
    Alpine.data('backupProgressWidget', () => ({
        task: null,
        visible: false,
        cancelling: false,
        timer: null,
        progressUrl: '',
        cancelUrl: '',
        notifyKey: 'dlh-backup-last-notified',

        init() {
            this.progressUrl = this.$el.dataset.progressUrl || '';
            this.cancelUrl = this.$el.dataset.cancelUrl || '';
            this.poll();
        },

        get title() {
            if (!this.task) return '';
            if (this.task.status === 'pending') return 'Menunggu antrian…';
            return this.task.type === 'restore' ? 'Melakukan restore…' : 'Membuat backup…';
        },

        get stalePending() {
            return this.task
                && this.task.status === 'pending'
                && (Date.now() / 1000 - (this.task.updated_at || 0)) > 60;
        },

        async poll() {
            try {
                const res = await fetch(this.progressUrl, { headers: { Accept: 'application/json' } });
                if (!res.ok) {
                    this.scheduleNext();
                    return;
                }
                this.applyState(await res.json());
            } catch (e) {
                this.scheduleNext();
            }
        },

        applyState(task) {
            const active = task && (task.status === 'pending' || task.status === 'running');

            if (active) {
                this.task = task;
                this.visible = true;
                this.scheduleNext();
                return;
            }

            this.stopPolling();

            if (task && task.status && task.status !== 'idle') {
                this.notify(task);
            }

            this.visible = false;
            this.task = null;
            this.cancelling = false;
        },

        scheduleNext() {
            if (this.timer) return;
            this.timer = setTimeout(() => {
                this.timer = null;
                this.poll();
            }, 2000);
        },

        stopPolling() {
            if (this.timer) {
                clearTimeout(this.timer);
                this.timer = null;
            }
        },

        notify(task) {
            const ts = String(task.updated_at || '');
            const last = localStorage.getItem(this.notifyKey) || '';
            if (!ts || ts <= last) return;
            localStorage.setItem(this.notifyKey, ts);

            const type = task.status === 'done' ? 'success' : (task.status === 'cancelled' ? 'warning' : 'error');
            const msg = task.message || (task.status === 'done' ? 'Proses selesai.' : 'Proses berakhir.');
            if (window.showToast) window.showToast(msg, type);

            if (task.status === 'done' && window.location.pathname.includes('/admin/backup')) {
                setTimeout(() => window.location.reload(), 1200);
            }
        },

        async cancel() {
            if (!confirm('Batalkan proses ini? Perubahan yang belum selesai akan digulungkan.')) return;

            this.cancelling = true;
            try {
                await fetch(this.cancelUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        Accept: 'application/json',
                    },
                });
            } catch (e) {
                this.cancelling = false;
            }
        },
    }));

}

document.addEventListener('alpine:init', () => registerAlpineExtensions(window.Alpine));

// Supports pages where Vite is evaluated after the Livewire bundle.
if (window.Alpine) registerAlpineExtensions(window.Alpine);
