<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'label' => '',
    'value' => 0,
    'icon' => 'chart',
    'color' => 'emerald',   // emerald|sky|teal|amber|rose|purple|indigo|bay|slate
]));

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

foreach (array_filter(([
    'label' => '',
    'value' => 0,
    'icon' => 'chart',
    'color' => 'emerald',   // emerald|sky|teal|amber|rose|purple|indigo|bay|slate
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $palette = [
        'emerald' => ['hex' => '#059669', 'bg' => 'bg-brand-50', 'text' => 'text-brand-600'],
        'sky'     => ['hex' => '#0284c7', 'bg' => 'bg-info-50', 'text' => 'text-info-600'],
        'teal'    => ['hex' => '#0d9488', 'bg' => 'bg-teal-50', 'text' => 'text-teal-700'],
        'amber'   => ['hex' => '#d97706', 'bg' => 'bg-warning-50', 'text' => 'text-warning-600'],
        'rose'    => ['hex' => '#e11d48', 'bg' => 'bg-danger-50', 'text' => 'text-danger-600'],
        'purple'  => ['hex' => '#7c3aed', 'bg' => 'bg-clay-50', 'text' => 'text-clay-600'],
        'indigo'  => ['hex' => '#4f46e5', 'bg' => 'bg-indigo-50', 'text' => 'text-indigo-600'],
        'bay'     => ['hex' => '#0dabce', 'bg' => 'bg-info-50', 'text' => 'text-info-600'],
        'slate'   => ['hex' => '#64748b', 'bg' => 'bg-slate-50', 'text' => 'text-slate-600'],
    ];
    $c = $palette[$color] ?? $palette['emerald'];
    $numeric = is_numeric($value);
?>

<div>
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['padding' => false,'class' => 'card-lift overflow-hidden']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'class' => 'card-lift overflow-hidden']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <div class="flex items-center gap-4 p-5">
            <div class="grid size-12 shrink-0 place-items-center rounded-xl <?php echo e($c['bg']); ?> <?php echo e($c['text']); ?>">
                <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => $icon,'size' => 24]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'size' => 24]); ?>
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
                <p class="truncate text-xs font-semibold uppercase tracking-[0.06em] text-ink-500"><?php echo e($label); ?></p>
                <p class="mt-1 text-xl font-bold tracking-tight text-ink-900">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($numeric): ?>
                        <?php if (isset($component)) { $__componentOriginald45cedd3e5403692fb01ca257c578ca2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald45cedd3e5403692fb01ca257c578ca2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.count-up','data' => ['value' => (int) $value]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.count-up'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((int) $value)]); ?>
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
<?php endif; ?>
                    <?php else: ?>
                        <?php echo e($value); ?>

                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </p>
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
<?php /**PATH C:\xampp\htdocs\DLH - PALU\resources\views/components/admin/kpi-strip.blade.php ENDPATH**/ ?>