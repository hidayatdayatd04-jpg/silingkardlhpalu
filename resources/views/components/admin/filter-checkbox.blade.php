@props(['label', 'value', 'checked' => false])

<label class="flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2 transition hover:bg-slate-50">
    <input 
        type="checkbox" 
        value="{{ $value }}"
        {{ $checked ? 'checked' : '' }}
        {{ $attributes->merge(['class' => 'size-4 rounded border-slate-300 text-emerald-600 transition focus:ring-2 focus:ring-emerald-500 focus:ring-offset-0']) }}
    >
    <span class="flex-1 text-sm font-medium text-slate-700">{{ $label }}</span>
    @if($slot->isNotEmpty())
        <span class="text-xs text-slate-500">{{ $slot }}</span>
    @endif
</label>
