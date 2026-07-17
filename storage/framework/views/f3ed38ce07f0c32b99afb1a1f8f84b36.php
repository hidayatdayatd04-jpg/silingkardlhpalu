<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['padding' => true, 'hover' => false]));

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

foreach (array_filter((['padding' => true, 'hover' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $classes = 'rounded-lg border border-white/80 bg-white shadow-[0_18px_60px_rgba(15,23,42,0.08)]';
    if ($padding) {
        $classes .= ' p-6';
    }
    if ($hover) {
        $classes .= ' transition hover:shadow-[0_18px_80px_rgba(15,23,42,0.12)] hover:border-emerald-200';
    }
?>

<div <?php echo e($attributes->merge(['class' => $classes])); ?>>
    <?php echo e($slot); ?>

</div>
<?php /**PATH D:\Backup\DLH - Palu\resources\views/components/admin/card.blade.php ENDPATH**/ ?>