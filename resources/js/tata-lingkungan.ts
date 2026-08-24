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

type CustomIconCategory = 'pdf' | 'word' | 'excel' | 'image' | 'other';

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
    folders: DriveFolder[];
    files: DriveFile[];
    total: number;
    page: number;
    per_page: number;
    has_more: boolean;
    search?: string;
    cached_at: string | null;
}

interface IconSpec {
    label: string;
    bg: string;
    color: string;
    svg: string;
}

const FOLDER_ICON_SVG = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3.75 7.5A2.25 2.25 0 0 1 6 5.25h4.12l1.7 1.7H18A2.25 2.25 0 0 1 20.25 9.2v7.05A2.25 2.25 0 0 1 18 18.5H6a2.25 2.25 0 0 1-2.25-2.25V7.5Z"/><path d="M3.9 9.5h16.2"/></svg>';

const CATEGORY_ICONS: Record<CustomIconCategory, IconSpec> = {
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
    image: {
        label: 'Gambar',
        bg: 'bg-violet-50 dark:bg-violet-500/10',
        color: 'text-violet-600 dark:text-violet-400',
        svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>',
    },
    other: {
        label: 'File',
        bg: 'bg-teal-50 dark:bg-teal-500/10',
        color: 'text-teal-600 dark:text-teal-400',
        svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>',
    },
};

const PREVIEWABLE = new Set<CategoryKey>(['pdf', 'word', 'excel', 'powerpoint', 'image', 'video', 'audio']);

