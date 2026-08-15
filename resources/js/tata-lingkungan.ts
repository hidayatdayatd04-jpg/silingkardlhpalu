/**
 * Tata Lingkungan — Penjelajah Dokumen
 *
 * Menampilkan pohon folder dan daftar file dari Google Drive (via endpoint
 * Laravel /api/tata-lingkungan/*). Klik file untuk membuka preview dokumen
 * di dalam modal (iframe Google Drive / Google Docs Viewer).
 */

export type CategoryKey =
    | 'pdf'
    | 'word'
    | 'excel'
    | 'powerpoint'
    | 'image'
    | 'video'
    | 'audio'
    | 'archive'
    | 'other';

export interface DriveFolder {
    id: string;
    name: string;
    parent_id: string;
    path: string;
}

export interface DriveFile {
    id: string;
    name: string;
    mimeType: string;
    extension: string;
    path: string;
    webViewLink: string | null;
    category: CategoryKey;
}

export interface RootInfo {
    id: string;
    name: string;
}

export interface FoldersResponse {
    error: string | null;
    message?: string;
    root: RootInfo;
    folders: DriveFolder[];
    total_files: number;
    cached_at: string | null;
}

export interface FilesResponse {
    error: string | null;
    message?: string;
    files: DriveFile[];
    total: number;
    page: number;
    per_page: number;
    has_more: boolean;
    cached_at: string | null;
}

interface IconSpec {
    label: string;
    bg: string;
    color: string;
    svg: string;
}

const CATEGORY_ICONS: Record<CategoryKey, IconSpec> = {
    pdf: {
        label: 'PDF',
        bg: 'bg-red-50 dark:bg-red-500/10',
        color: 'text-red-600 dark:text-red-400',
        svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M9 15h6M9 11h2"/></svg>',
    },
    word: {
        label: 'Word',
        bg: 'bg-blue-50 dark:bg-blue-500/10',
        color: 'text-blue-600 dark:text-blue-400',
        svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="m9 13 2 5 2-5 2 5 2-5"/></svg>',
    },
    excel: {
        label: 'Excel',
        bg: 'bg-emerald-50 dark:bg-emerald-500/10',
        color: 'text-emerald-600 dark:text-emerald-400',
        svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="m9 16 6-6M15 16 9 10"/></svg>',
    },
    powerpoint: {
        label: 'PowerPoint',
        bg: 'bg-orange-50 dark:bg-orange-500/10',
        color: 'text-orange-600 dark:text-orange-400',
        svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h3a2 2 0 0 1 0 4H9v4"/></svg>',
    },
    image: {
        label: 'Gambar',
        bg: 'bg-violet-50 dark:bg-violet-500/10',
        color: 'text-violet-600 dark:text-violet-400',
        svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>',
    },
    video: {
        label: 'Video',
        bg: 'bg-rose-50 dark:bg-rose-500/10',
        color: 'text-rose-600 dark:text-rose-400',
        svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 8-6 4 6 4V8Z"/><rect x="2" y="6" width="14" height="12" rx="2"/></svg>',
    },
    audio: {
        label: 'Audio',
        bg: 'bg-amber-50 dark:bg-amber-500/10',
        color: 'text-amber-600 dark:text-amber-400',
        svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>',
    },
    archive: {
        label: 'Arsip',
        bg: 'bg-slate-100 dark:bg-slate-700/40',
        color: 'text-slate-600 dark:text-slate-300',
        svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="5" rx="1"/><path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8"/><path d="M10 12h4"/></svg>',
    },
    other: {
        label: 'Dokumen',
        bg: 'bg-teal-50 dark:bg-teal-500/10',
        color: 'text-teal-600 dark:text-teal-400',
        svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>',
    },
};

const PREVIEWABLE = new Set<CategoryKey>(['pdf', 'word', 'excel', 'powerpoint', 'image', 'video', 'audio']);

interface FolderRow {
    folder: DriveFolder;
    depth: number;
}

