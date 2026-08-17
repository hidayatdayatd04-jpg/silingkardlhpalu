<?php $__env->startSection('title', \App\Support\Admin\AdminRegistry::titleFor($record, $resource).' — Detail — Admin DLH'); ?>
<?php $__env->startSection('heading', $resource['label']); ?>

<?php $__env->startSection('content'); ?>
<?php
    $format = function ($value) {
        if ($value instanceof BackedEnum) {
            return method_exists($value, 'label') ? $value->label() : $value->value;
        }
        if ($value instanceof DateTimeInterface) {
            return $value->format('d M Y H:i');
        }
        if (is_bool($value)) {
            return $value ? 'Ya' : 'Tidak';
        }
        return filled($value) ? (string) $value : '-';
    };

    $statusValue = $record->status ?? null;
    $isEnumStatus = $statusValue instanceof BackedEnum;
    $statusText = $statusValue ? (is_string($statusValue) ? $statusValue : $format($statusValue)) : null;
    $statusVariant = match(true) {
        !$statusValue => 'neutral',
        in_array($statusText, ['Ditinjau', 'Ditindaklanjuti', 'Selesai', 'Disetujui'], true) => 'success',
        in_array($statusText, ['Belum Ditinjau', 'Belum Ditindaklanjuti', 'Pending', 'Menunggu'], true) => 'warning',
        in_array($statusText, ['Ditolak', 'Gagal', 'Batal'], true) => 'danger',
        default => 'info',
    };

    $regularFields = collect($fields)->reject(fn($f) => in_array($f['type'], ['file', 'textarea', 'section', 'photos']))->values()->all();
    $textareaFieldList = collect($fields)->filter(fn($f) => $f['type'] === 'textarea')->values()->all();
    $fileFieldList = collect($fields)->filter(fn($f) => $f['type'] === 'file')->values()->all();

    $iconFor = function ($fieldName) {
        return match(true) {
            str_contains($fieldName, 'nomor') => 'file-text',
            str_contains($fieldName, 'nama') || str_contains($fieldName, 'pelapor') || str_contains($fieldName, 'pemohon') => 'user',
            in_array($fieldName, ['email', 'username'], true) => 'mail',
            in_array($fieldName, ['status', 'kondisi', 'hasil'], true) => 'alert-circle',
            str_contains($fieldName, 'alamat') || str_contains($fieldName, 'lokasi') => 'map-pin',
            str_contains($fieldName, 'tanggal') || in_array($fieldName, ['created_at', 'updated_at'], true) => 'calendar',
            in_array($fieldName, ['latitude', 'longitude'], true) => 'map-pin',
            str_contains($fieldName, 'jenis') || str_contains($fieldName, 'kategori') || str_contains($fieldName, 'bidang') => 'filter',
            str_contains($fieldName, 'hp') || str_contains($fieldName, 'telepon') => 'message',
            default => 'file-text',
        };
    };

    $mapLat = $record->latitude ?? null;
    $mapLng = $record->longitude ?? null;
    $hasMap = $mapLat !== null && $mapLng !== null && $mapLat != 0 && $mapLng != 0;

    $fotos = ($record instanceof \App\Models\Laporan && $record->fotos && $record->fotos->isNotEmpty())
        ? $record->fotos->map(fn($f) => ['url' => $f->fullUrl(), 'caption' => ''])->all()
        : [];
?>

<div x-data x-init="window.staggerReveal($el.querySelectorAll('.stagger-item'), 80)" class="space-y-6">

    
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => \App\Support\Admin\AdminRegistry::titleFor($record, $resource),'subtitle' => $record->created_at ? 'Dibuat ' . $record->created_at->translatedFormat('d F Y, H:i') : null,'breadcrumbs' => [
            ['label' => $resource['label'], 'url' => route('admin.resources.index', $resource['slug'])],
            ['label' => 'Detail'],
        ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\App\Support\Admin\AdminRegistry::titleFor($record, $resource)),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($record->created_at ? 'Dibuat ' . $record->created_at->translatedFormat('d F Y, H:i') : null),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
            ['label' => $resource['label'], 'url' => route('admin.resources.index', $resource['slug'])],
            ['label' => 'Detail'],
        ])]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

         <?php $__env->slot('actions', null, []); ?> 
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($statusText): ?>
                <?php if (isset($component)) { $__componentOriginal6506b403466a5cd22db8b21c3bf79bc8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6506b403466a5cd22db8b21c3bf79bc8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-pill','data' => ['variant' => $statusVariant,'label' => $statusText,'pulse' => $statusVariant === 'warning']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-pill'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($statusVariant),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($statusText),'pulse' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($statusVariant === 'warning')]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6506b403466a5cd22db8b21c3bf79bc8)): ?>
