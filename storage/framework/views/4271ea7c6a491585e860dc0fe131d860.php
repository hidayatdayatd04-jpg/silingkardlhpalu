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
use App\Traits\HandlesPengaduanPhotoUpload;
use Livewire\Component;
use Livewire\WithFileUploads;
?>

<div
    class="fi-form-card bg-white dark:bg-slate-950 rounded-2xl p-6 md:p-8 shadow-[0_1px_3px_rgba(13,43,29,0.06),0_12px_32px_-12px_rgba(13,43,29,0.10)] max-w-4xl mx-auto">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($processing): ?>
        <div class="space-y-6 text-center py-8" wire:poll.3s="checkPhotoStatus">
            <div
                class="h-16 w-16 bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 rounded-full flex items-center justify-center mx-auto text-3xl font-bold animate-spin">
                ↻
            </div>
            <div class="space-y-2">
                <h3 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100"><?php echo e(__('Sedang Memproses Foto')); ?></h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto"><?php echo e(__('Pengaduan Anda telah terkirim. Foto bukti sedang dioptimalkan dan diunggah ke penyimpanan cloud (maksimal beberapa menit).')); ?></p>
            </div>
            <div
                class="p-4 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg max-w-xs mx-auto">
                <span class="block text-[10px] text-brand-600 dark:text-brand-400 font-extrabold tracking-widest uppercase"><?php echo e(__('Nomor Tiket Anda')); ?></span>
                <span class="block text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1 select-all tracking-wider"><?php echo e($ticket); ?></span>
            </div>
        </div>
    <?php elseif($ticket): ?>
        <div class="space-y-6 text-center py-8">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($photoError): ?>
                <div class="p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-300 text-sm"><?php echo e($photoError); ?></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div
                class="h-16 w-16 bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 rounded-full flex items-center justify-center mx-auto text-3xl font-bold">
                ✓
            </div>
            <div class="space-y-2">
                <h3 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100"><?php echo e(__('Pengaduan Berhasil Terkirim')); ?></h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto"><?php echo e(__('Terima kasih atas pengaduan Anda. Simpan nomor tiket di bawah untuk mengecek status pengaduan.')); ?></p>
            </div>
            <div
                class="p-4 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg max-w-xs mx-auto">
                <span class="block text-[10px] text-brand-600 dark:text-brand-400 font-extrabold tracking-widest uppercase"><?php echo e(__('Nomor Tiket Anda')); ?></span>
                <?php if (isset($component)) { $__componentOriginal0ba85dda8da2317d0ab5473991b5d00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0ba85dda8da2317d0ab5473991b5d00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.copy-ticket','data' => ['ticket' => $ticket,'class' => 'block text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1 select-all tracking-wider']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.copy-ticket'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['ticket' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ticket),'class' => 'block text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1 select-all tracking-wider']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0ba85dda8da2317d0ab5473991b5d00a)): ?>
