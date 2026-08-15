/**
 * admin-common.js - Utilitas premium global untuk seluruh panel admin DLH.
 *
 * Fitur:
 *  1. Page transition: overlay halus saat navigasi antar-halaman admin,
 *     lalu konten masuk dengan reveal lembut.
 *  2. Skeleton loading: overlay shimmer ditampilkan saat navigasi (route change),
 *     otomatis menghilang saat dokumen baru siap.
 *  3. Auto stagger: elemen dengan atribut [data-animate] masuk berjenjang
 *     saat memasuki viewport (respect prefers-reduced-motion).
 *  4. Nav-link entrance: item sidebar & topbar mendapat delay berjenjang saat load.
 */

document.addEventListener('DOMContentLoaded', function () {
    initPageTransition();
    initAutoStagger();
    initContentReveal();
    initNavEntrance();
});

/* â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ Auto-reveal konten halaman yang belum beranimasi â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
function initContentReveal() {
    const main = document.querySelector('[data-page-content]');
    if (!main) return;

    // Langsung tambahkan is-in ke semua child agar visible
    Array.from(main.children).forEach((el, i) => {
        el.classList.add('is-in');
        el.style.opacity = '1';
        el.style.transform = 'none';
    });
}

/* â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ Page Transition + Skeleton â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
function initPageTransition() {
    const prefersReduced = window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Buat elemen overlay + skeleton sekali saja.
    const overlay = document.createElement('div');
    overlay.className = 'page-transition-overlay';
    overlay.setAttribute('aria-hidden', 'true');
    overlay.innerHTML = `
        <div class="page-transition-card">
            <div class="admin-preloader-glow" aria-hidden="true"></div>
            <div class="admin-preloader-badge">
                <span class="admin-preloader-ring"></span>
                <span class="admin-preloader-ring2"></span>
                <img src="/assets/images/logo-web.png" alt="" width="120" height="120" class="admin-preloader-logo">
            </div>
            <p class="admin-preloader-title">Dinas Lingkungan Hidup</p>
            <p class="admin-preloader-subtitle">Kota Palu</p>
            <div class="admin-preloader-bar"><span class="admin-preloader-fill"></span></div>
            <p class="admin-preloader-hint">Menyiapkan layanan untuk Anda...</p>
        </div>`;
    document.body.appendChild(overlay);

    const revealContent = () => {
        const main = document.querySelector('[data-page-content]');
        if (main) {
            main.classList.add('page-content-in');
        }
        document.documentElement.classList.add('dlh-ready');
    };

    // Tandai konten siap saat load awal.
    revealContent();
    window.addEventListener('load', revealContent);

    if (prefersReduced) return;

    // Intercept link internal admin (bukan #, bukan target baru, bukan download).
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a');
        if (!link) return;

        const href = link.getAttribute('href');
        const target = link.getAttribute('target');
        if (!href || href.startsWith('#') || href.startsWith('javascript:') ||
            target === '_blank' || link.hasAttribute('download')) return;

        // Hanya navigasi dalam panel admin (path diawali /admin).
        const url = new URL(link.href, window.location.origin);
        if (!url.pathname.startsWith('/admin')) return;

        // Jangan tampilkan ulang bila tetap di halaman yang sama.
        if (url.pathname === window.location.pathname && url.search === window.location.search) return;

        e.preventDefault();
        overlay.classList.add('is-active');

        // Failsafe: jika navigasi terblokir (prompt browser, koneksi lambat, dll.)
        // overlay tidak boleh menghalangi interaksi formulir selamanya.
        window.setTimeout(() => overlay.classList.remove('is-active'), 3000);

        // Animasi progress bar.
        const fill = overlay.querySelector('.admin-preloader-fill');
        if (fill) {
            fill.style.width = '0%';
            const start = Date.now();
            const DURATION = 800;
            (function tickBar() {
                const p = Math.min((Date.now() - start) / DURATION, 1);
                fill.style.width = (p * 100) + '%';
                if (p < 1) requestAnimationFrame(tickBar);
            })();
        }

        // Beri waktu overlay terlihat sebelum pindah halaman.
        setTimeout(() => {
            window.location.href = link.href;
        }, 850);
    });

    // Saat halaman baru mulai dimuat, sembunyikan overlay lama.
    window.addEventListener('pageshow', () => {
        overlay.classList.remove('is-active');
    });
}

/* â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ Auto Stagger Reveal â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
function initAutoStagger() {
    const els = Array.from(document.querySelectorAll('[data-animate]'));
    if (!els.length) return;

    const prefersReduced = window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (prefersReduced || !('IntersectionObserver' in window)) {
        els.forEach((el) => el.classList.add('is-in'));
        return;
    }

    const io = new IntersectionObserver((entries, obs) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-in');
                obs.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.04 });

    els.forEach((el) => io.observe(el));
}

/* â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ Nav Entrance (sidebar/topbar) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
function initNavEntrance() {
    const reduce = window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce) return;

    // Item dengan marker eksplisit, plus link level-1 sidebar & tombol topbar.
    const explicit = Array.from(document.querySelectorAll('[data-nav-item]'));
    const sidebarLinks = Array.from(document.querySelectorAll('aside nav > a, aside .sidebar-nav > a, aside nav > div > a'));
    const topbarBtns = Array.from(document.querySelectorAll('.topbar-btn, header .quick-action-btn'));
    const items = [...new Set([...explicit, ...sidebarLinks, ...topbarBtns])]
        .filter((el) => el.offsetParent !== null);

    items.forEach((el, i) => {
        el.style.setProperty('--nav-delay', (i * 26) + 'ms');
        el.classList.add('nav-enter');
    });

    // Hapus kelas entrance setelah animasi selesai agar tidak mengganggu hover.
    setTimeout(() => {
        items.forEach((el) => el.classList.remove('nav-enter'));
    }, 1200 + items.length * 26);
}

/* Backward-compat: expose staggerReveal global (sudah ada di admin.js). */
window.initPageTransition = initPageTransition;
window.initAutoStagger = initAutoStagger;
