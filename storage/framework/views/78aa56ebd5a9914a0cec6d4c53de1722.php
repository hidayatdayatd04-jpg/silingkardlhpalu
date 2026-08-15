<?php
use App\Enums\Bidang;
use App\Enums\JenisPengaduanPengendalian;
use App\Http\Requests\StorePengaduanPengendalianRequest;
use App\Models\Laporan;
use App\Models\LaporanFoto;
use App\Traits\HandlesPengaduanPhotoUpload;
use Livewire\Component;
use Livewire\WithFileUploads;
?>

<div
    class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 md:p-8 shadow-sm max-w-4xl mx-auto">
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
                <a href="<?php echo e(url('/cek-pengaduan-pengendalian')); ?>"
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
        <form wire:submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="space-y-6">
                <?php if (isset($component)) { $__componentOriginal81fa922672b9ba50e886bf531e3ad05e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81fa922672b9ba50e886bf531e3ad05e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.input','data' => ['wire:model' => 'nama_pelapor','name' => 'nama_pelapor','label' => ''.e(__('Nama Pelapor')).'','placeholder' => ''.e(__('Nama lengkap pelapor')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'nama_pelapor','name' => 'nama_pelapor','label' => ''.e(__('Nama Pelapor')).'','placeholder' => ''.e(__('Nama lengkap pelapor')).'']); ?>
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

                <div class="space-y-2.5">
                    <label for="jenis_pengaduan" class="block text-sm font-semibold text-slate-700 dark:text-slate-300"><?php echo e(__('Jenis Pengaduan')); ?></label>
                    <?php if (isset($component)) { $__componentOriginalcaa826401539fc57a784dadbb5b3020d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcaa826401539fc57a784dadbb5b3020d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.select','data' => ['wire:model' => 'jenis_pengaduan','id' => 'jenis_pengaduan','name' => 'jenis_pengaduan','options' => $this->jenisOptions(),'searchable' => false,'placeholder' => ''.e(__('-- Pilih Jenis Pengaduan --')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'jenis_pengaduan','id' => 'jenis_pengaduan','name' => 'jenis_pengaduan','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->jenisOptions()),'searchable' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'placeholder' => ''.e(__('-- Pilih Jenis Pengaduan --')).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcaa826401539fc57a784dadbb5b3020d)): ?>
