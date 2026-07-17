<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['groups', 'allGroups', 'user']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['groups', 'allGroups', 'user']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $activeResource = request()->route('resource');
    $isDashboard = request()->routeIs('admin.dashboard');

    $iconMap = [
        'dashboard' => 'dashboard',
        'pengaduan-pengendalian' => 'megaphone',
        'permohonan-rekomendasi' => 'clipboard-check',
        'pengajuan-rintek-pertek' => 'factory',
        'registrasi-usaha-lb3' => 'clipboard-list',
        'jadwal-armada' => 'truck',
        'statistik-sampah' => 'chart-bar',
        'perizinan-tebang-pohon' => 'axe',
        'pinjam-taman' => 'park-bench',
        'data-tanam-pohon' => 'seedling',
        'pengaduan-tata-penataan' => 'building',
        'objek-pengawasan' => 'eye',
        'sidak' => 'clipboard-check',
        'pelanggaran' => 'alert-triangle',
        'sanksi' => 'shield',
        'sosialisasi' => 'presentation',
        'sosialisasi-peserta' => 'users',
        'pengajuan-rintek-pertek-lb3' => 'factory',
        'artikel' => 'news',
        'ikm-response' => 'star',
        'email-notification-log' => 'send',
        'user' => 'user-check',
        'website-settings' => 'settings',
    ];

    $lockedGroups = collect($allGroups)->filter(fn($g, $k) => !$user->canAccessGroup($k));
    $roleName = $user->role?->label() ?? 'Admin';

    // Peta access per role
    $hasPetaAccess = false;
    $adminRole = $user->adminRole();
    if ($adminRole) {
        $allowedGroups = $adminRole->allowedGroups();
        $hasPetaAccess = ! empty(array_intersect(['sampah-lb3', 'tata-penataan', 'rth'], $allowedGroups));
    }
?>


