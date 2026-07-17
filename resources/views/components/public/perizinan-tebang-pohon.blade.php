<?php

use App\Http\Requests\StorePerizinanTebangPohonRequest;
use App\Models\PerizinanTebangPohon;
use App\Services\ImageCompressionService;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public string $nama_pemohon = '';
    public string $nomor_hp = '';
    public string $email = '';
    public ?string $surat_permohonan = null;
    public ?string $ktp_nib = null;
    public string $alasan_penebangan = '';
    public ?string $foto_pohon = null;
    public float $latitude = -0.9;
    public float $longitude = 119.87;
    public string $rencana_ganti_tanam = '';
    public ?string $successTicket = null;

    public function submit(ImageCompressionService $compression): void
    {
        $validated = $this->validate((new StorePerizinanTebangPohonRequest())->rules());

        $record = PerizinanTebangPohon::create([
            'nama_pemohon' => $validated['nama_pemohon'],
            'nomor_hp' => $validated['nomor_hp'],
            'email' => $validated['email'],
            'surat_permohonan' => $this->surat_permohonan->store('perizinan-tebang', 'public'),
            'ktp_nib' => $this->ktp_nib->store('perizinan-tebang', 'public'),
            'alasan_penebangan' => $validated['alasan_penebangan'],
            'foto_pohon' => $compression->compressAndStore($this->foto_pohon, 'perizinan-tebang'),
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'rencana_ganti_tanam' => $validated['rencana_ganti_tanam'],
        ]);

        $this->successTicket = $record->nomor_tiket;
        $this->reset(['nama_pemohon', 'nomor_hp', 'email', 'surat_permohonan', 'ktp_nib', 'alasan_penebangan', 'foto_pohon', 'rencana_ganti_tanam']);
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
                <h3 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ __('Permohonan Berhasil Terkirim') }}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">{{ __('Simpan nomor tiket di bawah untuk mengecek status permohonan perizinan tebang pohon.') }}</p>
            </div>
            <div
                class="p-4 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg max-w-xs mx-auto">
                <span class="block text-[10px] text-brand-600 dark:text-brand-400 font-extrabold tracking-widest uppercase">{{ __('Nomor Tiket Anda') }}</span>
                <span class="block text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1 select-all tracking-wider font-mono">{{ $successTicket }}</span>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 justify-center pt-4">
                <a href="{{ url('/cek-perizinan-tebang-pohon') }}"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors border border-slate-200 hover:bg-slate-100 h-10 py-2 px-4 dark:border-slate-800 dark:hover:bg-slate-800">
                    {{ __('Cek Status Permohonan') }}
                </a>
                <button wire:click="$set('successTicket', null)"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors bg-slate-900 text-slate-50 hover:bg-slate-900/90 h-10 py-2 px-4 dark:bg-slate-50 dark:text-slate-900">
                    {{ __('Ajukan Permohonan Baru') }}
                </button>
            </div>
        </div>
    @else
        <form wire:submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="space-y-6">
                <div class="space-y-2">
                    <label for="nama_pemohon" class="text-sm font-medium dark:text-slate-300">{{ __('Nama Pemohon') }}</label>
                    <input wire:model="nama_pemohon" id="nama_pemohon" type="text" placeholder="{{ __('Nama lengkap pemohon') }}"
                        class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm dark:border-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500" />
                    @error('nama_pemohon') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-2">
                    <label for="nomor_hp" class="text-sm font-medium dark:text-slate-300">{{ __('Nomor Telepon') }}</label>
                    <input wire:model="nomor_hp" id="nomor_hp" type="tel" placeholder="{{ __('Contoh: 08123456789') }}"
                        class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm dark:border-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500" />
                    @error('nomor_hp') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium dark:text-slate-300">{{ __('Email') }} <span class="text-red-500">*</span></label>
                    <input wire:model="email" id="email" type="email" placeholder="contoh@email.com"
                        class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm dark:border-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500" required />
                    @error('email') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
                    <p class="text-xs text-slate-500">{{ __('Email untuk menerima notifikasi update status permohonan') }}</p>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium dark:text-slate-300">{{ __('Surat Permohonan (PDF, max 5MB)') }}</label>
                    <input wire:model="surat_permohonan" type="file" accept="application/pdf"
                        class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-1.5 text-sm dark:border-slate-800" />
                    @error('surat_permohonan') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium dark:text-slate-300">{{ __('KTP/NIB (PDF/Gambar, max 5MB)') }}</label>
                    <input wire:model="ktp_nib" type="file" accept="application/pdf,image/jpeg,image/png,image/jpg"
                        class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-1.5 text-sm dark:border-slate-800" />
                    @error('ktp_nib') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-2">
                    <label for="alasan_penebangan" class="text-sm font-medium dark:text-slate-300">{{ __('Alasan Penebangan') }}</label>
                    <textarea wire:model="alasan_penebangan" id="alasan_penebangan" rows="3" placeholder="{{ __('Jelaskan alasan penebangan pohon...') }}"
                        class="flex w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm dark:border-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500"></textarea>
                    @error('alasan_penebangan') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium dark:text-slate-300">{{ __('Foto Pohon (JPG/PNG max 2MB)') }}</label>
                    <input wire:model="foto_pohon" type="file" accept="image/jpeg,image/png,image/jpg"
                        class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-1.5 text-sm dark:border-slate-800" />
                    @error('foto_pohon') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror

                    @if ($foto_pohon)
                        <div class="relative aspect-video rounded-md overflow-hidden border border-slate-200 dark:border-slate-800 mt-2">
                            <img src="{{ $foto_pohon->temporaryUrl() }}" alt="{{ __('Pratinjau foto pohon') }}" class="w-full h-full object-cover" />
                        </div>
                    @endif
                </div>

                <div class="space-y-2">
                    <label for="rencana_ganti_tanam" class="text-sm font-medium dark:text-slate-300">{{ __('Rencana Ganti Tanam') }}</label>
                    <textarea wire:model="rencana_ganti_tanam" id="rencana_ganti_tanam" rows="3" placeholder="{{ __('Jelaskan rencana ganti tanam...') }}"
                        class="flex w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm dark:border-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500"></textarea>
                    @error('rencana_ganti_tanam') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="space-y-6 flex flex-col justify-between">
                <div class="space-y-2 flex-1 flex flex-col">
                    <label class="text-sm font-medium dark:text-slate-300">{{ __('Lokasi Pohon (Klik Peta)') }}</label>
                    <div wire:ignore
                        class="w-full flex-1 min-h-[300px] border border-slate-200 dark:border-slate-800 rounded-md overflow-hidden relative z-0"
                        x-data="{
                            map: null, marker: null,
                            initMap() {
                                var self = this;
                                window.ensureMaplibreLoaded(function() {
                                    self.map = new maplibregl.Map({ container: self.$el, style: 'https://basemaps.cartocdn.com/gl/voyager-gl-style/style.json', center: [@js($longitude), @js($latitude)], zoom: 13, attributionControl: false });
                                    self.map.addControl(new maplibregl.NavigationControl({ showCompass: false, visualizePitch: false }), 'top-left');
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
                    @error('latitude') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
                    @error('longitude') <span class="text-[0.8rem] font-medium text-red-500">{{ $message }}</span> @enderror
                </div>

                <button type="submit"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-slate-900 text-slate-50 hover:bg-slate-900/90 h-10 py-2 px-4 w-full dark:bg-slate-50 dark:text-slate-900 shadow-sm">
                    {{ __('Ajukan Perizinan') }}
                </button>
            </div>
        </form>
    @endif
</div>