<?php $attributes = $__attributesOriginal6506b403466a5cd22db8b21c3bf79bc8; ?>
<?php unset($__attributesOriginal6506b403466a5cd22db8b21c3bf79bc8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6506b403466a5cd22db8b21c3bf79bc8)): ?>
<?php $component = $__componentOriginal6506b403466a5cd22db8b21c3bf79bc8; ?>
<?php unset($__componentOriginal6506b403466a5cd22db8b21c3bf79bc8); ?>
<?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal60a020e5340f3f52bbc4501dc9f93102 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal60a020e5340f3f52bbc4501dc9f93102 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.button','data' => ['variant' => 'secondary','size' => 'sm','icon' => 'chevron-left','href' => route('admin.resources.index', $resource['slug'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'secondary','size' => 'sm','icon' => 'chevron-left','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.resources.index', $resource['slug']))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                Kembali
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal60a020e5340f3f52bbc4501dc9f93102)): ?>
<?php $attributes = $__attributesOriginal60a020e5340f3f52bbc4501dc9f93102; ?>
<?php unset($__attributesOriginal60a020e5340f3f52bbc4501dc9f93102); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal60a020e5340f3f52bbc4501dc9f93102)): ?>
<?php $component = $__componentOriginal60a020e5340f3f52bbc4501dc9f93102; ?>
<?php unset($__componentOriginal60a020e5340f3f52bbc4501dc9f93102); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal60a020e5340f3f52bbc4501dc9f93102 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal60a020e5340f3f52bbc4501dc9f93102 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.button','data' => ['variant' => 'primary','size' => 'sm','icon' => 'edit','href' => route('admin.resources.edit', [$resource['slug'], $record])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'primary','size' => 'sm','icon' => 'edit','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.resources.edit', [$resource['slug'], $record]))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                Edit
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal60a020e5340f3f52bbc4501dc9f93102)): ?>
<?php $attributes = $__attributesOriginal60a020e5340f3f52bbc4501dc9f93102; ?>
<?php unset($__attributesOriginal60a020e5340f3f52bbc4501dc9f93102); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal60a020e5340f3f52bbc4501dc9f93102)): ?>
<?php $component = $__componentOriginal60a020e5340f3f52bbc4501dc9f93102; ?>
<?php unset($__componentOriginal60a020e5340f3f52bbc4501dc9f93102); ?>
<?php endif; ?>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $attributes = $__attributesOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__attributesOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $component = $__componentOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__componentOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        
        <div class="space-y-6 lg:col-span-2">
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($regularFields): ?>
                <div class="stagger-item">
                    <?php if (isset($component)) { $__componentOriginal6c55ae2c9251ebabe977f3f2190280eb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6c55ae2c9251ebabe977f3f2190280eb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.section-card','data' => ['title' => 'Informasi Utama','icon' => 'file-text','subtitle' => count($regularFields) . ' field']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.section-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Informasi Utama','icon' => 'file-text','subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(count($regularFields) . ' field')]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $regularFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php $value = $record->{$field['name']} ?? null; ?>
                                <?php if (isset($component)) { $__componentOriginal4361ff79aecccc7482839d485e57aa57 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4361ff79aecccc7482839d485e57aa57 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.detail-field','data' => ['label' => $field['label'],'icon' => $iconFor($field['name'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.detail-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($field['label']),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconFor($field['name']))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($field['name'] === 'status' && $statusText): ?>
                                        <?php if (isset($component)) { $__componentOriginal6506b403466a5cd22db8b21c3bf79bc8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6506b403466a5cd22db8b21c3bf79bc8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-pill','data' => ['variant' => $statusVariant,'label' => $statusText]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-pill'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($statusVariant),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($statusText)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6506b403466a5cd22db8b21c3bf79bc8)): ?>
<?php $attributes = $__attributesOriginal6506b403466a5cd22db8b21c3bf79bc8; ?>
<?php unset($__attributesOriginal6506b403466a5cd22db8b21c3bf79bc8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6506b403466a5cd22db8b21c3bf79bc8)): ?>
<?php $component = $__componentOriginal6506b403466a5cd22db8b21c3bf79bc8; ?>
<?php unset($__componentOriginal6506b403466a5cd22db8b21c3bf79bc8); ?>
<?php endif; ?>
                                    <?php elseif($field['type'] === 'checkbox'): ?>
                                        <?php echo e($value ? 'Ya' : 'Tidak'); ?>

                                    <?php elseif(in_array($field['name'], ['latitude', 'longitude'], true) && filled($value)): ?>
                                        <span class="font-mono"><?php echo e($value); ?></span>
                                    <?php else: ?>
                                        <?php echo e($format($value)); ?>

                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4361ff79aecccc7482839d485e57aa57)): ?>
