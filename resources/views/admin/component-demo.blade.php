@extends('layouts.admin')

@section('title', 'Component Demo - Form Components')
@section('heading', 'Component Demo')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        {{-- ═══════════════════════════════════════════════════════════
             Redesign showcase (Fase 1) — komponen baru
             ═══════════════════════════════════════════════════════════ --}}
        <x-admin.page-header
            title="Component Library — Redesign"
            subtitle="Showcase komponen baru Fase 1: modal, toast, dropzone, lightbox, map-picker, dll."
            icon="dashboard"
            :breadcrumbs="[['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Component Demo']]"
        >
            <x-slot:actions>
                <x-admin.button variant="secondary" size="sm" icon="refresh" onclick="window.showToast('Toast dari tombol!', 'success')">
                    Test Toast
                </x-admin.button>
            </x-slot:actions>
        </x-admin.page-header>

        {{-- Buttons --}}
        <x-admin.section-card :number="1" title="Buttons" subtitle="Variants & loading state">
            <div class="flex flex-wrap items-center gap-3">
                <x-admin.button variant="primary" icon="plus">Primary</x-admin.button>
                <x-admin.button variant="secondary" icon="edit">Secondary</x-admin.button>
                <x-admin.button variant="subtle" icon="eye">Subtle</x-admin.button>
                <x-admin.button variant="ghost" icon="settings">Ghost</x-admin.button>
                <x-admin.button variant="danger" icon="trash">Danger</x-admin.button>
                <x-admin.button variant="primary" :loading="true" loadingText="Menyimpan...">Loading</x-admin.button>
            </div>
            <div class="mt-3 flex flex-wrap items-center gap-3">
                <x-admin.button variant="primary" size="sm">Small</x-admin.button>
                <x-admin.button variant="primary" size="md">Medium</x-admin.button>
                <x-admin.button variant="primary" size="lg">Large</x-admin.button>
            </div>
        </x-admin.section-card>

        {{-- Status pills & avatars --}}
        <x-admin.section-card :number="2" title="Status Pill · Avatar" subtitle="Indikator status & identitas">
            <div class="flex flex-wrap items-center gap-3">
                <x-admin.status-pill variant="success" label="Ditinjau" />
                <x-admin.status-pill variant="warning" label="Belum Ditinjau" :pulse="true" />
                <x-admin.status-pill variant="danger" label="Ditolak" />
                <x-admin.status-pill variant="info" label="Proses" :pulse="true" />
                <x-admin.status-pill variant="neutral" label="Arsip" />
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-3">
                <x-admin.avatar name="Budi Santoso" size="sm" />
                <x-admin.avatar name="Siti Aminah" size="md" />
                <x-admin.avatar name="Ahmad Dahlan" size="lg" />
                <x-admin.avatar name="Rina Wati" size="md" />
            </div>
        </x-admin.section-card>

        {{-- Count-up --}}
        <x-admin.section-card :number="3" title="Count Up" subtitle="Animasi angka rAF, respect reduced-motion">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-xl bg-brand-50 p-4">
                    <p class="text-3xl font-extrabold text-brand-700"><x-admin.count-up :value="1284" /></p>
                    <p class="text-xs font-semibold text-slate-500">Total Pengaduan</p>
                </div>
                <div class="rounded-xl bg-info-50 p-4">
                    <p class="text-3xl font-extrabold text-info-700"><x-admin.count-up :value="342" /></p>
                    <p class="text-xs font-semibold text-slate-500">Ditinjau</p>
                </div>
                <div class="rounded-xl bg-warning-50 p-4">
                    <p class="text-3xl font-extrabold text-warning-700"><x-admin.count-up :value="87" /></p>
                    <p class="text-xs font-semibold text-slate-500">Menunggu</p>
                </div>
                <div class="rounded-xl bg-clay-50 p-4">
                    <p class="text-3xl font-extrabold text-clay-700"><x-admin.count-up :value="1200000" prefix="Rp " /></p>
                    <p class="text-xs font-semibold text-slate-500">Anggaran</p>
                </div>
            </div>
        </x-admin.section-card>

        {{-- Form input --}}
        <x-admin.section-card :number="4" title="Form Input" subtitle="Floating label, icon prefix, error shake">
            <div class="grid gap-5 sm:grid-cols-2">
                <x-admin.form-input name="demo_nama" label="Nama Lengkap" icon="user" hint="Isi sesuai KTP" />
                <x-admin.form-input name="demo_email" type="email" label="Email" icon="mail" />
                <x-admin.form-input name="demo_hp" label="Nomor HP" icon="message" suffix="ID" />
                <x-admin.form-input name="demo_err" label="Field Error" icon="alert-circle" error="Contoh pesan error (shake)" />
            </div>
        </x-admin.section-card>

        {{-- Modal + confirm delete --}}
        <x-admin.section-card :number="5" title="Modal · Confirm Delete" subtitle="Ganti confirm() native, focus trap, ESC">
            <div class="flex flex-wrap gap-3">
                <x-admin.button variant="secondary" icon="eye" x-data="" x-on:click="$dispatch('open-modal', 'demo-modal')">
                    Buka Modal
                </x-admin.button>
                <x-admin.button variant="danger" icon="trash" x-data="" x-on:click="$dispatch('open-modal', 'demo-delete')">
                    Hapus (Confirm)
                </x-admin.button>
            </div>

            <x-admin.modal name="demo-modal" title="Contoh Modal" icon="info-circle" max-width="lg">
                <p>Modal Alpine dengan transisi scale+fade, focus trap, ESC untuk tutup, dan klik backdrop.</p>
                <x-slot:footer>
                    <x-admin.button variant="ghost" x-on:click="closeModal()">Tutup</x-admin.button>
                    <x-admin.button variant="primary" x-on:click="closeModal(); window.showToast('Tersimpan!', 'success')">Simpan</x-admin.button>
                </x-slot:footer>
            </x-admin.modal>

            <x-admin.confirm-delete
                name="demo-delete"
                action="#"
                title="Hapus Data Demo"
                message="Data demo ini akan dihapus permanen. Aksi ini tidak bisa dibatalkan."
            />
        </x-admin.section-card>

        {{-- Dropzone --}}
        <x-admin.section-card :number="6" title="Dropzone" subtitle="Drag-drop, preview grid, hapus per-foto (input photos[])">
            <x-admin.dropzone name="photos" :max="5" :max-size-mb="2" hint="Seret foto ke area atau klik untuk memilih." />
        </x-admin.section-card>

        {{-- Lightbox --}}
        <x-admin.section-card :number="7" title="Lightbox" subtitle="Galeri klik-zoom, keyboard ←/→/ESC">
            <x-admin.lightbox :images="[
                'https://picsum.photos/id/1015/600/600',
                'https://picsum.photos/id/1016/600/600',
                'https://picsum.photos/id/1018/600/600',
                'https://picsum.photos/id/1020/600/600',
            ]" />
        </x-admin.section-card>

        {{-- Map picker --}}
        <x-admin.section-card :number="8" title="Map Picker" subtitle="MapLibre click→isi lat/lng (reuse ensureMaplibreLoaded)">
            <x-admin.map-picker lat-input="demo_lat" lng-input="demo_lng" />
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <x-admin.form-input id="demo_lat" name="demo_lat" label="Latitude" type="number" step="0.000001" />
                <x-admin.form-input id="demo_lng" name="demo_lng" label="Longitude" type="number" step="0.000001" />
            </div>
        </x-admin.section-card>

        {{-- Skeleton --}}
        <x-admin.section-card :number="9" title="Skeleton" subtitle="Loading placeholder komposit">
            <x-admin.skeleton-detail />
        </x-admin.section-card>

        {{-- ═══ Divider: showcase form lama ═══ --}}
        <div class="flex items-center gap-3 pt-2">
            <div class="h-px flex-1 bg-slate-200"></div>
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Komponen Form (lama)</span>
            <div class="h-px flex-1 bg-slate-200"></div>
        </div>

        <!-- Demo Card -->
        <x-admin.card>
            <div class="mb-6">
                <h2 class="text-2xl font-extrabold text-slate-900">Form Components</h2>
                <p class="mt-2 text-sm text-slate-600">
                    Collection component form yang modern, reusable, dan konsisten untuk seluruh aplikasi admin.
                </p>
            </div>

            <form method="POST" action="#" class="space-y-8">
                @csrf

                <!-- Text Input Component -->
                <x-admin.form-section 
                    title="Text Input Component" 
                    icon="edit"
                    description="Input field untuk teks, email, number, dll"
                >
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-admin.input
                            name="nama"
                            type="text"
                            label="Nama Lengkap"
                            placeholder="Masukkan nama lengkap"
                            :required="true"
                            hint="Nama sesuai KTP"
                        />
                        
                        <x-admin.input
                            name="email"
                            type="email"
                            label="Email"
                            placeholder="nama@email.com"
                            icon="mail"
                            :required="true"
                        />
                    </div>
                    
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-admin.input
                            name="telepon"
                            type="tel"
                            label="Nomor Telepon"
                            placeholder="08123456789"
                            icon="phone"
                            iconPosition="left"
                        />
                        
                        <x-admin.input
                            name="website"
                            type="url"
                            label="Website"
                            placeholder="website.com"
                            prefix="https://"
                        />
                    </div>
                </x-admin.form-section>

                <!-- Textarea Component -->
                <x-admin.form-section 
                    title="Textarea Component" 
                    icon="file-text"
                    description="Text area untuk input teks panjang"
                >
                    <x-admin.textarea
                        name="lokasi_kejadian"
                        label="Lokasi Kejadian"
                        placeholder="Jelaskan lokasi kejadian dengan detail..."
                        :rows="3"
                        :required="true"
                        hint="Sertakan nama jalan, kelurahan, dan patokan"
                    />
                    
                    <x-admin.textarea
                        name="deskripsi"
                        label="Deskripsi Pengaduan"
                        placeholder="Jelaskan kronologi kejadian secara detail..."
                        :rows="5"
                        :required="true"
                        :maxlength="500"
                        :showCharCount="true"
                        hint="Jelaskan dengan detail agar petugas dapat menindaklanjuti dengan tepat"
                    />
                    
                    <x-admin.textarea
                        name="catatan"
                        label="Catatan Tambahan"
                        placeholder="Catatan atau informasi tambahan (opsional)"
                        :rows="3"
                    />
                </x-admin.form-section>

                <!-- Dropdown Select Component -->
                <x-admin.form-section 
                    title="Dropdown Select Component" 
                    icon="layers"
                    description="Dropdown untuk memilih opsi"
                >
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-admin.select
                            name="jenis_pengaduan"
                            label="Jenis Pengaduan"
                            placeholder="Pilih Jenis Pengaduan"
                            :options="[
                                'pembakaran-sampah' => 'Pembakaran Sampah',
                                'limbah-b3' => 'Limbah B3',
                                'banjir' => 'Banjir',
                                'longsor' => 'Longsor',
                                'pencemaran-air' => 'Pencemaran Air',
                            ]"
                            :required="true"
                        />
                        
                        <x-admin.select
                            name="jenis_usaha"
                            label="Jenis Usaha"
                            placeholder="Pilih Jenis Usaha"
                            :options="\App\Models\JenisUsaha::pluck('nama', 'id')"
                            :searchable="true"
                            hint="Ketik untuk mencari"
                        />
                    </div>
                </x-admin.form-section>

                <!-- Combined Form Example -->
                <x-admin.form-section 
                    title="Form Lengkap - Contoh Pengaduan" 
                    icon="alert-circle"
                    description="Contoh form lengkap dengan berbagai component"
                >
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-admin.input
                            name="nama_pelapor"
                            label="Nama Pelapor"
                            placeholder="Nama lengkap pelapor"
                            icon="user"
                            :required="true"
                        />
                        
                        <x-admin.input
                            name="nomor_hp"
                            label="Nomor HP"
                            placeholder="08xxxxxxxxxx"
                            icon="phone"
                            :required="true"
                        />
                    </div>
                    
                    <x-admin.select
                        name="kategori"
                        label="Kategori Pengaduan"
                        placeholder="Pilih kategori"
                        :options="[
                            'sampah' => 'Persampahan',
                            'air' => 'Pencemaran Air',
                            'udara' => 'Pencemaran Udara',
                            'tanah' => 'Pencemaran Tanah',
                        ]"
                        :required="true"
                    />
                    
                    <x-admin.textarea
                        name="alamat_lengkap"
                        label="Alamat Lengkap Kejadian"
                        placeholder="Masukkan alamat lengkap lokasi kejadian..."
                        :rows="3"
                        :required="true"
                    />
                    
                    <x-admin.textarea
                        name="detail_pengaduan"
                        label="Detail Pengaduan"
                        placeholder="Jelaskan detail pengaduan Anda..."
                        :rows="5"
                        :required="true"
                        :maxlength="1000"
                        :showCharCount="true"
                    />
                </x-admin.form-section>

                <!-- Error States Example -->
                <x-admin.form-section 
                    title="Error States" 
                    icon="alert-triangle"
                    description="Contoh component dengan error validation"
                >
                    <x-admin.input
                        name="input_error"
                        label="Input dengan Error"
                        placeholder="Field dengan error"
                        :required="true"
                        error="Field ini wajib diisi"
                    />
                    
                    <x-admin.select
                        name="select_error"
                        label="Dropdown dengan Error"
                        placeholder="Pilih opsi"
                        :options="['a' => 'Option A', 'b' => 'Option B']"
                        :required="true"
                        error="Pilih salah satu opsi"
                    />
                    
                    <x-admin.textarea
                        name="textarea_error"
                        label="Textarea dengan Error"
                        placeholder="Field dengan error"
                        :rows="3"
                        :required="true"
                        error="Deskripsi minimal 50 karakter"
                    />
                </x-admin.form-section>

                <!-- Disabled & Readonly States -->
                <x-admin.form-section 
                    title="Disabled & Readonly States" 
                    icon="lock"
                    description="Contoh field yang tidak bisa diubah"
                >
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-admin.input
                            name="disabled_input"
                            label="Input Disabled"
                            value="Nilai tidak bisa diubah"
                            :disabled="true"
                        />
                        
                        <x-admin.input
                            name="readonly_input"
                            label="Input Readonly"
                            value="Hanya bisa dibaca"
                            :readonly="true"
                        />
                    </div>
                    
                    <x-admin.textarea
                        name="readonly_textarea"
                        label="Textarea Readonly"
                        value="Konten ini hanya bisa dibaca, tidak bisa diubah oleh user."
                        :rows="3"
                        :readonly="true"
                    />
                </x-admin.form-section>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-3 border-t border-slate-200 pt-6">
                    <a 
                        href="{{ route('admin.dashboard') }}" 
                        class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        Kembali
                    </a>
                    <button 
                        type="submit"
                        class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700"
                    >
                        Simpan Data
                    </button>
                </div>
            </form>
        </x-admin.card>

        <!-- Usage Guide -->
        <x-admin.card>
            <h3 class="mb-4 text-lg font-bold text-slate-900">📖 Dokumentasi Penggunaan</h3>
            
            <div class="space-y-6 text-sm">
                <!-- Input Component -->
                <div>
                    <h4 class="mb-3 flex items-center gap-2 font-semibold text-slate-900">
                        <span class="grid size-8 place-items-center rounded-lg bg-blue-100 text-blue-600">
                            <x-admin.icon name="edit" :size="16" />
                        </span>
                        Input Component
                    </h4>
                    <pre class="overflow-x-auto rounded-lg bg-slate-900 p-4 text-slate-100"><code>&lt;x-admin.input
    name="nama"
    type="text"
    label="Nama Lengkap"
    placeholder="Masukkan nama"
    icon="user"
    :required="true"
    hint="Nama sesuai KTP"
