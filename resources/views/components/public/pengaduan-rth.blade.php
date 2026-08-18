<?php

use App\Enums\JenisPengaduanRth;
use App\Http\Requests\StorePengaduanRthRequest;
use App\Models\PengaduanRth;
use App\Models\PengaduanRthFoto;
use App\Traits\HandlesPengaduanPhotoUpload;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;
    use HandlesPengaduanPhotoUpload;

    public string $nama_pelapor = '';
    public string $nomor_hp = '';
    public string $jenis_pengaduan = '';
    public string $deskripsi = '';
    public string $alamat = '';
    public float $latitude = -0.9;
    public float $longitude = 119.87;
    public array $photos = [];
    public function submit(): void
    {
        $validated = $this->validate((new StorePengaduanRthRequest())->rules());

        $ip = request()->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts('pengaduan-rth:'.$ip, 10)) {
            $this->addError('nomor_hp', __('Batas maksimal pengiriman tercapai (5 pengaduan per jam).'));

            return;
        }

        \Illuminate\Support\Facades\RateLimiter::hit('pengaduan-rth:'.$ip, 3600);

        $jenisPengaduan = $validated['jenis_pengaduan'];

        $pengaduan = PengaduanRth::create([
            'nama_pelapor' => $validated['nama_pelapor'],
            'nomor_hp' => $validated['nomor_hp'],
            'jenis_pengaduan' => $jenisPengaduan,
            'deskripsi' => $validated['deskripsi'],
            'alamat' => $validated['alamat'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
        ]);

        $this->processPhotos(
            $this->photos,
            $pengaduan->id,
            'pengaduan_rth_id',
            PengaduanRthFoto::class,
            'pengaduan-rth',
        );

        $this->ticket = $pengaduan->nomor_tiket;
        $this->processing = true;
        // Foto diproses sinkron di atas — langsung balik ke layar sukses bila selesai.
        $this->checkPhotoStatus();

        $this->reset(['nama_pelapor', 'nomor_hp', 'jenis_pengaduan', 'deskripsi', 'alamat', 'photos']);
        $this->latitude = -0.9;
        $this->longitude = 119.87;
    }

    public function jenisOptions(): array
    {
        return JenisPengaduanRth::options();
    }
};
?>

