<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'action',                       // URL form destroy (route)
    'method' => 'DELETE',
    'title' => 'Hapus Data',
    'message' => 'Data ini akan dihapus permanen. Aksi ini tidak bisa dibatalkan.',
    'confirmText' => 'Hapus',
    'cancelText' => 'Batal',
    'name' => null,                 // wajib bila dipicu dari luar via $dispatch('open-modal', '<name>')
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
    'action',                       // URL form destroy (route)
    'method' => 'DELETE',
    'title' => 'Hapus Data',
    'message' => 'Data ini akan dihapus permanen. Aksi ini tidak bisa dibatalkan.',
    'confirmText' => 'Hapus',
    'cancelText' => 'Batal',
    'name' => null,                 // wajib bila dipicu dari luar via $dispatch('open-modal', '<name>')
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $name = $name ?? 'confirm-delete-' . Str::random(6);
?>

<?php if (isset($component)) { $__componentOriginal883972b03e56cea0994a1aaccc5761f0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal883972b03e56cea0994a1aaccc5761f0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.modal','data' => ['name' => $name,'title' => $title,'variant' => 'danger','maxWidth' => 'md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($name),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title),'variant' => 'danger','max-width' => 'md']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <p class="text-sm leading-relaxed text-ink-600"><?php echo e($message); ?></p>
    <?php echo e($slot); ?>


     <?php $__env->slot('footer', null, []); ?> 
        <button
            type="button"
            x-on:click="closeModal()"
            class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300"
        >
            <?php echo e($cancelText); ?>

        </button>

        <form
            method="POST"
            action="<?php echo e($action); ?>"
            x-data="{ submitting: false }"
            x-on:submit="submitting = true"
        >
            <?php echo csrf_field(); ?>
            <?php echo method_field($method); ?>
            <?php echo e($form ?? ''); ?>

            <button
                type="submit"
                :disabled="submitting"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-danger-600 px-4 py-2.5 text-sm font-bold text-white shadow-[0_8px_20px_-8px_rgba(225,29,72,0.6)] transition hover:bg-danger-700 focus:outline-none focus:ring-4 focus:ring-danger-100 disabled:opacity-60"
            >
                <svg x-show="submitting" x-cloak class="size-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['xShow' => '!submitting','name' => 'trash','size' => 16]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-show' => '!submitting','name' => 'trash','size' => 16]); ?>
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
                <span x-text="submitting ? 'Menghapus...' : <?php echo \Illuminate\Support\Js::from($confirmText)->toHtml() ?>"></span>
            </button>
        </form>
     <?php $__env->endSlot(); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal883972b03e56cea0994a1aaccc5761f0)): ?>
<?php $attributes = $__attributesOriginal883972b03e56cea0994a1aaccc5761f0; ?>
<?php unset($__attributesOriginal883972b03e56cea0994a1aaccc5761f0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal883972b03e56cea0994a1aaccc5761f0)): ?>
<?php $component = $__componentOriginal883972b03e56cea0994a1aaccc5761f0; ?>
<?php unset($__componentOriginal883972b03e56cea0994a1aaccc5761f0); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\DLH - PALU\resources\views/components/admin/confirm-delete.blade.php ENDPATH**/ ?>