<div x-data="{ open: false }" x-on:keydown.escape.window="open = false" x-on:open-sidebar.window="open = true" class="lg:hidden">
    <button x-on:click="open = true" class="fixed bottom-6 right-6 z-50 size-14 rounded-2xl bg-emerald-600 text-white shadow-xl shadow-emerald-600/30 transition-all hover:scale-105 active:scale-95 flex items-center justify-center">
        <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'menu','size' => 22]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'menu','size' => 22]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
    </button>
    <div x-show="open" x-transition.opacity x-on:click="open = false" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm"></div>
    <aside x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="fixed inset-y-0 left-0 z-50 w-[300px] flex flex-col overflow-hidden text-white" style="background: linear-gradient(180deg, #0B3A2A 0%, #06291F 40%, #041B14 100%);">
        <div class="px-6 pt-6 pb-4">
            <div class="flex items-center justify-between">
                <a href="<?php echo e(route('admin.dashboard')); ?>" class="flex items-center gap-3">
                    <img src="<?php echo e(asset('assets/images/logo_kota_palu.png')); ?>" alt="Logo" class="h-11 w-auto" style="filter: drop-shadow(0 4px 12px rgba(16,185,129,0.2));">
                    <div>
                        <p class="text-[15px] font-bold text-white tracking-tight">DLH Kota Palu</p>
                        <p class="text-[10px] text-emerald-400/50 tracking-widest uppercase mt-0.5">Ruang Kendali Admin</p>
                    </div>
                </a>
                <button x-on:click="open = false" class="grid size-10 place-items-center rounded-xl text-white/40 transition-all hover:bg-white/10 hover:text-white">
                    <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'x','size' => 20]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x','size' => 20]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                </button>
            </div>
        </div>
        <nav class="sidebar-nav flex-1 overflow-y-auto px-4 py-3 space-y-1">
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="flex items-center gap-3.5 rounded-2xl px-4 py-3.5 transition-all duration-300 <?php echo e($isDashboard ? 'bg-gradient-to-r from-emerald-500/20 via-emerald-500/10 to-transparent text-white border border-emerald-500/15' : 'text-white/50 hover:bg-white/[0.04] hover:text-white/80 border border-transparent'); ?>">
                <span class="grid size-12 shrink-0 place-items-center rounded-xl <?php echo e($isDashboard ? 'bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/25' : 'bg-white/[0.04] text-white/40'); ?>"><?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'dashboard','size' => 22]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'dashboard','size' => 22]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?></span>
                <span class="text-[13px] font-semibold">Dashboard</span>
            </a>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupKey => $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="pt-3">
                    <div class="flex items-center gap-3 px-2 mb-2.5">
                        <div class="h-px flex-1 bg-gradient-to-r from-white/[0.08] to-transparent"></div>
                        <p class="text-[9px] font-bold uppercase tracking-[0.2em] text-white/30 shrink-0"><?php echo e($group['label']); ?></p>
                        <div class="h-px flex-1 bg-gradient-to-l from-white/[0.08] to-transparent"></div>
                    </div>
                    <div class="space-y-1">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $group['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php $url = route('admin.resources.index', ['resource' => $item['slug']]); $isActive = $activeResource === $item['slug']; ?>
                            <a href="<?php echo e($url); ?>" class="group relative flex items-center gap-3.5 rounded-2xl px-4 py-3 transition-all duration-300 <?php echo e($isActive ? 'bg-gradient-to-r from-emerald-500/20 via-emerald-500/10 to-transparent text-white border border-emerald-500/15' : 'text-white/50 hover:bg-white/[0.04] hover:text-white/80 border border-transparent'); ?>">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isActive): ?><span class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.5)]"></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <span class="grid size-10 shrink-0 place-items-center rounded-xl <?php echo e($isActive ? 'bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/20' : 'bg-white/[0.03] text-white/35'); ?>"><?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => $iconMap[$item['slug']] ?? 'folder','size' => 20]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconMap[$item['slug']] ?? 'folder'),'size' => 20]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?></span>
                                <span class="text-[13px] font-medium truncate flex-1"><?php echo e($item['label']); ?></span>
                            </a>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($groupKey === 'tata-penataan'): ?>
                            <?php
                                $standaloneLinks = [
                                    ['label' => 'Kalender Sidak', 'url' => route('admin.kalender-sidak.index'), 'icon' => 'calendar'],
                                    ['label' => 'Kalender Sosialisasi', 'url' => route('admin.kalender-sosialisasi.index'), 'icon' => 'presentation'],
                                    ['label' => 'Monitoring Sanksi', 'url' => route('admin.monitoring-sanksi.index'), 'icon' => 'shield'],
                                    ['label' => 'Laporan & Statistik', 'url' => route('admin.laporan-tata-penataan.index'), 'icon' => 'bar-chart'],
                                    ['label' => 'Laporan Sosialisasi', 'url' => route('admin.laporan-sosialisasi.index'), 'icon' => 'file-text'],
                                    ['label' => 'Laporan Ketaatan', 'url' => route('admin.laporan-ketaatan.index'), 'icon' => 'check-circle'],
                                ];
                            ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $standaloneLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php $isActive = request()->routeIs($link['url']); ?>
                                <a href="<?php echo e($link['url']); ?>" class="group relative flex items-center gap-3.5 rounded-2xl px-4 py-3 transition-all duration-300 <?php echo e($isActive ? 'bg-gradient-to-r from-emerald-500/20 via-emerald-500/10 to-transparent text-white border border-emerald-500/15' : 'text-white/50 hover:bg-white/[0.04] hover:text-white/80 border border-transparent'); ?>">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isActive): ?><span class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.5)]"></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <span class="grid size-10 shrink-0 place-items-center rounded-xl <?php echo e($isActive ? 'bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/20' : 'bg-white/[0.03] text-white/35'); ?>"><?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => $link['icon'],'size' => 20]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($link['icon']),'size' => 20]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?></span>
                                    <span class="text-[13px] font-medium truncate flex-1"><?php echo e($link['label']); ?></span>
                                </a>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasPetaAccess): ?>
            <div class="pt-3">
                <div class="flex items-center gap-3 px-2 mb-2.5">
                    <div class="h-px flex-1 bg-gradient-to-r from-white/[0.08] to-transparent"></div>
                    <p class="text-[9px] font-bold uppercase tracking-[0.2em] text-white/30 shrink-0">Peta</p>
                    <div class="h-px flex-1 bg-gradient-to-l from-white/[0.08] to-transparent"></div>
                </div>
                <div class="space-y-1">
                    <?php $isActive = request()->routeIs('admin.peta.*'); ?>
                    <a href="<?php echo e(route('admin.peta.index')); ?>" class="group relative flex items-center gap-3.5 rounded-2xl px-4 py-3 transition-all duration-300 <?php echo e($isActive ? 'bg-gradient-to-r from-emerald-500/20 via-emerald-500/10 to-transparent text-white border border-emerald-500/15' : 'text-white/50 hover:bg-white/[0.04] hover:text-white/80 border border-transparent'); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isActive): ?><span class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.5)]"></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <span class="grid size-10 shrink-0 place-items-center rounded-xl <?php echo e($isActive ? 'bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/20' : 'bg-white/[0.03] text-white/35'); ?>"><?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'map','size' => 20]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'map','size' => 20]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?></span>
                        <span class="text-[13px] font-medium truncate flex-1">Peta</span>
                    </a>
                </div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </nav>
        <div class="border-t border-white/[0.06] p-3">
            <div class="flex items-center gap-3 rounded-2xl bg-white/[0.03] border border-white/[0.06] p-3">
                <div class="relative shrink-0">
                    <div class="grid size-10 place-items-center rounded-xl bg-gradient-to-br from-emerald-400 to-teal-500 text-[14px] font-bold text-white"><?php echo e(substr($user->name, 0, 1)); ?></div>
                    <div class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-[2.5px] border-[#06291F] bg-emerald-400"></div>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[13px] font-semibold text-white/90 truncate"><?php echo e($user->name); ?></p>
                    <p class="text-[10px] text-emerald-400/40 truncate"><?php echo e($roleName); ?></p>
                </div>
            </div>
        </div>
    </aside>
</div>


<aside
    x-data="{ profileOpen: false, searchQuery: '', openSections: {} }"
    x-on:click.away="profileOpen = false"
    x-bind:class="$store.sidebar.collapsed ? 'w-[80px]' : 'w-[300px]'"
    class="hidden lg:flex relative min-h-screen flex-col border-r border-white/[0.06] text-white transition-all duration-300"
    style="background: linear-gradient(180deg, #0B3A2A 0%, #06291F 40%, #041B14 100%); color: #fff;"
>
    <div class="pointer-events-none absolute inset-0 opacity-[0.015]" style="background-image: url(&quot;data:image/svg+xml,%3Csvg viewBox='0 0 512 512' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.7' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='1'/%3E%3C/svg%3E&quot;); background-size: 256px;"></div>

    
    <div class="relative z-10 px-6 pt-6 pb-4" x-bind:class="$store.sidebar.collapsed ? 'px-3 pt-5 pb-3' : ''">
        <div class="flex items-center gap-3" x-bind:class="$store.sidebar.collapsed ? 'justify-center' : ''">
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="flex items-center gap-3 shrink-0">
                <img src="<?php echo e(asset('assets/images/logo_kota_palu.png')); ?>" alt="Logo" class="h-11 w-auto shrink-0" style="filter: drop-shadow(0 4px 12px rgba(16,185,129,0.2));">
                <div x-show="!$store.sidebar.collapsed" x-transition:enter="transition ease-out duration-200 delay-75" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="min-w-0">
                    <p class="text-[15px] font-bold text-white tracking-tight leading-tight">DLH Kota Palu</p>
                    <p class="text-[10px] text-emerald-400/50 tracking-widest uppercase mt-0.5">Ruang Kendali Admin</p>
                </div>
            </a>
        </div>
        <div x-show="!$store.sidebar.collapsed" x-transition class="mt-4">
            <div class="flex items-center gap-2 rounded-full border border-emerald-500/10 bg-emerald-500/[0.06] px-3 py-1.5">
                <span class="relative flex h-2 w-2"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400"></span></span>
                <span class="text-[10px] font-semibold text-emerald-400/70 tracking-wide uppercase">System Online</span>
            </div>
        </div>
    </div>

    

    
    <nav class="sidebar-nav relative z-10 flex-1 overflow-y-auto py-3 space-y-1" x-bind:class="$store.sidebar.collapsed ? 'px-2.5' : 'px-3.5'">

        
        <a href="<?php echo e(route('admin.dashboard')); ?>" class="group relative flex items-center gap-3.5 rounded-2xl transition-all duration-300 <?php echo e($isDashboard ? 'bg-gradient-to-r from-emerald-500/20 via-emerald-500/10 to-transparent text-white shadow-[0_0_20px_rgba(16,185,129,0.1)] border border-emerald-500/15' : 'text-white/50 hover:bg-white/[0.05] hover:text-white/90 border border-transparent hover:border-white/[0.05]'); ?>" x-bind:class="$store.sidebar.collapsed ? 'justify-center px-0 py-2.5' : 'px-3.5 py-2.5'" title="Dashboard">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isDashboard): ?><span x-show="!$store.sidebar.collapsed" class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.5)]"></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <span class="grid size-10 shrink-0 place-items-center rounded-xl transition-all duration-300 <?php echo e($isDashboard ? 'bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/20' : 'bg-white/[0.03] text-white/35 group-hover:bg-white/[0.07] group-hover:text-white/55'); ?>"><?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'dashboard','size' => 20]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'dashboard','size' => 20]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?></span>
            <span x-show="!$store.sidebar.collapsed" x-transition class="text-[13px] font-medium truncate flex-1">Dashboard</span>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isDashboard): ?><span x-show="!$store.sidebar.collapsed" class="size-1.5 rounded-full bg-emerald-400 shadow-[0_0_6px_rgba(52,211,153,0.5)]"></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </a>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupKey => $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="pt-3" x-bind:class="$store.sidebar.collapsed ? 'pt-2' : ''">
                <div x-show="!$store.sidebar.collapsed" x-transition class="flex items-center gap-3 px-2 mb-2.5 cursor-pointer select-none" x-on:click="openSections['<?php echo e($groupKey); ?>'] = openSections['<?php echo e($groupKey); ?>'] === false ? true : false">
                    <div class="h-px flex-1 bg-gradient-to-r from-white/[0.08] to-transparent"></div>
                    <p class="text-[9px] font-bold uppercase tracking-[0.2em] text-white/30 shrink-0"><?php echo e($group['label']); ?></p>
                    <div class="h-px flex-1 bg-gradient-to-l from-white/[0.08] to-transparent"></div>
                    <svg class="size-3 text-white/20 transition-transform duration-300 shrink-0" x-bind:class="openSections['<?php echo e($groupKey); ?>'] === false ? '-rotate-90' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
                </div>
                <div x-show="openSections['<?php echo e($groupKey); ?>'] !== false" x-collapse class="space-y-0.5">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $group['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php $url = route('admin.resources.index', ['resource' => $item['slug']]); $isActive = $activeResource === $item['slug']; ?>
                        <a href="<?php echo e($url); ?>" class="group relative flex items-center gap-3.5 rounded-2xl transition-all duration-300 <?php echo e($isActive ? 'bg-gradient-to-r from-emerald-500/20 via-emerald-500/10 to-transparent text-white shadow-[0_0_20px_rgba(16,185,129,0.1)] border border-emerald-500/15' : 'text-white/50 hover:bg-white/[0.05] hover:text-white/90 border border-transparent hover:border-white/[0.05]'); ?>" x-bind:class="$store.sidebar.collapsed ? 'justify-center px-0 py-2.5' : 'px-3.5 py-2.5'" title="<?php echo e($item['label']); ?>">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isActive): ?><span x-show="!$store.sidebar.collapsed" class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.5)]"></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <span class="grid size-10 shrink-0 place-items-center rounded-xl transition-all duration-300 <?php echo e($isActive ? 'bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/20' : 'bg-white/[0.03] text-white/35 group-hover:bg-white/[0.07] group-hover:text-white/55'); ?>"><?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => $iconMap[$item['slug']] ?? 'folder','size' => 20]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconMap[$item['slug']] ?? 'folder'),'size' => 20]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?></span>
                            <span x-show="!$store.sidebar.collapsed" x-transition class="text-[13px] font-medium truncate flex-1"><?php echo e($item['label']); ?></span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isActive): ?><span x-show="!$store.sidebar.collapsed" class="size-1.5 rounded-full bg-emerald-400 shadow-[0_0_6px_rgba(52,211,153,0.5)]"></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($groupKey === 'tata-penataan'): ?>
                        <?php
                            $standaloneLinks = [
                                ['label' => 'Kalender Sidak', 'url' => route('admin.kalender-sidak.index'), 'icon' => 'calendar', 'route' => 'admin.kalender-sidak.*'],
                                ['label' => 'Kalender Sosialisasi', 'url' => route('admin.kalender-sosialisasi.index'), 'icon' => 'presentation', 'route' => 'admin.kalender-sosialisasi.*'],
                                ['label' => 'Monitoring Sanksi', 'url' => route('admin.monitoring-sanksi.index'), 'icon' => 'shield', 'route' => 'admin.monitoring-sanksi.*'],
                                ['label' => 'Laporan & Statistik', 'url' => route('admin.laporan-tata-penataan.index'), 'icon' => 'bar-chart', 'route' => 'admin.laporan-tata-penataan.*'],
                                ['label' => 'Laporan Sosialisasi', 'url' => route('admin.laporan-sosialisasi.index'), 'icon' => 'file-text', 'route' => 'admin.laporan-sosialisasi.*'],
                                ['label' => 'Laporan Ketaatan', 'url' => route('admin.laporan-ketaatan.index'), 'icon' => 'check-circle', 'route' => 'admin.laporan-ketaatan.*'],
                            ];
                        ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $standaloneLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php $isActive = request()->routeIs($link['route']); ?>
                            <a href="<?php echo e($link['url']); ?>" class="group relative flex items-center gap-3.5 rounded-2xl transition-all duration-300 <?php echo e($isActive ? 'bg-gradient-to-r from-emerald-500/20 via-emerald-500/10 to-transparent text-white shadow-[0_0_20px_rgba(16,185,129,0.1)] border border-emerald-500/15' : 'text-white/50 hover:bg-white/[0.05] hover:text-white/90 border border-transparent hover:border-white/[0.05]'); ?>" x-bind:class="$store.sidebar.collapsed ? 'justify-center px-0 py-2.5' : 'px-3.5 py-2.5'" title="<?php echo e($link['label']); ?>">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isActive): ?><span x-show="!$store.sidebar.collapsed" class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.5)]"></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <span class="grid size-10 shrink-0 place-items-center rounded-xl transition-all duration-300 <?php echo e($isActive ? 'bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/20' : 'bg-white/[0.03] text-white/35 group-hover:bg-white/[0.07] group-hover:text-white/55'); ?>"><?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => $link['icon'],'size' => 20]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($link['icon']),'size' => 20]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?></span>
                                <span x-show="!$store.sidebar.collapsed" x-transition class="text-[13px] font-medium truncate flex-1"><?php echo e($link['label']); ?></span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isActive): ?><span x-show="!$store.sidebar.collapsed" class="size-1.5 rounded-full bg-emerald-400 shadow-[0_0_6px_rgba(52,211,153,0.5)]"></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </a>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasPetaAccess): ?>
        <div class="pt-3" x-bind:class="$store.sidebar.collapsed ? 'pt-2' : ''">
            <?php $isActive = request()->routeIs('admin.peta.*'); ?>
            <a href="<?php echo e(route('admin.peta.index')); ?>" class="group relative flex items-center gap-3.5 rounded-2xl transition-all duration-300 <?php echo e($isActive ? 'bg-gradient-to-r from-emerald-500/20 via-emerald-500/10 to-transparent text-white shadow-[0_0_20px_rgba(16,185,129,0.1)] border border-emerald-500/15' : 'text-white/50 hover:bg-white/[0.05] hover:text-white/90 border border-transparent hover:border-white/[0.05]'); ?>" x-bind:class="$store.sidebar.collapsed ? 'justify-center px-0 py-2.5' : 'px-3.5 py-2.5'" title="Peta">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isActive): ?><span x-show="!$store.sidebar.collapsed" class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.5)]"></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <span class="grid size-10 shrink-0 place-items-center rounded-xl transition-all duration-300 <?php echo e($isActive ? 'bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/20' : 'bg-white/[0.03] text-white/35 group-hover:bg-white/[0.07] group-hover:text-white/55'); ?>"><?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'map','size' => 20]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'map','size' => 20]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?></span>
                <span x-show="!$store.sidebar.collapsed" x-transition class="text-[13px] font-medium truncate flex-1">Peta</span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isActive): ?><span x-show="!$store.sidebar.collapsed" class="size-1.5 rounded-full bg-emerald-400 shadow-[0_0_6px_rgba(52,211,153,0.5)]"></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </a>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lockedGroups->isNotEmpty()): ?>
            <div class="pt-4" x-show="!$store.sidebar.collapsed">
                <div class="flex items-center gap-3 px-2 mb-2.5"><div class="h-px flex-1 bg-white/[0.06]"></div><p class="text-[9px] font-bold uppercase tracking-[0.2em] text-white/20 shrink-0">Terkunci</p><div class="h-px flex-1 bg-white/[0.06]"></div></div>
                <div class="space-y-1 opacity-25">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $lockedGroups->take(2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = array_slice($group['items'], 0, 2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="flex items-center gap-3.5 rounded-2xl px-4 py-3 text-white/30 cursor-not-allowed border border-transparent">
                                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-white/[0.03]"><?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'lock','size' => 18]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'lock','size' => 18]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?></span>
                                <span class="text-[13px] font-medium truncate"><?php echo e($item['label']); ?></span>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </nav>

    
    <button x-on:click="$store.sidebar.toggle()" style="top: 55%;" class="absolute right-2 z-30 size-6 rounded-full border border-white/10 bg-[#06291F] text-white/40 shadow-lg shadow-black/30 backdrop-blur-sm transition-all duration-300 hover:bg-emerald-600 hover:text-white hover:border-emerald-500/50 hover:shadow-emerald-500/30 hover:shadow-xl hover:scale-110 active:scale-90 flex items-center justify-center" x-bind:title="$store.sidebar.collapsed ? 'Perlebar' : 'Ciutkan'">
        <svg class="size-2.5 transition-transform duration-300" x-bind:class="$store.sidebar.collapsed ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
    </button>

    
    <div class="relative z-10 p-3" x-bind:class="$store.sidebar.collapsed ? 'p-2' : 'p-3'">
        <div class="relative rounded-2xl border border-white/[0.06] transition-all duration-300" x-bind:class="$store.sidebar.collapsed ? 'bg-white/[0.02]' : 'bg-white/[0.03] backdrop-blur-sm'">
            <button x-on:click="profileOpen = !profileOpen" class="flex w-full items-center gap-3 rounded-2xl p-3 transition-all duration-300 hover:bg-white/[0.04]" x-bind:class="$store.sidebar.collapsed ? 'justify-center' : ''">
                <div class="relative shrink-0">
                    <div class="grid size-10 place-items-center rounded-xl bg-gradient-to-br from-emerald-400 to-teal-500 text-[14px] font-bold text-white shadow-lg shadow-emerald-500/20"><?php echo e(substr($user->name, 0, 1)); ?></div>
                    <div class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-[2.5px] border-[#06291F] bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.5)]"></div>
                </div>
                <div x-show="!$store.sidebar.collapsed" x-transition class="min-w-0 flex-1 text-left">
                    <p class="text-[13px] font-semibold text-white/90 truncate"><?php echo e($user->name); ?></p>
                    <p class="text-[10px] text-emerald-400/40 truncate"><?php echo e($roleName); ?></p>
                </div>
                <svg x-show="!$store.sidebar.collapsed" class="size-4 shrink-0 text-white/25 transition-transform duration-200" x-bind:class="profileOpen ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 15l-6-6-6 6"/></svg>
            </button>
            <div x-show="profileOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute bottom-full left-3 right-3 mb-2 overflow-hidden rounded-2xl border border-white/[0.08] bg-[#0B3A2A]/95 backdrop-blur-xl shadow-2xl shadow-black/40" style="display: none;">
                <div class="px-4 py-3.5 border-b border-white/[0.06]">
                    <p class="text-[13px] font-bold text-white"><?php echo e($user->name); ?></p>
                    <p class="text-[11px] text-emerald-400/40 mt-0.5"><?php echo e($user->email); ?></p>
                </div>
                <div class="p-1.5">
                    <a href="<?php echo e(route('admin.profile.edit')); ?>" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium text-white/50 transition-all hover:bg-white/[0.06] hover:text-white/90"><span class="grid size-8 place-items-center rounded-lg bg-emerald-500/10 text-emerald-400/70"><?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'user','size' => 16]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'user','size' => 16]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?></span> Profil Saya</a>
                    <a href="<?php echo e(route('admin.settings.edit')); ?>" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium text-white/50 transition-all hover:bg-white/[0.06] hover:text-white/90"><span class="grid size-8 place-items-center rounded-lg bg-white/[0.04] text-white/35"><?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'settings','size' => 16]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'settings','size' => 16]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?></span> Pengaturan</a>
                    <a href="<?php echo e(route('admin.help.index')); ?>" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium text-white/50 transition-all hover:bg-white/[0.06] hover:text-white/90"><span class="grid size-8 place-items-center rounded-lg bg-white/[0.04] text-white/35"><?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'info-circle','size' => 16]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'info-circle','size' => 16]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?></span> Bantuan</a>
                </div>
                <div class="border-t border-white/[0.06] p-1.5">
                    <form method="POST" action="<?php echo e(route('admin.logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium text-red-400/60 transition-all hover:bg-red-500/10 hover:text-red-400"><span class="grid size-8 place-items-center rounded-lg bg-red-500/[0.08] text-red-400/60"><?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'logout','size' => 16]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'logout','size' => 16]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?></span> Keluar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</aside>

<style>
/* Hide all scrollbars in sidebar */
aside[style*="color: #fff"] { overflow-x: hidden; }
aside[style*="color: #fff"] .sidebar-nav { overflow-y: auto; overflow-x: hidden; -ms-overflow-style: none; scrollbar-width: none; }
aside[style*="color: #fff"] .sidebar-nav::-webkit-scrollbar { display: none; }

/* Fix tooltip position */
aside[style*="color: #fff"] .sidebar-nav a[title]:hover::after {
    content: attr(title);
    position: fixed;
    left: 80px;
    background: linear-gradient(135deg, #0B3A2A, #06291F);
    border: 1px solid rgba(255,255,255,0.1);
    color: white;
    padding: 8px 14px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
    z-index: 100;
    box-shadow: 0 8px 32px rgba(0,0,0,0.5);
    pointer-events: none;
}

/* Active icon glow */
aside[style*="color: #fff"] .sidebar-nav a .grid.rounded-xl.bg-gradient-to-br {
    box-shadow: 0 0 16px rgba(16,185,129,0.35), 0 4px 12px rgba(16,185,129,0.2);
}
</style>
<?php /**PATH D:\Backup\DLH - Palu\resources\views/components/admin/sidebar.blade.php ENDPATH**/ ?>