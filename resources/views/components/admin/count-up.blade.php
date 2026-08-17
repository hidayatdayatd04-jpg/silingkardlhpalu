@props([
    'value' => 0,          // angka target
    'duration' => 1200,    // ms
    'format' => true,      // format ribuan id-ID
    'prefix' => '',
    'suffix' => '',
])

@php
    $target = (float) $value;
@endphp

<span
    {{ $attributes }}
    x-data="{
        target: {{ $target }},
        display: 0,
        fmt(n) {
            const v = Math.round(n);
            return {{ $format ? 'true' : 'false' }} ? new Intl.NumberFormat('id-ID').format(v) : v;
        },
        run() {
            const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (reduce || this.target === 0) { this.display = this.target; return; }
            const dur = {{ (int) $duration }};
            let start = null;
            const step = (ts) => {
                if (start === null) start = ts;
                const p = Math.min((ts - start) / dur, 1);
                const eased = 1 - Math.pow(1 - p, 3);
                this.display = this.target * eased;
                if (p < 1) requestAnimationFrame(step);
                else this.display = this.target;
            };
            requestAnimationFrame(step);
        }
    }"
    x-init="run()"
><span aria-hidden="true">{{ $prefix }}<span x-text="fmt(display)">{{ $format ? number_format($target) : $target }}</span>{{ $suffix }}</span><span class="sr-only">{{ $prefix }}{{ $format ? number_format($target) : $target }}{{ $suffix }}</span></span>
