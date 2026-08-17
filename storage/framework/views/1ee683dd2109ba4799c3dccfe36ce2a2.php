
<div
    x-data="backupProgressWidget"
    x-cloak
    x-show="visible"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4"
    class="fixed bottom-4 right-4 z-[115] w-[calc(100vw-2rem)] max-w-sm overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[var(--shadow-elevated)]"
    data-progress-url="<?php echo e(route('admin.backup.progress')); ?>"
    data-cancel-url="<?php echo e(route('admin.backup.cancel')); ?>"
    role="status"
    aria-live="polite"
>
    <div class="flex items-start gap-3 px-4 pt-3.5">
        <div class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-600">
            <template x-if="task && task.type === 'restore'">
                <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'refresh','size' => 17]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'refresh','size' => 17]); ?>
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
            </template>
            <template x-if="!task || task.type !== 'restore'">
                <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'database','size' => 17]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'database','size' => 17]); ?>
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
            </template>
        </div>

        <div class="min-w-0 flex-1">
            <div class="flex items-center justify-between gap-2">
                <p class="truncate text-sm font-bold text-ink-900" x-text="title"></p>
                <span class="shrink-0 font-mono text-sm font-bold text-brand-600" x-text="(task ? task.percent : 0) + '%'"></span>
            </div>
            <p class="mt-0.5 truncate text-[11px] text-slate-500" x-text="task ? (task.label || '') : ''"></p>
        </div>
    </div>

    
    <div class="mx-4 mt-3 h-2 overflow-hidden rounded-full bg-slate-100">
        <div
            class="h-full rounded-full bg-brand-600 transition-all duration-500 ease-out"
            :style="'width:' + (task ? task.percent : 0) + '%'"
        ></div>
    </div>

    <div class="flex items-center justify-between gap-2 px-4 py-3">
        <p class="text-[11px] text-slate-400" x-show="stalePending" x-cloak>
            Menunggu antrian — pastikan queue worker berjalan.
        </p>
        <p class="text-[11px] text-slate-400" x-show="!stalePending">
            Proses berjalan di latar belakang.
        </p>

        <button
            type="button"
            x-on:click="cancel()"
            :disabled="cancelling"
            class="inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-danger-200 bg-danger-50 px-2.5 py-1.5 text-[11px] font-bold text-danger-600 transition hover:bg-danger-100 disabled:opacity-60"
        >
            <svg x-show="cancelling" class="size-3 animate-spin" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span x-text="cancelling ? 'Membatalkan…' : 'Batalkan'"></span>
        </button>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\DLH - PALU\resources\views/components/admin/backup-progress.blade.php ENDPATH**/ ?>