<?php $attributes = $__attributesOriginal4361ff79aecccc7482839d485e57aa57; ?>
<?php unset($__attributesOriginal4361ff79aecccc7482839d485e57aa57); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4361ff79aecccc7482839d485e57aa57)): ?>
<?php $component = $__componentOriginal4361ff79aecccc7482839d485e57aa57; ?>
<?php unset($__componentOriginal4361ff79aecccc7482839d485e57aa57); ?>
<?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6c55ae2c9251ebabe977f3f2190280eb)): ?>
<?php $attributes = $__attributesOriginal6c55ae2c9251ebabe977f3f2190280eb; ?>
<?php unset($__attributesOriginal6c55ae2c9251ebabe977f3f2190280eb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6c55ae2c9251ebabe977f3f2190280eb)): ?>
<?php $component = $__componentOriginal6c55ae2c9251ebabe977f3f2190280eb; ?>
<?php unset($__componentOriginal6c55ae2c9251ebabe977f3f2190280eb); ?>
<?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($textareaFieldList): ?>
                <div class="stagger-item">
                    <?php if (isset($component)) { $__componentOriginal6c55ae2c9251ebabe977f3f2190280eb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6c55ae2c9251ebabe977f3f2190280eb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.section-card','data' => ['title' => 'Deskripsi & Catatan','icon' => 'message']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.section-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Deskripsi & Catatan','icon' => 'message']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        <div class="space-y-5">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $textareaFieldList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php $value = $record->{$field['name']} ?? null; ?>
                                <div>
                                    <p class="mb-1.5 text-caption font-semibold uppercase tracking-[0.08em] text-slate-500"><?php echo e($field['label']); ?></p>
                                    <p class="whitespace-pre-line text-sm leading-relaxed text-ink-700"><?php echo e(filled($value) ? $value : '-'); ?></p>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6c55ae2c9251ebabe977f3f2190280eb)): ?>
