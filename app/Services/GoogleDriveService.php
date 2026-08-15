<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleDriveService
{
    protected string $apiKey;

    protected string $folderId;

    protected int $cacheTtl;

    protected int $maxDepth;

    public const CATEGORIES = [
        'pdf' => ['label' => 'PDF', 'patterns' => ['application/pdf']],
        'word' => [
            'label' => 'Word',
            'patterns' => [
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.google-apps.document',
                'application/rtf',
                'text/rtf',
            ],
        ],
        'excel' => [
            'label' => 'Excel',
            'patterns' => [
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.google-apps.spreadsheet',
                'text/csv',
            ],
        ],
        'powerpoint' => [
            'label' => 'PowerPoint',
            'patterns' => [
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/vnd.google-apps.presentation',
            ],
        ],
        'image' => ['label' => 'Gambar', 'prefixes' => ['image/']],
        'video' => ['label' => 'Video', 'prefixes' => ['video/']],
        'audio' => ['label' => 'Audio', 'prefixes' => ['audio/']],
        'archive' => [
            'label' => 'Arsip',
            'patterns' => [
                'application/zip',
                'application/x-zip-compressed',
                'application/x-rar-compressed',
                'application/x-7z-compressed',
                'application/gzip',
                'application/x-tar',
            ],
        ],
    ];

    public function __construct()
    {
        $this->apiKey = (string) config('services.google_drive.api_key');
        $this->folderId = (string) config('services.google_drive.tata_lingkungan_folder_id');
        $this->cacheTtl = (int) config('services.google_drive.cache_ttl', 900);
        $this->maxDepth = (int) config('services.google_drive.max_depth', 8);
    }

    /**
     * URL titik API Drive v3. Api key dipakai untuk file publik
     * ("siapa pun yang memiliki link dapat melihat").
     */
    protected function endpoint(string $path = 'files'): string
    {
        return "https://www.googleapis.com/drive/v3/{$path}";
    }

    protected function baseParams(): array
    {
        return [
            'key' => $this->apiKey,
            'pageSize' => 1000,
            'fields' => 'nextPageToken, files(id, name, mimeType, size, modifiedTime, fileExtension, webViewLink, iconLink, thumbnailLink)',
            'supportsAllDrives' => 'true',
            'includeItemsFromAllDrives' => 'true',
        ];
    }

    /**
     * Ambil struktur folder + metadata file di dalam folder akar secara
     * rekursif. Hanya metadata yang diambil (tanpa konten), lalu di-cache.
     */
    public function listStructure(bool $force = false): array
    {
        $cacheKey = $this->cacheKey();

        if ($force) {
            Cache::forget($cacheKey);
        }

        try {
            return Cache::remember($cacheKey, $this->cacheTtl, function () {
                $root = $this->fetchFileMeta($this->folderId);
                $result = $this->fetchAll($this->folderId, 0, '');

                return [
                    'root' => [
                        'id' => $root['id'] ?? $this->folderId,
                        'name' => $root['name'] ?? 'Dokumen Tata Lingkungan',
                    ],
                    'folders' => $result['folders'],
                    'files' => $result['files'],
                    'fetched_at' => now()->toIso8601String(),
                ];
            });
        } catch (\Throwable $e) {
            // Fallback ke metadata basi bila API sedang bermasalah.
            $stale = Cache::get($cacheKey);
            if (is_array($stale)) {
                return $stale;
            }

            throw $e;
        }
    }

    protected function cacheKey(): string
    {
        return 'google_drive.folder.'.md5($this->folderId);
    }

    /**
     * Metadata satu file/folder (untuk nama folder akar).
     */
    protected function fetchFileMeta(string $fileId): array
    {
        $params = ['key' => $this->apiKey, 'fields' => 'id, name'];

        $response = Http::timeout(30)
            ->acceptJson()
            ->get($this->endpoint('files/'.$fileId), $params);

        if (! $response->successful()) {
            throw new \RuntimeException('Google Drive API '.$response->status().': '.$response->body());
        }

        return $response->json() ?? [];
    }

    /**
     * Jalankan pencarian per folder (dengan pagination via nextPageToken).
     */
    protected function fetchFolderFiles(string $folderId): array
    {
        $files = [];
        $pageToken = null;

        do {
            $params = $this->baseParams();
            $params['q'] = "'{$folderId}' in parents and trashed = false and mimeType != 'application/vnd.google-apps.shortcut'";

            if ($pageToken) {
                $params['pageToken'] = $pageToken;
            }

            $response = Http::timeout(60)
                ->acceptJson()
                ->get($this->endpoint('files'), $params);

            if (! $response->successful()) {
                throw new \RuntimeException('Google Drive API '.$response->status().': '.$response->body());
            }

            $data = $response->json() ?? [];
            $files = array_merge($files, $data['files'] ?? []);
            $pageToken = $data['nextPageToken'] ?? null;
        } while ($pageToken);

        return $files;
    }

    /**
     * Rekursif ke seluruh subfolder: kumpulkan daftar folder
     * dan metadata file (dengan path relatif terhadap folder akar).
     */
    protected function fetchAll(string $folderId, int $depth, string $pathPrefix): array
    {
        if ($depth > $this->maxDepth) {
            return ['folders' => [], 'files' => []];
        }

        $folders = [];
        $files = [];

        foreach ($this->fetchFolderFiles($folderId) as $item) {
            $isFolder = ($item['mimeType'] ?? '') === 'application/vnd.google-apps.folder';
            $relativePath = $pathPrefix === '' ? ($item['name'] ?? '') : $pathPrefix.'/'.($item['name'] ?? '');

            if ($isFolder) {
                $folders[] = [
                    'id' => $item['id'],
                    'name' => $item['name'] ?? '',
                    'parent_id' => $folderId,
                    'path' => $relativePath,
                ];

                $sub = $this->fetchAll($item['id'], $depth + 1, $relativePath);
                $folders = array_merge($folders, $sub['folders']);
                $files = array_merge($files, $sub['files']);

                continue;
            }

            $files[] = [
                'id' => $item['id'],
                'name' => $item['name'] ?? '',
                'mimeType' => $item['mimeType'] ?? '',
                'extension' => $item['fileExtension'] ?? '',
                'size' => isset($item['size']) ? (int) $item['size'] : null,
                'modifiedTime' => $item['modifiedTime'] ?? null,
                'folder_id' => $folderId,
                'path' => $relativePath,
                'webViewLink' => $item['webViewLink'] ?? null,
                'iconLink' => $item['iconLink'] ?? null,
                'thumbnailLink' => $item['thumbnailLink'] ?? null,
                'category' => $this->categorize($item['mimeType'] ?? ''),
            ];
        }

        return ['folders' => $folders, 'files' => $files];
    }

    /**
     * Kategorikan tipe file untuk ikon (kunci const CATEGORIES).
     */
    public function categorize(string $mimeType): string
    {
        foreach (self::CATEGORIES as $key => $def) {
            if (isset($def['patterns']) && in_array($mimeType, $def['patterns'], true)) {
                return $key;
            }
            if (isset($def['prefixes'])) {
                foreach ($def['prefixes'] as $prefix) {
                    if (str_starts_with($mimeType, $prefix)) {
                        return $key;
                    }
                }
            }
        }

        return 'other';
    }

    /**
     * Apakah API key sudah dikonfigurasi.
     */
    public function isConfigured(): bool
    {
        return $this->apiKey !== '' && $this->folderId !== '';
    }
}
