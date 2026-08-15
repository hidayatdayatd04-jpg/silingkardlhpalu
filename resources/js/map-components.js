/**
 * DlhMapComponents - Reusable MapLibre GL JS controls
 * Premium basemap switcher with previews + modern scale display
 *
 * Usage:
 *   map.addControl(new DlhBasemapSwitcher(), 'bottom-right');
 *   map.addControl(new DlhScaleControl(), 'bottom-left');
 *   // Hide default attribution:
 *   map.addControl(new maplibregl.AttributionControl({ compact: false }), 'bottom-right');
 *   document.querySelector('.maplibregl-ctrl-attrib')?.remove();
 */

// ═══════════════ Basemap Switcher with Previews ═══════════════
window.DlhBasemapSwitcher = class {
    constructor(options = {}) {
        this._map = null;
        this._container = null;
        this._dropdown = null;
        this._btn = null;
        this._basemaps = options.basemaps || [
            {
                name: 'Voyager',
                url: 'https://basemaps.cartocdn.com/gl/voyager-gl-style/style.json',
                preview: 'https://basemaps.cartocdn.com/rastertiles/voyager/11/1705/1024.png',
                desc: 'Natural'
            },
            {
                name: 'Dark Matter',
                url: 'https://basemaps.cartocdn.com/gl/dark-matter-gl-style/style.json',
                preview: 'https://basemaps.cartocdn.com/rastertiles/dark_all/11/1705/1024.png',
                desc: 'Gelap'
            },
            {
                name: 'Positron',
                url: 'https://basemaps.cartocdn.com/gl/positron-gl-style/style.json',
                preview: 'https://basemaps.cartocdn.com/rastertiles/light_all/11/1705/1024.png',
                desc: 'Bersih'
            },
            {
                name: 'Satellite',
                url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
                preview: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/11/1024/1705',
                desc: 'Satelit',
                tileSize: 256
            },
            {
                name: 'OSM Standard',
                url: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                preview: 'https://tile.openstreetmap.org/11/1705/1024.png',
                desc: 'OSM',
                tileSize: 256
            },
            {
                name: 'Terrain',
                url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}',
                preview: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/11/1024/1705',
                desc: 'Topografi',
                tileSize: 256
            },
        ];
        this._currentIdx = 0;
    }

    onAdd(map) {
        this._map = map;
        // Buat wrapper utama — diposisikan langsung di map container (bukan via maplibregl ctrl)
        this._wrapper = document.createElement('div');
        this._wrapper.style.cssText = 'position:absolute;bottom:10px;right:10px;z-index:1;display:flex;align-items:center;gap:0;';

        this._container = document.createElement('div');
        this._container.className = 'maplibregl-ctrl';
        this._container.style.cssText = 'position:relative;margin:0;';

        // Button with text
        this._btn = document.createElement('button');
        this._btn.type = 'button';
        this._btn.title = 'Ganti Basemap';
        this._btn.style.cssText = 'display:flex;align-items:center;gap:6px;height:36px;padding:0 14px;background:#fff;outline:none;border:none;border-radius:10px;box-shadow:0 2px 12px rgba(0,0,0,0.1),0 0 0 1px rgba(0,0,0,0.05);cursor:pointer;transition:all 0.2s;font-family:inherit;font-size:12px;font-weight:600;color:#374151;';
        this._btn.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2 2 7l10 5 10-5-10-5Z"/><path d="m2 17 10 5 10-5"/><path d="m2 12 10 5 10-5"/>
            </svg>
            <span>Basemap</span>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="transition:transform 0.2s;" class="chevron"><path d="M6 9l6 6 6-6"/></svg>
        `;
        this._btn.onmouseenter = () => { this._btn.style.boxShadow = '0 4px 20px rgba(0,0,0,0.15),0 0 0 1px rgba(16,185,129,0.25)'; this._btn.style.color = '#047857'; };
        this._btn.onmouseleave = () => { this._btn.style.boxShadow = '0 2px 12px rgba(0,0,0,0.1),0 0 0 1px rgba(0,0,0,0.05)'; this._btn.style.color = '#374151'; };
        this._btn.onfocus = () => { this._btn.style.boxShadow = '0 0 0 4px rgba(16,185,129,0.18),0 2px 12px rgba(0,0,0,0.1)'; };
        this._btn.onblur = () => { this._btn.style.boxShadow = '0 2px 12px rgba(0,0,0,0.1),0 0 0 1px rgba(0,0,0,0.05)'; };

        // Dropdown containing 2-column grid
        this._dropdown = document.createElement('div');
        this._dropdown.style.cssText = 'display:none;position:absolute;bottom:calc(100% + 8px);right:0;width:300px;background:#fff;outline:none;border-radius:12px;box-shadow:0 20px 40px rgba(15,23,42,0.18),0 0 0 1px rgba(15,23,42,0.05);z-index:100;overflow:hidden;padding:14px;opacity:0;transform:translateY(8px) scale(0.98);transition:all 0.25s cubic-bezier(0.4,0,0.2,1);';

        const header = document.createElement('div');
        header.style.cssText = 'padding:0 0 10px 4px;font-size:10px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.12em;';
        header.textContent = 'Pilih Basemap';
        this._dropdown.appendChild(header);

        const grid = document.createElement('div');
        grid.style.cssText = 'display:grid;grid-template-columns:repeat(2, 1fr);gap:10px;';
        this._dropdown.appendChild(grid);

        this._basemaps.forEach((bm, idx) => {
            const item = document.createElement('button');
            item.type = 'button';
            item.style.cssText = `position:relative;width:100%;height:80px;border-radius:14px;border:2.5px solid ${idx === 0 ? '#10b981' : 'transparent'};overflow:hidden;cursor:pointer;background:url(${bm.preview}) center center / cover no-repeat;transition:all 0.2s ease-in-out;outline:none;padding:0;`;

            // Gradient Overlay
            const overlay = document.createElement('div');
            overlay.style.cssText = 'position:absolute;inset:0;background:linear-gradient(to top, rgba(15, 23, 42, 0.9) 0%, rgba(15, 23, 42, 0.2) 60%, rgba(15, 23, 42, 0.1) 100%);transition:opacity 0.2s;';
            item.appendChild(overlay);

            // Name
            const nameEl = document.createElement('div');
            nameEl.textContent = bm.name;
            nameEl.style.cssText = 'position:absolute;bottom:6px;left:10px;color:white;font-weight:700;font-size:11.5px;letter-spacing:-0.2px;text-shadow:0 1px 2px rgba(0,0,0,0.4);';
            item.appendChild(nameEl);

            // Checkmark corner indicator (shown when active)
            const check = document.createElement('div');
            check.className = 'basemap-check';
            check.style.cssText = `position:absolute;top:6px;right:6px;width:16px;height:16px;border-radius:50%;background:#10b981;color:white;display:${idx === 0 ? 'flex' : 'none'};align-items:center;justify-content:center;font-weight:bold;transition:transform .22s cubic-bezier(.34,1.56,.64,1),opacity .22s ease;`;
            check.innerHTML = '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4"><path d="M5 13l4 4L19 7"/></svg>';
            item.appendChild(check);

            item.onmouseenter = () => {
                if (idx !== this._currentIdx) {
                    item.style.borderColor = 'rgba(16, 185, 129, 0.4)';
                    item.style.transform = 'scale(1.03)';
                }
            };
            item.onmouseleave = () => {
                if (idx !== this._currentIdx) {
                    item.style.borderColor = 'transparent';
                    item.style.transform = 'scale(1)';
                }
            };

            item.onclick = (e) => {
                e.stopPropagation();
                if (idx === this._currentIdx) return;
                this._currentIdx = idx;
                var self = this;
                if (bm.tileSize) {
                    this._map.setStyle({
                        version: 8,
                        sources: { 'raster-tiles': { type: 'raster', tiles: [bm.url], tileSize: bm.tileSize } },
                        layers: [{ id: 'simple-tiles', type: 'raster', source: 'raster-tiles' }],
                    });
                } else {
                    this._map.setStyle(bm.url);
                }
                // Dispatch event setelah style berubah
                var fired = false;
                function fireBasemapChanged() {
                    if (fired) return;
                    fired = true;
                    self._map.fire('basemap-changed');
                }
                this._map.once('style.load', fireBasemapChanged);
                setTimeout(fireBasemapChanged, 300);

                this._updateCheckmarks();
                this._hide();
            };

            grid.appendChild(item);
        });

        this._btn.onclick = (e) => {
            e.stopPropagation();
            if (this._dropdown.style.display === 'none') this._show();
            else this._hide();
        };

        document.addEventListener('click', () => this._hide());

        this._container.appendChild(this._btn);
        this._container.appendChild(this._dropdown);
        this._wrapper.appendChild(this._container);

        // Langsung append ke map container (bukan via maplibregl ctrl system)
        map.getContainer().appendChild(this._wrapper);

        // Return div kosong agar maplibregl tidak menambahkan apapun ke ctrl group
        const empty = document.createElement('div');
        empty.style.display = 'none';
        return empty;
    }

    _show() {
        this._dropdown.style.display = 'block';
        const chevron = this._btn.querySelector('.chevron');
        if (chevron) chevron.style.transform = 'rotate(180deg)';
        requestAnimationFrame(() => {
            this._dropdown.style.opacity = '1';
            this._dropdown.style.transform = 'translateY(0) scale(1)';
        });
    }

    _hide() {
        const chevron = this._btn.querySelector('.chevron');
        if (chevron) chevron.style.transform = 'rotate(0deg)';
        this._dropdown.style.opacity = '0';
        this._dropdown.style.transform = 'translateY(8px) scale(0.98)';
        setTimeout(() => { this._dropdown.style.display = 'none'; }, 250);
    }

    _updateCheckmarks() {
        const grid = this._dropdown.querySelector('div:nth-child(2)');
        if (!grid) return;
        const items = grid.querySelectorAll('button');
        items.forEach((item, idx) => {
            const check = item.querySelector('.basemap-check');
            if (idx === this._currentIdx) {
                item.style.borderColor = '#10b981';
                item.style.transform = 'scale(1)';
                if (check) {
                    check.style.display = 'flex';
                    check.style.transform = 'scale(0.4)';
                    check.style.opacity = '0';
                    requestAnimationFrame(() => { check.style.transform = 'scale(1)'; check.style.opacity = '1'; });
                }
            } else {
                item.style.borderColor = 'transparent';
                if (check) check.style.display = 'none';
            }
        });
    }

    onRemove() {
        if (this._wrapper && this._wrapper.parentNode) {
            this._wrapper.parentNode.removeChild(this._wrapper);
        }
        this._map = undefined;
    }
};