export function tataLingkunganExplorer() {
    return {
        /* ── State ── */
        root: { id: '', name: '' } as RootInfo,
        folders: [] as DriveFolder[],
        searchQuery: '',
        treeWidth: 320,

        activeFolderId: '',
        activeFolderName: '',

        childFolders: [] as DriveFolder[],
        files: [] as DriveFile[],
        total: 0,
        page: 1,
        perPage: 60,
        hasMore: false,
        fileRequestId: 0,

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
            const savedTreeWidth = Number.parseInt(window.localStorage.getItem('tata-lingkungan:tree-width') ?? '', 10);
            if (Number.isFinite(savedTreeWidth)) {
                this.treeWidth = Math.min(560, Math.max(240, savedTreeWidth));
            }

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
            if ((!reset && this.loading) || !this.activeFolderId) return;

            const requestId = ++this.fileRequestId;
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

            if (this.searchTerm !== '') {
                params.set('search', this.searchTerm);
            }

            try {
                const response = await window.axios.get<FilesResponse>('/api/tata-lingkungan/files', { params });
                const data: FilesResponse = response.data;

                if (requestId !== this.fileRequestId) return;

                if (data.error) {
                    this.setError(data.error, data.message ?? 'Terjadi kesalahan.');
                    return;
                }

                this.childFolders = reset ? data.folders : this.childFolders;
                this.files = reset ? data.files : [...this.files, ...data.files];
                this.total = data.total;
                this.hasMore = data.has_more;
                this.page = data.page + 1;
            } catch (err) {
                if (requestId !== this.fileRequestId) return;
                const status = (err as { response?: { status?: number } }).response?.status;
                this.setError(
                    status === 503 ? 'not_configured' : 'drive_unavailable',
                    status === 503
                        ? 'Google Drive API belum dikonfigurasi. Hubungi administrator.'
                        : 'Gagal memuat daftar file. Silakan coba lagi.',
                );
            } finally {
                if (requestId === this.fileRequestId) {
                    this.loading = false;
                }
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

        searchDocuments(value: string) {
            this.searchQuery = value;
            this.selectedFile = null;
            this.previewUrl = '';
            this.fetchFiles(true);
        },

        clearSearch() {
            if (this.searchQuery === '') return;
            this.searchQuery = '';
            this.selectedFile = null;
            this.previewUrl = '';
            this.fetchFiles(true);
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
                if (!this.activeFolderId || ![data.root.id, ...data.folders.map((folder) => folder.id)].includes(this.activeFolderId)) {
                    this.activeFolderId = data.root.id;
                    this.activeFolderName = data.root.name;
                }
                await this.fetchFiles(true);
            } catch (err) {
                const status = (err as { response?: { status?: number } }).response?.status;
                this.setError(
                    status === 503 ? 'not_configured' : 'drive_unavailable',
                    'Gagal menyegarkan dokumen. Silakan coba lagi.',
                );
            } finally {
                this.loadingFolders = false;
            }
        },

        /* ── Navigasi folder ── */
        childrenOf(folderId: string): DriveFolder[] {
            return this.folders
                .filter((f) => f.parent_id === folderId)
                .sort((a, b) => a.name.localeCompare(b.name, 'id', { sensitivity: 'base' }));
        },

        get searchTerm(): string {
            return this.searchQuery.trim();
        },

        get isSearching(): boolean {
            return this.searchTerm !== '';
        },

        get sidebarFolders(): DriveFolder[] {
            const source = this.isSearching
                ? this.folders.filter((folder) => folder.name.toLocaleLowerCase('id-ID').includes(this.searchTerm.toLocaleLowerCase('id-ID')))
                : this.childrenOf(this.root.id);

            return [...source].sort((a, b) => a.name.localeCompare(b.name, 'id', { sensitivity: 'base' }));
        },

        selectFolder(folderId: string) {
            if (this.activeFolderId === folderId && this.files.length > 0 && !this.isSearching) return;
            this.searchQuery = '';
            this.activeFolderId = folderId;

            const folder = this.folders.find((f) => f.id === folderId);
            this.activeFolderName = folder ? folder.name : this.root.name;

            this.selectedFile = null;
            this.previewUrl = '';
            this.childFolders = [];
            this.files = [];
            this.total = 0;
            this.page = 1;
            this.hasMore = false;
            this.fetchFiles(true);
        },

        setTreeWidth(width: number) {
            const layout = this.$refs.layout as HTMLElement | undefined;
            const maxWidth = layout
                ? Math.max(240, Math.min(560, layout.getBoundingClientRect().width - 320))
                : 560;

            this.treeWidth = Math.round(Math.min(maxWidth, Math.max(240, width)));
            window.localStorage.setItem('tata-lingkungan:tree-width', String(this.treeWidth));
        },

        beginTreeResize(event: PointerEvent) {
            if (window.matchMedia('(max-width: 1023px)').matches) return;

            const startX = event.clientX;
            const startWidth = this.treeWidth;
            const move = (moveEvent: PointerEvent) => this.setTreeWidth(startWidth + moveEvent.clientX - startX);
            const stop = () => {
                window.removeEventListener('pointermove', move);
                window.removeEventListener('pointerup', stop);
            };

            event.preventDefault();
            window.addEventListener('pointermove', move);
            window.addEventListener('pointerup', stop, { once: true });
        },

        resizeTreeBy(amount: number) {
            this.setTreeWidth(this.treeWidth + amount);
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
        iconCategory(category: CategoryKey): CustomIconCategory {
            return ['pdf', 'word', 'excel', 'image'].includes(category)
                ? category as CustomIconCategory
                : 'other';
        },

        iconSpec(category: CategoryKey): IconSpec {
            return CATEGORY_ICONS[this.iconCategory(category)];
        },

        iconSvg(category: CategoryKey): string {
            return this.iconSpec(category).svg;
        },

        iconClass(category: CategoryKey): string {
            const spec = this.iconSpec(category);
            return `${spec.bg} ${spec.color}`;
        },

        categoryLabel(category: CategoryKey): string {
            return this.iconSpec(category).label;
        },

        folderIconSvg(): string {
            return FOLDER_ICON_SVG;
        },

        filePath(file: DriveFile): string {
            const parts = file.path.split('/');
            parts.pop();

            return parts.join(' / ') || this.root.name;
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
