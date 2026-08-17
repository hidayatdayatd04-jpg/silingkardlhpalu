<?php $__env->startSection('title', 'Dashboard Admin DLH Kota Palu'); ?>
<?php $__env->startSection('heading', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $hour = (int) now()->format('H');
        $greet = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 19 ? 'Selamat sore' : 'Selamat malam'));
        $user = auth()->user();
        $firstName = \Illuminate\Support\Str::of($user?->name ?? 'Admin')->explode(' ')->first();
        $initials = collect(explode(' ', $user?->name ?? 'A'))
            ->map(fn ($w) => mb_substr($w, 0, 1))
            ->take(2)
            ->join('');
        $roleName = $user?->roles?->first()?->name;
        $roleLabel = \App\Enums\AdminRole::tryFrom($roleName)?->label() ?? 'Admin';
    ?>

    
    <section class="relative overflow-hidden rounded-2xl border border-forest-800/40 text-white shadow-[var(--shadow-lift)]" style="background: var(--gradient-header-hero);">
        <div class="bg-grain pointer-events-none absolute inset-0 opacity-[0.05]"></div>
        <div class="pointer-events-none absolute -right-16 -top-20 size-64 rounded-full bg-brand-400/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 right-24 size-56 rounded-full bg-bay-400/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -left-10 top-1/2 size-48 rounded-full bg-emerald-500/10 blur-3xl"></div>

        <div class="relative flex flex-col gap-6 p-7 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <p class="text-sm font-semibold uppercase tracking-[0.14em] text-brand-200"><?php echo e($roleLabel); ?></p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                    <?php echo e($greet); ?>, <?php echo e($firstName); ?> <?php if (isset($component)) { $__componentOriginal7d82ddcfbe10f5886839d81e0b603519 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7d82ddcfbe10f5886839d81e0b603519 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.sapa','data' => ['class' => 'inline-block size-6 animate-pulse align-middle']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons.sapa'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'inline-block size-6 animate-pulse align-middle']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7d82ddcfbe10f5886839d81e0b603519)): ?>
<?php $attributes = $__attributesOriginal7d82ddcfbe10f5886839d81e0b603519; ?>
<?php unset($__attributesOriginal7d82ddcfbe10f5886839d81e0b603519); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7d82ddcfbe10f5886839d81e0b603519)): ?>
<?php $component = $__componentOriginal7d82ddcfbe10f5886839d81e0b603519; ?>
<?php unset($__componentOriginal7d82ddcfbe10f5886839d81e0b603519); ?>
<?php endif; ?>
                </h1>
                <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-white/75">
                    <span class="inline-flex items-center gap-1.5">
                        <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'calendar','size' => 16,'class' => 'text-brand-300']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'calendar','size' => 16,'class' => 'text-brand-300']); ?>
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
                        <?php echo e(\Carbon\Carbon::now()->translatedFormat('l, d F Y')); ?>

                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-2.5 py-1 font-mono text-xs font-semibold text-brand-100 backdrop-blur">
                        <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'clock','size' => 14,'class' => 'text-brand-300']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'clock','size' => 14,'class' => 'text-brand-300']); ?>
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
                        <span x-data="{ t: '' }" x-init="const u=()=>t=new Date().toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit',second:'2-digit'});u();setInterval(u,1000)" x-text="t"></span>
                        WITA
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-3 rounded-2xl bg-white/10 p-3 pr-5 backdrop-blur">
                <div class="grid size-14 shrink-0 place-items-center rounded-xl bg-white/15 text-base font-bold tracking-tight text-white">
                    <?php echo e(strtoupper($initials)); ?>

                </div>
                <div class="min-w-0">
                    <p class="truncate text-sm font-bold text-white"><?php echo e($user?->name ?? 'Admin'); ?></p>
                    <p class="truncate text-xs text-white/60"><?php echo e($user?->email); ?></p>
                </div>
            </div>
        </div>
    </section>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($summary): ?>
    <section class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <?php if (isset($component)) { $__componentOriginal5602281812c7dd97256c959080bb4e5d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5602281812c7dd97256c959080bb4e5d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-strip','data' => ['label' => 'Pengunjung Hari Ini','value' => $summary['pengunjung_hari_ini'],'icon' => 'users','color' => 'bay']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-strip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Pengunjung Hari Ini','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['pengunjung_hari_ini']),'icon' => 'users','color' => 'bay']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5602281812c7dd97256c959080bb4e5d)): ?>
<?php $attributes = $__attributesOriginal5602281812c7dd97256c959080bb4e5d; ?>
<?php unset($__attributesOriginal5602281812c7dd97256c959080bb4e5d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5602281812c7dd97256c959080bb4e5d)): ?>
<?php $component = $__componentOriginal5602281812c7dd97256c959080bb4e5d; ?>
<?php unset($__componentOriginal5602281812c7dd97256c959080bb4e5d); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal5602281812c7dd97256c959080bb4e5d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5602281812c7dd97256c959080bb4e5d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-strip','data' => ['label' => 'Total Pengunjung','value' => $summary['total_pengunjung'],'icon' => 'chart','color' => 'emerald']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-strip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Total Pengunjung','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['total_pengunjung']),'icon' => 'chart','color' => 'emerald']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5602281812c7dd97256c959080bb4e5d)): ?>
<?php $attributes = $__attributesOriginal5602281812c7dd97256c959080bb4e5d; ?>
<?php unset($__attributesOriginal5602281812c7dd97256c959080bb4e5d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5602281812c7dd97256c959080bb4e5d)): ?>
<?php $component = $__componentOriginal5602281812c7dd97256c959080bb4e5d; ?>
<?php unset($__componentOriginal5602281812c7dd97256c959080bb4e5d); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal5602281812c7dd97256c959080bb4e5d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5602281812c7dd97256c959080bb4e5d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-strip','data' => ['label' => 'Total Pelapor','value' => $summary['total_pelapor'],'icon' => 'megaphone','color' => 'sky']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-strip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Total Pelapor','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['total_pelapor']),'icon' => 'megaphone','color' => 'sky']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5602281812c7dd97256c959080bb4e5d)): ?>
<?php $attributes = $__attributesOriginal5602281812c7dd97256c959080bb4e5d; ?>
<?php unset($__attributesOriginal5602281812c7dd97256c959080bb4e5d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5602281812c7dd97256c959080bb4e5d)): ?>
<?php $component = $__componentOriginal5602281812c7dd97256c959080bb4e5d; ?>
<?php unset($__componentOriginal5602281812c7dd97256c959080bb4e5d); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal5602281812c7dd97256c959080bb4e5d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5602281812c7dd97256c959080bb4e5d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-strip','data' => ['label' => 'Total Pengajuan','value' => $summary['total_pengajuan'],'icon' => 'file-plus','color' => 'amber']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-strip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Total Pengajuan','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['total_pengajuan']),'icon' => 'file-plus','color' => 'amber']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5602281812c7dd97256c959080bb4e5d)): ?>
<?php $attributes = $__attributesOriginal5602281812c7dd97256c959080bb4e5d; ?>
<?php unset($__attributesOriginal5602281812c7dd97256c959080bb4e5d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5602281812c7dd97256c959080bb4e5d)): ?>
<?php $component = $__componentOriginal5602281812c7dd97256c959080bb4e5d; ?>
<?php unset($__componentOriginal5602281812c7dd97256c959080bb4e5d); ?>
<?php endif; ?>
    </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <section class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="stagger-item" style="--reveal-delay: <?php echo e($loop->index * 60); ?>ms;">
                <?php if (isset($component)) { $__componentOriginal3c3cb599308b2d9971dae437d0b6bab6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.stat-card','data' => ['label' => $card['label'],'value' => $card['value'],'icon' => $card['icon'] ?? 'folder','color' => $card['tone']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($card['label']),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($card['value']),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($card['icon'] ?? 'folder'),'color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($card['tone'])]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6)): ?>
