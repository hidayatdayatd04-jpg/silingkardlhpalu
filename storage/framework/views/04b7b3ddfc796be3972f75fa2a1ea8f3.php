<?php
    $bidangLabels = [
        'sampah-lb3' => 'Pengelolaan Sampah & LB3',
        'rth' => 'Ruang Terbuka Hijau',
    ];
    $bidangIcons = [
        'sampah-lb3' => 'recycle',
        'rth' => 'tree',
    ];

    $publicPageMap = \App\Models\GisDataLayer::publicPages();

    $layersJson = $layers->map(fn($l) => [
        'id' => $l->id,
        'nama_layer' => $l->nama_layer,
        'deskripsi' => $l->deskripsi,
        'bidang' => $l->bidang,
        'jenis_geometri' => $l->jenis_geometri,
        'metadata' => $l->metadata,
        'is_visible' => $l->is_visible,
        'is_public' => $l->is_public,
        'public_page' => $publicPageMap[$l->bidang] ?? null,
        'geojson' => $l->toGeoJson(),
    ])->toJson();
?>



<?php $__env->startSection('title', 'Peta — Admin DLH'); ?>
<?php $__env->startSection('full_width', 'w-full'); ?>

    <?php $__env->startPush('styles'); ?>
    <link rel="stylesheet" href="https://unpkg.com/maplibre-gl@4.1.1/dist/maplibre-gl.css" />
    <style>
        .map-wrapper { flex: 1; min-height: 0; position: relative; }
        .map-container { position: absolute; inset: 0; overflow: hidden; }
        .map-container .maplibregl-map { width: 100%; height: 100%; }

        /* ═══ Sidebar ═══ */
        .map-sidebar {
            position: absolute; top: 0; right: 0; height: 100%; width: 400px; z-index: 10;
            overflow-y: auto; transition: transform 0.35s cubic-bezier(0.4,0,0.2,1), opacity 0.3s;
            background: linear-gradient(180deg, rgba(255,255,255,0.97) 0%, rgba(249,250,251,0.97) 100%);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border-left: 1px solid rgba(0,0,0,0.06);
            box-shadow: -4px 0 24px rgba(0,0,0,0.06);
        }
        .map-sidebar::-webkit-scrollbar { width: 4px; }
        .map-sidebar::-webkit-scrollbar-track { background: transparent; }
        .map-sidebar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 4px; }
        .map-sidebar.collapsed { transform: translateX(100%); opacity: 0; pointer-events: none; }

        .sidebar-toggle-btn {
            position: absolute;
            top: 10px;
            z-index: 99;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.1);
            border: 1px solid rgba(0,0,0,0.08);
            padding: 10px;
            cursor: pointer;
            transition: all 0.3s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .sidebar-toggle-btn:hover {
            background: #f8fafc;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.12);
        }

        /* ═══ Layer Card ═══ */
        .layer-card {
            transition: all 0.2s cubic-bezier(0.4,0,0.2,1);
            border: 1px solid rgba(0,0,0,0.04);
            border-radius: 14px;
            background: white;
            overflow: hidden;
        }
        .layer-card:hover { border-color: rgba(16,185,129,0.15); box-shadow: 0 2px 12px rgba(16,185,129,0.06); }
        .layer-card.expanded { border-color: rgba(16,185,129,0.2); box-shadow: 0 4px 20px rgba(16,185,129,0.08); }

        /* ═══ Marker List ═══ */
        .marker-list { max-height: 320px; overflow-y: auto; }
        .marker-list::-webkit-scrollbar { width: 3px; }
        .marker-list::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.08); border-radius: 3px; }
        .marker-row {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 12px; border-radius: 10px;
            transition: all 0.15s; cursor: default;
        }
        .marker-row:hover { background: rgba(16,185,129,0.04); }
        .marker-row .marker-actions { opacity: 0; transition: opacity 0.15s; }
        .marker-row:hover .marker-actions { opacity: 1; }

        /* ═══ Action Buttons ═══ */
        .action-icon {
            width: 28px; height: 28px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            border: none; cursor: pointer; transition: all 0.15s;
            background: transparent; color: #94a3b8;
        }
        .action-icon:hover { background: rgba(0,0,0,0.04); color: #475569; }
        .action-icon.danger:hover { background: rgba(239,68,68,0.08); color: #ef4444; }
        .action-icon.edit:hover { background: rgba(16,185,129,0.08); color: #10b981; }

        /* ═══ Search Box ═══ */
        .marker-search {
            width: 100%; padding: 7px 10px 7px 32px;
            border: 1px solid rgba(0,0,0,0.08); border-radius: 10px;
            font-size: 12px; background: #fafbfc; outline: none;
            transition: all 0.15s;
        }
        .marker-search:focus { border-color: #10b981; background: white; box-shadow: 0 0 0 3px rgba(16,185,129,0.1); }

        /* ═══ Draw Toolbar ═══ */
        .draw-btn {
            width: 38px; height: 38px; display: flex; align-items: center; justify-content: center;
            border-radius: 10px; cursor: pointer; transition: all 0.2s;
            background: white; border: 1px solid rgba(0,0,0,0.06);
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }
        .draw-btn:hover { background: #f9fafb; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transform: translateY(-1px); }
        .draw-btn.active { background: #ecfdf5; color: #059669; border-color: #10b981; box-shadow: 0 0 0 2px rgba(16,185,129,0.2); }

        /* ═══ Map Controls ═══ */
        .maplibregl-ctrl-group { border-radius: 10px !important; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.1) !important; }
        .maplibregl-ctrl { margin: 12px !important; }

        /* ═══ Chips ═══ */
        .bidang-chip {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;
            transition: all 0.2s; cursor: pointer; border: 1.5px solid transparent;
        }
        .bidang-chip.active { background: #ecfdf5; color: #059669; border-color: #10b981; }
        .bidang-chip:not(.active) { background: #f3f4f6; color: #6b7280; }
        .bidang-chip:not(.active):hover { background: #e5e7eb; color: #374151; }

        /* ═══ Import Zone ═══ */
        .import-zone { border: 2px dashed #d1d5db; border-radius: 16px; padding: 28px; text-align: center; transition: all 0.2s; cursor: pointer; background: #fafafa; }
        .import-zone:hover, .import-zone.dragover { border-color: #10b981; background: #f0fdf4; }

        /* ═══ Modal ═══ */
        .peta-modal-backdrop { position: fixed; inset: 0; z-index: 60; display: flex; align-items: center; justify-content: center; padding: 16px; }
        .peta-modal-overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.4); backdrop-filter: blur(4px); }
        .peta-modal {
            position: relative; background: white; border-radius: 20px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.15); width: 100%; max-width: 520px;
            max-height: 85vh; overflow-y: auto;
        }
        .peta-modal::-webkit-scrollbar { width: 4px; }
        .peta-modal::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 4px; }

        /* ═══ Popup ═══ */
        .pp-popup .maplibregl-popup-content { padding: 0 !important; border-radius: 12px !important; box-shadow: 0 10px 40px rgba(0,0,0,0.12) !important; border: 1px solid rgba(0,0,0,0.06) !important; overflow: hidden; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .pp-popup .maplibregl-popup-tip { border-top-color: #fff !important; }
        .pp-popup .maplibregl-popup-close-button { width: 22px; height: 22px; border-radius: 50%; background: rgba(0,0,0,0.05); border: none; font-size: 14px; color: #94a3b8; display: flex; align-items: center; justify-content: center; top: 8px; right: 8px; transition: all 0.15s; }
        .pp-popup .maplibregl-popup-close-button:hover { background: rgba(239,68,68,0.1); color: #ef4444; }

        /* DlhMarkers popup styles */
        .dlh-popup{min-width:200px;max-width:300px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;border-radius:10px;overflow:hidden;position:relative}
        .dlh-popup-header{display:flex;align-items:center;gap:10px;padding:12px 14px;background:#fafbfc}
        .dlh-popup-marker-icon{width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;flex-shrink:0}
        .dlh-popup-title-group{min-width:0}
        .dlh-popup-name{font-size:13px;font-weight:700;color:#0f172a;line-height:1.3}
        .dlh-popup-cat{font-size:10px;color:#94a3b8;font-weight:500;margin-top:1px}
        .dlh-popup-body{padding:8px 14px 6px}
        .dlh-popup-row{display:flex;align-items:flex-start;gap:8px;padding:4px 0;border-bottom:1px solid #f8fafc}
        .dlh-popup-row:last-child{border-bottom:none}
        .dlh-popup-row-icon{flex-shrink:0;margin-top:1px}
        .dlh-popup-row-text{font-size:12px;color:#334155;line-height:1.45}
        .dlh-popup-status{display:flex;align-items:center;gap:6px;padding:8px 14px;background:#f8fafc;border-top:1px solid #f1f5f9;font-size:11px;color:#64748b;font-weight:500}
        .dlh-popup-status-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0}
        .dlh-popup-edit-btn:hover{background:rgba(16,185,129,0.2) !important}

        /* ═══ Expand/Collapse animation ═══ */
        .expand-enter { animation: expandIn 0.25s ease-out; }
        .expand-leave { animation: expandOut 0.2s ease-in; }
        @keyframes expandIn { from { opacity: 0; max-height: 0; } to { opacity: 1; max-height: 500px; } }
        @keyframes expandOut { from { opacity: 1; max-height: 500px; } to { opacity: 0; max-height: 0; } }

        /* ═══ Badge ═══ */
        .count-badge {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 20px; height: 20px; padding: 0 6px;
            border-radius: 10px; font-size: 10px; font-weight: 700;
            background: rgba(16,185,129,0.1); color: #059669;
        }

        /* ═══ Field Input ═══ */
        .field-input {
            width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0;
            border-radius: 10px; font-size: 13px; outline: none; transition: all 0.15s;
        }
        .field-input:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.1); }

        /* ═══ Public Visibility Toggle ═══ */
        .public-toggle {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 8px; border-radius: 20px; font-size: 10px; font-weight: 700;
            border: 1.5px solid transparent; cursor: pointer; transition: all 0.2s;
            white-space: nowrap; user-select: none;
        }
        .public-toggle.is-public {
            background: rgba(34,197,94,0.08); color: #16a34a;
            border-color: rgba(34,197,94,0.25);
        }
        .public-toggle.is-public:hover { background: rgba(239,68,68,0.08); color: #dc2626; border-color: rgba(239,68,68,0.25); }
        .public-toggle.is-hidden {
            background: rgba(239,68,68,0.07); color: #dc2626;
            border-color: rgba(239,68,68,0.2);
        }
        .public-toggle.is-hidden:hover { background: rgba(34,197,94,0.08); color: #16a34a; border-color: rgba(34,197,94,0.25); }

        /* ═══ Page Badge ═══ */
        .page-badge {
            display: inline-flex; align-items: center; gap: 3px;
            padding: 2px 7px; border-radius: 20px; font-size: 9.5px; font-weight: 700;
            background: rgba(59,130,246,0.08); color: #2563eb;
            border: 1px solid rgba(59,130,246,0.18); text-decoration: none;
            transition: all 0.15s;
        }
        .page-badge:hover { background: rgba(59,130,246,0.16); }
    </style>
    <?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="map-wrapper" x-data="petaAdmin()" x-init="$nextTick(() => setTimeout(() => init(), 300))">
    <div class="map-container">
        <!-- Map -->
        <div id="peta-map"></div>

        <!-- Draw Toolbar (Titik saja) -->
        <div x-show="showDrawToolbar" x-transition class="absolute top-3 left-1/2 -translate-x-1/2 z-10 bg-white rounded-xl shadow-lg border border-slate-200 p-1.5 flex gap-1">
            <button @click="drawMode === 'point' ? cancelDraw() : startPointDraw()" :class="drawMode === 'point' ? 'active' : ''" class="draw-btn" title="Tambah Titik">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </button>
            <div class="w-px bg-slate-200 mx-0.5"></div>
            <button @click="saveDrawn()" class="draw-btn" :class="tempMarker ? 'text-emerald-600' : 'opacity-40 cursor-not-allowed'" :disabled="!tempMarker" title="Simpan Titik">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </button>
            <button @click="cancelDraw()" class="draw-btn" title="Batal">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <!-- Draw mode hint -->
        <div x-show="drawMode === 'point' && !tempMarker" x-transition
            class="absolute top-16 left-1/2 -translate-x-1/2 z-10 bg-slate-800/80 text-white text-xs px-3 py-1.5 rounded-lg backdrop-blur-sm pointer-events-none"
            style="display:none;">
            Klik peta untuk meletakkan titik baru
        </div>
        <div x-show="drawMode === 'point' && tempMarker" x-transition
            class="absolute top-16 left-1/2 -translate-x-1/2 z-10 bg-emerald-700/80 text-white text-xs px-3 py-1.5 rounded-lg backdrop-blur-sm pointer-events-none"
            style="display:none;">
            Titik siap — klik ✓ untuk simpan
        </div>

        <!-- ═══ Floating Sidebar Toggle Button ═══ -->
        <button @click="sidebarOpen = !sidebarOpen"
            class="sidebar-toggle-btn"
            :style="{ right: sidebarOpen ? '408px' : '12px' }"
            :title="sidebarOpen ? 'Sembunyikan panel' : 'Tampilkan panel'"
        >
            <!-- Hamburger icon (shown when panel is closed) -->
            <svg :class="sidebarOpen ? 'hidden' : 'block'" class="w-5 h-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            <!-- X icon (shown when panel is open) -->
            <svg :class="sidebarOpen ? 'block' : 'hidden'" class="w-5 h-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <!-- ═══════════════ REDESIGNED SIDEBAR ═══════════════ -->
        <div class="map-sidebar" :class="sidebarOpen ? '' : 'collapsed'" x-show="sidebarOpen">
            <!-- Header -->
            <div class="px-5 pt-5 pb-4 border-b border-slate-100">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center shadow-lg shadow-emerald-500/20">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0021 18.382V7.618a1 1 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900 tracking-tight">Peta</h2>
                            <p class="text-[11px] text-slate-400 font-medium"><span x-text="layers.length"></span> layer</p>
                        </div>
                    </div>
                    <!-- Toggle All Layers -->
                    <button @click="toggleAllLayers()"
                        class="text-[11px] font-semibold px-3 py-1.5 rounded-lg transition-all"
                        :class="allLayersVisible() ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'"
                        :title="allLayersVisible() ? 'Sembunyikan semua layer' : 'Tampilkan semua layer'">
                        <span x-text="allLayersVisible() ? 'Semua On' : 'Semua Off'"></span>
                    </button>
                </div>

                <!-- Bidang Filter Chips -->
                <div class="flex flex-wrap gap-2">
                    <a href="<?php echo e(route('admin.peta.index')); ?>"
                       class="bidang-chip <?php echo e(!$activeBidang ? 'active' : ''); ?>">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                        Semua
                    </a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $accessibleBidang; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <a href="<?php echo e(route('admin.peta.index', ['bidang' => $b])); ?>"
                       class="bidang-chip <?php echo e($activeBidang === $b ? 'active' : ''); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($b === 'sampah-lb3'): ?><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <?php else: ?><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php echo e($bidangLabels[$b] ?? $b); ?>

                    </a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </div>

            <!-- Action Bar -->
            <div class="px-5 py-3 border-b border-slate-100">
                <div class="flex gap-2">
                    <button @click="showImport = true" class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-xs font-semibold rounded-xl hover:from-emerald-600 hover:to-teal-600 transition-all shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/30 hover:-translate-y-0.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
                        Import
                    </button>
                    <button @click="showDrawToolbar = !showDrawToolbar; if(!showDrawToolbar) cancelDraw()" :class="showDrawToolbar ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'" class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-xl hover:bg-slate-200 transition-all" title="Tambah Titik">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Titik
                    </button>
                    <button @click="isSelectionMode = !isSelectionMode; selectedLayers = []" :class="isSelectionMode ? 'bg-red-100 text-red-700 font-bold' : 'bg-slate-100 text-slate-600'" class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-xl hover:bg-slate-200 transition-all" title="Pilih Layer untuk Hapus Massal">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Hapus Massal
                    </button>
                </div>
            </div>

            <!-- Bulk Action Panel (Visible in Selection Mode) -->
            <div x-show="isSelectionMode" x-transition class="px-5 py-2.5 bg-slate-50 border-b border-slate-200/60 flex items-center justify-between" style="display: none;">
                <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer">
                    <input type="checkbox" @change="toggleSelectAll($event.target.checked)" :checked="selectedLayers.length === layers.length && layers.length > 0"
                        class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 focus:ring-offset-0 cursor-pointer" />
                    Pilih Semua
                </label>
                <div class="flex items-center gap-2">
                    <span class="text-[11px] text-slate-500 font-semibold"><span x-text="selectedLayers.length"></span> terpilih</span>
                    <button @click="bulkDeleteLayers()" :disabled="selectedLayers.length === 0"
                        class="px-2.5 py-1.5 bg-red-600 hover:bg-red-700 disabled:opacity-40 disabled:hover:bg-red-600 text-white text-[11px] font-bold rounded-lg transition-all flex items-center gap-1 shadow-sm">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Hapus
                    </button>
                </div>
            </div>

            <!-- ═══ Layer List (REDESIGNED) ═══ -->
            <div class="flex-1 overflow-y-auto p-4 space-y-2.5" style="max-height: calc(100vh - 260px);">
                <template x-for="(layer, idx) in layers" :key="layer.id">
                    <div class="layer-card" :class="{ 'expanded': expandedLayer === layer.id, 'opacity-50': !layer.is_visible && !isSelectionMode }">
                        <!-- Layer Header -->
                        <div class="p-3.5">
                            <div class="flex items-start gap-3">
                                <template x-if="!isSelectionMode">
                                    <input type="checkbox" x-model="layer.is_visible" @change="toggleLayerVisibility(layer)"
                                        class="mt-0.5 rounded-lg border-slate-300 text-emerald-600 focus:ring-emerald-500 focus:ring-offset-0 cursor-pointer" />
                                </template>
                                <template x-if="isSelectionMode">
                                    <input type="checkbox" :value="layer.id" x-model="selectedLayers"
                                        class="mt-0.5 rounded border-slate-300 text-red-600 focus:ring-red-500 focus:ring-offset-0 cursor-pointer" />
                                </template>
                                <div class="flex-1 min-w-0 cursor-pointer" @click="if(!isSelectionMode) toggleExpand(layer.id)">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-block w-2.5 h-2.5 rounded-full ring-2 ring-white shadow-sm" :style="'background:' + (layer.metadata?.color || '#6b7280')"></span>
                                        <span class="text-sm font-semibold text-slate-900 truncate" x-text="layer.nama_layer"></span>
                                        <span class="count-badge" x-text="getFeatureCount(layer)"></span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-1.5">
                                        <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-full"
                                            :class="{
                                                'bg-amber-50 text-amber-700 ring-1 ring-amber-200': layer.bidang === 'sampah-lb3',
                                                'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200': layer.bidang === 'rth',
                                            }"
                                            x-text="layer.bidang === 'sampah-lb3' ? 'Sampah' : 'RTH'"></span>
                                        <span class="text-[11px] text-slate-400 font-medium" x-text="layer.jenis_geometri"></span>
                                    </div>

                                    <!-- Public visibility + page badge row -->
                                    <div class="flex items-center flex-wrap gap-1.5 mt-2" @click.stop>
                                        <!-- Toggle Public -->
                                        <button @click.stop="toggleLayerPublic(layer)"
                                            :class="layer.is_public ? 'public-toggle is-public' : 'public-toggle is-hidden'"
                                            :title="layer.is_public ? 'Klik untuk sembunyikan dari publik' : 'Klik untuk tampilkan ke publik'">
                                            <!-- Eye icon when public -->
                                            <template x-if="layer.is_public">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </template>
                                            <!-- Eye-off icon when hidden -->
                                            <template x-if="!layer.is_public">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                            </template>
                                            <span x-text="layer.is_public ? 'Publik' : 'Disembunyikan'"></span>
                                        </button>

                                        <!-- Public page badge (only if layer has a public page) -->
                                        <template x-if="layer.public_page">
                                            <a :href="layer.public_page.url" target="_blank" class="page-badge" :title="'Tampil di: ' + layer.public_page.label">
                                                <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                <span x-text="layer.public_page.label"></span>
                                            </a>
                                        </template>

                                        <!-- No public page -->
                                        <template x-if="!layer.public_page">
                                            <span class="text-[9.5px] text-slate-400 font-medium italic">— tidak ada halaman publik</span>
                                        </template>
                                    </div>

                                    <template x-if="layer.deskripsi">
                                        <p class="text-[11px] text-slate-400 mt-1.5 leading-relaxed" x-text="layer.deskripsi"></p>
                                    </template>
                                </div>
                                <div class="flex items-center gap-0.5" x-show="!isSelectionMode">
                                    <!-- Expand/Collapse -->
                                    <button @click="toggleExpand(layer.id)" class="action-icon" title="Lihat marker">
                                        <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="expandedLayer === layer.id ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <!-- Edit Layer -->
                                    <button @click="editLayer(layer)" class="action-icon edit" title="Edit layer">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <!-- Delete Layer -->
                                    <button @click="deleteLayer(layer)" class="action-icon danger" title="Hapus layer">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- ═══ Expanded: Marker List ═══ -->
                        <template x-if="expandedLayer === layer.id">
                            <div class="border-t border-slate-100 bg-gradient-to-b from-slate-50/50 to-white">
                                <!-- Export Actions -->
                                <div class="px-3.5 pt-3 pb-1.5 flex gap-2 border-b border-slate-100/50">
                                    <button @click="exportLayer(layer, 'geojson')" class="flex-1 flex items-center justify-center gap-1.5 py-1.5 px-2.5 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 text-slate-600 text-[11px] font-bold rounded-lg transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        Export GeoJSON
                                    </button>
                                    <button @click="exportLayer(layer, 'csv')" class="flex-1 flex items-center justify-center gap-1.5 py-1.5 px-2.5 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 text-slate-600 text-[11px] font-bold rounded-lg transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        Export CSV
                                    </button>
                                </div>

                                <!-- Search -->
                                <div class="px-3.5 pt-3 pb-2 relative">
                                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-6 top-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                                    <input type="text" class="marker-search" placeholder="Cari marker..." x-model="markerSearch" />
                                </div>

                                <!-- Marker Items -->
                                <div class="marker-list px-2 pb-2">
                                    <template x-for="(item, mIdx) in getFilteredMarkers(layer)" :key="mIdx">
                                        <div class="marker-row">
                                            <!-- Marker Icon -->
                                            <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0" :style="'background:' + (layer.metadata?.color || '#6b7280') + '20'">
                                                <div class="w-2.5 h-2.5 rounded-full" :style="'background:' + (layer.metadata?.color || '#6b7280')"></div>
                                            </div>
                                            <!-- Marker Info -->
                                            <div class="flex-1 min-w-0">
                                                <p class="text-[12.5px] font-semibold text-slate-800 truncate" x-text="getMarkerName(item, layer)"></p>
                                                <p class="text-[10.5px] text-slate-400 truncate" x-text="getMarkerSub(item)"></p>
                                            </div>
                                            <!-- Actions -->
                                            <div class="marker-actions flex items-center gap-0.5">
                                                <!-- Fly to -->
                                                <button @click="flyToMarker(item)" class="action-icon" title="Lihat di peta">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                </button>
                                                <!-- Detail -->
                                                <button @click="showMarkerDetail(layer, item)" class="action-icon" title="Detail">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                </button>
                                                <!-- Edit -->
                                                <button @click="showMarkerEdit(layer, item)" class="action-icon edit" title="Edit">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                </button>
                                                <!-- Delete -->
                                                <button @click="deleteFeature(layer, item)" class="action-icon danger" title="Hapus">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                    <!-- Empty state -->
                                    <template x-if="getFilteredMarkers(layer).length === 0">
                                        <div class="py-6 text-center">
                                            <p class="text-xs text-slate-400">Tidak ada marker ditemukan</p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($layers->isEmpty()): ?>
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0021 18.382V7.618a1 1 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    </div>
                    <p class="text-sm font-semibold text-slate-500">Belum ada layer</p>
                    <p class="text-xs text-slate-400 mt-1">Klik "Import" untuk menambah data GIS</p>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

        <!-- Floating Legend (Collapsible) -->
        <div class="fixed bottom-6 left-6 z-10 bg-white/90 backdrop-blur-md rounded-2xl shadow-xl border border-slate-200/50 p-4 max-w-[260px] transition-all duration-300"
            x-data="{ legendCollapsed: true }" :class="legendCollapsed ? 'w-10 h-10 overflow-hidden !p-0 rounded-full flex items-center justify-center cursor-pointer hover:bg-slate-50' : 'w-[260px]'"
            @click="if (legendCollapsed) legendCollapsed = false" style="bottom: 24px; left: 24px;">
            
            <!-- Icon if collapsed -->
            <template x-if="legendCollapsed">
                <div class="flex items-center justify-center w-full h-full text-slate-700" title="Tampilkan Legenda">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </template>

            <!-- Content if expanded -->
            <div x-show="!legendCollapsed" style="display:none;" @click.stop>
                <div class="flex items-center justify-between border-b border-slate-100 pb-2 mb-3">
                    <span class="text-xs font-bold text-slate-800 tracking-wider uppercase">Legenda Peta</span>
                    <button @click="legendCollapsed = true" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="space-y-2.5 max-h-[220px] overflow-y-auto pr-1">
                    <template x-for="layer in layers" :key="layer.id">
                        <div class="flex items-center gap-3" x-show="layer.is_visible">
                            <span class="w-3.5 h-3.5 rounded-full border border-white ring-2 ring-slate-100 shadow-sm flex-shrink-0" :style="'background:' + (layer.metadata?.color || '#6b7280')"></span>
                            <span class="text-xs font-medium text-slate-700 truncate flex-1" x-text="layer.nama_layer"></span>
                        </div>
                    </template>
                    <div x-show="layers.filter(l => l.is_visible).length === 0" class="py-2 text-center text-[11px] text-slate-400">
                        Tidak ada layer aktif
                    </div>
                </div>
            </div>
        </div>
        <!-- Toggle Sidebar Button -->
        <button @click="sidebarOpen = !sidebarOpen"
            :style="sidebarOpen ? 'right: 412px;' : 'right: 16px;'"
            class="sidebar-toggle-btn">
            <svg x-show="!sidebarOpen" class="w-5 h-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            <svg x-show="sidebarOpen" class="w-5 h-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <!-- ═══════════════ DETAIL MODAL ═══════════════ -->
    <template x-if="detailModal.show">
        <div class="peta-modal-backdrop" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="peta-modal-overlay" @click="detailModal.show = false"></div>
            <div class="peta-modal" @click.stop>
                <!-- Header -->
                <div class="sticky top-0 bg-white z-10 px-6 pt-5 pb-4 border-b border-slate-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" :style="'background:' + (detailModal.color || '#6b7280') + '15'">
                                <div class="w-4 h-4 rounded-full" :style="'background:' + (detailModal.color || '#6b7280')"></div>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900" x-text="detailModal.name"></h3>
                                <p class="text-[11px] text-slate-400 font-medium" x-text="detailModal.layerName"></p>
                            </div>
                        </div>
                        <button @click="detailModal.show = false" class="action-icon">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
                <!-- Body: loop all properties -->
                <div class="px-6 py-4 space-y-3">
                    <template x-for="(val, key) in detailModal.properties" :key="key">
                        <div class="flex items-start gap-3 py-2 border-b border-slate-50" x-show="!key.startsWith('_')">
                            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide min-w-[100px] pt-0.5" x-text="humanizeKey(key)"></span>
                            <span class="text-[13px] text-slate-700 font-medium flex-1" x-text="val || '—'"></span>
                        </div>
                    </template>
                    <!-- Coordinates -->
                    <div class="flex items-start gap-3 py-2 border-b border-slate-50">
                        <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide min-w-[100px] pt-0.5">Koordinat</span>
                        <span class="text-[13px] text-slate-700 font-medium font-mono" x-text="detailModal.coords"></span>
                    </div>
                </div>
                <!-- Footer -->
                <div class="px-6 py-4 border-t border-slate-100 flex gap-2">
                    <button @click="detailModal.show = false; showMarkerEdit(detailModal.layerRef, detailModal.itemRef)" class="flex-1 px-4 py-2.5 rounded-xl bg-emerald-50 text-emerald-700 text-sm font-semibold hover:bg-emerald-100 transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        Edit Marker
                    </button>
                    <button @click="detailModal.show = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all">Tutup</button>
                </div>
            </div>
        </div>
    </template>

    <!-- ═══════════════ EDIT MODAL ═══════════════ -->
    <template x-if="editModal.show">
        <div class="peta-modal-backdrop" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="peta-modal-overlay" @click="editModal.show = false"></div>
            <div class="peta-modal" @click.stop>
                <!-- Header -->
                <div class="sticky top-0 bg-white z-10 px-6 pt-5 pb-4 border-b border-slate-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Edit Marker</h3>
                            <p class="text-[11px] text-slate-400 font-medium" x-text="editModal.layerName"></p>
                        </div>
                        <button @click="editModal.show = false" class="action-icon">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
                <!-- Body: editable fields -->
                <div class="px-6 py-4 space-y-4">
                    <!-- Existing Properties -->
                    <template x-for="(val, key) in editModal.properties" :key="key">
                        <div x-show="!key.startsWith('_')">
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-1.5" x-text="humanizeKey(key)"></label>
                            <input type="text" class="field-input" :value="val" @input="editModal.properties[key] = $event.target.value" />
                        </div>
                    </template>

                    <!-- Coordinates -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-1.5">Latitude</label>
                            <input type="number" step="any" class="field-input" x-model.number="editModal.lat" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-1.5">Longitude</label>
                            <input type="number" step="any" class="field-input" x-model.number="editModal.lng" />
                        </div>
                    </div>

                    <!-- Marker Type (Custom Dropdown with Icon Preview) -->
                    <div x-data="{ markerDropOpen: false }" class="relative">
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-1.5">Tipe Icon Marker</label>
                        <!-- Toggle Button -->
                        <button type="button" @click="markerDropOpen = !markerDropOpen"
                            class="field-input w-full flex items-center justify-between text-left cursor-pointer">
                            <div class="flex items-center gap-2">
                                <template x-if="!editModal.markerType">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded flex items-center justify-center bg-slate-200 text-slate-500">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"/></svg>
                                        </div>
                                        <span class="text-sm text-slate-500">Auto-detect</span>
                                    </div>
                                </template>
                                <template x-if="editModal.markerType">
                                    <div class="flex items-center gap-2"
                                        x-data="{ get _def() { return (typeof window.DlhMarkers !== 'undefined' && DlhMarkers.ICONS) ? (DlhMarkers.ICONS[editModal.markerType] || DlhMarkers.ICONS['default']) : null; } }">
                                        <div class="w-6 h-6 rounded flex items-center justify-center text-white flex-shrink-0"
                                            :style="'background:' + (_def ? _def.color : '#6b7280')"
                                            x-html="_def ? _def.svg : ''"></div>
                                        <span class="text-sm font-semibold text-slate-800" x-text="humanizeKey(editModal.markerType)"></span>
                                    </div>
                                </template>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 flex-shrink-0 transition-transform" :class="markerDropOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <!-- Dropdown Options -->
                        <div x-show="markerDropOpen" @click.away="markerDropOpen = false"
                            x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="absolute z-50 mt-1 w-full max-h-56 overflow-y-auto rounded-xl bg-white border border-slate-200 shadow-xl py-1" style="display:none;">
                            <!-- Auto-detect -->
                            <button type="button" @click="editModal.markerType = ''; markerDropOpen = false"
                                class="w-full flex items-center gap-2.5 px-3 py-2 hover:bg-slate-50 transition-colors"
                                :class="!editModal.markerType ? 'bg-emerald-50' : ''">
                                <div class="w-6 h-6 rounded bg-slate-200 flex items-center justify-center text-slate-500 flex-shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"/></svg>
                                </div>
                                <span class="text-xs font-semibold" :class="!editModal.markerType ? 'text-emerald-700' : 'text-slate-700'">Auto-detect</span>
                                <template x-if="!editModal.markerType">
                                    <svg class="w-3.5 h-3.5 text-emerald-500 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </template>
                            </button>
                            <div class="h-px bg-slate-100 my-0.5 mx-3"></div>
                            <!-- Icon Options -->
                            <template x-for="t in markerTypes" :key="t">
                                <button type="button" @click="editModal.markerType = t; markerDropOpen = false"
                                    class="w-full flex items-center gap-2.5 px-3 py-2 hover:bg-slate-50 transition-colors"
                                    :class="editModal.markerType === t ? 'bg-emerald-50' : ''"
                                    x-data="{ get _def() { return (typeof window.DlhMarkers !== 'undefined' && DlhMarkers.ICONS) ? (DlhMarkers.ICONS[t] || DlhMarkers.ICONS['default']) : null; } }">
                                    <div class="w-6 h-6 rounded flex items-center justify-center text-white flex-shrink-0"
                                        :style="'background:' + (_def ? _def.color : '#6b7280')"
                                        x-html="_def ? _def.svg : ''"></div>
                                    <span class="text-xs font-semibold" :class="editModal.markerType === t ? 'text-emerald-700' : 'text-slate-700'" x-text="humanizeKey(t)"></span>
                                    <template x-if="editModal.markerType === t">
                                        <svg class="w-3.5 h-3.5 text-emerald-500 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </template>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Add New Field -->
                    <div class="pt-3 border-t border-slate-100">
                        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-2">+ Tambah Field Baru</p>
                        <div class="flex gap-2">
                            <input type="text" class="field-input flex-1" placeholder="Nama field" x-model="newFieldKey" />
                            <input type="text" class="field-input flex-1" placeholder="Nilai" x-model="newFieldValue" />
                            <button @click="addNewField()" class="px-3 py-2 rounded-xl bg-emerald-50 text-emerald-700 text-xs font-bold hover:bg-emerald-100 transition-all flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Footer -->
                <div class="sticky bottom-0 bg-white px-6 py-4 border-t border-slate-100 flex gap-2">
                    <button @click="editModal.show = false" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</button>
                    <button @click="saveMarkerEdit()" :disabled="editModal.saving" class="flex-1 px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-sm font-semibold hover:from-emerald-600 hover:to-teal-600 transition-all shadow-lg shadow-emerald-500/20 disabled:opacity-50 flex items-center justify-center gap-2">
                        <template x-if="editModal.saving">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        </template>
                        <span x-text="editModal.saving ? 'Menyimpan...' : 'Simpan'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>

    <!-- ═══════════════ IMPORT MODAL ═══════════════ -->
    <div x-show="showImport" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showImport = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6" @click.away="showImport = false">
            <h3 class="text-lg font-bold text-slate-900 mb-4">Import Data GIS</h3>

            <form @submit.prevent="submitImport()">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Bidang</label>
                        <select x-model="importForm.bidang"
                            class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $accessibleBidang; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($b); ?>"><?php echo e($bidangLabels[$b] ?? $b); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nama Layer</label>
                        <input type="text" x-model="importForm.nama_layer" required
                            class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="Contoh: Titik TPA Palu" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Deskripsi (opsional)</label>
                        <input type="text" x-model="importForm.deskripsi"
                            class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="Deskripsi singkat layer" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Warna</label>
                        <input type="color" x-model="importForm.color" class="h-10 w-20 rounded-lg border border-slate-300 cursor-pointer" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">File GIS</label>
                        <div class="import-zone" @click="$refs.fileInput.click()"
                            @dragover.prevent="dragover = true" @dragleave="dragover = false"
                            @drop.prevent="dragover = false; handleFileDrop($event)"
                            :class="dragover ? 'dragover' : ''">
                            <template x-if="!importForm.file">
                                <div>
                                    <svg class="w-10 h-10 text-slate-300 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                    <p class="text-sm text-slate-500 mt-2">Klik atau drag file ke sini</p>
                                    <p class="text-xs text-slate-400 mt-1">Format: .zip (SHP), .geojson, .json, .kml, .csv</p>
                                </div>
                            </template>
                            <template x-if="importForm.file">
                                <div class="flex items-center gap-3">
                                    <svg class="w-8 h-8 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-900 truncate" x-text="importForm.file?.name"></p>
                                        <p class="text-xs text-slate-500" x-text="formatSize(importForm.file?.size)"></p>
                                    </div>
                                    <button type="button" @click="importForm.file = null" class="text-slate-400 hover:text-red-500">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                        <input type="file" x-ref="fileInput" @change="importForm.file = $event.target.files[0]"
                            accept=".zip,.shp,.geojson,.json,.kml,.csv" class="hidden" />
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" @click="showImport = false" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                    <button type="submit" :disabled="importing"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700 disabled:opacity-50 flex items-center justify-center gap-2">
                        <template x-if="importing">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        </template>
                        <span x-text="importing ? 'Importing...' : 'Import Data'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast -->
    <div x-show="toast.show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4"
        class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 px-5 py-3 rounded-xl shadow-lg text-sm font-medium flex items-center gap-3 backdrop-blur-md"
        :class="toast.type === 'success' ? 'bg-slate-900/90 text-white border border-slate-700/50' : 'bg-red-600 text-white'"
        style="display:none;">
        <span x-text="toast.message"></span>
        <template x-if="toast.hasAction">
            <button @click.stop="triggerToastAction()" class="px-2.5 py-1 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-xs font-bold text-white transition-colors duration-150">
                Urungkan
            </button>
        </template>
    </div>
</div>

    <?php $__env->startPush('scripts'); ?>
    <script src="https://unpkg.com/maplibre-gl@4.1.1/dist/maplibre-gl.js"></script>
    <script>
    // Initialize Alpine store for peta sidebar
    document.addEventListener('alpine:init', () => {
        Alpine.store('petaSidebar', {
            open: false,
            toggle() { this.open = !this.open; }
        });
    });

    function petaAdmin() {
        return {
            get sidebarOpen() { return Alpine.store('petaSidebar').open; },
            set sidebarOpen(val) { Alpine.store('petaSidebar').open = val; },
            get _sidebarStoreReady() { return true; },
            map: null,
            drawMode: 'simple_select',
            tempMarker: null,
            drawCoords: [],
            layers: <?php echo $layersJson; ?>,
            layerSources: {},
            layerMarkers: {},
            isSelectionMode: false,
            selectedLayers: [],
            showImport: false,
            showDrawToolbar: false,
            importing: false,
            dragover: false,
            // ═══ Draw state (titik saja) ═══
            drawMode: 'simple_select',
            tempMarker: null,
            importForm: { nama_layer: '', deskripsi: '', bidang: '<?php echo e($accessibleBidang[0] ?? "rth"); ?>', color: '#22c55e', file: null },
            currentBasemap: 'https://basemaps.cartocdn.com/gl/voyager-gl-style/style.json',
            toast: { show: false, message: '', type: 'success', hasAction: false },
            toastActionCallback: null,

            // ═══ Panel state ═══
            expandedLayer: null,
            markerSearch: '',
            markerTypes: [],

            // ═══ Detail Modal ═══
            detailModal: { show: false, name: '', layerName: '', properties: {}, coords: '', color: '', layerRef: null, itemRef: null },

            // ═══ Edit Modal ═══
            editModal: { show: false, layerName: '', layerId: null, featureIndex: null, properties: {}, lat: 0, lng: 0, markerType: '', saving: false },
            newFieldKey: '',
            newFieldValue: '',

            // ═══════════════ INIT ═══════════════
            init() {
                var self = this;

                // Get marker types from DlhMarkers
                if (typeof window.DlhMarkers !== 'undefined' && DlhMarkers.getTypes) {
                    this.markerTypes = DlhMarkers.getTypes().filter(t => t !== 'default');
                }

                this.map = new maplibregl.Map({
                    container: 'peta-map',
                    style: this.currentBasemap,
                    center: [119.86, -0.90],
                    zoom: 11,
                    transformRequest: (url, resourceType) => {
                        if (resourceType === 'Glyphs' && url.includes('basemaps.cartocdn.com')) {
                            return {
                                url: url.replace('https://basemaps.cartocdn.com/fonts', 'https://fonts.openmaptiles.org')
                            };
                        }
                        return { url: url };
                    }
                });

                this.map.addControl(new maplibregl.NavigationControl({ showCompass: false, visualizePitch: false, showZoom: true }), 'top-left');
                if (window.DlhBasemapSwitcher) {
                    this.map.addControl(new DlhBasemapSwitcher(), 'bottom-right');
                }
                if (window.dlhAddLocBtn) dlhAddLocBtn(this.map);

                // Fase 2 fix: actively detect DlhMarkers instead of relying on _dlhPetaReady flag
                function waitForReady(cb, tries) {
                    tries = tries || 0;
                    var mapReady = self.map.loaded();
                    var dlhReady = typeof window.DlhMarkers !== 'undefined';
                    if (mapReady && dlhReady) return cb();
                    if (tries > 100) {
                        console.warn('[DLH Peta] DlhMarkers belum tersedia setelah 10 detik, menggunakan fallback circle layer');
                        cb();
                        return;
                    }
                    setTimeout(function() { waitForReady(cb, tries + 1); }, 100);
                }

                this.map.on('load', () => {
                    // Hide attribution
                    document.querySelectorAll('.maplibregl-ctrl-attrib').forEach(el => el.style.display = 'none');

                    waitForReady(() => {
                        // Refresh marker types after DlhMarkers loaded
                        if (typeof window.DlhMarkers !== 'undefined' && DlhMarkers.getTypes) {
                            this.markerTypes = DlhMarkers.getTypes().filter(t => t !== 'default');
                        }

                        // Click handler — hanya mode titik
                        this.map.on('click', (e) => {
                            if (this.drawMode !== 'point') return;
                            if (this.tempMarker) this.tempMarker.remove();
                            var el = document.createElement('div');
                            el.style.cssText = 'width:24px;height:24px;background:#ef4444;border:3px solid #fff;border-radius:50%;box-shadow:0 2px 8px rgba(0,0,0,0.3);cursor:pointer;';
                            this.tempMarker = new maplibregl.Marker({ element: el, anchor: 'center' })
                                .setLngLat(e.lngLat)
                                .addTo(this.map);
                        });

                        // Load existing layers
                        this.layers.forEach(layer => this.addLayerToMap(layer));

                        // Auto-fit bounds after import (layer ID stored in sessionStorage)
                        const fitLayerId = sessionStorage.getItem('peta_import_fit_layer');
                        if (fitLayerId) {
                            sessionStorage.removeItem('peta_import_fit_layer');
                            const targetLayer = this.layers.find(l => l.id == fitLayerId);
                            if (targetLayer) {
                                setTimeout(() => this.fitLayerBounds(targetLayer), 300);
                            }
                        }
                    });
                });

                // Ketika basemap berubah, tambahkan ulang semua layer
                this.map.on('basemap-changed', () => {
                    setTimeout(() => {
                        this.layers.forEach(layer => {
                            const sid = 'layer-' + layer.id;
                            if (this.map.getSource(sid)) {
                                this.map.getStyle().layers.slice().forEach(l => {
                                    if (l.source === sid && this.map.getLayer(l.id)) this.map.removeLayer(l.id);
                                });
                                this.map.removeSource(sid);
                            }
                        });
                        this.layerSources = {};
                        this.layers.forEach(layer => this.addLayerToMap(layer));
                    }, 100);
                });

                this.map.on('zoomend', () => {
                    this.repositionMarkers();
                    this.updateClusteringVisibility();
                });
                this.map.on('moveend', () => {
                    this.repositionMarkers();
                    this.updateClusteringVisibility();
                });

                // ═══ Fase 4: Event delegation for popup edit button ═══
                document.addEventListener('click', (e) => {
                    var btn = e.target.closest('.dlh-popup-edit-btn');
                    if (!btn) return;
                    e.stopPropagation();
                    var layerId = parseInt(btn.dataset.layerId);
                    var featureIndex = parseInt(btn.dataset.featureIndex);
                    var layer = this.layers.find(l => l.id === layerId);
                    if (!layer) return;
                    var features = (layer.geojson && layer.geojson.features) || [];
                    var feature = features[featureIndex];
                    if (!feature) return;
                    // Open sidebar + expand layer + open edit modal
                    this.sidebarOpen = true;
                    this.expandedLayer = layerId;
                    this.showMarkerEdit(layer, { featureIndex: featureIndex, properties: feature.properties || {}, coords: feature.geometry?.coordinates });
                });
            },

            // ═══════════════ CLUSTERING VISIBILITY ═══════════════
            updateClusteringVisibility() {
                const zoom = this.map.getZoom();
                this.layers.forEach(layer => {
                    if (!layer.is_visible) return;
                    const id = 'layer-' + layer.id;
                    const hasMarkers = !!this.layerMarkers[layer.id];
                    const features = (layer.geojson && layer.geojson.features) || [];
                    const isClusterable = features.length >= 10;

                    if (isClusterable) {
                        if (zoom < 14) {
                            // Sembunyikan marker HTML
                            if (hasMarkers) {
                                this.layerMarkers[layer.id].forEach(item => item.marker.remove());
                            }
                            // Tampilkan clusters native
                            if (this.map.getLayer(id + '-clusters')) {
                                this.map.setLayoutProperty(id + '-clusters', 'visibility', 'visible');
                                this.map.setLayoutProperty(id + '-cluster-count', 'visibility', 'visible');
                            }
                            // Tampilkan fallback native point
                            if (this.map.getLayer(id + '-point')) {
                                this.map.setLayoutProperty(id + '-point', 'visibility', 'visible');
                                if (this.map.getLayer(id + '-point-glow')) {
                                    this.map.setLayoutProperty(id + '-point-glow', 'visibility', 'visible');
                                }
                            }
                        } else {
                            // Tampilkan marker HTML
                            if (hasMarkers) {
                                this.layerMarkers[layer.id].forEach(item => item.marker.addTo(this.map));
                            }
                            // Sembunyikan clusters native
                            if (this.map.getLayer(id + '-clusters')) {
                                this.map.setLayoutProperty(id + '-clusters', 'visibility', 'none');
                                this.map.setLayoutProperty(id + '-cluster-count', 'visibility', 'none');
                            }
                            // Sembunyikan fallback native point
                            if (this.map.getLayer(id + '-point')) {
                                this.map.setLayoutProperty(id + '-point', 'visibility', 'none');
                                if (this.map.getLayer(id + '-point-glow')) {
                                    this.map.setLayoutProperty(id + '-point-glow', 'visibility', 'none');
                                }
                            }
                        }
                    }
                });
            },

            // ═══════════════ ADD LAYER TO MAP ═══════════════
            addLayerToMap(layer) {
                const id = 'layer-' + layer.id;
                const color = layer.metadata?.color || '#6b7280';

                if (this.map.getSource(id)) return;

                const features = (layer.geojson && layer.geojson.features) || [];
                // Clustering HANYA untuk layer murni point (MapLibre cluster hanya handle Point)
                const isClusterable = features.length >= 10 && layer.jenis_geometri === 'point';

                // Buat salinan bersih GeoJSON — strip CRS field (MapLibre tidak support)
                // Dibuat baru tanpa copy crs dari layer.geojson
                const geojsonData = { type: 'FeatureCollection', features: features };

                try {
                    this.map.addSource(id, {
                        type: 'geojson',
                        data: geojsonData,
                        cluster: isClusterable,
                        clusterMaxZoom: 14,
                        clusterRadius: 50
                    });
                } catch (e) {
                    console.error('[DLH Peta] Gagal addSource untuk layer', layer.nama_layer, ':', e.message);
                    return;
                }

                // Points - DlhMarkers custom SVG icons
                if (['point', 'mixed'].includes(layer.jenis_geometri)) {
                    const features = (layer.geojson && layer.geojson.features) || [];
                    const useDlh = typeof window.DlhMarkers !== 'undefined';

                    const LAYER_MARKER_MAP = {
                        'taman kota': 'taman', 'taman': 'taman',
                        'hutan kota': 'hutan', 'hutan': 'hutan',
                        'pohon pelindung': 'pohon', 'pohon': 'pohon',
                        'jalur hijau': 'jalur_hijau', 'jalur': 'jalur_hijau',
                        'aset rth': 'aset_rth', 'aset': 'aset_rth',
                        'bank sampah': 'bank_sampah',
                        'tpst': 'tpst', 'tpa': 'tpa', 'tps': 'tps',
                        'armada': 'armada',
                        'objek pengawasan': 'objek_pengawasan',
                        'pengaduan': 'pengaduan',
                    };
                    function getMarkerType(layerName) {
                        var name = (layerName || '').toLowerCase().trim();
                        for (var key in LAYER_MARKER_MAP) {
                            if (name === key || name.indexOf(key) !== -1) return LAYER_MARKER_MAP[key];
                        }
                        return 'default';
                    }
                    const markerType = getMarkerType(layer.nama_layer);

                    // Setup native cluster layers if clusterable
                    if (isClusterable) {
                        this.map.addLayer({
                            id: id + '-clusters',
                            type: 'circle',
                            source: id,
                            filter: ['has', 'point_count'],
                            paint: {
                                'circle-color': color,
                                'circle-radius': [
                                    'step',
                                    ['get', 'point_count'],
                                    15, 5, 20, 20, 25
                                ],
                                'circle-opacity': 0.85,
                                'circle-stroke-width': 2.5,
                                'circle-stroke-color': '#fff'
                            }
                        });

                        this.map.addLayer({
                            id: id + '-cluster-count',
                            type: 'symbol',
                            source: id,
                            filter: ['has', 'point_count'],
                            layout: {
                                'text-field': '{point_count}',
                                'text-size': 11,
                                'text-offset': [0, 0]
                            },
                            paint: {
                                'text-color': '#ffffff'
                            }
                        });

                        this.map.on('click', id + '-clusters', (e) => {
                            var features = this.map.queryRenderedFeatures(e.point, { layers: [id + '-clusters'] });
                            if (!features.length) return;
                            var clusterId = features[0].properties.cluster_id;
                            this.map.getSource(id).getClusterExpansionZoom(clusterId, (err, zoom) => {
                                if (err) return;
                                this.map.easeTo({
                                    center: features[0].geometry.coordinates,
                                    zoom: zoom + 0.5
                                });
                            });
                        });

                        this.map.on('mouseenter', id + '-clusters', () => { this.map.getCanvas().style.cursor = 'pointer'; });
                        this.map.on('mouseleave', id + '-clusters', () => { this.map.getCanvas().style.cursor = ''; });

                        // Add native circle layers for unclustered points at low zoom
                        this.map.addLayer({ id: id + '-point-glow', type: 'circle', source: id, filter: ['!', ['has', 'point_count']], paint: { 'circle-radius': 16, 'circle-color': color, 'circle-opacity': 0.15, 'circle-blur': 0.8 } });
                        this.map.addLayer({ id: id + '-point', type: 'circle', source: id, filter: ['!', ['has', 'point_count']], paint: { 'circle-radius': 8, 'circle-color': color, 'circle-stroke-color': '#fff', 'circle-stroke-width': 2.5, 'circle-opacity': 0.9 } });

                        // Fallback click handler on native unclustered points
                        const fallbackClickHandler = (e) => {
                            if (!e.features || !e.features[0]) return;
                            var f = e.features[0];
                            var props = f.properties || {};
                            var name = props.NAMA || layer.nama_layer;
                            var rows = '';
                            Object.keys(props).forEach(key => {
                                if (key.startsWith('_') || key === 'NAMA') return;
                                if (props[key] === null || props[key] === '') return;
                                rows += '<div style="display:flex;gap:6px;padding:3px 0;font-size:12px;color:#475569;"><span style="color:#94a3b8;font-weight:600;min-width:80px;">' + this.humanizeKey(key) + '</span>' + props[key] + '</div>';
                            });
                            new maplibregl.Popup({ offset: 12, maxWidth: '280px', className: 'pp-popup' })
                                .setLngLat(e.lngLat)
                                .setHTML('<div style="padding:12px;"><p style="font-weight:700;font-size:13px;color:#0f172a;margin-bottom:6px;">' + name + '</p>' + rows + '</div>')
                                .addTo(this.map);
                        };
                        this.map.on('click', id + '-point', fallbackClickHandler);
                        this.map.on('mouseenter', id + '-point', () => { this.map.getCanvas().style.cursor = 'pointer'; });
                        this.map.on('mouseleave', id + '-point', () => { this.map.getCanvas().style.cursor = ''; });
                    }

                    if (useDlh) {
                        const pointMarkers = [];
                        // Fase 1: track featureIndex
                        features.forEach((f, featureIndex) => {
                            if (!f.geometry || f.geometry.type !== 'Point') return;
                            const coords = f.geometry.coordinates;
                            if (!coords || !coords[0] || !coords[1]) return;
                            const props = f.properties || {};
                            const lngLat = [coords[0], coords[1]];

                            // Fase 4: Build detail rows from ALL properties
                            const detailRows = [];
                            const priorityFields = ['ALAMAT', 'KECAMATAN', 'KELURAHAN'];
                            priorityFields.forEach(key => {
                                if (props[key]) detailRows.push({ icon: 'lokasi', value: props[key] });
                            });
                            detailRows.push({ icon: 'lokasi', value: coords[1].toFixed(6) + ', ' + coords[0].toFixed(6) });
                            const skipKeys = new Set(['NAMA', '_record', '_marker_type', ...priorityFields]);
                            Object.keys(props).forEach(key => {
                                if (skipKeys.has(key) || key.startsWith('_')) return;
                                if (props[key] === null || props[key] === '') return;
                                var icon = 'doc';
                                if (key === 'STATUS') icon = 'status';
                                else if (key.indexOf('LUAS') !== -1 || key.indexOf('AREA') !== -1) icon = 'area';
                                else if (key.indexOf('KAPASITAS') !== -1 || key.indexOf('VOLUME') !== -1) icon = 'volume';
                                else if (key.indexOf('POHON') !== -1 || key.indexOf('VEGETASI') !== -1) icon = 'pohon';
                                else if (key.indexOf('TAHUN') !== -1 || key.indexOf('TANGGAL') !== -1) icon = 'kalender';
                                else if (key.indexOf('ASET') !== -1) icon = 'aset';
                                else if (key.indexOf('FASILITAS') !== -1) icon = 'fasilitas';
                                else if (key.indexOf('PANJANG') !== -1 || key.indexOf('LEBAR') !== -1) icon = 'panjang';
                                detailRows.push({ icon: icon, value: this.humanizeKey(key) + ': ' + props[key] });
                            });

                            const featureMarkerType = props._marker_type || markerType;

                            const popupHtml = DlhMarkers.popup({
                                nama: props.NAMA || layer.nama_layer,
                                kategori: layer.nama_layer,
                                type: featureMarkerType,
                                layerId: layer.id,
                                featureIndex: featureIndex,
                                status: props.STATUS ? { text: props.STATUS, color: props.STATUS === 'Aktif' ? '#22c55e' : props.STATUS === 'Rusak' || props.STATUS === 'Rusak Ringan' ? '#ef4444' : '#f59e0b' } : null,
                                details: detailRows,
                            });

                            const mk = DlhMarkers.addToMap(this.map, featureMarkerType, lngLat, popupHtml);
                            
                            // Hanya tambahkan ke peta jika layer visible dan zoom level memadai atau tidak di-cluster
                            const currentZoom = this.map.getZoom();
                            const isVisibleInitially = layer.is_visible && (!isClusterable || currentZoom >= 14);
                            if (!isVisibleInitially) mk.remove();

                            pointMarkers.push({ marker: mk, coords: lngLat, featureIndex: featureIndex, properties: props });
                        });
                        this.layerMarkers[layer.id] = pointMarkers;
                    } else if (!isClusterable) {
                        // Fallback circle layers jika tidak pakai DlhMarkers dan tidak di-cluster
                        try {
                            this.map.addLayer({ id: id + '-point-glow', type: 'circle', source: id, paint: { 'circle-radius': 18, 'circle-color': color, 'circle-opacity': 0.15, 'circle-blur': 0.8 } });
                            this.map.addLayer({ id: id + '-point', type: 'circle', source: id, paint: { 'circle-radius': 9, 'circle-color': color, 'circle-stroke-color': '#fff', 'circle-stroke-width': 3, 'circle-opacity': 0.9 } });

                        this.map.on('click', id + '-point', (e) => {
                            if (!e.features || !e.features[0]) return;
                            var f = e.features[0];
                            var props = f.properties || {};
                            var name = props.NAMA || layer.nama_layer;
                            var rows = '';
                            Object.keys(props).forEach(key => {
                                if (key.startsWith('_') || key === 'NAMA') return;
                                if (props[key] === null || props[key] === '') return;
                                rows += '<div style="display:flex;gap:6px;padding:3px 0;font-size:12px;color:#475569;"><span style="color:#94a3b8;font-weight:600;min-width:80px;">' + this.humanizeKey(key) + '</span>' + props[key] + '</div>';
                            });
                            new maplibregl.Popup({ offset: 12, maxWidth: '280px', className: 'pp-popup' })
                                .setLngLat(e.lngLat)
                                .setHTML('<div style="padding:12px;"><p style="font-weight:700;font-size:13px;color:#0f172a;margin-bottom:6px;">' + name + '</p>' + rows + '</div>')
                                .addTo(this.map);
                        });
                        this.map.on('mouseenter', id + '-point', () => { this.map.getCanvas().style.cursor = 'pointer'; });
                        this.map.on('mouseleave', id + '-point', () => { this.map.getCanvas().style.cursor = ''; });
                        } catch (e) {
                            console.error('[DLH Peta] Gagal addLayer point fallback untuk', layer.nama_layer, ':', e.message);
                        }
                    }
                }

                // Lines — tanpa filter $type (MapLibre v4 hanya support Point/LineString/Polygon,
                // tidak support MultiLineString/MultiPolygon di filter. Line layer otomatis render line geometry)
                if (['line', 'mixed'].includes(layer.jenis_geometri)) {
                    try {
                        this.map.addLayer({
                            id: id + '-line', type: 'line', source: id,
                            paint: { 'line-color': color, 'line-width': 2 },
                        });
                    } catch (e) {
                        console.error('[DLH Peta] Gagal addLayer line untuk', layer.nama_layer, ':', e.message);
                    }
                }

                // Polygons — tanpa filter $type (sama alasanannya)
                if (['polygon', 'mixed'].includes(layer.jenis_geometri)) {
                    try {
                        this.map.addLayer({
                            id: id + '-fill', type: 'fill', source: id,
                            paint: { 'fill-color': color, 'fill-opacity': 0.3 },
                        });
                        this.map.addLayer({
                            id: id + '-outline', type: 'line', source: id,
                            paint: { 'line-color': color, 'line-width': 1 },
                        });
                    } catch (e) {
                        console.error('[DLH Peta] Gagal addLayer polygon untuk', layer.nama_layer, ':', e.message);
                    }
                }

                this.layerSources[layer.id] = id;

                if (!layer.is_visible) {
                    this.hideLayer(layer.id);
                }
            },

            // ═══════════════ LAYER VISIBILITY ═══════════════
            hideLayer(layerId) {
                const sourceId = this.layerSources[layerId];
                if (!sourceId) return;
                const suffixes = ['-point-glow', '-point', '-line', '-fill', '-outline', '-clusters', '-cluster-count'];
                suffixes.forEach(s => {
                    if (this.map.getLayer(sourceId + s)) {
                        this.map.setLayoutProperty(sourceId + s, 'visibility', 'none');
                    }
                });
                if (this.layerMarkers[layerId]) {
                    this.layerMarkers[layerId].forEach(item => item.marker.remove());
                }
            },

            showLayer(layerId) {
                const sourceId = this.layerSources[layerId];
                if (!sourceId) return;
                const suffixes = ['-point-glow', '-point', '-line', '-fill', '-outline'];
                suffixes.forEach(s => {
                    if (this.map.getLayer(sourceId + s)) {
                        this.map.setLayoutProperty(sourceId + s, 'visibility', 'visible');
                    }
                });
                this.updateClusteringVisibility();
            },

            repositionMarkers() {
                Object.values(this.layerMarkers).forEach(items => {
                    items.forEach(item => {
                        if (item.marker && item.coords) {
                            item.marker.setLngLat(item.coords);
                        }
                    });
                });
            },

            toggleLayerVisibility(layer) {
                if (layer.is_visible) { this.showLayer(layer.id); }
                else { this.hideLayer(layer.id); }
                fetch(`/admin/peta/layer/${layer.id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' },
                    body: JSON.stringify({ is_visible: layer.is_visible }),
                });
            },

            toggleLayerPublic(layer) {
                layer.is_public = !layer.is_public;
                fetch(`/admin/peta/layer/${layer.id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' },
                    body: JSON.stringify({ is_public: layer.is_public }),
                }).then(res => res.json()).then(data => {
                    if (data.success) {
                        this.showToast(
                            layer.is_public
                                ? `Layer "${layer.nama_layer}" sekarang tampil di publik`
                                : `Layer "${layer.nama_layer}" disembunyikan dari publik`,
                            'success'
                        );
                    } else {
                        layer.is_public = !layer.is_public; // rollback
                        this.showToast('Gagal mengubah visibilitas publik', 'error');
                    }
                }).catch(() => {
                    layer.is_public = !layer.is_public; // rollback on error
                    this.showToast('Gagal mengubah visibilitas publik', 'error');
                });
            },

            // ═══════════════ PANEL: EXPAND / COLLAPSE ═══════════════
            toggleExpand(layerId) {
                this.expandedLayer = this.expandedLayer === layerId ? null : layerId;
                this.markerSearch = '';
            },

            // ═══════════════ TOGGLE ALL LAYERS ═══════════════
            allLayersVisible() {
                return this.layers.length > 0 && this.layers.every(l => l.is_visible);
            },

            toggleAllLayers() {
                const targetVisible = !this.allLayersVisible();
                this.layers.forEach(layer => {
                    layer.is_visible = targetVisible;
                    if (targetVisible) { this.showLayer(layer.id); }
                    else { this.hideLayer(layer.id); }
                });
                fetch('/admin/peta/layers/bulk-visibility', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' },
                    body: JSON.stringify({ visible: targetVisible }),
                });
            },

            getFeatureCount(layer) {
                return (layer.geojson && layer.geojson.features) ? layer.geojson.features.length : 0;
            },

            getFilteredMarkers(layer) {
                var features = (layer.geojson && layer.geojson.features) || [];
                var search = (this.markerSearch || '').toLowerCase().trim();
                var result = [];
                features.forEach((f, idx) => {
                    if (!f.geometry || f.geometry.type !== 'Point') return;
                    var props = f.properties || {};
                    var item = { featureIndex: idx, properties: props, coords: f.geometry.coordinates };
                    if (!search) { result.push(item); return; }
                    // Search across all property values
                    var found = Object.values(props).some(v => v && String(v).toLowerCase().indexOf(search) !== -1);
                    if (found) result.push(item);
                });
                return result;
            },

            getMarkerName(item, layer) {
                return item.properties.NAMA || item.properties.name || layer.nama_layer;
            },

            getMarkerSub(item) {
                if (item.properties.ALAMAT) return item.properties.ALAMAT;
                if (item.properties.KECAMATAN) return item.properties.KECAMATAN;
                if (item.coords) return item.coords[1]?.toFixed(5) + ', ' + item.coords[0]?.toFixed(5);
                return '';
            },

            // ═══════════════ FLY TO MARKER ═══════════════
            flyToMarker(item) {
                if (item.coords) {
                    this.map.flyTo({ center: [item.coords[0], item.coords[1]], zoom: 16, duration: 800 });
                }
            },

            // ═══════════════ FIT BOUNDS TO LAYER ═══════════════
            fitLayerBounds(layer) {
                const features = (layer.geojson && layer.geojson.features) || [];
                if (features.length === 0) return;
                const bounds = new maplibregl.LngLatBounds();
                features.forEach(f => {
                    if (!f.geometry) return;
                    const t = f.geometry.type;
                    if (t === 'Point') { bounds.extend(f.geometry.coordinates); }
                    else if (t === 'MultiPoint') { f.geometry.coordinates.forEach(c => bounds.extend(c)); }
                    else if (t === 'LineString') { f.geometry.coordinates.forEach(c => bounds.extend(c)); }
                    else if (t === 'MultiLineString') { f.geometry.coordinates.forEach(line => line.forEach(c => bounds.extend(c))); }
                    else if (t === 'Polygon') { f.geometry.coordinates[0].forEach(c => bounds.extend(c)); }
                    else if (t === 'MultiPolygon') { f.geometry.coordinates.forEach(poly => poly[0].forEach(c => bounds.extend(c))); }
                });
                if (!bounds.isEmpty()) {
                    this.map.fitBounds(bounds, { padding: 50, maxZoom: 15, duration: 800 });
                }
            },

            // ═══════════════ DETAIL MODAL ═══════════════
            showMarkerDetail(layer, item) {
                this.detailModal = {
                    show: true,
                    name: item.properties.NAMA || item.properties.name || layer.nama_layer,
                    layerName: layer.nama_layer,
                    properties: { ...item.properties },
                    coords: item.coords ? (item.coords[1]?.toFixed(6) + ', ' + item.coords[0]?.toFixed(6)) : '—',
                    color: layer.metadata?.color || '#6b7280',
                    layerRef: layer,
                    itemRef: item,
                };
            },

            // ═══════════════ EDIT MODAL ═══════════════
            showMarkerEdit(layer, item) {
                var props = { ...item.properties };
                // Remove internal keys from display
                delete props._record;
                this.editModal = {
                    show: true,
                    layerName: layer.nama_layer,
                    layerId: layer.id,
                    featureIndex: item.featureIndex,
                    properties: props,
                    lat: item.coords ? item.coords[1] : 0,
                    lng: item.coords ? item.coords[0] : 0,
                    markerType: props._marker_type || '',
                    saving: false,
                };
                this.newFieldKey = '';
                this.newFieldValue = '';
            },

            addNewField() {
                if (!this.newFieldKey.trim()) return;
                var key = this.newFieldKey.trim().toUpperCase().replace(/\s+/g, '_');
                this.editModal.properties[key] = this.newFieldValue;
                this.newFieldKey = '';
                this.newFieldValue = '';
            },

            async saveMarkerEdit() {
                this.editModal.saving = true;
                var props = { ...this.editModal.properties };
                // Remove _marker_type from properties (sent separately)
                delete props._marker_type;

                try {
                    const res = await fetch(`/admin/peta/layer/${this.editModal.layerId}/feature/${this.editModal.featureIndex}`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' },
                        body: JSON.stringify({
                            properties: props,
                            geometry: { coordinates: [this.editModal.lng, this.editModal.lat] },
                            marker_type: this.editModal.markerType || null,
                        }),
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.showToast('Marker berhasil diupdate', 'success');
                        this.editModal.show = false;
                        // Update local data
                        var layer = this.layers.find(l => l.id === this.editModal.layerId);
                        if (layer && layer.geojson && layer.geojson.features[this.editModal.featureIndex]) {
                            layer.geojson.features[this.editModal.featureIndex].properties = data.feature?.properties || props;
                            layer.geojson.features[this.editModal.featureIndex].geometry.coordinates = [this.editModal.lng, this.editModal.lat];
                        }
                        // Refresh markers on map
                        this.refreshLayerMarkers(layer);
                    } else {
                        this.showToast(data.message || 'Gagal update marker', 'error');
                    }
                } catch (e) {
                    this.showToast('Gagal update marker: ' + e.message, 'error');
                } finally {
                    this.editModal.saving = false;
                }
            },

            // ═══════════════ DELETE FEATURE ═══════════════
            async deleteFeature(layer, item) {
                if (!confirm('Hapus marker "' + this.getMarkerName(item, layer) + '"?')) return;
                try {
                    const res = await fetch(`/admin/peta/layer/${layer.id}/feature`, {
                        method: 'DELETE',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' },
                        body: JSON.stringify({ feature_index: item.featureIndex }),
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.showToast('Marker berhasil dihapus', 'success');
                        // Remove from local data
                        if (layer.geojson && layer.geojson.features) {
                            layer.geojson.features.splice(item.featureIndex, 1);
                        }
                        // Remove marker from map
                        if (this.layerMarkers[layer.id]) {
                            var mkItem = this.layerMarkers[layer.id].find(m => m.featureIndex === item.featureIndex);
                            if (mkItem) mkItem.marker.remove();
                        }
                        // Refresh
                        this.refreshLayerMarkers(layer);
                    } else {
                        this.showToast(data.message || 'Gagal menghapus marker', 'error');
                    }
                } catch (e) {
                    this.showToast('Gagal menghapus marker', 'error');
                }
            },

            // ═══════════════ REFRESH LAYER MARKERS ═══════════════
            refreshLayerMarkers(layer) {
                // Remove old markers
                if (this.layerMarkers[layer.id]) {
                    this.layerMarkers[layer.id].forEach(item => item.marker.remove());
                    delete this.layerMarkers[layer.id];
                }
                // Remove old source
                const sourceId = this.layerSources[layer.id];
                if (sourceId && this.map.getSource(sourceId)) {
                    // Update source data instead of re-adding
                    this.map.getSource(sourceId).setData(layer.geojson);
                }
                // Re-add markers (only DlhMarkers, not MapLibre layers)
                if (['point', 'mixed'].includes(layer.jenis_geometri) && typeof window.DlhMarkers !== 'undefined') {
                    const features = (layer.geojson && layer.geojson.features) || [];
                    const LAYER_MARKER_MAP = {
                        'taman kota': 'taman', 'taman': 'taman', 'hutan kota': 'hutan', 'hutan': 'hutan',
                        'pohon pelindung': 'pohon', 'pohon': 'pohon', 'jalur hijau': 'jalur_hijau', 'jalur': 'jalur_hijau',
                        'aset rth': 'aset_rth', 'aset': 'aset_rth', 'bank sampah': 'bank_sampah',
                        'tpst': 'tpst', 'tpa': 'tpa', 'tps': 'tps', 'armada': 'armada',
                        'objek pengawasan': 'objek_pengawasan', 'pengaduan': 'pengaduan',
                    };
                    function getMarkerType(n) { var name = (n||'').toLowerCase().trim(); for (var k in LAYER_MARKER_MAP) { if (name === k || name.indexOf(k) !== -1) return LAYER_MARKER_MAP[k]; } return 'default'; }
                    const markerType = getMarkerType(layer.nama_layer);
                    const pointMarkers = [];
                    features.forEach((f, featureIndex) => {
                        if (!f.geometry || f.geometry.type !== 'Point') return;
                        const coords = f.geometry.coordinates;
                        if (!coords || !coords[0] || !coords[1]) return;
                        const props = f.properties || {};
                        const lngLat = [coords[0], coords[1]];
                        const featureMarkerType = props._marker_type || markerType;

                        const detailRows = [];
                        ['ALAMAT', 'KECAMATAN', 'KELURAHAN'].forEach(key => { if (props[key]) detailRows.push({ icon: 'lokasi', value: props[key] }); });
                        detailRows.push({ icon: 'lokasi', value: coords[1].toFixed(6) + ', ' + coords[0].toFixed(6) });
                        const skipKeys = new Set(['NAMA', '_record', '_marker_type', 'ALAMAT', 'KECAMATAN', 'KELURAHAN']);
                        Object.keys(props).forEach(key => {
                            if (skipKeys.has(key) || key.startsWith('_')) return;
                            if (props[key] === null || props[key] === '') return;
                            detailRows.push({ icon: 'doc', value: this.humanizeKey(key) + ': ' + props[key] });
                        });

                        const popupHtml = DlhMarkers.popup({
                            nama: props.NAMA || layer.nama_layer, kategori: layer.nama_layer,
                            type: featureMarkerType, layerId: layer.id, featureIndex: featureIndex,
                            status: props.STATUS ? { text: props.STATUS, color: props.STATUS === 'Aktif' ? '#22c55e' : '#f59e0b' } : null,
                            details: detailRows,
                        });
                        const mk = DlhMarkers.addToMap(this.map, featureMarkerType, lngLat, popupHtml);
                        
                        const isClusterable = features.length >= 10;
                        const currentZoom = this.map.getZoom();
                        const isVisibleInitially = layer.is_visible && (!isClusterable || currentZoom >= 14);
                        if (!isVisibleInitially) mk.remove();
                        
                        pointMarkers.push({ marker: mk, coords: lngLat, featureIndex: featureIndex, properties: props });
                    });
                    this.layerMarkers[layer.id] = pointMarkers;
                }
                this.updateClusteringVisibility();
            },

            // ═══════════════ DRAW TOOLS (Titik saja) ═══════════════
            startPointDraw() {
                this.drawMode = 'point';
                this.map.getCanvas().style.cursor = 'crosshair';
            },

            cancelDraw() {
                if (this.tempMarker) { this.tempMarker.remove(); this.tempMarker = null; }
                this.drawMode = 'simple_select';
                this.map.getCanvas().style.cursor = '';
            },

            async saveDrawn() {
                if (!this.tempMarker) return this.showToast('Klik peta untuk meletakkan titik terlebih dahulu', 'error');

                var layerId = this.expandedLayer;
                if (!layerId) {
                    return this.showToast('Buka salah satu layer di panel samping terlebih dahulu!', 'error');
                }
                var targetLayer = this.layers.find(l => l.id == layerId);
                if (!targetLayer) return this.showToast('Layer tidak ditemukan', 'error');

                const lngLat = this.tempMarker.getLngLat();
                const feature = {
                    type: 'Feature',
                    geometry: { type: 'Point', coordinates: [lngLat.lng, lngLat.lat] },
                    properties: { NAMA: 'Titik baru', created_at: new Date().toISOString() },
                };

                try {
                    const res = await fetch('/admin/peta/draw', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' },
                        body: JSON.stringify({ layer_id: layerId, features: [feature] }),
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.showToast(data.message, 'success');
                        this.cancelDraw();
                        this.showDrawToolbar = false;
                        location.reload();
                    } else {
                        this.showToast(data.message, 'error');
                    }
                } catch (e) {
                    this.showToast('Gagal menyimpan titik', 'error');
                }
            },

            // ═══════════════ IMPORT ═══════════════
            async submitImport() {
                if (!this.importForm.file || !this.importForm.nama_layer) return;

                this.importing = true;
                const formData = new FormData();
                formData.append('file', this.importForm.file);
                formData.append('nama_layer', this.importForm.nama_layer);
                formData.append('deskripsi', this.importForm.deskripsi || '');
                formData.append('bidang', this.importForm.bidang);
                formData.append('color', this.importForm.color);

                try {
                    const res = await fetch(`/admin/peta/import`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' },
                        body: formData,
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        this.showToast(data.message, 'success');
                        this.showImport = false;
                        this.importForm = { nama_layer: '', deskripsi: '', color: this.importForm.color, file: null };
                        // Store layer ID for auto-fit bounds after page reload
                        if (data.layer && data.layer.id) {
                            sessionStorage.setItem('peta_import_fit_layer', data.layer.id);
                        }
                        location.reload();
                    } else {
                        const msg = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Gagal import');
                        this.showToast(msg, 'error');
                    }
                } catch (e) {
                    this.showToast('Gagal import: ' + e.message, 'error');
                } finally {
                    this.importing = false;
                }
            },

            // ═══════════════ LAYER MANAGEMENT ═══════════════
            editLayer(layer) {
                const newName = prompt('Nama layer:', layer.nama_layer);
                if (newName && newName !== layer.nama_layer) {
                    fetch(`/admin/peta/layer/${layer.id}`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' },
                        body: JSON.stringify({ nama_layer: newName }),
                    }).then(() => location.reload());
                }
            },

            async deleteLayer(layer) {
                if (!confirm(`Hapus layer "${layer.nama_layer}"?`)) return;
                try {
                    const res = await fetch(`/admin/peta/layer/${layer.id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' },
                    });
                    const data = await res.json();
                    if (data.success) {
                        // Hapus layer dari data lokal Alpine
                        const layerIndex = this.layers.findIndex(l => l.id === layer.id);
                        if (layerIndex !== -1) {
                            this.layers.splice(layerIndex, 1);
                        }

                        // Sembunyikan layer dari peta
                        this.hideLayer(layer.id);

                        // Tampilkan toast sukses dengan opsi Urungkan (Undo)
                        this.showToast(`Layer "${layer.nama_layer}" berhasil dihapus`, 'success', async () => {
                            try {
                                const restoreRes = await fetch(`/admin/peta/layer/${data.layer_id}/restore`, {
                                    method: 'POST',
                                    headers: { 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' },
                                });
                                const restoreData = await restoreRes.json();
                                if (restoreData.success) {
                                    // Masukkan kembali ke data lokal Alpine
                                    this.layers.push(restoreData.layer);
                                    // Tampilkan kembali di peta
                                    this.addLayerToMap(restoreData.layer);
                                    this.showToast(`Layer "${restoreData.layer.nama_layer}" berhasil dipulihkan`, 'success');
                                } else {
                                    this.showToast('Gagal memulihkan layer', 'error');
                                }
                            } catch (err) {
                                this.showToast('Gagal memulihkan layer: ' + err.message, 'error');
                            }
                        });
                    }
                } catch (e) {
                    this.showToast('Gagal menghapus layer', 'error');
                }
            },

            toggleSelectAll(checked) {
                if (checked) {
                    this.selectedLayers = this.layers.map(l => l.id);
                } else {
                    this.selectedLayers = [];
                }
            },

            async bulkDeleteLayers() {
                if (this.selectedLayers.length === 0) return;
                if (!confirm(`Hapus ${this.selectedLayers.length} layer terpilih?`)) return;

                try {
                    const res = await fetch('/admin/peta/layers/bulk-delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        },
                        body: JSON.stringify({ ids: this.selectedLayers })
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.showToast(data.message, 'success');
                        
                        // Hide layers from map
                        this.selectedLayers.forEach(id => {
                            this.hideLayer(id);
                        });
                        
                        // Remove layers from Alpine state
                        this.layers = this.layers.filter(l => !this.selectedLayers.includes(l.id));
                        
                        this.selectedLayers = [];
                        this.isSelectionMode = false;
                    } else {
                        this.showToast(data.message || 'Gagal menghapus layer', 'error');
                    }
                } catch (e) {
                    this.showToast('Gagal menghapus layer: ' + e.message, 'error');
                }
            },

            changeBasemap(style) {
                this.currentBasemap = style;
                if (style === 'satellite') {
                    this.map.setStyle({
                        version: 8,
                        sources: {
                            'satellite': {
                                type: 'raster',
                                tiles: ['https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}'],
                                tileSize: 256,
                                attribution: 'Tiles &copy; Esri'
                            }
                        },
                        layers: [{
                            id: 'satellite',
                            type: 'raster',
                            source: 'satellite',
                            minzoom: 0,
                            maxzoom: 22
                        }]
                    });
                } else {
                    this.map.setStyle(style);
                }

                this.map.once('style.load', () => {
                    // Re-add draw preview sources & layers
                    try {
                        if (!this.map.getSource('draw-preview')) {
                            this.map.addSource('draw-preview', {
                                type: 'geojson',
                                data: { type: 'FeatureCollection', features: [] }
                            });
                            this.map.addLayer({
                                id: 'draw-preview-fill', type: 'fill', source: 'draw-preview',
                                paint: { 'fill-color': '#ef4444', 'fill-opacity': 0.2 }
                            });
                            this.map.addLayer({
                                id: 'draw-preview-line', type: 'line', source: 'draw-preview',
                                paint: { 'line-color': '#ef4444', 'line-width': 2.5, 'line-dasharray': [2, 1] }
                            });
                            this.map.addLayer({
                                id: 'draw-preview-points', type: 'circle', source: 'draw-preview',
                                paint: { 'circle-radius': 5, 'circle-color': '#ef4444', 'circle-stroke-color': '#fff', 'circle-stroke-width': 2 }
                            });
                        }
                    } catch (e) {
                        console.warn('[DLH Peta] Gagal/Sudah menambahkan source/layer draw-preview saat ganti basemap:', e);
                    }

                    this.layerSources = {};
                    this.layers.forEach(layer => this.addLayerToMap(layer));
                });
            },

            exportLayer(layer, format) {
                if (!layer.geojson || !layer.geojson.features || layer.geojson.features.length === 0) {
                    return this.showToast('Tidak ada data untuk diexport', 'error');
                }

                const safeName = layer.nama_layer.toLowerCase().replace(/[^a-z0-9]/g, '_');

                if (format === 'geojson') {
                    const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(layer.geojson));
                    const downloadAnchorNode = document.createElement('a');
                    downloadAnchorNode.setAttribute("href", dataStr);
                    downloadAnchorNode.setAttribute("download", safeName + ".geojson");
                    document.body.appendChild(downloadAnchorNode);
                    downloadAnchorNode.click();
                    downloadAnchorNode.remove();
                    this.showToast('GeoJSON berhasil diexport', 'success');
                } else if (format === 'csv') {
                    const features = layer.geojson.features;
                    const propKeys = new Set();
                    features.forEach(f => {
                        if (f.properties) {
                            Object.keys(f.properties).forEach(k => {
                                if (!k.startsWith('_')) propKeys.add(k);
                            });
                        }
                    });
                    const headers = Array.from(propKeys);
                    headers.push('LONGITUDE', 'LATITUDE');

                    const csvRows = [headers.join(',')];

                    features.forEach(f => {
                        const row = headers.map(header => {
                            if (header === 'LONGITUDE') return f.geometry?.coordinates?.[0] || '';
                            if (header === 'LATITUDE') return f.geometry?.coordinates?.[1] || '';
                            
                            let val = f.properties?.[header] ?? '';
                            val = String(val).replace(/"/g, '""');
                            if (val.search(/("|,|\n)/g) >= 0) val = `"${val}"`;
                            return val;
                        });
                        csvRows.push(row.join(','));
                    });

                    const csvContent = csvRows.join('\n');
                    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement("a");
                    link.setAttribute("href", url);
                    link.setAttribute("download", safeName + ".csv");
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    this.showToast('CSV berhasil diexport', 'success');
                }
            },

            // ═══════════════ HELPERS ═══════════════
            handleFileDrop(e) {
                const file = e.dataTransfer.files[0];
                if (file) this.importForm.file = file;
            },

            formatSize(bytes) {
                if (!bytes) return '';
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(1024));
                return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + sizes[i];
            },

            toastTimeout: null,
            showToast(message, type = 'success', actionCallback = null) {
                if (this.toastTimeout) clearTimeout(this.toastTimeout);
                // Simpan callback terpisah dari reactive state untuk mencegah auto-trigger
                this.toastActionCallback = actionCallback || null;
                this.toast = { show: true, message, type, hasAction: !!actionCallback };
                this.toastTimeout = setTimeout(() => {
                    this.toast.show = false;
                    this.toastActionCallback = null;
                }, actionCallback ? 8000 : 4000);
            },

            triggerToastAction() {
                if (typeof this.toastActionCallback === 'function') {
                    this.toastActionCallback();
                }
                this.toastActionCallback = null;
                this.toast.show = false;
                if (this.toastTimeout) { clearTimeout(this.toastTimeout); this.toastTimeout = null; }
            },

            humanizeKey(key) {
                if (!key) return '';
                return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            },

            getStatusDot(status) {
                const c = { 'Aktif': '#22c55e', 'Non-aktif': '#ef4444', 'Perlu Perbaikan': '#f59e0b' };
                const color = c[status] || '#6b7280';
                return `<svg width="8" height="8" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4" fill="${color}"/></svg>`;
            },
        };
    }
    </script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Backup\DLH - Palu\resources\views/admin/peta/index.blade.php ENDPATH**/ ?>