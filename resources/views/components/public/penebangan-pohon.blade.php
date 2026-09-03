<?php

use App\Enums\JenisTindakanPohon;
use App\Enums\StatusPermohonanPohon;
use App\Livewire\Concerns\ThrottlesPublic;
use App\Livewire\Concerns\VerifiesGoogleRecaptcha;
use App\Models\PermohonanPohon;
use App\Services\FileUploadService;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;
    use VerifiesGoogleRecaptcha;
    use ThrottlesPublic;

    // Form fields
    public string $nama_pelapor = '';
    public string $nomor_hp = '';
    public string $jenis_tindakan = 'Pemangkasan';
    public string $lokasi_pohon = '';
    public ?float $latitude = -0.8917;
    public ?float $longitude = 119.8707;
    public string $jenis_pohon = '';
    public string $alasan_pengajuan = '';
    public ?TemporaryUploadedFile $foto_pohon = null;
    public string $keterangan_tambahan = '';
    public bool $konfirmasi_area_publik = false;

    public ?string $successTicket = null;

    protected function rules(): array
    {
        return [
            'nama_pelapor' => 'required|string|min:3|max:150',
            'nomor_hp' => 'required|string|min:8|max:25',
            'jenis_tindakan' => 'required|in:Pemangkasan,Penebangan',
            'lokasi_pohon' => 'required|string|min:10|max:500',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'jenis_pohon' => 'nullable|string|max:100',
            'alasan_pengajuan' => 'required|string|min:10|max:1000',
            'foto_pohon' => 'required|image|mimes:jpg,jpeg,png,webp,avif|max:5120',
            'keterangan_tambahan' => 'nullable|string|max:1000',
            'konfirmasi_area_publik' => 'accepted',
        ];
    }

    protected function messages(): array
    {
        return [
            'nama_pelapor.required' => 'Nama lengkap pelapor wajib diisi.',
            'nomor_hp.required' => 'Nomor WhatsApp wajib diisi.',
            'jenis_tindakan.required' => 'Jenis tindakan wajib dipilih.',
            'lokasi_pohon.required' => 'Lokasi pohon wajib diisi dengan jelas.',
            'alasan_pengajuan.required' => 'Alasan pengajuan wajib dijelaskan.',
            'foto_pohon.required' => 'Foto kondisi pohon wajib diunggah.',
            'foto_pohon.image' => 'File harus berupa foto/gambar.',
            'foto_pohon.max' => 'Ukuran foto maksimal 5MB.',
            'konfirmasi_area_publik.accepted' => 'Anda harus mengonfirmasi bahwa pohon berada di fasilitas umum / area publik dan bukan di pekarangan pribadi.',
        ];
    }

    public function submit()
    {
        if (! $this->verifyCaptcha('submit')) {
            return;
        }

        $this->resetCaptcha();
        $this->validate();

        if ($this->hitRateLimit('permohonan-pohon:submit', 5, 'form', __('Pengiriman permohonan dibatasi maksimal 5 per jam.'))) {
            return;
        }

        $storedPath = null;
        if ($this->foto_pohon) {
            $storedPath = app(FileUploadService::class)->store($this->foto_pohon, 'permohonan-pohon', 'public');
        }

        $permohonan = PermohonanPohon::create([
            'nama_pelapor' => $this->nama_pelapor,
            'nomor_hp' => $this->nomor_hp,
            'jenis_tindakan' => $this->jenis_tindakan,
            'lokasi_pohon' => $this->lokasi_pohon,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'jenis_pohon' => $this->jenis_pohon ?: null,
            'alasan_pengajuan' => $this->alasan_pengajuan,
            'foto_pohon' => $storedPath,
            'keterangan_tambahan' => $this->keterangan_tambahan ?: null,
            'status' => StatusPermohonanPohon::DIAJUKAN->value,
        ]);

        $this->successTicket = $permohonan->nomor_tiket;
        $this->reset(['nama_pelapor', 'nomor_hp', 'lokasi_pohon', 'jenis_pohon', 'alasan_pengajuan', 'foto_pohon', 'keterangan_tambahan', 'konfirmasi_area_publik']);
    }
}; ?>