<?php $attributes = $__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6; ?>
<?php unset($__attributesOriginal3c3cb599308b2d9971dae437d0b6bab6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c3cb599308b2d9971dae437d0b6bab6)): ?>
<?php $component = $__componentOriginal3c3cb599308b2d9971dae437d0b6bab6; ?>
<?php unset($__componentOriginal3c3cb599308b2d9971dae437d0b6bab6); ?>
<?php endif; ?>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($cards)): ?>
            <div class="md:col-span-2 xl:col-span-4">
                <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<p class="text-sm text-slate-500">Belum ada modul yang bisa ditampilkan untuk akun Anda.</p> <?php echo $__env->renderComponent(); ?>
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
    </section>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($statusStats && $statusStats['total'] > 0): ?>
    <section class="mt-8">
        <div class="mb-4 flex items-center gap-3">
            <span class="grid size-9 place-items-center rounded-xl bg-brand-50 text-brand-600">
                <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'clipboard-check','size' => 18]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'clipboard-check','size' => 18]); ?>
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
            </span>
            <div>
                <h2 class="text-h3 font-bold text-ink-900">Ringkasan Kinerja Penanganan</h2>
                <p class="text-xs text-slate-500">Tingkat penyelesaian pengaduan secara keseluruhan</p>
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

            <div class="grid gap-6 p-6 md:grid-cols-3">
                
                <div class="md:col-span-2">
                    <div class="flex items-end justify-between">
                        <div>
                            <p class="text-sm font-semibold text-ink-500">Tingkat Penyelesaian</p>
                            <p class="mt-1 text-2xl font-bold tracking-tight text-ink-900"><?php echo e($statusStats['selesai_pct']); ?><span class="text-xl text-ink-400">%</span></p>
                        </div>
                        <p class="text-xs text-slate-500"><?php echo e(number_format($statusStats['selesai'], 0, ',', '.')); ?> dari <?php echo e(number_format($statusStats['total'], 0, ',', '.')); ?> pengaduan selesai</p>
                    </div>
                    <div class="mt-4 h-3 w-full overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-emerald-400 transition-all duration-700" style="width: <?php echo e($statusStats['selesai_pct']); ?>%;"></div>
                    </div>

                    <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                            <p class="text-lg font-bold text-amber-600"><?php echo e(number_format($statusStats['belum'], 0, ',', '.')); ?></p>
                            <p class="mt-0.5 text-xs font-medium text-slate-500">Belum Ditindak</p>
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                            <p class="text-lg font-bold text-sky-600"><?php echo e(number_format($statusStats['proses'], 0, ',', '.')); ?></p>
                            <p class="mt-0.5 text-xs font-medium text-slate-500">Diproses</p>
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                            <p class="text-lg font-bold text-emerald-600"><?php echo e(number_format($statusStats['selesai'], 0, ',', '.')); ?></p>
                            <p class="mt-0.5 text-xs font-medium text-slate-500">Selesai</p>
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                            <p class="text-lg font-bold text-rose-600"><?php echo e(number_format($statusStats['ditolak'], 0, ',', '.')); ?></p>
                            <p class="mt-0.5 text-xs font-medium text-slate-500">Ditolak</p>
                        </div>
                    </div>
                </div>

                
                <div class="relative">
                    <div class="relative mx-auto h-44 w-44">
                        <canvas id="chartStatusMini"></canvas>
                        <div class="pointer-events-none absolute inset-0 grid place-items-center">
                            <div class="text-center">
                                <p class="text-xl font-bold text-ink-900"><?php echo e(number_format($statusStats['total'], 0, ',', '.')); ?></p>
                                <p class="text-[11px] font-medium text-slate-500">Total</p>
                            </div>
                        </div>
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
    </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <section class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-12">
        
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'lg:col-span-8']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'lg:col-span-8']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h2 class="text-h4 font-bold text-ink-900">Tren Pengaduan 6 Bulan</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Jumlah pengaduan masuk per bulan</p>
                </div>
                <span class="grid size-10 place-items-center rounded-xl bg-brand-50 text-brand-600">
                    <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'trending-up','size' => 20]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'trending-up','size' => 20]); ?>
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
                </span>
            </div>
            <div class="relative h-64">
                <canvas id="chartTrend"></canvas>
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

        
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'lg:col-span-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'lg:col-span-4']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <div class="mb-5">
                <h2 class="text-h4 font-bold text-ink-900">Distribusi Status</h2>
                <p class="mt-0.5 text-xs text-slate-500">Status pengaduan saat ini</p>
            </div>
            <div class="relative h-64">
                <canvas id="chartStatus"></canvas>
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

        
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'lg:col-span-8']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'lg:col-span-8']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <div class="mb-5">
                <h2 class="text-h4 font-bold text-ink-900">Jumlah Data per Modul</h2>
                <p class="mt-0.5 text-xs text-slate-500">Total data pada tiap modul yang Anda akses</p>
            </div>
            <div class="relative h-72">
                <canvas id="chartModules"></canvas>
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

        
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'lg:col-span-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'lg:col-span-4']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <div class="mb-5">
                <h2 class="text-h4 font-bold text-ink-900">Performa Penanganan</h2>
                <p class="mt-0.5 text-xs text-slate-500">Tugas selesai vs tertunda</p>
            </div>
            <div class="relative h-72">
                <canvas id="chartPerformance"></canvas>
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

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($charts['trendPerBidang']['datasets'] ?? []) > 1): ?>
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'lg:col-span-12']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'lg:col-span-12']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <div class="mb-5">
                <h2 class="text-h4 font-bold text-ink-900">Tren per Bidang</h2>
                <p class="mt-0.5 text-xs text-slate-500">Perbandingan pengaduan antar bidang</p>
            </div>
            <div class="relative w-full" style="height: 340px;">
                <canvas id="chartTrendBidang"></canvas>
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
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </section>

    
    <section class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-12">
        <?php echo $__env->make('admin.partials.sebaran-pengaduan', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        
        <div class="space-y-6 lg:col-span-4">
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeUsers !== null || $visits !== null): ?>
                <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['padding' => false,'class' => 'overflow-hidden text-white','style' => 'background: var(--gradient-header-hero);']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'class' => 'overflow-hidden text-white','style' => 'background: var(--gradient-header-hero);']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <div class="relative p-6">
                        <div class="bg-grain pointer-events-none absolute inset-0 opacity-[0.05]"></div>
                        <div class="relative flex items-center gap-3">
                            <div class="grid size-12 place-items-center rounded-xl bg-white/10">
                                <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'chart','size' => 24]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chart','size' => 24]); ?>
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
                            </div>
                            <div>
                                <p class="text-sm font-bold text-brand-200">Status Sistem</p>
                                <p class="text-xs text-white/60">Ringkasan</p>
                            </div>
                        </div>
                        <div class="relative mt-6 grid grid-cols-2 gap-3">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeUsers !== null): ?>
                                <div class="rounded-lg bg-white/10 p-4 backdrop-blur">
                                    <p class="text-h1 font-bold"><?php if (isset($component)) { $__componentOriginald45cedd3e5403692fb01ca257c578ca2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald45cedd3e5403692fb01ca257c578ca2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.count-up','data' => ['value' => (int) $activeUsers]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.count-up'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((int) $activeUsers)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald45cedd3e5403692fb01ca257c578ca2)): ?>
