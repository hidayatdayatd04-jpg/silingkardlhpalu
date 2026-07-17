<?php $__env->startSection('title', 'Beranda - Dinas Lingkungan Hidup Kota Palu'); ?>
<?php $__env->startSection('description', 'Selamat datang di Portal SILP Dinas Lingkungan Hidup Kota Palu. Akses layanan multi-bidang: pengaduan lingkungan, pengelolaan sampah & LB3, ruang terbuka hijau, pelacakan armada, dan survei kepuasan.'); ?>
<?php $__env->startSection('full_width', ''); ?>

<?php $__env->startSection('content'); ?>

<?php if (isset($component)) { $__componentOriginal99ed4be9aac85ac7804c9847c53a3c39 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99ed4be9aac85ac7804c9847c53a3c39 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.preloader','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.preloader'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal99ed4be9aac85ac7804c9847c53a3c39)): ?>
<?php $attributes = $__attributesOriginal99ed4be9aac85ac7804c9847c53a3c39; ?>
<?php unset($__attributesOriginal99ed4be9aac85ac7804c9847c53a3c39); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal99ed4be9aac85ac7804c9847c53a3c39)): ?>
<?php $component = $__componentOriginal99ed4be9aac85ac7804c9847c53a3c39; ?>
<?php unset($__componentOriginal99ed4be9aac85ac7804c9847c53a3c39); ?>
<?php endif; ?>
<div class="overflow-x-clip">

    
    
    
    <section class="relative isolate overflow-hidden">
        
        <div class="absolute inset-0 -z-10">
            <img src="<?php echo e(asset('assets/images/hero.jpg')); ?>" alt="" aria-hidden="true"
                 class="h-full w-full object-cover object-center scale-105">
            
            <div class="absolute inset-0 bg-gradient-to-br from-brand-950/95 via-brand-800/85 to-bay-900/80"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_18%_18%,rgba(45,212,191,0.22),transparent_46%),radial-gradient(circle_at_82%_78%,rgba(13,171,206,0.20),transparent_44%)]"></div>
            <div class="absolute inset-x-0 bottom-0 h-40 bg-gradient-to-t from-slate-50 dark:from-slate-950 to-transparent"></div>
        </div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-32 sm:pt-28 sm:pb-40 lg:pt-32">
            <div class="max-w-3xl">
                <span class="hero-enter inline-flex items-center gap-2 rounded-full bg-white/10 ring-1 ring-inset ring-white/25 px-4 py-1.5 text-xs font-semibold tracking-wide uppercase text-white backdrop-blur-md"
                      style="--hero-delay:0ms">
                    <span class="relative flex size-2">
                        <span class="status-ping absolute inline-flex h-full w-full rounded-full bg-brand-300"></span>
                        <span class="relative inline-flex size-2 rounded-full bg-brand-300"></span>
                    </span>
                    <?php echo e(__('Sistem Layanan Publik Digital Terpadu')); ?>

                </span>

                <h1 class="hero-enter mt-6 text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white leading-[1.05]"
                    style="--hero-delay:90ms">
                    <?php echo e(__('Menjaga Palu Tetap')); ?>

                    <span class="block bg-gradient-to-r from-brand-200 via-emerald-200 to-bay-200 bg-clip-text text-transparent">
                        <?php echo e(__('Bersih, Hijau & Asri')); ?>

                    </span>
                </h1>

                <p class="hero-enter mt-6 text-base sm:text-lg text-brand-50/90 max-w-2xl leading-relaxed"
                   style="--hero-delay:180ms">
                    <?php echo e(__('Portal resmi Dinas Lingkungan Hidup Kota Palu untuk pengaduan lingkungan, pengelolaan sampah & LB3, ruang terbuka hijau, dan pelacakan armada — cepat, transparan, tanpa perlu mendaftar akun.')); ?>

                </p>

                <div class="hero-enter mt-9 flex flex-col sm:flex-row gap-3" style="--hero-delay:270ms">
                    <a href="/pengaduan"
                       class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-7 py-3.5 text-sm font-bold text-brand-700 shadow-xl shadow-brand-950/30 ring-1 ring-white/60 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-2xl hover:shadow-brand-900/40 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-white/50">
                        <svg class="size-4 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <?php echo e(__('Laporkan Aduan')); ?>

                    </a>
                    <a href="/lacak"
                       class="group inline-flex items-center justify-center gap-2 rounded-2xl border border-white/40 bg-white/5 px-7 py-3.5 text-sm font-semibold text-white backdrop-blur-md transition-all duration-300 hover:bg-white/15 hover:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-white/30">
                        <svg class="size-4 transition-transform duration-300 group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                        <?php echo e(__('Lacak Status')); ?>

                    </a>
                </div>
            </div>
        </div>
    </section>

    
    
    
    <section class="relative z-10 -mt-20 sm:-mt-24">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <?php $stats = [
                    ['label' => __('Pengunjung Hari Ini'), 'value' => $statistik['pengunjung_hari_ini'] ?? 0, 'icon' => 'M15 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75', 'ring' => 'ring-bay-500/20', 'grad' => 'from-bay-500 to-bay-600'],
                    ['label' => __('Total Pengunjung'), 'value' => $statistik['total_pengunjung'] ?? 0, 'icon' => 'M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z', 'ring' => 'ring-brand-500/20', 'grad' => 'from-brand-500 to-emerald-500'],
                    ['label' => __('Total Pelapor'), 'value' => $statistik['total_pelapor'] ?? 0, 'icon' => 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10zM9 12l2 2 4-4', 'ring' => 'ring-clay-500/20', 'grad' => 'from-clay-500 to-clay-600'],
                    ['label' => __('Total Pengajuan'), 'value' => $statistik['total_pengajuan'] ?? 0, 'icon' => 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8ZM14 2v6h6M9 13h6M9 17h4', 'ring' => 'ring-amber-500/20', 'grad' => 'from-amber-500 to-orange-500'],
                ]; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="reveal group rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm border border-slate-200/70 dark:border-slate-800 p-4 sm:p-5 shadow-[0_10px_40px_-12px_rgba(15,23,42,0.18)] ring-1 <?php echo e($card['ring']); ?> transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_20px_50px_-12px_rgba(15,23,42,0.28)]"
                         style="--reveal-delay: <?php echo e($i * 80); ?>ms">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 size-11 rounded-2xl bg-gradient-to-br <?php echo e($card['grad']); ?> text-white flex items-center justify-center shadow-sm transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-3">
                                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="<?php echo e($card['icon']); ?>"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-2xl font-extrabold text-slate-900 dark:text-white truncate tracking-tight" data-countup data-count="<?php echo e((int) $card['value']); ?>"><?php echo e(number_format($card['value'])); ?></p>
                                <p class="text-[11px] sm:text-xs font-medium text-slate-500 dark:text-slate-400 leading-tight"><?php echo e($card['label']); ?></p>
                            </div>
                        </div>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>
    </section>

    
    <div class="space-y-24 sm:space-y-32 pt-24 sm:pt-32 pb-24">

        
        
        
        <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="reveal max-w-2xl mb-12">
                <span class="inline-block text-xs font-bold uppercase tracking-[0.2em] text-brand-600 dark:text-brand-400 mb-3"><?php echo e(__('Layanan Terpadu')); ?></span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight"><?php echo e(__('Pilih Bidang Layanan Anda')); ?></h2>
                <p class="mt-3 text-base text-slate-500 dark:text-slate-400 leading-relaxed"><?php echo e(__('Empat bidang utama DLH Kota Palu, dirancang untuk mengarahkan Anda ke layanan yang tepat dalam sekali klik.')); ?></p>
            </div>

            <?php
            $bidangs = [
                [
                    'title' => __('Pengendalian'),
                    'desc' => __('Pengaduan, permohonan rekomendasi, & RINTEK/PERTEK lingkungan.'),
                    'accent' => 'clay',
                    'icon' => 'M3 12a9 9 0 1 0 18 0 9 9 0 0 0-18 0Zm9-5v5l3 2',
                    'links' => [
                        [__('Pengaduan'), '/pengaduan'],
                        [__('Cek Status'), '/cek-pengaduan-pengendalian'],
                        [__('Permohonan'), '/permohonan-rekomendasi'],
                        ['RINTEK/PERTEK', '/pengajuan-rintek-pertek'],
                    ],
                ],
                [
                    'title' => __('Sampah & LB3'),
                    'desc' => __('Peta persampahan, pengaduan, & registrasi usaha LB3.'),
                    'accent' => 'amber',
                    'icon' => 'M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6',
                    'links' => [
                        [__('Peta Sampah'), '/peta-persampahan'],
                        [__('Pengaduan'), '/pengaduan'],
                        [__('Registrasi LB3'), '/registrasi-usaha-lb3'],
                    ],
                ],
                [
                    'title' => __('Tata Penataan'),
                    'desc' => __('Pengaduan limbah/asap/kebisingan, peta objek pengawasan & sidak.'),
                    'accent' => 'bay',
                    'icon' => 'M9 20l-5.447-2.724A1 1 0 0 1 3 16.382V5.618a1 1 0 0 1 1.447-.894L9 7m0 13 6-3m-6 3V7m6 10 4.553 2.276A1 1 0 0 0 21 18.382V7.618a1 1 0 0 0-.553-.894L15 4m0 13V4m0 0L9 7',
                    'links' => [
                        [__('Info Modul'), '/tata-penataan'],
                        [__('Pengaduan'), '/pengaduan'],
                        [__('Cek Status'), '/cek-pengaduan-tata-penataan'],
                        [__('Peta Objek'), '/peta-objek-pengawasan'],
                    ],
                ],
                [
                    'title' => __('Ruang Terbuka Hijau'),
                    'desc' => __('Peta RTH, pengaduan, izin tebang & pinjam pakai taman.'),
                    'accent' => 'brand',
                    'icon' => 'M12 22V12m0 0c0-3 2-5 5-5 0 3-2 5-5 5Zm0 0C9 12 7 9.5 7 6c3 0 5 2.5 5 6Z',
                    'links' => [
                        [__('Peta RTH'), '/peta-rth'],
                        [__('Pengaduan'), '/pengaduan'],
                        [__('Izin Tebang'), '/perizinan-tebang-pohon'],
                        [__('Pinjam Taman'), '/pinjam-taman'],
                    ],
                ],
            ];
            $accentMap = [
                'brand' => ['grad' => 'from-brand-500 to-emerald-400', 'chip' => 'bg-brand-50 text-brand-600 dark:bg-brand-900/25 dark:text-brand-300', 'hover' => 'hover:border-brand-300 dark:hover:border-brand-700', 'linkHover' => 'hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 dark:hover:bg-brand-900/20 dark:hover:text-brand-300'],
                'bay'   => ['grad' => 'from-bay-500 to-bay-400', 'chip' => 'bg-bay-50 text-bay-600 dark:bg-bay-900/25 dark:text-bay-300', 'hover' => 'hover:border-bay-300 dark:hover:border-bay-700', 'linkHover' => 'hover:border-bay-300 hover:bg-bay-50 hover:text-bay-700 dark:hover:bg-bay-900/20 dark:hover:text-bay-300'],
                'clay'  => ['grad' => 'from-clay-500 to-clay-400', 'chip' => 'bg-clay-50 text-clay-600 dark:bg-clay-900/25 dark:text-clay-300', 'hover' => 'hover:border-clay-300 dark:hover:border-clay-700', 'linkHover' => 'hover:border-clay-300 hover:bg-clay-50 hover:text-clay-700 dark:hover:bg-clay-900/20 dark:hover:text-clay-300'],
                'amber' => ['grad' => 'from-amber-500 to-amber-400', 'chip' => 'bg-amber-50 text-amber-600 dark:bg-amber-900/25 dark:text-amber-300', 'hover' => 'hover:border-amber-300 dark:hover:border-amber-700', 'linkHover' => 'hover:border-amber-300 hover:bg-amber-50 hover:text-amber-700 dark:hover:bg-amber-900/20 dark:hover:text-amber-300'],
            ];
            ?>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $bidangs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $bidang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php $a = $accentMap[$bidang['accent']]; ?>
                    <div class="reveal group relative flex flex-col rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-[0_2px_10px_rgba(15,23,42,0.04)] transition-all duration-300 hover:-translate-y-1.5 hover:shadow-[0_24px_50px_-18px_rgba(15,23,42,0.28)] <?php echo e($a['hover']); ?>"
                         style="--reveal-delay: <?php echo e($i * 90); ?>ms">
                        
                        <span class="absolute inset-x-6 top-0 h-1 rounded-full bg-gradient-to-r <?php echo e($a['grad']); ?> opacity-70 transition-opacity duration-300 group-hover:opacity-100"></span>

                        <div class="size-12 rounded-2xl <?php echo e($a['chip']); ?> flex items-center justify-center mb-5 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-3">
                            <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="<?php echo e($bidang['icon']); ?>"/></svg>
                        </div>

                        <h3 class="font-bold text-lg text-slate-900 dark:text-white"><?php echo e($bidang['title']); ?></h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed"><?php echo e($bidang['desc']); ?></p>

                        <div class="flex flex-wrap gap-2 mt-6 pt-5 border-t border-slate-100 dark:border-slate-800">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $bidang['links']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $url]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <a href="<?php echo e($url); ?>" class="inline-flex items-center whitespace-nowrap rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/40 px-2.5 py-1.5 text-[11px] font-semibold text-slate-600 dark:text-slate-300 transition-all duration-200 <?php echo e($a['linkHover']); ?>">
                                    <?php echo e($label); ?>

                                </a>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </section>

        
        
        
        <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="reveal text-center max-w-2xl mx-auto mb-14">
                <span class="inline-block text-xs font-bold uppercase tracking-[0.2em] text-bay-600 dark:text-bay-400 mb-3"><?php echo e(__('Alur Sederhana')); ?></span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight"><?php echo e(__('Cara Melapor Tanpa Ribet')); ?></h2>
                <p class="mt-3 text-base text-slate-500 dark:text-slate-400"><?php echo e(__('Tiga langkah berurutan — tanpa perlu mendaftar akun.')); ?></p>
            </div>

            <div class="relative grid md:grid-cols-3 gap-8 md:gap-6">
                
                <div class="hidden md:block absolute top-9 left-[16.66%] right-[16.66%] h-px bg-gradient-to-r from-brand-200 via-bay-300 to-brand-200 dark:from-brand-800 dark:via-bay-800 dark:to-brand-800" aria-hidden="true"></div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [
                    ['step' => '01', 'title' => __('Pilih Layanan'), 'desc' => __('Buka menu bidang terkait dan pilih jenis pengaduan atau permohonan.'), 'icon' => 'M4 6h16M4 12h16M4 18h10'],
                    ['step' => '02', 'title' => __('Isi Formulir'), 'desc' => __('Lengkapi data, lokasi, deskripsi, dan lampirkan foto atau dokumen pendukung.'), 'icon' => 'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 1 1 3.536 3.536L6.5 21.036 3 22l.964-3.5 12.732-12.804Z'],
                    ['step' => '03', 'title' => __('Pantau Status'), 'desc' => __('Simpan nomor tiket untuk melacak progres penanganan kapan saja.'), 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="reveal relative text-center" style="--reveal-delay: <?php echo e($i * 140); ?>ms">
                    <div class="relative z-10 mx-auto mb-6 flex size-[4.5rem] items-center justify-center">
                        <div class="absolute inset-0 rounded-full bg-white dark:bg-slate-900 shadow-[0_10px_30px_-8px_rgba(5,150,105,0.35)] ring-1 ring-brand-100 dark:ring-brand-900/50"></div>
                        <div class="relative flex size-[4.5rem] flex-col items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-emerald-400 text-white">
                            <svg class="size-7" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="<?php echo e($item['icon']); ?>"/></svg>
                        </div>
                        <span class="absolute -top-2 -right-1 z-20 flex size-7 items-center justify-center rounded-full bg-slate-900 dark:bg-white text-[11px] font-extrabold text-white dark:text-slate-900 ring-4 ring-slate-50 dark:ring-slate-950"><?php echo e($item['step']); ?></span>
                    </div>
                    <h3 class="font-bold text-lg text-slate-900 dark:text-white"><?php echo e($item['title']); ?></h3>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 leading-relaxed max-w-xs mx-auto"><?php echo e($item['desc']); ?></p>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </section>

        
        
        
        <?php
        $aduanCategories = [
            ['label' => __('Pembakaran Sampah'), 'bidang' => 'Pengendalian', 'bidangLabel' => __('Pengendalian'), 'accent' => 'clay', 'url' => '/pengaduan'],
            ['label' => __('Limbah B3'), 'bidang' => 'Pengendalian', 'bidangLabel' => __('Pengendalian'), 'accent' => 'clay', 'url' => '/pengaduan'],
            ['label' => __('Banjir'), 'bidang' => 'Pengendalian', 'bidangLabel' => __('Pengendalian'), 'accent' => 'clay', 'url' => '/pengaduan'],
            ['label' => __('Longsor'), 'bidang' => 'Pengendalian', 'bidangLabel' => __('Pengendalian'), 'accent' => 'clay', 'url' => '/pengaduan'],
            ['label' => __('Sampah Menumpuk'), 'bidang' => 'Sampah & LB3', 'bidangLabel' => __('Sampah & LB3'), 'accent' => 'amber', 'url' => '/pengaduan'],
            ['label' => __('Armada Tidak Lewat'), 'bidang' => 'Sampah & LB3', 'bidangLabel' => __('Sampah & LB3'), 'accent' => 'amber', 'url' => '/pengaduan'],
            ['label' => __('Sampah Tidak Diangkut'), 'bidang' => 'Sampah & LB3', 'bidangLabel' => __('Sampah & LB3'), 'accent' => 'amber', 'url' => '/pengaduan'],
            ['label' => __('Limbah / Asap / Kebisingan'), 'bidang' => 'Tata Penataan', 'bidangLabel' => __('Tata Penataan'), 'accent' => 'bay', 'url' => '/pengaduan'],
            ['label' => __('Penebangan Liar'), 'bidang' => 'RTH', 'bidangLabel' => __('RTH'), 'accent' => 'brand', 'url' => '/pengaduan'],
            ['label' => __('Taman Rusak'), 'bidang' => 'RTH', 'bidangLabel' => __('RTH'), 'accent' => 'brand', 'url' => '/pengaduan'],
            ['label' => __('Fasilitas Rusak'), 'bidang' => 'RTH', 'bidangLabel' => __('RTH'), 'accent' => 'brand', 'url' => '/pengaduan'],
            ['label' => __('Lahan Beralih Fungsi'), 'bidang' => 'RTH', 'bidangLabel' => __('RTH'), 'accent' => 'brand', 'url' => '/pengaduan'],
        ];
        // Key filter stabil (Indonesia) untuk pencocokan; label ditampilkan terjemah.
        $aduanFilters = ['Semua' => __('Semua'), 'Pengendalian' => __('Pengendalian'), 'Sampah & LB3' => __('Sampah & LB3'), 'Tata Penataan' => __('Tata Penataan'), 'RTH' => __('RTH')];
        ?>
        <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8"
                 x-data="{ q: '', filter: 'Semua', items: <?php echo e(Illuminate\Support\Js::from($aduanCategories)); ?> }">
            <div class="reveal text-center max-w-2xl mx-auto mb-10">
                <span class="inline-block text-xs font-bold uppercase tracking-[0.2em] text-clay-600 dark:text-clay-400 mb-3"><?php echo e(__('Cari Cepat')); ?></span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight"><?php echo e(__('Temukan Jenis Aduan Anda')); ?></h2>
                <p class="mt-3 text-base text-slate-500 dark:text-slate-400"><?php echo e(__('Ketik kata kunci atau pilih bidang untuk langsung menuju formulir pengaduan yang tepat.')); ?></p>
            </div>

            <div class="reveal rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-[0_10px_40px_-16px_rgba(15,23,42,0.2)] p-5 sm:p-7">
                
                <div class="relative">
                    <svg class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 size-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                    <input type="text" x-model="q" placeholder="<?php echo e(__('Cari jenis aduan…')); ?>"
                           aria-label="<?php echo e(__('Cari jenis aduan…')); ?>"
                           class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50 pl-12 pr-4 py-3.5 text-sm font-medium text-slate-800 dark:text-slate-100 outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 dark:focus:ring-brand-500/20">
                </div>

                
                <div class="mt-4 flex flex-wrap gap-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $aduanFilters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $flabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <button type="button" @click="filter = '<?php echo e($val); ?>'"
                                :class="filter === '<?php echo e($val); ?>'
                                    ? 'bg-brand-600 text-white border-brand-600 shadow-sm shadow-brand-600/30'
                                    : 'bg-white dark:bg-slate-800/50 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:border-brand-300 hover:text-brand-700 dark:hover:text-brand-300'"
                                class="inline-flex items-center rounded-full border px-4 py-1.5 text-xs font-semibold transition-all duration-200">
                            <?php echo e($flabel); ?>

                        </button>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>

                
                <div class="mt-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2.5">
                    <template x-for="item in items.filter(i => (filter === 'Semua' || i.bidang === filter) && i.label.toLowerCase().includes(q.toLowerCase()))" :key="item.label">
                        <a :href="item.url"
                           class="group flex flex-col gap-2 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/40 p-3.5 transition-all duration-200 hover:-translate-y-0.5 hover:border-brand-300 dark:hover:border-brand-700 hover:bg-white dark:hover:bg-slate-800 hover:shadow-md">
                            <span class="text-sm font-semibold text-slate-800 dark:text-slate-100 leading-snug group-hover:text-brand-700 dark:group-hover:text-brand-300 transition-colors" x-text="item.label"></span>
                            <span class="inline-flex w-fit items-center rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide ring-1 ring-inset"
                                  :class="{
                                      'bg-brand-50 text-brand-700 ring-brand-500/20 dark:bg-brand-900/25 dark:text-brand-300': item.accent === 'brand',
                                      'bg-bay-50 text-bay-700 ring-bay-500/20 dark:bg-bay-900/25 dark:text-bay-300': item.accent === 'bay',
                                      'bg-clay-50 text-clay-700 ring-clay-500/20 dark:bg-clay-900/25 dark:text-clay-300': item.accent === 'clay',
                                      'bg-amber-50 text-amber-700 ring-amber-500/20 dark:bg-amber-900/25 dark:text-amber-300': item.accent === 'amber',
                                  }"
                                  x-text="item.bidangLabel"></span>
                        </a>
                    </template>

                    
                    <div class="col-span-full py-10 text-center"
                         x-show="items.filter(i => (filter === 'Semua' || i.bidang === filter) && i.label.toLowerCase().includes(q.toLowerCase())).length === 0"
                         x-cloak>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400"><?php echo e(__('Tidak ada jenis aduan yang cocok.')); ?>

                            <a href="/pengaduan" class="font-semibold text-brand-600 hover:underline"><?php echo e(__('lihat semua layanan')); ?></a>.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        
        
        
        <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="reveal relative overflow-hidden rounded-[2rem] bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-[0_20px_60px_-24px_rgba(15,23,42,0.25)]">
                <div class="absolute -top-24 -right-24 size-72 rounded-full bg-brand-500/10 blur-3xl" aria-hidden="true"></div>
                <div class="grid lg:grid-cols-5 gap-0 items-stretch">
                    
                    <div class="lg:col-span-2 relative min-h-[320px] lg:min-h-full">
                        <img class="absolute inset-0 h-full w-full object-cover object-top" src="<?php echo e(asset('assets/images/foto_kadis.jpeg')); ?>" alt="Kepala Dinas Lingkungan Hidup Kota Palu">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/85 via-slate-900/10 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-6">
                            <p class="text-white font-bold text-lg leading-tight">Mohamad Arif, S.STP., M.Si</p>
                            <p class="text-brand-200 text-sm font-medium mt-0.5"><?php echo e(__('Kepala Dinas Lingkungan Hidup Kota Palu')); ?></p>
                        </div>
                    </div>

                    
                    <div class="lg:col-span-3 p-7 sm:p-10 lg:p-12 flex flex-col justify-center">
                        <span class="inline-block text-xs font-bold uppercase tracking-[0.2em] text-brand-600 dark:text-brand-400 mb-4"><?php echo e(__('Komitmen Kami')); ?></span>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white leading-snug tracking-tight">
                            <?php echo e(__('Pelayanan Publik yang Transparan & Cepat')); ?>

                        </h2>
                        <p class="mt-4 text-slate-600 dark:text-slate-400 leading-relaxed">
                            <?php echo e(__('Dinas Lingkungan Hidup Kota Palu terus meningkatkan kualitas kebersihan, pengelolaan persampahan, dan pelestarian Ruang Terbuka Hijau melalui sistem layanan digital yang terintegrasi dan mudah diakses seluruh masyarakat.')); ?>

                        </p>
                        <div class="mt-7 grid sm:grid-cols-3 gap-3">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [
                                ['t' => __('Gratis'), 's' => __('Tanpa dipungut biaya'), 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2m0-6c1.11 0 2.08.402 2.599 1M12 8V6m0 0a9 9 0 1 0 0 18 9 9 0 0 0 0-18Z', 'c' => 'brand'],
                                ['t' => __('Real-time'), 's' => __('Pelacakan via GPS'), 'icon' => 'M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'c' => 'bay'],
                                ['t' => __('Terbuka'), 's' => __('Status dapat dipantau'), 'icon' => 'M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Zm10 3a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z', 'c' => 'clay'],
                            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php
                                $fc = [
                                    'brand' => 'bg-brand-50 text-brand-600 dark:bg-brand-900/25 dark:text-brand-300',
                                    'bay'   => 'bg-bay-50 text-bay-600 dark:bg-bay-900/25 dark:text-bay-300',
                                    'clay'  => 'bg-clay-50 text-clay-600 dark:bg-clay-900/25 dark:text-clay-300',
                                ][$feat['c']];
                            ?>
                            <div class="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 p-4">
                                <div class="size-9 rounded-xl <?php echo e($fc); ?> flex items-center justify-center mb-3">
                                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="<?php echo e($feat['icon']); ?>"/></svg>
                                </div>
                                <p class="font-bold text-slate-800 dark:text-white text-sm"><?php echo e($feat['t']); ?></p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5"><?php echo e($feat['s']); ?></p>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                        <div class="mt-7">
                            <a href="/profil" class="inline-flex items-center gap-1.5 text-sm font-bold text-brand-600 dark:text-brand-400 hover:gap-2.5 transition-all">
                                <?php echo e(__('Selengkapnya tentang DLH Kota Palu')); ?>

                                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        
        
        
        
        
        <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="reveal text-center max-w-2xl mx-auto mb-12">
                <span class="inline-block text-xs font-bold uppercase tracking-[0.2em] text-brand-600 dark:text-brand-400 mb-3"><?php echo e(__('Dampak Nyata')); ?></span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight"><?php echo e(__('Kerja Nyata untuk Lingkungan Palu')); ?></h2>
                <p class="mt-3 text-base text-slate-500 dark:text-slate-400"><?php echo e(__('Capaian layanan dan operasional DLH Kota Palu yang terus berjalan setiap hari.')); ?></p>
            </div>

            <?php
            $capaian = [
                ['value' => 180, 'suffix' => ' ton', 'label' => __('Sampah Terangkut / Hari'), 'grad' => 'from-amber-500 to-orange-500', 'soft' => 'bg-amber-50 dark:bg-amber-900/20', 'text' => 'text-amber-600 dark:text-amber-400', 'icon' => 'M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6'],
                ['value' => 68, 'suffix' => ' Ha', 'label' => __('Ruang Terbuka Hijau Dikelola'), 'grad' => 'from-brand-500 to-emerald-500', 'soft' => 'bg-brand-50 dark:bg-brand-900/20', 'text' => 'text-brand-600 dark:text-brand-400', 'icon' => 'M12 22V12m0 0c0-3 2-5 5-5 0 3-2 5-5 5Zm0 0C9 12 7 9.5 7 6c3 0 5 2.5 5 6Z'],
                ['value' => 45, 'suffix' => ' Titik', 'label' => __('TPS & Kontainer Aktif'), 'grad' => 'from-bay-500 to-bay-600', 'soft' => 'bg-bay-50 dark:bg-bay-900/20', 'text' => 'text-bay-600 dark:text-bay-400', 'icon' => 'M12 21s-8-4.5-8-11a8 8 0 1 1 16 0c0 6.5-8 11-8 11Zm0-8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z'],
                ['value' => 24, 'suffix' => ' Jam', 'label' => __('Respons Aduan Mendesak'), 'grad' => 'from-clay-500 to-clay-600', 'soft' => 'bg-clay-50 dark:bg-clay-900/20', 'text' => 'text-clay-600 dark:text-clay-400', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
            ];
            ?>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $capaian; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="reveal group relative overflow-hidden rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 sm:p-7 text-center shadow-[0_2px_10px_rgba(15,23,42,0.04)] transition-all duration-300 hover:-translate-y-1.5 hover:shadow-[0_24px_50px_-18px_rgba(15,23,42,0.25)]"
                     style="--reveal-delay: <?php echo e($i * 90); ?>ms">
                    <div class="absolute inset-x-0 -top-16 h-32 bg-gradient-to-b <?php echo e($c['grad']); ?> opacity-0 blur-2xl transition-opacity duration-500 group-hover:opacity-10" aria-hidden="true"></div>
                    <div class="relative mx-auto mb-4 size-14 rounded-2xl <?php echo e($c['soft']); ?> <?php echo e($c['text']); ?> flex items-center justify-center transition-transform duration-300 group-hover:scale-110">
                        <svg class="size-7" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="<?php echo e($c['icon']); ?>"/></svg>
                    </div>
                    <p class="relative text-3xl sm:text-4xl font-extrabold tracking-tight bg-gradient-to-br <?php echo e($c['grad']); ?> bg-clip-text text-transparent"
                       data-countup data-count="<?php echo e($c['value']); ?>" data-suffix="<?php echo e($c['suffix']); ?>">0<?php echo e($c['suffix']); ?></p>
                    <p class="relative mt-2 text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400 leading-tight"><?php echo $c['label']; ?></p>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </section>

        
        
        
        <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="reveal flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-10">
                <div class="max-w-xl">
                    <span class="inline-block text-xs font-bold uppercase tracking-[0.2em] text-brand-600 dark:text-brand-400 mb-3"><?php echo e(__('Informasi Terkini')); ?></span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight"><?php echo e(__('Berita & Artikel')); ?></h2>
                    <p class="mt-3 text-base text-slate-500 dark:text-slate-400"><?php echo e(__('Update kegiatan, edukasi lingkungan, dan informasi layanan DLH Kota Palu.')); ?></p>
                </div>
                <a href="/berita" class="group inline-flex items-center justify-center gap-1.5 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-5 py-3 text-sm font-bold text-brand-600 dark:text-brand-400 shadow-sm transition-all hover:border-brand-300 hover:bg-brand-50 dark:hover:bg-brand-900/20 shrink-0">
                    <?php echo e(__('Lihat Semua Berita')); ?>

                    <svg class="size-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
                </a>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($artikels) && $artikels->count()): ?>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $artikels->take(6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $artikel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <a href="/berita/<?php echo e($artikel->slug); ?>"
                           class="reveal group flex flex-col rounded-3xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden shadow-[0_2px_10px_rgba(15,23,42,0.04)] transition-all duration-300 hover:-translate-y-1.5 hover:shadow-[0_24px_50px_-18px_rgba(15,23,42,0.25)] hover:border-brand-200 dark:hover:border-brand-800"
                           style="--reveal-delay: <?php echo e(($i % 3) * 100); ?>ms">
                            <div class="relative aspect-[16/10] overflow-hidden bg-slate-100 dark:bg-slate-800">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($artikel->thumbnail): ?>
                                    <img src="<?php echo e(asset('storage/'.$artikel->thumbnail)); ?>" alt="<?php echo e($artikel->judul); ?>" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy">
                                <?php else: ?>
                                    <div class="h-full w-full bg-gradient-to-br from-brand-600 via-brand-500 to-bay-400"></div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <span class="absolute left-3 top-3 inline-flex items-center rounded-full bg-white/90 dark:bg-slate-900/90 backdrop-blur-sm px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-brand-700 dark:text-brand-300 ring-1 ring-inset ring-brand-500/20">
                                    <?php echo e($artikel->kategori?->label() ?? 'Umum'); ?>

                                </span>
                            </div>
                            <div class="p-5 flex flex-col flex-1">
                                <h3 class="font-bold text-slate-900 dark:text-white line-clamp-2 leading-snug group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors"><?php echo e($artikel->judul); ?></h3>
                                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed flex-1"><?php echo e(Str::limit(strip_tags($artikel->konten), 120)); ?></p>
                                <div class="flex items-center justify-between pt-4 mt-4 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-400 dark:text-slate-500">
                                    <span class="font-medium"><?php echo e($artikel->tanggal_publish?->translatedFormat('d M Y')); ?></span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($artikel->user): ?>
                                        <span class="inline-flex items-center gap-1.5">
                                            <span class="size-1.5 rounded-full bg-brand-400"></span>
                                            <?php echo e($artikel->user->name); ?>

                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        </a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            <?php else: ?>
                <div class="reveal rounded-3xl border border-dashed border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50 p-12 text-center">
                    <div class="mx-auto size-16 rounded-2xl bg-brand-50 dark:bg-brand-900/25 flex items-center justify-center text-brand-600 dark:text-brand-400 mb-4">
                        <svg class="size-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M19 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v1m2 13a2 2 0 0 1-2-2V7m2 13a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8Z"/></svg>
                    </div>
                    <p class="font-semibold text-slate-800 dark:text-slate-200"><?php echo e(__('Belum ada berita dipublikasikan')); ?></p>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-md mx-auto"><?php echo e(__('Artikel akan ditampilkan di sini setelah admin mempublikasikannya melalui panel admin.')); ?></p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </section>

        
        
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($profil?->visi) || !empty($profil?->misi)): ?>
        <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="reveal relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-brand-950 via-brand-900 to-bay-950 p-8 sm:p-12 lg:p-16 ring-1 ring-white/10 shadow-[0_30px_80px_-30px_rgba(4,120,87,0.6)]">
                
                <div class="absolute -top-32 -right-20 size-96 rounded-full bg-brand-500/20 blur-3xl" aria-hidden="true"></div>
                <div class="absolute -bottom-32 -left-16 size-80 rounded-full bg-bay-500/20 blur-3xl" aria-hidden="true"></div>
                <svg class="absolute -top-8 right-6 size-52 text-white/[0.04] rotate-12" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M17 8C8 10 5.9 16.17 3.82 21.34l1.89.66C7.72 16.5 9.8 12 17 12v3l5-5-5-5v3z"/></svg>
                <div class="absolute inset-0 opacity-[0.05]" aria-hidden="true"
                     style="background-image:radial-gradient(circle,white 1px,transparent 1px);background-size:26px 26px"></div>

                <div class="relative grid lg:grid-cols-12 gap-10 lg:gap-12 items-center">
                    
                    <div class="lg:col-span-4 reveal" style="--reveal-delay:80ms">
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/10 ring-1 ring-inset ring-white/20 px-3.5 py-1.5 text-[11px] font-bold uppercase tracking-[0.2em] text-brand-200 mb-5">
                            <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 2v4M12 18v4M2 12h4M18 12h4"/></svg>
                            <?php echo e(__('Arah & Tujuan')); ?>

                        </span>
                        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-[1.05]">
                            <?php echo e(__('Visi &')); ?><br class="hidden lg:block">
                            <span class="bg-gradient-to-r from-brand-200 via-emerald-200 to-bay-200 bg-clip-text text-transparent"><?php echo e(__('Misi Kami')); ?></span>
                        </h2>
                        <p class="mt-5 text-brand-50/70 leading-relaxed max-w-md">
                            <?php echo e(__('Fondasi arah pembangunan lingkungan hidup Kota Palu yang berkelanjutan, inklusif, dan berpihak pada masyarakat.')); ?>

                        </p>
                        <a href="/profil#visi-misi" class="group mt-7 inline-flex items-center gap-2 rounded-2xl bg-white px-6 py-3 text-sm font-bold text-brand-800 shadow-lg shadow-brand-950/40 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl">
                            <?php echo e(__('Baca Selengkapnya')); ?>

                            <svg class="size-4 transition-transform duration-300 group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
                        </a>
                    </div>

                    
                    <div class="lg:col-span-8 space-y-5">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($profil->visi): ?>
                        <div class="reveal group relative overflow-hidden rounded-3xl bg-white/[0.07] backdrop-blur-md ring-1 ring-white/15 p-6 sm:p-8 transition-all duration-300 hover:bg-white/[0.1] hover:ring-white/25" style="--reveal-delay:160ms">
                            <span class="absolute left-0 top-8 bottom-8 w-1 rounded-full bg-gradient-to-b from-brand-300 to-emerald-400"></span>
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 size-12 rounded-2xl bg-gradient-to-br from-brand-400 to-emerald-500 text-white flex items-center justify-center shadow-lg shadow-brand-900/50 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-3">
                                    <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Zm10 3a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-xs font-bold uppercase tracking-[0.2em] text-brand-300 mb-2"><?php echo e(__('Visi')); ?></h3>
                                    <div class="text-lg sm:text-xl font-semibold text-white leading-snug line-clamp-4"><?php echo e(strip_tags($profil->visi_translated)); ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($profil->misi): ?>
                        <div class="reveal group relative overflow-hidden rounded-3xl bg-white/[0.07] backdrop-blur-md ring-1 ring-white/15 p-6 sm:p-8 transition-all duration-300 hover:bg-white/[0.1] hover:ring-white/25" style="--reveal-delay:240ms">
                            <span class="absolute left-0 top-8 bottom-8 w-1 rounded-full bg-gradient-to-b from-bay-300 to-bay-500"></span>
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 size-12 rounded-2xl bg-gradient-to-br from-bay-400 to-bay-600 text-white flex items-center justify-center shadow-lg shadow-bay-900/50 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-3">
                                    <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 12a10 10 0 1 1-20 0 10 10 0 0 1 20 0Zm-6 0a4 4 0 1 1-8 0 4 4 0 0 1 8 0Zm-4 0h.01"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-xs font-bold uppercase tracking-[0.2em] text-bay-300 mb-2"><?php echo e(__('Misi')); ?></h3>
                                    <div class="text-sm sm:text-base text-brand-50/85 leading-relaxed line-clamp-5"><?php echo e(strip_tags($profil->misi_translated)); ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        
        
        <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="reveal relative overflow-hidden rounded-[2rem] bg-slate-950 p-7 sm:p-10 lg:p-12 ring-1 ring-white/10">
                
                <div class="absolute -top-24 -right-24 size-80 rounded-full bg-brand-600/25 blur-3xl" aria-hidden="true"></div>
                <div class="absolute -bottom-24 -left-24 size-72 rounded-full bg-bay-600/20 blur-3xl" aria-hidden="true"></div>
                
                <div class="absolute inset-0 opacity-[0.04]" aria-hidden="true"
                     style="background-image:linear-gradient(to right,white 1px,transparent 1px),linear-gradient(to bottom,white 1px,transparent 1px);background-size:44px 44px"></div>

                <div class="relative z-10 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-9">
                    <div class="max-w-xl">
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/5 ring-1 ring-inset ring-white/15 px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-brand-300 mb-4">
                            <span class="relative flex size-2">
                                <span class="status-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400"></span>
                                <span class="relative inline-flex size-2 rounded-full bg-emerald-400"></span>
                            </span>
                            <?php echo e(__('Sistem Online')); ?>

                        </span>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight"><?php echo e(__('Kesiapan Armada Operasional')); ?></h2>
                        <p class="text-slate-400 mt-3 text-sm sm:text-base leading-relaxed"><?php echo e(__('Armada siap merespons di seluruh penjuru Kota Palu, dipantau secara real-time via GPS.')); ?></p>
                    </div>
                    <a href="/armada" class="group inline-flex items-center gap-2 rounded-2xl bg-white/5 ring-1 ring-inset ring-white/15 px-5 py-3 text-sm font-bold text-white transition-all hover:bg-white/10 shrink-0">
                        <?php echo e(__('Peta Pelacakan GPS')); ?>

                        <svg class="size-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                </div>

                <div class="relative z-10 grid md:grid-cols-2 gap-5">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [
                        ['img' => 'r4_pickup.jpeg', 'title' => __('Armada L300 / Pick Up'), 'desc' => __('Menjangkau pemukiman padat dan gang sempit.'), 'tag' => __('Unit Ringan')],
                        ['img' => 'r6_truck.jpeg', 'title' => __('Armada Truk (R6)'), 'desc' => __('Memindahkan sampah dari TPS menuju TPA.'), 'tag' => __('Unit Berat')],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $armada): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="reveal group overflow-hidden rounded-2xl border border-white/10 bg-white/[0.03] backdrop-blur-sm transition-all duration-300 hover:border-white/20"
                         style="--reveal-delay: <?php echo e($i * 120); ?>ms">
                        <div class="relative aspect-[16/9] overflow-hidden">
                            <img src="<?php echo e(asset('assets/images/'.$armada['img'])); ?>" alt="<?php echo e($armada['title']); ?>" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 to-transparent"></div>
                            <span class="absolute right-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-slate-950/70 backdrop-blur-sm px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-300 ring-1 ring-inset ring-emerald-400/30">
                                <span class="relative flex size-1.5">
                                    <span class="status-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400"></span>
                                    <span class="relative inline-flex size-1.5 rounded-full bg-emerald-400"></span>
                                </span>
                                <?php echo e(__('Online')); ?>

                            </span>
                        </div>
                        <div class="p-5">
                            <div class="flex items-center justify-between gap-3">
                                <h3 class="font-bold text-white"><?php echo e($armada['title']); ?></h3>
                                <span class="text-[10px] font-bold uppercase tracking-wide text-slate-400 bg-white/5 rounded-full px-2 py-0.5 ring-1 ring-inset ring-white/10"><?php echo e($armada['tag']); ?></span>
                            </div>
                            <p class="text-sm text-slate-400 mt-1.5 leading-relaxed"><?php echo e($armada['desc']); ?></p>
                        </div>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </div>
        </section>

        
        
        
        <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="reveal text-center max-w-2xl mx-auto mb-10">
                <span class="inline-block text-xs font-bold uppercase tracking-[0.2em] text-bay-600 dark:text-bay-400 mb-3"><?php echo e(__('Pertanyaan Umum')); ?></span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight"><?php echo e(__('Hal yang Sering Ditanyakan')); ?></h2>
                <p class="mt-3 text-base text-slate-500 dark:text-slate-400"><?php echo e(__('Belum menemukan jawaban? Hubungi call center kami di bagian bawah halaman.')); ?></p>
            </div>

            <?php
            $faqs = [
                ['q' => __('Apakah saya perlu mendaftar akun untuk melapor?'), 'a' => __('Tidak. Seluruh layanan pengaduan dan permohonan dapat diakses tanpa registrasi akun. Cukup isi formulir, dan Anda akan mendapatkan nomor tiket untuk memantau status.')],
                ['q' => __('Bagaimana cara melacak status laporan saya?'), 'a' => __('Simpan nomor tiket yang muncul setelah Anda mengirim laporan, lalu buka menu “Lacak Pelaporan” atau halaman Cek Status pada bidang terkait, dan masukkan nomor tiket tersebut.')],
                ['q' => __('Apakah ada biaya untuk layanan pengaduan?'), 'a' => __('Seluruh layanan pengaduan lingkungan, persampahan, dan RTH bersifat gratis dan tidak dipungut biaya apa pun.')],
                ['q' => __('Berapa lama laporan saya akan ditindaklanjuti?'), 'a' => __('Laporan mendesak diupayakan direspons dalam 24 jam. Waktu penanganan akhir menyesuaikan jenis aduan dan tingkat kompleksitas di lapangan, dan dapat Anda pantau melalui nomor tiket.')],
                ['q' => __('Apa saja yang perlu saya siapkan saat melapor?'), 'a' => __('Sebaiknya siapkan deskripsi singkat kejadian, titik lokasi, serta foto atau dokumen pendukung agar petugas dapat memverifikasi dan menindaklanjuti dengan lebih cepat.')],
            ];
            ?>

            <div class="space-y-3" x-data="{ open: 0 }">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $faqs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $faq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="reveal rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden transition-colors"
                     :class="open === <?php echo e($i); ?> ? 'ring-1 ring-brand-500/30 shadow-[0_12px_40px_-16px_rgba(5,150,105,0.35)]' : ''"
                     style="--reveal-delay: <?php echo e($i * 70); ?>ms">
                    <button type="button" @click="open = (open === <?php echo e($i); ?> ? null : <?php echo e($i); ?>)"
                            class="w-full flex items-center justify-between gap-4 p-5 text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500/40 rounded-2xl"
                            :aria-expanded="open === <?php echo e($i); ?>">
                        <span class="font-semibold text-slate-800 dark:text-slate-100 text-sm sm:text-base"><?php echo e($faq['q']); ?></span>
                        <span class="flex-shrink-0 size-8 rounded-full flex items-center justify-center transition-all duration-300"
                              :class="open === <?php echo e($i); ?> ? 'bg-brand-600 text-white rotate-45' : 'bg-slate-100 dark:bg-slate-800 text-slate-500'">
                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                        </span>
                    </button>
                    <div x-cloak class="grid overflow-hidden transition-all duration-300 ease-out"
                         :class="open === <?php echo e($i); ?> ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'">
                        <div class="min-h-0 overflow-hidden">
                            <p class="px-5 pb-5 text-sm text-slate-500 dark:text-slate-400 leading-relaxed"><?php echo e($faq['a']); ?></p>
                        </div>
                    </div>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </section>

        
        
        
        <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="reveal relative overflow-hidden rounded-[2rem] bg-gradient-to-r from-brand-700 via-brand-600 to-bay-600 p-8 sm:p-10 shadow-[0_20px_60px_-24px_rgba(5,150,105,0.5)]">
                <div class="absolute inset-0 opacity-25 bg-[radial-gradient(circle_at_15%_20%,white,transparent_45%),radial-gradient(circle_at_85%_85%,rgba(255,255,255,0.4),transparent_40%)]" aria-hidden="true"></div>
                <div class="relative flex flex-col sm:flex-row items-center justify-between gap-6">
                    <div class="text-center sm:text-left max-w-xl">
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight"><?php echo e(__('Bantu Kami Menjadi Lebih Baik')); ?></h2>
                        <p class="text-brand-50/90 mt-2 text-sm sm:text-base leading-relaxed"><?php echo e(__('Isi Survei Kepuasan Masyarakat (IKM) untuk membantu kami mengevaluasi dan meningkatkan kualitas layanan.')); ?></p>
                    </div>
                    <a href="/survei" class="group flex-shrink-0 inline-flex items-center gap-2 rounded-2xl bg-white px-7 py-3.5 text-sm font-bold text-brand-700 shadow-xl shadow-brand-950/20 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-2xl focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-white/50">
                        <?php echo e(__('Isi Survei Sekarang')); ?>

                        <svg class="size-4 transition-transform duration-300 group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                </div>
            </div>
        </section>

        
        
        
        <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="reveal grid lg:grid-cols-2 gap-0 overflow-hidden rounded-[2rem] border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-[0_20px_60px_-28px_rgba(15,23,42,0.22)]">
                
                <div class="p-7 sm:p-10 lg:p-12 flex flex-col justify-center">
                    <span class="inline-block text-xs font-bold uppercase tracking-[0.2em] text-bay-600 dark:text-bay-400 mb-3"><?php echo e(__('Kunjungi Kami')); ?></span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight"><?php echo e(__('Lokasi & Jam Layanan')); ?></h2>
                    <p class="mt-3 text-slate-500 dark:text-slate-400 leading-relaxed"><?php echo e(__('Datang langsung ke kantor kami pada jam kerja, atau akses seluruh layanan secara daring 24 jam.')); ?></p>

                    <div class="mt-7 space-y-4">
                        <div class="flex items-start gap-3.5">
                            <span class="mt-0.5 flex-shrink-0 size-10 rounded-2xl bg-brand-50 dark:bg-brand-900/25 text-brand-600 dark:text-brand-300 flex items-center justify-center">
                                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-8-4.5-8-11a8 8 0 1 1 16 0c0 6.5-8 11-8 11Zm0-8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/></svg>
                            </span>
                            <div>
                                <p class="font-bold text-slate-800 dark:text-white text-sm"><?php echo e(__('Alamat Kantor')); ?></p>
                                <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed"><?php echo e(__('Jl. Pipit, Tanamodindi, Kec. Palu Selatan, Kota Palu, Sulawesi Tengah 94111')); ?></p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3.5">
                            <span class="mt-0.5 flex-shrink-0 size-10 rounded-2xl bg-bay-50 dark:bg-bay-900/25 text-bay-600 dark:text-bay-300 flex items-center justify-center">
                                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            </span>
                            <div>
                                <p class="font-bold text-slate-800 dark:text-white text-sm"><?php echo e(__('Jam Kerja')); ?></p>
                                <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed"><?php echo e(__('Senin – Jumat, 08.00 – 16.00 WITA')); ?></p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3.5">
                            <span class="mt-0.5 flex-shrink-0 size-10 rounded-2xl bg-clay-50 dark:bg-clay-900/25 text-clay-600 dark:text-clay-300 flex items-center justify-center">
                                <svg class="size-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 0C5.405 0 .025 5.38.025 12.006c0 2.118.552 4.186 1.603 6.002L.002 24l6.14-1.61c1.748.956 3.722 1.463 5.889 1.463 6.626 0 12.006-5.38 12.006-12.006S18.657 0 12.031 0z"/></svg>
                            </span>
                            <div>
                                <p class="font-bold text-slate-800 dark:text-white text-sm"><?php echo e(__('Call Center / WhatsApp')); ?></p>
                                <a href="https://wa.me/6285191512076" target="_blank" rel="noopener noreferrer" class="text-sm font-semibold text-brand-600 dark:text-brand-400 hover:underline">0851-9151-2076</a>
                            </div>
                        </div>
                    </div>

                    <a href="https://www.google.com/maps/search/?api=1&query=Dinas+Lingkungan+Hidup+Kota+Palu" target="_blank" rel="noopener noreferrer"
                       class="group mt-8 inline-flex w-fit items-center gap-2 rounded-2xl bg-brand-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-brand-600/25 transition-all duration-300 hover:-translate-y-0.5 hover:bg-brand-700">
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 0 1 3 16.382V5.618a1 1 0 0 1 1.447-.894L9 7m0 13 6-3m-6 3V7m6 10 5.447 2.724A1 1 0 0 0 21 18.382V7.618a1 1 0 0 0-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        <?php echo e(__('Buka di Google Maps')); ?>

                    </a>
                </div>

                
                <div class="relative min-h-[320px] lg:min-h-full bg-slate-100 dark:bg-slate-800">
                    <iframe
                        title="Peta Lokasi DLH Kota Palu"
                        src="https://www.google.com/maps?q=Dinas%20Lingkungan%20Hidup%20Kota%20Palu&output=embed"
                        class="absolute inset-0 h-full w-full grayscale-[0.15] contrast-[1.05]"
                        style="border:0" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                </div>
            </div>
        </section>

        
        
        
        <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="reveal flex flex-col sm:flex-row items-center justify-center gap-x-3 gap-y-2 rounded-2xl border border-clay-200/80 dark:border-clay-900/40 bg-clay-50/70 dark:bg-clay-950/20 px-6 py-4 text-center">
                <span class="inline-flex items-center gap-2 text-sm font-bold text-clay-700 dark:text-clay-300">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/></svg>
                    <?php echo e(__('Butuh Penanganan Mendesak?')); ?>

                </span>
                <span class="text-sm text-slate-600 dark:text-slate-400">
                    <?php echo e(__('Hubungi Call Center:')); ?>

                    <a href="https://wa.me/6285191512076" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 font-bold text-brand-600 dark:text-brand-400 hover:underline">
                        <svg class="size-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 0C5.405 0 .025 5.38.025 12.006c0 2.118.552 4.186 1.603 6.002L.002 24l6.14-1.61c1.748.956 3.722 1.463 5.889 1.463 6.626 0 12.006-5.38 12.006-12.006S18.657 0 12.031 0z"/></svg>
                        0851-9151-2076 (WhatsApp)
                    </a>
                </span>
            </div>
        </section>

    </div>
</div>


<?php $__env->startPush('scripts'); ?>
<script>
    (function () {
        var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        function initMotion() {
        /* ---- Scroll reveal (bidirectional: masuk & keluar viewport) ---- */
        var els = document.querySelectorAll('.reveal');
        if (reduced || !('IntersectionObserver' in window)) {
            els.forEach(function (el) { el.classList.add('is-revealed'); });
        } else {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-revealed');
                    } else if (entry.boundingClientRect.top > 0) {
                        // Hanya sembunyikan lagi bila elemen keluar ke BAWAH viewport,
                        // agar animasi berulang saat scroll naik-turun tanpa "berkedip" di atas.
                        entry.target.classList.remove('is-revealed');
                    }
                });
            }, { rootMargin: '0px 0px -10% 0px', threshold: 0.12 });
            els.forEach(function (el) { observer.observe(el); });
        }

        /* ---- Count-up angka (statistik & capaian) ---- */
        var counters = document.querySelectorAll('[data-countup]');
        var fmt = function (n) { return new Intl.NumberFormat('id-ID').format(Math.round(n)); };

        if (reduced || !('IntersectionObserver' in window)) {
            counters.forEach(function (el) {
                el.textContent = fmt(parseFloat(el.getAttribute('data-count')) || 0) + (el.getAttribute('data-suffix') || '');
            });
        } else {
            var animate = function (el) {
                var target = parseFloat(el.getAttribute('data-count')) || 0;
                var suffix = el.getAttribute('data-suffix') || '';
                var dur = 1400, start = null;
                var step = function (ts) {
                    if (start === null) start = ts;
                    var p = Math.min((ts - start) / dur, 1);
                    var eased = 1 - Math.pow(1 - p, 3); // easeOutCubic
                    el.textContent = fmt(target * eased) + suffix;
                    if (p < 1) requestAnimationFrame(step);
                    else el.textContent = fmt(target) + suffix;
                };
                requestAnimationFrame(step);
            };
            var countObs = new IntersectionObserver(function (entries, obs) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) { animate(entry.target); obs.unobserve(entry.target); }
                });
            }, { threshold: 0.4 });
            counters.forEach(function (el) { countObs.observe(el); });
        }
        } /* /initMotion */

        // Mulai animasi SETELAH preloader terangkat, agar entrance hero & reveal
        // layar pertama tidak terbuang di balik layar loading.
        var started = false;
        function start() {
            if (started) return;
            started = true;
            document.documentElement.classList.add('dlh-ready');
            initMotion();
        }

        if (document.documentElement.classList.contains('dlh-ready') ||
            !document.getElementById('dlh-preloader')) {
            start();
        } else {
            window.addEventListener('dlh:ready', start, { once: true });
            // Failsafe bila event tak pernah terkirim (lebih lama dari durasi preloader 6 dtk).
            setTimeout(start, 7000);
        }
    })();
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Backup\DLH - Palu\resources\views/welcome.blade.php ENDPATH**/ ?>