<?php $attributes = $__attributesOriginal0ba85dda8da2317d0ab5473991b5d00a; ?>
<?php unset($__attributesOriginal0ba85dda8da2317d0ab5473991b5d00a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0ba85dda8da2317d0ab5473991b5d00a)): ?>
<?php $component = $__componentOriginal0ba85dda8da2317d0ab5473991b5d00a; ?>
<?php unset($__componentOriginal0ba85dda8da2317d0ab5473991b5d00a); ?>
<?php endif; ?>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 justify-center pt-4">
                <a href="<?php echo e($this->getCekUrl()); ?>"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors border border-slate-200 hover:bg-slate-100 h-10 py-2 px-4 dark:border-slate-800 dark:hover:bg-slate-800">
                    <?php echo e(__('Cek Status Pengaduan')); ?>

                </a>
                <button wire:click="resetPhotoState"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors bg-slate-900 text-slate-50 hover:bg-slate-900/90 h-10 py-2 px-4 dark:bg-slate-50 dark:text-slate-900">
                    <?php echo e(__('Buat Pengaduan Baru')); ?>

                </button>
            </div>
        </div>
    <?php else: ?>
        <form wire:submit.prevent="submit" class="grid gap-8"
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
                                    window.Livewire.find('<?php echo e($_instance->getId()); ?>').set('alamat', addr);
                                })
                                .catch(function () {})
                                .finally(function () {
                                    window.Livewire.find('<?php echo e($_instance->getId()); ?>').set('latitude', lat);
                                    window.Livewire.find('<?php echo e($_instance->getId()); ?>').set('longitude', lon);
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
                        self.marker.on('dragend', function () { var ll = self.marker.getLngLat(); window.Livewire.find('<?php echo e($_instance->getId()); ?>').set('latitude', ll.lat); window.Livewire.find('<?php echo e($_instance->getId()); ?>').set('longitude', ll.lng); });
                        self.map.on('click', function (e) { self.marker.setLngLat(e.lngLat); window.Livewire.find('<?php echo e($_instance->getId()); ?>').set('latitude', e.lngLat.lat); window.Livewire.find('<?php echo e($_instance->getId()); ?>').set('longitude', e.lngLat.lng); });
                        setTimeout(function () { try { self.map.resize(); } catch (e) {} }, 150);
                    });
                },
                moveMarker(lat, lon) {
                    if (this.marker) this.marker.setLngLat([lon, lat]);
                    if (this.map) this.map.flyTo({ center: [lon, lat], zoom: 15, essential: true });
                }
            }">
            <div class="space-y-6">
                <?php if (isset($component)) { $__componentOriginal81fa922672b9ba50e886bf531e3ad05e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81fa922672b9ba50e886bf531e3ad05e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.input','data' => ['wire:model' => 'nama_pelapor','name' => 'nama_pelapor','label' => ''.e(__('Nama Pelapor')).'','placeholder' => ''.e(__('Nama lengkap pelapor')).'','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'nama_pelapor','name' => 'nama_pelapor','label' => ''.e(__('Nama Pelapor')).'','placeholder' => ''.e(__('Nama lengkap pelapor')).'','required' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal81fa922672b9ba50e886bf531e3ad05e)): ?>
