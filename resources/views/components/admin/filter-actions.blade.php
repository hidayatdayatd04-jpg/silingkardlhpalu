@props(['resetUrl' => null])

<div class="flex items-center gap-2 bg-slate-50 p-4">
    @if($resetUrl)
        <a 
            href="{{ $resetUrl }}"
            class="flex-1 rounded-lg border border-slate-200 bg-white px-4 py-2 text-center text-sm font-bold text-slate-700 transition hover:bg-slate-50"
        >
            Reset
        </a>
    @endif
    <button 
        type="submit"
        class="flex-1 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-emerald-700"
    >
        Terapkan Filter
    </button>
</div>
