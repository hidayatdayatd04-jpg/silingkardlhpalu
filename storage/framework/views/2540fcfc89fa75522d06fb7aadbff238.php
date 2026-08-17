<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mapReports): ?>
<div class="lg:col-span-8">
    <div class="mb-4 flex items-center justify-between gap-4">
        <div>
            <h2 class="text-h3 font-bold text-ink-900">Sebaran Pengaduan</h2>
            <p class="mt-0.5 text-sm text-slate-500">Lokasi geografis pengaduan berdasarkan akses Anda</p>
        </div>
    </div>

    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['padding' => false,'class' => 'overflow-hidden']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'class' => 'overflow-hidden']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        
        <div class="pl-filter">
            <div class="pl-filter-head">
                <div class="pl-filter-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    Filter Periode Laporan
                </div>
                <div class="pl-live">
                    <span class="pl-live-dot"></span>
                    <span>Pembaruan otomatis</span>
                </div>
            </div>

            <div class="pl-filter-body">
                <div class="pl-field">
                    <label class="pl-field-label" for="sp-from">Dari Tanggal</label>
                    <div class="pl-input-wrap">
                        <input type="text" id="sp-from" value="<?php echo e($from ?? now()->startOfMonth()->toDateString()); ?>" class="pl-input pl-date" placeholder="Pilih tanggal mulai" readonly />
                        <svg class="pl-input-icon-right" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                </div>
                <div class="pl-field">
                    <label class="pl-field-label" for="sp-to">Sampai Tanggal</label>
                    <div class="pl-input-wrap">
                        <input type="text" id="sp-to" value="<?php echo e($to ?? now()->endOfMonth()->toDateString()); ?>" class="pl-input pl-date" placeholder="Pilih tanggal akhir" readonly />
                        <svg class="pl-input-icon-right" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                </div>
                <div class="pl-actions">
                    <button id="sp-apply" class="pl-btn-primary">Terapkan</button>
                    <button id="sp-reset" class="pl-btn-ghost">Reset</button>
                </div>
            </div>

            <div class="pl-presets">
                <span class="pl-presets-label">Cepat</span>
                <button type="button" class="pl-chip" data-preset="today">Hari Ini</button>
                <button type="button" class="pl-chip" data-preset="7">7 Hari</button>
                <button type="button" class="pl-chip" data-preset="30">30 Hari</button>
                <button type="button" class="pl-chip is-active" data-preset="month">Bulan Ini</button>
            </div>

            <p id="sp-range-text" class="pl-range-text">
                Menampilkan laporan <b><?php echo e(\Carbon\Carbon::parse($from ?? now()->startOfMonth()->toDateString())->format('d M Y')); ?></b>
                s.d.
                <b><?php echo e(\Carbon\Carbon::parse($to ?? now()->endOfMonth()->toDateString())->format('d M Y')); ?></b>
            </p>
        </div>

        
        <div class="pl-map-wrap">
            <div id="sebaran-pengaduan-map" class="w-full h-full"></div>

            <div class="pl-legend">
                <p class="pl-legend-title">Panduan Bidang</p>
                <div class="pl-legend-row">
                    <span class="pl-legend-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11V7a3 3 0 016 0v4"/><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M12 15v2"/><path d="M8 15v2"/><path d="M16 15v2"/></svg></span>
                    <span class="pl-legend-text">Pengendalian</span>
                </div>
                <div class="pl-legend-row">
                    <span class="pl-legend-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2"/><path d="M4 6v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V6"/><path d="M10 11v5"/><path d="M14 11v5"/><path d="M12 12V8"/></svg></span>
                    <span class="pl-legend-text">Sampah &amp; LB3</span>
                </div>
                <div class="pl-legend-row">
                    <span class="pl-legend-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22V8"/><path d="M5 12H2a10 10 0 0020 0h-3"/><path d="M8 5.2C9 4 10.5 3 12 3s3 1 4 2.2"/><circle cx="12" cy="8" r="2"/><path d="M17 2h-2v4h2V2z"/><path d="M9 2H7v4h2V2z"/></svg></span>
                    <span class="pl-legend-text">RTH</span>
                </div>
                <div class="pl-legend-row">
                    <span class="pl-legend-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M3 12h18"/><path d="M3 18h12"/><rect x="15" y="12" width="6" height="8" rx="1"/><rect x="16" y="9" width="4" height="3" rx="0.5"/><circle cx="17" cy="14" r="1"/><circle cx="19" cy="17" r="1"/></svg></span>
                    <span class="pl-legend-text">Tata Penataan</span>
                </div>
                <div class="pl-count">
                    <span id="sp-count">0</span> laporan
                </div>
            </div>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php $__env->startPush('styles'); ?>
    <style>
        .pl-filter {
            background: linear-gradient(180deg, #ffffff, #f8fafc);
            border: 1px solid rgba(15, 23, 42, 0.07);
            border-radius: 20px 20px 0 0;
            padding: 18px 20px;
            box-shadow: 0 18px 40px -24px rgba(15, 23, 42, 0.35);
        }
        .dark .pl-filter {
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.55), rgba(2, 6, 23, 0.5));
            border-color: rgba(148, 163, 184, 0.14);
        }
        .pl-filter-head {
            display: flex; align-items: center; justify-content: space-between;
            gap: 12px; flex-wrap: wrap; margin-bottom: 14px;
        }
        .pl-filter-title { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: #0f172a; }
        .dark .pl-filter-title { color: #e2e8f0; }
        .pl-filter-title svg { width: 18px; height: 18px; color: #10b981; }
        .pl-live { display: flex; align-items: center; gap: 7px; font-size: 12px; font-weight: 500; color: #475569; }
        .dark .pl-live { color: #94a3b8; }
        .pl-filter-body { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 14px; }
        .pl-field { display: flex; flex-direction: column; gap: 6px; }
        .pl-field-label {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.04em; color: #64748b;
        }
        .dark .pl-field-label { color: #94a3b8; }
        .pl-input-wrap { position: relative; display: flex; align-items: center; }
        .pl-input-icon-right {
            position: absolute; right: 12px; width: 16px; height: 16px;
            color: #94a3b8; pointer-events: none;
        }
        .pl-input {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.5rem 0.75rem;
            font-size: 13px;
            color: #0f172a;
            background: #fff;
            transition: all .15s ease;
        }
        .dark .pl-input {
            border-color: rgba(148, 163, 184, 0.2);
            background: rgba(2, 6, 23, 0.5);
            color: #e2e8f0;
        }
        .pl-input:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        }
        .pl-date {
            padding-left: 12px; padding-right: 38px;
            min-width: 200px; cursor: pointer; letter-spacing: 0.02em;
        }
        .pl-actions { display: flex; gap: 10px; }
        .pl-btn-primary {
            background: linear-gradient(135deg, #059669, #047857);
            color: #fff;
            font-weight: 600;
            font-size: 13px;
            padding: 0.5rem 1.1rem;
            border-radius: 10px;
            box-shadow: 0 6px 16px -6px rgba(5, 150, 105, 0.6);
            transition: all .15s ease;
        }
        .pl-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 10px 22px -8px rgba(5, 150, 105, 0.65); }
        .pl-btn-ghost {
            background: #f1f5f9;
            color: #475569;
            font-weight: 600;
            font-size: 13px;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            transition: all .15s ease;
        }
        .dark .pl-btn-ghost { background: rgba(51, 65, 85, 0.5); color: #cbd5e1; }
        .pl-btn-ghost:hover { background: #e2e8f0; color: #334155; }
        .dark .pl-btn-ghost:hover { background: rgba(71, 85, 105, 0.6); color: #f8fafc; }

        .pl-presets { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
        .pl-presets-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #94a3b8; }
        .pl-chip {
            font-size: 12px; font-weight: 600; color: #475569;
            background: #f1f5f9; border: 1px solid transparent; border-radius: 9999px;
            padding: 6px 14px; cursor: pointer; transition: all .15s ease;
        }
        .pl-chip:hover { background: #e2e8f0; color: #334155; }
        .dark .pl-chip { background: rgba(51, 65, 85, 0.5); color: #cbd5e1; }
        .dark .pl-chip:hover { background: rgba(71, 85, 105, 0.6); color: #f8fafc; }
        .pl-chip.is-active {
            background: linear-gradient(135deg, #059669, #047857); color: #fff;
            box-shadow: 0 6px 16px -6px rgba(5, 150, 105, 0.6);
        }
        .pl-range-text { margin-top: 12px; font-size: 12px; color: #64748b; }
        .dark .pl-range-text { color: #94a3b8; }
        .pl-range-text b { color: #0f172a; }
        .dark .pl-range-text b { color: #e2e8f0; }

        .pl-map-wrap {
            position: relative;
            width: 100%;
            height: 560px;
            border-radius: 0 0 20px 20px;
            overflow: hidden;
            border-top: 1px solid rgba(0, 0, 0, 0.04);
        }
        .pl-map-wrap .maplibregl-map { width: 100%; height: 100%; }
        .pl-map-wrap .maplibregl-ctrl-group { border-radius: 10px !important; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.1) !important; }
        .pl-map-wrap .maplibregl-ctrl { margin: 12px !important; }
        .pl-map-wrap .maplibregl-ctrl-top-left { top: 12px !important; left: 12px !important; }
        .pl-map-wrap .maplibregl-ctrl-bottom-right { bottom: 12px !important; right: 12px !important; }

        .pl-live-dot {
            width: 8px; height: 8px; border-radius: 9999px; background: #10b981;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pl-pulse 1.8s infinite;
        }
        @keyframes pl-pulse {
            0%   { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6); }
            70%  { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .pl-legend {
            position: absolute; bottom: 70px; left: 20px; z-index: 20;
            background: rgba(255, 255, 255, 0.96); backdrop-filter: blur(8px);
            border: 1px solid rgba(0, 0, 0, 0.06); border-radius: 14px;
            padding: 12px 14px; box-shadow: 0 8px 26px -12px rgba(0, 0, 0, 0.3);
            max-width: 210px;
        }
        .dark .pl-legend { background: rgba(15, 23, 42, 0.85); border-color: rgba(148,163,184,0.15); }
        .pl-legend-title {
            font-size: 10px; font-weight: 800; text-transform: uppercase;
            letter-spacing: 0.12em; color: #64748b; margin-bottom: 8px;
        }
        .dark .pl-legend-title { color: #94a3b8; }
        .pl-legend-row { display: flex; align-items: center; gap: 9px; padding: 3px 0; }
        .pl-legend-icon {
            width: 22px; height: 22px; border-radius: 7px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            background: rgba(16, 185, 129, 0.12); color: #10b981;
        }
        .dark .pl-legend-icon { background: rgba(16, 185, 129, 0.18); color: #6ee7b7; }
        .pl-legend-text { font-size: 12px; color: #475569; font-weight: 500; }
        .dark .pl-legend-text { color: #cbd5e1; }

        .pl-count {
            margin-top: 10px; padding-top: 10px;
            border-top: 1px solid rgba(15, 23, 42, 0.08);
            display: flex; align-items: center; gap: 6px;
            font-size: 12px; font-weight: 600; color: #064e3b;
        }
        .dark .pl-count { border-top-color: rgba(148, 163, 184, 0.18); color: #a7f3d0; }
        .pl-count #pl-count, .pl-count #sp-count { font-size: 14px; font-weight: 800; color: #047857; }
        .dark .pl-count #pl-count, .dark .pl-count #sp-count { color: #6ee7b7; }

        /* flatpickr dark theme (scoped ke dalam .dark) */
        .dark .flatpickr-calendar {
            background: #0f172a; border-color: rgba(148, 163, 184, 0.2);
            box-shadow: 0 10px 36px rgba(0, 0, 0, 0.45); color: #e2e8f0;
        }
        .dark .flatpickr-calendar .flatpickr-months,
        .dark .flatpickr-calendar .flatpickr-month,
        .dark .flatpickr-calendar .flatpickr-weekdays { background: #0f172a; color: #e2e8f0; }
        .dark .flatpickr-calendar .flatpickr-monthDropdown-months,
        .dark .flatpickr-calendar .numInputWrapper input { background: #0f172a; color: #e2e8f0; }
        .dark .flatpickr-calendar .flatpickr-weekday { color: #94a3b8; }
        .dark .flatpickr-calendar .flatpickr-day { color: #cbd5e1; }
        .dark .flatpickr-calendar .flatpickr-day:hover { background: #1e293b; border-color: #1e293b; }
        .dark .flatpickr-calendar .flatpickr-day.selected,
        .dark .flatpickr-calendar .flatpickr-day.startRange,
        .dark .flatpickr-calendar .flatpickr-day.endRange { background: #10b981; border-color: #10b981; color: #fff; }
        .dark .flatpickr-calendar .flatpickr-day.flatpickr-disabled,
        .dark .flatpickr-calendar .flatpickr-day.prevMonthDay,
        .dark .flatpickr-calendar .flatpickr-day.nextMonthDay { color: #475569; }
        .dark .flatpickr-calendar .numInputWrapper span.arrowUp:after { border-bottom-color: #94a3b8; }
        .dark .flatpickr-calendar .numInputWrapper span.arrowDown:after { border-top-color: #94a3b8; }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (!document.getElementById('sebaran-pengaduan-map')) return;

            var reports = <?php echo json_encode($mapReports ?? [], 15, 512) ?>;
            var currentFrom = '<?php echo e($from ?? now()->startOfMonth()->toDateString()); ?>';
            var currentTo = '<?php echo e($to ?? now()->endOfMonth()->toDateString()); ?>';

            // Warna marker SERAGAM — pembeda antar bidang adalah IKON, bukan warna titik.
            var MARKER_COLOR = '#10b981';
            var BIDANG_ICON = {
                'pengendalian': 'bidang-pengendalian',
                'sampah-lb3': 'bidang-sampah-lb3',
                'rth': 'bidang-rth',
                'tata-penataan': 'bidang-tata-penataan'
            };

            function buildMarkerItems(items) {
                return (items || []).map(function (r) {
                    var type = BIDANG_ICON[r.bidang] || 'pengaduan';
                    var details = [
                        { icon: 'doc', value: (r.bidang_label || 'Laporan') + ' • ' + (r.jenis_label || r.jenis_pengaduan || '-') },
                        { icon: 'lokasi', value: r.alamat || 'Alamat tidak diisi' },
                        { icon: 'kalender', value: 'Lapor: ' + (r.tanggal || '-') },
                        { icon: 'lokasi', value: 'Koordinat: ' + (r.latitude != null ? r.latitude : '-') + ', ' + (r.longitude != null ? r.longitude : '-') }
                    ];
                    if (r.deskripsi) { details.push({ icon: 'doc', value: r.deskripsi }); }
                    return {
                        lat: r.latitude,
                        lng: r.longitude,
                        type: type,
                        opts: { color: MARKER_COLOR, size: 34 },
                        popup: {
                            type: type,
                            color: MARKER_COLOR,
                            nama: r.nomor_tiket,
                            kategori: r.bidang_label || 'Laporan',
                            details: details,
                            status: { text: r.status_label || r.status, color: r.status_color || MARKER_COLOR }
                        }
                    };
                });
            }

            function updateCount(n) {
                var el = document.getElementById('sp-count');
                if (el) el.textContent = n;
            }

            var map = null;
            var currentMarkers = [];

            function renderMarkers(items, fit) {
                currentMarkers.forEach(function (m) { if (m && m.remove) m.remove(); });
                currentMarkers = [];
                if (!items || !items.length) { updateCount(0); return; }
                currentMarkers = window.DlhMarkers.renderAll(map, buildMarkerItems(items), false);
                updateCount(items.length);
                if (fit && currentMarkers.length) {
                    var b = new maplibregl.LngLatBounds();
                    currentMarkers.forEach(function (m) { try { b.extend(m.getLngLat()); } catch (e) {} });
                    if (!b.isEmpty()) { map.fitBounds(b, { padding: 60, maxZoom: 15 }); }
                }
            }

            function formatID(ymd) {
                var p = (ymd || '').split('-');
                if (p.length !== 3) return ymd;
                var months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
                var m = parseInt(p[1], 10);
                if (isNaN(m) || m < 1 || m > 12) return ymd;
                return parseInt(p[2], 10) + ' ' + months[m - 1] + ' ' + p[0];
            }

            function loadReports(from, to, fit) {
                currentFrom = from;
                currentTo = to;
                var url = '<?php echo e(route("admin.peta-laporan.data")); ?>?from=' + encodeURIComponent(from) + '&to=' + encodeURIComponent(to);
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (r) { return r.json(); })
                    .then(function (d) {
                        renderMarkers(d.reports || [], fit);
                        var rt = document.getElementById('sp-range-text');
                        if (rt) {
                            rt.innerHTML = 'Menampilkan laporan <b>'
                                + formatID(from) + '</b> s.d. <b>'
                                + formatID(to) + '</b>';
                        }
                    })
                    .catch(function (e) { console.error('[SebaranPengaduan]', e); });
            }

            function currentMonthRange() {
                var now = new Date();
                var y = now.getFullYear(), m = now.getMonth();
                var last = new Date(y, m + 1, 0).getDate();
                var pad = function (n) { return (n < 10 ? '0' : '') + n; };
                return { from: y + '-' + pad(m + 1) + '-01', to: y + '-' + pad(m + 1) + '-' + pad(last) };
            }

            // ── Date pickers (flatpickr) ──
            var fpFrom = null, fpTo = null;
            function initPickers() {
                if (!window.flatpickr) { return setTimeout(initPickers, 80); }
                var filterEl = document.querySelector('.pl-filter');
                var common = { dateFormat: 'Y-m-d', allowInput: false, appendTo: filterEl };
                fpFrom = flatpickr('#sp-from', common);
                fpTo = flatpickr('#sp-to', common);

                var fromEl = document.getElementById('sp-from');
                var toEl = document.getElementById('sp-to');
                var ymd = function (d) {
                    var p = function (n) { return (n < 10 ? '0' : '') + n; };
                    return d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate());
                };

                function clearActiveChips() {
                    document.querySelectorAll('.pl-chip').forEach(function (c) { c.classList.remove('is-active'); });
                }

                document.getElementById('sp-apply').addEventListener('click', function () {
                    clearActiveChips();
                    loadReports(fromEl.value, toEl.value, true);
                });
                document.getElementById('sp-reset').addEventListener('click', function () {
                    var r = currentMonthRange();
                    fpFrom.setDate(r.from, true); fpTo.setDate(r.to, true);
                    clearActiveChips();
                    var mc = document.querySelector('.pl-chip[data-preset="month"]');
                    if (mc) mc.classList.add('is-active');
                    loadReports(r.from, r.to, true);
                });

                document.querySelectorAll('.pl-chip').forEach(function (chip) {
                    chip.addEventListener('click', function () {
                        var preset = chip.getAttribute('data-preset');
                        var r, now = new Date();
                        if (preset === 'today') {
                            var t = ymd(now); r = { from: t, to: t };
                        } else if (preset === 'month') {
                            r = currentMonthRange();
                        } else {
                            var days = parseInt(preset, 10);
                            var to = ymd(now);
                            var f = new Date(now); f.setDate(now.getDate() - (days - 1));
                            r = { from: ymd(f), to: to };
                        }
                        fpFrom.setDate(r.from, true); fpTo.setDate(r.to, true);
                        clearActiveChips();
                        chip.classList.add('is-active');
                        loadReports(r.from, r.to, true);
                    });
                });
            }

            function init() {
                if (!window.maplibregl || !window.DlhMarkers) {
                    return setTimeout(init, 80);
                }

                map = new maplibregl.Map({
                    container: 'sebaran-pengaduan-map',
                    style: 'https://basemaps.cartocdn.com/gl/voyager-gl-style/style.json',
                    center: [119.87, -0.90],
                    zoom: 12,
                    attributionControl: false
                });

                map.addControl(new window.DlhZoomControl(), 'top-right');
                if (window.DlhBasemapSwitcher) map.addControl(new window.DlhBasemapSwitcher(), 'bottom-right');
                if (window.DlhFullscreenControl) map.addControl(new window.DlhFullscreenControl(), 'top-left');
                if (window.DlhWeatherControl) map.addControl(new window.DlhWeatherControl({ position: 'top-left' }), 'top-left');

                map.on('load', function () { renderMarkers(reports, true); });
                map.on('basemap-changed', function () { renderMarkers(reports, false); });

                if (window.dlhAddLocBtn) window.dlhAddLocBtn(map);

                // Auto-refresh: marker baru muncul otomatis tanpa refresh manual
                setInterval(function () { loadReports(currentFrom, currentTo, false); }, 45000);
            }

            initPickers();
            init();
        });
    </script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\xampp\htdocs\DLH - PALU\resources\views/admin/partials/sebaran-pengaduan.blade.php ENDPATH**/ ?>