<?php $attributes = $__attributesOriginald45cedd3e5403692fb01ca257c578ca2; ?>
<?php unset($__attributesOriginald45cedd3e5403692fb01ca257c578ca2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald45cedd3e5403692fb01ca257c578ca2)): ?>
<?php $component = $__componentOriginald45cedd3e5403692fb01ca257c578ca2; ?>
<?php unset($__componentOriginald45cedd3e5403692fb01ca257c578ca2); ?>
<?php endif; ?></p>
                                    <p class="mt-1 text-xs text-brand-200">Admin Aktif</p>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($visits !== null): ?>
                                <div class="rounded-lg bg-white/10 p-4 backdrop-blur">
                                    <p class="text-h1 font-bold"><?php if (isset($component)) { $__componentOriginald45cedd3e5403692fb01ca257c578ca2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald45cedd3e5403692fb01ca257c578ca2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.count-up','data' => ['value' => (int) $visits]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.count-up'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((int) $visits)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald45cedd3e5403692fb01ca257c578ca2)): ?>
<?php $attributes = $__attributesOriginald45cedd3e5403692fb01ca257c578ca2; ?>
<?php unset($__attributesOriginald45cedd3e5403692fb01ca257c578ca2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald45cedd3e5403692fb01ca257c578ca2)): ?>
<?php $component = $__componentOriginald45cedd3e5403692fb01ca257c578ca2; ?>
<?php unset($__componentOriginald45cedd3e5403692fb01ca257c578ca2); ?>
<?php endif; ?></p>
                                    <p class="mt-1 text-xs text-brand-200">Kunjungan</p>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendingTasks['total'] > 0): ?>
                <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['padding' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                        <div>
                            <h2 class="text-h4 font-bold text-ink-900">Perlu Tindakan</h2>
                            <p class="text-xs text-slate-500">Antrean verifikasi & penanganan</p>
                        </div>
                        <span class="grid size-9 place-items-center rounded-full bg-warning-100 text-warning-700">
                            <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'alert-triangle','size' => 18]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'alert-triangle','size' => 18]); ?>
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
                        </span>
                    </div>
                    <div class="divide-y divide-slate-100">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $pendingTasks['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <a href="<?php echo e($task['href']); ?>" class="flex items-center justify-between gap-3 px-5 py-3 transition hover:bg-slate-50">
                                <span class="min-w-0 flex-1 truncate text-sm font-medium text-ink-700"><?php echo e($task['label']); ?></span>
                                <span class="shrink-0 rounded-full bg-warning-100 px-2.5 py-0.5 text-xs font-bold text-warning-700"><?php echo e(number_format($task['count'], 0, ',', '.')); ?></span>
                            </a>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
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
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['padding' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-h4 font-bold text-ink-900">Tautan Cepat</h2>
                    <p class="text-xs text-slate-500">Akses cepat ke modul Anda</p>
                </div>
                <div class="p-3">
                    <div class="grid grid-cols-2 gap-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupKey => $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = array_slice($group['items'], 0, 2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <a href="<?php echo e(route('admin.resources.index', $item['slug'])); ?>" class="flex items-center gap-2 rounded-xl border border-slate-100 bg-slate-50/50 px-3 py-2.5 text-xs font-semibold text-slate-700 transition hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200">
                                    <span class="size-1.5 rounded-full bg-emerald-400"></span>
                                    <?php echo e(\Illuminate\Support\Str::limit($item['label'], 20)); ?>

                                </a>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
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

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activityFeed->isNotEmpty()): ?>
                <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['padding' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <div class="border-b border-slate-100 px-5 py-4">
                        <h2 class="text-h4 font-bold text-ink-900">Aktivitas Terbaru</h2>
                        <p class="text-xs text-slate-500">Log tindakan pengguna</p>
                    </div>
                    <div class="max-h-80 divide-y divide-slate-100 overflow-y-auto">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $activityFeed; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php $meta = $log->eventMeta(); ?>
                            <div class="flex items-start gap-3 px-5 py-3">
                                <div class="grid size-8 shrink-0 place-items-center rounded-lg bg-<?php echo e($meta['variant'] === 'default' ? 'slate' : $meta['variant']); ?>-100 text-<?php echo e($meta['variant'] === 'default' ? 'slate' : $meta['variant']); ?>-600">
                                    <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => $meta['icon'],'size' => 15]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($meta['icon']),'size' => 15]); ?>
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
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-ink-800"><?php echo e($log->subject_label); ?></p>
                                    <p class="text-xs text-slate-500"><?php echo e($log->user_name); ?> • <?php echo e($log->created_at?->diffForHumans()); ?></p>
                                </div>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                    <a href="<?php echo e(route('admin.activity-log.index')); ?>" class="block border-t border-slate-100 px-5 py-3 text-center text-xs font-bold text-brand-600 hover:bg-slate-50">Lihat semua log</a>
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
            <?php else: ?>
                <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['padding' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <div class="flex items-center gap-3 px-5 py-4">
                        <div class="grid size-10 place-items-center rounded-xl bg-slate-100 text-slate-400">
                            <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'check-circle','size' => 20]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check-circle','size' => 20]); ?>
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
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-ink-700">Semua Terkini</p>
                            <p class="text-xs text-slate-500">Tidak ada aktivitas terbaru</p>
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
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </section>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($recent)): ?>
    <section x-data="{ activeTab: '<?php echo e(array_key_first($recent)); ?>' }" class="mt-8 space-y-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-h3 font-bold text-ink-900">Data Terbaru</h2>
                <p class="text-sm text-slate-500">Catatan terbaru pada modul yang Anda akses</p>
            </div>
            <div class="flex flex-wrap rounded-xl bg-slate-100 p-1">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($recent['laporan'])): ?>
                    <button x-on:click="activeTab = 'laporan'" :class="activeTab === 'laporan' ? 'bg-white shadow-sm text-brand-700' : 'text-slate-500 hover:text-slate-700'" class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-bold transition">
                        <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'megaphone','size' => 14]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'megaphone','size' => 14]); ?>
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
                        Pengaduan
                        <span class="rounded-full bg-slate-200 px-1.5 py-0.5 text-[10px] font-bold text-slate-600"><?php echo e($recent['laporan']->count()); ?></span>
                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($recent['permohonan'])): ?>
                    <button x-on:click="activeTab = 'permohonan'" :class="activeTab === 'permohonan' ? 'bg-white shadow-sm text-brand-700' : 'text-slate-500 hover:text-slate-700'" class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-bold transition">
                        <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'clipboard-check','size' => 14]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'clipboard-check','size' => 14]); ?>
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
                        Rekomendasi
                        <span class="rounded-full bg-slate-200 px-1.5 py-0.5 text-[10px] font-bold text-slate-600"><?php echo e($recent['permohonan']->count()); ?></span>
                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($recent['registrasi_lb3'])): ?>
                    <button x-on:click="activeTab = 'registrasi_lb3'" :class="activeTab === 'registrasi_lb3' ? 'bg-white shadow-sm text-brand-700' : 'text-slate-500 hover:text-slate-700'" class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-bold transition">
                        <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'building','size' => 14]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'building','size' => 14]); ?>
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
                        LB3
                        <span class="rounded-full bg-slate-200 px-1.5 py-0.5 text-[10px] font-bold text-slate-600"><?php echo e($recent['registrasi_lb3']->count()); ?></span>
                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($recent['rintek_pertek'])): ?>
                    <button x-on:click="activeTab = 'rintek_pertek'" :class="activeTab === 'rintek_pertek' ? 'bg-white shadow-sm text-brand-700' : 'text-slate-500 hover:text-slate-700'" class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-bold transition">
                        <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'factory','size' => 14]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'factory','size' => 14]); ?>
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
                        RINTEK/PERTEK
                        <span class="rounded-full bg-slate-200 px-1.5 py-0.5 text-[10px] font-bold text-slate-600"><?php echo e($recent['rintek_pertek']->count()); ?></span>
                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($recent['artikel'])): ?>
                    <button x-on:click="activeTab = 'artikel'" :class="activeTab === 'artikel' ? 'bg-white shadow-sm text-brand-700' : 'text-slate-500 hover:text-slate-700'" class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-bold transition">
                        <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'news','size' => 14]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'news','size' => 14]); ?>
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
                        Artikel
                        <span class="rounded-full bg-slate-200 px-1.5 py-0.5 text-[10px] font-bold text-slate-600"><?php echo e($recent['artikel']->count()); ?></span>
                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($recent['laporan'])): ?>
            <div x-show="activeTab === 'laporan'" class="divide-y divide-slate-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recent['laporan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginalcdcde7774b77f71cb3a332fe4d2d9bae = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcdcde7774b77f71cb3a332fe4d2d9bae = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.recent-item','data' => ['icon' => 'megaphone','iconColor' => 'emerald','title' => $item->nomor_tiket,'subtitle' => 'Pengaduan — '.($item->bidang_label ?? '-').($item->jenis_pengaduan ? ' • '.$item->jenis_pengaduan : ''),'time' => $item->created_at?->diffForHumans(),'badge' => $item->status_text ?? (string) $item->status,'href' => '#']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.recent-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'megaphone','icon-color' => 'emerald','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->nomor_tiket),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Pengaduan — '.($item->bidang_label ?? '-').($item->jenis_pengaduan ? ' • '.$item->jenis_pengaduan : '')),'time' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->created_at?->diffForHumans()),'badge' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->status_text ?? (string) $item->status),'href' => '#']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcdcde7774b77f71cb3a332fe4d2d9bae)): ?>
