{{-- Host toast untuk Alpine store `toasts`; window.showToast() tetap memakai store ini. --}}
<div
    class="pointer-events-none fixed inset-x-0 bottom-0 z-[120] flex flex-col items-end gap-2 p-4 sm:p-6"
    aria-live="polite"
    aria-relevant="additions"
    x-data
>
    <template x-for="toast in $store.toasts.items" :key="toast.id">
        <div
            x-show="true"
            x-transition:enter="transition-[opacity,transform] ease-out duration-200"
            x-transition:enter-start="translate-y-3 opacity-0 sm:translate-x-6 sm:translate-y-0"
            x-transition:enter-end="translate-x-0 translate-y-0 opacity-100"
            x-transition:leave="transition-[opacity,transform] ease-in duration-150"
            x-transition:leave-start="translate-x-0 opacity-100"
            x-transition:leave-end="translate-y-2 opacity-0 sm:translate-x-6 sm:translate-y-0"
            class="pointer-events-auto relative w-full max-w-sm overflow-hidden rounded-xl text-white shadow-[var(--shadow-elevated)]"
            :class="{
                'bg-success-600': toast.type === 'success',
                'bg-danger-600': toast.type === 'error',
                'bg-warning-600': toast.type === 'warning',
                'bg-info-600': (toast.type === 'info' || !['success', 'error', 'warning'].includes(toast.type)),
            }"
            :role="toast.type === 'error' ? 'alert' : 'status'"
            aria-atomic="true"
        >
            <div class="flex items-start gap-3 px-4 py-3">
                <span class="mt-0.5 shrink-0" aria-hidden="true">
                    <template x-if="toast.type === 'success'"><x-admin.icon name="circle-check" :size="18" /></template>
                    <template x-if="toast.type === 'error'"><x-admin.icon name="alert-circle" :size="18" /></template>
                    <template x-if="toast.type === 'warning'"><x-admin.icon name="alert-triangle" :size="18" /></template>
                    <template x-if="!['success', 'error', 'warning'].includes(toast.type)"><x-admin.icon name="info-circle" :size="18" /></template>
                </span>
                <p class="min-w-0 flex-1 text-sm font-semibold leading-snug" x-text="toast.message"></p>
                <button
                    type="button"
                    x-on:click="$store.toasts.dismiss(toast.id)"
                    class="-mr-1 grid size-8 shrink-0 place-items-center rounded-md text-white/75 transition-[background-color,color] duration-150 hover:bg-white/15 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/80"
                    aria-label="Tutup notifikasi"
                >
                    <x-admin.icon name="x" :size="16" />
                </button>
            </div>
            <div class="h-1 origin-left bg-white/35" aria-hidden="true" x-init="$el.style.animation = `toastProgress ${toast.timeout}ms linear forwards`"></div>
        </div>
    </template>
</div>