<?php $attributes = $__attributesOriginal6c55ae2c9251ebabe977f3f2190280eb; ?>
<?php unset($__attributesOriginal6c55ae2c9251ebabe977f3f2190280eb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6c55ae2c9251ebabe977f3f2190280eb)): ?>
<?php $component = $__componentOriginal6c55ae2c9251ebabe977f3f2190280eb; ?>
<?php unset($__componentOriginal6c55ae2c9251ebabe977f3f2190280eb); ?>
<?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($fileFieldList): ?>
                <?php $fileFieldsWithData = collect($fileFieldList)->filter(fn ($field) => filled($record->{$field['name']} ?? null))->values(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($fileFieldsWithData->isNotEmpty()): ?>
                    <div class="stagger-item">
                        <?php if (isset($component)) { $__componentOriginal6c55ae2c9251ebabe977f3f2190280eb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6c55ae2c9251ebabe977f3f2190280eb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.section-card','data' => ['title' => 'Dokumen & Lampiran','icon' => 'download','subtitle' => $fileFieldsWithData->count() . ' file']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.section-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Dokumen & Lampiran','icon' => 'download','subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fileFieldsWithData->count() . ' file')]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                            <div class="space-y-3">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $fileFieldsWithData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <?php
                                        $docPath = $record->{$field['name']} ?? null;
                                        $docExt = $docPath ? pathinfo($docPath, PATHINFO_EXTENSION) : '';
                                        $docName = $docExt ? $field['label'].'.'.$docExt : $field['label'];
                                    ?>
                                    <?php if (isset($component)) { $__componentOriginala0b46b010867f32683bc0307839ab73f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala0b46b010867f32683bc0307839ab73f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.file-preview','data' => ['label' => $field['label'],'path' => $docPath,'downloadName' => $docName]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.file-preview'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($field['label']),'path' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($docPath),'downloadName' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($docName)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala0b46b010867f32683bc0307839ab73f)): ?>
<?php $attributes = $__attributesOriginala0b46b010867f32683bc0307839ab73f; ?>
<?php unset($__attributesOriginala0b46b010867f32683bc0307839ab73f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala0b46b010867f32683bc0307839ab73f)): ?>
<?php $component = $__componentOriginala0b46b010867f32683bc0307839ab73f; ?>
<?php unset($__componentOriginala0b46b010867f32683bc0307839ab73f); ?>
<?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6c55ae2c9251ebabe977f3f2190280eb)): ?>
<?php $attributes = $__attributesOriginal6c55ae2c9251ebabe977f3f2190280eb; ?>
<?php unset($__attributesOriginal6c55ae2c9251ebabe977f3f2190280eb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6c55ae2c9251ebabe977f3f2190280eb)): ?>
<?php $component = $__componentOriginal6c55ae2c9251ebabe977f3f2190280eb; ?>
<?php unset($__componentOriginal6c55ae2c9251ebabe977f3f2190280eb); ?>
<?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($fotos)): ?>
                <div class="stagger-item">
                    <?php if (isset($component)) { $__componentOriginal6c55ae2c9251ebabe977f3f2190280eb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6c55ae2c9251ebabe977f3f2190280eb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.section-card','data' => ['title' => 'Foto Bukti','icon' => 'eye','subtitle' => count($fotos) . ' foto']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.section-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Foto Bukti','icon' => 'eye','subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(count($fotos) . ' foto')]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        <?php if (isset($component)) { $__componentOriginal8146b52c98dce9aec45c865760c68dc6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8146b52c98dce9aec45c865760c68dc6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lightbox','data' => ['images' => $fotos,'columns' => 3]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lightbox'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['images' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fotos),'columns' => 3]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8146b52c98dce9aec45c865760c68dc6)): ?>
<?php $attributes = $__attributesOriginal8146b52c98dce9aec45c865760c68dc6; ?>
<?php unset($__attributesOriginal8146b52c98dce9aec45c865760c68dc6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8146b52c98dce9aec45c865760c68dc6)): ?>
<?php $component = $__componentOriginal8146b52c98dce9aec45c865760c68dc6; ?>
<?php unset($__componentOriginal8146b52c98dce9aec45c865760c68dc6); ?>
<?php endif; ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6c55ae2c9251ebabe977f3f2190280eb)): ?>
<?php $attributes = $__attributesOriginal6c55ae2c9251ebabe977f3f2190280eb; ?>
<?php unset($__attributesOriginal6c55ae2c9251ebabe977f3f2190280eb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6c55ae2c9251ebabe977f3f2190280eb)): ?>
<?php $component = $__componentOriginal6c55ae2c9251ebabe977f3f2190280eb; ?>
<?php unset($__componentOriginal6c55ae2c9251ebabe977f3f2190280eb); ?>
<?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        </div>

        
        <div class="space-y-6">
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasMap): ?>
                <div class="stagger-item">
                    <?php if (isset($component)) { $__componentOriginal6c55ae2c9251ebabe977f3f2190280eb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6c55ae2c9251ebabe977f3f2190280eb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.section-card','data' => ['title' => 'Lokasi Kejadian','icon' => 'map-pin']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.section-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Lokasi Kejadian','icon' => 'map-pin']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        <div id="admin-pengendalian-map" style="height:280px" class="w-full overflow-hidden rounded-lg border border-slate-200 bg-slate-100"></div>
                        <div class="mt-3 grid grid-cols-2 gap-3 text-xs">
                            <div class="rounded-lg bg-slate-50 px-3 py-2">
                                <p class="font-semibold uppercase tracking-wide text-slate-400">Latitude</p>
                                <p class="mt-0.5 font-mono font-semibold text-ink-800"><?php echo e($mapLat); ?></p>
                            </div>
                            <div class="rounded-lg bg-slate-50 px-3 py-2">
                                <p class="font-semibold uppercase tracking-wide text-slate-400">Longitude</p>
                                <p class="mt-0.5 font-mono font-semibold text-ink-800"><?php echo e($mapLng); ?></p>
                            </div>
                        </div>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6c55ae2c9251ebabe977f3f2190280eb)): ?>
