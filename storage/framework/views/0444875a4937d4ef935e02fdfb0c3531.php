<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'value' => 0,          // angka target
    'duration' => 1200,    // ms
    'format' => true,      // format ribuan id-ID
    'prefix' => '',
    'suffix' => '',
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
    'value' => 0,          // angka target
    'duration' => 1200,    // ms
    'format' => true,      // format ribuan id-ID
    'prefix' => '',
    'suffix' => '',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $target = (float) $value;
?>

<span
    <?php echo e($attributes); ?>

    x-data="{
        target: <?php echo e($target); ?>,
        display: 0,
        fmt(n) {
            const v = Math.round(n);
            return <?php echo e($format ? 'true' : 'false'); ?> ? new Intl.NumberFormat('id-ID').format(v) : v;
        },
        run() {
            const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (reduce || this.target === 0) { this.display = this.target; return; }
            const dur = <?php echo e((int) $duration); ?>;
            let start = null;
            const step = (ts) => {
                if (start === null) start = ts;
                const p = Math.min((ts - start) / dur, 1);
                const eased = 1 - Math.pow(1 - p, 3);
                this.display = this.target * eased;
                if (p < 1) requestAnimationFrame(step);
                else this.display = this.target;
            };
            requestAnimationFrame(step);
        }
    }"
    x-init="run()"
><span aria-hidden="true"><?php echo e($prefix); ?><span x-text="fmt(display)"></span><?php echo e($suffix); ?></span><span class="sr-only"><?php echo e($prefix); ?><?php echo e($format ? number_format($target) : $target); ?><?php echo e($suffix); ?></span></span>
<?php /**PATH D:\Backup\DLH - Palu\resources\views/components/admin/count-up.blade.php ENDPATH**/ ?>