<?php $attributes = $__attributesOriginalcdcde7774b77f71cb3a332fe4d2d9bae; ?>
<?php unset($__attributesOriginalcdcde7774b77f71cb3a332fe4d2d9bae); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcdcde7774b77f71cb3a332fe4d2d9bae)): ?>
<?php $component = $__componentOriginalcdcde7774b77f71cb3a332fe4d2d9bae; ?>
<?php unset($__componentOriginalcdcde7774b77f71cb3a332fe4d2d9bae); ?>
<?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <p class="px-5 py-8 text-center text-sm text-slate-400">Belum ada laporan terbaru</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($recent['permohonan'])): ?>
            <div x-show="activeTab === 'permohonan'" class="divide-y divide-slate-100" x-cloak>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recent['permohonan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginalcdcde7774b77f71cb3a332fe4d2d9bae = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcdcde7774b77f71cb3a332fe4d2d9bae = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.recent-item','data' => ['icon' => 'clipboard-check','iconColor' => 'indigo','title' => 'Rekomendasi #' . $item->id,'subtitle' => $item->nama_perusahaan ?? $item->nama_pemilik ?? 'Permohonan Rekomendasi','time' => $item->created_at?->diffForHumans(),'badge' => $item->status ?? null,'href' => '#']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.recent-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'clipboard-check','icon-color' => 'indigo','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Rekomendasi #' . $item->id),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->nama_perusahaan ?? $item->nama_pemilik ?? 'Permohonan Rekomendasi'),'time' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->created_at?->diffForHumans()),'badge' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->status ?? null),'href' => '#']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcdcde7774b77f71cb3a332fe4d2d9bae)): ?>
