@extends('layouts.app')

@section('title', 'Tata Lingkungan - DLH Kota Palu')
@section('description', 'Dokumen kajian lingkungan Dinas Lingkungan Hidup Kota Palu: KLHS, RPPLH, IKPLHD, daya dukung daya tampung, pemantauan lingkungan, persampahan, RTH, dan dokumen lainnya - dapat dipratinjau dan diunduh secara langsung.')

@section('content')
<div class="tl-page-shell space-y-8 tl-wrap">
    <x-public.page-hero
        badge="{{ __('Bidang Tata Penataan') }}"
        icon="leaf"
        title="{{ __('Tata Lingkungan') }}"
        description="{{ __('Kumpulan dokumen kajian lingkungan hidup Kota Palu yang tersinkron otomatis dengan Google Drive DLH. Pilih folder dan file untuk membaca dokumen secara langsung.') }}"
    />

    {{-- Penjelajah dokumen --}}
    <div x-data="tataLingkunganExplorer()" class="tl-explorer reveal">
        <div class="tl-accent" aria-hidden="true"></div>

        {{-- ── Error state ── --}}
        <section x-show="error" x-cloak class="tl-state-card" x-transition.opacity>
            <span class="tl-state-icon tl-state-icon--error">
                <x-icons.ui name="alert" />
            </span>
            <h3 class="tl-state-title">{{ __('Dokumen Tidak Dapat Dimuat') }}</h3>
            <p class="tl-state-desc" x-text="error"></p>
            <button @click="retry()" class="tl-btn tl-btn--primary">
                <x-icons.ui name="refresh" class="size-4" />
                {{ __('Coba Lagi') }}
            </button>
        </section>

        <div x-show="!error">
            {{-- ── Header: judul + breadcrumb + jumlah + sinkron ── --}}
                <section class="tl-head">
                    <div class="tl-head-left">
                        <span class="tl-head-logo">
                            <x-icons.ui name="document" />
                        </span>
                        <div class="min-w-0">
                            <h2 class="tl-head-title">{{ __('Dokumen Tata Lingkungan') }}</h2>
                            <nav class="tl-crumbs" aria-label="Lokasi folder">
                                <template x-for="(seg, idx) in crumbs" :key="seg.id">
                                    <span class="tl-crumb-group">
                                        <template x-if="idx < crumbs.length - 1">
                                            <button @click="selectFolder(seg.id)" class="tl-crumb" x-text="seg.name"></button>
                                        </template>
                                        <template x-if="idx === crumbs.length - 1">
                                            <span class="tl-crumb tl-crumb--current" x-text="seg.name"></span>
                                        </template>
                                        <x-icons.ui name="chevron-right" x-show="idx < crumbs.length - 1" class="tl-crumb-sep" />
                                    </span>
                                </template>
                            </nav>
                        </div>
                    </div>

                    <div class="tl-head-right">
                        <label class="tl-search" :class="{ 'tl-search--active': isSearching }">
                            <x-icons.ui name="search" class="tl-search-icon" />
                            <input
                                x-model="searchQuery"
                                @input.debounce.300ms="searchDocuments($event.target.value)"
                                type="search"
                                class="tl-search-input"
                                placeholder="Cari folder atau dokumen"
                                aria-label="Cari folder atau dokumen Google Drive"
                                autocomplete="off">
                            <button x-show="searchQuery !== ''" x-cloak @click="clearSearch()" type="button" class="tl-search-clear" aria-label="Hapus pencarian">
                                <x-icons.ui name="close" />
                            </button>
                        </label>
                        <span class="tl-count" x-show="!loadingFolders" x-cloak>
                            <x-icons.ui name="document" />
                            <span x-text="total.toLocaleString('id-ID')"></span>
                        </span>
                        <button @click="refresh()" class="tl-refresh" :disabled="loadingFolders || loading" :title="'Sinkronkan dengan Google Drive'" aria-label="Sinkronkan dengan Google Drive">
                            <x-icons.ui name="refresh" x-bind:class="{ 'tl-spin': loadingFolders || loading }" />
                        </button>
                    </div>
                </section>

                {{-- ── Isi: pohon folder + daftar file ── --}}
                <div class="tl-layout">
                    {{-- Pohon folder --}}
                    <aside class="tl-tree" aria-label="Daftar folder">
                        <div class="tl-tree-head">
                            <span class="tl-tree-head-icon" aria-hidden="true" x-html="folderIconSvg()"></span>
                            <span>{{ __('Folder') }}</span>
                        </div>

                        <div x-show="loadingFolders" class="tl-tree-loading">
                            <span class="tl-spinner"></span>
                        </div>

                        <template x-if="!loadingFolders">
                            <div class="tl-tree-list">
                                <button
                                    @click="selectFolder(root.id)"
                                    class="tl-tree-row tl-tree-row--root"
                                    :class="{ 'tl-tree-row--active': activeFolderId === root.id }">
                                    <span class="tl-tree-folder-tile" aria-hidden="true" x-html="folderIconSvg()">
                                    </span>
                                    <span class="tl-tree-label" x-text="root.name || 'Tata Lingkungan'"></span>
                                </button>

                                <template x-for="row in orderedRows()" :key="row.folder.id">
                                    <div class="tl-tree-item" :style="{ paddingLeft: (row.depth * 16) + 'px' }">
                                        <button
                                            @click="toggleExpand(row.folder.id)"
                                            class="tl-caret"
                                            :class="{ 'tl-caret--open': expandedIds[row.folder.id] }"
                                            x-show="visibleChildrenCount(row.folder.id) > 0"
                                            :aria-label="expandedIds[row.folder.id] ? 'Tutup subfolder' : 'Perluas subfolder'"
                                            :aria-expanded="expandedIds[row.folder.id] ? 'true' : 'false'">
                                            <x-icons.ui name="chevron-right" />
                                        </button>
                                        <button
                                            @click="selectFolder(row.folder.id)"
                                            class="tl-tree-row"
                                            :class="{ 'tl-tree-row--active': activeFolderId === row.folder.id }">
                                            <span class="tl-tree-folder-tile" aria-hidden="true" x-html="folderIconSvg()">
                                            </span>
                                            <span class="tl-tree-label" x-text="row.folder.name"></span>
                                        </button>
                                    </div>
                                </template>

                                <p x-show="orderedRows().length === 0" x-cloak class="tl-tree-empty" x-text="isSearching ? 'Folder tidak ditemukan.' : 'Tidak ada subfolder'"></p>
                            </div>
                        </template>
                    </aside>

                    {{-- Daftar file --}}
                    <section class="tl-files" aria-label="Daftar dokumen">
                        {{-- Skeleton --}}
                        <div x-show="loadingFolders" class="tl-files-skeleton">
                            <template x-for="i in 8" :key="i">
                                <div class="tl-file-skeleton"></div>
                            </template>
                        </div>

                        <div x-show="!loadingFolders && !loading && files.length > 0" x-cloak class="tl-files-summary">
                            <p>
                                <span x-show="isSearching">Hasil untuk “<span x-text="searchTerm"></span>”</span>
                                <span x-show="!isSearching" x-text="activeFolderName || root.name"></span>
                            </p>
                            <span x-text="`${total.toLocaleString('id-ID')} dokumen`"></span>
                        </div>

                        {{-- Daftar --}}
                        <template x-if="!loadingFolders && files.length > 0">
                            <div>
                                <template x-for="file in files" :key="file.id">
                                    <button
                                        @click="openPreview(file)"
                                        class="tl-file-row"
                                        :class="{ 'tl-file-row--active': selectedFile && selectedFile.id === file.id }">
                                        <span class="tl-file-icon" aria-hidden="true" :class="iconClass(file.category)" x-html="iconSvg(file.category)"></span>
                                        <span class="tl-file-copy">
                                            <span class="tl-file-name" x-text="file.name" :title="file.name"></span>
                                            <span class="tl-file-meta">
                                                <span class="tl-file-kind" :class="`tl-file-kind--${file.category}`" x-text="categoryLabel(file.category)"></span>
                                                <span class="tl-file-path" x-show="isSearching" x-cloak x-text="filePath(file)"></span>
                                            </span>
                                        </span>
                                        <span class="tl-file-eye">
                                            <x-icons.ui name="eye" />
                                        </span>
                                    </button>
                                </template>
                            </div>
                        </template>

                        {{-- Empty: folder tidak berisi file --}}
                        <div x-show="!loadingFolders && !loading && files.length === 0" x-cloak class="tl-files-empty">
                            <span class="tl-state-icon tl-state-icon--sm">
                                <x-icons.ui name="document" />
                            </span>
                            <p class="tl-files-empty-text" x-text="isSearching ? 'Dokumen yang dicari tidak ditemukan.' : 'Folder ini belum memiliki dokumen.'"></p>
                        </div>

                        {{-- Sentinel infinite scroll --}}
                        <div x-ref="sentinel" class="tl-sentinel" aria-hidden="true"></div>

                        {{-- Loader halaman berikutnya --}}
                        <div x-show="loading && !loadingFolders" x-cloak class="tl-load-more" x-transition.opacity>
                            <span class="tl-spinner"></span>
                            <span>{{ __('Memuat dokumen lainnya...') }}</span>
                        </div>
                    </section>
                </div>
        </div>

        {{-- ── Modal Preview ── --}}
        <div x-show="selectedFile !== null"
            x-cloak
            x-transition.opacity
            @keydown.escape.window="closePreview()"
            class="tl-modal"
            role="dialog" aria-modal="true" aria-label="Pratinjau dokumen">
            <div class="tl-modal-backdrop" @click="closePreview()"></div>
            <div class="tl-modal-panel" x-transition:enter="tl-transition-in" x-transition:leave="tl-transition-out">
                <header class="tl-modal-head">
                    <span class="tl-file-icon" :class="selectedFile ? iconClass(selectedFile.category) : ''" x-show="selectedFile" x-html="selectedFile ? iconSvg(selectedFile.category) : ''"></span>
                    <div class="min-w-0 flex-1">
                        <h3 class="tl-modal-title" x-text="selectedFile?.name ?? ''"></h3>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a :href="selectedFile ? webView(selectedFile) : '#'" target="_blank" rel="noopener" class="tl-btn tl-btn--ghost tl-btn--sm" x-show="selectedFile">
                            <x-icons.ui name="arrow-right" />
                            {{ __('Buka di Drive') }}
                        </a>
                        <a :href="selectedFile ? downloadUrl(selectedFile) : '#'" target="_blank" rel="noopener" class="tl-btn tl-btn--primary tl-btn--sm" x-show="selectedFile">
                            <x-icons.ui name="download" />
                            {{ __('Download') }}
                        </a>
                        <button @click="closePreview()" class="tl-btn tl-btn--icon" aria-label="Tutup pratinjau">
                            <x-icons.ui name="close" />
                        </button>
                    </div>
                </header>
                <div class="tl-modal-body">
                    <div x-show="previewLoading" class="tl-preview-loading">
                        <span class="tl-spinner tl-spinner--lg"></span>
                        <p>{{ __('Memuat pratinjau...') }}</p>
                    </div>
                    <iframe
                        x-show="previewUrl !== ''"
                        :src="previewUrl"
                        @load="onPreviewLoaded()"
                        class="tl-preview-frame"
                        loading="lazy"
                        allow="autoplay; fullscreen"
                        frameborder="0"
                        scrolling="yes"></iframe>
                </div>
            </div>
        </div>
    </div>

    <style>

        .tl-wrap {
            position: relative;
            font-family: 'Plus Jakarta Sans Variable', 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
        }
        .tl-page-shell::before {
            content: '';
            position: absolute;
            z-index: -1;
            top: 13rem;
            left: -12rem;
            width: 27rem;
            height: 27rem;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(16, 185, 129, .09), transparent 68%);
            filter: blur(7px);
            pointer-events: none;
        }

        /* ── Wadah utama ── */
        .tl-explorer {
            position: relative;
            isolation: isolate;
            background: #ffffff;
            border: 1px solid rgba(22, 137, 83, .13);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(13,43,29,0.06);
        }
        .tl-accent {
            height: 3px;
            background: #178a53;
        }

        /* ── Header ── */
        .tl-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            padding: 18px 22px;
            border-bottom: 1px solid #e8efe9;
            background: #fbfdfc;
        }
        .tl-head-left { display: flex; align-items: center; gap: 14px; min-width: 0; flex: 1; }
        .tl-head-logo {
            flex-shrink: 0;
            width: 46px; height: 46px;
            border-radius: 14px;
            background: #146a44;
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 3px 8px rgba(20,106,68,0.22);
        }
        .tl-head-logo svg { width: 22px; height: 22px; }
        .tl-head-title {
            font-size: 16px;
            font-weight: 700;
            color: #12201a;
            letter-spacing: -0.01em;
            margin-bottom: 4px;
        }
        .tl-crumbs { display: flex; align-items: center; flex-wrap: wrap; gap: 2px; min-width: 0; min-height: 26px; }
        .tl-crumb-group { display: inline-flex; align-items: center; gap: 2px; }
        .tl-crumb {
            font-size: 12.5px;
            font-weight: 600;
            color: #5b6b63;
            padding: 3px 8px;
            border-radius: 8px;
            max-width: 240px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer;
            background: none;
            border: 0;
            transition: background .15s ease, color .15s ease;
        }
        button.tl-crumb:hover { background: #e6f5ec; color: #146a44; }
        .tl-crumb--current { color: #12201a; font-weight: 700; }
        .tl-crumb-sep { width: 12px; height: 12px; color: #b6c2bb; flex-shrink: 0; }
        .tl-head-right { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
        .tl-search {
            display: flex;
            align-items: center;
            gap: 8px;
            width: min(20rem, 32vw);
            min-height: 42px;
            padding: 0 8px 0 12px;
            border: 1px solid #d9e5de;
            border-radius: 12px;
            background: #fff;
            color: #63756b;
            transition: border-color .16s ease, box-shadow .16s ease, background .16s ease;
        }
        .tl-search:focus-within,
        .tl-search--active { border-color: #178a53; box-shadow: 0 0 0 3px rgba(23,138,83,.12); }
        .tl-search-icon { width: 16px; height: 16px; flex: 0 0 auto; }
        .tl-search-input { min-width: 0; flex: 1; border: 0; outline: 0; background: transparent; color: #17251e; font: inherit; font-size: 13px; font-weight: 500; }
        .tl-search-input::placeholder { color: #718279; opacity: 1; }
        .tl-search-clear { display: grid; width: 28px; height: 28px; place-items: center; flex: 0 0 auto; border: 0; border-radius: 8px; background: transparent; color: #64748b; cursor: pointer; }
        .tl-search-clear:hover { background: #edf4ef; color: #146a44; }
        .tl-search-clear svg { width: 15px; height: 15px; }
        .tl-count {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 14px;
            border-radius: 9999px;
            background: #eef7f1;
            border: 1px solid #d8ede0;
            font-size: 12.5px;
            font-weight: 700;
            color: #146a44;
        }
        .tl-count svg { width: 14px; height: 14px; }
        .tl-refresh {
            width: 42px; height: 42px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #475569;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            transition: color .15s ease, background .15s ease, border-color .15s ease, box-shadow .15s ease, transform .15s ease;
            box-shadow: 0 1px 2px rgba(13,43,29,0.05);
        }
        .tl-refresh:hover { border-color: #1ea567; color: #146a44; background: #f4faf6; box-shadow: 0 4px 12px -4px rgba(20,106,68,0.25); transform: translateY(-1px); }
        .tl-refresh:disabled { opacity: 0.55; cursor: wait; }
        .tl-refresh svg { width: 16px; height: 16px; }
        .tl-spin { animation: tl-spin 1s linear infinite; }

        /* ── Layout: pohon + file ── */
        .tl-layout {
            display: grid;
            grid-template-columns: 300px minmax(0, 1fr);
            min-height: 480px;
            max-height: 72vh;
        }

        /* ── Pohon folder ── */
        .tl-tree {
            border-right: 1px solid #e8efe9;
            background: #f8faf9;
            overflow-y: auto;
            padding: 16px 12px 20px;
            scrollbar-width: thin;
            scrollbar-color: #c8d6cd transparent;
        }
        .tl-tree::-webkit-scrollbar { width: 6px; }
        .tl-tree::-webkit-scrollbar-thumb { background: #c8d6cd; border-radius: 9999px; }
        .tl-tree::-webkit-scrollbar-track { background: transparent; }
        .tl-tree-head {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #5f7268;
            padding: 0 10px 12px;
        }
        .tl-tree-head-icon { width: 14px; height: 14px; color: #10b981; }
        .tl-tree-loading { display: flex; justify-content: center; padding: 36px 0; }
        .tl-tree-list { display: flex; flex-direction: column; gap: 2px; }
        .tl-tree-item { display: flex; align-items: center; gap: 0; }
        .tl-caret {
            flex-shrink: 0;
            width: 26px; height: 34px;
            border: 0;
            background: none;
            color: #94a3b8;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            border-radius: 8px;
            transition: transform .18s ease, color .15s ease;
        }
        .tl-caret:hover { color: #146a44; background: #eef4f0; }
        .tl-caret svg { width: 13px; height: 13px; }
        .tl-caret--open { transform: rotate(90deg); }
        .tl-tree-row {
            flex: 1;
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 7px 10px;
            border: 0;
            border-radius: 12px;
            background: none;
            cursor: pointer;
            text-align: left;
            transition: background .15s ease, box-shadow .15s ease, transform .15s ease;
        }
        .tl-tree-row:hover { background: #edf4ef; transform: translateX(2px); }
        .tl-tree-row--active {
            background: #146a44;
            color: #fff;
            box-shadow: none;
        }
        .tl-tree-row--root { font-weight: 700; margin-bottom: 8px; }
        .tl-tree-folder-tile {
            flex-shrink: 0;
            width: 30px; height: 30px;
            border-radius: 9px;
            background: #fdf3e0;
            display: flex; align-items: center; justify-content: center;
            transition: background .15s ease;
        }
        .tl-tree-head-icon { display: inline-flex; width: 14px; height: 14px; color: #10b981; }
        .tl-tree-head-icon svg { width: 14px; height: 14px; }
        .tl-tree-folder-tile svg { width: 17px; height: 17px; color: #f59e0b; }
        .tl-tree-row--active .tl-tree-folder-tile { background: rgba(255,255,255,0.18); }
        .tl-tree-row--active .tl-tree-folder-tile svg { color: #fde68a; }
        .tl-tree-label {
            font-size: 13px;
            font-weight: 600;
            color: #22322a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .tl-tree-row--active .tl-tree-label { color: #fff; }
        .tl-tree-empty { font-size: 12.5px; color: #94a3b8; padding: 14px 10px; }

        /* ── Daftar file ── */
        .tl-files {
            overflow-y: auto;
            padding: 12px;
            scrollbar-width: thin;
            scrollbar-color: #c8d6cd transparent;
        }
        .tl-files::-webkit-scrollbar { width: 6px; }
        .tl-files::-webkit-scrollbar-thumb { background: #c8d6cd; border-radius: 9999px; }
        .tl-files::-webkit-scrollbar-track { background: transparent; }
        .tl-files-summary { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 3px 10px 12px; color: #63756b; font-size: 12px; font-weight: 600; }
        .tl-files-summary p { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #31463a; }
        .tl-files-summary > span { flex: 0 0 auto; color: #63756b; }
        .tl-file-row {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            border: 0;
            border-radius: 14px;
            background: none;
            cursor: pointer;
            text-align: left;
            transition: background .15s ease, box-shadow .15s ease, transform .15s ease;
        }
        .tl-file-row:hover { background: #f2f7f4; box-shadow: inset 0 0 0 1px #e3efe7; transform: translateX(2px); }
        .tl-file-row--active { background: #e9f6ee; box-shadow: inset 0 0 0 1.5px #a8dcc0; }
        .tl-file-icon {
            flex-shrink: 0;
            width: 40px; height: 40px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            box-shadow: inset 0 0 0 1px rgba(15,23,42,0.04);
        }
        .tl-file-icon svg { width: 20px; height: 20px; }
        .tl-file-copy { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 4px; }
        .tl-file-name {
            flex: 1;
            min-width: 0;
            font-size: 13.5px;
            font-weight: 600;
            color: #1f2d26;
            line-height: 1.45;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            word-break: break-word;
        }
        .tl-file-meta { display: flex; min-width: 0; align-items: center; gap: 7px; color: #63756b; font-size: 11px; line-height: 1.2; }
        .tl-file-kind { border-radius: 999px; background: #eef2f0; color: #53645b; padding: 3px 6px; font-size: 10px; font-weight: 700; line-height: 1; }
        .tl-file-kind--pdf { background: #fef2f2; color: #b91c1c; }
        .tl-file-kind--word { background: #eff6ff; color: #1d4ed8; }
        .tl-file-kind--excel { background: #ecfdf5; color: #047857; }
        .tl-file-kind--image { background: #f5f3ff; color: #6d28d9; }
        .tl-file-path { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .tl-file-eye {
            flex-shrink: 0;
            width: 28px; height: 28px;
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            color: #94a3b8;
            background: transparent;
            opacity: 0;
            transform: translateX(4px);
            transition: opacity .15s ease, transform .15s ease, background .15s ease, color .15s ease;
        }
        .tl-file-eye svg { width: 15px; height: 15px; }
        .tl-file-row:hover .tl-file-eye, .tl-file-row--active .tl-file-eye { opacity: 1; transform: translateX(0); }
        .tl-file-row--active .tl-file-eye { color: #146a44; background: #d7efe1; }

        .tl-files-skeleton { display: flex; flex-direction: column; gap: 8px; }
        .tl-file-skeleton {
            position: relative;
            height: 58px;
            border-radius: 14px;
            background: #eef3f0;
            overflow: hidden;
        }
        .tl-file-skeleton::after {
            content: "";
            position: absolute; inset: 0;
            background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.7) 50%, transparent 100%);
            transform: translateX(-100%);
            animation: tl-shimmer 1.4s ease-in-out infinite;
        }
        @keyframes tl-shimmer { 100% { transform: translateX(100%); } }

        .tl-files-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 72px 20px;
            text-align: center;
        }
        .tl-files-empty-text { font-size: 13.5px; color: #5b6b63; font-weight: 500; }
        .tl-state-icon--sm { width: 48px; height: 48px; border-radius: 15px; margin-bottom: 0; }
        .tl-state-icon--sm svg { width: 22px; height: 22px; }

        /* ── State card (error) ── */
        .tl-state-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 8px;
            padding: 76px 24px;
        }
        .tl-state-icon {
            width: 60px; height: 60px;
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            background: #146a44;
            color: #fff;
            box-shadow: 0 4px 8px rgba(20,106,68,0.2);
            margin-bottom: 8px;
        }
        .tl-state-icon svg { width: 28px; height: 28px; }
        .tl-state-icon--error { background: #b91c1c; box-shadow: 0 4px 8px rgba(185,28,28,0.2); }
        .tl-state-title { font-size: 17px; font-weight: 700; color: #12201a; }
        .tl-state-desc { font-size: 13px; color: #5b6b63; max-width: 440px; line-height: 1.6; }

        /* ── Tombol ── */
        .tl-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: color .15s ease, background .15s ease, border-color .15s ease, box-shadow .15s ease, transform .15s ease;
            text-decoration: none;
        }
        .tl-btn svg { width: 15px; height: 15px; }
        .tl-btn--sm { padding: 8px 14px; font-size: 12.5px; }
        .tl-btn--primary {
            background: #146a44;
            color: #fff;
            box-shadow: 0 4px 10px -2px rgba(20,106,68,0.35);
        }
        .tl-btn--primary:hover { box-shadow: 0 6px 14px -2px rgba(20,106,68,0.45); transform: translateY(-1px); }
        .tl-btn--ghost {
            background: #f0f7f2;
            color: #146a44;
            border: 1px solid #d3e8d9;
        }
        .tl-btn--ghost:hover { background: #e4f3e9; }
        .tl-btn--icon {
            width: 38px; height: 38px;
            border-radius: 12px;
            background: #f1f5f9;
            color: #475569;
            padding: 0;
        }
        .tl-btn--icon:hover { background: #fee2e2; color: #b91c1c; transform: translateY(-1px); }
        .tl-btn--icon svg { width: 16px; height: 16px; }
        .tl-crumb:focus-visible,
        .tl-refresh:focus-visible,
        .tl-caret:focus-visible,
        .tl-tree-row:focus-visible,
        .tl-file-row:focus-visible,
        .tl-btn:focus-visible {
            outline: 3px solid rgba(22, 137, 83, .32);
            outline-offset: 2px;
        }

        /* ── Infinite scroll ── */
        .tl-sentinel { height: 1px; }
        .tl-load-more {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 18px 0 4px;
            font-size: 13px;
            color: #5b6b63;
            font-weight: 600;
        }
        .tl-spinner {
            width: 18px; height: 18px;
            border-radius: 9999px;
            border: 2.5px solid #d1fae5;
            border-top-color: #059669;
            animation: tl-spin .8s linear infinite;
        }
        .tl-spinner--lg { width: 30px; height: 30px; border-width: 3px; }
        @keyframes tl-spin { to { transform: rotate(360deg); } }

        /* ── Modal ── */
        .tl-modal { position: fixed; inset: 0; z-index: 90; }
        .tl-modal-backdrop {
            position: absolute; inset: 0;
            background: rgba(2, 22, 14, 0.6);
            backdrop-filter: blur(6px);
        }
        .tl-modal-panel {
            position: relative;
            margin: 4vh auto 0;
            width: min(960px, calc(100% - 24px));
            height: 92vh;
            background: #fff;
            border-radius: 22px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 32px 80px -16px rgba(0,0,0,0.5);
            border: 1px solid rgba(255,255,255,0.08);
        }
        .tl-transition-in { transition: transform .2s ease, opacity .2s ease; transform: translateY(0); opacity: 1; }
        .tl-transition-out { transition: transform .2s ease, opacity .2s ease; transform: translateY(12px); opacity: 0; }
        .tl-modal-head {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 18px;
            border-bottom: 1px solid #e2e8f0;
            background: #fbfdfc;
        }
        .tl-modal-title {
            font-size: 14.5px;
            font-weight: 700;
            color: #12201a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .tl-modal-body { position: relative; flex: 1; background: #f1f5f9; }
        .tl-preview-frame { width: 100%; height: 100%; border: 0; display: block; }
        .tl-preview-loading {
            position: absolute; inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 14px;
            font-size: 13px;
            color: #64748b;
            font-weight: 600;
        }

        /* ── Dark mode ── */
        .dark .tl-explorer { background: linear-gradient(145deg, #152234 0%, #101b2a 100%); border-color: rgba(110,231,183,0.16); }
        .dark .tl-head { background: linear-gradient(180deg, #0f172a 0%, #111c2c 100%); border-color: #334155; }
        .dark .tl-head-title { color: #e2e8f0; }
        .dark .tl-crumb { color: #94a3b8; }
        .dark button.tl-crumb:hover { background: rgba(30,165,103,0.15); color: #6ee7b7; }
        .dark .tl-crumb--current { color: #e2e8f0; }
        .dark .tl-count { background: rgba(30,165,103,0.12); border-color: rgba(110,231,183,0.22); color: #6ee7b7; }
        .dark .tl-search { background: #0f172a; border-color: #334155; color: #94a3b8; }
        .dark .tl-search:focus-within,
        .dark .tl-search--active { border-color: #34d399; box-shadow: 0 0 0 3px rgba(52,211,153,.15); }
        .dark .tl-search-input { color: #e2e8f0; }
        .dark .tl-search-input::placeholder { color: #94a3b8; }
        .dark .tl-search-clear { color: #94a3b8; }
        .dark .tl-search-clear:hover { background: rgba(148,163,184,0.12); color: #6ee7b7; }
        .dark .tl-refresh { background: #0f172a; border-color: #334155; color: #94a3b8; }
        .dark .tl-refresh:hover { border-color: #1ea567; color: #6ee7b7; background: rgba(30,165,103,0.08); }
        .dark .tl-tree { background: #0f172a; border-color: #334155; scrollbar-color: #334155 transparent; }
        .dark .tl-tree::-webkit-scrollbar-thumb { background: #334155; }
        .dark .tl-tree-head { color: #94a3b8; }
        .dark .tl-tree-head-icon { color: #34d399; }
        .dark .tl-tree-row:hover { background: rgba(148,163,184,0.12); }
        .dark .tl-tree-folder-tile { background: rgba(245,158,11,0.14); }
        .dark .tl-tree-folder-tile svg { color: #fbbf24; }
        .dark .tl-tree-label { color: #e2e8f0; }
        .dark .tl-caret:hover { color: #6ee7b7; background: rgba(148,163,184,0.12); }
        .dark .tl-tree-empty { color: #64748b; }
        .dark .tl-files { scrollbar-color: #334155 transparent; }
        .dark .tl-files::-webkit-scrollbar-thumb { background: #334155; }
        .dark .tl-file-row:hover { background: rgba(148,163,184,0.12); box-shadow: inset 0 0 0 1px #334155; }
        .dark .tl-file-row--active { background: rgba(30,165,103,0.15); box-shadow: inset 0 0 0 1.5px rgba(110,231,183,0.35); }
        .dark .tl-file-name { color: #e2e8f0; }
        .dark .tl-files-summary p { color: #d8e5dd; }
        .dark .tl-files-summary,
        .dark .tl-files-summary > span,
        .dark .tl-file-meta { color: #94a3b8; }
        .dark .tl-file-kind { background: rgba(148,163,184,.14); color: #cbd5e1; }
        .dark .tl-file-kind--pdf { background: rgba(248,113,113,.14); color: #fca5a5; }
        .dark .tl-file-kind--word { background: rgba(96,165,250,.14); color: #93c5fd; }
        .dark .tl-file-kind--excel { background: rgba(52,211,153,.14); color: #6ee7b7; }
        .dark .tl-file-kind--image { background: rgba(167,139,250,.14); color: #c4b5fd; }
        .dark .tl-file-eye { color: #64748b; }
        .dark .tl-file-row--active .tl-file-eye { color: #6ee7b7; background: rgba(30,165,103,0.2); }
        .dark .tl-file-skeleton { background: #263449; }
        .dark .tl-file-skeleton::after {
            background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.06) 50%, transparent 100%);
        }
        .dark .tl-files-empty-text { color: #94a3b8; }
        .dark .tl-state-title { color: #e2e8f0; }
        .dark .tl-state-desc { color: #94a3b8; }
        .dark .tl-load-more { color: #94a3b8; }
        .dark .tl-btn--ghost { background: rgba(30,165,103,0.12); color: #6ee7b7; border-color: rgba(110,231,183,0.25); }
        .dark .tl-btn--ghost:hover { background: rgba(30,165,103,0.2); }
        .dark .tl-btn--icon { background: #334155; color: #94a3b8; }
        .dark .tl-modal-panel { background: #1e293b; border-color: #334155; }
        .dark .tl-modal-head { background: #0f172a; border-color: #334155; }
        .dark .tl-modal-title { color: #e2e8f0; }
        .dark .tl-modal-body { background: #0f172a; }
        .dark .tl-preview-loading { color: #94a3b8; }

        /* ── Responsive ── */
        @media (max-width: 1023px) {
            .tl-layout { grid-template-columns: 1fr; max-height: none; }
            .tl-tree {
                border-right: 0;
                border-bottom: 1px solid #e8efe9;
                max-height: 264px;
            }
            .dark .tl-tree { border-bottom-color: #334155; }
            .tl-files { max-height: 60vh; }
        }
        @media (max-width: 640px) {
            .tl-page-shell::before { left: -18rem; }
            .tl-head { padding: 14px 16px; }
            .tl-head-left { flex-basis: 100%; }
            .tl-head-right { width: 100%; }
            .tl-search { width: auto; flex: 1; }
            .tl-count { display: none !important; }
            .tl-file-row { padding: 11px 10px; }
            .tl-files-summary { padding-inline: 4px; }
            .tl-modal-panel { margin-top: 0; width: 100%; height: 100vh; border-radius: 0; }
        }
        @media (prefers-reduced-motion: reduce) {
            .tl-page-shell *,
            .tl-page-shell *::before,
            .tl-page-shell *::after {
                scroll-behavior: auto !important;
                transition-duration: .01ms !important;
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
            }
        }
    </style>
</div>
@endsection

@push('scripts')
@vite(['resources/js/tata-lingkungan.ts'])
@endpush
