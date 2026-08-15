<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Laporan;
use App\Models\LaporanFoto;
use App\Services\ImageCompressionService;
use Illuminate\Support\Str;

new class extends Component {
    use WithFileUploads;

    public ?string $nomor_hp = null;
    public string $kategori = 'Rawan';
    public ?string $deskripsi = null;
    public float $latitude = -0.9;
    public float $longitude = 119.87;
    public array $photos = [];

    public ?string $successTicket = null;

    protected $rules = [
        'nomor_hp' => 'required|string|max:20',
        'kategori' => 'required|in:Tumbang,Rawan,Kabel',
        'deskripsi' => 'required|string',
        'latitude' => 'required|numeric',
        'longitude' => 'required|numeric',
        'photos' => 'required|array|min:1|max:3',
        'photos.*' => 'image|max:5120',
    ];

    public function submit(ImageCompressionService $compressionService)
    {
        $this->validate();

        $ip = request()->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts('tree-report:' . $ip, 5)) {
            $this->addError('nomor_hp', __('Batas maksimal pengiriman laporan tercapai (5 laporan per jam). Silakan coba beberapa saat lagi.'));
            return;
        }

        \Illuminate\Support\Facades\RateLimiter::hit('tree-report:' . $ip, 3600);

        do {
            $ticket = 'TK-' . strtoupper(Str::random(8));
        } while (Laporan::where('nomor_tiket', $ticket)->exists());

        $laporan = Laporan::create([
            'nomor_tiket' => $ticket,
            'nomor_hp' => $this->nomor_hp,
            'kategori' => $this->kategori,
            'deskripsi' => $this->deskripsi,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'status' => 'Menunggu',
        ]);

        foreach ($this->photos as $photo) {
            $path = $compressionService->compressAndStore($photo, 'laporans');
            LaporanFoto::create([
                'laporan_id' => $laporan->id,
                'path_foto' => $path,
            ]);
        }

        $this->successTicket = $ticket;

        $this->reset(['nomor_hp', 'deskripsi', 'photos']);
        $this->latitude = -0.9;
        $this->longitude = 119.87;
    }
};
?>