<?php $attributes = $__attributesOriginalcdcde7774b77f71cb3a332fe4d2d9bae; ?>
<?php unset($__attributesOriginalcdcde7774b77f71cb3a332fe4d2d9bae); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcdcde7774b77f71cb3a332fe4d2d9bae)): ?>
<?php $component = $__componentOriginalcdcde7774b77f71cb3a332fe4d2d9bae; ?>
<?php unset($__componentOriginalcdcde7774b77f71cb3a332fe4d2d9bae); ?>
<?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <p class="px-5 py-8 text-center text-sm text-slate-400">Belum ada permohonan terbaru</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($recent['registrasi_lb3'])): ?>
            <div x-show="activeTab === 'registrasi_lb3'" class="divide-y divide-slate-100" x-cloak>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recent['registrasi_lb3']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginalcdcde7774b77f71cb3a332fe4d2d9bae = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcdcde7774b77f71cb3a332fe4d2d9bae = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.recent-item','data' => ['icon' => 'building','iconColor' => 'amber','title' => $item->nama_usaha ?? ('Registrasi #' . $item->id),'subtitle' => $item->nama_pemilik ?? ('Registrasi Usaha LB3' . ($item->status ? ' • ' . ($item->status instanceof \BackedEnum ? $item->status->value : $item->status) : '')),'time' => $item->created_at?->diffForHumans(),'badge' => $item->status ?? null,'href' => '#']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.recent-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'building','icon-color' => 'amber','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->nama_usaha ?? ('Registrasi #' . $item->id)),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->nama_pemilik ?? ('Registrasi Usaha LB3' . ($item->status ? ' • ' . ($item->status instanceof \BackedEnum ? $item->status->value : $item->status) : ''))),'time' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->created_at?->diffForHumans()),'badge' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->status ?? null),'href' => '#']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcdcde7774b77f71cb3a332fe4d2d9bae)): ?>
