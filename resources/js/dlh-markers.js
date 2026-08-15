/**
 * DLH Custom Map Markers v2
 * Komponen marker SVG untuk seluruh peta di project DLH Palu.
 * Ikon SVG real, hover tidak geser koordinat, popup detail premium.
 *
 * @example
 *   DlhMarkers.addToMap(map, 'taman', [119.87, -0.90], popupHTML)
 *   DlhMarkers.popup({ nama: 'Taman X', type: 'taman', details: [...] })
 *   DlhMarkers.detectType(layerName, properties) // auto-detect dari nama/props
 */
window.DlhMarkers = (function () {
    'use strict';

    // ═══════════════ Definisi Ikon SVG ═══════════════

    var ICONS = {
        // ─── RTH ───
        taman: {
            color: '#22c55e', bg: '#f0fdf4', size: 30,
            label: 'Taman Kota',
            svg: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22V8"/><path d="M5 12H2a10 10 0 0020 0h-3"/><path d="M8 5.2C9 4 10.5 3 12 3s3 1 4 2.2"/><circle cx="12" cy="8" r="2"/></svg>',
        },
        hutan: {
            color: '#15803d', bg: '#f0fdf4', size: 30,
            label: 'Hutan Kota',
            svg: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 22v-2"/><path d="M9 18H4l4-6h8l4 6h-5"/><path d="M12 12V2"/><path d="M7 7l5-5 5 5"/><path d="M4 14l8-8 8 8"/></svg>',
        },
        pohon: {
            color: '#16a34a', bg: '#f0fdf4', size: 24,
            label: 'Pohon Pelindung',
            svg: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22V10"/><path d="M6 14l6-8 6 8"/><path d="M8 18l4-6 4 6"/></svg>',
        },
        jalur_hijau: {
            color: '#16a34a', bg: '#f0fdf4', size: 26,
            label: 'Jalur Hijau',
            svg: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20c2-4 4-8 8-12"/><path d="M12 8c4 4 6 8 8 12"/></svg>',
        },
        aset_rth: {
            color: '#eab308', bg: '#fefce8', size: 28,
            label: 'Aset RTH',
            svg: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>',
        },

        // ─── Sampah & LB3 ───
        tpa: {
            color: '#b45309', bg: '#fffbeb', size: 30,
            label: 'TPA',
            svg: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>',
        },
        tpst: {
            color: '#92400e', bg: '#fffbeb', size: 30,
            label: 'TPST',
            svg: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M12 11v6"/></svg>',
        },
        bank_sampah: {
            color: '#0d9488', bg: '#f0fdfa', size: 28,
            label: 'Bank Sampah',
            svg: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>',
        },
        tps: {
            color: '#6366f1', bg: '#eef2ff', size: 28,
            label: 'TPS',
            svg: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 6h20"/><path d="M6 6V4c0-1.1.9-2 2-2h8c1.1 0 2 .9 2 2v2"/><path d="M4 6v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V6"/><path d="M10 11v5"/><path d="M14 11v5"/></svg>',
        },
        armada: {
            color: '#2563eb', bg: '#eff6ff', size: 34,
            label: 'Armada',
            svg: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 00-2-2H4a2 2 0 00-2 2v11a1 1 0 001 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 001-1v-3.65a1 1 0 00-.22-.624l-3.48-4.35A1 1 0 0017.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>',
        },

        // ─── Pengawasan & Pengaduan ───
        lokasi: {
            color: '#ef4444', bg: '#fef2f2', size: 32,
            label: 'Lokasi',
            svg: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>',
        },
        objek_pengawasan: {
            color: '#10b981', bg: '#ecfdf5', size: 30,
            label: 'Objek Pengawasan',
            svg: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>',
        },
        pengaduan: {
            color: '#f97316', bg: '#fff7ed', size: 28,
            label: 'Pengaduan',
            svg: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7.9 20A9 9 0 104 16.1L2 22z"/><path d="M8 12h.01"/><path d="M12 12h.01"/><path d="M16 12h.01"/></svg>',
        },
        titik_kumpul: {
            color: '#f59e0b', bg: '#fffbeb', size: 28,
            label: 'Titik Kumpul',
            svg: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>',
        },

        // ─── RTH Tambahan ───

        pinjam_taman: {
            color: '#8b5cf6', bg: '#f5f3ff', size: 28,
            label: 'Penyewaan Taman',
            svg: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
        },

        // ─── Bidang Laporan ───
        'bidang-pengendalian': {
            color: '#10b981', bg: '#f0fdf4', size: 32,
            label: 'Pengendalian',
            svg: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11V7a3 3 0 016 0v4"/><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M12 15v2"/><path d="M8 15v2"/><path d="M16 15v2"/></svg>',
        },
        'bidang-sampah-lb3': {
            color: '#10b981', bg: '#f0fdf4', size: 32,
            label: 'Sampah & LB3',
            svg: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2"/><path d="M4 6v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V6"/><path d="M10 11v5"/><path d="M14 11v5"/><path d="M12 12V8"/></svg>',
        },
        'bidang-rth': {
            color: '#10b981', bg: '#f0fdf4', size: 32,
            label: 'RTH',
            svg: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22V8"/><path d="M5 12H2a10 10 0 0020 0h-3"/><path d="M8 5.2C9 4 10.5 3 12 3s3 1 4 2.2"/><circle cx="12" cy="8" r="2"/><path d="M17 2h-2v4h2V2z"/><path d="M9 2H7v4h2V2z"/></svg>',
        },
        'bidang-tata-penataan': {
            color: '#10b981', bg: '#f0fdf4', size: 32,
            label: 'Tata Penataan',
            svg: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M3 12h18"/><path d="M3 18h12"/><rect x="15" y="12" width="6" height="8" rx="1"/><rect x="16" y="9" width="4" height="3" rx="0.5"/><circle cx="17" cy="14" r="1"/><circle cx="19" cy="17" r="1"/></svg>',
        },

        // ─── Generic ───
        default: {
            color: '#6b7280', bg: '#f9fafb', size: 26,
            label: 'Lokasi',
            svg: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
        },
    };

    // ═══════════════ Small SVG icons untuk popup rows ═══════════════

    var ROW_ICONS = {
        lokasi: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>',
        area: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>',
        panjang: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6l-12 12"/><path d="M6 6l0 0"/><path d="M18 6l0 0"/></svg>',
        pohon: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22V10"/><path d="M6 14l6-8 6 8"/></svg>',
        aset: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/></svg>',
        status: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>',
        fasilitas: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
        volume: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8v12a2 2 0 01-2 2H5a2 2 0 01-2-2V8"/><path d="M1 12l10-4 10 4-10 4-10-4z"/></svg>',
        kalender: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
        doc: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>',
    };

    // ═══════════════ Auto-detect marker type ═══════════════

    /**
     * Deteksi tipe marker dari nama layer atau properties.
     * @param {string} layerName
     * @param {object} [props]
     * @returns {string} marker type key
     */
    function detectType(layerName, props) {
        var name = (layerName || '').toLowerCase();
        var jenis = ((props && props.JENIS_ASET) || '').toLowerCase();
        var nama = ((props && props.NAMA) || '').toLowerCase();

        // Cek dari JENIS_ASET
        if (jenis.indexOf('gazebo') !== -1 || jenis.indexOf('tempat duduk') !== -1 || jenis.indexOf('bangku') !== -1) return 'aset_rth';
        if (jenis.indexOf('penerangan') !== -1 || jenis.indexOf('lampu') !== -1) return 'aset_rth';
        if (jenis.indexOf('permainan') !== -1) return 'aset_rth';
        if (jenis.indexOf('olahraga') !== -1) return 'aset_rth';
        if (jenis.indexOf('fasilitas') !== -1) return 'aset_rth';

        // Cek dari NAMA
        if (nama.indexOf('taman') !== -1) return 'taman';
        if (nama.indexOf('hutan') !== -1) return 'hutan';
        if (nama.indexOf('pohon') !== -1) return 'pohon';

        // Cek dari nama layer
        if (name.indexOf('taman') !== -1) return 'taman';
        if (name.indexOf('hutan') !== -1) return 'hutan';
        if (name.indexOf('pohon') !== -1) return 'pohon';
        if (name.indexOf('jalur') !== -1 || name.indexOf('hijau') !== -1) return 'jalur_hijau';
        if (name.indexOf('aset') !== -1) return 'aset_rth';
        if (name.indexOf('bank sampah') !== -1) return 'bank_sampah';
        if (name.indexOf('tpst') !== -1) return 'tpst';
        if (name.indexOf('tpa') !== -1) return 'tpa';
        if (name.indexOf('tps') !== -1) return 'tps';
        if (name.indexOf('armada') !== -1) return 'armada';
        if (name.indexOf('pinjam') !== -1) return 'pinjam_taman';
        if (name.indexOf('pengaduan') !== -1) return 'pengaduan';
        if (name.indexOf('pengawasan') !== -1) return 'objek_pengawasan';

        return 'default';
    }

    // ═══════════════ Pembuat Element Marker ═══════════════

    /**
     * Membuat DOM element marker.
     * Hover effect di dalam wrapper sehingga transform TIDAK menggeser koordinat.
     */
    function createEl(type, opts) {
        opts = opts || {};
        var def = ICONS[type] || ICONS['default'];
        var size = opts.size || def.size;
        var color = opts.color || def.color;
        var bgColor = opts.bg || def.bg;

        // Outer container (tidak di-transform)
        var el = document.createElement('div');
        el.className = 'dlh-marker dlh-marker-' + type;
        el.setAttribute('data-marker-type', type);
        el.style.cssText = 'width:' + size + 'px;height:' + size + 'px;position:relative;cursor:pointer;';

        // Inner element (yang di-transform saat hover)
        var inner = document.createElement('div');
        inner.style.cssText = [
            'width:100%',
            'height:100%',
            'background:' + color,
            'border-radius:50%',
            'border:2.5px solid white',
            'box-shadow:0 2px 8px rgba(0,0,0,0.22),0 0 0 1px rgba(0,0,0,0.04)',
            'display:flex',
            'align-items:center',
            'justify-content:center',
            'color:white',
            'transition:opacity 0.15s ease',
            'transform-origin:center center',
        ].join(';');
        inner.innerHTML = def.svg;
        el.appendChild(inner);

        // Hover: HANYA ubah opacity (TANPA transform/shadow/border)
        // Ini 100% aman untuk MapLibre marker positioning
        el.addEventListener('mouseenter', function () {
            inner.style.opacity = '0.85';
        });
        el.addEventListener('mouseleave', function () {
            inner.style.opacity = '1';
        });

        return el;
    }

    // ═══════════════ Popup Component ═══════════════

    /**
     * Buat popup HTML premium.
     * @param {object} cfg
     *   - nama: string
     *   - kategori: string
     *   - type: string (marker type key)
     *   - color: string (override warna)
     *   - details: [{ icon: string(key atau SVG), value: string }]
     *   - status: { text: string, color: string } (opsional)
     */
    function popup(cfg) {
        var def = ICONS[cfg.type || 'default'];
        var color = cfg.color || def.color;
        var bgColor = cfg.bg || def.bg;
        var markerSvg = def.svg;

        // === Build detail rows from ALL properties (dynamic, not hardcoded) ===
        var rows = '';
        var details = cfg.details || [];
        if (details.length > 0) {
            details.forEach(function (d) {
                if (!d.value) return;
                var iconHtml = ROW_ICONS[d.icon] || ROW_ICONS.lokasi;
                rows += '<div class="dlh-popup-row">'
                    + '<span class="dlh-popup-row-icon">' + iconHtml + '</span>'
                    + '<span class="dlh-popup-row-text">' + d.value + '</span>'
                    + '</div>';
            });
        }

        var statusHtml = '';
        if (cfg.status && cfg.status.text) {
            var sc = cfg.status.color || '#6b728b';
            statusHtml = '<div class="dlh-popup-status">'
                + '<span class="dlh-popup-status-dot" style="background:' + sc + '"></span>'
                + cfg.status.text
                + '</div>';
        }

        // Edit button (only if layerId and featureIndex are provided)
        var editBtnHtml = '';
        if (cfg.layerId !== undefined && cfg.featureIndex !== undefined) {
            editBtnHtml = '<button class="dlh-popup-edit-btn" data-layer-id="' + cfg.layerId + '" data-feature-index="' + cfg.featureIndex + '" style="position:absolute;top:8px;right:30px;width:22px;height:22px;border-radius:50%;background:rgba(16,185,129,0.1);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;z-index:101;transition:all 0.15s;" title="Edit marker">'
                + '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>'
                + '</button>';
        }

        return '<div class="dlh-popup">'
            + editBtnHtml
            + '<div class="dlh-popup-header" style="border-left:3px solid ' + color + '">'
            + '<div class="dlh-popup-marker-icon" style="background:' + color + '">' + markerSvg + '</div>'
            + '<div class="dlh-popup-title-group">'
            + '<div class="dlh-popup-name">' + (cfg.nama || '-') + '</div>'
            + '<div class="dlh-popup-cat">' + (cfg.kategori || '') + '</div>'
            + '</div>'
            + '</div>'
            + (rows ? '<div class="dlh-popup-body">' + rows + '</div>' : '')
            + statusHtml
            + '</div>';
    }

    // ═══════════════ Custom Overlay (TIDAK shift saat zoom) ═══════════════

    /**
     * DlhMarkerOverlay - Custom overlay yang positioning via requestAnimationFrame.
     * TIDAK menggunakan MapLibre Marker (yang punya offset cache issue).
     * Positioning diupdate setiap frame → 100% presisi saat zoom/pan.
     */
    function DlhMarkerOverlay(map, element, lngLat) {
        this._map = map;
        this._el = element;
        this._lngLat = lngLat;
        this._popup = null;
        this._popupEl = null;
        this._visible = true;

        // Buat container wrapper
        this._container = document.createElement('div');
        this._container.style.cssText = 'position:absolute;top:0;left:0;pointer-events:none;z-index:5;';
        this._container.appendChild(this._el);
        this._el.style.pointerEvents = 'auto';
        this._el.style.position = 'absolute';

        // Inject ke map
        this._map.getContainer().querySelector('.maplibregl-canvas-container').parentNode.appendChild(this._container);

        // Update posisi pertama
        this._updatePosition();

        // Update posisi setiap frame
        var self = this;
        this._onMove = function () { self._updatePosition(); };
        this._map.on('move', this._onMove);
        this._map.on('zoom', this._onMove);

        // Click handler untuk popup
        this._el.addEventListener('click', function (e) {
            e.stopPropagation();
            if (self._popupHtml) self._showPopup();
        });
    }

    DlhMarkerOverlay.prototype._updatePosition = function () {
        if (!this._visible) return;
        var point = this._map.project(this._lngLat);
        var w = this._el.offsetWidth || 30;
        var h = this._el.offsetHeight || 30;
        this._container.style.transform = 'translate(' + (point.x - w / 2) + 'px,' + (point.y - h / 2) + 'px)';
    };

    DlhMarkerOverlay.prototype.setLngLat = function (lngLat) {
        this._lngLat = lngLat;
        this._updatePosition();
    };

    DlhMarkerOverlay.prototype.getLngLat = function () {
        return this._lngLat;
    };

    DlhMarkerOverlay.prototype.setPopup = function (html, opts) {
        opts = opts || {};
        this._popupHtml = html;
        this._popupOpts = opts;
    };

    DlhMarkerOverlay.prototype._showPopup = function () {
        if (!this._popupHtml) return;

        // Close existing popup — clean up all listeners
        if (this._popupEl) {
            this._cleanupPopup();
            return;
        }

        var popup = document.createElement('div');
        popup.innerHTML = this._popupHtml;

        // Styling inline - tanpa class conflict
        popup.style.cssText = 'position:absolute;min-width:220px;max-width:300px;background:#fff;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,0.15);border:1px solid rgba(0,0,0,0.06);overflow:hidden;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;z-index:100;pointer-events:auto;';

        // Close button
        var closeBtn = document.createElement('button');
        closeBtn.innerHTML = '&times;';
        closeBtn.style.cssText = 'position:absolute;top:6px;right:6px;width:22px;height:22px;border-radius:50%;background:rgba(0,0,0,0.05);border:none;font-size:16px;color:#94a3b8;cursor:pointer;display:flex;align-items:center;justify-content:center;z-index:101;line-height:1;';
        var self = this;
        closeBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            self._cleanupPopup();
        });
        popup.appendChild(closeBtn);

        // Position: project koordinat ke pixel, append ke map container
        var point = this._map.project(this._lngLat);
        var mapContainer = this._map.getContainer();
        popup.style.left = point.x + 'px';
        popup.style.top = (point.y - 15) + 'px';
        popup.style.transform = 'translate(-50%, -100%)';

        mapContainer.appendChild(popup);
        this._popupEl = popup;

        // Update position saat peta bergerak — store reference for cleanup
        this._updatePopupPos = function () {
            if (!self._popupEl) return;
            var p = self._map.project(self._lngLat);
            self._popupEl.style.left = p.x + 'px';
            self._popupEl.style.top = (p.y - 15) + 'px';
        };
        this._map.on('move', this._updatePopupPos);
        this._map.on('zoom', this._updatePopupPos);

        // Close on click outside — store reference for cleanup
        this._closeHandler = function (e) {
            if (self._popupEl && !popup.contains(e.target) && !self._el.contains(e.target)) {
                self._cleanupPopup();
            }
        };
        setTimeout(function () { document.addEventListener('click', self._closeHandler); }, 200);
    };

    DlhMarkerOverlay.prototype._cleanupPopup = function () {
        if (this._popupEl) {
            this._popupEl.remove();
            this._popupEl = null;
        }
        if (this._updatePopupPos) {
            this._map.off('move', this._updatePopupPos);
            this._map.off('zoom', this._updatePopupPos);
            this._updatePopupPos = null;
        }
        if (this._closeHandler) {
            document.removeEventListener('click', this._closeHandler);
            this._closeHandler = null;
        }
    };

    DlhMarkerOverlay.prototype.remove = function () {
        this._map.off('move', this._onMove);
        this._map.off('zoom', this._onMove);
        this._cleanupPopup();
        this._container.remove();
        this._visible = false;
    };

    DlhMarkerOverlay.prototype.addTo = function (map) {
        this._map = map;
        this._map.getContainer().querySelector('.maplibregl-canvas-container').parentNode.appendChild(this._container);
        this._map.on('move', this._onMove);
        this._map.on('zoom', this._onMove);
        this._visible = true;
        this._updatePosition();
        return this;
    };

    // ═══════════════ API Publik ═══════════════

    function addToMap(map, type, lngLat, popupHTML, opts) {
        opts = opts || {};
        var el = createEl(type, opts);

        // PAKAI CustomOverlay - TIDAK PERNAH shift saat zoom
        var overlay = new DlhMarkerOverlay(map, el, lngLat);

        if (popupHTML) {
            var def = ICONS[type] || ICONS['default'];
            overlay.setPopup(popupHTML, {
                offset: opts.popupOffset || [0, -(def.size / 2 + 4)],
                className: opts.className,
            });
        }

        return overlay;
    }

    function create(type, lngLat, popupHTML, opts) {
        opts = opts || {};
        var el = createEl(type, opts);
        var markerOpts = { element: el, anchor: opts.anchor || 'center' };
        if (opts.offset) markerOpts.offset = opts.offset;
        var marker = new maplibregl.Marker(markerOpts).setLngLat(lngLat);
        if (popupHTML) {
            var def = ICONS[type] || ICONS['default'];
            marker.setPopup(new maplibregl.Popup({
                offset: opts.popupOffset || [0, -(def.size / 2 + 4)],
                maxWidth: '300px',
                closeButton: true,
            }).setHTML(popupHTML));
        }
        return { element: el, marker: marker };
    }

    function renderAll(map, data, useNative) {
        var markers = [];
        (data || []).forEach(function (item) {
            if (!item.lat || !item.lng) return;
            var popupHTML = null;
            if (item.popup) {
                item.popup.color = item.popup.color || getColor(item.type);
                item.popup.type = item.type;
                popupHTML = popup(item.popup);
            }
            if (useNative) {
                // Use MapLibre native Marker — lebih kompatibel dengan v4+ DOM
                var result = create(item.type, [item.lng, item.lat], popupHTML, item.opts || {});
                result.marker.addTo(map);
                markers.push(result.marker);
            } else {
                markers.push(addToMap(map, item.type, [item.lng, item.lat], popupHTML, item.opts || {}));
            }
        });
        return markers;
    }

    function getTypes() { return Object.keys(ICONS); }
    function getColor(type) { return (ICONS[type] || ICONS['default']).color; }

    // ═══════════════ Inject CSS ═══════════════

    (function () {
        if (document.getElementById('dlh-marker-styles')) return;
        var s = document.createElement('style');
        s.id = 'dlh-marker-styles';
        s.textContent = [
            /* Popup */
            '.dlh-popup{min-width:200px;max-width:300px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;border-radius:10px;overflow:hidden}',
            '.maplibregl-popup-content{padding:0!important;border-radius:10px!important;box-shadow:0 8px 32px rgba(0,0,0,0.12)!important;border:1px solid rgba(0,0,0,0.06)!important;overflow:hidden}',
            '.maplibregl-popup-tip{border-top-color:#fff!important}',
            '.maplibregl-popup-close-button{width:20px;height:20px;border-radius:50%;background:rgba(0,0,0,0.05);border:none;font-size:14px;color:#94a3b8;display:flex;align-items:center;justify-content:center;top:6px;right:6px;transition:all .15s;line-height:1}',
            '.maplibregl-popup-close-button:hover{background:rgba(239,68,68,0.1);color:#ef4444}',
            '.dlh-popup-header{display:flex;align-items:center;gap:10px;padding:12px 14px;background:#fafbfc}',
            '.dlh-popup-marker-icon{width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;flex-shrink:0}',
            '.dlh-popup-title-group{min-width:0}',
            '.dlh-popup-name{font-size:13px;font-weight:700;color:#0f172a;line-height:1.3}',
            '.dlh-popup-cat{font-size:10px;color:#94a3b8;font-weight:500;margin-top:1px}',
            '.dlh-popup-body{padding:8px 14px 6px}',
            '.dlh-popup-row{display:flex;align-items:flex-start;gap:8px;padding:4px 0;border-bottom:1px solid #f8fafc}',
            '.dlh-popup-row:last-child{border-bottom:none}',
            '.dlh-popup-row-icon{flex-shrink:0;margin-top:1px}',
            '.dlh-popup-row-text{font-size:12px;color:#334155;line-height:1.45}',
            '.dlh-popup-status{display:flex;align-items:center;gap:6px;padding:8px 14px;background:#f8fafc;border-top:1px solid #f1f5f9;font-size:11px;color:#64748b;font-weight:500}',
            '.dlh-popup-status-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0}',
            '.dlh-marker{will-change:auto;pointer-events:auto}',
            '.dlh-overlay-popup{min-width:200px;max-width:300px;background:white;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,0.15);border:1px solid rgba(0,0,0,0.06);overflow:hidden;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;transition:none}',
            '.dlh-popup-close{position:absolute;top:6px;right:6px;width:20px;height:20px;border-radius:50%;background:rgba(0,0,0,0.05);border:none;font-size:16px;color:#94a3b8;cursor:pointer;display:flex;align-items:center;justify-content:center;z-index:11;line-height:1}',
            '.dlh-popup-close:hover{background:rgba(239,68,68,0.1);color:#ef4444}',
        ].join('\n');
        document.head.appendChild(s);
    })();

    return {
        ICONS: ICONS,
        ROW_ICONS: ROW_ICONS,
        createEl: createEl,
        addToMap: addToMap,
        create: create,
        popup: popup,
        renderAll: renderAll,
        detectType: detectType,
        getTypes: getTypes,
        getColor: getColor,
    };
})();
