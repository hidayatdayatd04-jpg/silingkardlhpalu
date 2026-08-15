<?php
use Livewire\Component;
use App\Models\Laporan;
use App\Models\PengaduanTataPenataan;
?>

<div class="space-y-6 lc-wrap">
    
    <div class="lc-search-card max-w-4xl mx-auto">
        <div class="lc-search-head">
            <span class="lc-search-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            </span>
            <div class="flex-1">
                <h3 class="lc-search-title"><?php echo e(__('Lacak Laporan')); ?></h3>
                <p class="lc-search-desc"><?php echo e(__('Masukkan nomor tiket untuk memantau status verifikasi dan tindak lanjut petugas.')); ?></p>
            </div>
        </div>
        <form wire:submit.prevent="search" class="flex flex-col md:flex-row items-stretch md:items-end gap-3">
            <div class="flex-1">
                <?php if (isset($component)) { $__componentOriginal81fa922672b9ba50e886bf531e3ad05e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81fa922672b9ba50e886bf531e3ad05e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.input','data' => ['wire:model' => 'searchTicket','name' => 'searchTicket','placeholder' => ''.e(__('Contoh: TK-XXXXXX atau TTP-XXXX-XXXX')).'','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'searchTicket','name' => 'searchTicket','placeholder' => ''.e(__('Contoh: TK-XXXXXX atau TTP-XXXX-XXXX')).'','required' => true]); ?>
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
            </div>
            <button type="submit" class="lc-search-btn">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <?php echo e(__('Cari Laporan')); ?>

            </button>
        </form>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($laporan): ?>
        <div class="lc-result-card max-w-4xl mx-auto">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-6">
                <div class="flex items-center gap-3">
                    <span class="lc-result-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                    </span>
                    <div>
                        <span class="block text-[10px] text-slate-500 dark:text-slate-400 font-extrabold tracking-widest uppercase"><?php echo e(__('Nomor Tiket')); ?></span>
                        <?php if (isset($component)) { $__componentOriginal0ba85dda8da2317d0ab5473991b5d00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0ba85dda8da2317d0ab5473991b5d00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.copy-ticket','data' => ['ticket' => $laporan->nomor_tiket,'class' => 'text-2xl font-bold font-mono text-slate-900 dark:text-slate-100']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.copy-ticket'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['ticket' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($laporan->nomor_tiket),'class' => 'text-2xl font-bold font-mono text-slate-900 dark:text-slate-100']); ?>
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
                </div>
                <?php
                    $badgeColors = [
                        'Belum Ditinjau' => 'lc-status--pending',
                        'Ditinjau' => 'lc-status--info',
                        'Selesai' => 'lc-status--done',
                        'Ditolak' => 'lc-status--rejected',
                        'Belum Ditindaklanjuti' => 'lc-status--pending',
                        'Ditindaklanjuti' => 'lc-status--info',
                    ];
                    $isDone = in_array($laporan->status, ['Selesai']);
                    $isRejected = $laporan->status === 'Ditolak';
                ?>
                <span class="lc-status-badge <?php echo e($badgeColors[$laporan->status_label] ?? 'lc-status--pending'); ?>">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isDone): ?>
                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                    <?php elseif($isRejected): ?>
                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                    <?php else: ?>
                        <span class="lc-status-dot"></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php echo e($laporan->status_label); ?>

                </span>
            </div>

            
            <div class="lc-stepper-wrap">
                <?php
                    $statusStr = $laporan->status;
                    $steps = [__('Menunggu'), __('Selesai')];
                    $statusToStep = [
                        'Belum Ditinjau' => 0,
                        'Ditinjau' => 0,
                        'Selesai' => 1,
                        'Ditolak' => 1,
                        'Belum Ditindaklanjuti' => 0,
                        'Ditindaklanjuti' => 1,
                    ];
                    $currentIdx = $statusToStep[$statusStr] ?? 0;
                    $isRejected = $statusStr === 'Ditolak';
                    if ($isRejected) { $steps = [__('Menunggu'), __('Ditolak')]; }
                ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="lc-step">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($idx < count($steps) - 1): ?>
                            <div class="lc-step-line lc-step-line--bg"></div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($idx < $currentIdx): ?>
                                <div class="lc-step-line <?php echo e($isRejected ? 'lc-step-line--rejected' : 'lc-step-line--done'); ?>"></div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'lc-step-dot',
                            'lc-step-dot--done' => $idx <= $currentIdx && !$isRejected,
                            'lc-step-dot--rejected' => $idx <= $currentIdx && $isRejected,
                            'lc-step-dot--pending' => $idx > $currentIdx,
                        ]); ?>"><?php echo e($idx + 1); ?></div>
                        <span class="lc-step-label"><?php echo e($step); ?></span>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>

            <?php if (isset($component)) { $__componentOriginal1a3ac395ac0a4ff13489a9dd135cb158 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1a3ac395ac0a4ff13489a9dd135cb158 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.status-timeline','data' => ['timeline' => \App\Services\TicketTimelineService::forTicket($laporan)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.status-timeline'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['timeline' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\App\Services\TicketTimelineService::forTicket($laporan))]); ?>
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

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 border-t border-slate-100 dark:border-slate-800 pt-8">
                <div class="space-y-6">
                    <div class="space-y-4">
                        <h3 class="lc-section-title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M9 13h6M9 17h3"/></svg>
                            <?php echo e(__('Rincian Aduan')); ?>

                        </h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="lc-info-tile">
                                <span class="lc-info-label"><?php echo e(__('Kategori')); ?></span>
                                <span class="lc-info-value"><?php echo e($laporan->kategori); ?></span>
                            </div>
                            <div class="lc-info-tile">
                                <span class="lc-info-label"><?php echo e(__('Tanggal Masuk')); ?></span>
                                <span class="lc-info-value"><?php echo e($laporan->created_at->format('d M Y H:i')); ?></span>
                            </div>
                        </div>
                        <div class="lc-desc-box">
                            <span class="lc-info-label"><?php echo e(__('Deskripsi')); ?></span>
                            <p class="lc-desc-text"><?php echo e($laporan->deskripsi); ?></p>
                        </div>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($statusStr === 'Ditolak'): ?>
                        <div class="lc-reject-box">
                            <span class="lc-reject-label">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                <?php echo e(__('Alasan Penolakan')); ?>

                            </span>
                            <p class="text-sm mt-1.5"><?php echo e($laporan->alasan_penolakan ?? __('Tidak ada alasan penolakan yang ditulis.')); ?></p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($laporan->fotos->isNotEmpty()): ?>
                        <div class="space-y-2">
                            <span class="lc-info-label"><?php echo e(__('Foto Lampiran Pengaduan')); ?></span>
                            <div class="grid grid-cols-3 gap-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $laporan->fotos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $foto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <div class="lc-photo-thumb">
                                        <img src="<?php echo e($foto->fullUrl()); ?>" alt="<?php echo e(__('Foto lampiran pengaduan')); ?>" class="w-full h-full object-cover" />
                                    </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="space-y-4">
                    <h3 class="lc-section-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.4 8.5c0 5.5-8.4 11.5-8.4 11.5S3.6 14 3.6 8.5a8.4 8.4 0 1 1 16.8 0Z"/><circle cx="12" cy="8.5" r="2.6"/></svg>
                        <?php echo e(__('Lokasi Peta')); ?>

                    </h3>
                    <div wire:ignore <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'map-'.e($laporan->nomor_tiket).''; ?>wire:key="map-<?php echo e($laporan->nomor_tiket); ?>"
                         x-data x-init="setTimeout(function(){dlhSimpleMap('cek-map-laporan-<?php echo e($laporan->nomor_tiket); ?>',{lat:<?php echo \Illuminate\Support\Js::from($laporan->latitude)->toHtml() ?>,lng:<?php echo \Illuminate\Support\Js::from($laporan->longitude)->toHtml() ?>,zoom:14,popupText:'<?php echo e(__('Lokasi Laporan')); ?>'})},100)">
                        <div id="cek-map-laporan-<?php echo e($laporan->nomor_tiket); ?>" class="lc-map"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pengaduanTataPenataan): ?>
        <?php
            $statusColor = $pengaduanTataPenataan->status?->color() ?? 'gray';
            $badgeMap = [
                'gray' => 'lc-status--pending',
                'warning' => 'lc-status--info',
                'success' => 'lc-status--done',
            ];
            $isDone = in_array($statusColor, ['success']);
        ?>
        <div class="lc-result-card max-w-4xl mx-auto">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-6">
                <div class="flex items-center gap-3">
                    <span class="lc-result-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                    </span>
                    <div>
                        <span class="block text-[10px] text-slate-500 dark:text-slate-400 font-extrabold tracking-widest uppercase"><?php echo e(__('Nomor Tiket')); ?></span>
                        <?php if (isset($component)) { $__componentOriginal0ba85dda8da2317d0ab5473991b5d00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0ba85dda8da2317d0ab5473991b5d00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.copy-ticket','data' => ['ticket' => $pengaduanTataPenataan->nomor_tiket,'class' => 'text-2xl font-bold font-mono text-slate-900 dark:text-slate-100']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.copy-ticket'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['ticket' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pengaduanTataPenataan->nomor_tiket),'class' => 'text-2xl font-bold font-mono text-slate-900 dark:text-slate-100']); ?>
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
                </div>
                <span class="lc-status-badge <?php echo e($badgeMap[$statusColor] ?? 'lc-status--pending'); ?>">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isDone): ?>
                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                    <?php else: ?>
                        <span class="lc-status-dot"></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php echo e($pengaduanTataPenataan->status?->label() ?? $pengaduanTataPenataan->status); ?>

                </span>
            </div>

            <?php if (isset($component)) { $__componentOriginal1a3ac395ac0a4ff13489a9dd135cb158 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1a3ac395ac0a4ff13489a9dd135cb158 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.status-timeline','data' => ['timeline' => \App\Services\TicketTimelineService::forTicket($pengaduanTataPenataan)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.status-timeline'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['timeline' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\App\Services\TicketTimelineService::forTicket($pengaduanTataPenataan))]); ?>
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
                    <div class="grid grid-cols-2 gap-4">
                        <?php
                            $items = [
                                __('Nama Pelapor') => $pengaduanTataPenataan->nama_pelapor,
                                __('Jenis Pengaduan') => \App\Enums\JenisPengaduanTataPenataan::tryFrom($pengaduanTataPenataan->jenis_pengaduan)?->label() ?? $pengaduanTataPenataan->jenis_pengaduan,
                                __('Tanggal Lapor') => $pengaduanTataPenataan->created_at->format('d M Y H:i'),
                                __('Alamat') => $pengaduanTataPenataan->alamat,
                            ];
                            if ($pengaduanTataPenataan->nama_terlapor) $items[__('Nama Terlapor')] = $pengaduanTataPenataan->nama_terlapor;
                            if ($pengaduanTataPenataan->nama_perusahaan_terlapor) $items[__('Perusahaan Terlapor')] = $pengaduanTataPenataan->nama_perusahaan_terlapor;
                        ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="lc-info-tile">
                                <span class="lc-info-label"><?php echo e($label); ?></span>
                                <span class="lc-info-value"><?php echo e($value); ?></span>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                    <div class="lc-desc-box">
                        <span class="lc-info-label"><?php echo e(__('Deskripsi')); ?></span>
                        <p class="lc-desc-text"><?php echo e($pengaduanTataPenataan->deskripsi); ?></p>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pengaduanTataPenataan->catatan_admin): ?>
                        <div class="lc-note-box">
                            <span class="lc-note-label">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                <?php echo e(__('Catatan Admin')); ?>

                            </span>
                            <p class="text-sm mt-1.5"><?php echo e($pengaduanTataPenataan->catatan_admin); ?></p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pengaduanTataPenataan->fotos->isNotEmpty()): ?>
                        <div class="space-y-2">
                            <span class="lc-info-label"><?php echo e(__('Foto Bukti Pengaduan')); ?></span>
                            <div class="grid grid-cols-3 gap-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $pengaduanTataPenataan->fotos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $foto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <div class="lc-photo-thumb">
                                        <img src="<?php echo e($foto->fullUrl()); ?>" alt="<?php echo e(__('Foto bukti pengaduan')); ?>" class="w-full h-full object-cover" />
                                    </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="space-y-4">
                    <h3 class="lc-section-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.4 8.5c0 5.5-8.4 11.5-8.4 11.5S3.6 14 3.6 8.5a8.4 8.4 0 1 1 16.8 0Z"/><circle cx="12" cy="8.5" r="2.6"/></svg>
                        <?php echo e(__('Lokasi Peta')); ?>

                    </h3>
                    <div wire:ignore <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'map-ttp-'.e($pengaduanTataPenataan->nomor_tiket).''; ?>wire:key="map-ttp-<?php echo e($pengaduanTataPenataan->nomor_tiket); ?>"
                         x-data x-init="setTimeout(function(){dlhSimpleMap('cek-map-ttp-<?php echo e($pengaduanTataPenataan->nomor_tiket); ?>',{lat:<?php echo \Illuminate\Support\Js::from($pengaduanTataPenataan->latitude)->toHtml() ?>,lng:<?php echo \Illuminate\Support\Js::from($pengaduanTataPenataan->longitude)->toHtml() ?>,zoom:14,popupText:'<?php echo e(__('Lokasi Pengaduan')); ?>'})},100)">
                        <div id="cek-map-ttp-<?php echo e($pengaduanTataPenataan->nomor_tiket); ?>" class="lc-map"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap');

        .lc-wrap { font-family: 'Outfit', ui-sans-serif, system-ui, sans-serif; }

        /* ── Search Card ── */
        .lc-search-card {
            background: #fff; border: 1px solid #e8efe9; border-radius: 24px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(13,43,29,0.04), 0 12px 32px -12px rgba(13,43,29,0.1);
        }
        .lc-search-head { display: flex; align-items: flex-start; gap: 14px; margin-bottom: 16px; }
        .lc-search-icon {
            flex-shrink: 0; width: 44px; height: 44px; border-radius: 13px;
            background: linear-gradient(135deg, #178a53, #146a44); color: #fff;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 6px 14px -2px rgba(20, 106, 68, 0.35);
        }
        .lc-search-icon svg { width: 20px; height: 20px; }
        .lc-search-title { font-size: 17px; font-weight: 700; color: #12201a; letter-spacing: -0.01em; }
        .lc-search-desc { font-size: 13px; color: #5b6b63; margin-top: 4px; line-height: 1.55; }

        .lc-search-btn {
            height: 48px; padding: 0 24px; border: none; border-radius: 9999px;
            background: linear-gradient(180deg, #178a53, #146a44); color: #fff;
            font-family: 'Outfit', ui-sans-serif, system-ui, sans-serif;
            font-size: 14px; font-weight: 700; cursor: pointer;
            box-shadow: 0 8px 20px -6px rgba(20, 106, 68, 0.5);
            transition: transform .12s ease, box-shadow .12s ease;
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            white-space: nowrap;
        }
        .lc-search-btn:hover { transform: translateY(-1px); box-shadow: 0 12px 24px -6px rgba(20, 106, 68, 0.55); }

        /* ── Result Card ── */
        .lc-result-card {
            background: #fff; border: 1px solid #e8efe9; border-radius: 24px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(13,43,29,0.04), 0 12px 32px -12px rgba(13,43,29,0.1);
        }
        .lc-result-icon {
            flex-shrink: 0; width: 44px; height: 44px; border-radius: 13px;
            background: #e6f5ec; color: #146a44;
            display: flex; align-items: center; justify-content: center;
        }
        .lc-result-icon svg { width: 20px; height: 20px; }

        /* ── Status Badge ── */
        .lc-status-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 12px; border-radius: 9999px;
            font-size: 12px; font-weight: 700; border: 1px solid transparent;
        }
        .lc-status--done { background: #dcfce7; color: #166534; border-color: #86efac; }
        .lc-status--pending { background: #fef3c7; color: #92400e; border-color: #fde68a; }
        .lc-status--info { background: #dbeafe; color: #1e40af; border-color: #93c5fd; }
        .lc-status--rejected { background: #fee2e2; color: #991b1b; border-color: #fecaca; }
        .lc-status-dot {
            display: inline-block; width: 6px; height: 6px; border-radius: 9999px;
            background: currentColor; animation: lc-pulse 1.6s ease-in-out infinite;
        }
        @keyframes lc-pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }

        /* ── Stepper ── */
        .lc-stepper-wrap {
            display: flex; width: 100%; position: relative; z-index: 0; margin: 20px 0;
        }
        .lc-step { position: relative; flex: 1; text-align: center; }
        .lc-step-line {
            position: absolute; top: 16px; left: 50%; width: 100%; height: 2px; z-index: -1;
        }
        .lc-step-line--bg { background: #e8efe9; }
        .lc-step-line--done { background: #1ea567; }
        .lc-step-line--rejected { background: #ef4444; }
        .lc-step-dot {
            width: 32px; height: 32px; margin: 0 auto; border-radius: 9999px;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700;
            box-shadow: 0 0 0 6px #fff;
        }
        .lc-step-dot--done { background: linear-gradient(135deg, #1ea567, #146a44); color: #fff; box-shadow: 0 0 0 6px #fff, 0 4px 10px -2px rgba(20, 106, 68, 0.4); }
        .lc-step-dot--rejected { background: #ef4444; color: #fff; box-shadow: 0 0 0 6px #fff, 0 4px 10px -2px rgba(239, 68, 68, 0.4); }
        .lc-step-dot--pending { background: #f1f5f3; color: #94a3b8; }
        .lc-step-label {
            display: block; margin-top: 12px;
            font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #5b6b63;
        }

        /* ── Section title ── */
        .lc-section-title {
            display: inline-flex; align-items: center; gap: 8px;
            font-size: 16px; font-weight: 700; color: #12201a;
        }
        .lc-section-title svg { width: 18px; height: 18px; color: #146a44; }

        /* ── Info tile ── */
        .lc-info-tile {
            background: #f8faf9; border: 1px solid #e8efe9; border-radius: 12px; padding: 12px 14px;
        }
        .lc-info-label {
            display: block; font-size: 11px; font-weight: 600; color: #5b6b63;
            text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;
        }
        .lc-info-value { display: block; font-size: 14px; font-weight: 600; color: #12201a; line-height: 1.4; }

        .lc-desc-box {
            background: #f8faf9; border: 1px solid #e8efe9; border-radius: 12px; padding: 14px;
        }
        .lc-desc-text { font-size: 13.5px; color: #475569; line-height: 1.6; margin-top: 6px; }

        .lc-reject-box {
            padding: 14px 16px; background: #fef2f2; border: 1px solid #fecaca;
            border-radius: 14px; border-left: 3px solid #ef4444;
        }
        .lc-reject-label {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 13px; font-weight: 700; color: #991b1b;
        }

        .lc-note-box {
            padding: 14px 16px; background: #f4faf6; border: 1px solid #d1e7da;
            border-radius: 14px; border-left: 3px solid #1ea567;
        }
        .lc-note-label {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 13px; font-weight: 700; color: #146a44;
        }

        .lc-photo-thumb {
            aspect-ratio: 1; border-radius: 12px; overflow: hidden;
            border: 1px solid #e8efe9;
            transition: transform .15s ease;
        }
        .lc-photo-thumb:hover { transform: scale(1.04); }

        .lc-map {
            width: 100%; height: 300px; border-radius: 16px; overflow: hidden;
            border: 1px solid #e8efe9; position: relative; z-index: 0;
        }

        /* ── Dark mode ── */
        .dark .lc-search-card { background: #1e293b; border-color: #334155; }
        .dark .lc-search-title { color: #e2e8f0; }
        .dark .lc-search-desc { color: #94a3b8; }
        .dark .lc-result-card { background: #1e293b; border-color: #334155; }
        .dark .lc-result-icon { background: rgba(30,165,103,0.15); color: #1ea567; }
        .dark .lc-status--done { background: rgba(16,185,129,0.15); color: #6ee7b7; border-color: rgba(16,185,129,0.3); }
        .dark .lc-status--pending { background: rgba(245,158,11,0.15); color: #fcd34d; border-color: rgba(245,158,11,0.3); }
        .dark .lc-status--info { background: rgba(59,130,246,0.15); color: #93c5fd; border-color: rgba(59,130,246,0.3); }
        .dark .lc-status--rejected { background: rgba(239,68,68,0.15); color: #fca5a5; border-color: rgba(239,68,68,0.3); }
        .dark .lc-step-line--bg { background: #334155; }
        .dark .lc-step-dot--done { box-shadow: 0 0 0 6px #1e293b, 0 4px 10px -2px rgba(20, 106, 68, 0.4); }
        .dark .lc-step-dot--rejected { box-shadow: 0 0 0 6px #1e293b, 0 4px 10px -2px rgba(239, 68, 68, 0.4); }
        .dark .lc-step-dot--pending { background: #0f172a; color: #64748b; }
        .dark .lc-step-label { color: #94a3b8; }
        .dark .lc-section-title { color: #e2e8f0; }
        .dark .lc-section-title svg { color: #6ee7b7; }
        .dark .lc-info-tile { background: #0f172a; border-color: #334155; }
        .dark .lc-info-label { color: #94a3b8; }
        .dark .lc-info-value { color: #e2e8f0; }
        .dark .lc-desc-box { background: #0f172a; border-color: #334155; }
        .dark .lc-desc-text { color: #cbd5e1; }
        .dark .lc-reject-box { background: rgba(239,68,68,0.1); border-color: rgba(239,68,68,0.3); }
        .dark .lc-reject-label { color: #fca5a5; }
        .dark .lc-note-box { background: rgba(30,165,103,0.08); border-color: rgba(30,165,103,0.25); }
        .dark .lc-note-label { color: #6ee7b7; }
        .dark .lc-photo-thumb { border-color: #334155; }
        .dark .lc-map { border-color: #334155; }
        .dark .lc-search-btn { background: linear-gradient(180deg, #1ea567, #178a53); }
    </style>
</div><?php /**PATH C:\xampp\htdocs\DLH - PALU\storage\framework\views/livewire/views/c3d0fc44.blade.php ENDPATH**/ ?>