<?php $attributes = $__attributesOriginal81fa922672b9ba50e886bf531e3ad05e; ?>
<?php unset($__attributesOriginal81fa922672b9ba50e886bf531e3ad05e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal81fa922672b9ba50e886bf531e3ad05e)): ?>
<?php $component = $__componentOriginal81fa922672b9ba50e886bf531e3ad05e; ?>
<?php unset($__componentOriginal81fa922672b9ba50e886bf531e3ad05e); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginale01c23deb4ccbaad2431c8f81651ef25 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale01c23deb4ccbaad2431c8f81651ef25 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.select','data' => ['wire:model.live' => 'bidang','id' => 'bidang','name' => 'bidang','label' => ''.e(__('Bidang Pengaduan')).'','options' => $this->getBidangOptions(),'selected' => $bidang,'searchable' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'bidang','id' => 'bidang','name' => 'bidang','label' => ''.e(__('Bidang Pengaduan')).'','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->getBidangOptions()),'selected' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($bidang),'searchable' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale01c23deb4ccbaad2431c8f81651ef25)): ?>
<?php $attributes = $__attributesOriginale01c23deb4ccbaad2431c8f81651ef25; ?>
<?php unset($__attributesOriginale01c23deb4ccbaad2431c8f81651ef25); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale01c23deb4ccbaad2431c8f81651ef25)): ?>
<?php $component = $__componentOriginale01c23deb4ccbaad2431c8f81651ef25; ?>
<?php unset($__componentOriginale01c23deb4ccbaad2431c8f81651ef25); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginale01c23deb4ccbaad2431c8f81651ef25 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale01c23deb4ccbaad2431c8f81651ef25 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.select','data' => ['wire:key' => 'jenis-pengaduan-'.e($bidang).'','wire:model' => 'jenis_pengaduan','id' => 'jenis_pengaduan','name' => 'jenis_pengaduan','label' => ''.e(__('Jenis Pengaduan')).'','options' => $this->jenisOptions(),'selected' => $jenis_pengaduan,'searchable' => true,'placeholder' => ''.e(__('-- Pilih Jenis Pengaduan --')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:key' => 'jenis-pengaduan-'.e($bidang).'','wire:model' => 'jenis_pengaduan','id' => 'jenis_pengaduan','name' => 'jenis_pengaduan','label' => ''.e(__('Jenis Pengaduan')).'','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->jenisOptions()),'selected' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($jenis_pengaduan),'searchable' => true,'placeholder' => ''.e(__('-- Pilih Jenis Pengaduan --')).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale01c23deb4ccbaad2431c8f81651ef25)): ?>
<?php $attributes = $__attributesOriginale01c23deb4ccbaad2431c8f81651ef25; ?>
<?php unset($__attributesOriginale01c23deb4ccbaad2431c8f81651ef25); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale01c23deb4ccbaad2431c8f81651ef25)): ?>
<?php $component = $__componentOriginale01c23deb4ccbaad2431c8f81651ef25; ?>
<?php unset($__componentOriginale01c23deb4ccbaad2431c8f81651ef25); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal81fa922672b9ba50e886bf531e3ad05e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81fa922672b9ba50e886bf531e3ad05e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.input','data' => ['wire:model' => 'nomor_hp','name' => 'nomor_hp','type' => 'tel','maxlength' => '15','label' => ''.e(__('Nomor Telepon')).'','placeholder' => ''.e(__('Contoh: 08123456789')).'','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'nomor_hp','name' => 'nomor_hp','type' => 'tel','maxlength' => '15','label' => ''.e(__('Nomor Telepon')).'','placeholder' => ''.e(__('Contoh: 08123456789')).'','required' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal81fa922672b9ba50e886bf531e3ad05e)): ?>
<?php $attributes = $__attributesOriginal81fa922672b9ba50e886bf531e3ad05e; ?>
<?php unset($__attributesOriginal81fa922672b9ba50e886bf531e3ad05e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal81fa922672b9ba50e886bf531e3ad05e)): ?>
<?php $component = $__componentOriginal81fa922672b9ba50e886bf531e3ad05e; ?>
<?php unset($__componentOriginal81fa922672b9ba50e886bf531e3ad05e); ?>
<?php endif; ?>

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
                        <span><?php echo e(__('Lokasi terdeteksi — peta dan alamat terisi otomatis.')); ?></span>
                    </p>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bidang === 'tata-penataan'): ?>
                    <?php if (isset($component)) { $__componentOriginal81fa922672b9ba50e886bf531e3ad05e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81fa922672b9ba50e886bf531e3ad05e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.input','data' => ['wire:model' => 'nama_terlapor','name' => 'nama_terlapor','label' => ''.e(__('Nama Terlapor')).'','placeholder' => ''.e(__('Nama individu yang dilaporkan')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'nama_terlapor','name' => 'nama_terlapor','label' => ''.e(__('Nama Terlapor')).'','placeholder' => ''.e(__('Nama individu yang dilaporkan')).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal81fa922672b9ba50e886bf531e3ad05e)): ?>
<?php $attributes = $__attributesOriginal81fa922672b9ba50e886bf531e3ad05e; ?>
<?php unset($__attributesOriginal81fa922672b9ba50e886bf531e3ad05e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal81fa922672b9ba50e886bf531e3ad05e)): ?>
<?php $component = $__componentOriginal81fa922672b9ba50e886bf531e3ad05e; ?>
<?php unset($__componentOriginal81fa922672b9ba50e886bf531e3ad05e); ?>
<?php endif; ?>

                    <?php if (isset($component)) { $__componentOriginal81fa922672b9ba50e886bf531e3ad05e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81fa922672b9ba50e886bf531e3ad05e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.input','data' => ['wire:model' => 'nama_perusahaan_terlapor','name' => 'nama_perusahaan_terlapor','label' => ''.e(__('Nama Perusahaan Terlapor')).'','placeholder' => ''.e(__('Nama perusahaan/industri terlapor')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'nama_perusahaan_terlapor','name' => 'nama_perusahaan_terlapor','label' => ''.e(__('Nama Perusahaan Terlapor')).'','placeholder' => ''.e(__('Nama perusahaan/industri terlapor')).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal81fa922672b9ba50e886bf531e3ad05e)): ?>
<?php $attributes = $__attributesOriginal81fa922672b9ba50e886bf531e3ad05e; ?>
<?php unset($__attributesOriginal81fa922672b9ba50e886bf531e3ad05e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal81fa922672b9ba50e886bf531e3ad05e)): ?>
<?php $component = $__componentOriginal81fa922672b9ba50e886bf531e3ad05e; ?>
<?php unset($__componentOriginal81fa922672b9ba50e886bf531e3ad05e); ?>
<?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if (isset($component)) { $__componentOriginalfcda6e771345efc6ade18f29253a2b5b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfcda6e771345efc6ade18f29253a2b5b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.textarea','data' => ['wire:model' => 'alamat','name' => 'alamat','label' => ''.e(__('Alamat Lokasi Kejadian')).'','placeholder' => ''.e(__('Alamat lengkap lokasi kejadian')).'','rows' => '2','maxlength' => '150','hint' => ''.e(__('Sertakan patokan terdekat')).'','required' => true,'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.4 8.5c0 5.5-8.4 11.5-8.4 11.5S3.6 14 3.6 8.5a8.4 8.4 0 1 1 16.8 0Z"/><circle cx="12" cy="8.5" r="2.6"/></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'alamat','name' => 'alamat','label' => ''.e(__('Alamat Lokasi Kejadian')).'','placeholder' => ''.e(__('Alamat lengkap lokasi kejadian')).'','rows' => '2','maxlength' => '150','hint' => ''.e(__('Sertakan patokan terdekat')).'','required' => true,'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.4 8.5c0 5.5-8.4 11.5-8.4 11.5S3.6 14 3.6 8.5a8.4 8.4 0 1 1 16.8 0Z"/><circle cx="12" cy="8.5" r="2.6"/></svg>']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfcda6e771345efc6ade18f29253a2b5b)): ?>
<?php $attributes = $__attributesOriginalfcda6e771345efc6ade18f29253a2b5b; ?>
<?php unset($__attributesOriginalfcda6e771345efc6ade18f29253a2b5b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfcda6e771345efc6ade18f29253a2b5b)): ?>
<?php $component = $__componentOriginalfcda6e771345efc6ade18f29253a2b5b; ?>
<?php unset($__componentOriginalfcda6e771345efc6ade18f29253a2b5b); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginalfcda6e771345efc6ade18f29253a2b5b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfcda6e771345efc6ade18f29253a2b5b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.textarea','data' => ['wire:model' => 'deskripsi','name' => 'deskripsi','label' => ''.e(__('Deskripsi Pengaduan')).'','placeholder' => ''.e(__('Jelaskan detail kejadian secara lengkap: apa yang terjadi, kapan, dan dampaknya...')).'','rows' => '4','maxlength' => '5000','hint' => ''.e(__('Minimal 20 karakter')).'','required' => true,'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16M4 12h16M4 19h10"/></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'deskripsi','name' => 'deskripsi','label' => ''.e(__('Deskripsi Pengaduan')).'','placeholder' => ''.e(__('Jelaskan detail kejadian secara lengkap: apa yang terjadi, kapan, dan dampaknya...')).'','rows' => '4','maxlength' => '5000','hint' => ''.e(__('Minimal 20 karakter')).'','required' => true,'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16M4 12h16M4 19h10"/></svg>']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfcda6e771345efc6ade18f29253a2b5b)): ?>
<?php $attributes = $__attributesOriginalfcda6e771345efc6ade18f29253a2b5b; ?>
<?php unset($__attributesOriginalfcda6e771345efc6ade18f29253a2b5b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfcda6e771345efc6ade18f29253a2b5b)): ?>
<?php $component = $__componentOriginalfcda6e771345efc6ade18f29253a2b5b; ?>
<?php unset($__componentOriginalfcda6e771345efc6ade18f29253a2b5b); ?>
<?php endif; ?>

                <div class="fi-field">
                    <label class="fi-label"><?php echo e(__('Foto Bukti')); ?> <span class="fi-required">*</span> <span style="font-weight:400;color:#5b6b63;font-size:12.5px;">(min 1, max 5, JPG/PNG/WebP maksimal 5MB)</span></label>
                    <div class="fi-file-drop">
                        <button type="button" class="fi-file-btn" x-on:click="$refs.fileInput.click()"><?php echo e(__('Choose Files')); ?></button>
                        <span class="fi-file-status"><?php echo e(__('No file chosen')); ?></span>
                        <input wire:model="photos" x-ref="fileInput" type="file" multiple accept="image/jpeg,image/png,image/webp" required
                            style="position:absolute;width:1px;height:1px;opacity:0;overflow:hidden;"
                            x-on:change="
                                let files = $el.files;
                                if(files.length > 5){ alert('Maksimal 5 foto yang diizinkan!'); $el.value=''; return; }
                                for(let f of files){
                                    if(f.size > 5*1024*1024){ alert('Ukuran foto ' + f.name + ' melebihi 5MB!'); $el.value=''; return; }
                                    if(!['image/jpeg','image/png','image/webp'].includes(f.type)){ alert('File ' + f.name + ' bukan JPG/PNG/WebP!'); $el.value=''; return; }
                                }
                            "
                        />
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['photos'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="fi-error"><svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['photos.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="fi-error"><svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($photos): ?>
                        <div class="grid grid-cols-3 gap-3 pt-3">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $photos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $photo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div class="relative aspect-square rounded-xl" style="overflow:visible;">
                                    <img src="<?php echo e($photo->temporaryUrl()); ?>" alt="<?php echo e(__('Pratinjau foto bukti')); ?>" class="w-full h-full object-cover rounded-xl" />
                                    <button type="button"
                                        wire:click="removePhoto(<?php echo e($index); ?>)"
                                        style="position:absolute;top:-6px;right:-6px;width:24px;height:24px;border-radius:50%;background:#ef4444;color:#fff;display:flex;align-items:center;justify-content:center;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.3);cursor:pointer;z-index:10;"
                                        title="<?php echo e(__('Hapus foto')); ?>">
                                        <svg style="width:12px;height:12px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <button type="submit" class="fi-submit-btn">
                    <?php echo e(__('Kirim Pengaduan')); ?>

                </button>
            </div>

            <div x-show="located" x-cloak class="space-y-6 flex flex-col">
                <label class="fi-label">
                    <span class="fi-icon-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.4 8.5c0 5.5-8.4 11.5-8.4 11.5S3.6 14 3.6 8.5a8.4 8.4 0 1 1 16.8 0Z"/><circle cx="12" cy="8.5" r="2.6"/></svg></span>
                    <?php echo e(__('Peta Lokasi Kejadian')); ?>

                </label>

                <div wire:ignore x-ref="mapEl"
                    class="w-full flex-1 min-h-[300px] rounded-2xl overflow-hidden relative z-0">
                </div>

                <div class="flex justify-between text-xs text-[#5b6b63] dark:text-slate-400 font-medium tabular-nums">
                    <span>Lat: <?php echo e(number_format($latitude, 6)); ?></span>
                    <span>Lng: <?php echo e(number_format($longitude, 6)); ?></span>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['latitude'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="fi-error"><svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['longitude'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="fi-error"><svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </form>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

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
            font-family: 'Outfit', ui-sans-serif, system-ui, sans-serif;
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
</div><?php /**PATH C:\xampp\htdocs\DLH - PALU\storage\framework\views/livewire/views/99b7670e.blade.php ENDPATH**/ ?>