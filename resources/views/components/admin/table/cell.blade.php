@props(['truncate' => false])

<td {{ $attributes->merge(['class' => 'px-4 py-3.5 align-middle font-medium text-slate-700 dark:text-slate-200 sm:px-5 '.($truncate ? 'max-w-[18rem] truncate' : '')]) }}>
    {{ $slot }}
</td>