<?php $attributes = $__attributesOriginalcaa826401539fc57a784dadbb5b3020d; ?>
<?php unset($__attributesOriginalcaa826401539fc57a784dadbb5b3020d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcaa826401539fc57a784dadbb5b3020d)): ?>
<?php $component = $__componentOriginalcaa826401539fc57a784dadbb5b3020d; ?>
<?php unset($__componentOriginalcaa826401539fc57a784dadbb5b3020d); ?>
<?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['jenis_pengaduan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-[0.8rem] font-medium text-danger-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                </div>

                <?php if (isset($component)) { $__componentOriginal81fa922672b9ba50e886bf531e3ad05e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81fa922672b9ba50e886bf531e3ad05e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.input','data' => ['wire:model' => 'nomor_hp','name' => 'nomor_hp','type' => 'tel','label' => ''.e(__('Nomor Telepon')).'','placeholder' => ''.e(__('Contoh: 08123456789')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'nomor_hp','name' => 'nomor_hp','type' => 'tel','label' => ''.e(__('Nomor Telepon')).'','placeholder' => ''.e(__('Contoh: 08123456789')).'']); ?>
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

                <?php if (isset($component)) { $__componentOriginalfcda6e771345efc6ade18f29253a2b5b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfcda6e771345efc6ade18f29253a2b5b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.textarea','data' => ['wire:model' => 'alamat','name' => 'alamat','label' => ''.e(__('Alamat Lokasi Kejadian')).'','placeholder' => ''.e(__('Alamat lengkap lokasi kejadian')).'','rows' => '2','maxlength' => '150','hint' => ''.e(__('Sertakan patokan terdekat')).'','icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.4 8.5c0 5.5-8.4 11.5-8.4 11.5S3.6 14 3.6 8.5a8.4 8.4 0 1 1 16.8 0Z"/><circle cx="12" cy="8.5" r="2.6"/></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'alamat','name' => 'alamat','label' => ''.e(__('Alamat Lokasi Kejadian')).'','placeholder' => ''.e(__('Alamat lengkap lokasi kejadian')).'','rows' => '2','maxlength' => '150','hint' => ''.e(__('Sertakan patokan terdekat')).'','icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.4 8.5c0 5.5-8.4 11.5-8.4 11.5S3.6 14 3.6 8.5a8.4 8.4 0 1 1 16.8 0Z"/><circle cx="12" cy="8.5" r="2.6"/></svg>']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.textarea','data' => ['wire:model' => 'deskripsi','name' => 'deskripsi','label' => ''.e(__('Deskripsi Pengaduan')).'','placeholder' => ''.e(__('Jelaskan detail kejadian secara lengkap: apa yang terjadi, kapan, dan dampaknya...')).'','rows' => '4','maxlength' => '5000','hint' => ''.e(__('Minimal 20 karakter')).'','icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16M4 12h16M4 19h10"/></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'deskripsi','name' => 'deskripsi','label' => ''.e(__('Deskripsi Pengaduan')).'','placeholder' => ''.e(__('Jelaskan detail kejadian secara lengkap: apa yang terjadi, kapan, dan dampaknya...')).'','rows' => '4','maxlength' => '5000','hint' => ''.e(__('Minimal 20 karakter')).'','icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16M4 12h16M4 19h10"/></svg>']); ?>
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

                <div class="space-y-2.5">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300"><?php echo e(__('Foto Bukti (min 1, max 5, JPG/PNG/WebP maksimal 5MB)')); ?></label>
                    <input wire:model="photos" type="file" multiple accept="image/jpeg,image/png,image/webp"
                        class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-1.5 text-sm dark:border-slate-800" />
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['photos'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-[0.8rem] font-medium text-danger-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['photos.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-[0.8rem] font-medium text-danger-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($photos): ?>
                        <div class="grid grid-cols-3 gap-3 pt-3">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $photos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $photo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div class="relative aspect-square rounded-md overflow-hidden border border-slate-200 dark:border-slate-800">
                                    <img src="<?php echo e($photo->temporaryUrl()); ?>" alt="<?php echo e(__('Pratinjau foto bukti')); ?>" class="w-full h-full object-cover" />
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="space-y-6 flex flex-col justify-between">
                <div class="space-y-2.5 flex-1 flex flex-col">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300"><?php echo e(__('Tentukan Lokasi (Klik Peta)')); ?></label>
                    <div wire:ignore
                        class="w-full flex-1 min-h-[300px] border border-slate-200 dark:border-slate-800 rounded-md overflow-hidden relative z-0"
                        x-data="{
                            map: null, marker: null,
                            initMap() {
                                var self = this;
                                window.ensureMaplibreLoaded(function() {
                                    self.map = new maplibregl.Map({ container: self.$el, style: 'https://basemaps.cartocdn.com/gl/voyager-gl-style/style.json', center: [<?php echo \Illuminate\Support\Js::from($longitude)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($latitude)->toHtml() ?>], zoom: 13, attributionControl: false });
                                    self.map.addControl(new DlhZoomControl(), 'top-left');
if (window.DlhWeatherControl) map.addControl(new DlhWeatherControl(), 'top-right');
                                    if (window.DlhBasemapSwitcher) { var bs = new DlhBasemapSwitcher(); self.map.on('load', function() { bs.onAdd(self.map); }); }
                                    self.marker = new maplibregl.Marker({ draggable: true, anchor: 'center' }).setLngLat([<?php echo \Illuminate\Support\Js::from($longitude)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($latitude)->toHtml() ?>]).addTo(self.map);
                                    self.marker.on('dragend', function() { var ll = self.marker.getLngLat(); window.Livewire.find('<?php echo e($_instance->getId()); ?>').set('latitude', ll.lat); window.Livewire.find('<?php echo e($_instance->getId()); ?>').set('longitude', ll.lng); });
                                    self.map.on('click', function(e) { self.marker.setLngLat(e.lngLat); window.Livewire.find('<?php echo e($_instance->getId()); ?>').set('latitude', e.lngLat.lat); window.Livewire.find('<?php echo e($_instance->getId()); ?>').set('longitude', e.lngLat.lng); });
                                    dlhAddLocBtn(self.map, function(lat, lng) { self.marker.setLngLat([lng, lat]); window.Livewire.find('<?php echo e($_instance->getId()); ?>').set('latitude', lat); window.Livewire.find('<?php echo e($_instance->getId()); ?>').set('longitude', lng); });
                                });
                            }
                        }" x-init="initMap()">
                    </div>
                    <div class="flex justify-between text-[0.8rem] text-slate-500 mt-2">
                        <span>Lat: <?php echo e(number_format($latitude, 6)); ?></span>
                        <span>Lng: <?php echo e(number_format($longitude, 6)); ?></span>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['latitude'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-[0.8rem] font-medium text-red-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['longitude'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-[0.8rem] font-medium text-red-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <button type="submit"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-slate-900 text-slate-50 hover:bg-slate-900/90 h-10 py-2 px-4 w-full dark:bg-slate-50 dark:text-slate-900 shadow-sm">
                    <?php echo e(__('Kirim Pengaduan')); ?>

                </button>
            </div>
        </form>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\xampp\htdocs\DLH - PALU\storage\framework\views/livewire/views/2f3c4984.blade.php ENDPATH**/ ?>