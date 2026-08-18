@props(['resource'])

<div
    x-show="selected.length > 0"
    x-transition:enter="transition-[opacity,transform] ease-out duration-150"
    x-transition:enter-start="opacity-0 -translate-y-1"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition-[opacity,transform] ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-1"
    class="flex flex-col gap-3 border-b border-brand-200 bg-brand-50/80 px-4 py-3 dark:border-brand-900 dark:bg-brand-950/35 sm:flex-row sm:items-center sm:justify-between sm:px-6"
    style="display: none;"
    aria-live="polite"
>
    <div class="flex min-w-0 items-center gap-3">
        <div class="grid size-9 shrink-0 place-items-center rounded-xl bg-brand-700 text-sm font-bold text-white shadow-sm" x-text="selected.length" aria-hidden="true"></div>
        <div class="min-w-0">
            <p class="text-sm font-semibold text-slate-900 dark:text-white"><span x-text="selected.length"></span> item dipilih</p>
            <button type="button" x-on:click="selected = []; selectAll = false" class="mt-0.5 rounded text-xs font-medium text-slate-600 outline-none transition-colors duration-150 hover:text-brand-800 focus-visible:text-brand-800 dark:text-slate-300 dark:hover:text-brand-200 dark:focus-visible:text-brand-200">
                Batalkan pilihan
            </button>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <x-admin.button variant="secondary" size="sm" icon="download" x-on:click="bulkExport()">Export terpilih</x-admin.button>
        <x-admin.button variant="danger" size="sm" icon="trash" x-on:click="$dispatch('open-modal', 'bulk-delete')">Hapus</x-admin.button>
    </div>
</div>

<x-admin.modal name="bulk-delete" title="Hapus Data Terpilih" variant="danger" max-width="md">
    <p class="text-sm leading-6 text-slate-600 dark:text-slate-300">
        <span x-text="selected.length"></span> data terpilih akan dihapus permanen. Aksi ini tidak bisa dibatalkan.
    </p>
    <x-slot:footer>
        <x-admin.button variant="ghost" x-on:click="closeModal()">Batal</x-admin.button>
        <form method="POST" action="{{ route('admin.resources.bulk-delete', $resource['slug']) }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
            @csrf
            @method('DELETE')
            <template x-for="id in selected" :key="id">
                <input type="hidden" name="ids[]" :value="id">
            </template>
            <x-admin.button type="submit" variant="danger" size="sm" icon="trash" x-bind:disabled="submitting" x-bind:loading="submitting" loading-text="Menghapus…">
                <span x-text="submitting ? 'Menghapus…' : 'Ya, hapus'"></span>
            </x-admin.button>
        </form>
    </x-slot:footer>
</x-admin.modal>
