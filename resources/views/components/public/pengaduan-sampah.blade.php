<?php

use App\Enums\Bidang;
use App\Enums\JenisPengaduanSampah;
use App\Http\Requests\StorePengaduanSampahRequest;
use App\Models\Laporan;
use App\Models\LaporanFoto;
use App\Services\ImageCompressionService;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public ?string $nama_pelapor = null;
    public ?string $nomor_hp = null;
    public ?string $email = null;
    public ?string $jenis_pengaduan = null;
    public ?string $jenis_pengaduan_lainnya = null;
    public ?string $deskripsi = null;
    public ?string $alamat = null;
    public float $latitude = -0.9;
    public float $longitude = 119.87;
    public array $photos = [];

    public ?string $successTicket = null;

    public function mount(): void
    {
        $this->jenis_pengaduan = JenisPengaduanSampah::SAMPAH_MENUMPUK->value;
    }

    public function submit(ImageCompressionService $compressionService): void
    {
        $validated = $this->validate((new StorePengaduanSampahRequest())->rules());

        $ip = request()->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts('pengaduan-sampah:'.$ip, 5)) {
            $this->addError('nomor_hp', __('Batas maksimal pengiriman tercapai (5 pengaduan per jam).'));

            return;
        }

        \Illuminate\Support\Facades\RateLimiter::hit('pengaduan-sampah:'.$ip, 3600);

        $jenisPengaduan = $validated['jenis_pengaduan'] === '__lainnya__' ? ($this->jenis_pengaduan_lainnya ?? $validated['jenis_pengaduan']) : $validated['jenis_pengaduan'];

        $laporan = Laporan::create([
            'bidang' => Bidang::SAMPAH_LB3->value,
            'nama_pelapor' => $validated['nama_pelapor'],
            'nomor_hp' => $validated['nomor_hp'],
            'email' => $validated['email'],
            'jenis_pengaduan' => $jenisPengaduan,
            'kategori' => $jenisPengaduan,
            'deskripsi' => $validated['deskripsi'],
            'alamat' => $validated['alamat'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'status' => 'Belum Ditindaklanjuti',
        ]);

        foreach ($this->photos as $photo) {
            $path = $compressionService->compressAndStore($photo, 'laporans');
            LaporanFoto::create([
                'laporan_id' => $laporan->id,
                'path_foto' => $path,
            ]);
        }

        $this->successTicket = $laporan->nomor_tiket;
        $this->reset(['nama_pelapor', 'nomor_hp', 'email', 'deskripsi', 'alamat', 'photos']);
        $this->latitude = -0.9;
        $this->longitude = 119.87;
        $this->jenis_pengaduan = JenisPengaduanSampah::SAMPAH_MENUMPUK->value;
    }
};
?>

