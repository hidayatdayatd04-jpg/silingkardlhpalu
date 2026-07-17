@props(['user' => null, 'allGroups'])

@php
    $currentAccess = old('additional_access', $user->additional_access ?? []);
    $isSuperadmin = $user->exists && $user->isSuperadmin();
    $userRole = $user->exists ? $user->adminRole() : null;
    $defaultGroups = $userRole?->allowedGroups() ?? [];
@endphp

<div x-data="{ 
    selectedGroups: {{ json_encode($currentAccess) }},
    defaultGroups: {{ json_encode($defaultGroups) }}
}" class="space-y-4">
    
    <div class="rounded-xl border-2 border-slate-200 bg-slate-50 p-5">
        <div class="mb-4 flex items-start justify-between">
            <div>
                <label class="text-sm font-bold text-slate-700">
                    <x-admin.icon name="shield" :size="16" class="inline" />
                    Akses Menu Bidang
                </label>
                <p class="mt-1 text-xs text-slate-500">
                    @if($isSuperadmin)
                        Superadmin memiliki akses penuh ke semua menu
                    @else
                        Kelola akses tambahan ke menu bidang lain
                    @endif
                </p>
            </div>
        </div>
        
        @if($isSuperadmin)
            {{-- Superadmin locked state --}}
            <div class="rounded-lg bg-amber-50 p-4 text-center">
                <x-admin.icon name="lock" :size="32" class="mx-auto text-amber-600" />
                <p class="mt-2 text-sm font-semibold text-amber-800">
                    Role Superadmin Terlindungi
                </p>
                <p class="mt-1 text-xs text-amber-700">
                    Superadmin memiliki akses penuh ke semua menu secara default
                </p>
            </div>
        @else
            {{-- Default Access Info --}}
            @if(!empty($defaultGroups))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-3">
                    <p class="mb-2 text-xs font-bold uppercase tracking-wider text-emerald-700">
                        <x-admin.icon name="check" :size="12" class="inline" />
                        Akses Default (dari Role)
                    </p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($defaultGroups as $groupKey)
                            @php
                                $group = collect($allGroups)->firstWhere('key', $groupKey);
                            @endphp
                            @if($group)
                                <div class="inline-flex items-center gap-1 rounded-full border border-emerald-300 bg-emerald-100 px-2 py-1 text-xs font-bold text-emerald-800">
                                    <x-admin.icon :name="$group['icon']" :size="12" />
                                    {{ $group['label'] }}
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
            
            {{-- Additional Access Checkboxes --}}
            <div>
                <p class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-600">
                    <x-admin.icon name="plus" :size="12" class="inline" />
                    Akses Tambahan (Opsional)
                </p>
                
                <div class="space-y-2">
                    @foreach($allGroups as $group)
                        @php
                            $isDefault = in_array($group['key'], $defaultGroups);
                        @endphp
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border px-3 py-2.5 transition {{ $isDefault ? 'border-slate-200 bg-slate-50 opacity-60 cursor-not-allowed' : 'border-slate-200 bg-white hover:border-emerald-300 hover:bg-emerald-50/30' }}">
                            <input 
                                type="checkbox"
                                name="additional_access[]"
                                value="{{ $group['key'] }}"
                                x-model="selectedGroups"
                                {{ $isDefault ? 'disabled' : '' }}
                                class="size-4 rounded border-slate-300 text-emerald-600 focus:ring-2 focus:ring-emerald-500 focus:ring-offset-0"
                            />
                            <div class="flex flex-1 items-center gap-2">
                                <div class="grid size-7 shrink-0 place-items-center rounded-lg bg-slate-100 text-slate-600">
                                    <x-admin.icon :name="$group['icon']" :size="14" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold text-slate-700">{{ $group['label'] }}</p>
                                    @if($isDefault)
                                        <p class="text-xs text-slate-500">(Sudah termasuk dalam akses default)</p>
                                    @endif
                                </div>
                                <div x-show="selectedGroups.includes('{{ $group['key'] }}') && !{{ $isDefault ? 'true' : 'false' }}" class="text-emerald-600">
                                    <x-admin.icon name="check-circle" :size="18" />
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
            
            <div class="mt-4 rounded-lg bg-blue-50 p-3">
                <div class="flex gap-2">
                    <x-admin.icon name="info-circle" :size="14" class="mt-0.5 shrink-0 text-blue-600" />
                    <p class="text-xs text-blue-700">
                        <strong>Catatan:</strong> Menu default dari role tetap akan muncul. Akses tambahan ini memberikan fleksibilitas akses lintas bidang.
                    </p>
                </div>
            </div>
        @endif
    </div>
</div>
