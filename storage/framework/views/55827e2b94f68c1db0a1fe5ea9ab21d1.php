<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'default',   // success | warning | danger | info | default | neutral
    'label' => null,
    'pulse' => false,         // dot berdenyut (status aktif/pending)
    'dot' => true,
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
    'variant' => 'default',   // success | warning | danger | info | default | neutral
    'label' => null,
    'pulse' => false,         // dot berdenyut (status aktif/pending)
    'dot' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $map = [
        'success' => 'border-success-200 bg-success-50 text-success-700',
        'warning' => 'border-warning-200 bg-warning-50 text-warning-700',
        'danger'  => 'border-danger-200 bg-danger-50 text-danger-700',
        'info'    => 'border-info-200 bg-info-50 text-info-700',
        'neutral' => 'border-slate-200 bg-slate-50 text-slate-600',
        'default' => 'border-slate-200 bg-slate-50 text-slate-700',
    ];
    $dotColor = [
        'success' => 'text-success-500',
        'warning' => 'text-warning-500',
        'danger'  => 'text-danger-500',
        'info'    => 'text-info-500',
        'neutral' => 'text-slate-400',
        'default' => 'text-slate-400',
    ];
    $cls = $map[$variant] ?? $map['default'];
    $dc = $dotColor[$variant] ?? $dotColor['default'];
?>

<span <?php echo e($attributes->merge(['class' => "inline-flex max-w-full items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold leading-none $cls"])); ?>>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dot): ?>
        <span class="relative grid size-1.5 place-items-center <?php echo e($dc); ?> <?php echo e($pulse ? 'dot-pulse' : ''); ?>">
            <span class="size-1.5 rounded-full bg-current"></span>
        </span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <span class="truncate"><?php echo e($label ?? $slot); ?></span>
</span>
<?php /**PATH C:\xampp\htdocs\DLH - PALU\resources\views/components/admin/status-pill.blade.php ENDPATH**/ ?>