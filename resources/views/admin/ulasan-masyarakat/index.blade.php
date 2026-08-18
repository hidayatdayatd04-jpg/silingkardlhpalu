@extends('layouts.admin')

@section('title', 'Ulasan Masyarakat - Admin DLH')
@section('heading', 'Ulasan Masyarakat')

@section('content')
    <x-admin.page-header
        title="Ulasan Masyarakat"
        subtitle="Ringkasan rating dan komentar dari masyarakat"
    />

    {{-- Summary Cards --}}
    <section class="grid gap-5 md:grid-cols-3 mb-8">
        <div data-animate class="stagger-item" style="--reveal-delay: 0ms"><x-admin.stat-card
            label="Total Ulasan"
            :value="$totalFeedback"
            icon="star"
            color="amber"
        /></div>
        <div data-animate class="stagger-item" style="--reveal-delay: 60ms"><x-admin.stat-card
            label="Rata-rata Rating"
            :value="number_format($avgOverall, 1)"
            icon="trending-up"
            color="emerald"
        /></div>
        <div data-animate class="stagger-item" style="--reveal-delay: 120ms"><x-admin.stat-card
            label="Jenis Layanan"
            :value="$ratingPerBidang->count()"
            icon="folder"
            color="sky"
        /></div>
    </section>

    {{-- Rating per Bidang --}}
    <div data-animate class="stagger-item mb-8"><x-admin.card class="mb-8">
        <div class="mb-4">
            <h2 class="text-h4 font-bold text-ink-900">Rating per Jenis Layanan</h2>
            <p class="text-xs text-slate-500">Rata-rata penilaian masyarakat per bidang</p>
        </div>

        @if ($ratingPerBidang->isEmpty())
            <p class="text-sm text-slate-500">Belum ada ulasan dari masyarakat.</p>
        @else
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($ratingPerBidang as $key => $data)
                    <div class="card-lift rounded-lg border border-slate-200 dark:border-slate-800 p-4">
                        <p class="text-sm font-semibold text-ink-800 mb-2">{{ $data['label'] }}</p>
                        <div class="flex items-center gap-2 mb-1">
                            <div class="flex items-center gap-0.5">
                                @for ($i = 1; $i <= 5; $i++)
                                    <x-admin.icon name="star" :size="16" filled class="{{ $i <= round($data['avg_rating']) ? 'text-amber-400' : 'text-slate-300' }}" />
                                @endfor
                            </div>
                            <span class="text-lg font-bold text-ink-900">{{ $data['avg_rating'] }}</span>
                        </div>
                        <p class="text-xs text-slate-500">{{ $data['total'] }} ulasan</p>
                    </div>
                @endforeach
            </div>
        @endif
    </x-admin.card>
    </div>

    {{-- Recent Comments --}}
    <div><x-admin.card>
        <div class="mb-4">
            <h2 class="text-h4 font-bold text-ink-900">Komentar Terbaru</h2>
            <p class="text-xs text-slate-500">Ulasan masyarakat yang menyertakan komentar</p>
        </div>

        @if ($recentFeedback->isEmpty())
            <p class="text-sm text-slate-500">Belum ada komentar dari masyarakat.</p>
        @else
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach ($recentFeedback as $fb)
                    <div class="py-4">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="flex items-center gap-0.5">
                                @for ($i = 1; $i <= 5; $i++)
                                    <x-admin.icon name="star" :size="14" filled class="{{ $i <= $fb['rating'] ? 'text-amber-400' : 'text-slate-300' }}" />
                                @endfor
                            </div>
                            <span class="text-xs font-mono font-bold text-slate-600">{{ $fb['ticket_number'] }}</span>
                            <span class="text-xs text-slate-400">•</span>
                            <span class="text-xs text-slate-500">{{ $fb['model_label'] }}</span>
                            <span class="text-xs text-slate-400">•</span>
                            <span class="text-xs text-slate-400">{{ $fb['created_at']->diffForHumans() }}</span>
                        </div>
                        <p class="text-sm text-slate-700 dark:text-slate-300">{{ $fb['komentar'] }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </x-admin.card>
</div>
@endsection