export function tataLingkunganExplorer() {
    return {
        /* ── State ── */
        root: { id: '', name: '' } as RootInfo,
        folders: [] as DriveFolder[],
        expandedIds: {} as Record<string, boolean>,

        activeFolderId: '',
        activeFolderName: '',

        files: [] as DriveFile[],
        total: 0,
        page: 1,
        perPage: 60,
        hasMore: false,

        loading: false,
        loadingFolders: true,
        error: null as string | null,
        errorCode: null as string | null,
        observer: null as IntersectionObserver | null,

        selectedFile: null as DriveFile | null,
        previewUrl: '',
        previewLoading: false,

        /* Magic properties Alpine (disuntik oleh framework saat mount) */
        $refs: {} as Record<string, HTMLElement>,
        $nextTick: (() => {}) as unknown as (cb: () => void) => void,

        /* ── Lifecycle ── */
        init() {
            this.observer = new IntersectionObserver((entries) => {
                const entry = entries[0];
                if (entry.isIntersecting && !this.loading && this.hasMore && !this.error) {
                    this.loadMore();
                }
            }, { rootMargin: '400px 0px' });

            this.$nextTick(() => {
                const sentinel = this.$refs.sentinel as HTMLElement | undefined;
                if (sentinel && this.observer) {
                    this.observer.observe(sentinel);
                }
            });

            this.loadFolders();
        },

        /* ── Data fetching ── */
        async loadFolders() {
            this.loadingFolders = true;
            this.error = null;

            try {
                const response = await window.axios.get<FoldersResponse>('/api/tata-lingkungan/folders');
                const data: FoldersResponse = response.data;

                if (data.error) {
                    this.setError(data.error, data.message ?? 'Terjadi kesalahan.');
                    return;
                }

                this.root = data.root;
                this.folders = data.folders;
                this.expandedIds = { [data.root.id]: true };
                this.selectFolder(data.root.id);
            } catch (err) {
                const status = (err as { response?: { status?: number } }).response?.status;
                this.setError(
                    status === 503 ? 'not_configured' : 'drive_unavailable',
                    status === 503
                        ? 'Google Drive API belum dikonfigurasi. Hubungi administrator.'
                        : 'Gagal memuat dokumen. Periksa koneksi internet Anda dan coba lagi.',
                );
            } finally {
                this.loadingFolders = false;
            }
        },

        async fetchFiles(reset: boolean) {
            if (this.loading || !this.activeFolderId) return;
            this.loading = true;
            this.error = null;

            if (reset) {
                this.page = 1;
                this.files = [];
            }

            const params = new URLSearchParams({
                folder_id: this.activeFolderId,
                page: String(this.page),
                per_page: String(this.perPage),
            });

            try {
                const response = await window.axios.get<FilesResponse>('/api/tata-lingkungan/files', { params });
                const data: FilesResponse = response.data;

                if (data.error) {
                    this.setError(data.error, data.message ?? 'Terjadi kesalahan.');
                    return;
                }

                this.files = reset ? data.files : [...this.files, ...data.files];
                this.total = data.total;
                this.hasMore = data.has_more;
                this.page = data.page + 1;
            } catch (err) {
                const status = (err as { response?: { status?: number } }).response?.status;
                this.setError(
                    status === 503 ? 'not_configured' : 'drive_unavailable',
                    status === 503
                        ? 'Google Drive API belum dikonfigurasi. Hubungi administrator.'
                        : 'Gagal memuat daftar file. Silakan coba lagi.',
                );
            } finally {
                this.loading = false;
            }
        },

        setError(code: string, message: string) {
            this.error = message;
            this.errorCode = code;
        },

        loadMore() {
            if (!this.hasMore || this.loading || this.error) return;
            this.fetchFiles(false);
        },

        async retry() {
            this.error = null;
            this.errorCode = null;
            this.selectedFile = null;
            this.previewUrl = '';
            await this.loadFolders();
        },

        async refresh() {
            this.selectedFile = null;
            this.previewUrl = '';
            this.error = null;
            this.loadingFolders = true;
            this.loading = true;
            try {
                const response = await window.axios.get<FoldersResponse>('/api/tata-lingkungan/folders', {
                    params: { refresh: true },
                });
                const data: FoldersResponse = response.data;

                if (data.error) {
                    this.setError(data.error, data.message ?? 'Terjadi kesalahan.');
                    return;
                }

                this.root = data.root;
                this.folders = data.folders;
                await this.fetchFiles(true);
            } catch (err) {
                const status = (err as { response?: { status?: number } }).response?.status;
                this.setError(
                    status === 503 ? 'not_configured' : 'drive_unavailable',
                    'Gagal menyegarkan dokumen. Silakan coba lagi.',
                );
            } finally {
                this.loadingFolders = false;
                this.loading = false;
            }
        },

        /* ── Navigasi folder ── */
        childrenOf(folderId: string): DriveFolder[] {
            return this.folders
                .filter((f) => f.parent_id === folderId)
                .sort((a, b) => a.name.localeCompare(b.name, 'id', { sensitivity: 'base' }));
        },

        childrenCount(folderId: string): number {
            return this.childrenOf(folderId).length;
        },

        orderedRows(): FolderRow[] {
            const rows: FolderRow[] = [];
            const walk = (parentId: string, depth: number) => {
                for (const folder of this.childrenOf(parentId)) {
                    rows.push({ folder, depth });
                    if (this.expandedIds[folder.id]) {
                        walk(folder.id, depth + 1);
                    }
                }
            };
            walk(this.root.id, 1);
            return rows;
        },

        toggleExpand(folderId: string) {
            if (this.expandedIds[folderId]) {
                delete this.expandedIds[folderId];
            } else {
                this.expandedIds[folderId] = true;
            }
        },

        selectFolder(folderId: string) {
            if (this.activeFolderId === folderId && this.files.length > 0) return;
            this.activeFolderId = folderId;
            this.expandedIds[folderId] = true;

            const folder = this.folders.find((f) => f.id === folderId);
            this.activeFolderName = folder ? folder.name : this.root.name;

            this.selectedFile = null;
            this.previewUrl = '';
            this.files = [];
            this.total = 0;
            this.page = 1;
            this.hasMore = false;
            this.fetchFiles(true);
        },

        get crumbs(): { id: string; name: string }[] {
            const segments: { id: string; name: string }[] = [];
            let current = this.folders.find((f) => f.id === this.activeFolderId);

            while (current) {
                segments.unshift({ id: current.id, name: current.name });
                const parent = this.folders.find((f) => f.id === current?.parent_id);
                current = parent ?? undefined;
            }

            segments.unshift({ id: this.root.id, name: this.root.name || 'Tata Lingkungan' });
            return segments;
        },

        /* ── Preview & download ── */
        previewUrlFor(file: DriveFile): string {
            if (PREVIEWABLE.has(file.category)) {
                return `https://drive.google.com/file/d/${file.id}/preview`;
            }
            // Google Docs Viewer untuk tipe yang tidak didukung preview Drive
            return `https://docs.google.com/gview?embedded=true&url=${encodeURIComponent(this.downloadUrl(file))}`;
        },

        downloadUrl(file: DriveFile): string {
            return `https://drive.google.com/uc?export=download&id=${file.id}`;
        },

        webView(file: DriveFile): string {
            return file.webViewLink || `https://drive.google.com/file/d/${file.id}/view`;
        },

        openPreview(file: DriveFile) {
            this.selectedFile = file;
            this.previewUrl = '';
            this.previewLoading = true;
            document.body.style.overflow = 'hidden';

            // Lazy: iframe diisi hanya saat modal dibuka
            window.setTimeout(() => {
                this.previewUrl = this.previewUrlFor(file);
            }, 80);
        },

        onPreviewLoaded() {
            this.previewLoading = false;
        },

        closePreview() {
            this.selectedFile = null;
            this.previewUrl = '';
            this.previewLoading = false;
            document.body.style.overflow = '';
        },

        /* ── Helpers ── */
        iconSpec(category: CategoryKey): IconSpec {
            return CATEGORY_ICONS[category] ?? CATEGORY_ICONS.other;
        },

        iconSvg(category: CategoryKey): string {
            return this.iconSpec(category).svg;
        },

        iconClass(category: CategoryKey): string {
            const spec = this.iconSpec(category);
            return `${spec.bg} ${spec.color}`;
        },

        /* ── Empty state flags ── */
        get isRootFolder(): boolean {
            return this.activeFolderId === this.root.id;
        },
    };
}

declare global {
    interface Window {
        axios: {
            get: <T = unknown>(
                url: string,
                config?: Record<string, unknown>,
            ) => Promise<{ data: T }>;
        };
        Alpine?: {
            data: (name: string, factory: () => ReturnType<typeof tataLingkunganExplorer>) => void;
        };
    }
}

document.addEventListener('alpine:init', () => {
    if (!window.Alpine) return;
    window.Alpine.data('tataLingkunganExplorer', () => tataLingkunganExplorer());
});
