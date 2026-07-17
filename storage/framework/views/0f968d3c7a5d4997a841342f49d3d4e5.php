<div
    class="relative"
    x-data="{
        open: false,
        count: <?php echo e((int) ($notificationCount ?? 0)); ?>,
        items: <?php echo e(Illuminate\Support\Js::from($notifications ?? collect())); ?>,
        csrf: '<?php echo e(csrf_token()); ?>',
        async refresh() {
            try {
                const r = await fetch('<?php echo e(route('admin.notifications.poll')); ?>', { headers: { 'Accept': 'application/json' } });
                if (!r.ok) return;
                const d = await r.json();
                this.count = d.unread;
                this.items = d.notifications;
            } catch(e) {}
        },
        async markRead(id) {
            try {
                const r = await fetch('<?php echo e(url('admin/notifications')); ?>/' + id + '/read', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf }
                });
                if (r.ok) {
                    const d = await r.json();
                    this.count = d.unread;
                    await this.refresh();
                }
            } catch(e) {}
        },
        async markAllRead() {
            try {
                const r = await fetch('<?php echo e(route('admin.notifications.read-all')); ?>', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf }
                });
                if (r.ok) {
                    this.count = 0;
                    await this.refresh();
                }
            } catch(e) {}
        }
    }"
    x-init="setInterval(() => refresh(), 30000)"
    x-on:click.away="open = false"
    x-on:keydown.escape.window="open = false"
>
    <button
        x-on:click="open = !open"
        class="topbar-btn relative"
        title="Notifikasi"
    >
        <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'bell','size' => 20]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'bell','size' => 20]); ?>
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
        <template x-if="count > 0">
            <span class="absolute -right-0.5 -top-0.5 flex size-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white shadow-lg shadow-red-500/30">
                <span class="absolute inset-0 rounded-full bg-red-500 animate-ping opacity-40"></span>
                <span class="relative" x-text="count > 9 ? '9+' : count"></span>
            </span>
        </template>
    </button>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="glass-dropdown absolute right-0 top-full z-50 mt-2 w-[380px] overflow-hidden rounded-2xl"
        style="display: none;"
    >
        
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3.5 dark:border-white/[.06]">
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Notifikasi</h3>
                <template x-if="count > 0">
                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400" x-text="count"></span>
                </template>
            </div>
            <button
                x-show="count > 0"
                x-on:click="markAllRead()"
                class="text-[12px] font-semibold text-emerald-600 transition-colors hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300"
            >
                Tandai semua dibaca
            </button>
        </div>

        
        <div class="max-h-[360px] overflow-y-auto">
            <template x-if="items.length === 0">
                <div class="px-5 py-14 text-center">
                    <div class="mx-auto grid size-14 place-items-center rounded-2xl bg-slate-100 dark:bg-white/[.06]">
                        <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'bell','size' => 24,'class' => 'text-slate-300 dark:text-slate-600']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'bell','size' => 24,'class' => 'text-slate-300 dark:text-slate-600']); ?>
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
                    <p class="mt-4 text-sm font-semibold text-slate-700 dark:text-slate-300">Tidak ada notifikasi</p>
                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Semua sudah dibaca</p>
                </div>
            </template>
            <template x-for="notif in items" :key="notif.id">
                <a
                    :href="notif.href || '#'"
                    x-on:click="markRead(notif.id)"
                    class="flex gap-3.5 border-b border-slate-50 px-5 py-3.5 transition-all duration-150 hover:bg-emerald-50/50 dark:border-white/[.04] dark:hover:bg-emerald-900/10"
                    :class="notif.read ? 'opacity-50' : ''"
                >
                    <div class="grid size-10 shrink-0 place-items-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                        <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'bell','size' => 18]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'bell','size' => 18]); ?>
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
                        <p class="text-[13px] font-semibold text-slate-800 dark:text-slate-100" x-text="notif.title"></p>
                        <p class="mt-0.5 line-clamp-2 text-[12px] text-slate-500 dark:text-slate-400" x-text="notif.message"></p>
                        <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500" x-text="notif.time"></p>
                    </div>
                    <span x-show="!notif.read" class="mt-1.5 size-2 shrink-0 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500/50"></span>
                </a>
            </template>
        </div>

        
        <a
            href="<?php echo e(route('admin.notifications.index')); ?>"
            class="block border-t border-slate-100 bg-slate-50/80 px-5 py-3 text-center text-[12px] font-semibold text-emerald-600 transition-all duration-150 hover:bg-slate-100 dark:border-white/[.06] dark:bg-white/[.03] dark:text-emerald-400 dark:hover:bg-white/[.06]"
        >
            Lihat semua notifikasi
        </a>
    </div>
</div>
<?php /**PATH D:\Backup\DLH - Palu\resources\views/components/admin/topbar/notification-dropdown.blade.php ENDPATH**/ ?>