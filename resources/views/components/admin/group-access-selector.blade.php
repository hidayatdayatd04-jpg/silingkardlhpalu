@props(['user' => null, 'allGroups', 'roleDefaults' => []])

@php
    $currentAccess = old('additional_access', $user->additional_access ?? []) ?? [];
    $isSuperadmin = $user->exists && $user->isSuperadmin();
    $userRole = $user->exists ? $user->adminRole() : null;
    $defaultGroups = $userRole?->allowedGroups() ?? [];
@endphp

<div x-data="{ 
        selectedSlugs: {{ json_encode($currentAccess) }},
        defaultGroups: {{ json_encode($defaultGroups) }},
        roleDefaults: {{ json_encode($roleDefaults) }}
    }"
    @select-lainnya.window="if ($event.detail.name === 'role') { defaultGroups = (roleDefaults[$event.detail.value] || []); }"
    class="space-y-4">
    
    <div class="rounded-xl border-2 border-slate-200 bg-slate-50 p-5">
        <div class="mb-4 flex items-start justify-between">
            <div>
                <label class="text-sm font-bold text-slate-700">
                    <x-admin.icon name="shield" :size="16" class="inline" />
                    Akses Menu Bidang
                </label>
                <p class="mt-1 text-xs text-slate-500">
                    @if($isSuperadmin)
                        Administrator Utama memiliki akses penuh ke semua menu
                    @else
                        Kelola akses tambahan ke menu bidang lain, termasuk sub-menu spesifik
                    @endif
                </p>
            </div>
        </div>
        
        @if($isSuperadmin)
            {{-- Superadmin locked state --}}
            <div class="rounded-lg bg-amber-50 p-4 text-center">
                <x-admin.icon name="lock" :size="32" class="mx-auto text-amber-600" />
                <p class="mt-2 text-sm font-semibold text-amber-800">
                    Administrator Utama
                </p>
                <p class="mt-1 text-xs text-amber-700">
                    Administrator Utama memiliki akses penuh ke semua menu secara default
                </p>
            </div>
        @else
            {{-- Default Access Info --}}
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-3" x-show="defaultGroups.length > 0">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-emerald-700">
                    <x-admin.icon name="check" :size="12" class="inline" />
                    Akses Bawaan (dari Jabatan / Peran)
                </p>
                <div class="flex flex-wrap gap-2">
                    @foreach($allGroups as $groupKey => $group)
                        <div x-show="defaultGroups.includes('{{ $groupKey }}')" class="inline-flex items-center gap-1 rounded-full border border-emerald-300 bg-emerald-100 px-2 py-1 text-xs font-bold text-emerald-800">
                            <x-admin.icon :name="$group['icon'] ?? 'folder'" :size="12" />
                            {{ $group['label'] }}
                        </div>
                    @endforeach
                </div>
            </div>
            
            {{-- Additional Access: per sub-menu --}}
            <div>
                <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-slate-600">
                    <x-admin.icon name="plus" :size="12" class="inline" />
                    Akses Tambahan (Opsional)
                </p>
                <p class="mb-3 text-xs text-slate-500">
                    Pilih menu spesifik (termasuk sub-menu tiap bidang, mis. Pengaduan RTH, Penyewaan Taman) yang ingin diberikan sebagai akses tambahan.
                </p>
                
                <div class="space-y-3">
                    @foreach($allGroups as $groupKey => $group)
                        <div class="rounded-xl border border-slate-200 bg-white p-3">
                            <p class="mb-2 flex items-center gap-2 text-sm font-bold text-slate-700">
                                <x-admin.icon :name="$group['icon'] ?? 'folder'" :size="14" class="text-slate-400" />
                                {{ $group['label'] }}
                            </p>
                            <div class="space-y-1.5">
                                @foreach($group['items'] as $item)
                                    @php
                                        $slug = $item['slug'] ?? null;
                                        $label = $item['label'] ?? $slug;
                                    @endphp
                                    @if($slug)
                                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border px-3 py-2 transition"
                                            :class="defaultGroups.includes('{{ $groupKey }}') ? 'border-slate-200 bg-slate-50 opacity-60 cursor-not-allowed' : 'border-slate-200 bg-white hover:border-emerald-300 hover:bg-emerald-50/30'">
                                            <input 
                                                type="checkbox"
                                                name="additional_access[]"
                                                value="{{ $slug }}"
                                                x-bind:checked="defaultGroups.includes('{{ $groupKey }}') || selectedSlugs.includes('{{ $slug }}')"
                                                x-bind:disabled="defaultGroups.includes('{{ $groupKey }}')"
                                                x-on:change="if (!defaultGroups.includes('{{ $groupKey }}')) { if ($event.target.checked) { if (!selectedSlugs.includes('{{ $slug }}')) selectedSlugs.push('{{ $slug }}'); } else { selectedSlugs = selectedSlugs.filter(function(k){ return k !== '{{ $slug }}'; }); } }"
                                                class="size-4 rounded border-slate-300 text-emerald-600 focus:ring-2 focus:ring-emerald-500 focus:ring-offset-0"
                                            />
                                            <span class="text-sm text-slate-700">{{ $label }}</span>
                                        </label>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div class="mt-4 rounded-lg bg-blue-50 p-3">
                <div class="flex gap-2">
                    <x-admin.icon name="info-circle" :size="14" class="mt-0.5 shrink-0 text-blue-600" />
                    <p class="text-xs text-blue-700">
                        <strong>Catatan:</strong> Menu bawaan dari peran akun tetap aktif secara otomatis. Akses tambahan ini digunakan jika akun memerlukan akses menu lintas bidang tugas.
                    </p>
                </div>
            </div>
        @endif
    </div>
</div>
