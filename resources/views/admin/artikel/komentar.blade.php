@extends('layouts.admin')

@section('title', 'Komentar — '.$artikel->judul.' - Admin DLH')
@section('heading', 'Komentar Artikel')

@php
  use Illuminate\Support\Str;

  $sortLabels = ['terbaru' => 'Terbaru', 'terlama' => 'Terlama', 'teratas' => 'Paling disukai'];
  $statusLabels = [
    '' => 'Semua status',
    'visible' => 'Tayang',
    'hidden' => 'Disembunyikan',
    'pinned' => 'Disematkan',
    'admin' => 'Dari admin',
  ];
  $activeStatus = (string) request('status', '');
  $hasFilter = request()->filled('q') || $activeStatus !== '' || ($sort ?? 'terbaru') !== 'terbaru';
  $statusOptions = collect($statusLabels)->except('')->all();
  $filterCount = ($activeStatus !== '' ? 1 : 0) + (($sort ?? 'terbaru') !== 'terbaru' ? 1 : 0);

  // Warna avatar mengikuti halaman publik (components/public/komentar-artikel.blade.php):
  // admin hitam, Anonim abu-abu, nama lain di-hash deterministik ke palet warna.
  $avatarTone = function (?string $name, bool $isAdmin): string {
    if ($isAdmin) return 'bg-slate-900 text-white dark:bg-white dark:text-slate-900';
    $name = trim((string) $name);
    if ($name === '' || strcasecmp($name, 'Anonim') === 0) return 'bg-slate-400 text-white';
    $palette = ['bg-sky-500', 'bg-violet-500', 'bg-emerald-500', 'bg-amber-500', 'bg-rose-500', 'bg-cyan-600', 'bg-indigo-500'];
    $h = 0;
    for ($i = 0, $len = mb_strlen($name); $i < $len; $i++) {
      $h = ($h * 31 + (int) mb_ord(mb_substr($name, $i, 1))) % count($palette);
    }
    return $palette[$h].' text-white';
  };
@endphp

