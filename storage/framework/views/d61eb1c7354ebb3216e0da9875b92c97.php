<?php
use App\Enums\StatusPengaduanTataPenataan;
use App\Models\PengaduanTataPenataan;
use Livewire\Component;
?>

<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-4xl mx-auto">
        <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm space-y-4">
            <h3 class="font-semibold text-slate-900 dark:text-slate-100"><?php echo e(__('Cek via Nomor Tiket')); ?></h3>
            <input wire:model="searchTicket" type="text" placeholder="TTP-XXXX-XXXX"
                class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm font-mono uppercase dark:border-slate-800" />
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['searchTicket'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-[0.8rem] font-medium text-red-500 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <button wire:click="searchByTicket"
                class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-slate-900 text-slate-50 h-10 px-4 w-full dark:bg-slate-50 dark:text-slate-900">
                <?php echo e(__('Cari Tiket')); ?>

            </button>
        </div>

        <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm space-y-4">
            <h3 class="font-semibold text-slate-900 dark:text-slate-100"><?php echo e(__('Cek via Nomor HP')); ?></h3>
            <input wire:model="searchPhone" type="tel" placeholder="08123456789"
                class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm dark:border-slate-800" />
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['searchPhone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-[0.8rem] font-medium text-red-500 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <button wire:click="searchByPhone"
                class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-slate-900 text-slate-50 h-10 px-4 w-full dark:bg-slate-50 dark:text-slate-900">
                <?php echo e(__('Cari via HP')); ?>

            </button>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pengaduan): ?>
        <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 md:p-8 shadow-sm space-y-8 max-w-4xl mx-auto">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-6">
                <div>
                    <span class="text-xs text-slate-500 font-medium tracking-wider uppercase"><?php echo e(__('Nomor Tiket')); ?></span>
                    <?php if (isset($component)) { $__componentOriginal0ba85dda8da2317d0ab5473991b5d00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0ba85dda8da2317d0ab5473991b5d00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.copy-ticket','data' => ['ticket' => $pengaduan->nomor_tiket,'class' => 'text-2xl font-bold font-mono mt-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.copy-ticket'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['ticket' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pengaduan->nomor_tiket),'class' => 'text-2xl font-bold font-mono mt-1']); ?>
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
                <?php
                    $statusColor = $pengaduan->status?->color() ?? 'gray';
                    $badgeMap = [
                        'gray' => 'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-100',
                        'warning' => 'bg-amber-100 text-amber-900 dark:bg-amber-900/30 dark:text-amber-400',
                        'success' => 'bg-emerald-100 text-emerald-900 dark:bg-emerald-900/30 dark:text-emerald-400',
                    ];
                ?>
                <span class="inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold <?php echo e($badgeMap[$statusColor] ?? $badgeMap['gray']); ?>">
                    <?php echo e($pengaduan->status?->label() ?? $pengaduan->status); ?>

                </span>
            </div>

            <?php if (isset($component)) { $__componentOriginal99eaeb965d503948f5881d24e0efc523 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99eaeb965d503948f5881d24e0efc523 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.ticket-feedback','data' => ['ticket' => $pengaduan]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.ticket-feedback'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['ticket' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pengaduan)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal99eaeb965d503948f5881d24e0efc523)): ?>
<?php $attributes = $__attributesOriginal99eaeb965d503948f5881d24e0efc523; ?>
<?php unset($__attributesOriginal99eaeb965d503948f5881d24e0efc523); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal99eaeb965d503948f5881d24e0efc523)): ?>
<?php $component = $__componentOriginal99eaeb965d503948f5881d24e0efc523; ?>
<?php unset($__componentOriginal99eaeb965d503948f5881d24e0efc523); ?>
<?php endif; ?>

            <?php if (isset($component)) { $__componentOriginal1a3ac395ac0a4ff13489a9dd135cb158 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1a3ac395ac0a4ff13489a9dd135cb158 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.status-timeline','data' => ['timeline' => \App\Services\TicketTimelineService::forTicket($pengaduan)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.status-timeline'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['timeline' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\App\Services\TicketTimelineService::forTicket($pengaduan))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1a3ac395ac0a4ff13489a9dd135cb158)): ?>