// ═══════════════ Modern Scale Display ═══════════════
window.DlhScaleControl = class {
    constructor(options = {}) {
        this._map = null;
        this._container = null;
        this._maxWidth = options.maxWidth || 120;
    }

    onAdd(map) {
        this._map = map;
        this._container = document.createElement('div');
        this._container.className = 'maplibregl-ctrl';
        this._container.style.cssText = 'margin:0 0 28px 8px;pointer-events:none;';

        this._inner = document.createElement('div');
        this._inner.style.cssText = 'display:inline-flex;align-items:center;gap:0;background:rgba(255,255,255,0.92);backdrop-filter:blur(12px);border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.08),0 0 0 1px rgba(0,0,0,0.04);overflow:hidden;pointer-events:auto;transition:all 0.2s;';

        this._inner.onmouseenter = () => { this._inner.style.background = 'rgba(255,255,255,0.98)'; this._inner.style.boxShadow = '0 3px 12px rgba(0,0,0,0.12),0 0 0 1px rgba(0,0,0,0.06)'; };
        this._inner.onmouseleave = () => { this._inner.style.background = 'rgba(255,255,255,0.92)'; this._inner.style.boxShadow = '0 2px 8px rgba(0,0,0,0.08),0 0 0 1px rgba(0,0,0,0.04)'; };

        // Scale line
        this._line = document.createElement('div');
        this._line.style.cssText = 'width:60px;height:0;border-top:2px solid #374151;border-left:2px solid #374151;border-right:2px solid #374151;padding-top:4px;margin:6px 0 6px 10px;';
        this._inner.appendChild(this._line);

        // Text
        this._text = document.createElement('div');
        this._text.style.cssText = 'padding:5px 10px 5px 6px;font-size:11px;font-weight:600;color:#374151;font-family:-apple-system,BlinkMacSystemFont,system-ui,sans-serif;white-space:nowrap;letter-spacing:-0.01em;';
        this._inner.appendChild(this._text);

        this._container.appendChild(this._inner);

        this._map.on('move', () => this._update());
        this._map.on('zoom', () => this._update());
        this._update();

        return this._container;
    }

    _update() {
        if (!this._map || !this._container) return;

        const center = this._map.getCenter();
        const zoom = this._map.getZoom();
        const dist = this._getDistance(
            center.lat, center.lng,
            center.lat, center.lng + this._getLngDelta(center.lat, zoom)
        );

        let scaleText = '';
        if (dist >= 1000) {
            const km = dist / 1000;
            scaleText = km >= 10 ? Math.round(km) + ' km' : km.toFixed(1) + ' km';
        } else {
            scaleText = Math.round(dist) + ' m';
        }

        this._text.textContent = scaleText;

        const lineWidth = Math.min(this._maxWidth, Math.max(40, this._maxWidth * (dist / this._getDistance(center.lat, center.lng, center.lat, center.lng + this._getLngDelta(center.lat, zoom)))));
        this._line.style.width = lineWidth + 'px';
    }

    _getDistance(lat1, lng1, lat2, lng2) {
        const R = 6371000;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLng = (lng2 - lng1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) ** 2 + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLng / 2) ** 2;
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    _getLngDelta(lat, zoom) {
        const metersPerPixel = 40075016.686 * Math.cos(lat * Math.PI / 180) / (2 ** zoom * 256);
        return (this._maxWidth * metersPerPixel) / (111320 * Math.cos(lat * Math.PI / 180));
    }

    onRemove() {
        this._container.parentNode.removeChild(this._container);
        this._map = undefined;
    }
};

