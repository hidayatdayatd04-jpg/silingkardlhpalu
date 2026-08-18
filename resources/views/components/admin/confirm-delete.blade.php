@props([
    'action',
    'method' => 'DELETE',
    'title' => 'Hapus Data',
    'message' => 'Data ini akan dihapus permanen. Aksi ini tidak bisa dibatalkan.',
    'confirmText' => 'Hapus',
    'cancelText' => 'Batal',
    'name' => null,
])

@php
    $name = $name ?? 'confirm-delete-' . Str::random(6);
@endphp

<x-admin.modal :name="$name" :title="$title" variant="danger" max-width="md">
    <p class="text-sm leading-relaxed text-ink-700 dark:text-slate-200">{{ $message }}</p>
    {{ $slot }}

    <x-slot:footer>
        <button
            type="button"
            data-modal-autofocus
            x-on:click="closeModal()"
            class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition-[background-color,border-color,color] duration-150 hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400/60 dark:border-white/[.1] dark:bg-white/[.04] dark:text-slate-200 dark:hover:bg-white/[.08]"
        >
            {{ $cancelText }}
        </button>

        <form
            method="POST"
            action="{{ $action }}"
            x-data="{ submitting: false }"
            x-on:submit="submitting = true"
            x-bind:aria-busy="submitting"
        >
            @csrf
            @method($method)
            {{ $form ?? '' }}
            <button
                type="submit"
                x-bind:disabled="submitting"
                class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-danger-600 px-4 py-2.5 text-sm font-bold text-white shadow-[0_8px_20px_-8px_rgba(225,29,72,0.6)] transition-[background-color,box-shadow,opacity] duration-150 hover:bg-danger-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-danger-400 focus-visible:ring-offset-2 disabled:cursor-wait disabled:opacity-60 dark:focus-visible:ring-offset-slate-900"
            >
                <x-admin.icon x-show="submitting" x-cloak name="loader" :size="16" class="animate-spin" aria-hidden="true" />
                <x-admin.icon x-show="!submitting" name="trash" :size="16" aria-hidden="true" />
                <span x-text="submitting ? 'Menghapus...' : @js($confirmText)"></span>
            </button>
        </form>
    </x-slot:footer>
</x-admin.modal>
