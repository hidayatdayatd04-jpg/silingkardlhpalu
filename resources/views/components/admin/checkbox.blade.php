@props([
    'label' => '',
    'description' => null,
    'checked' => false
])

<label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 px-4 py-3 transition hover:border-emerald-300 hover:bg-emerald-50/50">
    <input 
        type="checkbox"
        {{ $checked ? 'checked' : '' }}
        {{ $attributes->merge(['class' => 'mt-0.5 size-4 rounded border-slate-300 text-emerald-600 transition focus:ring-2 focus:ring-emerald-500 focus:ring-offset-0']) }}
    >
    <div class="flex-1">
        <span class="text-sm font-bold text-slate-700">{{ $label }}</span>
        @if($description)
            <p class="mt-0.5 text-xs text-slate-500">{{ $description }}</p>
        @endif
    </div>
</label>
