@extends('layouts.admin')

@section('title', 'Kalender Jadwal Sidak')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Kalender Jadwal Sidak</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Lihat jadwal inspeksi lapangan dalam tampilan kalender</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.resources.index', 'sidak') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                <x-admin.icon name="list" :size="16" />
                Lihat Semua Sidak
            </a>
            <a href="{{ route('admin.resources.create', 'sidak') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-colors shadow-lg shadow-emerald-600/20">
                <x-admin.icon name="plus" :size="16" />
                Jadwalkan Sidak
            </a>
        </div>
    </div>

    {{-- Statistik --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
        @foreach([
            ['label' => 'Total Sidak', 'value' => $statistik['total'], 'icon' => 'clipboard-check', 'color' => 'emerald'],
            ['label' => 'Jadwal', 'value' => $statistik['jadwal'], 'icon' => 'calendar', 'color' => 'amber'],
            ['label' => 'Terlaksana', 'value' => $statistik['terlaksana'], 'icon' => 'check-circle', 'color' => 'blue'],
            ['label' => 'Belum Ditindak', 'value' => $statistik['belum'], 'icon' => 'clock', 'color' => 'orange'],
            ['label' => 'Selesai', 'value' => $statistik['selesai'], 'icon' => 'check', 'color' => 'green'],
        ] as $item)
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4">
            <div class="flex items-center gap-3">
                <div class="grid size-10 place-items-center rounded-xl bg-{{ $item['color'] }}-50 dark:bg-{{ $item['color'] }}-900/20">
                    <x-admin.icon name="{{ $item['icon'] }}" :size="20" class="text-{{ $item['color'] }}-600 dark:text-{{ $item['color'] }}-400" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $item['value'] }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $item['label'] }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Kalender --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button onclick="calendar.prev()" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                    <x-admin.icon name="chevron-left" :size="20" />
                </button>
                <h2 id="calendar-title" class="text-lg font-semibold text-slate-900 dark:text-white min-w-[200px] text-center"></h2>
                <button onclick="calendar.next()" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                    <x-admin.icon name="chevron-right" :size="20" />
                </button>
                <button onclick="calendar.today()" class="px-3 py-1.5 text-sm font-medium text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/30 transition-colors">
                    Hari Ini
                </button>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="calendar.changeView('dayGridMonth')" class="px-3 py-1.5 text-sm font-medium rounded-lg calendar-view-btn active" data-view="dayGridMonth">Bulan</button>
                <button onclick="calendar.changeView('timeGridWeek')" class="px-3 py-1.5 text-sm font-medium rounded-lg calendar-view-btn" data-view="timeGridWeek">Minggu</button>
                <button onclick="calendar.changeView('listWeek')" class="px-3 py-1.5 text-sm font-medium rounded-lg calendar-view-btn" data-view="listWeek">Daftar</button>
            </div>
        </div>
        <div id="calendar" class="p-4"></div>
    </div>

    {{-- Legend --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4">
        <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Keterangan Warna</h3>
        <div class="flex flex-wrap gap-4">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                <span class="text-sm text-slate-600 dark:text-slate-400">Belum Ditindaklanjuti</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                <span class="text-sm text-slate-600 dark:text-slate-400">Sedang Proses</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                <span class="text-sm text-slate-600 dark:text-slate-400">Selesai</span>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<style>
    .fc {
        font-family: inherit !important;
    }
    .fc .fc-toolbar-title {
        font-size: 1.125rem !important;
        font-weight: 600 !important;
    }
    .fc .fc-button {
        background: transparent !important;
        border: 1px solid #e2e8f0 !important;
        color: #475569 !important;
        border-radius: 0.5rem !important;
        padding: 0.375rem 0.75rem !important;
        font-size: 0.875rem !important;
        font-weight: 500 !important;
    }
    .fc .fc-button:hover {
        background: #f8fafc !important;
    }
    .fc .fc-button-active {
        background: #059669 !important;
        border-color: #059669 !important;
        color: white !important;
    }
    .fc .fc-daygrid-day-number {
        padding: 0.5rem !important;
    }
    .fc .fc-event {
        border-radius: 0.375rem !important;
        padding: 0.125rem 0.375rem !important;
        font-size: 0.75rem !important;
        cursor: pointer !important;
    }
    .calendar-view-btn {
        color: #64748b;
        background: transparent;
    }
    .calendar-view-btn.active {
        color: #059669;
        background: #ecfdf5;
    }
    @media (prefers-color-scheme: dark) {
        .fc .fc-button {
            border-color: #334155 !important;
            color: #94a3b8 !important;
        }
        .fc .fc-button:hover {
            background: #1e293b !important;
        }
        .fc .fc-button-active {
            background: #059669 !important;
            border-color: #059669 !important;
            color: white !important;
        }
        .fc .fc-daygrid-day-number {
            color: #e2e8f0;
        }
        .fc .fc-col-header-cell {
            background: #1e293b !important;
        }
        .calendar-view-btn.active {
            background: #064e3b;
            color: #6ee7b7;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locale/id.global.min.js"></script>
<script>
    const events = @json($events);

    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'id',
        headerToolbar: false,
        events: events,
        height: 'auto',
        eventDidMount: function(info) {
            // Tooltip
            const tooltip = document.createElement('div');
            tooltip.className = 'fc-tooltip';
            tooltip.innerHTML = `
                <div class="p-3 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 min-w-[200px]">
                    <p class="font-semibold text-slate-900 dark:text-white">${info.event.title}</p>
                    <p class="text-sm text-slate-500 mt-1">Petugas: ${info.event.extendedProps.petugas}</p>
                    <p class="text-sm text-slate-500">Hasil: ${info.event.extendedProps.hasil}</p>
                    <p class="text-sm text-slate-500">Status: ${info.event.extendedProps.status}</p>
                    ${info.event.extendedProps.is_jadwal ? '<span class="inline-block mt-2 px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-700 rounded-full">Jadwal</span>' : ''}
                </div>
            `;
            info.el.setAttribute('title', `${info.event.title}\nPetugas: ${info.event.extendedProps.petugas}\nStatus: ${info.event.extendedProps.status}`);
        },
        eventClick: function(info) {
            window.location.href = info.event.extendedProps.url;
        },
        dateClick: function(info) {
            // Redirect ke form sidak dengan tanggal yang dipilih
            window.location.href = '{{ route("admin.resources.create", "sidak") }}?tanggal=' + info.dateStr;
        },
        viewDidMount: function(view) {
            // Update title
            document.getElementById('calendar-title').textContent = view.view.title;
            // Update active button
            document.querySelectorAll('.calendar-view-btn').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.view === view.view.type);
            });
        }
    });

    calendar.render();

    // Set initial title
    document.getElementById('calendar-title').textContent = calendar.view.title;
</script>
@endpush
@endsection