<?php $attributes = $__attributesOriginal1a3ac395ac0a4ff13489a9dd135cb158; ?>
<?php unset($__attributesOriginal1a3ac395ac0a4ff13489a9dd135cb158); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1a3ac395ac0a4ff13489a9dd135cb158)): ?>
<?php $component = $__componentOriginal1a3ac395ac0a4ff13489a9dd135cb158; ?>
<?php unset($__componentOriginal1a3ac395ac0a4ff13489a9dd135cb158); ?>
<?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="block text-slate-500 font-medium"><?php echo e(__('Nama Pelapor')); ?></span>
                            <span class="font-semibold"><?php echo e($pengaduan->nama_pelapor); ?></span>
                        </div>
                        <div>
                            <span class="block text-slate-500 font-medium"><?php echo e(__('Jenis Pengaduan')); ?></span>
                            <span class="font-semibold"><?php echo e(\App\Enums\JenisPengaduanTataPenataan::tryFrom($pengaduan->jenis_pengaduan)?->label() ?? $pengaduan->jenis_pengaduan); ?></span>
                        </div>
                        <div>
                            <span class="block text-slate-500 font-medium"><?php echo e(__('Tanggal Lapor')); ?></span>
                            <span class="font-semibold"><?php echo e($pengaduan->created_at->format('d M Y H:i')); ?></span>
                        </div>
                        <div>
                            <span class="block text-slate-500 font-medium"><?php echo e(__('Alamat')); ?></span>
                            <span class="font-semibold"><?php echo e($pengaduan->alamat); ?></span>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pengaduan->nama_terlapor): ?>
                            <div>
                                <span class="block text-slate-500 font-medium"><?php echo e(__('Nama Terlapor')); ?></span>
                                <span class="font-semibold"><?php echo e($pengaduan->nama_terlapor); ?></span>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pengaduan->nama_perusahaan_terlapor): ?>
                            <div>
                                <span class="block text-slate-500 font-medium"><?php echo e(__('Perusahaan Terlapor')); ?></span>
                                <span class="font-semibold"><?php echo e($pengaduan->nama_perusahaan_terlapor); ?></span>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div>
                        <span class="block text-sm text-slate-500 font-medium"><?php echo e(__('Deskripsi')); ?></span>
                        <p class="text-sm text-slate-700 dark:text-slate-300 mt-1"><?php echo e($pengaduan->deskripsi); ?></p>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pengaduan->catatan_admin): ?>
                        <div class="p-4 bg-brand-50 dark:bg-brand-900/20 border border-brand-200 dark:border-brand-800 rounded-lg">
                            <span class="block text-sm font-semibold text-brand-800 dark:text-brand-400"><?php echo e(__('Catatan Admin')); ?></span>
                            <p class="text-sm mt-1"><?php echo e($pengaduan->catatan_admin); ?></p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pengaduan->fotos->isNotEmpty()): ?>
                        <div class="grid grid-cols-3 gap-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $pengaduan->fotos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $foto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div class="aspect-square rounded-md overflow-hidden border border-slate-200 dark:border-slate-800">
                                    <img src="<?php echo e($foto->fullUrl()); ?>" alt="<?php echo e(__('Foto bukti pengaduan')); ?>" class="w-full h-full object-cover" />
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="space-y-4">
                    <h3 class="text-lg font-semibold"><?php echo e(__('Lokasi Peta')); ?></h3>
                    <div wire:ignore <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'map-'.e($pengaduan->nomor_tiket).''; ?>wire:key="map-<?php echo e($pengaduan->nomor_tiket); ?>"
                         x-data x-init="setTimeout(function(){dlhSimpleMap('cek-map-<?php echo e($pengaduan->nomor_tiket); ?>',{lat:<?php echo \Illuminate\Support\Js::from($pengaduan->latitude)->toHtml() ?>,lng:<?php echo \Illuminate\Support\Js::from($pengaduan->longitude)->toHtml() ?>,zoom:14,popupText:'<?php echo e(__('Lokasi Pengaduan')); ?>'})},100)">
                        <div id="cek-map-<?php echo e($pengaduan->nomor_tiket); ?>" class="w-full h-[300px] border border-slate-200 dark:border-slate-800 rounded-md z-0"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\xampp\htdocs\DLH - PALU\storage\framework\views/livewire/views/007f7bc9.blade.php ENDPATH**/ ?>