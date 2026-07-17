@props(['align' => 'right'])

<td {{ $attributes->merge(['class' => 'px-5 py-4']) }}>
    <div class="flex gap-2 {{ $align === 'right' ? 'justify-end' : '' }}">
        {{ $slot }}
    </div>
</td>
