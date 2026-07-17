<?php

namespace App\Services;

use RuntimeException;

class ShpParserService
{
    /**
     * Parse a .shp + .dbf file pair and return GeoJSON features.
     *
     * @param string $shpPath Path to .shp file
     * @param string|null $dbfPath Path to .dbf file (auto-detected if null)
     * @return array GeoJSON features array
     */
    public function parseShp(string $shpPath, ?string $dbfPath = null): array
    {
        if (! file_exists($shpPath)) {
            throw new RuntimeException("SHP file not found: {$shpPath}");
        }

        // Cari companion files secara case-insensitive (Linux compatibility)
        $dbfPath ??= $this->findCompanionFile($shpPath, '.dbf');
        $prjPath = $this->findCompanionFile($shpPath, '.prj');
        $cpgPath = $this->findCompanionFile($shpPath, '.cpg');

        // Baca encoding dari .cpg jika ada
        $cpgEncoding = null;
        if ($cpgPath && file_exists($cpgPath)) {
            $cpgEncoding = strtolower(trim(file_get_contents($cpgPath)));
        }

        $attributes = ($dbfPath && file_exists($dbfPath)) ? $this->parseDbf($dbfPath, $cpgEncoding) : [];

        // Deteksi proyeksi dari file .prj jika ada
        $utmZone = null;
        $isSouth = true;
        $isProjected = false;
        if ($prjPath && file_exists($prjPath)) {
            $prjContent = file_get_contents($prjPath);
            if (stripos($prjContent, 'PROJCS') !== false) {
                $isProjected = true;
                if (preg_match('/UTM\s+zone\s+(\d+)([NSns])/i', $prjContent, $matches)) {
                    $utmZone = (int) $matches[1];
                    $isSouth = strtolower($matches[2]) === 's';
                }
            }
        }

        $handle = fopen($shpPath, 'rb');
        if (! $handle) {
            throw new RuntimeException("Cannot open SHP file: {$shpPath}");
        }

        // Read SHP header
        $header = $this->readShpHeader($handle);
        $features = [];
        $recordIndex = 0;

        while (! feof($handle)) {
            $recordHeader = @fread($handle, 8);
            if (strlen($recordHeader) < 8) {
                break;
            }

            $recordNumber = unpack('N', substr($recordHeader, 0, 4))[1];
            $contentLength = unpack('N', substr($recordHeader, 4, 4))[1];

            $content = fread($handle, $contentLength * 2);
            if (strlen($content) < $contentLength * 2) {
                break;
            }

            $shapeType = unpack('V', substr($content, 0, 4))[1];

            $geometry = match ($shapeType) {
                0 => null, // Null Shape
                1, 11, 21 => $this->readPoint($content), // Point
                3, 13, 23 => $this->readPolyline($content, true), // Polyline
                5, 15, 25 => $this->readPolygon($content), // Polygon
                8, 18, 28 => $this->readMultiPoint($content), // MultiPoint
                default => null,
            };

            if ($geometry !== null) {
                $attr = $attributes[$recordIndex] ?? [];
                $features[] = [
                    'type' => 'Feature',
                    'geometry' => $geometry,
                    'properties' => array_merge(['_record' => $recordNumber], $attr),
                ];
            }

            $recordIndex++;
        }

        fclose($handle);

        // Reproyeksi UTM jika koordinat berada di luar rentang derajat geografis WGS84
        $needsReprojection = false;
        if (! empty($features)) {
            $firstGeo = $features[0]['geometry'] ?? null;
            if ($firstGeo && isset($firstGeo['coordinates'])) {
                $coords = $firstGeo['coordinates'];
                while (is_array($coords) && is_array($coords[0])) {
                    $coords = $coords[0];
                }
                if (is_array($coords) && count($coords) >= 2) {
                    $x = (float) $coords[0];
                    $y = (float) $coords[1];
                    if ($x < -180 || $x > 180 || $y < -90 || $y > 90) {
                        $needsReprojection = true;
                    }
                }
            }
        }

        if ($needsReprojection) {
            $zone = $utmZone ?? 50; // Fallback ke Zone 50S (untuk area Palu)
            $south = $isSouth;
            foreach ($features as &$f) {
                if (isset($f['geometry']['coordinates'])) {
                    $this->convertCoordsRecursive($f['geometry']['coordinates'], $zone, $south);
                }
            }
            // Tambahkan flag warning ke request jika menebak zona
            if (empty($utmZone)) {
                // Store warning safely — request() mungkin tidak tersedia di CLI
                try {
                    request()->attributes->set('utm_guessed', true);
                } catch (\Throwable $e) {
                    // Ignore — outside HTTP context
                }
            }
        }

        return $features;
    }

