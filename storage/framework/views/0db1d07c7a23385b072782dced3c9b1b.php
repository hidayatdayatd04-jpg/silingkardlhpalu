<div class="hidden lg:block" x-data="{ focused: false }">
    <div class="relative search-glow" :class="focused ? 'z-10' : ''">
        
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
            <span x-bind:class="focused ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500'" class="transition-colors duration-200">
                <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'search','size' => 16]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'search','size' => 16]); ?>
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

        
        <input
            type="search"
            x-on:focus="focused = true"
            x-on:blur="focused = false"
            x-on:click="$dispatch('open-command-palette')"
            readonly
            placeholder="Cari menu, artikel, pengguna..."
            class="h-10 w-[340px] cursor-pointer rounded-full border border-slate-200/80 bg-slate-50/60 py-2 pl-10 pr-20 text-[13px] text-slate-700 outline-none transition-all duration-250 placeholder:text-slate-400 hover:border-slate-300 hover:bg-white dark:border-white/[.08] dark:bg-white/[.04] dark:text-slate-200 dark:placeholder:text-slate-500 dark:hover:border-white/[.15] dark:hover:bg-white/[.06]"
        />

        
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
            <kbd class="hidden rounded-md border border-slate-200/80 bg-white px-1.5 py-0.5 text-[10px] font-semibold text-slate-400 shadow-sm dark:border-white/[.1] dark:bg-white/[.06] dark:text-slate-500 md:inline-block">
                <span class="text-xs">&#8984;</span>K
            </kbd>
        </div>
    </div>
</div>
<?php /**PATH D:\Backup\DLH - Palu\resources\views/components/admin/topbar/global-search.blade.php ENDPATH**/ ?>