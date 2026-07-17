@props(['responsive' => true])

<div {{ $attributes->merge(['class' => 'overflow-x-auto']) }}>
    <table class="w-full {{ $responsive ? 'min-w-[820px]' : '' }} text-left text-sm">
        {{ $slot }}
    </table>
</div>