<?php $attributes = $__attributesOriginalcdcde7774b77f71cb3a332fe4d2d9bae; ?>
<?php unset($__attributesOriginalcdcde7774b77f71cb3a332fe4d2d9bae); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcdcde7774b77f71cb3a332fe4d2d9bae)): ?>
<?php $component = $__componentOriginalcdcde7774b77f71cb3a332fe4d2d9bae; ?>
<?php unset($__componentOriginalcdcde7774b77f71cb3a332fe4d2d9bae); ?>
<?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <p class="px-5 py-8 text-center text-sm text-slate-400">Belum ada registrasi LB3 terbaru</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($recent['rintek_pertek'])): ?>
            <div x-show="activeTab === 'rintek_pertek'" class="divide-y divide-slate-100" x-cloak>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recent['rintek_pertek']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginalcdcde7774b77f71cb3a332fe4d2d9bae = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcdcde7774b77f71cb3a332fe4d2d9bae = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.recent-item','data' => ['icon' => 'factory','iconColor' => 'sky','title' => $item->nama_perusahaan ?? ('RINTEK #' . $item->id),'subtitle' => 'Pengajuan RINTEK/PERTEK','time' => $item->created_at?->diffForHumans(),'badge' => $item->status ?? null,'href' => '#']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.recent-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'factory','icon-color' => 'sky','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->nama_perusahaan ?? ('RINTEK #' . $item->id)),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Pengajuan RINTEK/PERTEK'),'time' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->created_at?->diffForHumans()),'badge' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->status ?? null),'href' => '#']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcdcde7774b77f71cb3a332fe4d2d9bae)): ?>
<?php $attributes = $__attributesOriginalcdcde7774b77f71cb3a332fe4d2d9bae; ?>
<?php unset($__attributesOriginalcdcde7774b77f71cb3a332fe4d2d9bae); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcdcde7774b77f71cb3a332fe4d2d9bae)): ?>
<?php $component = $__componentOriginalcdcde7774b77f71cb3a332fe4d2d9bae; ?>
<?php unset($__componentOriginalcdcde7774b77f71cb3a332fe4d2d9bae); ?>
<?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <p class="px-5 py-8 text-center text-sm text-slate-400">Belum ada pengajuan RINTEK/PERTEK terbaru</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($recent['artikel'])): ?>
            <div x-show="activeTab === 'artikel'" class="divide-y divide-slate-100" x-cloak>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recent['artikel']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginalcdcde7774b77f71cb3a332fe4d2d9bae = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcdcde7774b77f71cb3a332fe4d2d9bae = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.recent-item','data' => ['icon' => 'news','iconColor' => 'blue','title' => $item->judul ?? ('Artikel #' . $item->id),'subtitle' => 'Artikel','time' => $item->created_at?->diffForHumans(),'badge' => $item->status ?? null,'href' => '#']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.recent-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'news','icon-color' => 'blue','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->judul ?? ('Artikel #' . $item->id)),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Artikel'),'time' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->created_at?->diffForHumans()),'badge' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->status ?? null),'href' => '#']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcdcde7774b77f71cb3a332fe4d2d9bae)): ?>