<div
    class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 md:p-8 shadow-sm max-w-4xl mx-auto">
    @if ($processing)
        <div class="space-y-6 text-center py-8" wire:poll.3s="checkPhotoStatus">
            <div
                class="h-16 w-16 bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 rounded-full flex items-center justify-center mx-auto text-3xl font-bold animate-spin">
                ↻
            </div>
            <div class="space-y-2">
                <h3 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ __('Sedang Memproses Foto') }}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">{{ __('Pengaduan Anda telah terkirim. Foto bukti sedang dioptimalkan dan diunggah ke penyimpanan cloud (maksimal beberapa menit).') }}</p>
            </div>
            <div
                class="p-4 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg max-w-xs mx-auto">
                <span class="block text-[10px] text-brand-600 dark:text-brand-400 font-bold tracking-widest uppercase">{{ __('Nomor Tiket Anda') }}</span>
                <span class="block text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1 select-all tracking-wider">{{ $ticket }}</span>
            </div>
        </div>
    @elseif ($ticket)
        <div class="space-y-6 text-center py-8">
            @if ($photoError)
                <div class="p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-300 text-sm">{{ $photoError }}</div>
            @endif
            <div
                class="h-16 w-16 bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 rounded-full flex items-center justify-center mx-auto">
                <x-icons.berhasil class="size-8" />
            </div>
            <div class="space-y-2">
                <h3 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ __('Pengaduan Berhasil Terkirim') }}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">{{ __('Terima kasih atas pengaduan Anda. Simpan nomor tiket di bawah untuk mengecek status pengaduan.') }}</p>
            </div>
            <div
                class="p-4 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg max-w-xs mx-auto">
                <span class="block text-[10px] text-brand-600 dark:text-brand-400 font-bold tracking-widest uppercase">{{ __('Nomor Tiket Anda') }}</span>
                <x-public.copy-ticket :ticket="$ticket" class="block text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1 select-all tracking-wider" />
            </div>
            <div class="flex flex-col sm:flex-row gap-3 justify-center pt-4">
                <a href="{{ url('/lacak') }}"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors border border-slate-200 hover:bg-slate-100 h-10 py-2 px-4 dark:border-slate-800 dark:hover:bg-slate-800">
                    {{ __('Cek Status Pengaduan') }}
                </a>
                <button wire:click="resetPhotoState"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors bg-slate-900 text-slate-50 hover:bg-slate-900/90 h-10 py-2 px-4 dark:bg-slate-50 dark:text-slate-900">
                    {{ __('Buat Pengaduan Baru') }}
                </button>
            </div>
        </div>
    @else
        <form wire:submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="space-y-6">
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
                    label="{{ __('Nomor Telepon') }}"
                    placeholder="{{ __('Contoh: 08123456789') }}"
                />

                <div class="space-y-2.5">
                    <label for="jenis_pengaduan" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('Jenis Pengaduan') }}</label>
                    <x-admin.select
                        wire:model="jenis_pengaduan"
                        id="jenis_pengaduan"
                        name="jenis_pengaduan"
                        :options="$this->jenisOptions()"
                        :searchable="false"
                        placeholder="{{ __('-- Pilih Jenis Pengaduan --') }}"
                    />
                    @error('jenis_pengaduan') <span class="text-[0.8rem] font-medium text-danger-500">{{ $message }}</span> @enderror

                </div>

                <x-public.textarea
                    wire:model="alamat"
                    name="alamat"
                    label="{{ __('Alamat Lokasi Kejadian') }}"
                    placeholder="{{ __('Alamat lengkap lokasi kejadian') }}"
                    rows="2"
                    maxlength="150"
                    hint="{{ __('Sertakan patokan terdekat') }}"
                    icon='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.4 8.5c0 5.5-8.4 11.5-8.4 11.5S3.6 14 3.6 8.5a8.4 8.4 0 1 1 16.8 0Z"/><circle cx="12" cy="8.5" r="2.6"/></svg>'
                />

                <x-public.textarea
                    wire:model="deskripsi"
                    name="deskripsi"
                    label="{{ __('Deskripsi Pengaduan') }}"
                    placeholder="{{ __('Jelaskan detail kejadian secara lengkap: apa yang terjadi, kapan, dan dampaknya...') }}"
                    rows="4"
                    maxlength="5000"
                    hint="{{ __('Minimal 20 karakter') }}"
                    icon='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16M4 12h16M4 19h10"/></svg>'
                />

                <div class="space-y-2.5">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('Foto Bukti (min 1, max 5, JPG/PNG/WebP/AVIF/HEIC maksimal 5MB)') }}</label>
                    <input wire:model="photos" type="file" multiple accept="image/jpeg,image/jpg,image/png,image/webp,image/avif,image/heic,image/heif,.jpg,.jpeg,.png,.webp,.avif,.heic,.heif" aria-label="{{ __('Foto Bukti') }}"
                        class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-1.5 text-sm dark:border-slate-800" />
                    @error('photos') <span class="text-[0.8rem] font-medium text-danger-500">{{ $message }}</span> @enderror
                    @error('photos.*') <span class="text-[0.8rem] font-medium text-danger-500">{{ $message }}</span> @enderror

                    @if ($photos)
                        <div class="grid grid-cols-3 gap-3 pt-3">
                            @foreach ($photos as $photo)
                                <div class="relative aspect-square rounded-md overflow-hidden border border-slate-200 dark:border-slate-800">
                                    <img src="{{ $photo->temporaryUrl() }}" alt="{{ __('Pratinjau foto bukti') }}" class="w-full h-full object-cover" />
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-6 flex flex-col justify-between">
                <div class="space-y-2.5 flex-1 flex flex-col">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('Tentukan Lokasi (Klik Peta)') }}</label>
                    <div wire:ignore
                        class="w-full flex-1 min-h-[300px] border border-slate-200 dark:border-slate-800 rounded-md overflow-hidden relative z-0"
                        x-data="{
                            map: null, marker: null,
                            initMap() {
                                var self = this;
                                window.ensureMaplibreLoaded(function() {
                                    self.map = new maplibregl.Map({
                                        container: self.$el, style: 'https://basemaps.cartocdn.com/gl/voyager-gl-style/style.json',
                                        center: [@js($longitude), @js($latitude)], zoom: 13, attributionControl: false
                                    });
                                    self.map.addControl(new DlhZoomControl(), 'top-left');
if (window.DlhWeatherControl) self.map.addControl(new DlhWeatherControl(), 'top-right');
                                    if (window.DlhBasemapSwitcher) { var bs = new DlhBasemapSwitcher(); self.map.on('load', function() { bs.onAdd(self.map); }); }
                                    self.marker = new maplibregl.Marker({ draggable: true, anchor: 'center' }).setLngLat([@js($longitude), @js($latitude)]).addTo(self.map);
                                    self.marker.on('dragend', function() { var ll = self.marker.getLngLat(); @this.set('latitude', ll.lat); @this.set('longitude', ll.lng); });
                                    self.map.on('click', function(e) { self.marker.setLngLat(e.lngLat); @this.set('latitude', e.lngLat.lat); @this.set('longitude', e.lngLat.lng); });
                                    dlhAddLocBtn(self.map, function(lat, lng) { self.marker.setLngLat([lng, lat]); @this.set('latitude', lat); @this.set('longitude', lng); });
                                });
                            }
                        }" x-intersect.once="initMap()">
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
                    {{ __('Kirim Pengaduan') }}
                </button>
            </div>
        </form>
    @endif
</div>
