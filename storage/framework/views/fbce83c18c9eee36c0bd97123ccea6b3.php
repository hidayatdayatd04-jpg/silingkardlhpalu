<?php $__env->startSection('title', 'Peta Persampahan - DLH Kota Palu'); ?>
<?php $__env->startSection('description', 'Peta interaktif titik TPA, TPST, Bank Sampah, TPS, pelacakan armada real-time, statistik timbulan sampah, dan jadwal armada DLH Kota Palu.'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="https://unpkg.com/maplibre-gl@4.1.1/dist/maplibre-gl.css" />
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<style>
    .map-container {
        position: relative;
        width: 100%;
        height: 550px;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.06);
        box-shadow: 0 4px 24px rgba(0,0,0,0.06);
    }
    .map-container .maplibregl-map {
        width: 100%;
        height: 100%;
    }
    .map-container .maplibregl-ctrl-group {
        border-radius: 10px !important;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.1) !important;
    }
    .map-container .maplibregl-ctrl {
        margin: 12px !important;
    }
    .map-container .maplibregl-ctrl-top-left {
        top: 12px !important;
        left: 12px !important;
    }
    .map-container .maplibregl-ctrl-bottom-right {
        bottom: 12px !important;
        right: 12px !important;
    }
    .map-container .maplibregl-popup-content {
        border-radius: 12px !important;
        box-shadow: 0 8px 32px rgba(0,0,0,0.12) !important;
        padding: 0 !important;
        overflow: hidden;
    }
    .custom-layer-container {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 8px;
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 20px;
        margin-bottom: 16px;
    }
    .dark .custom-layer-container {
        background: rgba(15, 23, 42, 0.3);
        border-color: #1e293b;
    }
    .custom-layer-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 12px;
        font-size: 12.5px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        border: 1.5px solid transparent;
        background: transparent;
        color: #64748b;
        user-select: none;
    }
    .custom-layer-chip:hover {
        color: #334155;
        background: rgba(15, 23, 42, 0.03);
    }
    .dark .custom-layer-chip:hover {
        color: #cbd5e1;
        background: rgba(255,255,255,0.03);
    }
    .custom-layer-chip .dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #cbd5e1;
        transition: transform 0.2s ease, background-color 0.2s ease;
    }
    .dark .custom-layer-chip .dot {
        background-color: #475569;
    }
    .layer-toggle:checked + .custom-layer-chip {
        background-color: #ffffff;
        color: #0f172a;
        border-color: #e2e8f0;
        box-shadow: 0 4px 12px rgba(15,23,42,0.04), 0 0 0 1px rgba(15,23,42,0.02);
    }
    .dark .layer-toggle:checked + .custom-layer-chip {
        background-color: #1e293b;
        color: #f8fafc;
        border-color: #334155;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    .layer-toggle:checked + .custom-layer-chip .dot {
        transform: scale(1.2);
        background-color: var(--active-color);
    }
    .custom-vehicle-icon{background:transparent!important;border:none!important;box-shadow:none!important;cursor:pointer}
    .custom-vehicle-icon img{filter:drop-shadow(0 2px 6px rgba(0,0,0,0.3));transition:transform .3s ease}
    .custom-vehicle-icon:hover img{filter:drop-shadow(0 3px 10px rgba(0,0,0,0.4))}
    .armada-count-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 2px 10px;
        background: rgba(16,185,129,0.1);
        border: 1px solid rgba(16,185,129,0.2);
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        color: #059669;
    }
    .dark .armada-count-badge {
        background: rgba(16,185,129,0.15);
        border-color: rgba(16,185,129,0.3);
        color: #34d399;
    }
    .armada-count-badge .pulse-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #10b981;
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(1.3); }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <?php if (isset($component)) { $__componentOriginal7667e390c55120bda1fc27c0189959e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7667e390c55120bda1fc27c0189959e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.page-hero','data' => ['badge' => ''.e(__('Sampah & LB3')).'','title' => ''.e(__('Peta & Informasi Persampahan')).'','description' => ''.e(__('Lihat lokasi fasilitas persampahan, lacak armada real-time, statistik timbulan sampah, dan jadwal armada.')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.page-hero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['badge' => ''.e(__('Sampah & LB3')).'','title' => ''.e(__('Peta & Informasi Persampahan')).'','description' => ''.e(__('Lihat lokasi fasilitas persampahan, lacak armada real-time, statistik timbulan sampah, dan jadwal armada.')).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7667e390c55120bda1fc27c0189959e9)): ?>
<?php $attributes = $__attributesOriginal7667e390c55120bda1fc27c0189959e9; ?>
<?php unset($__attributesOriginal7667e390c55120bda1fc27c0189959e9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7667e390c55120bda1fc27c0189959e9)): ?>
<?php $component = $__componentOriginal7667e390c55120bda1fc27c0189959e9; ?>
<?php unset($__componentOriginal7667e390c55120bda1fc27c0189959e9); ?>
<?php endif; ?>

    <div class="space-y-8">
        <div>
            <div class="custom-layer-container" id="layer-chips">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $layers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $layer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $color = $layer['metadata']['color'] ?? '#6b7280';
                ?>
                <label class="cursor-pointer">
                    <input type="checkbox" checked data-layer="<?php echo e($layer['id']); ?>" class="layer-toggle sr-only" />
                    <div class="custom-layer-chip" style="--active-color: <?php echo e($color); ?>;">
                        <span class="dot"></span>
                        <span><?php echo e($layer['nama_layer']); ?></span>
                        <span class="text-xs text-slate-400 font-semibold">(<?php echo e(count($layer['geojson']['features'] ?? [])); ?>)</span>
                    </div>
                </label>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                <label class="cursor-pointer">
                    <input type="checkbox" checked data-layer="armada" class="layer-toggle sr-only" />
                    <div class="custom-layer-chip" style="--active-color: #10b981;">
                        <span class="dot"></span>
                        <span><?php echo e(__('Pelacakan Armada')); ?></span>
                        <span class="armada-count-badge" id="armada-count-badge"><span class="pulse-dot"></span><span id="armada-count-num"><?php echo e($armada->count()); ?></span></span>
                    </div>
                </label>
            </div>

            <div class="map-container">
                <div id="peta-persampahan-map" style="width:100%;height:100%"></div>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-6 mt-4 py-3 px-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl">
                <div class="flex items-center gap-2.5">
                    <img src="/assets/tracking/car_acc_on.png" class="w-8 h-8 object-contain" alt="Pickup">
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200"><?php echo e(__('Mobil Pickup (Roda 4)')); ?></span>
                </div>
                <div class="flex items-center gap-2.5">
                    <img src="/assets/tracking/truck_acc_on.png" class="w-8 h-8 object-contain" alt="Dump Truck">
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200"><?php echo e(__('Dump Truck (Roda 6)')); ?></span>
                </div>
                <div class="flex items-center gap-2.5">
                    <img src="/assets/tracking/car_parking.png" class="w-8 h-8 object-contain" alt="Parkir">
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200"><?php echo e(__('Parkir / Mesin Mati')); ?></span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm">
                <h3 class="text-lg font-bold mb-4"><?php echo e(__('Statistik Timbulan Sampah')); ?></h3>
                <canvas id="statistik-sampah-chart" height="200"
                    data-labels='<?php echo json_encode($chartLabels, 15, 512) ?>'
                    data-values='<?php echo json_encode($chartValues, 15, 512) ?>'></canvas>
            </div>

            <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold"><?php echo e(__('Jadwal & Rute Armada')); ?></h3>
                    <div class="armada-count-badge"><span class="pulse-dot"></span><?php echo e($armada->count()); ?> <?php echo e(__('unit aktif')); ?></div>
                </div>
                <div class="space-y-3 max-h-[280px] overflow-y-auto">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $jadwal; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800">
                            <div class="font-semibold text-sm"><?php echo e($j->nama_rute); ?></div>
                            <div class="text-xs text-slate-500 mt-1"><?php echo e($j->hari); ?> · <?php echo e($j->jam); ?></div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($j->wilayah_dilalui): ?>
                                <div class="text-xs text-slate-600 dark:text-slate-400 mt-1"><?php echo e($j->wilayah_dilalui); ?></div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <p class="text-sm text-slate-500"><?php echo e(__('Belum ada jadwal armada.')); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var mapLayers = <?php echo json_encode($layers, 15, 512) ?>;
        var initialArmada = <?php echo json_encode($armada, 15, 512) ?>;
        var armadaVisible = true;

        function initMap() {
            if (typeof dlhPetaPersampahan === 'function' && mapLayers.length > 0) {
                dlhPetaPersampahan('peta-persampahan-map', mapLayers, initialArmada);
            } else {
                setTimeout(initMap, 100);
            }
        }
        initMap();

        // Armada toggle chip listener
        var armadaToggle = document.querySelector('[data-layer="armada"]');
        if (armadaToggle) {
            armadaToggle.addEventListener('change', function () {
                armadaVisible = this.checked;
                if (window._dlhArmadaMarkers) {
                    window._dlhArmadaMarkers.forEach(function (mk) {
                        armadaVisible ? mk.addTo(window._dlhMap) : mk.remove();
                    });
                }
            });
        }

        // Polling armada setiap 30 detik
        setInterval(function () {
            fetch('/api/armada-aktif')
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res.status && res.data) {
                        window.dlhPetaPersampahanDrawArmada(res.data);
                        var countEl = document.getElementById('armada-count-num');
                        if (countEl) countEl.textContent = res.data.length;
                    }
                })
                .catch(function () {});
        }, 30000);

        var canvas = document.getElementById('statistik-sampah-chart');
        if (canvas && typeof Chart !== 'undefined') {
            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: JSON.parse(canvas.dataset.labels || '[]'),
                    datasets: [{
                        label: '<?php echo e(__("Volume (ton)")); ?>',
                        data: JSON.parse(canvas.dataset.values || '[]'),
                        backgroundColor: 'rgba(16,185,129,0.6)',
                        borderColor: 'rgb(16,185,129)',
                        borderWidth: 1,
                    }],
                },
                options: {
                    responsive: true,
                    scales: { y: { beginAtZero: true } },
                },
            });
        }
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Backup\DLH - Palu\resources\views/public/peta-persampahan.blade.php ENDPATH**/ ?>