<?php $attributes = $__attributesOriginal6c55ae2c9251ebabe977f3f2190280eb; ?>
<?php unset($__attributesOriginal6c55ae2c9251ebabe977f3f2190280eb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6c55ae2c9251ebabe977f3f2190280eb)): ?>
<?php $component = $__componentOriginal6c55ae2c9251ebabe977f3f2190280eb; ?>
<?php unset($__componentOriginal6c55ae2c9251ebabe977f3f2190280eb); ?>
<?php endif; ?>
                </div>
                <?php $__env->startPush('scripts'); ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        window.ensureMaplibreLoaded(function () {
                            var map = new maplibregl.Map({
                                container: 'admin-pengendalian-map',
                                style: 'https://basemaps.cartocdn.com/gl/voyager-gl-style/style.json',
                                center: [<?php echo e($mapLng); ?>, <?php echo e($mapLat); ?>],
                                zoom: 15,
                                attributionControl: false
                            });
                            map.addControl(new DlhZoomControl(), 'top-left');

                            if (window.DlhWeatherControl) map.addControl(new DlhWeatherControl(), 'top-right');
                            if (window.DlhBasemapSwitcher) map.addControl(new DlhBasemapSwitcher(), 'bottom-right');
                            if (window.dlhAddLocBtn) dlhAddLocBtn(map);
                            var el = document.createElement('div');
                            el.style.cssText = 'width:30px;height:30px;border-radius:50%;background:#10b981;color:#fff;box-shadow:0 4px 12px rgba(16,185,129,.5);border:2px solid #fff;display:grid;place-items:center;cursor:pointer';
                            el.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>';
                            new maplibregl.Marker({ element: el, anchor: 'center' })
                                .setLngLat([<?php echo e($mapLng); ?>, <?php echo e($mapLat); ?>])
                                .setPopup(new maplibregl.Popup({ offset: [0, -20] }).setText('Lokasi kejadian'))
                                .addTo(map);
                            setTimeout(function () { map.resize(); }, 200);
                        });
                    });
                </script>
                <?php $__env->stopPush(); ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <div class="stagger-item">
                <?php if (isset($component)) { $__componentOriginal6c55ae2c9251ebabe977f3f2190280eb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6c55ae2c9251ebabe977f3f2190280eb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.section-card','data' => ['title' => 'Informasi Sistem','icon' => 'clock']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.section-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Informasi Sistem','icon' => 'clock']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <div class="space-y-4">
                        <?php if (isset($component)) { $__componentOriginal4361ff79aecccc7482839d485e57aa57 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4361ff79aecccc7482839d485e57aa57 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.detail-field','data' => ['label' => 'Dibuat','icon' => 'calendar','value' => $record->created_at?->translatedFormat('d F Y, H:i')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.detail-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Dibuat','icon' => 'calendar','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($record->created_at?->translatedFormat('d F Y, H:i'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4361ff79aecccc7482839d485e57aa57)): ?>
<?php $attributes = $__attributesOriginal4361ff79aecccc7482839d485e57aa57; ?>
<?php unset($__attributesOriginal4361ff79aecccc7482839d485e57aa57); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4361ff79aecccc7482839d485e57aa57)): ?>
<?php $component = $__componentOriginal4361ff79aecccc7482839d485e57aa57; ?>
<?php unset($__componentOriginal4361ff79aecccc7482839d485e57aa57); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal4361ff79aecccc7482839d485e57aa57 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4361ff79aecccc7482839d485e57aa57 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.detail-field','data' => ['label' => 'Diperbarui','icon' => 'clock','value' => $record->updated_at?->translatedFormat('d F Y, H:i')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.detail-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Diperbarui','icon' => 'clock','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($record->updated_at?->translatedFormat('d F Y, H:i'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4361ff79aecccc7482839d485e57aa57)): ?>
<?php $attributes = $__attributesOriginal4361ff79aecccc7482839d485e57aa57; ?>
<?php unset($__attributesOriginal4361ff79aecccc7482839d485e57aa57); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4361ff79aecccc7482839d485e57aa57)): ?>
<?php $component = $__componentOriginal4361ff79aecccc7482839d485e57aa57; ?>
<?php unset($__componentOriginal4361ff79aecccc7482839d485e57aa57); ?>
<?php endif; ?>
                    </div>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6c55ae2c9251ebabe977f3f2190280eb)): ?>
