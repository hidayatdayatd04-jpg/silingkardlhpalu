<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GisDataLayer;
use App\Services\ShpParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PetaController extends Controller
{
    public function __construct(
        private ShpParserService $shpParser = new ShpParserService(),
    ) {}

    /**
     * Show unified peta page with bidang filter
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $adminRole = $user?->adminRole();

        if (! $adminRole) {
            abort(403, 'Unauthorized');
        }

        $isSuperadmin = $adminRole->isSuperadmin();
        $allowedGroups = $adminRole->allowedGroups();

        // Build accessible bidang list (tata-penataan tidak perlu di peta karena data input manual)
        $accessibleBidang = [];
        foreach (['sampah-lb3', 'rth'] as $b) {
            if ($isSuperadmin || in_array($b, $allowedGroups)) {
                $accessibleBidang[] = $b;
            }
        }

        if (empty($accessibleBidang)) {
            abort(403, 'Anda tidak memiliki akses ke peta');
        }

        // Active bidang filter (from query or show all)
        // Support both ?bidang=rth and ?rth for backwards compatibility
        $activeBidang = $request->input('bidang');
        if (! $activeBidang && $request->has('rth')) {
            $activeBidang = 'rth';
        }
        if (! $activeBidang && $request->has('sampah-lb3')) {
            $activeBidang = 'sampah-lb3';
        }
        if ($activeBidang && ! in_array($activeBidang, $accessibleBidang)) {
            $activeBidang = null;
        }

        $excludedLayers = ['Taman Kota', 'Hutan Kota', 'Jalur Hijau', 'Pohon Pelindung', 'Aset RTH'];

        $query = GisDataLayer::whereIn('bidang', $accessibleBidang)
            ->whereNotIn('nama_layer', $excludedLayers)
            ->orderBy('bidang')
            ->orderBy('z_index')
            ->orderBy('created_at');

        if ($activeBidang) {
            $query->where('bidang', $activeBidang);
        }

        $layers = $query->get();

        return view('admin.peta.index', compact('layers', 'accessibleBidang', 'activeBidang', 'isSuperadmin'));
    }

    /**
     * Get layers as GeoJSON (API) - supports all accessible bidang
     */
    public function layers(Request $request)
    {
        $user = auth()->user();
        $adminRole = $user?->adminRole();

        if (! $adminRole) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $isSuperadmin = $adminRole->isSuperadmin();
        $allowedGroups = $adminRole->allowedGroups();

        $accessibleBidang = [];
        foreach (['sampah-lb3', 'rth'] as $b) {
            if ($isSuperadmin || in_array($b, $allowedGroups)) {
                $accessibleBidang[] = $b;
            }
        }

        $activeBidang = $request->input('bidang');
        if ($activeBidang && ! in_array($activeBidang, $accessibleBidang)) {
            $activeBidang = null;
        }

        $excludedLayers = ['Taman Kota', 'Hutan Kota', 'Jalur Hijau', 'Pohon Pelindung', 'Aset RTH'];

        $query = GisDataLayer::whereIn('bidang', $accessibleBidang)->whereNotIn('nama_layer', $excludedLayers)->visible()->orderBy('z_index');

        if ($activeBidang) {
            $query->where('bidang', $activeBidang);
        }

        $layers = $query->get();

        $collections = $layers->map(fn ($layer) => [
            'id' => $layer->id,
            'nama_layer' => $layer->nama_layer,
            'deskripsi' => $layer->deskripsi,
            'bidang' => $layer->bidang,
            'jenis_geometri' => $layer->jenis_geometri,
            'metadata' => $layer->metadata ?? ['color' => GisDataLayer::defaultColor($layer->bidang)],
            'is_visible' => $layer->is_visible,
            'geojson' => $layer->toGeoJson(),
        ]);

        return response()->json($collections);
    }

    /**
     * Import GIS data from uploaded file
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:50000', // 50MB max
            'bidang' => 'required|string|in:sampah-lb3,rth',
            'nama_layer' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:7',
        ]);

        $bidang = $request->input('bidang');
        $this->authorizeBidang($bidang);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $tempPath = $file->store('temp/gis', 'local');

        try {
            $realPath = Storage::disk('local')->path($tempPath);
            $color = $request->input('color', GisDataLayer::defaultColor($bidang));
            $deskripsi = $request->input('deskripsi');

            // ═══ ZIP: bisa multi-layer (satu subfolder = satu layer) ═══
            if ($extension === 'zip') {
                $layerDatas = $this->parseShpZipLayers($realPath, $file->getClientOriginalName());
                $createdLayers = [];
                $totalFeatures = 0;

                foreach ($layerDatas as $layerData) {
                    $features = $layerData['features'];
                    $this->shpParser->validateAndSwapCoordinates($features);
                    $jenisGeometri = $this->detectGeometryType($features);

                    $layer = GisDataLayer::create([
                        'bidang' => $bidang,
                        'nama_layer' => $layerData['name'],
                        'deskripsi' => $deskripsi,
                        'jenis_geometri' => $jenisGeometri,
                        'geojson_features' => $features,
                        'metadata' => [
                            'color' => $color,
                            'source_file' => $file->getClientOriginalName(),
                            'feature_count' => count($features),
                            'imported_at' => now()->toISOString(),
                        ],
                    ]);

                    $createdLayers[] = [
                        'id' => $layer->id,
                        'nama_layer' => $layer->nama_layer,
                        'jenis_geometri' => $jenisGeometri,
                        'feature_count' => count($features),
                    ];
                    $totalFeatures += count($features);
                }

                Storage::disk('local')->delete($tempPath);

                $msg = "Berhasil import " . count($createdLayers) . " layer ({$totalFeatures} fitur total)";
                $shpWarning = request()->attributes->get('shp_warning');
                if ($shpWarning) {
                    $msg .= ". Catatan: " . $shpWarning;
                }

                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'layer' => $createdLayers[0] ?? null,
                    'layers' => $createdLayers,
                ]);
            }

            // ═══ File tunggal (.shp, .geojson, .kml, .csv) ═══
            $features = match ($extension) {
                'shp' => $this->shpParser->parseShp($realPath),
                'geojson', 'json' => $this->shpParser->parseGeoJson($realPath),
                'kml' => $this->shpParser->parseKml($realPath),
                'csv' => $this->shpParser->parseCsv($realPath),
                default => throw new \RuntimeException("Format tidak didukung: {$extension}"),
            };

            $swapped = $this->shpParser->validateAndSwapCoordinates($features);
            $jenisGeometri = $this->detectGeometryType($features);

            $layer = GisDataLayer::create([
                'bidang' => $bidang,
                'nama_layer' => $request->input('nama_layer'),
                'deskripsi' => $deskripsi,
                'jenis_geometri' => $jenisGeometri,
                'geojson_features' => $features,
                'metadata' => [
                    'color' => $color,
                    'source_file' => $file->getClientOriginalName(),
                    'feature_count' => count($features),
                    'imported_at' => now()->toISOString(),
                ],
            ]);

            Storage::disk('local')->delete($tempPath);

            $msg = "Berhasil import {$layer->nama_layer} (" . count($features) . " fitur)";
            $warnings = [];
            if ($swapped) {
                $warnings[] = "Koordinat otomatis ditukar karena terdeteksi terbalik (Lat/Lng)";
            }
            if (request()->attributes->get('utm_guessed')) {
                $warnings[] = "Proyeksi UTM dideteksi tanpa file .prj, diasumsikan UTM Zone 50S";
            }
            if ($shpWarning = request()->attributes->get('shp_warning')) {
                $warnings[] = $shpWarning;
            }
            if (! empty($warnings)) {
                $msg .= ". Catatan: " . implode(', ', $warnings);
            }

            return response()->json([
                'success' => true,
                'message' => $msg,
                'layer' => [
                    'id' => $layer->id,
                    'nama_layer' => $layer->nama_layer,
                    'jenis_geometri' => $jenisGeometri,
                    'feature_count' => count($features),
                ],
            ]);
        } catch (\Exception $e) {
            Storage::disk('local')->delete($tempPath);
            return response()->json([
                'success' => false,
                'message' => 'Gagal import: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update layer metadata (visibility, color, etc)
     */
    public function updateLayer(Request $request, GisDataLayer $layer)
    {
        $this->authorizeBidang($layer->bidang);

        $validated = $request->validate([
            'nama_layer' => 'sometimes|string|max:255',
            'deskripsi'  => 'nullable|string|max:500',
            'is_visible' => 'sometimes|boolean',
            'is_public'  => 'sometimes|boolean',
            'color'      => 'nullable|string|max:7',
            'z_index'    => 'sometimes|integer|min:0|max:100',
        ]);

        $metadata = $layer->metadata ?? [];
        if (isset($validated['color'])) {
            $metadata['color'] = $validated['color'];
            unset($validated['color']);
        }

        $validated['metadata'] = $metadata;
        $layer->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Layer berhasil diupdate',
        ]);
    }

    /**
     * Delete a layer (soft delete)
     */
    public function destroyLayer(GisDataLayer $layer)
    {
        $this->authorizeBidang($layer->bidang);

        $layerId = $layer->id;
        $layer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Layer berhasil dihapus',
            'layer_id' => $layerId,
        ]);
    }

    /**
     * Delete multiple layers (soft delete)
     */
    public function bulkDestroyLayers(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:gis_data_layers,id',
        ]);

        $ids = $request->input('ids');
        $deletedIds = [];

        foreach ($ids as $id) {
            $layer = GisDataLayer::findOrFail($id);
            try {
                $this->authorizeBidang($layer->bidang);
                $layer->delete();
                $deletedIds[] = $id;
            } catch (\Exception $e) {
                // Ignore unauthorized
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($deletedIds) . ' layer berhasil dihapus',
            'deleted_ids' => $deletedIds,
        ]);
    }

    /**
     * Toggle visibility for all accessible layers
     */
    public function bulkVisibility(Request $request)
    {
        $request->validate([
            'visible' => 'required|boolean',
        ]);

        $user = auth()->user();
        $adminRole = $user?->adminRole();
        if (! $adminRole) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $isSuperadmin = $adminRole->isSuperadmin();
        $allowedGroups = $adminRole->allowedGroups();

        $accessibleBidang = [];
        foreach (['sampah-lb3', 'rth'] as $b) {
            if ($isSuperadmin || in_array($b, $allowedGroups)) {
                $accessibleBidang[] = $b;
            }
        }

        $visible = $request->input('visible');
        GisDataLayer::whereIn('bidang', $accessibleBidang)->update(['is_visible' => $visible]);

        return response()->json([
            'success' => true,
            'message' => $visible ? 'Semua layer ditampilkan' : 'Semua layer disembunyikan',
        ]);
    }

    /**
     * Restore a soft-deleted layer
     */
    public function restoreLayer(int $layerId)
    {
        $layer = GisDataLayer::onlyTrashed()->findOrFail($layerId);
        $this->authorizeBidang($layer->bidang);

        $layer->restore();

        return response()->json([
            'success' => true,
            'message' => 'Layer berhasil dipulihkan',
            'layer' => [
                'id' => $layer->id,
                'nama_layer' => $layer->nama_layer,
                'jenis_geometri' => $layer->jenis_geometri,
                'is_visible' => $layer->is_visible,
                'geojson' => $layer->toGeoJson(),
                'metadata' => $layer->metadata,
            ]
        ]);
    }

    /**
     * Save drawn features (from MapLibre GL Draw)
     */
    public function saveDrawnFeatures(Request $request)
    {
        $request->validate([
            'layer_id' => 'required|exists:gis_data_layers,id',
            'features' => 'required|array',
            'features.*.type' => 'required|in:Feature',
            'features.*.geometry' => 'required|array',
            'features.*.geometry.type' => 'required|string',
            'features.*.geometry.coordinates' => 'required|array',
        ]);

        $layer = GisDataLayer::findOrFail($request->input('layer_id'));
        $this->authorizeBidang($layer->bidang);

        $featuresInput = $request->input('features');
        $swapped = $this->shpParser->validateAndSwapCoordinates($featuresInput);

        $existing = $layer->geojson_features ?? [];
        $newFeatures = array_merge($existing, $featuresInput);

        $layer->update([
            'geojson_features' => $newFeatures,
            'jenis_geometri' => $this->detectGeometryType($newFeatures),
            'metadata' => array_merge($layer->metadata ?? [], [
                'feature_count' => count($newFeatures),
                'last_drawn_at' => now()->toISOString(),
            ]),
        ]);

        $msg = count($request->input('features')) . " fitur berhasil disimpan";
        if ($swapped) {
            $msg .= " (Koordinat otomatis ditukar karena terbalik)";
        }

        return response()->json([
            'success' => true,
            'message' => $msg,
        ]);
    }

    /**
     * Delete a specific feature from a layer
     */
    public function deleteFeature(Request $request, GisDataLayer $layer)
    {
        $this->authorizeBidang($layer->bidang);

        $request->validate([
            'feature_index' => 'required|integer|min:0',
        ]);

        $features = $layer->geojson_features ?? [];
        $index = $request->input('feature_index');

        if ($index >= count($features)) {
            return response()->json(['success' => false, 'message' => 'Fitur tidak ditemukan'], 404);
        }

        array_splice($features, $index, 1);

        $layer->update([
            'geojson_features' => $features,
            'jenis_geometri' => $this->detectGeometryType($features),
            'metadata' => array_merge($layer->metadata ?? [], [
                'feature_count' => count($features),
            ]),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Fitur berhasil dihapus',
        ]);
    }

    /**
     * Update a specific feature within a layer
     */
    public function updateFeature(Request $request, GisDataLayer $layer, int $featureIndex)
    {
        $this->authorizeBidang($layer->bidang);

        $request->validate([
            'properties' => 'sometimes|array',
            'geometry' => 'sometimes|array',
            'geometry.coordinates' => 'sometimes|array',
            'marker_type' => 'nullable|string|max:50',
        ]);

        $features = $layer->geojson_features ?? [];

        if ($featureIndex >= count($features)) {
            return response()->json(['success' => false, 'message' => 'Fitur tidak ditemukan'], 404);
        }

        // Update properties (merge, not replace — allows adding new fields)
        if ($request->has('properties')) {
            $features[$featureIndex]['properties'] = array_merge(
                $features[$featureIndex]['properties'] ?? [],
                $request->input('properties')
            );
        }

        // Update geometry coordinates & validate swap
        if ($request->has('geometry.coordinates')) {
            $coords = $request->input('geometry.coordinates');
            if (count($coords) >= 2) {
                $lng = (float) $coords[0];
                $lat = (float) $coords[1];
                if ($lng < 0 && $lat > 0) {
                    $coords = [$lat, $lng]; // swap
                }
            }
            $features[$featureIndex]['geometry']['coordinates'] = $coords;
        }

        // Store marker_type override in properties
        if ($request->has('marker_type')) {
            $features[$featureIndex]['properties']['_marker_type'] = $request->input('marker_type');
        }

        $layer->update([
            'geojson_features' => $features,
            'metadata' => array_merge($layer->metadata ?? [], [
                'last_edited_at' => now()->toISOString(),
            ]),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Fitur berhasil diupdate',
            'feature' => $features[$featureIndex],
        ]);
    }

    // ═══════════════ Private helpers ═══════════════

    /**
     * Parse zip shapefile → array of ['name' => ..., 'features' => ...]
     * Setiap subfolder dengan .shp jadi 1 layer terpisah.
     */
    private function parseShpZipLayers(string $filePath, string $originalName): array
    {
        $tempDir = sys_get_temp_dir() . '/shp_' . uniqid();
        mkdir($tempDir, 0755, true);

        $this->shpParser->extractZip($filePath, $tempDir);

        // Step 1: Scan semua file, temukan .shp dan geojson
        $shpGroups = []; // [dirPath => shpPath]
        $geojsonSingles = []; // [filePath => name]
        $dirsHavingShp = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($tempDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            $ext = strtolower($file->getExtension());
            $realPath = $file->getRealPath();
            $basename = strtolower(pathinfo($file->getFilename(), PATHINFO_FILENAME));

            if (str_contains($basename, 'gabungan') || str_contains($basename, 'combined') || str_contains($basename, 'merged')) {
                continue;
            }

            if ($ext === 'shp') {
                $dir = dirname($realPath);
                $shpGroups[$dir] = $realPath;
                $dirsHavingShp[$dir] = true;
            }
        }

        // Step 2: Scan geojson — hanya yang BUKAN di folder yang punya .shp
        // Dan namanya TIDAK sama dengan nama folder yang punya .shp
        $shpDirNames = array_map(fn($d) => strtolower(basename($d)), array_keys($dirsHavingShp));

        $iterator2 = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($tempDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($iterator2 as $file) {
            $ext = strtolower($file->getExtension());
            if (! in_array($ext, ['geojson', 'json'])) continue;

            $realPath = $file->getRealPath();
            $basename = strtolower(pathinfo($file->getFilename(), PATHINFO_FILENAME));
            if (str_contains($basename, 'gabungan') || str_contains($basename, 'combined') || str_contains($basename, 'merged')) continue;

            // Skip jika folder ini punya .shp
            $dir = dirname($realPath);
            if (isset($dirsHavingShp[$dir])) continue;

            // Skip jika namanya sama dengan folder shapefile yang sudah diproses
            $name = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            if (in_array(strtolower($name), $shpDirNames)) continue;

            $geojsonSingles[$realPath] = $name;
        }

        $layers = [];

        // Step 3: Proses shapefile groups (per kelurahan)
        foreach ($shpGroups as $dir => $shpPath) {
            $name = basename($dir);
            try {
                $this->shpParser->normalizeCompanionFiles($shpPath);
                $features = $this->shpParser->parseShp($shpPath);
                if (! empty($features)) {
                    $layers[] = ['name' => $name, 'features' => $features];
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        // Step 4: Proses standalone geojson files
        foreach ($geojsonSingles as $path => $name) {
            try {
                $features = $this->shpParser->parseGeoJson($path);
                if (! empty($features)) {
                    $layers[] = ['name' => $name, 'features' => $features];
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        $this->cleanupDir($tempDir);

        if (empty($layers)) {
            throw new \RuntimeException("Tidak ada data GIS yang valid ditemukan dalam archive");
        }

        $layerNames = array_map(fn($l) => $l['name'], $layers);
        request()->attributes->set('shp_warning',
            count($layers) > 1
                ? "Ditemukan " . count($layers) . " layer: " . implode(', ', array_slice($layerNames, 0, 10)) . (count($layerNames) > 10 ? ' ...' : '')
                : null
        );

        return $layers;
    }

    /**
     * Cari file geojson gabungan/combined (biasanya nama mengandung "gabungan" atau "combined")
     */
    private function findCombinedGeojson(string $dir): ?string
    {
        $allGeojson = $this->findAllFilesRecursive($dir, ['.geojson', '.json']);
        if (empty($allGeojson)) return null;

        // Cari yang namanya mengandung "gabungan" atau "combined"
        foreach ($allGeojson as $file) {
            $basename = strtolower(basename($file));
            if (str_contains($basename, 'gabungan') || str_contains($basename, 'combined') || str_contains($basename, 'merged')) {
                // Validasi isinya GeoJSON
                $content = file_get_contents($file);
                if ($content === false) continue;
                $data = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($data['type']) && $data['type'] === 'FeatureCollection') {
                    return $file;
                }
            }
        }

        // Fallback: file geojson terbesar (kemungkinan gabungan)
        $largest = null;
        $largestSize = 0;
        foreach ($allGeojson as $file) {
            $size = filesize($file);
            if ($size > $largestSize) {
                $content = file_get_contents($file);
                if ($content === false) continue;
                $data = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($data['type']) && $data['type'] === 'FeatureCollection') {
                    $largest = $file;
                    $largestSize = $size;
                }
            }
        }

        return $largest;
    }

    /**
     * Cari semua file dengan extension tertentu secara recursive
     */
    private function findAllFilesRecursive(string $dir, array $extensions): array
    {
        $results = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $extLower = array_map(fn($e) => strtolower(ltrim($e, '.')), $extensions);

        foreach ($iterator as $file) {
            if (in_array(strtolower($file->getExtension()), $extLower)) {
                $results[] = $file->getRealPath();
            }
        }

        return $results;
    }

    private function detectGeometryType(array $features): string
    {
        $types = [];
        foreach ($features as $f) {
            $type = $f['geometry']['type'] ?? null;
            if ($type) {
                $types[$type] = true;
            }
        }

        $typeKeys = array_keys($types);
        $pointTypes = ['Point', 'MultiPoint'];
        $lineTypes = ['LineString', 'MultiLineString'];
        $polygonTypes = ['Polygon', 'MultiPolygon'];

        $hasPoint = ! empty(array_intersect($typeKeys, $pointTypes));
        $hasLine = ! empty(array_intersect($typeKeys, $lineTypes));
        $hasPolygon = ! empty(array_intersect($typeKeys, $polygonTypes));

        if (($hasPoint + $hasLine + $hasPolygon) > 1) return 'mixed';
        if ($hasPolygon) return 'polygon';
        if ($hasLine) return 'line';
        return 'point';
    }

    private function authorizeBidang(string $bidang): void
    {
        $user = auth()->user();
        $adminRole = $user?->adminRole();

        if (! $adminRole) {
            abort(403, 'Unauthorized');
        }

        // Superadmin akses semua
        if ($adminRole->isSuperadmin()) return;

        // Cek apakah role boleh akses bidang ini
        $allowedGroups = $adminRole->allowedGroups();
        $bidangToGroup = [
            'pengendalian' => 'pengendalian',
            'sampah-lb3' => 'sampah-lb3',
            'rth' => 'rth',
            'tata-penataan' => 'tata-penataan',
        ];

        $requiredGroup = $bidangToGroup[$bidang] ?? null;
        if ($requiredGroup && in_array($requiredGroup, $allowedGroups)) {
            return;
        }

        abort(403, 'Anda tidak memiliki akses ke bidang ini');
    }

    private function findFileRecursive(string $dir, string $extension): ?string
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $extLower = strtolower(ltrim($extension, '.'));
        foreach ($iterator as $file) {
            if (strtolower($file->getExtension()) === $extLower) {
                return $file->getRealPath();
            }
        }

        return null;
    }

    private function cleanupDir(string $dir): void
    {
        if (! is_dir($dir)) return;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $file) {
            if ($file->isDir()) rmdir($file->getRealPath());
            else unlink($file->getRealPath());
        }
        rmdir($dir);
    }
}
