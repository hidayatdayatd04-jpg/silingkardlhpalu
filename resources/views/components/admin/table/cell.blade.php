@props(['truncate' => false])

<td {{ $attributes->merge(['class' => 'px-5 py-4 font-medium text-slate-700 ' . ($truncate ? 'max-w-[260px] truncate' : '')]) }}>
    {{ $slot }}
</td>
