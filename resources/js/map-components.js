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
        this._btn.style.cssText = 'display:flex;align-items:center;gap:6px;height:36px;padding:0 14px;background:white;border:none;border-radius:10px;box-shadow:0 2px 12px rgba(0,0,0,0.1),0 0 0 1px rgba(0,0,0,0.05);cursor:pointer;transition:all 0.2s;font-family:inherit;font-size:12px;font-weight:600;color:#374151;';
        this._btn.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
            </svg>
            <span>Basemap</span>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="transition:transform 0.2s;" class="chevron"><path d="M6 9l6 6 6-6"/></svg>
        `;
        this._btn.onmouseenter = () => { this._btn.style.boxShadow = '0 4px 20px rgba(0,0,0,0.15),0 0 0 1px rgba(0,0,0,0.08)'; };
        this._btn.onmouseleave = () => { this._btn.style.boxShadow = '0 2px 12px rgba(0,0,0,0.1),0 0 0 1px rgba(0,0,0,0.05)'; };

        // Dropdown containing 2-column grid
        this._dropdown = document.createElement('div');
        this._dropdown.style.cssText = 'display:none;position:absolute;bottom:calc(100% + 8px);right:0;width:300px;background:white;border-radius:20px;box-shadow:0 20px 40px rgba(15,23,42,0.18),0 0 0 1px rgba(15,23,42,0.05);z-index:100;overflow:hidden;padding:14px;opacity:0;transform:translateY(8px) scale(0.98);transition:all 0.25s cubic-bezier(0.4,0,0.2,1);';

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
            item.style.cssText = `position:relative;width:100%;height:75px;border-radius:12px;border:2.5px solid ${idx === 0 ? '#10b981' : 'transparent'};overflow:hidden;cursor:pointer;background:url(${bm.preview}) center center / cover no-repeat;transition:all 0.2s ease-in-out;outline:none;padding:0;`;

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
            check.style.cssText = `position:absolute;top:6px;right:6px;width:16px;height:16px;border-radius:50%;background:#10b981;color:white;display:${idx === 0 ? 'flex' : 'none'};align-items:center;justify-content:center;font-weight:bold;`;
            check.innerHTML = '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4"><path d="M5 13l4 4L19 7"/></svg>';
            item.appendChild(check);

            item.onmouseenter = () => {
                if (idx !== this._currentIdx) {
                    item.style.borderColor = 'rgba(16, 185, 129, 0.4)';
                    item.style.transform = 'scale(1.02)';
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
                if (check) check.style.display = 'flex';
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
