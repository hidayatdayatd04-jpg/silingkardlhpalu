/**
 * Admin Panel JavaScript Enhancements
 * Enhanced UX interactions and utilities
 */

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initTooltips();
    initKeyboardShortcuts();
    initSmoothScroll();
    initAutoHideAlerts();
    initConfirmActions();
    initTableEnhancements();
});

/**
 * Initialize tooltips using Alpine.js
 */
function initTooltips() {
    // Tooltips are handled by Alpine.js x-tooltip directive
    // Add tooltip styles
    const style = document.createElement('style');
    style.textContent = `
        [x-tooltip] {
            position: relative;
        }
        
        .tooltip {
            position: absolute;
            z-index: 50;
            padding: 0.5rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: white;
            background-color: rgb(15 23 42);
            border-radius: 0.375rem;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s;
        }
        
        .tooltip.show {
            opacity: 1;
        }
    `;
    document.head.appendChild(style);
}

/**
 * Keyboard shortcuts
 */
function initKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Cmd/Ctrl + K - Open command palette
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            window.dispatchEvent(new CustomEvent('open-command-palette'));
        }
        
        // Cmd/Ctrl + N - Add new (if on index page)
        if ((e.metaKey || e.ctrlKey) && e.key === 'n') {
            const addButton = document.querySelector('a[href*="/create"]');
            if (addButton && !isInputFocused()) {
                e.preventDefault();
                addButton.click();
            }
        }
        
        // Escape - Close modals/dropdowns
        if (e.key === 'Escape') {
            // Alpine.js handles this automatically with x-on:keydown.escape
        }
    });
}

/**
 * Check if any input is focused
 */
function isInputFocused() {
    const activeElement = document.activeElement;
    return activeElement && (
        activeElement.tagName === 'INPUT' || 
        activeElement.tagName === 'TEXTAREA' || 
        activeElement.tagName === 'SELECT' ||
        activeElement.isContentEditable
    );
}

/**
 * Smooth scroll to anchors
 */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

/**
 * Auto-hide alerts after 5 seconds
 */
function initAutoHideAlerts() {
    const alerts = document.querySelectorAll('[x-data*="show: true"]');
    alerts.forEach(alert => {
        setTimeout(() => {
            const closeButton = alert.querySelector('[x-on\\:click="show = false"]');
            if (closeButton) {
                closeButton.click();
            }
        }, 5000);
    });
}

/**
 * Confirm delete actions — DINONAKTIFKAN.
 * Konfirmasi hapus kini pakai komponen <x-admin.confirm-delete> (modal Alpine),
 * bukan confirm() native. Fungsi lama disisakan sebagai no-op agar pemanggilan
 * di initialize() tidak error.
 */
function initConfirmActions() {
    // no-op: modal konfirmasi menggantikan confirm() native.
}

/**
 * Table enhancements
 */
function initTableEnhancements() {
    // Add hover effect to table rows
    const tableRows = document.querySelectorAll('tbody tr[data-clickable]');
    tableRows.forEach(row => {
        row.style.cursor = 'pointer';
        row.addEventListener('click', function(e) {
            // Don't trigger if clicking on a button or link
            if (e.target.closest('button, a')) return;
            
            const link = this.querySelector('a[href*="show"]');
            if (link) {
                window.location.href = link.href;
            }
        });
    });
    
    // Table column resizing (optional enhancement)
    enhanceTableColumns();
}

/**
 * Enhance table columns with better overflow handling
 */
function enhanceTableColumns() {
    const tables = document.querySelectorAll('table');
    tables.forEach(table => {
        const cells = table.querySelectorAll('td[class*="truncate"]');
        cells.forEach(cell => {
            cell.setAttribute('title', cell.textContent.trim());
        });
    });
}

/**
 * Copy to clipboard utility
 */
window.copyToClipboard = function(text, successMessage = 'Disalin ke clipboard!') {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            showToast(successMessage, 'success');
        }).catch(() => {
            fallbackCopyToClipboard(text, successMessage);
        });
    } else {
        fallbackCopyToClipboard(text, successMessage);
    }
};

/**
 * Fallback copy to clipboard for older browsers
 */
function fallbackCopyToClipboard(text, successMessage) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    
    try {
        document.execCommand('copy');
        showToast(successMessage, 'success');
    } catch (err) {
        showToast('Gagal menyalin', 'error');
    }
    
    document.body.removeChild(textarea);
}

