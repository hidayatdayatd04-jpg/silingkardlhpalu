<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['heading' => 'Dashboard']));

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

foreach (array_filter((['heading' => 'Dashboard']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $user = auth()->user();
?>

<header
    class="topbar-noise sticky top-0 z-50 topbar-glass shadow-[0_1px_3px_rgba(0,0,0,.04)] dark:shadow-[0_1px_3px_rgba(0,0,0,.25)]"
>
    <div class="relative z-10 flex h-20 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">

        
        <div class="flex min-w-0 items-center gap-3">
            
            <?php if (isset($component)) { $__componentOriginal436997bdaf5bd09684f151681708f53c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal436997bdaf5bd09684f151681708f53c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.topbar.breadcrumb','data' => ['heading' => $heading]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.topbar.breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($heading)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal436997bdaf5bd09684f151681708f53c)): ?>
<?php $attributes = $__attributesOriginal436997bdaf5bd09684f151681708f53c; ?>
<?php unset($__attributesOriginal436997bdaf5bd09684f151681708f53c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal436997bdaf5bd09684f151681708f53c)): ?>
<?php $component = $__componentOriginal436997bdaf5bd09684f151681708f53c; ?>
<?php unset($__componentOriginal436997bdaf5bd09684f151681708f53c); ?>
<?php endif; ?>
        </div>

        
        <div class="hidden flex-1 justify-center lg:flex">
            <?php if (isset($component)) { $__componentOriginal678dfd04e07d6d237e9dffa11e82aae2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal678dfd04e07d6d237e9dffa11e82aae2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.topbar.global-search','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.topbar.global-search'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal678dfd04e07d6d237e9dffa11e82aae2)): ?>
<?php $attributes = $__attributesOriginal678dfd04e07d6d237e9dffa11e82aae2; ?>
<?php unset($__attributesOriginal678dfd04e07d6d237e9dffa11e82aae2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal678dfd04e07d6d237e9dffa11e82aae2)): ?>
<?php $component = $__componentOriginal678dfd04e07d6d237e9dffa11e82aae2; ?>
<?php unset($__componentOriginal678dfd04e07d6d237e9dffa11e82aae2); ?>
<?php endif; ?>
        </div>

        
        <div class="flex items-center gap-1.5">
            
            <?php if (isset($component)) { $__componentOriginalf2daba246a72211f18f0f67aa2cbb8e7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2daba246a72211f18f0f67aa2cbb8e7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.topbar.quick-action','data' => ['user' => $user]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.topbar.quick-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf2daba246a72211f18f0f67aa2cbb8e7)): ?>
<?php $attributes = $__attributesOriginalf2daba246a72211f18f0f67aa2cbb8e7; ?>
<?php unset($__attributesOriginalf2daba246a72211f18f0f67aa2cbb8e7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf2daba246a72211f18f0f67aa2cbb8e7)): ?>
<?php $component = $__componentOriginalf2daba246a72211f18f0f67aa2cbb8e7; ?>
<?php unset($__componentOriginalf2daba246a72211f18f0f67aa2cbb8e7); ?>
<?php endif; ?>

            
            <?php if (isset($component)) { $__componentOriginal0fcdd27693e75bf843ab1e28d2ad979e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0fcdd27693e75bf843ab1e28d2ad979e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.topbar.notification-dropdown','data' => ['notifications' => $notifications,'notificationCount' => $notificationCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.topbar.notification-dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['notifications' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notifications),'notification-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationCount)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0fcdd27693e75bf843ab1e28d2ad979e)): ?>
<?php $attributes = $__attributesOriginal0fcdd27693e75bf843ab1e28d2ad979e; ?>
<?php unset($__attributesOriginal0fcdd27693e75bf843ab1e28d2ad979e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0fcdd27693e75bf843ab1e28d2ad979e)): ?>
<?php $component = $__componentOriginal0fcdd27693e75bf843ab1e28d2ad979e; ?>
<?php unset($__componentOriginal0fcdd27693e75bf843ab1e28d2ad979e); ?>
<?php endif; ?>

            
            <?php if (isset($component)) { $__componentOriginal270f28ffb9b905731178974eb3b4913b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal270f28ffb9b905731178974eb3b4913b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.topbar.public-website','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.topbar.public-website'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal270f28ffb9b905731178974eb3b4913b)): ?>
<?php $attributes = $__attributesOriginal270f28ffb9b905731178974eb3b4913b; ?>
<?php unset($__attributesOriginal270f28ffb9b905731178974eb3b4913b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal270f28ffb9b905731178974eb3b4913b)): ?>
<?php $component = $__componentOriginal270f28ffb9b905731178974eb3b4913b; ?>
<?php unset($__componentOriginal270f28ffb9b905731178974eb3b4913b); ?>
<?php endif; ?>
        </div>
    </div>
</header>
<?php /**PATH C:\xampp\htdocs\DLH - PALU\resources\views/components/admin/topbar.blade.php ENDPATH**/ ?>