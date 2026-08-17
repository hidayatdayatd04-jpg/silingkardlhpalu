/**
 * DLH Motion — scroll-reveal & count-up terpusat (tanpa library).
 *
 * Menghidupkan animasi `.reveal` dan counter `[data-countup]` di SEMUA
 * halaman publik tanpa perlu script inline per halaman. Logika identik
 * dengan observer inline di welcome.blade.php, tapi idempoten:
 *  - elemen yang sudah `.is-revealed` / sudah di-observe dilewati,
 *  - counter yang sudah selesai (`data-countup-done`) dilewati,
 * sehingga aman hidup berdampingan dengan script inline yang ada.
 *
 * Di beranda (ada preloader #dlh-preloader) inisialisasi menunggu
 * `html.dlh-ready` / event `dlh:ready` (failsafe 7 detik) agar animasi
 * tidak terbuang di balik layar loading — sama seperti script inline
 * welcome. Di halaman lain langsung jalan.
 *
 * Performa: hanya animasi transform + opacity (compositor-only), satu
 * IntersectionObserver bersama, dan dihormati oleh prefers-reduced-motion.
 */

const OBSERVED = new WeakSet();

function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

/** Aktifkan scroll-reveal untuk semua elemen `.reveal` yang belum ter-reveal. */
export function initReveal() {
    const els = document.querySelectorAll('.reveal:not(.is-revealed)');
    if (!els.length) return;

    if (prefersReducedMotion() || !('IntersectionObserver' in window)) {
        els.forEach((el) => el.classList.add('is-revealed'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-revealed');
                } else if (entry.boundingClientRect.top > 0) {
                    // Belum pernah terlihat & masih di bawah viewport →
                    // boleh di-reset agar animasi main saat discroll ke sana.
                    entry.target.classList.remove('is-revealed');
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -10% 0px' }
    );

    els.forEach((el) => {
        if (OBSERVED.has(el)) return;
        OBSERVED.add(el);
        observer.observe(el);
    });
}

/** Format angka mengikuti locale id-ID (1.234). */
function formatCount(value) {
    return Number(value).toLocaleString('id-ID');
}

/** Jalankan animasi count-up untuk satu counter. */
function runCountUp(el) {
    const target = parseInt(el.dataset.count || '0', 10);
    const suffix = el.dataset.suffix || '';
    const duration = 1400;
    const start = performance.now();

    function tick(now) {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3); // easeOutCubic
        el.textContent = formatCount(Math.round(target * eased)) + suffix;
        if (progress < 1) requestAnimationFrame(tick);
    }

    requestAnimationFrame(tick);
}

/** Aktifkan count-up untuk semua `[data-countup]` yang belum selesai. */
export function initCountUp() {
    const counters = document.querySelectorAll('[data-countup]:not([data-countup-done])');
    if (!counters.length) return;

    if (prefersReducedMotion() || !('IntersectionObserver' in window)) {
        counters.forEach((el) => {
            el.textContent = formatCount(el.dataset.count || 0) + (el.dataset.suffix || '');
            el.setAttribute('data-countup-done', '');
        });
        return;
    }

    const io = new IntersectionObserver(
        (entries, obs) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                runCountUp(entry.target);
                entry.target.setAttribute('data-countup-done', '');
                obs.unobserve(entry.target);
            });
        },
        { threshold: 0.4 }
    );

    counters.forEach((el) => io.observe(el));
}

/** Inisialisasi semua motion; di beranda menunggu preloader terangkat. */
export function initMotion() {
    const preloader = document.getElementById('dlh-preloader');
    const ready =
        document.documentElement.classList.contains('dlh-ready') || !preloader;

    if (ready) {
        initReveal();
        initCountUp();
        return;
    }

    let started = false;
    const start = () => {
        if (started) return;
        started = true;
        initReveal();
        initCountUp();
    };

    window.addEventListener('dlh:ready', start, { once: true });
    // Failsafe: jangan biarkan konten tak terlihat bila preloader gagal.
    setTimeout(start, 7000);
}

export default initMotion;