/**
 * Show toast notification.
 * Prefer Alpine store ($store.toasts) bila tersedia (stack, progress bar).
 * Fallback ke DOM vanilla lama supaya backward-compat penuh.
 */
window.showToast = function(message, type = 'info') {
    const store = window.Alpine && window.Alpine.store && window.Alpine.store('toasts');
    if (store && typeof store.push === 'function') {
        store.push(message, type);
        return;
    }

    const toast = document.createElement('div');
    const colors = {
        success: 'bg-green-600',
        error: 'bg-rose-600',
        warning: 'bg-amber-600',
        info: 'bg-blue-600'
    };

    toast.className = `fixed bottom-6 right-6 z-50 ${colors[type] || colors.info} text-white px-6 py-3 rounded-lg shadow-lg font-semibold text-sm transform transition-[opacity,transform] duration-200 translate-y-0 opacity-100`;
    toast.setAttribute('role', 'status');
    toast.textContent = message;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.transform = 'translateY(1rem)';
        toast.style.opacity = '0';
        setTimeout(() => {
            if (toast.parentNode) document.body.removeChild(toast);
        }, 300);
    }, 3000);
};

/**
 * Alpine store 'toasts' — queue, stack, auto-dismiss.
 */
document.addEventListener('alpine:init', () => {
    if (!window.Alpine || !window.Alpine.store) return;

    window.Alpine.store('toasts', {
        items: [],
        _id: 0,
        push(message, type = 'info', timeout = 4000) {
            const id = ++this._id;
            this.items.push({ id, message, type, timeout });
            if (timeout > 0) {
                setTimeout(() => this.dismiss(id), timeout);
            }
            return id;
        },
        dismiss(id) {
            this.items = this.items.filter((t) => t.id !== id);
        }
    });
});

/**
 * Catatan: komponen Alpine 'backupProgressWidget' (widget progres backup/restore)
 * telah dipindahkan ke resources/js/alpine.js — didaftarkan LANGSUNG di dalam
 * bootstrapAlpine() SEBELUM Alpine.start() agar registrasi terjamin terjadi
 * sebelum DOM dipindai. JANGAN mendaftarkannya kembali di sini (risiko race
 * dengan Alpine.start) dan JANGAN pakai <script> inline di blade.
 */

/**
 * staggerReveal — animasi berjenjang untuk section/list.
 * Menambah .is-in ke tiap elemen selector dgn delay bertahap.
 * Respect prefers-reduced-motion → langsung final tanpa delay.
 * @param {string|NodeList|Element[]} target selector atau koleksi elemen
 * @param {number} delayMs jeda antar elemen (default 60ms), max 8 langkah
 */
window.staggerReveal = function(target, delayMs = 60) {
    let els;
    if (typeof target === 'string') {
        els = Array.from(document.querySelectorAll(target));
    } else if (target instanceof Element) {
        els = [target];
    } else {
        els = Array.from(target || []);
    }
    if (!els.length) return;

    const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    els.forEach((el, i) => {
        if (reduce) {
            el.classList.add('is-in');
            return;
        }
        const step = Math.min(i, 8);
        setTimeout(() => el.classList.add('is-in'), step * delayMs);
    });
};

/**
 * Debounce utility
 */
window.debounce = function(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

/**
 * Format number with thousand separators
 */
window.formatNumber = function(num) {
    return new Intl.NumberFormat('id-ID').format(num);
};

/**
 * Format currency (IDR)
 */
window.formatCurrency = function(num) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(num);
};

/**
 * Lazy load images
 */
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                observer.unobserve(img);
            }
        });
    });
    
    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });
}

/**
 * Handle offline/online status
 */
window.addEventListener('online', () => {
    showToast('Koneksi internet tersambung kembali', 'success');
});

window.addEventListener('offline', () => {
    showToast('Koneksi internet terputus', 'warning');
});

/**
 * Print page functionality
 */
window.printPage = function() {
    window.print();
};

/**
 * Export console helper for debugging
 */
if (process.env.NODE_ENV === 'development') {
    window.adminDebug = {
        version: '1.0.0',
        logState: (component) => {
            console.log(`[Admin] ${component} state:`, Alpine.$data(component));
        }
    };
}