    /**
     * Parse uploaded zip containing SHP files
     */
    public function parseZip(string $zipPath): array
    {
        if (! file_exists($zipPath)) {
            throw new RuntimeException("ZIP file not found: {$zipPath}");
        }

        $tempDir = sys_get_temp_dir() . '/shp_' . uniqid();
        if (! mkdir($tempDir, 0755, true)) {
            throw new RuntimeException("Cannot create temp directory");
        }

        $this->extractZip($zipPath, $tempDir);

        // Find .shp file
        $shpFile = $this->findFileRecursive($tempDir, '.shp');
        if (! $shpFile) {
            $this->cleanupDir($tempDir);
            throw new RuntimeException("No .shp file found in archive");
        }

        $features = $this->parseShp($shpFile);
        $this->cleanupDir($tempDir);

        return $features;
    }

    /**
     * Extract ZIP file to directory (works without ZipArchive extension)
     */
    public function extractZip(string $zipPath, string $destDir): void
    {
        if (class_exists('ZipArchive')) {
            $zip = new \ZipArchive();
            if ($zip->open($zipPath) === true) {
                $zip->extractTo($destDir);
                $zip->close();
                return;
            }
        }

        // Pure PHP fallback: manual ZIP extraction
        $this->extractZipPure($zipPath, $destDir);
    }

    /**
     * Pure PHP ZIP extraction (stored + deflated methods)
     */
    private function extractZipPure(string $zipPath, string $destDir): void
    {
        $handle = fopen($zipPath, 'rb');
        if (! $handle) {
            throw new RuntimeException("Cannot open ZIP file for reading");
        }

        // Find End of Central Directory record (EOCD)
        $fileSize = filesize($zipPath);
        $eocdPos = max(0, $fileSize - 65557);
        fseek($handle, $eocdPos);
        $tail = fread($handle, $fileSize - $eocdPos);
        $eocdOffset = strrpos($tail, "PK\x05\x06");
        if ($eocdOffset === false) {
            fclose($handle);
            throw new RuntimeException("Invalid ZIP file: EOCD not found");
        }

        $eocdAbsPos = $eocdPos + $eocdOffset;
        fseek($handle, $eocdAbsPos + 10);
        $numEntries = unpack('v', fread($handle, 2))[1];
        $cdSize = unpack('V', fread($handle, 4))[1];
        $cdOffset = unpack('V', fread($handle, 4))[1];

        // Read Central Directory
        fseek($handle, $cdOffset);
        for ($i = 0; $i < $numEntries; $i++) {
            $entry = fread($handle, 46);
            if (strlen($entry) < 46) break;

            $sig = substr($entry, 0, 4);
            if ($sig !== "PK\x01\x02") break;

            $compMethod = unpack('v', substr($entry, 10, 2))[1];
            $compSize = unpack('V', substr($entry, 20, 4))[1];
            $uncompSize = unpack('V', substr($entry, 24, 4))[1];
            $nameLen = unpack('v', substr($entry, 28, 2))[1];
            $extraLen = unpack('v', substr($entry, 30, 2))[1];
            $commentLen = unpack('v', substr($entry, 32, 2))[1];
            $localOffset = unpack('V', substr($entry, 42, 4))[1];

            $fileName = fread($handle, $nameLen);
            fread($handle, $extraLen + $commentLen);

            // Skip directories
            if (substr($fileName, -1) === '/') {
                $dirPath = $destDir . '/' . str_replace('\\', '/', $fileName);
                if (! is_dir($dirPath)) {
                    mkdir($dirPath, 0755, true);
                }
                continue;
            }

            // Read local file header to get data offset
            fseek($handle, $localOffset + 26);
            $localNameLen = unpack('v', fread($handle, 2))[1];
            $localExtraLen = unpack('v', fread($handle, 2))[1];
            $dataOffset = $localOffset + 30 + $localNameLen + $localExtraLen;

            // Read compressed data
            fseek($handle, $dataOffset);
            $compData = fread($handle, $compSize);

            // Decompress
            if ($compMethod === 0) {
                // Stored (no compression)
                $data = $compData;
            } elseif ($compMethod === 8) {
                // Deflated
                $data = @gzuncompress($compData);
                if ($data === false) {
                    // Try raw deflate (without zlib header)
                    $data = @gzinflate($compData);
                }
                if ($data === false) {
                    throw new RuntimeException("Cannot decompress: {$fileName}");
                }
            } else {
                throw new RuntimeException("Unsupported compression method {$compMethod} for: {$fileName}");
            }

            // Write file
            $filePath = $destDir . '/' . str_replace('\\', '/', $fileName);
            $fileDir = dirname($filePath);
            if (! is_dir($fileDir)) {
                mkdir($fileDir, 0755, true);
            }
            file_put_contents($filePath, $data);
        }

        fclose($handle);
    }

