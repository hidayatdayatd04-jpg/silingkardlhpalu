@props(['resource'])

<div
    x-show="selected.length > 0"
    x-transition
    class="flex items-center justify-between border-b border-brand-200 bg-brand-50 px-6 py-4"
    style="display: none;"
>
    <div class="flex items-center gap-3">
        <div class="grid size-10 place-items-center rounded-lg bg-brand-600 text-sm font-bold text-white" x-text="selected.length"></div>
        <div>
            <p class="text-sm font-bold text-ink-900"><span x-text="selected.length"></span> item dipilih</p>
            <button x-on:click="selected = []; selectAll = false" class="text-xs font-semibold text-slate-600 hover:text-slate-900">
                Batalkan pilihan
            </button>
        </div>
    </div>

    <div class="flex items-center gap-2">
        <x-admin.button variant="secondary" size="sm" icon="download" x-on:click="bulkExport()">
            Export Terpilih
        </x-admin.button>
        <x-admin.button variant="danger" size="sm" icon="trash" x-on:click="$dispatch('open-modal', 'bulk-delete')">
            Hapus
        </x-admin.button>
    </div>
</div>

{{-- Bulk delete confirm — form kirim selected ids via Alpine --}}
<x-admin.modal name="bulk-delete" title="Hapus Data Terpilih" variant="danger" max-width="md">
    <p class="text-sm leading-relaxed text-ink-600">
        <span x-text="selected.length"></span> data terpilih akan dihapus permanen. Aksi ini tidak bisa dibatalkan.
    </p>
    <x-slot:footer>
        <x-admin.button variant="ghost" x-on:click="closeModal()">Batal</x-admin.button>
        <form method="POST" action="{{ route('admin.resources.bulk-delete', $resource['slug']) }}"
              x-data="{ submitting: false }" x-on:submit="submitting = true">
            @csrf
            @method('DELETE')
            <template x-for="id in selected" :key="id">
                <input type="hidden" name="ids[]" :value="id">
            </template>
            <button type="submit" :disabled="submitting"
                class="inline-flex items-center gap-2 rounded-lg bg-danger-600 px-4 py-2.5 text-sm font-bold text-white shadow-[0_8px_20px_-8px_rgba(225,29,72,0.6)] transition hover:bg-danger-700 focus:outline-none focus:ring-4 focus:ring-danger-100 disabled:opacity-60">
                <x-admin.icon name="trash" :size="16" />
                <span x-text="submitting ? 'Menghapus...' : 'Ya, Hapus'"></span>
            </button>
        </form>
    </x-slot:footer>
</x-admin.modal>