<?php $attributes = $__attributesOriginal6c55ae2c9251ebabe977f3f2190280eb; ?>
<?php unset($__attributesOriginal6c55ae2c9251ebabe977f3f2190280eb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6c55ae2c9251ebabe977f3f2190280eb)): ?>
<?php $component = $__componentOriginal6c55ae2c9251ebabe977f3f2190280eb; ?>
<?php unset($__componentOriginal6c55ae2c9251ebabe977f3f2190280eb); ?>
<?php endif; ?>
            </div>

            
            <div class="stagger-item rounded-xl border border-danger-200 bg-danger-50/50 p-5">
                <div class="flex items-start gap-3">
                    <div class="grid size-10 shrink-0 place-items-center rounded-lg bg-danger-100 text-danger-600">
                        <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'trash','size' => 20]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'trash','size' => 20]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-ink-900">Hapus Data</p>
                        <p class="mt-0.5 text-xs text-slate-500">Tindakan ini permanen dan tidak dapat dibatalkan.</p>
                        <div class="mt-3">
                            <?php if (isset($component)) { $__componentOriginal60a020e5340f3f52bbc4501dc9f93102 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal60a020e5340f3f52bbc4501dc9f93102 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.button','data' => ['variant' => 'danger','size' => 'sm','icon' => 'trash','xData' => '','xOn:click' => '$dispatch(\'open-modal\', \'show-delete\')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'danger','size' => 'sm','icon' => 'trash','x-data' => '','x-on:click' => '$dispatch(\'open-modal\', \'show-delete\')']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                                Hapus Data
                             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal60a020e5340f3f52bbc4501dc9f93102)): ?>
<?php $attributes = $__attributesOriginal60a020e5340f3f52bbc4501dc9f93102; ?>
<?php unset($__attributesOriginal60a020e5340f3f52bbc4501dc9f93102); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal60a020e5340f3f52bbc4501dc9f93102)): ?>
<?php $component = $__componentOriginal60a020e5340f3f52bbc4501dc9f93102; ?>
<?php unset($__componentOriginal60a020e5340f3f52bbc4501dc9f93102); ?>
<?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (isset($component)) { $__componentOriginal0fb102436b9e8819b632c430c5eb68fb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0fb102436b9e8819b632c430c5eb68fb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.confirm-delete','data' => ['name' => 'show-delete','action' => route('admin.resources.destroy', [$resource['slug'], $record]),'title' => 'Hapus Pengaduan','message' => 'Data pengaduan ' . (\App\Support\Admin\AdminRegistry::titleFor($record, $resource)) . ' akan dihapus permanen. Aksi ini tidak bisa dibatalkan.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.confirm-delete'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'show-delete','action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.resources.destroy', [$resource['slug'], $record])),'title' => 'Hapus Pengaduan','message' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Data pengaduan ' . (\App\Support\Admin\AdminRegistry::titleFor($record, $resource)) . ' akan dihapus permanen. Aksi ini tidak bisa dibatalkan.')]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0fb102436b9e8819b632c430c5eb68fb)): ?>
<?php $attributes = $__attributesOriginal0fb102436b9e8819b632c430c5eb68fb; ?>
<?php unset($__attributesOriginal0fb102436b9e8819b632c430c5eb68fb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0fb102436b9e8819b632c430c5eb68fb)): ?>
<?php $component = $__componentOriginal0fb102436b9e8819b632c430c5eb68fb; ?>
<?php unset($__componentOriginal0fb102436b9e8819b632c430c5eb68fb); ?>
<?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\DLH - PALU\resources\views/admin/pengendalian/show.blade.php ENDPATH**/ ?>