// ════════════════════════════════════════════════════════════════════════════
// Premium Fullscreen Control (custom, matches design system)
// ════════════════════════════════════════════════════════════════════════════
window.DlhFullscreenControl = class {
    constructor() {
        this._map = null;
        this._container = null;
        this._btn = null;
        this._fsHandler = null;
    }

    onAdd(map) {
        this._map = map;

        // Style premium & kontras tinggi agar ikon tetap terlihat di basemap gelap/satelit.
        if (!document.getElementById('dlh-fullscreen-style')) {
            var fsStyle = document.createElement('style');
            fsStyle.id = 'dlh-fullscreen-style';
            fsStyle.textContent = ''
                + '.maplibregl-ctrl.dlh-fullscreen-ctrl{background:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.12);border:1px solid rgba(15,23,42,.08)}'
                + '.maplibregl-ctrl.dlh-fullscreen-ctrl button{width:36px;height:36px;line-height:0;background:#ffffff;color:#334155;display:flex;align-items:center;justify-content:center;cursor:pointer;border:none;padding:0;transition:background .15s,color .15s}'
                + '.maplibregl-ctrl.dlh-fullscreen-ctrl button:hover{background:#f1f5f9;color:#0f172a}'
                + '.maplibregl-ctrl.dlh-fullscreen-ctrl button:focus-visible{outline:2px solid #10b981;outline-offset:-2px}'
                + '.maplibregl-ctrl.dlh-fullscreen-ctrl button svg{display:block;margin:auto;flex:0 0 auto}'
                + '.maplibregl-ctrl.dlh-fullscreen-ctrl.is-active button{background:#ecfdf5;color:#059669}'
                + '.maplibregl-ctrl-top-right{display:flex;flex-direction:column;align-items:flex-end;gap:8px;margin:10px}'
                + '.maplibregl-ctrl-top-right .maplibregl-ctrl{margin:0}'
                + '.maplibregl-ctrl-bottom-right{margin:10px}'
                + '.maplibregl-ctrl-bottom-right .maplibregl-ctrl{margin:0}'
                + '.maplibregl-ctrl-bottom-left{margin:10px}'
                + '.maplibregl-ctrl-bottom-left .maplibregl-ctrl{margin:0}'
                + '.maplibregl-ctrl-top-left{display:flex;flex-direction:column;align-items:flex-start;gap:8px;margin:10px}'
                + '.maplibregl-ctrl-top-left .maplibregl-ctrl-group{display:flex;flex-direction:column;gap:4px}'
                + '.maplibregl-ctrl-top-left .maplibregl-ctrl{margin:0 !important}';
            document.head.appendChild(fsStyle);
        }

        this._container = document.createElement('div');
        this._container.className = 'maplibregl-ctrl maplibregl-ctrl-group dlh-fullscreen-ctrl';

        this._btn = document.createElement('button');
        this._btn.type = 'button';
        this._btn.title = 'Layar Penuh';
        this._btn.setAttribute('aria-label', 'Layar Penuh');
        this._btn.innerHTML = DlhFullscreenControl._expandIcon();
        this._btn.onclick = () => this._toggle();

        this._container.appendChild(this._btn);

        this._fsHandler = () => {
            this._render();
            // Beri waktu transisi layout selesai lalu resize agar peta render benar.
            setTimeout(() => { try { this._map.resize(); } catch (e) {} }, 120);
        };
        document.addEventListener('fullscreenchange', this._fsHandler);
        document.addEventListener('webkitfullscreenchange', this._fsHandler);

        // Render awal (mis. peta dimuat kembali saat sudah fullscreen).
        this._render();

        return this._container;
    }

    _toggle() {
        const container = this._map.getContainer();
        const isFs = !!(document.fullscreenElement || document.webkitFullscreenElement);
        if (!isFs) {
            const req = container.requestFullscreen || container.webkitRequestFullscreen;
            if (req) {
                try { req.call(container); } catch (e) { /* diabaikan */ }
            }
        } else {
            const exit = document.exitFullscreen || document.webkitExitFullscreen;
            if (exit) {
                try { exit.call(document); } catch (e) { /* diabaikan */ }
            }
        }
        // Jamin resize juga di sisi kita (Escape menangani exit secara native).
        setTimeout(() => { try { this._map.resize(); } catch (e) {} }, 120);
    }

    _render() {
        const active = !!(document.fullscreenElement || document.webkitFullscreenElement);
        if (!this._btn) return;
        this._btn.innerHTML = active ? DlhFullscreenControl._compressIcon() : DlhFullscreenControl._expandIcon();
        this._btn.title = active ? 'Keluar Layar Penuh' : 'Layar Penuh';
        try {
            this._container.classList.toggle('is-active', active);
        } catch (e) { /* noop */ }
        try {
            this._map.getContainer().classList.toggle('is-fullscreen', active);
        } catch (e) { /* noop */ }
    }

    onRemove() {
        if (this._fsHandler) {
            document.removeEventListener('fullscreenchange', this._fsHandler);
            document.removeEventListener('webkitfullscreenchange', this._fsHandler);
            this._fsHandler = null;
        }
        if (this._container && this._container.parentNode) {
            this._container.parentNode.removeChild(this._container);
        }
        this._map = undefined;
    }

    static _expandIcon() {
        return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">'
            + '<path d="M3 7V4a1 1 0 0 1 1-1H7"/>'
            + '<path d="M21 7V4a1 1 0 0 0-1-1H17"/>'
            + '<path d="M3 17V20a1 1 0 0 0 1 1H7"/>'
            + '<path d="M21 17V20a1 1 0 0 1-1 1H17"/>'
            + '</svg>';
    }

    static _compressIcon() {
        return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">'
            + '<path d="M7 3V6a1 1 0 0 1-1 1H3"/>'
            + '<path d="M17 3V6a1 1 0 0 0-1 1H21"/>'
            + '<path d="M7 21V18a1 1 0 0 1-1-1H3"/>'
            + '<path d="M17 21V18a1 1 0 0 0-1-1H21"/>'
            + '</svg>';
    }
};


