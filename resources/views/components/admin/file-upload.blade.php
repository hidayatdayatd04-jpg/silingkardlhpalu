@props([
    'label' => null,
    'error' => null,
    'hint' => null,
    'required' => false,
    'currentFile' => null,
    'accept' => null
])

<div {{ $attributes->only('class') }} x-data="{ fileName: '' }">
    @if($label)
        <label class="mb-2 block text-sm font-bold text-slate-700">
            {{ $label }}
            @if($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif
    
    <div class="relative">
        <input 
            type="file"
            x-on:change="fileName = $event.target.files[0]?.name || ''"
            {{ $attributes->except('class')->merge([
                'class' => 'w-full cursor-pointer rounded-lg border-2 border-dashed bg-slate-50 px-4 py-6 text-sm font-medium text-slate-800 outline-none transition file:mr-4 file:cursor-pointer file:rounded-lg file:border-0 file:bg-emerald-600 file:px-4 file:py-2 file:text-sm file:font-bold file:text-white hover:border-emerald-300 hover:bg-emerald-50/50 focus:border-emerald-500 focus:ring-4 ' . 
                    ($error ? 'border-rose-300 focus:ring-rose-100' : 'border-slate-300 focus:ring-emerald-100'),
                'accept' => $accept
            ]) }}
        >
        
        <!-- File name display -->
        <div x-show="fileName" x-cloak class="mt-2 flex items-center gap-2 text-sm">
            <x-admin.icon name="file-text" :size="16" class="text-emerald-600" />
            <span class="font-semibold text-slate-700" x-text="fileName"></span>
        </div>
    </div>
    
    @if($currentFile)
        <div class="mt-3 flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
            <x-admin.icon name="file-text" :size="16" class="text-slate-400" />
            <span class="flex-1 text-sm font-medium text-slate-600">File saat ini</span>
            <a 
                href="{{ Storage::url($currentFile) }}" 
                target="_blank"
                class="text-sm font-bold text-emerald-600 transition hover:text-emerald-700"
            >
                Lihat
            </a>
        </div>
    @endif
    
    @if($error)
        <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $error }}</p>
    @elseif($hint)
        <p class="mt-1.5 text-xs text-slate-500">{{ $hint }}</p>
    @endif
</div>
