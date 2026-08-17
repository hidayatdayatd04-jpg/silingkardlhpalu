import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

// Paksa seluruh @font-face (termasuk @fontsource) menggunakan font-display:
// optional agar tidak ada layout shift (CLS) saat font web selesai dimuat —
// font dipakai bila sudah siap di first paint, jika tidak fallback dipakai
// permanen tanpa swap.
const fontDisplayOptional = () => ({
    postcssPlugin: 'font-display-optional',
    AtRule: {
        'font-face': (atRule) => {
            let found = false;
            atRule.walkDecls('font-display', (decl) => {
                found = true;
                decl.value = 'optional';
            });
            if (!found) {
                atRule.append({ prop: 'font-display', value: 'optional' });
            }
        },
    },
});
fontDisplayOptional.postcss = true;

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/admin-common.js',
                'resources/js/map-bundle.js',
                'resources/js/dashboard-charts.js',
                'resources/js/flatpickr-init.js',
                'resources/js/tata-lingkungan.ts',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    css: {
        postcss: {
            plugins: [fontDisplayOptional()],
        },
    },
    build: {
        // Source map penuh agar audit Best Practices Lighthouse
        // (valid-source-maps) lulus pada bundle produksi.
        sourcemap: true,
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
