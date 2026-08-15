@props(['truncate' => false])

<td {{ $attributes->merge(['class' => 'px-5 py-3.5 align-middle font-medium text-ink-700 first:rounded-l-xl last:rounded-r-xl ' . ($truncate ? 'max-w-[260px] truncate' : '')]) }}>
    {{ $slot }}
</td>