// ════════════════════════════════════════════════════════════════════════════
// Premium Zoom Control (custom, replaces maplibregl.NavigationControl zoom)
// ════════════════════════════════════════════════════════════════════════════
window.DlhZoomControl = class {
    constructor(options) {
        options = options || {};
        this._map = null;
        this._container = null;
        this._in = null;
        this._out = null;
    }
    onAdd(map) {
        this._map = map;
        var self = this;
        this._container = document.createElement('div');
        this._container.className = 'maplibregl-ctrl maplibregl-ctrl-group';
        this._container.style.cssText = 'border-radius:10px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.12),0 0 0 1px rgba(15,23,42,0.06);background:#fff;';

        function btn(svg, title, fn) {
            var b = document.createElement('button');
            b.type = 'button';
            b.title = title;
            b.innerHTML = svg;
            b.style.cssText = 'width:36px;height:36px;display:flex;align-items:center;justify-content:center;cursor:pointer;border:none;background:transparent;color:#334155;transition:background .15s,color .15s;';
            b.onmouseenter = function () { b.style.background = '#f1f5f9'; b.style.color = '#0f172a'; };
            b.onmouseleave = function () { b.style.background = 'transparent'; b.style.color = '#334155'; };
            b.onclick = fn;
            return b;
        }
        this._in = btn('<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>', 'Perbesar', function () { map.zoomIn(); });
        this._out = btn('<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14"/></svg>', 'Perkecil', function () { map.zoomOut(); });

        var sep = document.createElement('div');
        sep.style.cssText = 'height:1px;background:rgba(15,23,42,0.08);margin:0 7px;';

        this._container.appendChild(this._in);
        this._container.appendChild(sep);
        this._container.appendChild(this._out);

        this._sync();
        map.on('zoom', function () { self._sync(); });
        return this._container;
    }
    _sync() {
        var z = this._map.getZoom();
        var min = this._map.getMinZoom(), max = this._map.getMaxZoom();
        this._out.disabled = z <= min + 0.01;
        this._in.disabled = z >= max - 0.01;
        [this._in, this._out].forEach(function (b) {
            b.style.opacity = b.disabled ? '0.35' : '1';
            b.style.cursor = b.disabled ? 'default' : 'pointer';
        });
    }
    onRemove() {
        if (this._container && this._container.parentNode) this._container.parentNode.removeChild(this._container);
        this._map = undefined;
    }
};

