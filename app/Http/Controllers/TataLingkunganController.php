<?php

namespace App\Http\Controllers;

use App\Services\GoogleDriveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TataLingkunganController extends Controller
{
    public function __construct(protected GoogleDriveService $drive)
    {
    }

    public function index()
    {
        return view('public.tata-lingkungan');
    }

    /**
     * API publik: struktur folder Google Drive (untuk pohon folder).
     */
    public function folders(Request $request): JsonResponse
    {
        if (! $this->drive->isConfigured()) {
            return response()->json([
                'error' => 'not_configured',
                'message' => 'Google Drive API belum dikonfigurasi. Hubungi administrator.',
            ], 503);
        }

        try {
            $structure = $this->drive->listStructure($request->boolean('refresh') && auth()->check());
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'error' => 'drive_unavailable',
                'message' => 'Gagal menghubungi Google Drive API. Silakan coba lagi beberapa saat.',
            ], 502);
        }

        return response()->json([
            'error' => null,
            'root' => $structure['root'],
            'folders' => $structure['folders'],
            'total_files' => count($structure['files']),
            'cached_at' => $structure['fetched_at'] ?? null,
        ]);
    }

    /**
     * API publik: daftar file di dalam satu folder (dengan pagination).
     */
    public function files(Request $request): JsonResponse
    {
        if (! $this->drive->isConfigured()) {
            return response()->json([
                'error' => 'not_configured',
                'message' => 'Google Drive API belum dikonfigurasi. Hubungi administrator.',
            ], 503);
        }

        try {
            // Refresh cache (memaksa fetch ulang ke Google Drive API) hanya
            // boleh dipicu pengguna terautentikasi — pengunjung anonim cukup
            // membaca cache agar kuota API tidak bisa dikuras dari luar.
            $structure = $this->drive->listStructure($request->boolean('refresh') && auth()->check());
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'error' => 'drive_unavailable',
                'message' => 'Gagal menghubungi Google Drive API. Silakan coba lagi beberapa saat.',
            ], 502);
        }

        $rootId = $structure['root']['id'];
        $folderId = (string) $request->input('folder_id', $rootId);
        $search = mb_substr(trim((string) $request->input('search', '')), 0, 120);

        $list = array_values(array_filter($structure['files'], function (array $file) use ($folderId, $search): bool {
            if ($search === '') {
                return ($file['folder_id'] ?? null) === $folderId;
            }

            $needle = mb_strtolower($search);
            $haystack = mb_strtolower(implode(' ', [
                $file['name'] ?? '',
                $file['path'] ?? '',
                $file['extension'] ?? '',
            ]));

            return str_contains($haystack, $needle);
        }));

        // Saat mencari, nama yang diawali kata kunci tampil lebih dahulu;
        // setelah itu tetap diurutkan menurut nama agar mudah dipindai.
        usort($list, function (array $a, array $b) use ($search): int {
            if ($search !== '') {
                $needle = mb_strtolower($search);
                $aStartsWith = str_starts_with(mb_strtolower($a['name'] ?? ''), $needle);
                $bStartsWith = str_starts_with(mb_strtolower($b['name'] ?? ''), $needle);

                if ($aStartsWith !== $bStartsWith) {
                    return $aStartsWith ? -1 : 1;
                }
            }

            return strcasecmp($a['name'] ?? '', $b['name'] ?? '');
        });

        $total = count($list);
        $perPage = min(max((int) $request->input('per_page', 60), 1), 200);
        $page = max((int) $request->input('page', 1), 1);
        $offset = ($page - 1) * $perPage;
        $paged = array_slice($list, $offset, $perPage);

        return response()->json([
            'error' => null,
            'files' => array_values($paged),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'has_more' => ($offset + count($paged)) < $total,
            'search' => $search,
            'cached_at' => $structure['fetched_at'] ?? null,
        ]);
    }
}