<div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 md:p-8 shadow-sm max-w-4xl mx-auto">
    @if ($successTicket)
        <div class="space-y-6 text-center py-8">
            <div class="h-16 w-16 bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 rounded-full flex items-center justify-center mx-auto text-3xl font-bold">✓</div>
            <div class="space-y-2">
                <h3 class="text-2xl font-bold">{{ __('Pengaduan Berhasil Terkirim') }}</h3>
                <p class="text-sm text-slate-500 max-w-md mx-auto">{{ __('Simpan nomor tiket berikut untuk mengecek status pengaduan.') }}</p>
            </div>
            <div class="p-4 bg-slate-50 dark:bg-slate-900 border rounded-lg max-w-xs mx-auto">
                <span class="block text-[10px] font-extrabold tracking-widest uppercase text-brand-600">{{ __('Nomor Tiket') }}</span>
                <span class="block text-2xl font-bold mt-1 select-all">{{ $successTicket }}</span>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 justify-center pt-2">
                <a href="/cek-pengaduan-sampah" class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-brand-600 text-white h-10 px-4 hover:bg-brand-700">{{ __('Cek Status') }}</a>
                <button wire:click="$set('successTicket', null)" type="button" class="inline-flex items-center justify-center rounded-md text-sm font-medium border h-10 px-4">{{ __('Buat Pengaduan Baru') }}</button>
            </div>
        </div>
    @else
        <form wire:submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="space-y-4">
                <x-public.input
                    wire:model="nama_pelapor"
                    name="nama_pelapor"
                    label="{{ __('Nama Pelapor') }}"
                    placeholder="{{ __('Nama lengkap pelapor') }}"
                />

                <x-public.input
                    wire:model="nomor_hp"
                    name="nomor_hp"
                    type="tel"
                    label="{{ __('Nomor HP') }}"
                    placeholder="{{ __('Contoh: 08123456789') }}"
                />

                <x-public.input
                    wire:model="email"
                    name="email"
                    type="email"
                    label="{{ __('Email') }}"
                    placeholder="contoh@email.com"
                    required
                    hint="{{ __('Email untuk menerima notifikasi update status pengaduan') }}"
                />

                <div class="space-y-2.5">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('Jenis Pengaduan') }}</label>
                    <x-admin.select
                        wire:model="jenis_pengaduan"
                        name="jenis_pengaduan"
                        :options="array_merge(collect(JenisPengaduanSampah::cases())->mapWithKeys(fn($j) => [$j->value => $j->label()])->toArray(), ['__lainnya__' => __('Lainnya...')])"
                        :searchable="false"
                        placeholder="{{ __('-- Pilih Jenis Pengaduan --') }}"
                    />
                    @error('jenis_pengaduan') <span class="text-xs text-danger-500">{{ $message }}</span> @enderror

                    @if($jenis_pengaduan === '__lainnya__')
                        <div class="mt-2">
                            <x-public.input
                                wire:model="jenis_pengaduan_lainnya"
                                name="jenis_pengaduan_lainnya"
                                label="{{ __('Jenis Pengaduan Lainnya') }}"
                                placeholder="{{ __('Tulis jenis pengaduan secara manual...') }}"
                                required
                            />
                        </div>
                    @endif
                </div>

                <x-public.textarea
                    wire:model="alamat"
                    name="alamat"
                    label="{{ __('Alamat Lokasi') }}"
                    placeholder="{{ __('Alamat lengkap lokasi kejadian') }}"
                    rows="2"
                    maxlength="150"
                    hint="{{ __('Sertakan patokan terdekat') }}"
                    icon='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.4 8.5c0 5.5-8.4 11.5-8.4 11.5S3.6 14 3.6 8.5a8.4 8.4 0 1 1 16.8 0Z"/><circle cx="12" cy="8.5" r="2.6"/></svg>'
                />

                <x-public.textarea
                    wire:model="deskripsi"
                    name="deskripsi"
                    label="{{ __('Deskripsi') }}"
                    placeholder="{{ __('Jelaskan detail kejadian secara lengkap: apa yang terjadi, kapan, dan dampaknya...') }}"
                    rows="3"
                    maxlength="5000"
                    hint="{{ __('Minimal 20 karakter') }}"
                    icon='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16M4 12h16M4 19h10"/></svg>'
                />

                <div class="space-y-2.5">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('Foto Bukti (1-3 foto)') }}</label>
                    <input wire:model="photos" type="file" multiple accept="image/*" class="flex h-10 w-full rounded-md border border-slate-200 px-3 text-sm dark:border-slate-800" />
                    @error('photos') <span class="text-xs text-danger-500">{{ $message }}</span> @enderror
                    @error('photos.*') <span class="text-xs text-danger-500">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="space-y-4 flex flex-col">
                <div class="space-y-2.5 flex-1 flex flex-col">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('Tentukan Lokasi (Klik Peta)') }}</label>
                    <div wire:ignore class="w-full flex-1 min-h-[300px] border border-slate-200 dark:border-slate-800 rounded-md overflow-hidden"
                         x-data="{
                             map: null, marker: null,
                             initMap() {
                                 var self = this;
                                 window.ensureMaplibreLoaded(function() {
                                     self.map = new maplibregl.Map({
                                         container: self.$el,
                                         style: 'https://basemaps.cartocdn.com/gl/voyager-gl-style/style.json',
                                         center: [@js($longitude), @js($latitude)],
                                         zoom: 13,
                                         attributionControl: false
                                     });
                                     self.map.addControl(new maplibregl.NavigationControl({ showCompass: false, visualizePitch: false }), 'top-left');
                                     if (window.DlhBasemapSwitcher) { var bs = new DlhBasemapSwitcher(); self.map.on('load', function() { bs.onAdd(self.map); }); }
                                     dlhAddLocBtn(self.map, function(lat, lng) { self.marker.setLngLat([lng, lat]); @this.set('latitude', lat); @this.set('longitude', lng); });
                                     self.marker = new maplibregl.Marker({ draggable: true, anchor: 'center' })
                                         .setLngLat([@js($longitude), @js($latitude)])
                                         .addTo(self.map);
                                     self.marker.on('dragend', function() { var ll = self.marker.getLngLat(); @this.set('latitude', ll.lat); @this.set('longitude', ll.lng); });
                                     self.map.on('click', function(e) { self.marker.setLngLat(e.lngLat); @this.set('latitude', e.lngLat.lat); @this.set('longitude', e.lngLat.lng); });
                                 });
                             }
                         }" x-init="initMap()"></div>
                    <div class="flex justify-between text-xs text-slate-500">
                        <span>Lat: {{ number_format($latitude, 6) }}</span>
                        <span>Lng: {{ number_format($longitude, 6) }}</span>
                    </div>
                </div>
                <button type="submit" class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-slate-900 text-white h-10 w-full dark:bg-slate-50 dark:text-slate-900">{{ __('Kirim Pengaduan') }}</button>
            </div>
        </form>
    @endif
</div>