    /**
     * Parse a GeoJSON file
     */
    public function parseGeoJson(string $filePath): array
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new RuntimeException("Cannot read GeoJSON file");
        }

        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Invalid GeoJSON: " . json_last_error_msg());
        }

        if (isset($data['type']) && $data['type'] === 'FeatureCollection') {
            return $data['features'] ?? [];
        }

        if (isset($data['type']) && $data['type'] === 'Feature') {
            return [$data];
        }

        // Single geometry
        return [
            [
                'type' => 'Feature',
                'geometry' => $data,
                'properties' => [],
            ],
        ];
    }

    /**
     * Parse a KML file
     */
    public function parseKml(string $filePath): array
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new RuntimeException("Cannot read KML file");
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content);
        if ($xml === false) {
            throw new RuntimeException("Invalid KML: " . libxml_get_errors()[0]->message);
        }

        $features = [];
        $kmlNs = $xml->getNamespaces(true);

        // Register namespaces
        foreach ($kmlNs as $prefix => $uri) {
            SimpleXMLElement::registerXPathNamespace($prefix, $uri);
        }

        $placemarks = $xml->xpath('//Placemark') ?? [];
        foreach ($placemarks as $pm) {
            $name = (string) ($pm->name ?? '');
            $desc = (string) ($pm->description ?? '');
            $geometry = null;

            // Point
            if (isset($pm->Point)) {
                $coords = trim((string) $pm->Point->coordinates);
                $parts = array_map('floatval', explode(',', $coords));
                $geometry = [
                    'type' => 'Point',
                    'coordinates' => [$parts[0], $parts[1]],
                ];
            }

            // LineString
            if (isset($pm->LineString)) {
                $coords = trim((string) $pm->LineString->coordinates);
                $geometry = $this->parseKmlCoordinates($coords, true);
            }

            // Polygon
            if (isset($pm->Polygon)) {
                $geometry = $this->parseKmlPolygon($pm->Polygon);
            }

            if ($geometry) {
                $features[] = [
                    'type' => 'Feature',
                    'geometry' => $geometry,
                    'properties' => [
                        'name' => $name,
                        'description' => $desc,
                    ],
                ];
            }
        }

        return $features;
    }

    /**
     * Parse CSV with lat/lng columns
     */
    public function parseCsv(string $filePath, string $latCol = 'lat', string $lngCol = 'lng'): array
    {
        $handle = fopen($filePath, 'r');
        if (! $handle) {
            throw new RuntimeException("Cannot open CSV file");
        }

        $headers = fgetcsv($handle);
        if (! $headers) {
            fclose($handle);
            throw new RuntimeException("Empty CSV file");
        }

        // Find lat/lng columns (case-insensitive)
        $headersLower = array_map('strtolower', $headers);
        $latIdx = array_search(strtolower($latCol), $headersLower);
        $lngIdx = array_search(strtolower($lngCol), $headersLower);

        // Auto-detect common column names
        if ($latIdx === false) {
            foreach (['latitude', 'lat', 'y', 'lintang'] as $name) {
                $latIdx = array_search($name, $headersLower);
                if ($latIdx !== false) break;
            }
        }
        if ($lngIdx === false) {
            foreach (['longitude', 'lng', 'lon', 'x', 'bujur'] as $name) {
                $lngIdx = array_search($name, $headersLower);
                if ($lngIdx !== false) break;
            }
        }

        if ($latIdx === false || $lngIdx === false) {
            fclose($handle);
            throw new RuntimeException("Cannot find lat/lng columns. Headers: " . implode(', ', $headers));
        }

        $features = [];
        while (($row = fgetcsv($handle)) !== false) {
            $lat = (float) ($row[$latIdx] ?? 0);
            $lng = (float) ($row[$lngIdx] ?? 0);

            if ($lat == 0 && $lng == 0) continue;

            $properties = [];
            foreach ($headers as $i => $h) {
                if ($i !== $latIdx && $i !== $lngIdx) {
                    $properties[$h] = $row[$i] ?? '';
                }
            }

            $features[] = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [$lng, $lat],
                ],
                'properties' => $properties,
            ];
        }

        fclose($handle);

        return $features;
    }

    // ═══════════════ Private helpers ═══════════════

    /**
     * Normalisasi casing semua companion files (.dbf, .prj, .cpg, .shx, .sbn, .sbx)
     * agar match dengan .shp file. Penting untuk Linux (case-sensitive filesystem).
     * Dipanggil setelah extract zip sebelum parseShp().
     *
     * @param string $shpPath Path ke file .shp
     * @return array Daftar companion files yang ditemukan (key: extension)
     */
    public function normalizeCompanionFiles(string $shpPath): array
    {
        $baseDir = dirname($shpPath);
        $baseName = pathinfo($shpPath, PATHINFO_FILENAME);
        $extensions = ['dbf', 'prj', 'cpg', 'shx', 'sbn', 'sbx'];

        if (! is_dir($baseDir)) return [];

        $dirFiles = scandir($baseDir);
        if ($dirFiles === false) return [];

        $found = [];
        foreach ($extensions as $ext) {
            $expected = $baseDir . '/' . $baseName . '.' . $ext;

            // 1. Sudah benar casing?
            if (file_exists($expected)) {
                $found[$ext] = $expected;
                continue;
            }

            // 2. Cari case-insensitive: base name match + extension match
            foreach ($dirFiles as $file) {
                $fileBase = pathinfo($file, PATHINFO_FILENAME);
                $fileExt = pathinfo($file, PATHINFO_EXTENSION);

                if (strtolower($fileExt) === $ext && strtolower($fileBase) === strtolower($baseName)) {
                    // Rename ke casing yang benar agar parseShp bisa temukan
                    $wrongPath = $baseDir . '/' . $file;
                    if ($wrongPath !== $expected) {
                        @rename($wrongPath, $expected);
                    }
                    $found[$ext] = $expected;
                    break;
                }
            }
        }

        return $found;
    }

    /**
     * Cari companion file (dbf, prj, cpg, shx, sbn, sbx) secara case-insensitive.
     * Penting untuk kompatibilitas Linux di mana filesystem case-sensitive.
     *
     * @param string $shpPath Path ke file .shp
     * @param string $extension Extension yang dicari (e.g. '.dbf', '.prj', '.cpg')
     * @return string|null Path file jika ditemukan, null jika tidak
     */
    private function findCompanionFile(string $shpPath, string $extension): ?string
    {
        // 1. Coba exact match (case-sensitive) — paling cepat
        $expected = preg_replace('/\.shp$/i', $extension, $shpPath);
        if (file_exists($expected)) return $expected;

        // 2. Scan directory untuk case-insensitive match
        $baseDir = dirname($shpPath);
        $baseName = pathinfo($shpPath, PATHINFO_FILENAME);

        if (! is_dir($baseDir)) return null;

        $dirFiles = scandir($baseDir);
        if ($dirFiles === false) return null;

        $extLower = strtolower(ltrim($extension, '.'));

        foreach ($dirFiles as $file) {
            $fileBase = pathinfo($file, PATHINFO_FILENAME);
            $fileExt = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            if ($fileExt === $extLower && strtolower($fileBase) === strtolower($baseName)) {
                return $baseDir . '/' . $file;
            }
        }

        // 3. Fallback: jika hanya ada satu file dengan extension itu
        $candidates = [];
        foreach ($dirFiles as $file) {
            if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) === $extLower) {
                $candidates[] = $baseDir . '/' . $file;
            }
        }

        return count($candidates) === 1 ? $candidates[0] : null;
    }

    private function readShpHeader($handle): array
    {
        $header = fread($handle, 100);
        return [
            'file_code' => unpack('N', substr($header, 0, 4))[1],
            'file_length' => unpack('N', substr($header, 24, 4))[1] * 2,
            'version' => unpack('V', substr($header, 28, 4))[1],
            'shape_type' => unpack('V', substr($header, 32, 4))[1],
            'bbox' => [
                'min_x' => unpack('d', strrev(substr($header, 36, 8)))[1],
                'min_y' => unpack('d', strrev(substr($header, 44, 8)))[1],
                'max_x' => unpack('d', strrev(substr($header, 52, 8)))[1],
                'max_y' => unpack('d', strrev(substr($header, 60, 8)))[1],
            ],
        ];
    }

    private function readPoint(string $content): ?array
    {
        if (strlen($content) < 20) return null;

        $x = unpack('d', substr($content, 4, 8))[1];
        $y = unpack('d', substr($content, 12, 8))[1];

        return [
            'type' => 'Point',
            'coordinates' => [$x, $y],
        ];
    }

    private function readMultiPoint(string $content): ?array
    {
        // SHP MultiPoint: [4 ShapeType][32 BBox][4 NumPoints][NumPoints*16 Points]
        if (strlen($content) < 40) return null;

        $numPoints = unpack('V', substr($content, 36, 4))[1]; // offset 36 = after ShapeType(4) + BBox(32)
        $points = [];
        $offset = 40; // after ShapeType(4) + BBox(32) + NumPoints(4)

        for ($i = 0; $i < $numPoints; $i++) {
            if (strlen($content) < $offset + 16) break;
            $x = unpack('d', substr($content, $offset, 8))[1];
            $y = unpack('d', substr($content, $offset + 8, 8))[1];
            $points[] = [$x, $y];
            $offset += 16;
        }

        return [
            'type' => 'MultiPoint',
            'coordinates' => $points,
        ];
    }

    private function readPolyline(string $content, bool $isPolyline = true): ?array
    {
        // SHP Polyline/Polygon: [4 ShapeType][32 BBox][4 NumParts][4 NumPoints][NumParts*4 Parts][NumPoints*16 Points]
        if (strlen($content) < 44) return null;

        $numParts  = unpack('V', substr($content, 36, 4))[1]; // offset 36 = after ShapeType(4) + BBox(32)
        $numPoints = unpack('V', substr($content, 40, 4))[1]; // offset 40 = after ShapeType(4) + BBox(32) + NumParts(4)

        $parts = [];
        $offset = 44; // after ShapeType(4) + BBox(32) + NumParts(4) + NumPoints(4)

        for ($i = 0; $i < $numParts; $i++) {
            if (strlen($content) < $offset + 4) break;
            $parts[] = unpack('V', substr($content, $offset, 4))[1];
            $offset += 4;
        }

        $allPoints = [];
        for ($i = 0; $i < $numPoints; $i++) {
            if (strlen($content) < $offset + 16) break;
            $x = unpack('d', substr($content, $offset, 8))[1];
            $y = unpack('d', substr($content, $offset + 8, 8))[1];
            $allPoints[] = [$x, $y];
            $offset += 16;
        }

        if (count($parts) <= 1) {
            return [
                'type' => $isPolyline ? 'LineString' : 'Polygon',
                'coordinates' => $isPolyline ? $allPoints : [$allPoints],
            ];
        }

        $coordinates = [];
        for ($i = 0; $i < count($parts); $i++) {
            $start = $parts[$i];
            $end = $i + 1 < count($parts) ? $parts[$i + 1] : $numPoints;
            $coordinates[] = array_slice($allPoints, $start, $end - $start);
        }

        return [
            'type' => $isPolyline ? 'MultiLineString' : 'Polygon',
            'coordinates' => $coordinates,
        ];
    }

    private function readPolygon(string $content): ?array
    {
        return $this->readPolyline($content, false);
    }

    private function parseDbf(string $dbfPath, ?string $cpgEncoding = null): array
    {
        $handle = fopen($dbfPath, 'rb');
        if (! $handle) return [];

        $header = fread($handle, 32);
        $numRecords = unpack('V', substr($header, 4, 4))[1];
        $headerSize = unpack('v', substr($header, 8, 2))[1];
        $recordSize = unpack('v', substr($header, 10, 2))[1];

        // Prioritas encoding: .cpg file > Language Driver byte > default Windows-1252
        $encoding = 'Windows-1252';
        if ($cpgEncoding) {
            // Normalisasi nama encoding dari .cpg
            $encoding = match (true) {
                str_contains($cpgEncoding, 'utf-8') || str_contains($cpgEncoding, 'utf8') => 'UTF-8',
                str_contains($cpgEncoding, 'latin') || str_contains($cpgEncoding, 'iso-8859') => 'ISO-8859-1',
                str_contains($cpgEncoding, 'cp1252') || str_contains($cpgEncoding, 'windows-1252') => 'Windows-1252',
                default => mb_detect_encoding($cpgEncoding, ['UTF-8', 'ISO-8859-1', 'Windows-1252']) ?: 'UTF-8',
            };
        } else {
            // Fallback ke deteksi Language Driver byte
            $langDriver = ord($header[29] ?? "\x00");
            if ($langDriver === 0x50 || $langDriver === 0x5A) {
                $encoding = 'UTF-8';
            }
        }

        // Read field descriptors
        $numFields = (int) (($headerSize - 33) / 32);
        $fields = [];
        for ($i = 0; $i < $numFields; $i++) {
            $fieldHeader = fread($handle, 32);
            $rawName = substr($fieldHeader, 0, 11);
            if ($encoding !== 'UTF-8' && function_exists('mb_convert_encoding')) {
                $rawName = @mb_convert_encoding($rawName, 'UTF-8', $encoding);
            }
            $fieldName = trim($rawName, "\0 ");
            $fieldType = substr($fieldHeader, 11, 1);
            $fieldLength = ord($fieldHeader[16]); // byte 16 = field length
            $fields[] = ['name' => $fieldName, 'type' => $fieldType, 'length' => $fieldLength];
        }

        // Skip header terminator
        fread($handle, 1);

        // Read records
        $records = [];
        for ($r = 0; $r < $numRecords; $r++) {
            $record = fread($handle, $recordSize);
            if (strlen($record) < $recordSize) break;

            $deleted = $record[0] === '*';
            if ($deleted) continue;

            $offset = 1;
            $row = [];
            foreach ($fields as $field) {
                $rawVal = substr($record, $offset, $field['length']);
                if ($encoding !== 'UTF-8' && function_exists('mb_convert_encoding')) {
                    $rawVal = @mb_convert_encoding($rawVal, 'UTF-8', $encoding);
                }
                $value = trim($rawVal);
                $row[$field['name']] = $this->castDbfValue($value, $field['type']);
                $offset += $field['length'];
            }

            $records[] = $row;
        }

        fclose($handle);

        return $records;
    }

    private function castDbfValue(string $value, string $type): string|int|float|null
    {
        $trimmed = trim($value);
        if ($trimmed === '') return null;

        return match ($type) {
            'N', 'F' => is_numeric($trimmed) ? (float) $trimmed : $trimmed,
            'D' => $trimmed,
            'L' => in_array(strtolower($trimmed), ['t', 'y', '1']),
            default => $trimmed,
        };
    }

    private function parseKmlCoordinates(string $coords, bool $isLine = false): ?array
    {
        $lines = array_filter(array_map('trim', explode("\n", $coords)));
        $points = [];

        foreach ($lines as $line) {
            $parts = array_map('floatval', explode(',', $line));
            if (count($parts) >= 2) {
                $points[] = [$parts[0], $parts[1]];
            }
        }

        if (empty($points)) return null;

        if (count($points) === 1) {
            return ['type' => 'Point', 'coordinates' => $points[0]];
        }

        return [
            'type' => $isLine ? 'LineString' : 'Polygon',
            'coordinates' => $isLine ? $points : [$points],
        ];
    }

    private function parseKmlPolygon(\SimpleXMLElement $polygon): ?array
    {
        $outer = $polygon->outerBoundaryIs->LinearRing->coordinates ?? null;
        if (! $outer) return null;

        $coords = $this->parseKmlCoordinates((string) $outer);
        if (! $coords) return null;

        $coords['type'] = 'Polygon';
        return $coords;
    }

    private function findFileRecursive(string $dir, string $extension): ?string
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() === ltrim($extension, '.')) {
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
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        rmdir($dir);
    }

    /**
     * Convert coordinates recursively in place from UTM to LatLng
     */
    private function convertCoordsRecursive(&$coords, int $zone, bool $isSouth)
    {
        if (is_array($coords) && count($coords) > 0) {
            if (!is_array($coords[0])) {
                // Point [x, y]
                $converted = $this->utm2LatLng((float) $coords[0], (float) $coords[1], $zone, $isSouth);
                $coords[0] = $converted[0];
                $coords[1] = $converted[1];
            } else {
                for ($i = 0; $i < count($coords); $i++) {
                    $this->convertCoordsRecursive($coords[$i], $zone, $isSouth);
                }
            }
        }
    }

    /**
     * Convert UTM coordinates to WGS84 Lat/Lng
     */
    private function utm2LatLng(float $easting, float $northing, int $zone, bool $isSouth): array
    {
        $a = 6378137.0; // semi-major axis
        $f = 1.0 / 298.257223563; // flattening
        $b = $a * (1.0 - $f); // semi-minor axis
        
        $e2 = ($a*$a - $b*$b) / ($a*$a); // eccentricity squared
        $ePrime2 = ($a*$a - $b*$b) / ($b*$b); // second eccentricity squared
        
        $k0 = 0.9996; // scale factor
        
        $x = $easting - 500000.0;
        $y = $isSouth ? $northing - 10000000.0 : $northing;
        
        $e1 = (1.0 - sqrt(1.0 - $e2)) / (1.0 + sqrt(1.0 - $e2));
        
        $M = $y / $k0;
        $mu = $M / ($a * (1.0 - $e2/4.0 - 3.0*$e2*$e2/64.0 - 5.0*$e2*$e2*$e2/256.0));
        
        $phi1Rad = $mu + (3.0*$e1/2.0 - 27.0*$e1*$e1*$e1/32.0)*sin(2.0*$mu)
                    + (21.0*$e1*$e1/16.0 - 55.0*$e1*$e1*$e1*$e1/32.0)*sin(4.0*$mu)
                    + (151.0*$e1*$e1*$e1/96.0)*sin(6.0*$mu)
                    + (1097.0*$e1*$e1*$e1*$e1/512.0)*sin(8.0*$mu);
                    
        $n1 = $a / sqrt(1.0 - $e2*sin($phi1Rad)*sin($phi1Rad));
        $t1 = tan($phi1Rad)*tan($phi1Rad);
        $c1 = $ePrime2*cos($phi1Rad)*cos($phi1Rad);
        $r1 = $a*(1.0 - $e2) / pow(1.0 - $e2*sin($phi1Rad)*sin($phi1Rad), 1.5);
        $d = $x / ($n1*$k0);
        
        $lat = $phi1Rad - ($n1*tan($phi1Rad)/$r1) * (
            pow($d, 2)/2.0
            - (5.0 + 3.0*$t1 + 10.0*$c1 - 4.0*$c1*$c1 - 9.0*$ePrime2)*pow($d, 4)/24.0
            + (61.0 + 90.0*$t1 + 298.0*$c1 + 45.0*$t1*$t1 - 252.0*$ePrime2 - 3.0*$c1*$c1)*pow($d, 6)/720.0
        );
        
        $lon0 = (($zone - 1) * 6 - 180 + 3) * pi() / 180.0;
        
        $lon = $lon0 + (
            $d
            - (1.0 + 2.0*$t1 + $c1)*pow($d, 3)/6.0
            + (5.0 - 2.0*$c1 + 28.0*$t1 - 3.0*$c1*$c1 + 8.0*$ePrime2 + 24.0*$t1*$t1)*pow($d, 5)/120.0
        ) / cos($phi1Rad);
        
        return [
            $lon * 180.0 / pi(),
            $lat * 180.0 / pi()
        ];
    }

    /**
     * Validasi koordinat wilayah Kota Palu dan swap jika lat/lng tertukar
     */
    public function validateAndSwapCoordinates(&$features): bool
    {
        $swapped = false;
        foreach ($features as &$f) {
            if (isset($f['geometry']['coordinates'])) {
                $hasSwapped = false;
                $this->checkAndSwapCoordsRecursive($f['geometry']['coordinates'], $hasSwapped);
                if ($hasSwapped) {
                    $swapped = true;
                }
            }
        }
        return $swapped;
    }

    private function checkAndSwapCoordsRecursive(&$coords, &$hasSwapped)
    {
        if (is_array($coords) && count($coords) > 0) {
            if (!is_array($coords[0])) {
                // Point [x, y]
                $x = (float) $coords[0];
                $y = (float) $coords[1];
                // Jika x negatif (sekitar -0.9, latitude) dan y positif (sekitar 119.8, longitude), maka tertukar!
                if ($x < 0 && $y > 0) {
                    $coords[0] = $y;
                    $coords[1] = $x;
                    $hasSwapped = true;
                }
            } else {
                for ($i = 0; $i < count($coords); $i++) {
                    $this->checkAndSwapCoordsRecursive($coords[$i], $hasSwapped);
                }
            }
        }
    }
}