<div class="space-y-6">
    {{-- KETENTUAN LAYANAN: KHUSUS POHON DI AREA PUBLIK / FASILITAS UMUM --}}
    <div class="rounded-2xl bg-gradient-to-br from-amber-50/80 via-orange-50/40 to-amber-50/60 dark:from-amber-950/25 dark:via-orange-950/15 dark:to-amber-950/10 shadow-[0_4px_24px_-6px_rgba(217,119,6,0.15)] overflow-hidden">
        {{-- Header Strip --}}
        <div class="bg-gradient-to-r from-amber-500 via-amber-500 to-orange-400 dark:from-amber-600 dark:via-amber-600 dark:to-orange-500 px-5 sm:px-6 py-4 flex items-center gap-3.5">
            <span class="size-9 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shrink-0 ring-1 ring-white/10">
                <x-icons.ui name="alert-triangle" class="size-5 text-white" />
            </span>
            <div class="flex flex-col">
                <span class="text-[11px] font-black tracking-widest uppercase text-white/70">
                    {{ __('KETENTUAN LAYANAN') }}
                </span>
                <span class="text-sm sm:text-base font-bold text-white mt-0.5">
                    {{ __('Khusus Pohon di Area Publik & Fasilitas Umum') }}
                </span>
            </div>
        </div>

        {{-- Body --}}
        <div class="px-5 sm:px-6 pt-5 pb-6 sm:pt-6 sm:pb-7 space-y-5">
            {{-- Penjelasan Ruang Lingkup --}}
            <p class="text-sm sm:text-[15px] text-slate-700 dark:text-slate-300 leading-relaxed">
                Dinas Lingkungan Hidup (DLH) Kota Palu <strong class="font-bold text-slate-900 dark:text-white">hanya melayani permohonan penebangan atau pemangkasan pohon pelindung/perindang yang berada di fasilitas umum : </strong>.
            </p>

            {{-- Daftar Area yang Dilayani --}}
            <div class="grid sm:grid-cols-2 gap-2.5">
                @foreach ([
                    'Sempadan jalan raya',
                    'Trotoar & jalur hijau publik',
                    'Median jalan',
                    'Taman kota',
                ] as $area)
                    <div class="flex items-center gap-2.5 text-sm text-slate-600 dark:text-slate-400">
                        <span class="size-5.5 rounded-full bg-emerald-500/10 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        </span>
                        <span>{{ __($area) }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Peringatan PENTING --}}
            <div class="rounded-xl overflow-hidden" style="background: linear-gradient(135deg, #e11d48, #dc2626);">
                <div class="px-5 sm:px-6 py-5 sm:py-6 space-y-3.5">
                    {{-- PENTING Label --}}
                    <div class="flex items-center gap-2.5">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-black tracking-widest uppercase bg-white/20 text-white backdrop-blur-sm">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                            {{ __('PENTING') }}
                        </span>
                    </div>

                    {{-- Pesan Utama --}}
                    <p class="text-[15px] sm:text-base font-semibold text-white leading-relaxed">
                        {{ __('DLH tidak menerima laporan atau permohonan untuk pohon yang berada di area pribadi') }}
                        <span class="font-normal text-white/75">{{ __('(halaman rumah, pekarangan pribadi, lahan milik pribadi, dan area privat lainnya)') }}</span>,
                        <strong class="font-bold text-white">{{ __('kecuali keadaan darurat (pohon tumbang).') }}</strong>
                    </p>

                    {{-- Catatan Kaki --}}
                    <p class="text-xs sm:text-[13px] italic text-white/60 leading-relaxed">
                        {{ __('Pemangkasan/penebangan pohon pada area pribadi sepenuhnya menjadi tanggung jawab pemilik lahan.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- KARTU FORMULIR (CLEAN, MINIMAL, RAPI) --}}
    <div class="fi-form-card bg-white dark:bg-slate-950 rounded-2xl p-6 md:p-8 shadow-[0_1px_3px_rgba(13,43,29,0.06),0_12px_32px_-12px_rgba(13,43,29,0.10)] max-w-4xl mx-auto space-y-6">
        @if ($successTicket)
            <div class="space-y-6 text-center py-8">
                <div class="h-16 w-16 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-full flex items-center justify-center mx-auto">
                    <x-icons.berhasil class="size-8" />
                </div>
                <div class="space-y-2">
                    <h3 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ __('Permohonan Berhasil Terkirim') }}</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                        {{ __('Laporan permohonan Anda telah terdaftar di DLH Kota Palu. Simpan nomor tiket di bawah untuk memantau status survei dan pelaksanaan tindakan.') }}
                    </p>
                </div>
                <div class="p-4 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg max-w-xs mx-auto">
                    <span class="block text-[10px] text-emerald-600 dark:text-emerald-400 font-bold tracking-widest uppercase">{{ __('Nomor Tiket Anda') }}</span>
                    <x-public.copy-ticket :ticket="$successTicket" class="block text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1 select-all tracking-wider font-mono" />
                </div>
                <div class="flex flex-col sm:flex-row gap-3 justify-center pt-4">
                    <a
                        href="{{ url('/cek-permohonan-pohon?tiket=' . $successTicket) }}"
                        class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors bg-emerald-600 hover:bg-emerald-700 text-white h-10 py-2 px-5 shadow-xs"
                    >
                        {{ __('Cek Status Permohonan Ini') }}
                    </a>
                    <button
                        wire:click="$set('successTicket', null)"
                        class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors border border-slate-200 hover:bg-slate-100 h-10 py-2 px-4 dark:border-slate-800 dark:hover:bg-slate-800 cursor-pointer"
                    >
                        {{ __('Ajukan Permohonan Baru') }}
                    </button>
                </div>
            </div>
        @else
            <form wire:submit.prevent="submit" @if(\App\Support\Captcha::enabled()) data-dlh-recaptcha-action="submit" @endif class="space-y-5">
                {{-- Data Pelapor --}}
                <div class="grid md:grid-cols-2 gap-5">
                    <x-public.input
                        wire:model="nama_pelapor"
                        name="nama_pelapor"
                        label="{{ __('Nama Lengkap Pelapor') }}"
                        placeholder="{{ __('Nama pelapor sesuai identitas') }}"
                        required
                    />

                    <x-public.input
                        wire:model="nomor_hp"
                        name="nomor_hp"
                        type="tel"
                        label="{{ __('Nomor WhatsApp') }}"
                        placeholder="{{ __('Contoh: 081234567890') }}"
                        required
                    />
                </div>

                {{-- Jenis Tindakan (MINIMAL, CLEAN RADIO CARDS, NO BLACK BORDER) --}}
                <div x-data="{ selectedTindakan: @entangle('jenis_tindakan') }">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                        {{ __('Jenis Tindakan yang Dimohonkan') }} <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="relative flex items-start gap-3 p-3.5 rounded-xl border transition-all cursor-pointer select-none"
                            :class="selectedTindakan === 'Pemangkasan' ? 'border-emerald-500 bg-emerald-50/50 dark:border-emerald-500 dark:bg-emerald-950/20 ring-1 ring-emerald-500/30' : 'border-slate-200 bg-white hover:border-slate-300 dark:border-slate-800 dark:bg-slate-900'">
                            <input type="radio" x-model="selectedTindakan" value="Pemangkasan" class="mt-0.5 text-emerald-600 focus:ring-emerald-500 size-4 cursor-pointer" />
                            <div>
                                <span class="block text-sm font-bold text-slate-900 dark:text-white">{{ __('Pemangkasan (Pruning)') }}</span>
                                <span class="block text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">{{ __('Pemotongan dahan/ranting yang rimbun, menutupi penerangan jalan, atau mengenai kabel utilitas.') }}</span>
                            </div>
                        </label>

                        <label class="relative flex items-start gap-3 p-3.5 rounded-xl border transition-all cursor-pointer select-none"
                            :class="selectedTindakan === 'Penebangan' ? 'border-emerald-500 bg-emerald-50/50 dark:border-emerald-500 dark:bg-emerald-950/20 ring-1 ring-emerald-500/30' : 'border-slate-200 bg-white hover:border-slate-300 dark:border-slate-800 dark:bg-slate-900'">
                            <input type="radio" x-model="selectedTindakan" value="Penebangan" class="mt-0.5 text-emerald-600 focus:ring-emerald-500 size-4 cursor-pointer" />
                            <div>
                                <span class="block text-sm font-bold text-slate-900 dark:text-white">{{ __('Penebangan Total') }}</span>
                                <span class="block text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">{{ __('Penebangan habis pohon yang telah lapuk, condong berbahaya, atau akar merusak struktur fasum.') }}</span>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Lokasi Pohon di Fasum --}}
                <div>
                    <x-public.textarea
                        wire:model="lokasi_pohon"
                        name="lokasi_pohon"
                        rows="2"
                        label="{{ __('Alamat / Lokasi Pohon di Fasilitas Umum') }}"
                        placeholder="{{ __('Sebutkan nama jalan, kelurahan, dan patokan terdekat. Contoh: Jl. Sam Ratulangi No. 12 (depan Kantor Pos), Kel. Besusu Barat, posisi pohon di pinggir jalan raya.') }}"
                        required
                    />
                </div>

                {{-- Map Picker Koordinat Pohon --}}
                <div
                    wire:ignore
                    x-data="{
                        map: null,
                        marker: null,
                        currentLat: @entangle('latitude'),
                        currentLng: @entangle('longitude'),
                        locating: false,
                        initMap(lat, lon) {
                            var self = this;
                            if (this.map) return;
                            window.ensureMaplibreLoaded(function () {
                                self.map = new maplibregl.Map({
                                    container: self.$refs.mapContainer,
                                    style: 'https://basemaps.cartocdn.com/gl/voyager-gl-style/style.json',
                                    center: [lon, lat],
                                    zoom: 15,
                                    attributionControl: false
                                });
                                self.map.addControl(new DlhZoomControl(), 'top-left');

                                self.marker = new maplibregl.Marker({ draggable: true, anchor: 'center' })
                                    .setLngLat([lon, lat])
                                    .addTo(self.map);

                                self.marker.on('dragend', function () {
                                    var ll = self.marker.getLngLat();
                                    self.currentLat = ll.lat;
                                    self.currentLng = ll.lng;
                                });

                                self.map.on('click', function (e) {
                                    self.marker.setLngLat(e.lngLat);
                                    self.currentLat = e.lngLat.lat;
                                    self.currentLng = e.lngLat.lng;
                                });

                                setTimeout(function () { try { self.map.resize(); } catch(e){} }, 250);
                            });
                        },
                        geolocate() {
                            var self = this;
                            if (!navigator.geolocation) return;
                            self.locating = true;
                            navigator.geolocation.getCurrentPosition(function (pos) {
                                var lat = pos.coords.latitude;
                                var lon = pos.coords.longitude;
                                self.currentLat = lat;
                                self.currentLng = lon;
                                if (self.marker) self.marker.setLngLat([lon, lat]);
                                if (self.map) self.map.flyTo({ center: [lon, lat], zoom: 16 });
                                self.locating = false;
                            }, function () {
                                self.locating = false;
                            }, { enableHighAccuracy: true, timeout: 10000 });
                        }
                    }"
                    x-init="initMap({{ $latitude ?? -0.8917 }}, {{ $longitude ?? 119.8707 }})"
                    class="space-y-2"
                >
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            {{ __('Titik Lokasi / Koordinat Pohon pada Peta') }} <span class="text-xs font-normal lowercase text-slate-400">({{ __('klik peta atau geser pin') }})</span>
                        </label>
                        <button
                            type="button"
                            @click="geolocate()"
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 transition cursor-pointer"
                        >
                            <x-icons.ui name="map-pin" class="size-3.5" />
                            <span x-text="locating ? 'Mencari...' : 'Gunakan Lokasi Saya'"></span>
                        </button>
                    </div>

                    <div
                        wire:ignore
                        x-ref="mapContainer"
                        style="height: 240px;"
                        class="w-full rounded-xl overflow-hidden border border-slate-200 bg-slate-100 dark:border-slate-800 dark:bg-slate-900 shadow-inner"
                    ></div>

                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800">
                            <span class="text-slate-400 font-semibold text-[10px] uppercase">Latitude:</span>
                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200 ml-1" x-text="currentLat ? Number(currentLat).toFixed(6) : '-'"></span>
                        </div>
                        <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800">
                            <span class="text-slate-400 font-semibold text-[10px] uppercase">Longitude:</span>
                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200 ml-1" x-text="currentLng ? Number(currentLng).toFixed(6) : '-'"></span>
                        </div>
                    </div>
                </div>

                {{-- Jenis Pohon & Upload Foto --}}
                <div class="grid md:grid-cols-2 gap-5">
                    <x-public.input
                        wire:model="jenis_pohon"
                        name="jenis_pohon"
                        label="{{ __('Jenis Pohon (Opsional / Jika Diketahui)') }}"
                        placeholder="{{ __('Contoh: Trembesi, Mahoni, Flamboyan, dll.') }}"
                    />

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            {{ __('Foto Kondisi Pohon') }} <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="file"
                            wire:model="foto_pohon"
                            accept="image/jpeg,image/png,image/webp,image/avif"
                            class="block w-full text-xs text-slate-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 dark:file:bg-slate-800 dark:file:text-slate-200 cursor-pointer"
                        />
                        @error('foto_pohon') <p class="mt-1 text-xs text-rose-500 font-semibold">{{ $message }}</p> @enderror
                        <div wire:loading wire:target="foto_pohon" class="mt-1 text-xs text-emerald-600 font-semibold">Mengunggah foto...</div>
                    </div>
                </div>

                {{-- Preview Foto Pohon --}}
                @if ($foto_pohon)
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center gap-3">
                        <img src="{{ $foto_pohon->temporaryUrl() }}" class="size-16 rounded-lg object-cover" />
                        <div class="text-xs">
                            <p class="font-bold text-slate-800 dark:text-slate-200">{{ __('Foto Siap Dikirim') }}</p>
                            <p class="text-slate-400">{{ $foto_pohon->getClientOriginalName() }}</p>
                        </div>
                    </div>
                @endif

                {{-- Alasan Pengajuan --}}
                <div>
                    <x-public.textarea
                        wire:model="alasan_pengajuan"
                        name="alasan_pengajuan"
                        rows="3"
                        label="{{ __('Alasan Pengajuan Tindakan') }}"
                        placeholder="{{ __('Jelaskan alasan permohonan. Contoh: Dahan pohon sudah menyentuh kabel listrik bertegangan tinggi dan bagian pangkal batang mulai miring ke arah badan jalan raya.') }}"
                        required
                    />
                </div>

                {{-- Keterangan Tambahan --}}
                <div>
                    <x-public.textarea
                        wire:model="keterangan_tambahan"
                        name="keterangan_tambahan"
                        rows="2"
                        label="{{ __('Keterangan Tambahan (Opsional)') }}"
                        placeholder="{{ __('Informasi tambahan untuk mempermudah petugas saat melakukan survei lokasi...') }}"
                    />
                </div>

                {{-- Ceklis Konfirmasi Fasilitas Umum / Area Publik (MINIMAL, CLEAN, NO BLACK BORDER) --}}
                <div class="p-4 rounded-xl bg-slate-50/80 dark:bg-slate-900/40 border border-slate-200/60 dark:border-slate-800/80">
                    <label class="flex items-start gap-3 cursor-pointer select-none">
                        <input
                            type="checkbox"
                            wire:model="konfirmasi_area_publik"
                            class="mt-1 rounded text-emerald-600 focus:ring-emerald-500 size-4.5 cursor-pointer"
                            required
                        />
                        <span class="text-xs sm:text-[13px] text-slate-700 dark:text-slate-300 leading-relaxed">
                            <strong class="font-bold text-slate-900 dark:text-white">{{ __('Pernyataan Kebenaran Lokasi:') }}</strong>
                            {{ __('Saya menyatakan dengan sungguh-sungguh bahwa pohon yang dimohonkan') }}
                            <strong class="font-bold text-slate-900 dark:text-white">{{ __('berada di fasilitas umum / area publik Kota Palu') }}</strong>
                            {{ __('dan') }} <span class="font-semibold text-rose-600 dark:text-rose-400">{{ __('BUKAN di pekarangan, halaman rumah, maupun lahan milik pribadi') }}</span>.
                        </span>
                    </label>
                    @error('konfirmasi_area_publik')
                        <p class="mt-2 text-xs text-rose-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Recaptcha & Error --}}
                @error('form')
                    <div class="dlh-limit-alert" role="alert">
                        <x-icons.ui name="alert" />
                        <span>{{ $message }}</span>
                    </div>
                @enderror

                <x-google-recaptcha />

                <div class="pt-2">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="w-full py-3.5 px-6 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm sm:text-base transition shadow-sm flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <x-icons.ui name="arrow-right" class="size-4.5" />
                        <span wire:loading.remove wire:target="submit">{{ __('Kirim Permohonan Pohon') }}</span>
                        <span wire:loading wire:target="submit">{{ __('Memproses Permohonan...') }}</span>
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