/&gt;</code></pre>
                    <div class="mt-3">
                        <p class="mb-2 font-medium text-slate-700">Props:</p>
                        <ul class="ml-4 list-inside list-disc space-y-1 text-slate-600">
                            <li><code class="rounded bg-slate-100 px-1.5 py-0.5">name</code> - Nama field (required)</li>
                            <li><code class="rounded bg-slate-100 px-1.5 py-0.5">type</code> - Type input (text, email, number, tel, url, date, dll)</li>
                            <li><code class="rounded bg-slate-100 px-1.5 py-0.5">label</code> - Label field</li>
                            <li><code class="rounded bg-slate-100 px-1.5 py-0.5">placeholder</code> - Text placeholder</li>
                            <li><code class="rounded bg-slate-100 px-1.5 py-0.5">icon</code> - Icon name (opsional)</li>
                            <li><code class="rounded bg-slate-100 px-1.5 py-0.5">iconPosition</code> - Position icon: left/right</li>
                            <li><code class="rounded bg-slate-100 px-1.5 py-0.5">prefix</code> - Text prefix (contoh: "Rp", "https://")</li>
                            <li><code class="rounded bg-slate-100 px-1.5 py-0.5">suffix</code> - Text suffix (contoh: "kg", "%")</li>
                            <li><code class="rounded bg-slate-100 px-1.5 py-0.5">required</code> - Boolean wajib diisi</li>
                            <li><code class="rounded bg-slate-100 px-1.5 py-0.5">disabled</code> - Boolean disabled</li>
                            <li><code class="rounded bg-slate-100 px-1.5 py-0.5">readonly</code> - Boolean readonly</li>
                            <li><code class="rounded bg-slate-100 px-1.5 py-0.5">error</code> - Pesan error</li>
                            <li><code class="rounded bg-slate-100 px-1.5 py-0.5">hint</code> - Text bantuan</li>
                        </ul>
                    </div>
                </div>

                <!-- Textarea Component -->
                <div class="border-t border-slate-200 pt-6">
                    <h4 class="mb-3 flex items-center gap-2 font-semibold text-slate-900">
                        <span class="grid size-8 place-items-center rounded-lg bg-emerald-100 text-emerald-600">
                            <x-admin.icon name="file-text" :size="16" />
                        </span>
                        Textarea Component
                    </h4>
                    <pre class="overflow-x-auto rounded-lg bg-slate-900 p-4 text-slate-100"><code>&lt;x-admin.textarea
    name="deskripsi"
    label="Deskripsi"
    placeholder="Jelaskan detail..."
    :rows="5"
    :maxlength="500"
    :showCharCount="true"
    :required="true"
