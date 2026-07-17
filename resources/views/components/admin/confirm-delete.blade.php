@props([
    'action',                       // URL form destroy (route)
    'method' => 'DELETE',
    'title' => 'Hapus Data',
    'message' => 'Data ini akan dihapus permanen. Aksi ini tidak bisa dibatalkan.',
    'confirmText' => 'Hapus',
    'cancelText' => 'Batal',
    'name' => null,                 // wajib bila dipicu dari luar via $dispatch('open-modal', '<name>')
])

@php
    $name = $name ?? 'confirm-delete-' . Str::random(6);
@endphp

<x-admin.modal :name="$name" :title="$title" variant="danger" max-width="md">
    <p class="text-sm leading-relaxed text-ink-600">{{ $message }}</p>
    {{ $slot }}

    <x-slot:footer>
        <button
            type="button"
            x-on:click="closeModal()"
            class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300"
        >
            {{ $cancelText }}
        </button>

        <form
            method="POST"
            action="{{ $action }}"
            x-data="{ submitting: false }"
            x-on:submit="submitting = true"
        >
            @csrf
            @method($method)
            {{ $form ?? '' }}
            <button
                type="submit"
                :disabled="submitting"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-danger-600 px-4 py-2.5 text-sm font-bold text-white shadow-[0_8px_20px_-8px_rgba(225,29,72,0.6)] transition hover:bg-danger-700 focus:outline-none focus:ring-4 focus:ring-danger-100 disabled:opacity-60"
            >
                <svg x-show="submitting" x-cloak class="size-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <x-admin.icon x-show="!submitting" name="trash" :size="16" />
                <span x-text="submitting ? 'Menghapus...' : @js($confirmText)"></span>
            </button>
        </form>
    </x-slot:footer>
</x-admin.modal>
