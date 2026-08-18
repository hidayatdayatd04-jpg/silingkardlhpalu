<?php

use App\Enums\JenisPengaduanPengendalian;
use App\Enums\JenisPengaduanSampah;
use App\Enums\JenisPengaduanTataPenataan;
use App\Enums\JenisPengaduanRth;
use App\Models\PengaduanPengendalian;
use App\Models\PengaduanPengendalianFoto;
use App\Models\PengaduanRth;
use App\Models\PengaduanRthFoto;
use App\Models\PengaduanSampah;
use App\Models\PengaduanSampahFoto;
use App\Models\PengaduanTataPenataan;
use App\Models\PengaduanTataPenataanFoto;
use App\Traits\HandlesPengaduanPhotoUpload;
use App\Livewire\Concerns\ThrottlesPublic;
use App\Livewire\Concerns\VerifiesGoogleRecaptcha;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;
    use HandlesPengaduanPhotoUpload;
    use VerifiesGoogleRecaptcha;
    use ThrottlesPublic;

    public string $bidang = 'pengendalian';
    public ?string $nama_pelapor = null;
    public ?string $nomor_hp = null;
    public ?string $jenis_pengaduan = null;
    public ?string $nama_terlapor = null;
    public ?string $nama_perusahaan_terlapor = null;
    public ?string $alamat = null;
    public float $latitude = -0.9;
    public float $longitude = 119.87;
    public ?string $deskripsi = null;
    public array $photos = [];

    public ?string $successTicket = null;
    private string $initialBidang = 'pengendalian';

    public function mount(): void
    {
        $bidang = request()->query('bidang');
        $jenis = request()->query('jenis');

        $validBidang = ['pengendalian', 'sampah', 'tata-penataan', 'rth'];

        if ($bidang && in_array($bidang, $validBidang, true)) {
            $this->bidang = $bidang;
            $this->initialBidang = $bidang;
        }

        if ($jenis) {
            $jenisOptions = $this->jenisOptions();
            if (array_key_exists($jenis, $jenisOptions)) {
                $this->jenis_pengaduan = $jenis;
            }
        }
    }

    public function updatedBidang(string $value): void
    {
        if ($value === $this->initialBidang) {
            return;
        }
        $this->jenis_pengaduan = '';
        $this->resetValidation(['jenis_pengaduan']);
    }

    public function removePhoto(int $index): void
    {
        if (isset($this->photos[$index])) {
            unset($this->photos[$index]);
            $this->photos = array_values($this->photos);
        }
    }

    public function submit(): void
    {
        if (! $this->verifyCaptcha('submit')) {
            return;
        }

        $this->resetCaptcha();

        $this->validate($this->rules(), $this->messages());

        if ($this->hitRateLimit('pengaduan-unified', 10, 'form', __('Pengaduan dibatasi maksimal 10 per jam.'))) {
            return;
        }

        if ($this->bidang === 'tata-penataan') {
            $pengaduan = PengaduanTataPenataan::create([
                'nama_pelapor' => $this->nama_pelapor,
                'nomor_hp' => $this->nomor_hp,
                'jenis_pengaduan' => $this->jenis_pengaduan,
                'nama_terlapor' => $this->nama_terlapor ?? null,
                'nama_perusahaan_terlapor' => $this->nama_perusahaan_terlapor ?? null,
                'alamat' => $this->alamat,
                'deskripsi' => $this->deskripsi,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ]);

            $this->ticket = $pengaduan->nomor_tiket;

            $this->processPhotos(
                $this->photos,
                $pengaduan->id,
                'pengaduan_tata_penataan_id',
                PengaduanTataPenataanFoto::class,
                'pengaduan-tata-penataan',
            );
        } else {
            $payload = [
                'nama_pelapor' => $this->nama_pelapor,
                'nomor_hp' => $this->nomor_hp,
                'jenis_pengaduan' => $this->jenis_pengaduan,
                'alamat' => $this->alamat,
                'deskripsi' => $this->deskripsi,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ];

            [$modelClass, $fotoClass, $fkColumn, $storageDir] = match ($this->bidang) {
                'pengendalian' => [PengaduanPengendalian::class, PengaduanPengendalianFoto::class, 'pengaduan_pengendalian_id', 'pengaduan-pengendalian'],
                'sampah' => [PengaduanSampah::class, PengaduanSampahFoto::class, 'pengaduan_sampah_id', 'pengaduan-sampah'],
                'rth' => [PengaduanRth::class, PengaduanRthFoto::class, 'pengaduan_rth_id', 'pengaduan-rth'],
            };

            $pengaduan = $modelClass::create($payload);

            $this->ticket = $pengaduan->nomor_tiket;

            $this->processPhotos(
                $this->photos,
                $pengaduan->id,
                $fkColumn,
                $fotoClass,
                $storageDir,
            );
        }

        $this->processing = true;
        // Foto diproses sinkron di atas — langsung balik ke layar sukses bila selesai.
        $this->checkPhotoStatus();

        $this->resetForm();
    }

    private function rules(): array
    {
        $base = [
            'nama_pelapor' => ['required', 'string', 'max:255'],
            'nomor_hp' => ['required', 'string', 'regex:/^(?:\+62|62|0)8[1-9][0-9]{6,12}$/', 'max:15'],
            'alamat' => ['required', 'string', 'max:1000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'deskripsi' => ['required', 'string', 'max:5000'],
            'photos' => ['required', 'array', 'min:1', 'max:5'],
            'photos.*' => ['mimes:jpg,jpeg,png,webp,avif,heic,heif', 'max:5120'],
        ];

        $jenisValues = match ($this->bidang) {
            'pengendalian' => array_column(JenisPengaduanPengendalian::cases(), 'value'),
            'sampah' => array_column(JenisPengaduanSampah::cases(), 'value'),
            'tata-penataan' => array_column(JenisPengaduanTataPenataan::cases(), 'value'),
            'rth' => array_column(JenisPengaduanRth::cases(), 'value'),
        };

        $base['jenis_pengaduan'] = ['required', 'string', \Illuminate\Validation\Rule::in($jenisValues)];

        if ($this->bidang === 'tata-penataan') {
            $base['nama_terlapor'] = ['nullable', 'string', 'max:255'];
            $base['nama_perusahaan_terlapor'] = ['nullable', 'string', 'max:255'];
        }

        return $base;
    }

    private function messages(): array
    {
        return [
            'nomor_hp.required' => 'Nomor telepon wajib diisi.',
            'nomor_hp.regex' => 'Format nomor telepon tidak valid. Gunakan format: 08xxx.',
            'nomor_hp.max' => 'Nomor telepon maksimal 15 digit.',
            'photos.required' => 'Foto bukti wajib diunggah minimal 1 foto.',
            'photos.array' => 'Foto bukti harus berupa array.',
            'photos.min' => 'Foto bukti minimal 1 foto.',
            'photos.max' => 'Foto bukti maksimal 5 foto.',
            'photos.*.mimes' => 'Format foto harus JPG, JPEG, PNG, WEBP, AVIF, HEIC, atau HEIF.',
            'photos.*.max' => 'Ukuran foto maksimal 5MB.',
            'deskripsi.required' => 'Deskripsi pengaduan wajib diisi.',
            'deskripsi.max' => 'Deskripsi maksimal 5000 karakter.',
            'alamat.required' => 'Alamat lokasi kejadian wajib diisi.',
            'latitude.required' => 'Lokasi pada peta wajib ditentukan.',
            'longitude.required' => 'Lokasi pada peta wajib ditentukan.',
        ];
    }

    private function resetForm(): void
    {
        $this->reset([
            'nama_pelapor', 'nomor_hp', 'jenis_pengaduan', 'nama_terlapor',
            'nama_perusahaan_terlapor', 'alamat', 'deskripsi', 'photos',
        ]);
        $this->latitude = -0.9;
        $this->longitude = 119.87;
    }

    public function jenisOptions(): array
    {
        $options = match ($this->bidang) {
            'pengendalian' => JenisPengaduanPengendalian::options(),
            'sampah' => JenisPengaduanSampah::options(),
            'tata-penataan' => JenisPengaduanTataPenataan::options(),
            'rth' => JenisPengaduanRth::options(),
            default => [],
        };

        return $options;
    }

    public function getBidangOptions(): array
    {
        return [
            'pengendalian' => __('Pengendalian Dampak Lingkungan'),
            'sampah' => __('Pengelolaan Sampah & LB3'),
            'tata-penataan' => __('Tata Penataan'),
            'rth' => __('Ruang Terbuka Hijau'),
        ];
    }

    public function getCekUrl(): string
    {
        // Semua bidang kini dilacak terpusat di /lacak.
        return '/lacak';
    }
};
?>