/&gt;</code></pre>
                    <div class="mt-3">
                        <p class="mb-2 font-medium text-slate-700">Props:</p>
                        <ul class="ml-4 list-inside list-disc space-y-1 text-slate-600">
                            <li><code class="rounded bg-slate-100 px-1.5 py-0.5">name</code> - Nama field (required)</li>
                            <li><code class="rounded bg-slate-100 px-1.5 py-0.5">label</code> - Label field</li>
                            <li><code class="rounded bg-slate-100 px-1.5 py-0.5">placeholder</code> - Text placeholder</li>
                            <li><code class="rounded bg-slate-100 px-1.5 py-0.5">rows</code> - Jumlah baris (default: 4)</li>
                            <li><code class="rounded bg-slate-100 px-1.5 py-0.5">maxlength</code> - Maksimal karakter</li>
                            <li><code class="rounded bg-slate-100 px-1.5 py-0.5">showCharCount</code> - Boolean tampilkan counter</li>
                            <li><code class="rounded bg-slate-100 px-1.5 py-0.5">resizable</code> - Boolean bisa di-resize (default: true)</li>
                            <li><code class="rounded bg-slate-100 px-1.5 py-0.5">required</code> - Boolean wajib diisi</li>
                            <li><code class="rounded bg-slate-100 px-1.5 py-0.5">disabled</code> - Boolean disabled</li>
                            <li><code class="rounded bg-slate-100 px-1.5 py-0.5">readonly</code> - Boolean readonly</li>
                            <li><code class="rounded bg-slate-100 px-1.5 py-0.5">error</code> - Pesan error</li>
                            <li><code class="rounded bg-slate-100 px-1.5 py-0.5">hint</code> - Text bantuan</li>
                        </ul>
                    </div>
                </div>

                <!-- Select Component -->
                <div class="border-t border-slate-200 pt-6">
                    <h4 class="mb-3 flex items-center gap-2 font-semibold text-slate-900">
                        <span class="grid size-8 place-items-center rounded-lg bg-purple-100 text-purple-600">
                            <x-admin.icon name="layers" :size="16" />
                        </span>
                        Select Component
                    </h4>
                    <pre class="overflow-x-auto rounded-lg bg-slate-900 p-4 text-slate-100"><code>&lt;x-admin.select
    name="kategori"
    label="Kategori"
    placeholder="Pilih kategori"
    :options="['a' => 'Option A', 'b' => 'Option B']"
    :searchable="true"
    :required="true"