<?php $attributes = $__attributesOriginalcdcde7774b77f71cb3a332fe4d2d9bae; ?>
<?php unset($__attributesOriginalcdcde7774b77f71cb3a332fe4d2d9bae); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcdcde7774b77f71cb3a332fe4d2d9bae)): ?>
<?php $component = $__componentOriginalcdcde7774b77f71cb3a332fe4d2d9bae; ?>
<?php unset($__componentOriginalcdcde7774b77f71cb3a332fe4d2d9bae); ?>
<?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <p class="px-5 py-8 text-center text-sm text-slate-400">Belum ada artikel terbaru</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
    </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>

<?php
    $buildManifest = is_file(public_path('build/manifest.json'))
        ? json_decode((string) file_get_contents(public_path('build/manifest.json')), true)
        : [];
    $lazyChartsJs = $buildManifest['resources/js/dashboard-charts.js']['file'] ?? null;
    $lazyFpEntry = $buildManifest['resources/js/flatpickr-init.js'] ?? null;
?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lazyChartsJs && $lazyFpEntry): ?>
<script>
    // Muat bundle chart + flatpickr secara lazy agar keluar dari critical path.
    (function () {
        var base = <?php echo json_encode(rtrim(asset('build'), '/') . '/', 512) ?>;
        var kicked = false;
        function kick() {
            if (kicked) return;
            kicked = true;
            <?php $__currentLoopData = (array) ($lazyFpEntry['css'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cssFile): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            var l = document.createElement('link');
            l.rel = 'stylesheet';
            l.href = base + <?php echo json_encode($cssFile, 15, 512) ?>;
            document.head.appendChild(l);
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            [<?php echo json_encode($lazyFpEntry['file'], 15, 512) ?>, <?php echo json_encode($lazyChartsJs, 15, 512) ?>].forEach(function (f) {
                var s = document.createElement('script');
                s.type = 'module';
                s.src = base + f;
                document.head.appendChild(s);
            });
        }
        function schedule() {
            if (window.requestIdleCallback) requestIdleCallback(kick, { timeout: 1200 });
            else setTimeout(kick, 300);
        }
        if (document.readyState === 'complete') schedule();
        else window.addEventListener('load', schedule, { once: true });
    })();
</script>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<script>
    // Muat chunk peta secara lazy agar keluar dari critical path dashboard.
    // Partial sebaran-pengaduan mem-poll window.maplibregl tiap 80ms, sehingga
    // peta tetap ter-init begitu dynamic import di bawah selesai.
    (function () {
        var kick = function () { if (window.ensureMapComponents) window.ensureMapComponents(); };
        var schedule = function () {
            if (window.requestIdleCallback) requestIdleCallback(kick, { timeout: 1500 });
            else setTimeout(kick, 250);
        };
        if (document.readyState === 'complete') schedule();
        else window.addEventListener('load', schedule, { once: true });
    })();
</script>
<script>
    (function () {
        const charts = <?php echo json_encode($charts, 15, 512) ?>;
        const emerald = '#059669', teal = '#0d9488', sky = '#0284c7', amber = '#d97706', rose = '#e11d48', indigo = '#4f46e5', purple = '#7c3aed', slate = '#64748b';
        const palette = [emerald, sky, amber, teal, indigo, rose, purple, slate];
        const bidangColors = { pengendalian: emerald, sampah: sky, rth: teal, 'tata-penataan': purple };

        function ready(fn) {
            if (window.Chart) return fn();
            let tries = 0;
            const iv = setInterval(() => {
                if (window.Chart || tries++ > 50) { clearInterval(iv); if (window.Chart) fn(); }
            }, 100);
        }

        ready(function () {
            Chart.defaults.font.family = 'ui-sans-serif, system-ui, sans-serif';
            Chart.defaults.color = '#64748b';

            const trendEl = document.getElementById('chartTrend');
            if (trendEl) {
                new Chart(trendEl, {
                    type: 'line',
                    data: {
                        labels: charts.trend.labels,
                        datasets: [{
                            label: 'Pengaduan', data: charts.trend.data,
                            borderColor: emerald, backgroundColor: 'rgba(5,150,105,0.12)',
                            fill: true, tension: 0.35, borderWidth: 2, pointRadius: 3, pointBackgroundColor: emerald,
                        }],
                    },
                    options: { responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } },
                });
            }

            const statusEl = document.getElementById('chartStatus');
            if (statusEl && charts.status.data.length) {
                new Chart(statusEl, {
                    type: 'doughnut',
                    data: { labels: charts.status.labels, datasets: [{ data: charts.status.data, backgroundColor: palette, borderWidth: 2, borderColor: '#fff' }] },
                    options: { responsive: true, maintainAspectRatio: false, cutout: '62%',
                        plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12 } } } },
                });
            } else if (statusEl) {
                statusEl.parentElement.innerHTML = '<p class="grid h-full place-items-center text-sm text-slate-400">Belum ada data status</p>';
            }

            const statusStats = <?php echo json_encode($statusStats ?? null, 15, 512) ?>;
            const miniEl = document.getElementById('chartStatusMini');
            if (miniEl && statusStats && statusStats.distribution.data.length) {
                const miniColors = { 'Belum Ditindaklanjuti': amber, 'Belum Ditinjau': amber, 'Ditindaklanjuti': sky, 'Ditinjau': sky, 'Selesai': emerald, 'Ditolak': rose };
                new Chart(miniEl, {
                    type: 'doughnut',
                    data: {
                        labels: statusStats.distribution.labels,
                        datasets: [{
                            data: statusStats.distribution.data,
                            backgroundColor: statusStats.distribution.labels.map(l => miniColors[l] || slate),
                            borderWidth: 3,
                            borderColor: '#ffffff',
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',
                        plugins: { legend: { display: false }, tooltip: { enabled: true } },
                    },
                });
            } else if (miniEl) {
                miniEl.parentElement.innerHTML = '<p class="grid h-full place-items-center text-sm text-slate-400">Belum ada data</p>';
            }

            const modEl = document.getElementById('chartModules');
            if (modEl && charts.modules.data.length) {
                new Chart(modEl, {
                    type: 'bar',
                    data: { labels: charts.modules.labels, datasets: [{ label: 'Jumlah', data: charts.modules.data, backgroundColor: palette, borderRadius: 6, maxBarThickness: 48 }] },
                    options: { responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } }, x: { grid: { display: false }, ticks: { autoSkip: false, maxRotation: 45, minRotation: 0 } } } },
                });
            } else if (modEl) {
                modEl.parentElement.innerHTML = '<p class="grid h-full place-items-center text-sm text-slate-400">Belum ada data modul</p>';
            }

            const perfEl = document.getElementById('chartPerformance');
            if (perfEl && charts.performance && charts.performance.length) {
                const perfColors = { 'Belum Ditindaklanjuti': amber, 'Belum Ditinjau': amber, 'Ditindaklanjuti': sky, 'Ditinjau': sky, 'Selesai': emerald, 'Ditolak': rose };
                const labels = charts.performance.map(p => p.status);
                const data = charts.performance.map(p => p.total);
                new Chart(perfEl, {
                    type: 'bar',
                    data: { labels, datasets: [{ label: 'Jumlah', data, backgroundColor: labels.map(l => perfColors[l] || slate), borderRadius: 6, maxBarThickness: 26 }] },
                    options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } }, y: { grid: { display: false } } } },
                });
            } else if (perfEl) {
                perfEl.parentElement.innerHTML = '<p class="grid h-full place-items-center text-sm text-slate-400">Belum ada data</p>';
            }

            const trendBidangEl = document.getElementById('chartTrendBidang');
            if (trendBidangEl && charts.trendPerBidang && Object.keys(charts.trendPerBidang.datasets || {}).length > 1) {
                const datasets = Object.entries(charts.trendPerBidang.datasets).map(([bidang, data]) => ({
                    label: bidang.charAt(0).toUpperCase() + bidang.slice(1),
                    data: data,
                    borderColor: bidangColors[bidang] || slate,
                    backgroundColor: (bidangColors[bidang] || slate) + '18',
                    fill: false,
                    tension: 0.35,
                    borderWidth: 2,
                    pointRadius: 3,
                }));
                const trendBidangChart = new Chart(trendBidangEl, {
                    type: 'line',
                    data: { labels: charts.trendPerBidang.labels, datasets },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: { padding: { top: 4, bottom: 4 } },
                        plugins: {
                            legend: {
                                position: 'top',
                                align: 'center',
                                labels: { boxWidth: 12, padding: 16, usePointStyle: true, pointStyle: 'circle' },
                            },
                        },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                            x: { grid: { display: false } },
                        },
                    },
                });
                if (window.ResizeObserver) {
                    new ResizeObserver(() => trendBidangChart.resize()).observe(trendBidangEl.parentElement);
                }
                window.addEventListener('resize', () => trendBidangChart.resize());
            } else if (trendBidangEl) {
                trendBidangEl.parentElement.innerHTML = '<p class="grid h-full place-items-center text-sm text-slate-400">Perlu akses lebih dari 1 bidang</p>';
            }
        });

        // Peta Sebaran Pengaduan ditangani di partial admin.partials.sebaran-pengaduan

        // Stagger reveal
        const revealEls = Array.from(document.querySelectorAll('.stagger-item'));
        if (revealEls.length) {
            const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (reduce || !('IntersectionObserver' in window)) {
                revealEls.forEach(el => el.classList.add('is-in'));
            } else {
                const io = new IntersectionObserver((entries, obs) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-in');
                            obs.unobserve(entry.target);
                        }
                    });
                }, { rootMargin: '0px 0px -10% 0px', threshold: 0.05 });
                revealEls.forEach(el => io.observe(el));
            }
        }
    })();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\DLH - PALU\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>