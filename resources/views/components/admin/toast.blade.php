{{-- Toast host — merender Alpine store 'toasts' (didefinisikan di resources/js/admin.js).
     window.showToast(msg, type) tetap bekerja (push ke store bila ada, fallback DOM). --}}
@php
    $toastConf = [
        'success' => ['bg' => 'bg-success-600', 'icon' => 'circle-check'],
        'error'   => ['bg' => 'bg-danger-600',  'icon' => 'alert-circle'],
        'warning' => ['bg' => 'bg-warning-600', 'icon' => 'alert-triangle'],
        'info'    => ['bg' => 'bg-info-600',    'icon' => 'info-circle'],
    ];
@endphp

<div
    class="pointer-events-none fixed inset-x-0 bottom-0 z-[120] flex flex-col items-end gap-2 p-4 sm:p-6"
    role="status"
    aria-live="polite"
    x-data
>
    <template x-for="toast in $store.toasts.items" :key="toast.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-3 sm:translate-x-6 sm:translate-y-0"
            x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-6"
            class="pointer-events-auto relative w-full max-w-sm overflow-hidden rounded-xl text-white shadow-[var(--shadow-elevated)]"
            :class="{
                'bg-success-600': toast.type === 'success',
                'bg-danger-600': toast.type === 'error',
                'bg-warning-600': toast.type === 'warning',
                'bg-info-600': (toast.type === 'info' || !['success','error','warning'].includes(toast.type)),
            }"
        >
            <div class="flex items-start gap-3 px-4 py-3">
                <div class="mt-0.5 shrink-0">
                    <template x-if="toast.type === 'success'"><x-admin.icon name="circle-check" :size="18" /></template>
                    <template x-if="toast.type === 'error'"><x-admin.icon name="alert-circle" :size="18" /></template>
                    <template x-if="toast.type === 'warning'"><x-admin.icon name="alert-triangle" :size="18" /></template>
                    <template x-if="!['success','error','warning'].includes(toast.type)"><x-admin.icon name="info-circle" :size="18" /></template>
                </div>
                <p class="min-w-0 flex-1 text-sm font-semibold leading-snug" x-text="toast.message"></p>
                <button
                    type="button"
                    x-on:click="$store.toasts.dismiss(toast.id)"
                    class="-mr-1 shrink-0 rounded-md p-1 text-white/70 transition hover:bg-white/15 hover:text-white"
                    aria-label="Tutup"
                >
                    <x-admin.icon name="x" :size="16" />
                </button>
            </div>
            {{-- Progress bar --}}
            <div
                class="h-1 origin-left bg-white/40 motion-safe:animate-none"
                x-init="$el.style.animation = `toastProgress ${toast.timeout}ms linear forwards`"
            ></div>
        </div>
    </template>
</div>