/&gt;</code></pre>
                    <div class="mt-3">
                        <p class="mb-2 font-medium text-slate-700">Props: (Lihat dokumentasi lengkap di atas)</p>
                    </div>
                </div>

                <!-- Tips -->
                <div class="rounded-lg border-2 border-emerald-200 bg-emerald-50 p-4">
                    <h4 class="mb-2 flex items-center gap-2 font-semibold text-emerald-900">
                        <x-admin.icon name="lightbulb" :size="18" />
                        Tips Penggunaan
                    </h4>
                    <ul class="ml-4 list-inside list-disc space-y-1 text-sm text-emerald-800">
                        <li>Gunakan <code class="rounded bg-emerald-100 px-1.5 py-0.5">:required="true"</code> untuk field wajib diisi</li>
                        <li>Tambahkan <code class="rounded bg-emerald-100 px-1.5 py-0.5">hint</code> untuk memberikan informasi tambahan</li>
                        <li>Untuk textarea panjang, gunakan <code class="rounded bg-emerald-100 px-1.5 py-0.5">:showCharCount="true"</code> dengan maxlength</li>
                        <li>Icon akan membuat input lebih visual dan mudah dipahami</li>
                        <li>Gunakan <code class="rounded bg-emerald-100 px-1.5 py-0.5">:searchable="true"</code> untuk dropdown dengan banyak opsi</li>
                        <li>Kombinasikan dengan <code class="rounded bg-emerald-100 px-1.5 py-0.5">x-admin.form-section</code> untuk grouping yang rapi</li>
                    </ul>
                </div>
            </div>
        </x-admin.card>
    </div>
@endsection
