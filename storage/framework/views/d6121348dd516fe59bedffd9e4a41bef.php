<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0a2f24">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Admin DLH Kota Palu'); ?></title>
    <meta name="description" content="Panel admin Ruang Kendali Operasional Dinas Lingkungan Hidup Kota Palu — kelola permohonan, pengaduan, sampah, RTH, tata penataan, konten, dan pengguna.">
    <script>document.documentElement.classList.add('js');</script>
    <link rel="icon" type="image/png" href="<?php echo e(asset('favicon-32x32.png')); ?>">
    <?php echo $__env->make('partials.web-fonts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin-common.js']); ?>
    <style>
        /* Hide scrollbar on sidebar nav */
        .sidebar-nav::-webkit-scrollbar { display: none; }
        .sidebar-nav { -ms-overflow-style: none; scrollbar-width: none; }
        /* Hide scrollbar on main content */
        .main-scroll::-webkit-scrollbar { display: none; }
        .main-scroll { -ms-overflow-style: none; scrollbar-width: none; }
        /* Prevent Alpine flash before init */
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="min-h-screen text-ink-900 antialiased" x-data data-alpine-bootstrap style="background: var(--gradient-page);">
    <?php
        $user = auth()->user();
        $adminGroups = \App\Support\Admin\AdminRegistry::forUser($user);
        $allGroups = \App\Support\Admin\AdminRegistry::all();
        $activeResource = request()->route('resource');
        $heading = '';
    ?>

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar Component -->
        <?php if (isset($component)) { $__componentOriginalbebe114f3ccde4b38d7462a3136be045 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbebe114f3ccde4b38d7462a3136be045 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar','data' => ['groups' => $adminGroups,'allGroups' => $allGroups,'user' => $user]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['groups' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($adminGroups),'allGroups' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($allGroups),'user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbebe114f3ccde4b38d7462a3136be045)): ?>
<?php $attributes = $__attributesOriginalbebe114f3ccde4b38d7462a3136be045; ?>
<?php unset($__attributesOriginalbebe114f3ccde4b38d7462a3136be045); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbebe114f3ccde4b38d7462a3136be045)): ?>
<?php $component = $__componentOriginalbebe114f3ccde4b38d7462a3136be045; ?>
<?php unset($__componentOriginalbebe114f3ccde4b38d7462a3136be045); ?>
<?php endif; ?>

        <div class="main-scroll flex min-w-0 flex-1 flex-col <?php echo e(trim(view()->yieldContent('full_width')) ? '' : 'overflow-y-auto'); ?>">
            <!-- Topbar Component -->
            <?php if (isset($component)) { $__componentOriginal232f012cda936a4eb249de3234ccfddd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal232f012cda936a4eb249de3234ccfddd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.topbar','data' => ['heading' => $heading = trim(view()->yieldContent('heading') ?: 'Dashboard')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.topbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($heading = trim(view()->yieldContent('heading') ?: 'Dashboard'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal232f012cda936a4eb249de3234ccfddd)): ?>
<?php $attributes = $__attributesOriginal232f012cda936a4eb249de3234ccfddd; ?>
<?php unset($__attributesOriginal232f012cda936a4eb249de3234ccfddd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal232f012cda936a4eb249de3234ccfddd)): ?>
<?php $component = $__componentOriginal232f012cda936a4eb249de3234ccfddd; ?>
<?php unset($__componentOriginal232f012cda936a4eb249de3234ccfddd); ?>
<?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(trim(view()->yieldContent('full_width'))): ?>
                <?php echo $__env->yieldContent('content'); ?>
            <?php else: ?>
            <main class="mx-auto w-full max-w-[1400px] px-4 py-6 sm:px-6 lg:px-8">
                <div class="space-y-6" data-page-content>
                <!-- Flash Messages -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
                    <?php if (isset($component)) { $__componentOriginald888329b8246e32afd68d2decbd25cf1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald888329b8246e32afd68d2decbd25cf1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.alert','data' => ['type' => 'success','dismissible' => true,'class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'success','dismissible' => true,'class' => 'mb-6']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        <?php echo e(session('success')); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald888329b8246e32afd68d2decbd25cf1)): ?>
<?php $attributes = $__attributesOriginald888329b8246e32afd68d2decbd25cf1; ?>
<?php unset($__attributesOriginald888329b8246e32afd68d2decbd25cf1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald888329b8246e32afd68d2decbd25cf1)): ?>
<?php $component = $__componentOriginald888329b8246e32afd68d2decbd25cf1; ?>
<?php unset($__componentOriginald888329b8246e32afd68d2decbd25cf1); ?>
<?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
                    <?php if (isset($component)) { $__componentOriginald888329b8246e32afd68d2decbd25cf1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald888329b8246e32afd68d2decbd25cf1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.alert','data' => ['type' => 'error','dismissible' => true,'class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'error','dismissible' => true,'class' => 'mb-6']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        <?php echo e(session('error')); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald888329b8246e32afd68d2decbd25cf1)): ?>
