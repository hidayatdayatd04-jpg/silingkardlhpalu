{{-- Widget progres backup/restore latar belakang.
     Dipasang di layout admin — polling endpoint progres tiap 2 detik selama task aktif,
     menampilkan kartu mengambang dengan persentase 1–100% dan tombol batalkan.
     Saat task selesai (done/failed/cancelled), hasil ditampilkan lewat toast.

      Logika komponen terdaftar via Alpine.data('backupProgressWidget') di
      resources/js/alpine.js (di dalam bootstrapAlpine(), SEBELUM Alpine.start())
      — JANGAN pindahkan ke <script> inline, karena registrasi harus terjadi
      sebelum Alpine memindai DOM. --}}
<div
    x-data="backupProgressWidget"
    x-cloak
    x-show="visible"
    x-transition:enter="transition-[opacity,transform] ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition-[opacity,transform] ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4"
    class="fixed bottom-4 right-4 z-[115] w-[calc(100vw-2rem)] max-w-sm overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[var(--shadow-elevated)] dark:border-slate-700 dark:bg-slate-900"
    data-progress-url="{{ route('admin.backup.progress') }}"
    data-cancel-url="{{ route('admin.backup.cancel') }}"
    role="status"
    aria-live="polite"
>
    <div class="flex items-start gap-3 px-4 pt-3.5">
        <div class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-600">
            <template x-if="task && task.type === 'restore'">
                <x-admin.icon name="refresh" :size="17" />
            </template>
            <template x-if="!task || task.type !== 'restore'">
                <x-admin.icon name="database" :size="17" />
            </template>
        </div>

        <div class="min-w-0 flex-1">
            <div class="flex items-center justify-between gap-2">
                <p class="truncate text-sm font-bold text-slate-950 dark:text-white" x-text="title"></p>
                <span class="shrink-0 font-mono text-sm font-bold text-brand-600" x-text="(task ? task.percent : 0) + '%'"></span>
            </div>
            <p class="mt-0.5 truncate text-[11px] text-slate-500" x-text="task ? (task.label || '') : ''"></p>
        </div>
    </div>

    {{-- Progress bar --}}
    <div class="mx-4 mt-3 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
        <div
            class="h-full rounded-full bg-brand-600 transition-[width] duration-500 ease-out"
            :style="'width:' + (task ? task.percent : 0) + '%'"
        ></div>
    </div>

    <div class="flex items-center justify-between gap-2 px-4 py-3">
        <p class="text-[11px] text-slate-400" x-show="stalePending" x-cloak>
            Menunggu antrian — pastikan queue worker berjalan.
        </p>
        <p class="text-[11px] text-slate-400" x-show="!stalePending">
            Progres diperbarui otomatis.
        </p>

        <button
            type="button"
            x-on:click="cancel()"
            :disabled="cancelling"
            class="inline-flex min-h-8 shrink-0 items-center gap-1.5 rounded-lg border border-danger-200 bg-danger-50 px-2.5 py-1.5 text-[11px] font-bold text-danger-600 outline-none transition-[background-color,border-color,color] duration-150 hover:bg-danger-100 focus-visible:ring-2 focus-visible:ring-danger-500/30 disabled:opacity-60 dark:border-danger-900/70 dark:bg-danger-950/35 dark:text-danger-300 dark:hover:bg-danger-950/60"
        >
            <x-admin.icon name="loader" :size="14" x-show="cancelling" class="animate-spin" aria-hidden="true" />
            <span x-text="cancelling ? 'Membatalkan…' : 'Batalkan'"></span>
        </button>
    </div>
</div>
