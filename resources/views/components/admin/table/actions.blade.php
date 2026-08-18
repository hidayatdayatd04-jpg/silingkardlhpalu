@props(['align' => 'right'])

<td {{ $attributes->merge(['class' => 'px-4 py-3 sm:px-5']) }}>
    <div class="flex min-w-max items-center gap-1.5 {{ $align === 'right' ? 'justify-end' : '' }}">
        {{ $slot }}
    </div>
</td>