<?php $attributes = $__attributesOriginald888329b8246e32afd68d2decbd25cf1; ?>
<?php unset($__attributesOriginald888329b8246e32afd68d2decbd25cf1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald888329b8246e32afd68d2decbd25cf1)): ?>
<?php $component = $__componentOriginald888329b8246e32afd68d2decbd25cf1; ?>
<?php unset($__componentOriginald888329b8246e32afd68d2decbd25cf1); ?>
<?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('warning')): ?>
                    <?php if (isset($component)) { $__componentOriginald888329b8246e32afd68d2decbd25cf1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald888329b8246e32afd68d2decbd25cf1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.alert','data' => ['type' => 'warning','dismissible' => true,'class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'warning','dismissible' => true,'class' => 'mb-6']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        <?php echo e(session('warning')); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald888329b8246e32afd68d2decbd25cf1)): ?>
<?php $attributes = $__attributesOriginald888329b8246e32afd68d2decbd25cf1; ?>
<?php unset($__attributesOriginald888329b8246e32afd68d2decbd25cf1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald888329b8246e32afd68d2decbd25cf1)): ?>
<?php $component = $__componentOriginald888329b8246e32afd68d2decbd25cf1; ?>
<?php unset($__componentOriginald888329b8246e32afd68d2decbd25cf1); ?>
<?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('info')): ?>
                    <?php if (isset($component)) { $__componentOriginald888329b8246e32afd68d2decbd25cf1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald888329b8246e32afd68d2decbd25cf1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.alert','data' => ['type' => 'info','dismissible' => true,'class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'info','dismissible' => true,'class' => 'mb-6']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        <?php echo e(session('info')); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald888329b8246e32afd68d2decbd25cf1)): ?>
<?php $attributes = $__attributesOriginald888329b8246e32afd68d2decbd25cf1; ?>
<?php unset($__attributesOriginald888329b8246e32afd68d2decbd25cf1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald888329b8246e32afd68d2decbd25cf1)): ?>
<?php $component = $__componentOriginald888329b8246e32afd68d2decbd25cf1; ?>
<?php unset($__componentOriginald888329b8246e32afd68d2decbd25cf1); ?>
<?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php echo $__env->yieldContent('content'); ?>
                </div>
            </main>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <?php if (isset($component)) { $__componentOriginalb774badd5a25171b3116df46663b1b78 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb774badd5a25171b3116df46663b1b78 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.command-palette','data' => ['user' => $user]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.command-palette'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb774badd5a25171b3116df46663b1b78)): ?>
<?php $attributes = $__attributesOriginalb774badd5a25171b3116df46663b1b78; ?>
<?php unset($__attributesOriginalb774badd5a25171b3116df46663b1b78); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb774badd5a25171b3116df46663b1b78)): ?>
<?php $component = $__componentOriginalb774badd5a25171b3116df46663b1b78; ?>
<?php unset($__componentOriginalb774badd5a25171b3116df46663b1b78); ?>
<?php endif; ?>

    
    <?php if (isset($component)) { $__componentOriginalbecd58215a07d84c26124574b5f7c626 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbecd58215a07d84c26124574b5f7c626 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.backup-progress','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.backup-progress'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbecd58215a07d84c26124574b5f7c626)): ?>
<?php $attributes = $__attributesOriginalbecd58215a07d84c26124574b5f7c626; ?>
<?php unset($__attributesOriginalbecd58215a07d84c26124574b5f7c626); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbecd58215a07d84c26124574b5f7c626)): ?>
<?php $component = $__componentOriginalbecd58215a07d84c26124574b5f7c626; ?>
<?php unset($__componentOriginalbecd58215a07d84c26124574b5f7c626); ?>
<?php endif; ?>

    
    <?php if (isset($component)) { $__componentOriginal30b09ba64d8f9e6b0023e860875d7bb6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal30b09ba64d8f9e6b0023e860875d7bb6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.toast','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.toast'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal30b09ba64d8f9e6b0023e860875d7bb6)): ?>
<?php $attributes = $__attributesOriginal30b09ba64d8f9e6b0023e860875d7bb6; ?>
<?php unset($__attributesOriginal30b09ba64d8f9e6b0023e860875d7bb6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal30b09ba64d8f9e6b0023e860875d7bb6)): ?>
<?php $component = $__componentOriginal30b09ba64d8f9e6b0023e860875d7bb6; ?>
<?php unset($__componentOriginal30b09ba64d8f9e6b0023e860875d7bb6); ?>
<?php endif; ?>

    <?php echo $__env->yieldPushContent('styles'); ?>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>

</html>
<?php /**PATH C:\xampp\htdocs\DLH - PALU\resources\views/layouts/admin.blade.php ENDPATH**/ ?>