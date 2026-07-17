<?php

use App\Enums\Bidang;
use App\Enums\JenisPengaduanPengendalian;
use App\Enums\JenisPengaduanSampah;
use App\Enums\JenisPengaduanTataPenataan;
use App\Enums\JenisPengaduanRth;
use App\Enums\PengaduanStatus;
use App\Models\Laporan;
use App\Models\LaporanFoto;
use App\Models\PengaduanTataPenataan;
use App\Models\PengaduanTataPenataanFoto;
use App\Services\ImageCompressionService;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public string $bidang = 'pengendalian';
    public ?string $nama_pelapor = null;
    public ?string $nomor_hp = null;
    public ?string $email = null;
    public ?string $jenis_pengaduan = null;
    public ?string $jenis_pengaduan_lainnya = null;
    public ?string $nama_terlapor = null;
    public ?string $nama_perusahaan_terlapor = null;
    public ?string $alamat = null;
    public float $latitude = -0.9;
    public float $longitude = 119.87;
    public ?string $deskripsi = null;
    public array $photos = [];

    public ?string $successTicket = null;

    public function updatedBidang(): void
    {
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

    public function submit(ImageCompressionService $compressionService): void
    {
        $this->validate($this->rules(), $this->messages());

        $ip = request()->ip();
        $limiterKey = 'pengaduan-unified:' . $this->bidang . ':' . $ip;
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($limiterKey, 5)) {
            $this->addError('nomor_hp', __('Batas maksimal pengiriman tercapai (5 pengaduan per jam).'));

            return;
        }

        \Illuminate\Support\Facades\RateLimiter::hit($limiterKey, 3600);

        if ($this->bidang === 'tata-penataan') {
            $pengaduan = PengaduanTataPenataan::create([
                'nama_pelapor' => $this->nama_pelapor,
                'no_hp' => $this->nomor_hp,
                'email' => $this->email,
                'jenis_pengaduan' => $this->jenis_pengaduan === '__lainnya__' ? $this->jenis_pengaduan_lainnya : $this->jenis_pengaduan,
                'nama_terlapor' => $this->nama_terlapor ?? null,
                'nama_perusahaan_terlapor' => $this->nama_perusahaan_terlapor ?? null,
                'alamat' => $this->alamat,
                'deskripsi' => $this->deskripsi,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ]);

            foreach ($this->photos as $photo) {
                $path = $compressionService->compressAndStore($photo, 'pengaduan-tata-penataan');
                PengaduanTataPenataanFoto::create([
                    'pengaduan_tata_penataan_id' => $pengaduan->id,
                    'path_foto' => $path,
                ]);
            }

            $this->successTicket = $pengaduan->nomor_tiket;
        } else {
            $bidangEnum = match ($this->bidang) {
                'pengendalian' => Bidang::PENGENDALIAN,
                'sampah' => Bidang::SAMPAH_LB3,
                'rth' => Bidang::RTH,
            };

            $storageDir = match ($this->bidang) {
                'pengendalian' => 'pengaduan-pengendalian',
                default => 'laporans',
            };

            $laporan = Laporan::create([
                'bidang' => $bidangEnum->value,
                'nama_pelapor' => $this->nama_pelapor,
                'nomor_hp' => $this->nomor_hp,
                'email' => $this->email,
                'jenis_pengaduan' => $this->jenis_pengaduan === '__lainnya__' ? $this->jenis_pengaduan_lainnya : $this->jenis_pengaduan,
                'kategori' => $this->jenis_pengaduan === '__lainnya__' ? $this->jenis_pengaduan_lainnya : $this->jenis_pengaduan,
                'alamat' => $this->alamat,
                'deskripsi' => $this->deskripsi,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'status' => PengaduanStatus::BELUM_DITINJAU->value,
            ]);

            foreach ($this->photos as $photo) {
                $path = $compressionService->compressAndStore($photo, $storageDir);
                LaporanFoto::create([
                    'laporan_id' => $laporan->id,
                    'path_foto' => $path,
                ]);
            }

            $this->successTicket = $laporan->nomor_tiket;
        }

        $this->resetForm();
    }

    private function rules(): array
    {
        $base = [
            'nama_pelapor' => ['required', 'string', 'max:255'],
            'nomor_hp' => ['required', 'string', 'regex:/^(?:\+62|62|0)8[1-9][0-9]{6,12}$/', 'max:15'],
            'email' => ['required', 'email:rfc,dns', 'max:255', 'ends_with:gmail.com'],
            'alamat' => ['required', 'string', 'max:1000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'deskripsi' => ['required', 'string', 'max:5000'],
            'photos' => ['required', 'array', 'min:1', 'max:5'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];

        $jenisValues = match ($this->bidang) {
            'pengendalian' => array_column(JenisPengaduanPengendalian::cases(), 'value'),
            'sampah' => array_column(JenisPengaduanSampah::cases(), 'value'),
            'tata-penataan' => array_column(JenisPengaduanTataPenataan::cases(), 'value'),
            'rth' => array_column(JenisPengaduanRth::cases(), 'value'),
        };

        $base['jenis_pengaduan'] = ['required', 'string', \Illuminate\Validation\Rule::in(array_merge($jenisValues, ['__lainnya__']))];
        $base['jenis_pengaduan_lainnya'] = ['required_if:jenis_pengaduan,__lainnya__', 'nullable', 'string', 'max:255'];

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
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.ends_with' => 'Email harus menggunakan domain @gmail.com.',
            'photos.required' => 'Foto bukti wajib diunggah minimal 1 foto.',
            'photos.array' => 'Foto bukti harus berupa array.',
            'photos.min' => 'Foto bukti minimal 1 foto.',
            'photos.max' => 'Foto bukti maksimal 5 foto.',
            'photos.*.image' => 'File harus berupa gambar.',
            'photos.*.mimes' => 'Format foto harus JPG atau PNG.',
            'photos.*.max' => 'Ukuran foto maksimal 2MB.',
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
            'nama_pelapor', 'nomor_hp', 'email', 'jenis_pengaduan', 'jenis_pengaduan_lainnya', 'nama_terlapor',
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

        return array_merge($options, ['__lainnya__' => __('Lainnya...')]);
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
        return match ($this->bidang) {
            'pengendalian' => '/cek-pengaduan-pengendalian',
            'sampah' => '/cek-pengaduan-sampah',
            'tata-penataan' => '/cek-pengaduan-tata-penataan',
            'rth' => '/cek-pengaduan-rth',
        };
    }
};
?>

<div
    class="fi-form-card bg-white dark:bg-slate-950 rounded-2xl p-6 md:p-8 shadow-[0_1px_3px_rgba(13,43,29,0.06),0_12px_32px_-12px_rgba(13,43,29,0.10)] max-w-4xl mx-auto">
    @if ($successTicket)
        <div class="space-y-6 text-center py-8">
            <div
                class="h-16 w-16 bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 rounded-full flex items-center justify-center mx-auto text-3xl font-bold">
                ✓
            </div>
            <div class="space-y-2">
                <h3 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ __('Pengaduan Berhasil Terkirim') }}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">{{ __('Terima kasih atas pengaduan Anda. Simpan nomor tiket di bawah untuk mengecek status pengaduan.') }}</p>
            </div>
            <div
                class="p-4 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg max-w-xs mx-auto">
                <span class="block text-[10px] text-brand-600 dark:text-brand-400 font-extrabold tracking-widest uppercase">{{ __('Nomor Tiket Anda') }}</span>
                <span class="block text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1 select-all tracking-wider">{{ $successTicket }}</span>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 justify-center pt-4">
                <a href="{{ $this->getCekUrl() }}"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors border border-slate-200 hover:bg-slate-100 h-10 py-2 px-4 dark:border-slate-800 dark:hover:bg-slate-800">
                    {{ __('Cek Status Pengaduan') }}
                </a>
                <button wire:click="$set('successTicket', null)"
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
                    required
                />

                <x-public.select
                    wire:model.live="bidang"
                    id="bidang"
                    name="bidang"
                    label="{{ __('Bidang Pengaduan') }}"
                    :options="$this->getBidangOptions()"
                    :searchable="false"
                />

                <x-public.select
                    wire:key="jenis-pengaduan-{{ $bidang }}"
                    wire:model="jenis_pengaduan"
                    id="jenis_pengaduan"
                    name="jenis_pengaduan"
                    label="{{ __('Jenis Pengaduan') }}"
                    :options="$this->jenisOptions()"
                    :searchable="true"
                    placeholder="{{ __('-- Pilih Jenis Pengaduan --') }}"
                />

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

                <x-public.input
                    wire:model="nomor_hp"
                    name="nomor_hp"
                    type="tel"
                    maxlength="15"
                    label="{{ __('Nomor Telepon') }}"
                    placeholder="{{ __('Contoh: 08123456789') }}"
                    required
                />

                <x-public.input
                    wire:model="email"
                    name="email"
                    type="email"
                    label="{{ __('Email') }}"
                    placeholder="contoh@gmail.com"
                    required
                    hint="{{ __('Email untuk menerima notifikasi update status pengaduan') }}"
                />

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
                    <label class="fi-label">{{ __('Foto Bukti') }} <span class="fi-required">*</span> <span style="font-weight:400;color:#5b6b63;font-size:12.5px;">(min 1, max 5, JPG/PNG max 2MB)</span></label>
                    <div class="fi-file-drop">
                        <button type="button" class="fi-file-btn" x-on:click="$refs.fileInput.click()">{{ __('Choose Files') }}</button>
                        <span class="fi-file-status">{{ __('No file chosen') }}</span>
                        <input wire:model="photos" x-ref="fileInput" type="file" multiple accept="image/jpeg,image/png,image/jpg" required
                            style="position:absolute;width:1px;height:1px;opacity:0;overflow:hidden;"
                            x-on:change="
                                let files = $el.files;
                                if(files.length > 5){ alert('Maksimal 5 foto yang diizinkan!'); $el.value=''; return; }
                                for(let f of files){
                                    if(f.size > 2*1024*1024){ alert('Ukuran foto \"'+f.name+'\" melebihi 2MB!'); $el.value=''; return; }
                                    if(!['image/jpeg','image/png','image/jpg'].includes(f.type)){ alert('File \"'+f.name+'\" bukan JPG/PNG!'); $el.value=''; return; }
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
            </div>

            <div class="space-y-6 flex flex-col justify-between">
                <div class="space-y-2.5 flex-1 flex flex-col">
                    <label class="fi-label">
                        <span class="fi-icon-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.4 8.5c0 5.5-8.4 11.5-8.4 11.5S3.6 14 3.6 8.5a8.4 8.4 0 1 1 16.8 0Z"/><circle cx="12" cy="8.5" r="2.6"/></svg></span>
                        {{ __('Tentukan Lokasi (Klik Peta)') }}
                    </label>
                    <div wire:ignore
                        class="w-full flex-1 min-h-[300px] rounded-2xl overflow-hidden relative z-0"
                        x-data="{
                            map: null, marker: null,
                            initMap() {
                                var self = this;
                                window.ensureMaplibreLoaded(function() {
                                    self.map = new maplibregl.Map({ container: self.$el, style: 'https://basemaps.cartocdn.com/gl/voyager-gl-style/style.json', center: [@js($longitude), @js($latitude)], zoom: 13, attributionControl: false });
                                    self.map.addControl(new maplibregl.NavigationControl({ showCompass: false, visualizePitch: false }), 'top-left');

                                    // Basemap switcher — append langsung ke map container
                                    if (window.DlhBasemapSwitcher) {
                                        var bs = new DlhBasemapSwitcher();
                                        self.map.on('load', function() { bs.onAdd(self.map); });
                                    }

                                    self.marker = new maplibregl.Marker({ draggable: true, anchor: 'center' }).setLngLat([@js($longitude), @js($latitude)]).addTo(self.map);
                                    self.marker.on('dragend', function() { var ll = self.marker.getLngLat(); @this.set('latitude', ll.lat); @this.set('longitude', ll.lng); });
                                    self.map.on('click', function(e) { self.marker.setLngLat(e.lngLat); @this.set('latitude', e.lngLat.lat); @this.set('longitude', e.lngLat.lng); });
                                    dlhAddLocBtn(self.map, function(lat, lng) { self.marker.setLngLat([lng, lat]); @this.set('latitude', lat); @this.set('longitude', lng); });
                                });
                            }
                        }" x-init="initMap()">
                    </div>
                    <div class="flex justify-between text-xs text-[#5b6b63] dark:text-slate-400 mt-2 font-medium tabular-nums">
                        <span>Lat: {{ number_format($latitude, 6) }}</span>
                        <span>Lng: {{ number_format($longitude, 6) }}</span>
                    </div>
                    @error('latitude') <p class="fi-error"><svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>{{ $message }}</p> @enderror
                    @error('longitude') <p class="fi-error"><svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="fi-submit-btn">
                    {{ __('Kirim Pengaduan') }}
                </button>
            </div>
        </form>
    @endif

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap');

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
            font-family: 'Outfit', ui-sans-serif, system-ui, sans-serif;
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
            font-family: 'Outfit', ui-sans-serif, system-ui, sans-serif;
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
            color: #9fb0a8;
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
            font-family: 'Outfit', ui-sans-serif, system-ui, sans-serif;
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
            font-family: 'Outfit', ui-sans-serif, system-ui, sans-serif;
        }

        /* ── Map label font fix ── */
        .fi-label.fi-label { font-family: 'Outfit', ui-sans-serif, system-ui, sans-serif; }

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

        // Email validation
        const emailInput = document.querySelector('input[name="email"]');
        if(emailInput) {
            emailInput.addEventListener('blur', function() {
                const val = this.value.trim();
                if(val && !val.endsWith('@gmail.com')) {
                    alert('Email harus menggunakan domain @gmail.com!');
                }
            });
        }
    });
    </script>
</div>