<div
    class="fi-form-card bg-white dark:bg-slate-950 rounded-2xl p-6 md:p-8 shadow-[0_1px_3px_rgba(13,43,29,0.06),0_12px_32px_-12px_rgba(13,43,29,0.10)] max-w-4xl mx-auto">
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
                <a href="{{ $this->getCekUrl() }}"
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
        <form wire:submit.prevent="submit" data-dlh-recaptcha-action="submit" class="grid gap-8"
            :class="located ? 'grid-cols-1 lg:grid-cols-2' : 'grid-cols-1'"
            x-data="{
                located: false,
                detecting: false,
                geoError: null,
                map: null,
                marker: null,
                detectLocation() {
                    var self = this;
                    this.geoError = null;
                    if (!navigator.geolocation) {
                        this.geoError = 'Browser Anda tidak mendukung geolokasi. Silakan isi alamat secara manual.';
                        return;
                    }
                    this.detecting = true;
                    navigator.geolocation.getCurrentPosition(
                        function (pos) {
                            var lat = pos.coords.latitude;
                            var lon = pos.coords.longitude;
                            fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=' + lat + '&lon=' + lon + '&accept-language=id')
                                .then(function (r) { return r.json(); })
                                .then(function (data) {
                                    var addr = (data && data.display_name) ? data.display_name : '';
                                    @this.set('alamat', addr);
                                })
                                .catch(function () {})
                                .finally(function () {
                                    @this.set('latitude', lat);
                                    @this.set('longitude', lon);
                                    self.detecting = false;
                                    self.located = true;
                                    self.$nextTick(function () { self.initMap(lat, lon); });
                                });
                        },
                        function (err) {
                            self.detecting = false;
                            if (err.code === 1) {
                                self.geoError = 'Izin lokasi ditolak. Izinkan akses lokasi pada browser Anda lalu coba lagi.';
                            } else if (err.code === 2) {
                                self.geoError = 'Posisi tidak dapat ditentukan saat ini. Coba lagi atau isi alamat secara manual.';
                            } else if (err.code === 3) {
                                self.geoError = 'Waktu permintaan lokasi habis. Silakan coba lagi.';
                            } else {
                                self.geoError = 'Gagal mendapatkan lokasi: ' + err.message;
                            }
                        },
                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 30000 }
                    );
                },
                initMap(lat, lon) {
                    var self = this;
                    if (this.map) { this.moveMarker(lat, lon); return; }
                    window.ensureMaplibreLoaded(function () {
                        self.map = new maplibregl.Map({ container: self.$refs.mapEl, style: 'https://basemaps.cartocdn.com/gl/voyager-gl-style/style.json', center: [lon, lat], zoom: 15, attributionControl: false });
                        self.map.addControl(new DlhZoomControl(), 'top-left');
                        if (window.DlhBasemapSwitcher) {
                            var bs = new DlhBasemapSwitcher();
                            self.map.on('load', function () { bs.onAdd(self.map); });
                        }
                        self.marker = new maplibregl.Marker({ draggable: true, anchor: 'center' }).setLngLat([lon, lat]).addTo(self.map);
                        self.marker.on('dragend', function () { var ll = self.marker.getLngLat(); @this.set('latitude', ll.lat); @this.set('longitude', ll.lng); });
                        self.map.on('click', function (e) { self.marker.setLngLat(e.lngLat); @this.set('latitude', e.lngLat.lat); @this.set('longitude', e.lngLat.lng); });
                        setTimeout(function () { try { self.map.resize(); } catch (e) {} }, 150);
                    });
                },
                moveMarker(lat, lon) {
                    if (this.marker) this.marker.setLngLat([lon, lat]);
                    if (this.map) this.map.flyTo({ center: [lon, lat], zoom: 15, essential: true });
                }
            }">
            <div class="space-y-6">
                <x-public.input
                    wire:model="nama_pelapor"
                    name="nama_pelapor"
                    label="{{ __('Nama Pelapor') }}"
                    placeholder="{{ __('Nama lengkap pelapor') }}"
                    required
                />

                <x-public.select
                    wire:model.live="bidang"
                    id="bidang"
                    name="bidang"
                    label="{{ __('Bidang Pengaduan') }}"
                    :options="$this->getBidangOptions()"
                    :selected="$bidang"
                    :searchable="false"
                />

                <x-public.select
                    wire:key="jenis-pengaduan-{{ $bidang }}"
                    wire:model="jenis_pengaduan"
                    id="jenis_pengaduan"
                    name="jenis_pengaduan"
                    label="{{ __('Jenis Pengaduan') }}"
                    :options="$this->jenisOptions()"
                    :selected="$jenis_pengaduan"
                    :searchable="true"
                    placeholder="{{ __('-- Pilih Jenis Pengaduan --') }}"
                />

                <x-public.input
                    wire:model="nomor_hp"
                    name="nomor_hp"
                    type="tel"
                    maxlength="15"
                    label="{{ __('Nomor Telepon') }}"
                    placeholder="{{ __('Contoh: 08123456789') }}"
                    required
                />

                <div>
                    <button type="button" id="btn-detect-location"
                        class="fi-detect-btn">
                        <svg class="detect-icon-normal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>
                        <svg class="detect-icon-spin hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M21 12a9 9 0 1 1-6.22-8.56"/></svg>
                        <span class="detect-label">Deteksi Lokasi Saya</span>
                    </button>

                    <p class="detect-error hidden mt-2 flex items-start gap-1.5 text-xs font-medium text-red-600 dark:text-red-400">
                        <svg class="w-4 h-4 shrink-0 mt-px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
                        <span class="detect-error-text"></span>
                    </p>

                    <p class="detect-success hidden mt-2 flex items-center gap-1.5 text-xs font-semibold text-brand-600 dark:text-brand-400">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                        <span>{{ __('Lokasi terdeteksi — peta dan alamat terisi otomatis.') }}</span>
                    </p>
                </div>

                @if ($bidang === 'tata-penataan')
                    <x-public.input
                        wire:model="nama_terlapor"
                        name="nama_terlapor"
                        label="{{ __('Nama Terlapor') }}"
                        placeholder="{{ __('Nama individu yang dilaporkan') }}"
                    />

                    <x-public.input
                        wire:model="nama_perusahaan_terlapor"
                        name="nama_perusahaan_terlapor"
                        label="{{ __('Nama Perusahaan Terlapor') }}"
                        placeholder="{{ __('Nama perusahaan/industri terlapor') }}"
                    />
                @endif

                <x-public.textarea
                    wire:model="alamat"
                    name="alamat"
                    label="{{ __('Alamat Lokasi Kejadian') }}"
                    placeholder="{{ __('Alamat lengkap lokasi kejadian') }}"
                    rows="2"
                    maxlength="150"
                    hint="{{ __('Sertakan patokan terdekat') }}"
                    required
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
                    required
                    icon='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16M4 12h16M4 19h10"/></svg>'
                />

                <div class="fi-field">
                    <label class="fi-label">{{ __('Foto Bukti') }} <span class="fi-required">*</span> <span style="font-weight:400;color:#5b6b63;font-size:12.5px;">(min 1, max 5, JPG/PNG/WebP/AVIF/HEIC maksimal 5MB)</span></label>
                    <div class="fi-file-drop">
                        <button type="button" class="fi-file-btn" x-on:click="$refs.fileInput.click()">{{ __('Choose Files') }}</button>
                        <span class="fi-file-status">{{ __('No file chosen') }}</span>
                        <input wire:model="photos" x-ref="fileInput" type="file" multiple accept="image/jpeg,image/jpg,image/png,image/webp,image/avif,image/heic,image/heif,.jpg,.jpeg,.png,.webp,.avif,.heic,.heif" required aria-label="{{ __('Foto Bukti') }}"
                            style="position:absolute;width:1px;height:1px;opacity:0;overflow:hidden;"
                            x-on:change="
                                let files = $el.files;
                                if(files.length > 5){ alert('Maksimal 5 foto yang diizinkan!'); $el.value=''; return; }
                                const okTypes = ['image/jpeg','image/jpg','image/png','image/webp','image/avif','image/heic','image/heif'];
                                const okExts = ['jpg','jpeg','png','webp','avif','heic','heif'];
                                for(let f of files){
                                    if(f.size > 5*1024*1024){ alert('Ukuran foto ' + f.name + ' melebihi 5MB!'); $el.value=''; return; }
                                    const ext = f.name.split('.').pop().toLowerCase();
                                    if(!okTypes.includes(f.type) && !okExts.includes(ext)){ alert('File ' + f.name + ' bukan JPG/PNG/WebP/AVIF/HEIC!'); $el.value=''; return; }
                                }
                            "
                        />
                    </div>
                    @error('photos') <p class="fi-error"><svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>{{ $message }}</p> @enderror
                    @error('photos.*') <p class="fi-error"><svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>{{ $message }}</p> @enderror

                    @if ($photos)
                        <div class="grid grid-cols-3 gap-3 pt-3">
                            @foreach ($photos as $index => $photo)
                                <div class="relative aspect-square rounded-xl" style="overflow:visible;">
                                    <img src="{{ $photo->temporaryUrl() }}" alt="{{ __('Pratinjau foto bukti') }}" class="w-full h-full object-cover rounded-xl" />
                                    <button type="button"
                                        wire:click="removePhoto({{ $index }})"
                                        style="position:absolute;top:-6px;right:-6px;width:24px;height:24px;border-radius:50%;background:#ef4444;color:#fff;display:flex;align-items:center;justify-content:center;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.3);cursor:pointer;z-index:10;"
                                        title="{{ __('Hapus foto') }}">
                                        <svg style="width:12px;height:12px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                @error('form')
                    <div class="dlh-limit-alert" role="alert">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4M12 17h.01"/></svg>
                        <span>{{ $message }}</span>
                    </div>
                @enderror

        <x-google-recaptcha />

                <button type="submit" class="fi-submit-btn">
                    {{ __('Kirim Pengaduan') }}
                </button>
            </div>

            <div x-show="located" x-cloak class="space-y-6 flex flex-col">
                <label class="fi-label">
                    <span class="fi-icon-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.4 8.5c0 5.5-8.4 11.5-8.4 11.5S3.6 14 3.6 8.5a8.4 8.4 0 1 1 16.8 0Z"/><circle cx="12" cy="8.5" r="2.6"/></svg></span>
                    {{ __('Peta Lokasi Kejadian') }}
                </label>

                <div wire:ignore x-ref="mapEl"
                    class="w-full flex-1 min-h-[300px] rounded-2xl overflow-hidden relative z-0">
                </div>

                <div class="flex justify-between text-xs text-[#5b6b63] dark:text-slate-400 font-medium tabular-nums">
                    <span>Lat: {{ number_format($latitude, 6) }}</span>
                    <span>Lng: {{ number_format($longitude, 6) }}</span>
                </div>
                @error('latitude') <p class="fi-error"><svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>{{ $message }}</p> @enderror
                @error('longitude') <p class="fi-error"><svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>{{ $message }}</p> @enderror
            </div>
        </form>
    @endif

    <style>

        /* ── File Upload ── */
        .fi-file-drop {
            border: 1.5px dashed #a9dcc0;
            border-radius: 16px;
            background: #f4faf6;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: border-color .18s ease, background .18s ease;
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
        }

        .fi-file-drop:hover {
            background: #eefaf3;
            border-color: #1ea567;
        }

        .fi-file-btn {
            flex-shrink: 0;
            height: 38px;
            padding: 0 20px;
            border-radius: 9999px;
            border: none;
            background: linear-gradient(180deg, #178a53, #146a44);
            color: #fff;
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 10px -2px rgba(20, 106, 68, 0.4);
            transition: filter .15s ease;
        }

        .fi-file-btn:hover {
            filter: brightness(1.05);
        }

        .fi-file-status {
            font-size: 13px;
            color: #5f7268;
        }

        .fi-file-hidden {
            display: none;
        }

        /* ── Submit Button ── */
        .fi-submit-btn {
            width: 100%;
            height: 52px;
            border: none;
            border-radius: 9999px;
            background: linear-gradient(180deg, #178a53, #146a44);
            color: #fff;
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: .2px;
            cursor: pointer;
            box-shadow: 0 10px 24px -8px rgba(20, 106, 68, 0.55);
            transition: transform .12s ease, box-shadow .12s ease;
        }

        .fi-submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 28px -8px rgba(20, 106, 68, 0.6);
        }

        .fi-submit-btn:active {
            transform: translateY(0);
        }

        /* ── Form card ── */
        .fi-form-card {
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
        }

        /* ── Map label font fix ── */
        .fi-label.fi-label { font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif; }

        /* ── Dark mode file/submit ── */
        .dark .fi-file-drop {
            background: #0f172a;
            border-color: #334155;
        }
        .dark .fi-file-drop:hover {
            background: #1e293b;
            border-color: #1ea567;
        }
        .dark .fi-file-status { color: #64748b; }
        .dark .fi-submit-btn {
            background: linear-gradient(180deg, #1ea567, #178a53);
            box-shadow: 0 10px 24px -8px rgba(30, 165, 103, 0.5);
        }

        .fi-detect-btn {
            width: 100%;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border-radius: 12px;
            background: linear-gradient(180deg, #178a53, #146a44);
            color: #fff;
            font-size: 13.5px;
            font-weight: 700;
            letter-spacing: 0.02em;
            box-shadow: 0 8px 20px -6px rgba(20,106,68,0.5);
            transition: filter .18s ease, transform .12s ease;
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
            cursor: pointer;
            border: none;
        }
        .fi-detect-btn:hover { filter: brightness(1.1); }
        .fi-detect-btn:active { transform: scale(0.99); }
        .fi-detect-btn:disabled { opacity: 0.6; cursor: not-allowed; }
        .fi-detect-btn .detect-icon-normal,
        .fi-detect-btn .detect-icon-spin {
            width: 18px; height: 18px; flex-shrink: 0;
        }
        .fi-detect-btn .detect-icon-spin { animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Phone validation
        const phoneInput = document.querySelector('input[name="nomor_hp"]');
        if(phoneInput) {
            phoneInput.addEventListener('blur', function() {
                const val = this.value.trim();
                if(val && val.length > 15) {
                    alert('Nomor telepon maksimal 15 digit!');
                    this.value = val.substring(0, 15);
                }
            });
        }

        // Detect location button (vanilla JS — no Alpine dependency)
        var btnDetect = document.getElementById('btn-detect-location');
        if (!btnDetect) return;

        btnDetect.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!navigator.geolocation) {
                var errEl = btnDetect.closest('div').querySelector('.detect-error');
                var errText = btnDetect.closest('div').querySelector('.detect-error-text');
                if (errEl && errText) {
                    errText.textContent = 'Browser Anda tidak mendukung geolokasi.';
                    errEl.classList.remove('hidden');
                }
                return;
            }

            btnDetect.disabled = true;
            btnDetect.querySelector('.detect-icon-normal').classList.add('hidden');
            btnDetect.querySelector('.detect-icon-spin').classList.remove('hidden');
            btnDetect.querySelector('.detect-label').textContent = 'Mendeteksi Lokasi...';

            navigator.geolocation.getCurrentPosition(function(pos) {
                var lat = pos.coords.latitude;
                var lon = pos.coords.longitude;

                fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=' + lat + '&lon=' + lon + '&zoom=18&addressdetails=1&accept-language=id')
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        var addr = '';
                        if (data && data.address) {
                            var a = data.address;
                            var parts = [];
                            if (a.road) parts.push(a.road);
                            if (a.suburb || a.village || a.hamlet) parts.push(a.suburb || a.village || a.hamlet);
                            if (a.city || a.town || a.county) parts.push(a.city || a.town || a.county);
                            if (a.state) parts.push(a.state);
                            if (a.postcode) parts.push(a.postcode);
                            if (a.country) parts.push(a.country);
                            addr = parts.join(', ');
                        }
                        if (!addr && data.display_name) addr = data.display_name;
                        var alamatEl = document.querySelector('[wire\\:model="alamat"]');
                        if (alamatEl) {
                            alamatEl.value = addr;
                            alamatEl.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    })
                    .catch(function() {})
                    .finally(function() {
                        var latEl = document.querySelector('[wire\\:model="latitude"]');
                        var lonEl = document.querySelector('[wire\\:model="longitude"]');
                        if (latEl) { latEl.value = lat; latEl.dispatchEvent(new Event('input', { bubbles: true })); }
                        if (lonEl) { lonEl.value = lon; lonEl.dispatchEvent(new Event('input', { bubbles: true })); }

                        var wrapper = btnDetect.closest('div');
                        wrapper.querySelector('.detect-success').classList.remove('hidden');
                        wrapper.querySelector('.detect-error').classList.add('hidden');

                        btnDetect.disabled = false;
                        btnDetect.querySelector('.detect-icon-normal').classList.remove('hidden');
                        btnDetect.querySelector('.detect-icon-spin').classList.add('hidden');
                        btnDetect.querySelector('.detect-label').textContent = 'Deteksi Lokasi Saya';

                        var form = btnDetect.closest('form');
                        if (form && window.Alpine) {
                            var formData = Alpine.$data(form);
                            if (formData) {
                                formData.located = true;
                                formData.detecting = false;
                                setTimeout(function() { formData.initMap(lat, lon); }, 100);
                            }
                        }
                    });
            }, function(err) {
                btnDetect.disabled = false;
                btnDetect.querySelector('.detect-icon-normal').classList.remove('hidden');
                btnDetect.querySelector('.detect-icon-spin').classList.add('hidden');
                btnDetect.querySelector('.detect-label').textContent = 'Deteksi Lokasi Saya';

                var wrapper = btnDetect.closest('div');
                var errEl = wrapper.querySelector('.detect-error');
                var errText = wrapper.querySelector('.detect-error-text');
                var msg = 'Gagal mendapatkan lokasi.';
                if (err.code === 1) msg = 'Izin lokasi ditolak. Izinkan akses lokasi pada browser Anda.';
                else if (err.code === 2) msg = 'Posisi tidak dapat ditentukan. Coba lagi.';
                else if (err.code === 3) msg = 'Waktu habis. Silakan coba lagi.';
                if (errEl && errText) {
                    errText.textContent = msg;
                    errEl.classList.remove('hidden');
                }
            }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 30000 });
        });
    });
    </script>
</div>