<div
    class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 md:p-8 shadow-sm max-w-4xl mx-auto">
    @if ($successTicket)
        <div class="space-y-6 text-center py-8">
            <div
                class="h-16 w-16 bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 rounded-full flex items-center justify-center mx-auto text-3xl font-bold">
                ✓
            </div>
            <div class="space-y-2">
                <h3 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ __('Laporan Berhasil Terkirim') }}
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">{{ __('Terima kasih atas laporan Anda. Mohon
                    simpan Nomor Tiket di bawah ini untuk melacak status aduan Anda secara berkala.') }}</p>
            </div>
            <div
                class="p-4 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg max-w-xs mx-auto">
                <span
                    class="block text-[10px] text-brand-600 dark:text-brand-400 font-extrabold tracking-widest uppercase">{{ __('Nomor
                    Tiket Anda') }}</span>
                <x-public.copy-ticket :ticket="$successTicket" class="block text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1 select-all tracking-wider" />
            </div>
            <div class="pt-4">
                <button wire:click="$set('successTicket', null)"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none ring-offset-white border border-slate-200 hover:bg-slate-100 hover:text-slate-900 h-10 py-2 px-4 dark:border-slate-800 dark:hover:bg-slate-800 dark:hover:text-slate-50 dark:focus-visible:ring-slate-300">
                    {{ __('Buat Laporan Baru') }}
                </button>
            </div>
        </div>
    @else
        <form wire:submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="space-y-6">
                <div class="space-y-2">
                    <label for="phone"
                        class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 dark:text-slate-300">{{ __('Nomor
                        HP Aktif') }}</label>
                    <input wire:model="nomor_hp" id="phone" type="tel" placeholder="{{ __('Contoh: 08123456789') }}"
                        class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm ring-offset-white file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-800 dark:ring-offset-slate-950 dark:focus-visible:ring-brand-500 dark:placeholder:text-slate-400" />
                    @error('nomor_hp') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-2">
                    <label for="category"
                        class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 dark:text-slate-300">{{ __('Kategori
                        Masalah') }}</label>
                    <x-admin.select
                        wire:model="kategori"
                        id="category"
                        name="kategori"
                        :options="['Tumbang' => __('Pohon Tumbang (Darurat)'), 'Rawan' => __('Pohon Rawan (Lapuk/Miring)'), 'Kabel' => __('Mengganggu Kabel Listrik/Utilitas')]"
                        :searchable="false"
                    />
                    @error('kategori') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-2">
                    <label for="desc"
                        class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 dark:text-slate-300">{{ __('Deskripsi
                        Laporan') }}</label>
                    <textarea wire:model="deskripsi" id="desc" rows="4"
                        placeholder="{{ __('Jelaskan detail lokasi, patokan, dan kondisi pohon...') }}"
                        class="flex min-h-[80px] w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm ring-offset-white placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-800 dark:ring-offset-slate-950 dark:focus-visible:ring-brand-500 dark:placeholder:text-slate-400"></textarea>
                    @error('deskripsi') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-2">
                    <label
                        class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 dark:text-slate-300">{{ __('Foto
                        Bukti (1 s/d 3 Foto)') }}</label>
                    <input wire:model="photos" type="file" multiple
                        class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-1.5 text-sm ring-offset-white file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-800 dark:ring-offset-slate-950 dark:focus-visible:ring-brand-500" />
                    @error('photos') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
                    @error('photos.*') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror

                    @if ($photos)
                        <div class="grid grid-cols-3 gap-3 pt-3">
                            @foreach ($photos as $photo)
                                <div
                                    class="relative aspect-square rounded-md overflow-hidden border border-slate-200 dark:border-slate-800">
                                    <img src="{{ $photo->temporaryUrl() }}" alt="{{ __('Pratinjau foto bukti kejadian') }}" class="w-full h-full object-cover" />
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-6 flex flex-col justify-between">
                <div class="space-y-2 flex-1 flex flex-col">
                    <label
                        class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 dark:text-slate-300">{{ __('Tentukan
                        Lokasi (Klik Peta)') }}</label>
                    <div wire:ignore
                        class="w-full flex-1 min-h-[300px] border border-slate-200 dark:border-slate-800 rounded-md overflow-hidden relative z-0"
                        x-data="{
                                 map: null, marker: null,
                                 initMap() {
                                     var self = this;
                                     window.ensureMaplibreLoaded(function() {
                                         self.map = new maplibregl.Map({ container: self.$el, style: 'https://basemaps.cartocdn.com/gl/voyager-gl-style/style.json', center: [@js($longitude), @js($latitude)], zoom: 13, attributionControl: false });
                                         self.map.addControl(new DlhZoomControl(), 'top-left');
if (window.DlhWeatherControl) map.addControl(new DlhWeatherControl(), 'top-right');
                                         if (window.DlhBasemapSwitcher) self.map.addControl(new DlhBasemapSwitcher(), 'bottom-right');
                                         self.marker = new maplibregl.Marker({ draggable: true, anchor: 'center' }).setLngLat([@js($longitude), @js($latitude)]).addTo(self.map);
                                         self.marker.on('dragend', function() { var ll = self.marker.getLngLat(); @this.set('latitude', ll.lat); @this.set('longitude', ll.lng); });
                                         self.map.on('click', function(e) { self.marker.setLngLat(e.lngLat); @this.set('latitude', e.lngLat.lat); @this.set('longitude', e.lngLat.lng); });
                                         dlhAddLocBtn(self.map, function(lat, lng) { self.marker.setLngLat([lng, lat]); @this.set('latitude', lat); @this.set('longitude', lng); });
                                     });
                                 }
                             }" x-init="initMap()">
                    </div>
                    <div class="flex justify-between text-[0.8rem] text-slate-500 mt-2">
                        <span>Lat: {{ number_format($latitude, 6) }}</span>
                        <span>Lng: {{ number_format($longitude, 6) }}</span>
                    </div>
                </div>

                <button type="submit"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none ring-offset-white bg-slate-900 text-slate-50 hover:bg-slate-900/90 h-10 py-2 px-4 w-full dark:bg-slate-50 dark:text-slate-900 dark:hover:bg-slate-50/90 dark:ring-offset-slate-950 shadow-sm">
                    {{ __('Kirim Aduan') }}
                </button>
            </div>
        </form>
    @endif
</div>