// ════════════════════════════════════════════════════════════════════════════
// Premium Weather Control (Open-Meteo) — floating current-weather card
// ════════════════════════════════════════════════════════════════════════════
window.DlhWeatherControl = class {
    constructor(options) {
        options = options || {};
        this._map = null;
        this._container = null;
        this._card = null;
        this._open = false;
        this._abort = null;
        this._loc = null;
        this._pos = options.position || 'top-right';
    }
    onAdd(map) {
        this._map = map;
        var self = this;
        this._container = document.createElement('div');
        this._container.className = 'maplibregl-ctrl';
        this._container.style.cssText = 'position:relative;border-radius:12px;overflow:visible;box-shadow:0 2px 12px rgba(0,0,0,0.12),0 0 0 1px rgba(15,23,42,0.06);background:#fff;';

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.title = 'Cuaca (Open-Meteo)';
        btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.5 19a4.5 4.5 0 1 0 0-9 5.5 5.5 0 0 0-10.7 1.6A3.5 3.5 0 0 0 6.5 19Z"/><path d="M9 13h1.5M12 11v2M14 13h1"/></svg>';
        btn.style.cssText = 'width:36px;height:36px;display:flex;align-items:center;justify-content:center;cursor:pointer;border:none;background:transparent;color:#334155;transition:background .15s,color .15s;';
        btn.onmouseenter = function () { btn.style.background = '#f1f5f9'; btn.style.color = '#0f172a'; };
        btn.onmouseleave = function () { btn.style.background = 'transparent'; btn.style.color = '#334155'; };
        btn.onclick = function () { self._toggle(); };
        this._container.appendChild(btn);

        this._buildCard();

        var t = null;
        map.on('moveend', function () {
            if (!self._open) return;
            clearTimeout(t);
            t = setTimeout(function () { self._load(); }, 800);
        });

        return this._container;
    }
    _buildCard() {
        var self = this;
        this._card = document.createElement('div');
        var right = this._pos.indexOf('right') > -1;
        var bottom = this._pos.indexOf('bottom') > -1;
        var x = right ? 'right:0;' : 'left:0;';
        var y = bottom ? 'bottom:calc(100% + 10px);' : 'top:calc(100% + 10px);';
        this._card.style.cssText = 'position:absolute;' + x + y + 'z-index:5;width:250px;background:rgba(255,255,255,0.96);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);border-radius:14px;box-shadow:0 12px 32px rgba(15,23,42,0.18),0 0 0 1px rgba(15,23,42,0.05);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;color:#0f172a;overflow:hidden;display:none;opacity:0;transform:translateY(' + (bottom ? '6px' : '-6px') + ');transition:opacity .2s,transform .2s;';
        this._card.innerHTML = '<div id="dlh-wx-head" style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px 6px;"></div><div id="dlh-wx-body" style="padding:0 14px 14px;"></div>';
        this._container.appendChild(this._card);
    }
    _toggle() {
        this._open = !this._open;
        var c = this._card;
        if (this._open) {
            c.style.display = 'block';
            requestAnimationFrame(function () { c.style.opacity = '1'; c.style.transform = 'translateY(0)'; });
            this._load();
        } else {
            c.style.opacity = '0';
            c.style.transform = 'translateY(-6px)';
            setTimeout(function () { c.style.display = 'none'; }, 200);
        }
    }
    _render(s) {
        var self = this;
        var head = this._card.querySelector('#dlh-wx-head');
        var body = this._card.querySelector('#dlh-wx-body');
        var m = DlhWeatherControl._meta(s.code);
        head.innerHTML = '<div style="display:flex;flex-direction:column;"><span id="dlh-wx-place" style="font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#64748b;">Cuaca &middot; ' + self._esc(self._placeName()) + '</span>'
        + '<span style="font-size:10px;color:#94a3b8;margin-top:1px;">' + s.lat.toFixed(4) + ', ' + s.lng.toFixed(4) + '</span></div>'
            + '<div style="display:flex;gap:6px;align-items:center;">'
            + '<button type="button" id="dlh-wx-refresh" title="Muat ulang" style="width:28px;height:28px;border:none;background:rgba(15,23,42,0.05);border-radius:8px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#475569;transition:background .15s;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-2.64-6.36"/><path d="M21 3v6h-6"/></svg></button>'
            + '<button type="button" id="dlh-wx-close" title="Tutup" style="width:28px;height:28px;border:none;background:rgba(15,23,42,0.05);border-radius:8px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#94a3b8;transition:background .15s,color .15s;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg></button>'
            + '</div>';
        body.innerHTML = '<div style="display:flex;align-items:center;gap:12px;">'
            + '<div style="width:48px;height:48px;flex:none;display:flex;align-items:center;justify-content:center;color:' + m.color + ';">' + m.svg + '</div>'
            + '<div><div style="font-size:28px;font-weight:800;line-height:1;color:#0f172a;">' + Math.round(s.temp) + '<span style="font-size:14px;font-weight:600;color:#94a3b8;">°C</span></div>'
            + '<div style="font-size:12px;font-weight:700;color:' + m.color + ';margin-top:2px;">' + m.label + '</div></div></div>'
            + '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:12px;">'
            + DlhWeatherControl._stat('Terasa', Math.round(s.feels) + '°')
            + DlhWeatherControl._stat('Lembap', Math.round(s.hum) + '%')
            + DlhWeatherControl._stat('Angin', Math.round(s.wind) + ' km/j')
            + DlhWeatherControl._stat('Awan', Math.round(s.cloud) + '%')
            + '</div>'
            + '<div style="margin-top:10px;padding-top:9px;border-top:1px solid rgba(15,23,42,.06);">'
            + '<div style="font-size:9px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#94a3b8;margin-bottom:5px;">Lokasi</div>'
            + '<div id="dlh-wx-loc" style="display:flex;flex-direction:column;gap:3px;font-size:11px;">' + self._locHtml() + '</div>'
            + '</div>';
        head.querySelector('#dlh-wx-refresh').onclick = function () { self._load(); };
        head.querySelector('#dlh-wx-close').onclick = function () { self._toggle(); };
    }
    _msg(text) {
        var head = this._card.querySelector('#dlh-wx-head');
        var body = this._card.querySelector('#dlh-wx-body');
        if (head) head.innerHTML = '<span style="font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#64748b;padding:2px 0;">Cuaca</span>';
        if (body) body.innerHTML = '<div style="font-size:12px;color:#64748b;text-align:center;padding:12px 0;">' + text + '</div>';
    }
    _error(text) {
        var self = this;
        var head = this._card.querySelector('#dlh-wx-head');
        var body = this._card.querySelector('#dlh-wx-body');
        if (head) head.innerHTML = '<span style="font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#64748b;padding:2px 0;">Cuaca</span>';
        if (body) {
            body.innerHTML = '<div style="font-size:12px;color:#64748b;text-align:center;padding:10px 0 12px;">' + text + '</div>'
                + '<button type="button" id="dlh-wx-retry" style="display:block;margin:0 auto;padding:6px 14px;border:none;background:#10b981;color:#fff;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer;transition:background .15s;">Coba lagi</button>';
            var retry = body.querySelector('#dlh-wx-retry');
            if (retry) retry.onclick = function () { self._load(); };
        }
    }

    _placeName() {
        var l = this._loc || {};
        return l.kota || l.kabupaten || l.locality || 'Pusat Peta';
    }
    _locHtml() {
        var l = this._loc || {};
        var self = this;
        function row(label, val) {
            return '<div style="display:flex;justify-content:space-between;gap:10px;">'
                + '<span style="color:#94a3b8;white-space:nowrap;">' + self._esc(label) + '</span>'
                + '<span style="font-weight:700;color:#334155;text-align:right;">' + (val ? self._esc(val) : '&mdash;') + '</span></div>';
        }
        return row('Negara', l.negara) + row('Provinsi', l.provinsi) + row('Kota', l.kota)
            + row('Kecamatan', l.kecamatan) + row('Kabupaten', l.kabupaten);
    }
    _renderLocation() {
        var place = this._card && this._card.querySelector('#dlh-wx-place');
        var loc = this._card && this._card.querySelector('#dlh-wx-loc');
        if (place) place.textContent = 'Cuaca · ' + this._placeName();
        if (loc) loc.innerHTML = this._locHtml();
    }
    _reverse(lat, lng) {
        var self = this;
        var bd = 'https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=' + lat
            + '&longitude=' + lng + '&localityLanguage=id';
        fetch(bd)
            .then(function (r) { if (!r.ok) throw new Error('bdc'); return r.json(); })
            .then(function (d) {
                var loc = self._parseBDC(d || {});
                self._loc = loc;
                self._renderLocation();
                if (!loc.kecamatan && !loc.kabupaten) self._reverseNom(lat, lng);
            })
            .catch(function () { self._reverseNom(lat, lng); });
    }
    _reverseNom(lat, lng) {
        var self = this;
        var nom = 'https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat
            + '&lon=' + lng + '&zoom=18&addressdetails=1&accept-language=id';
        fetch(nom)
            .then(function (r) { if (!r.ok) throw new Error('nom'); return r.json(); })
            .then(function (d) {
                if (!d || !d.address) return;
                var loc = self._parseNom(d);
                self._loc = self._loc || {};
                self._loc.negara = self._loc.negara || loc.negara;
                self._loc.provinsi = self._loc.provinsi || loc.provinsi;
                self._loc.kota = self._loc.kota || loc.kota;
                self._loc.kabupaten = self._loc.kabupaten || loc.kabupaten;
                self._loc.kecamatan = self._loc.kecamatan || loc.kecamatan;
                self._renderLocation();
            })
            .catch(function () { /* abaikan */ });
    }
    _parseBDC(d) {
        d = d || {};
        var negara = d.countryName || '';
        var provinsi = d.principalSubdivision || '';
        var kota = d.city || d.locality || '';
        var kabupaten = d.county || '';
        var kecamatan = '';
        var admin = (d.localityInfo && d.localityInfo.administrative) || [];
        for (var i = 0; i < admin.length; i++) {
            var desc = (admin[i].description || '').toLowerCase();
            if (!kecamatan && (desc === 'county' || desc === 'district' || desc === 'city district' || desc === 'sublocality' || desc === 'borough' || desc === 'quarter' || desc === 'neighbourhood')) {
                kecamatan = admin[i].name || '';
            }
        }
        return { negara: negara, provinsi: provinsi, kota: kota, kabupaten: kabupaten, kecamatan: kecamatan };
    }
    _parseNom(d) {
        d = d || {};
        var ad = d.address || {};
        return {
            negara: ad.country || '',
            provinsi: ad.state || ad.region || '',
            kota: ad.city || ad.town || ad.village || ad.municipality || '',
            kabupaten: ad.county || '',
            kecamatan: ad.suburb || ad.city_district || ad.quarter || ad.borough || ''
        };
    }
    _esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }
    _load() {
        var self = this;
        var c = this._map.getCenter();
        var lat = c.lat, lng = c.lng;
        this._msg('Memuat cuaca…');
        var url = 'https://api.open-meteo.com/v1/forecast?latitude=' + lat + '&longitude=' + lng
            + '&current=temperature_2m,relative_humidity_2m,apparent_temperature,weather_code,wind_speed_10m,cloud_cover&timezone=auto';
        if (this._abort) { try { this._abort.abort(); } catch (e) {} }
        this._abort = new AbortController();
        fetch(url, { signal: this._abort.signal })
            .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(function (d) {
                var cur = d.current || {};
                self._render({
                    code: cur.weather_code != null ? cur.weather_code : 3,
                    temp: cur.temperature_2m != null ? cur.temperature_2m : 0,
                    feels: cur.apparent_temperature != null ? cur.apparent_temperature : 0,
                    hum: cur.relative_humidity_2m != null ? cur.relative_humidity_2m : 0,
                    wind: cur.wind_speed_10m != null ? cur.wind_speed_10m : 0,
                    cloud: cur.cloud_cover != null ? cur.cloud_cover : 0,
                    lat: lat, lng: lng
                });
                self._reverse(lat, lng);
            })
            .catch(function (err) {
                if (err && err.name === 'AbortError') return;
                self._error('Gagal memuat cuaca. Periksa koneksi.');
            });
    }
    onRemove() {
        if (this._card && this._card.parentNode) this._card.parentNode.removeChild(this._card);
        if (this._container && this._container.parentNode) this._container.parentNode.removeChild(this._container);
        this._map = undefined;
    }
    static _stat(label, val) {
        return '<div style="background:rgba(15,23,42,0.035);border-radius:9px;padding:7px 9px;"><div style="font-size:9px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:#94a3b8;">' + label + '</div><div style="font-size:12px;font-weight:700;color:#334155;margin-top:1px;">' + val + '</div></div>';
    }
    static _meta(code) {
        code = Number(code);
        var sun = '<svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>';
        var sunCloud = '<svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v2M5 12H3M6.3 6.3 4.9 4.9M4.9 19.1l1.4-1.4M12 19v2M19.1 12l1.9-1M17.7 17.7l1.4 1.4"/><circle cx="9" cy="9" r="3"/><path d="M17.5 19a4.5 4.5 0 1 0 0-9 6 6 0 0 0-11.7 1.5A3.5 3.5 0 0 0 6.5 19Z"/></svg>';
        var cloud = '<svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.5 19a4.5 4.5 0 1 0 0-9 6 6 0 0 0-11.7 1.5A3.5 3.5 0 0 0 6.5 19Z"/></svg>';
        var fog = '<svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.5 19a4.5 4.5 0 1 0 0-9 6 6 0 0 0-11.7 1.5A3.5 3.5 0 0 0 6.5 19Z"/><path d="M3 7h13M4 11h15M3 15h11"/></svg>';
        var rain = '<svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.5 15a4.5 4.5 0 1 0 0-9 6 6 0 0 0-11.7 1.5A3.5 3.5 0 0 0 6.5 15Z"/><path d="M8 18l-1 2M12 18l-1 2M16 18l-1 2"/></svg>';
        var snow = '<svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.5 15a4.5 4.5 0 1 0 0-9 6 6 0 0 0-11.7 1.5A3.5 3.5 0 0 0 6.5 15Z"/><path d="M12 17v4M10 19l2-2 2 2"/></svg>';
        var storm = '<svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.5 15a4.5 4.5 0 1 0 0-9 6 6 0 0 0-11.7 1.5A3.5 3.5 0 0 0 6.5 15Z"/><path d="M11 18l-2 3h3l-2 3"/></svg>';
        if (code === 0) return { label: 'Cerah', color: '#f59e0b', svg: sun };
        if (code === 1 || code === 2) return { label: 'Cerah Berawan', color: '#f59e0b', svg: sunCloud };
        if (code === 3) return { label: 'Berawan', color: '#64748b', svg: cloud };
        if (code === 45 || code === 48) return { label: 'Kabut', color: '#64748b', svg: fog };
        if ((code >= 51 && code <= 57) || (code >= 61 && code <= 67) || (code >= 80 && code <= 82)) return { label: 'Hujan', color: '#3b82f6', svg: rain };
        if ((code >= 71 && code <= 77) || code === 85 || code === 86) return { label: 'Salju', color: '#0ea5e9', svg: snow };
        if (code >= 95) return { label: 'Badai Petir', color: '#7c3aed', svg: storm };
        return { label: 'Cuaca', color: '#64748b', svg: cloud };
    }
};