@section('content')
<div class="space-y-6" x-data="{ replyId: null, replyName: '', pin: true }">

  <x-admin.page-header
    :title="'Komentar: '.$artikel->judul"
    subtitle="Kelola diskusi publik pada artikel ini."
    icon="message"
    :breadcrumbs="[['label'=>'Artikel','url'=>route('admin.resources.index','artikel')],['label'=>'Komentar']]">
    <x-slot:actions>
      <a href="{{ route('admin.resources.show',['artikel',$artikel]) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
        <x-admin.icon name="arrow-left" :size="16" aria-hidden="true" />Detail Artikel
      </a>
      <a href="{{ url('/berita/'.$artikel->slug) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-slate-700 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200">
        <x-admin.icon name="external-link" :size="16" aria-hidden="true" />Lihat Publik
      </a>
    </x-slot:actions>
  </x-admin.page-header>

  {{-- ── statistik ── --}}
  <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
    <x-admin.stat-card label="Total" :value="$stats['total']" icon="message" color="emerald" :sublabel="$stats['root'].' utama · '.$stats['reply'].' balasan'" />
    <x-admin.stat-card label="Tayang" :value="$stats['visible']" icon="eye" color="teal" sublabel="Terlihat publik" />
    <x-admin.stat-card label="Disembunyikan" :value="$stats['hidden']" icon="eye-off" color="amber" sublabel="Tidak tampil publik" />
    <x-admin.stat-card label="Disematkan" :value="$stats['pinned']" icon="star" color="purple" :sublabel="$stats['admin'].' komentar admin'" />
  </div>

  {{-- ── composer admin ── --}}
  <x-admin.card>
    <div class="flex flex-wrap items-start justify-between gap-3">
      <div>
        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Tulis sebagai Admin</h3>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
          Komentar admin ditandai lencana <span class="font-semibold">Penulis</span> di halaman publik.
        </p>
      </div>
      <span x-show="replyId" x-cloak class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800 dark:bg-amber-950/40 dark:text-amber-200">
        <x-admin.icon name="message-circle" :size="14" aria-hidden="true" />
        Membalas <span x-text="replyName" class="font-bold"></span>
        <button type="button" @click="replyId=null; replyName=''" class="rounded-full bg-white px-2 py-0.5 text-[11px] font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-200">Batal</button>
      </span>
    </div>

    <form method="POST" action="{{ route('admin.artikel.komentar.store',$artikel->id) }}" class="mt-4 space-y-3">
      @csrf
      <input type="hidden" name="parent_id" :value="replyId">
      <input type="hidden" name="pin" :value="replyId ? 0 : (pin ? 1 : 0)">

      <x-public.textarea
        id="admin-komentar-body"
        name="body"
        label="Komentar"
        placeholder="Tulis komentar atau balasan sebagai admin..."
        rows="3"
        required
        maxlength="2000"
        icon="message"
      />
      @error('parent_id')<p class="text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
      @error('pin')<p class="text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror

      <div class="flex flex-wrap items-center justify-between gap-3">
        <div x-show="!replyId" x-cloak class="flex items-center gap-3">
          <button type="button" @click="pin = !pin"
            class="spring-toggle" :class="{ 'is-on': pin }"
            role="switch" :aria-checked="pin ? 'true' : 'false'" aria-label="Sematkan di paling atas">
            <span class="spring-track"></span>
            <span class="spring-thumb"><x-admin.icon name="check" :size="14" /></span>
          </button>
          <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">Sematkan di paling atas</span>
        </div>
        <span x-show="replyId" x-cloak class="text-xs text-slate-500 dark:text-slate-400">Balasan tidak dapat disematkan.</span>
        <button type="submit" class="ml-auto inline-flex items-center gap-2 rounded-full bg-gradient-to-br from-emerald-600 to-teal-500 px-5 py-2.5 text-sm font-bold text-white shadow transition-transform active:scale-[0.98]">
          <x-admin.icon name="send" :size="15" aria-hidden="true" />
          <span x-text="replyId ? 'Kirim Balasan' : 'Kirim Komentar'">Kirim Komentar</span>
        </button>
      </div>
    </form>
  </x-admin.card>

  {{-- ── daftar komentar ── --}}
  <x-admin.card :padding="false" class="overflow-hidden">

    <form method="GET" class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/70 px-4 py-3 dark:border-slate-800 dark:bg-slate-800/40 sm:flex-row sm:items-center sm:px-6">
      <div class="relative max-w-md flex-1">
        <x-admin.icon name="search" :size="16" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true" />
        <label for="filter-q" class="sr-only">Cari komentar</label>
        <input id="filter-q" name="q" value="{{ request('q') }}" placeholder="Cari isi komentar atau nama..."
          class="h-10 w-full rounded-xl border border-slate-200 bg-white pl-9 pr-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
      </div>

      <x-admin.filter-dropdown label="Filter" :active="$filterCount > 0" :count="$filterCount">
        <x-admin.filter-section title="Status">
          <div class="px-1">
            <x-admin.select name="status" :options="$statusOptions" :selected="$activeStatus" placeholder="Semua status" />
          </div>
        </x-admin.filter-section>
        <x-admin.filter-section title="Urutkan">
          <div class="px-1">
            <x-admin.select name="sort" :options="$sortLabels" :selected="$sort ?? 'terbaru'" placeholder="Urutkan" />
          </div>
        </x-admin.filter-section>
        <x-admin.filter-actions :resetUrl="route('admin.artikel.komentar.index',$artikel->id)" />
      </x-admin.filter-dropdown>
    </form>

    <div class="flex items-center justify-between border-b border-slate-100 px-4 py-2 sm:px-6">
      <div class="flex items-center gap-3">
        <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 dark:text-slate-300">
          <input type="checkbox" id="komentar-select-all" class="size-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500/30 dark:border-slate-600 dark:bg-slate-800">
          Pilih semua
        </label>
        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Menampilkan {{ $komentars->count() }} dari {{ $komentars->total() }} komentar utama · {{ Str::lower($sortLabels[$sort ?? 'terbaru']) }}</span>
      </div>
      <form id="komentar-bulk-form" method="POST" action="{{ route('admin.artikel.komentar.bulkDestroy',$artikel->id) }}" onsubmit="return confirm('Hapus ' + document.querySelectorAll('.komentar-check:checked').length + ' komentar terpilih beserta balasannya?')" class="hidden">
        @csrf @method('DELETE')
        <div id="komentar-bulk-ids"></div>
        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-rose-700">
          <x-admin.icon name="trash" :size="13" /> Hapus terpilih (<span id="komentar-bulk-count">0</span>)
        </button>
      </form>
    </div>

    <div id="komentar-list-admin" class="divide-y divide-slate-100 dark:divide-slate-800">
      @forelse($komentars as $k)
        @php
          $rowTone = $k->is_hidden
            ? 'bg-rose-50/40 dark:bg-rose-950/10'
            : ($k->is_pinned ? 'bg-amber-50/40 dark:bg-amber-950/10' : 'bg-white dark:bg-slate-900');
        @endphp
        <div class="{{ $rowTone }}">
          <div class="flex gap-3 px-4 py-3.5 sm:px-6">

            <label class="shrink-0 pt-1">
              <input type="checkbox" value="{{ $k->id }}" class="komentar-check size-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500/30 dark:border-slate-600 dark:bg-slate-800">
            </label>

            <div class="grid size-9 shrink-0 place-items-center rounded-full text-[11px] font-black {{ $avatarTone($k->display_name, $k->is_admin) }}">
              {{ Str::upper(Str::substr($k->initials, 0, 2)) }}
            </div>

            <div class="min-w-0 flex-1">
              {{-- header: nama + lencana + waktu --}}
              <div class="flex flex-wrap items-center gap-x-1.5 gap-y-0.5">
                <span class="text-[13.5px] font-bold leading-tight text-slate-900 dark:text-white">{{ $k->display_name }}</span>
                @if($k->is_admin)
                  <span class="inline-flex items-center gap-1 rounded-md bg-slate-900 px-1.5 py-[2px] text-[9.5px] font-bold uppercase tracking-wide leading-none text-white dark:bg-white dark:text-slate-900">Penulis<span class="grid size-3 place-items-center rounded-full bg-sky-500 text-white"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l4 4L19 6"/></svg></span></span>
                @endif
                @if($k->is_pinned)
                  <span class="rounded-full bg-amber-400 px-1.5 py-px text-[9.5px] font-black uppercase tracking-wider leading-[1.5] text-amber-950">Semat</span>
                @endif
                @if($k->is_hidden)
                  <span class="rounded-full bg-rose-500 px-1.5 py-px text-[9.5px] font-black uppercase tracking-wider leading-[1.5] text-white">Hidden</span>
                @endif
                <span class="text-[11.5px] font-medium text-slate-400 dark:text-slate-500"
                  title="{{ $k->created_at->translatedFormat('d M Y, H:i') }}@if($k->ip_address) · IP {{ $k->ip_address }}@endif">· {{ $k->time_ago }}</span>
              </div>

              {{-- isi --}}
              <p class="mt-1 whitespace-pre-wrap break-words text-[13.5px] leading-[1.55] text-slate-700 dark:text-slate-200">{{ $k->body }}</p>

              @if($k->is_hidden)
                <p class="mt-1 inline-flex items-center gap-1 text-[11px] font-semibold text-rose-600/90 dark:text-rose-400/90">
                  <x-admin.icon name="eye-off" :size="12" aria-hidden="true" />Disembunyikan dari halaman publik
                </p>
              @endif

              {{-- reaksi + aksi --}}
              <div class="mt-2 flex flex-wrap items-center gap-x-0.5 gap-y-1">
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11.5px] font-semibold text-slate-400 transition-colors hover:bg-rose-50 hover:text-rose-500 dark:text-slate-500 dark:hover:bg-rose-950/30" title="Suka (love)">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 20.6c-.3 0-.6-.1-.8-.3l-6.1-5.9A5.3 5.3 0 0 1 12 6.9a5.3 5.3 0 0 1 6.9 7.5l-6.1 5.9c-.2.2-.5.3-.8.3Z"/></svg>
                  {{ $k->loves_count }}
                </span>
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11.5px] font-semibold text-slate-400 transition-colors hover:bg-indigo-50 hover:text-indigo-500 dark:text-slate-500 dark:hover:bg-indigo-950/30" title="Tidak suka (like)">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7.4 3.5h7.3a3 3 0 0 1 2.93 2.35l.94 4.3A2.4 2.4 0 0 1 16.23 13H13.2l.55 3.06a2.35 2.35 0 0 1-2.31 2.77.9.9 0 0 1-.83-.55L7.4 11.3V3.5Z"/><path d="M4.6 3.5h2.8v7.8H4.6a1.4 1.4 0 0 1-1.4-1.4V4.9a1.4 1.4 0 0 1 1.4-1.4Z"/></svg>
                  {{ $k->likes_count }}
                </span>

                <span class="mx-1 h-4 w-px shrink-0 bg-slate-200 dark:bg-slate-700"></span>

                <button type="button"
                  @click="replyId={{ $k->id }}; replyName={{ Illuminate\Support\Js::from($k->display_name) }}; document.getElementById('admin-komentar-body').focus(); window.scrollTo({top:0,behavior:'smooth'})"
                  class="rounded-full px-2 py-1 text-[12px] font-bold text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white">Balas</button>

                <form method="POST" action="{{ route('admin.artikel.komentar.hide',[$artikel->id,$k->id]) }}">@csrf
                  <button type="submit" class="rounded-full px-2 py-1 text-[12px] font-bold transition-colors {{ $k->is_hidden ? 'text-emerald-600 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-950/30' : 'text-slate-500 hover:bg-slate-100 hover:text-amber-600 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-amber-400' }}">{{ $k->is_hidden ? 'Tampilkan' : 'Sembunyikan' }}</button>
                </form>

                <form method="POST" action="{{ route('admin.artikel.komentar.pin',[$artikel->id,$k->id]) }}">@csrf
                  <button type="submit" class="rounded-full px-2 py-1 text-[12px] font-bold transition-colors {{ $k->is_pinned ? 'text-violet-600 hover:bg-violet-50 dark:text-violet-400 dark:hover:bg-violet-950/30' : 'text-slate-500 hover:bg-slate-100 hover:text-violet-600 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-violet-400' }}">{{ $k->is_pinned ? 'Lepas Semat' : 'Sematkan' }}</button>
                </form>

                <form method="POST" action="{{ route('admin.artikel.komentar.destroy',[$artikel->id,$k->id]) }}"
                  onsubmit="return confirm({{ Illuminate\Support\Js::from($k->replies_count > 0 ? 'Hapus komentar ini beserta '.$k->replies_count.' balasannya? Tindakan ini tidak dapat dibatalkan dari panel.' : 'Hapus komentar ini? Tindakan ini tidak dapat dibatalkan dari panel.') }})">
                  @csrf @method('DELETE')
                  <button type="submit" class="rounded-full px-2 py-1 text-[12px] font-bold text-slate-500 transition-colors hover:bg-rose-50 hover:text-rose-600 dark:text-slate-400 dark:hover:bg-rose-950/30 dark:hover:text-rose-400">Hapus</button>
                </form>
              </div>

              {{-- balasan tersarang --}}
              @if($k->relationLoaded('replies') && $k->replies->isNotEmpty())
                <button type="button" data-count="{{ $k->replies->count() }}"
                  class="js-replies-toggle mt-2 inline-flex items-center gap-1 text-[12px] font-bold text-slate-500 transition-colors hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                  <svg class="js-chevron shrink-0 transition-transform duration-200" style="transform:rotate(180deg)" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
                  <span class="js-replies-label">Sembunyikan balasan</span>
                </button>
                <div class="js-replies-wrap mt-1 space-y-0.5 border-l-2 border-slate-100 pl-3 dark:border-slate-800">
                  @foreach($k->replies as $r)
                    <div class="py-1.5">
                      <div class="flex gap-2.5">
                        <div class="grid size-7 shrink-0 place-items-center rounded-full text-[9px] font-black {{ $avatarTone($r->display_name, $r->is_admin) }}">
                          {{ Str::upper(Str::substr($r->initials, 0, 2)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                          <div class="flex flex-wrap items-center gap-x-1.5 gap-y-0.5">
                            <span class="text-[12.5px] font-bold leading-tight text-slate-900 dark:text-white">{{ $r->display_name }}</span>
                            @if($r->is_admin)<span class="inline-flex items-center gap-0.5 rounded-md bg-slate-900 px-1.5 py-[2px] text-[9px] font-bold uppercase tracking-wide leading-none text-white dark:bg-white dark:text-slate-900">Penulis<span class="grid size-2.5 place-items-center rounded-full bg-sky-500 text-white"><svg width="7" height="7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l4 4L19 6"/></svg></span></span>@endif
                            @if($r->is_hidden)<span class="rounded-full bg-rose-500 px-1.5 py-px text-[9px] font-black uppercase tracking-wider leading-[1.5] text-white">Hidden</span>@endif
                            <span class="text-[11px] font-medium text-slate-400 dark:text-slate-500"
                              title="{{ $r->created_at->translatedFormat('d M Y, H:i') }}@if($r->ip_address) · IP {{ $r->ip_address }}@endif">· {{ $r->time_ago }}</span>
                          </div>
                          <p class="mt-0.5 whitespace-pre-wrap break-words text-[13px] leading-[1.5] text-slate-700 dark:text-slate-200">{{ $r->body }}</p>
                          <div class="mt-1 flex flex-wrap items-center gap-x-0.5 gap-y-1">
                            <span class="inline-flex items-center gap-1 rounded-full px-1.5 py-0.5 text-[11px] font-semibold text-slate-400 dark:text-slate-500" title="Suka (love)">
                              <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 20.6c-.3 0-.6-.1-.8-.3l-6.1-5.9A5.3 5.3 0 0 1 12 6.9a5.3 5.3 0 0 1 6.9 7.5l-6.1 5.9c-.2.2-.5.3-.8.3Z"/></svg>
                              {{ $r->loves_count }}
                            </span>
                            <span class="inline-flex items-center gap-1 rounded-full px-1.5 py-0.5 text-[11px] font-semibold text-slate-400 dark:text-slate-500" title="Tidak suka (like)">
                              <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7.4 3.5h7.3a3 3 0 0 1 2.93 2.35l.94 4.3A2.4 2.4 0 0 1 16.23 13H13.2l.55 3.06a2.35 2.35 0 0 1-2.31 2.77.9.9 0 0 1-.83-.55L7.4 11.3V3.5Z"/><path d="M4.6 3.5h2.8v7.8H4.6a1.4 1.4 0 0 1-1.4-1.4V4.9a1.4 1.4 0 0 1 1.4-1.4Z"/></svg>
                              {{ $r->likes_count }}
                            </span>
                            <span class="mx-1 h-3.5 w-px shrink-0 bg-slate-200 dark:bg-slate-700"></span>
                            <button type="button"
                              @click="replyId={{ $r->id }}; replyName={{ Illuminate\Support\Js::from($r->display_name) }}; document.getElementById('admin-komentar-body').focus(); window.scrollTo({top:0,behavior:'smooth'})"
                              class="rounded-full px-1.5 py-0.5 text-[11px] font-bold text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white">Balas</button>
                            <form method="POST" action="{{ route('admin.artikel.komentar.hide',[$artikel->id,$r->id]) }}">@csrf
                              <button type="submit" class="rounded-full px-1.5 py-0.5 text-[11px] font-bold transition-colors {{ $r->is_hidden ? 'text-emerald-600 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-950/30' : 'text-slate-500 hover:bg-slate-100 hover:text-amber-600 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-amber-400' }}">{{ $r->is_hidden ? 'Tampilkan' : 'Sembunyikan' }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.artikel.komentar.destroy',[$artikel->id,$r->id]) }}" onsubmit="return confirm('Hapus balasan ini? Tindakan ini tidak dapat dibatalkan dari panel.')">@csrf @method('DELETE')
                              <button type="submit" class="rounded-full px-1.5 py-0.5 text-[11px] font-bold text-slate-500 transition-colors hover:bg-rose-50 hover:text-rose-600 dark:text-slate-400 dark:hover:bg-rose-950/30 dark:hover:text-rose-400">Hapus</button>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
              @endif
            </div>
          </div>
        </div>
      @empty
        <div class="px-6 py-14 text-center">
          <div class="mx-auto grid size-14 place-items-center rounded-full bg-slate-100 text-slate-400 dark:bg-slate-800">
            <x-admin.icon name="message" :size="26" aria-hidden="true" />
          </div>
          <p class="mt-3 text-sm font-bold text-slate-700 dark:text-slate-200">
            {{ $hasFilter ? 'Tidak ada komentar yang cocok' : 'Belum ada komentar' }}
          </p>
          <p class="mt-1 text-sm text-slate-400 dark:text-slate-500">
            {{ $hasFilter ? 'Coba ubah kata kunci atau reset filter.' : 'Komentar publik akan muncul di sini.' }}
          </p>
        </div>
      @endforelse
    </div>

    <div id="admin-komentar-sentinel" class="flex justify-center py-4">
      <span id="admin-sentinel-text" class="hidden text-xs font-medium text-slate-400">Memuat...</span>
    </div>
  </x-admin.card>
</div>
@endsection

@push('styles')
<style>
.spring-toggle{position:relative;width:64px;height:34px;cursor:pointer;-webkit-tap-highlight-color:transparent}
.spring-track{position:absolute;inset:0;border-radius:999px;background:#CBD5E1;border:1px solid rgba(0,0,0,0.04);transition:background 0.4s ease,box-shadow 0.3s ease}
.spring-toggle.is-on .spring-track{background:#059669;box-shadow:0 0 0 1px rgba(5,150,105,0.15)}
.spring-thumb{position:absolute;top:3px;left:3px;width:28px;height:28px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;box-shadow:0 1px 3px rgba(0,0,0,0.15),0 1px 2px rgba(0,0,0,0.06);transition:left 0.45s cubic-bezier(0.34,1.56,0.64,1)}
.spring-toggle.is-on .spring-thumb{left:33px}
.spring-toggle:active .spring-thumb{width:34px;border-radius:16px}
.spring-toggle.is-on:active .spring-thumb{left:27px}
.spring-thumb svg{width:14px;height:14px;color:#94A3B8;transition:transform 0.4s ease,color 0.4s ease}
.spring-toggle.is-on .spring-thumb svg{transform:rotate(360deg);color:#059669}
/* virtualization: container tetap smooth */
#komentar-list-admin{contain:layout style; content-visibility:auto}
/* filter komentar: opsi tampil penuh tanpa scrollbar */
div[role="dialog"]{overflow-y:visible}
div[role="dialog"] .fi-select-options-scroll{max-height:none;overflow-y:visible}
</style>
@endpush

@push('scripts')
<script>
(function(){
  const listEl = document.getElementById('komentar-list-admin');
  const sentinel = document.getElementById('admin-komentar-sentinel');
  const sentinelText = document.getElementById('admin-sentinel-text');
  const selectAll = document.getElementById('komentar-select-all');
  const bulkForm = document.getElementById('komentar-bulk-form');
  const bulkIds = document.getElementById('komentar-bulk-ids');
  const bulkCount = document.getElementById('komentar-bulk-count');
  const checks = () => document.querySelectorAll('.komentar-check');

  // bulk select
  function syncBulk(){
    const all = checks();
    const sel = document.querySelectorAll('.komentar-check:checked');
    const n = sel.length;
    if(bulkCount) bulkCount.textContent = n;
    if(bulkForm) bulkForm.classList.toggle('hidden', n===0);
    if(bulkIds){
      bulkIds.innerHTML = '';
      sel.forEach(ch=>{
        const inp = document.createElement('input');
        inp.type='hidden'; inp.name='ids[]'; inp.value=ch.value;
        bulkIds.appendChild(inp);
      });
    }
    if(selectAll){
      selectAll.checked = n>0 && n===all.length;
      selectAll.indeterminate = n>0 && n<all.length;
    }
  }
  if(selectAll){
    selectAll.addEventListener('change', ()=>{
      checks().forEach(ch=> ch.checked = selectAll.checked);
      syncBulk();
    });
  }
  document.addEventListener('change', (e)=>{
    if(e.target.classList.contains('komentar-check')) syncBulk();
  });

  // toggle balasan (delegasi — baris baru dari infinite scroll tetap berfungsi)
  document.addEventListener('click', (e)=>{
    const btn = e.target.closest('.js-replies-toggle');
    if(!btn) return;
    const wrap = btn.nextElementSibling;
    if(!wrap || !wrap.classList.contains('js-replies-wrap')) return;
    const willShow = wrap.classList.contains('hidden');
    wrap.classList.toggle('hidden', !willShow);
    const label = btn.querySelector('.js-replies-label');
    const chev = btn.querySelector('.js-chevron');
    const n = btn.getAttribute('data-count') || '';
    if(label) label.textContent = willShow ? 'Sembunyikan balasan' : `${n} balasan`;
    if(chev) chev.style.transform = willShow ? 'rotate(180deg)' : 'rotate(0deg)';
  });

  // infinite scroll + cache + virtualization (admin)
  let page = {{ $komentars->currentPage() }};
  let lastPage = {{ $komentars->lastPage() }};
  let loading = false;
  let hasMore = page < lastPage;
  const baseUrl = new URL(window.location.href);
  function buildUrl(p){
    const u = new URL(baseUrl);
    u.searchParams.set('page', p);
    return u.toString();
  }
  // simple cache via sessionStorage
  const cache = new Map();
  function getCache(p){
    const k = 'admin-komentar:'+{{ $artikel->id }}+':'+new URLSearchParams(window.location.search).toString()+':'+p;
    if(cache.has(k)) return cache.get(k);
    try{
      const v = JSON.parse(sessionStorage.getItem(k));
      if(v && Date.now()-v.t < 300000){ cache.set(k,v.data); return v.data; }
    }catch(e){}
    return null;
  }
  function setCache(p, html){
    const k = 'admin-komentar:'+{{ $artikel->id }}+':'+new URLSearchParams(window.location.search).toString()+':'+p;
    const v = {t:Date.now(), data:html};
    cache.set(k, html);
    try{ sessionStorage.setItem(k, JSON.stringify(v)); }catch(e){}
  }

  async function loadMore(){
    if(loading || !hasMore) return;
    loading = true;
    if(sentinelText){ sentinelText.classList.remove('hidden'); }
    const next = page+1;
    // cache check
    const cached = getCache(next);
    if(cached){
      const temp = document.createElement('div');
      temp.innerHTML = cached;
      while(temp.firstChild){ listEl.appendChild(temp.firstChild); }
      page = next; hasMore = page < lastPage;
      if(!hasMore && sentinel) sentinel.style.display='none';
      loading=false;
      if(sentinelText) sentinelText.classList.add('hidden');
      return;
    }
    try{
      const res = await fetch(buildUrl(next), {headers:{'X-Requested-With':'XMLHttpRequest','Accept':'text/html'}});
      const text = await res.text();
      const doc = new DOMParser().parseFromString(text,'text/html');
      const newItems = doc.querySelectorAll('#komentar-list-admin > div');
      if(newItems.length===0){ hasMore=false; if(sentinel) sentinel.style.display='none'; }
      else {
        let html='';
        newItems.forEach(n=>{
          listEl.appendChild(document.importNode(n,true));
          html += n.outerHTML;
        });
        setCache(next, html);
        page = next;
        const nextLast = doc.querySelector('#admin-komentar-sentinel') ? lastPage : page;
        // update lastPage from pagination if available
        hasMore = page < lastPage;
        if(!hasMore && sentinel) sentinel.style.display='none';
      }
    }catch(e){ hasMore=false; }
    finally{ loading=false; if(sentinelText) sentinelText.classList.add('hidden'); }
  }

  if(sentinel && 'IntersectionObserver' in window){
    const obs = new IntersectionObserver((entries)=>{
      entries.forEach(ent=>{ if(ent.isIntersecting) loadMore(); });
    }, {rootMargin:'300px'});
    obs.observe(sentinel);
  } else if(listEl){
    listEl.addEventListener('scroll', ()=>{
      if(listEl.scrollTop + listEl.clientHeight >= listEl.scrollHeight - 300) loadMore();
    }, {passive:true});
    window.addEventListener('scroll', ()=>{
      if(window.innerHeight + window.scrollY >= document.body.offsetHeight - 400) loadMore();
    }, {passive:true});
  }
  // virtualization: hide offscreen via content-visibility already, plus limit DOM for >200 items
  // cache already handles fast back navigation
})();
</script>
@endpush
