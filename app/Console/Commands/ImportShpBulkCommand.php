<?php

namespace App\Console\Commands;

use App\Models\GisDataLayer;
use App\Services\ShpParserService;
use Illuminate\Console\Command;

class ImportShpBulkCommand extends Command
{
    protected $signature = "shp:import-bulk
                            {folder : Absolute path ke folder yang berisi SHP (bisa nested)}
                            {bidang : bidang data (sampah-lb3 atau rth)}
                            {--dry-run : Hanya tampilkan file yang akan diimport, tidak benar-benar menyimpan}
                            {--skip-existing : Lewati layer yang sudah ada (berdasarkan nama)}
                            {--color= : Warna hex default untuk layer (contoh: #f59e0b)}";

    protected $description = "Bulk import semua file .shp dari folder (termasuk sub-folder) ke GIS Data Layers";

    public function __construct(private ShpParserService $shpParser)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $folder = $this->argument("folder");
        $bidang = $this->argument("bidang");
        $isDryRun = $this->option("dry-run");
        $skipExisting = $this->option("skip-existing");
        $defaultColor = $this->option("color") ?? GisDataLayer::defaultColor($bidang);

        if (! in_array($bidang, ["sampah-lb3", "rth", "pengendalian", "tata-penataan"])) {
            $this->error("Bidang tidak valid. Gunakan: sampah-lb3, rth, pengendalian, atau tata-penataan");
            return 1;
        }

        if (! is_dir($folder)) {
            $this->error("Folder tidak ditemukan: {$folder}");
            return 1;
        }

        $this->info("Scanning folder: {$folder}");
        $this->info("Bidang: {$bidang}");
        if ($isDryRun) {
            $this->warn("DRY RUN mode aktif - tidak ada data yang disimpan");
        }

        $shpFiles = $this->findAllShpFiles($folder);
        $this->info("Ditemukan " . count($shpFiles) . " file .shp\n");

        if (empty($shpFiles)) {
            $this->warn("Tidak ada file .shp yang ditemukan.");
            return 0;
        }

        $successCount = 0;
        $skipCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($shpFiles as $shpPath) {
            $layerName = $this->guessLayerName($shpPath, $folder);
            $relativePath = ltrim(str_replace(str_replace("/", DIRECTORY_SEPARATOR, $folder), "", $shpPath), DIRECTORY_SEPARATOR);

            $this->line("--- {$layerName}");
            $this->line("    " . $relativePath);

            if ($isDryRun) {
                $this->line("    [DRY-RUN] Akan diimport sebagai: '{$layerName}'\n");
                $successCount++;
                continue;
            }

            if ($skipExisting) {
                $exists = GisDataLayer::where("bidang", $bidang)
                    ->where("nama_layer", $layerName)
                    ->withTrashed()
                    ->exists();

                if ($exists) {
                    $this->line("    [SKIP] Sudah ada di database\n");
                    $skipCount++;
                    continue;
                }
            }

            try {
                $features = $this->shpParser->parseShp($shpPath);

                if (empty($features)) {
                    $this->warn("    [SKIP] Tidak ada fitur yang terbaca\n");
                    $skipCount++;
                    continue;
                }

                $swapped = $this->shpParser->validateAndSwapCoordinates($features);
                $jenisGeometri = $this->detectGeometryType($features);
                $finalName = $this->makeUniqueName($layerName, $bidang);

                GisDataLayer::create([
                    "bidang"           => $bidang,
                    "nama_layer"       => $finalName,
                    "deskripsi"        => "Import dari: {$relativePath}",
                    "jenis_geometri"   => $jenisGeometri,
                    "geojson_features" => $features,
                    "metadata"         => [
                        "color"         => $defaultColor,
                        "source_file"   => basename($shpPath),
                        "source_path"   => $relativePath,
                        "feature_count" => count($features),
                        "imported_at"   => now()->toISOString(),
                        "coord_swapped" => $swapped,
                    ],
                ]);

                $featureCount = count($features);
                $geoType = strtoupper($jenisGeometri);
                $swappedNote = $swapped ? " (koordinat ditukar)" : "";
                $this->info("    [OK] Berhasil - {$featureCount} fitur [{$geoType}]{$swappedNote}\n");
                $successCount++;

            } catch (\Throwable $e) {
                $msg = $e->getMessage();
                $this->error("    [GAGAL] {$msg}\n");
                $errors[] = ["file" => $relativePath, "error" => $msg];
                $errorCount++;
            }
        }

        $this->newLine();
        $this->info("=== RINGKASAN IMPORT ===");
        $this->info("Berhasil  : {$successCount}");
        if ($skipCount > 0) {
            $this->warn("Dilewati  : {$skipCount}");
        }
        if ($errorCount > 0) {
            $this->error("Gagal     : {$errorCount}");
            foreach ($errors as $err) {
                $this->error("  * {$err["file"]}: {$err["error"]}");
            }
        }

        return $errorCount > 0 ? 1 : 0;
    }

    private function findAllShpFiles(string $dir): array
    {
        $shpFiles = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (strtolower($file->getExtension()) === "shp") {
                if (str_contains($file->getFilename(), ".sr.lock")) {
                    continue;
                }
                $shpFiles[] = $file->getRealPath();
            }
        }

        sort($shpFiles);
        return $shpFiles;
    }

    private function guessLayerName(string $shpPath, string $baseFolder): string
    {
        $realBase = realpath($baseFolder);
        $realShp  = realpath($shpPath);

        $relative = ltrim(str_replace($realBase, "", $realShp), "/\\");
        $parts = preg_split("/[\/\\\\]/", $relative);
        array_pop($parts); // hapus nama file

        if (empty($parts)) {
            return pathinfo($shpPath, PATHINFO_FILENAME);
        }

        if (count($parts) === 1) {
            $fileName = pathinfo($shpPath, PATHINFO_FILENAME);
            return $fileName ?: $parts[0];
        }

        // Ambil 2 level terakhir folder
        $relevant = array_slice($parts, max(0, count($parts) - 2));
        return implode(" - ", $relevant);
    }

    private function makeUniqueName(string $name, string $bidang): string
    {
        $exists = GisDataLayer::where("bidang", $bidang)
            ->where("nama_layer", $name)
            ->withTrashed()
            ->exists();

        if (! $exists) {
            return $name;
        }

        $counter = 2;
        while (GisDataLayer::where("bidang", $bidang)
            ->where("nama_layer", "{$name} ({$counter})")
            ->withTrashed()
            ->exists()) {
            $counter++;
        }

        return "{$name} ({$counter})";
    }

    private function detectGeometryType(array $features): string
    {
        $types = [];
        foreach ($features as $f) {
            $type = $f["geometry"]["type"] ?? null;
            if ($type) {
                $types[$type] = true;
            }
        }

        $typeKeys = array_keys($types);
        $hasPoint   = ! empty(array_intersect($typeKeys, ["Point", "MultiPoint"]));
        $hasLine    = ! empty(array_intersect($typeKeys, ["LineString", "MultiLineString"]));
        $hasPolygon = ! empty(array_intersect($typeKeys, ["Polygon", "MultiPolygon"]));

        if (($hasPoint + $hasLine + $hasPolygon) > 1) return "mixed";
        if ($hasPolygon) return "polygon";
        if ($hasLine)    return "line";
        return "point";
    }
}
