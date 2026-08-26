<?php

namespace App\Support;

use App\Support\Admin\AdminRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Export & import data resource admin — dependency-free (TANPA maatwebsite/excel).
 *
 * Mendukung:
 *  - CSV  : stream tulis langsung, BOM UTF-8 agar Excel membaca åmbali benar.
 *  - XLSX : format .xlsx asli (OOXML) via ZipArchive + XML sederhana.
 *  - Read : CSV (fgetcsv) & XLSX (parse sharedStrings + sheet XML).
 */
class DataIO
{
    /**
     * Nilai terbaca untuk enum/tanggal/boolean (dipakai CSV, XLSX, PDF).
     */
    public static function displayValue(mixed $value): string
    {
        if ($value instanceof \BackedEnum) {
            return method_exists($value, 'label') ? $value->label() : (string) $value->value;
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('d M Y H:i');
        }
        if (is_bool($value)) {
            return $value ? 'Ya' : 'Tidak';
        }
        if (is_array($value)) {
            return implode(', ', $value);
        }

        return filled($value) ? (string) $value : '-';
    }

    /**
     * Headings terbaca dari kolom resource.
     *
     * @param  array<int,string>  $columns
     */
    public static function headings(array $columns): array
    {
        return collect($columns)->map(fn ($c) => Str::headline($c))->all();
    }

    /**
     * Stream unduh CSV dari builder + kolom.
     *
     * @param  array<int,string>  $columns
     */
    public static function csvDownload(Builder $builder, array $columns, string $filename, ?array $headings = null, ?callable $rowMapper = null)
    {
        return response()->streamDownload(function () use ($builder, $columns, $headings, $rowMapper) {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 supaya Excel membuka karakter spesial dengan benar.
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, array_map(fn ($heading) => self::sanitizeCell((string) $heading), $headings ?? self::headings($columns)));

            (clone $builder)->chunk(500, function ($rows) use ($handle, $columns, $rowMapper) {
                foreach ($rows as $row) {
                    fputcsv($handle, self::exportRowValues($row, $columns, $rowMapper));
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Tulis file CSV ke path absolut (dipakai job export berantre).
     * Mirip csvDownload tapi ke disk, bukan stream ke browser.
     *
     * @param  array<int,string>  $columns
     */
    public static function writeCsvFile(Builder $builder, array $columns, string $absolutePath, ?array $headings = null, ?callable $rowMapper = null): void
    {
        $dir = dirname($absolutePath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $handle = fopen($absolutePath, 'w');
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, array_map(fn ($heading) => self::sanitizeCell((string) $heading), $headings ?? self::headings($columns)));

        (clone $builder)->chunk(500, function ($rows) use ($handle, $columns, $rowMapper) {
            foreach ($rows as $row) {
                fputcsv($handle, self::exportRowValues($row, $columns, $rowMapper));
            }
        });

        fclose($handle);
    }

    /**
     * Tulis file .xlsx asli (bagi-kali/baris) ke path absolut.
     *
     * @param  array<int,string>  $columns
     */
    public static function writeXlsx(Builder $builder, array $columns, string $absolutePath, ?array $headings = null, ?callable $rowMapper = null): void
    {
        $headings = $headings ?? self::headings($columns);

        // Pastikan direktori tujuan ada (ZipArchive tidak membuat parent dir).
        $dir = dirname($absolutePath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $zip = new \ZipArchive();
        // open() mengembalikan true (sukses) atau kode error integer — bandingkan eksplisit.
        if ($zip->open($absolutePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Tidak dapat membuat file XLSX.');
        }

        $zip->addFromString('[Content_Types].xml', self::xlsxContentTypes());
        $zip->addFromString('_rels/.rels', self::xlsxRels());
        $zip->addFromString('xl/workbook.xml', self::xlsxWorkbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::xlsxWorkbookRels());
        $zip->addFromString('xl/styles.xml', self::xlsxStyles());

        // Kumpulkan baris ke string XML (chunk agar tak meledak memori).
        $rowsXml = '';
        $rowIndex = 2;
        (clone $builder)->chunk(500, function ($rows) use ($columns, $headings, $rowMapper, &$rowsXml, &$rowIndex) {
            foreach ($rows as $row) {
                $rowsXml .= self::xlsxDataRow(
                    $rowIndex,
                    self::exportRowValues($row, $columns, $rowMapper),
                    $headings,
                );
                $rowIndex++;
            }
        });

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            // Urutan elemen wajib mengikuti CT_Worksheet OOXML. Excel
            // menganggap `<cols>` sebelum `<sheetViews>` sebagai worksheet
            // rusak walau XML-nya sendiri well-formed, lalu membuang seluruh
            // sheet saat proses repair.
            . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="15"/>'
            . '<cols>'
            . collect($headings)->map(fn ($heading, $i) => '<col min="'.($i + 1).'" max="'.($i + 1).'" width="'.self::xlsxColumnWidth((string) $heading).'" customWidth="true"/>')->implode('')
            . '</cols>'
            . '<sheetData>'
            . self::xlsxHeaderRow($headings)
            . $rowsXml
            . '</sheetData>'
            . '<autoFilter ref="A1:'.self::colLetter(max(1, count($headings))).max(1, $rowIndex - 1).'"/>'
            . '</worksheet>';

        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();
    }

    /**
     * Tulis XLSX dari baris siap-pakai (headings + rows berupa string/angka).
     */
    public static function writeXlsxRows(array $headings, array $rows, string $absolutePath): void
    {
        $dir = dirname($absolutePath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $zip = new \ZipArchive();
        if (! $zip->open($absolutePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            throw new \RuntimeException('Tidak dapat membuat file XLSX.');
        }

        $zip->addFromString('[Content_Types].xml', self::xlsxContentTypes());
        $zip->addFromString('_rels/.rels', self::xlsxRels());
        $zip->addFromString('xl/workbook.xml', self::xlsxWorkbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::xlsxWorkbookRels());
        $zip->addFromString('xl/styles.xml', self::xlsxStyles());

        $rowsXml = '';
        $rowIndex = 1;
        foreach ($rows as $row) {
            $rowIndex++;
            $rowsXml .= self::xlsxDataRow($rowIndex, $row, $headings);
        }

        $headRow = self::xlsxHeaderRow($headings);

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="15"/>'
            . '<cols>'
            . collect($headings)->map(fn ($heading, $i) => '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="'.self::xlsxColumnWidth((string) $heading).'" customWidth="true"/>')->implode('')
            . '</cols>'
            . '<sheetData>' . $headRow . $rowsXml . '</sheetData>'
            . '<autoFilter ref="A1:'.self::colLetter(max(1, count($headings))).max(1, $rowIndex).'"/>'
            . '</worksheet>';

        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();
    }

    /**
     * Stream unduh CSV dari headings + baris siap-pakai.
     */
    public static function csvRowsDownload(array $headings, array $rows, string $filename)
    {
        return response()->streamDownload(function () use ($headings, $rows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, array_map(fn ($h) => self::sanitizeCell((string) $h), $headings));
            foreach ($rows as $row) {
                fputcsv($handle, array_map(fn ($cell) => self::sanitizeCell((string) $cell), $row));
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected static function xlsxCell(string $value): string
    {
        // Pertahankan line break agar data relasi panjang tetap terbaca ketika
        // Excel melakukan wrap text; buang karakter kontrol XML yang tidak sah.
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $value) ?? '';

        return self::sanitizeCell(trim($value));
    }

    /**
     * Normalisasi satu baris builder, dengan atau tanpa mapper khusus resource.
     * Mapper boleh mengembalikan array berurutan atau asosiatif sesuai key kolom.
     *
     * @param  array<int,string>  $columns
     * @return array<int,string>
     */
    protected static function exportRowValues(mixed $row, array $columns, ?callable $rowMapper): array
    {
        $mapped = $rowMapper ? $rowMapper($row) : null;

        if (! is_array($mapped)) {
            $mapped = collect($columns)
                ->map(fn ($column) => $row->{$column} ?? null)
                ->all();
        } elseif (! array_is_list($mapped)) {
            $mapped = collect($columns)
                ->map(fn ($column) => $mapped[$column] ?? null)
                ->all();
        }

        return array_map(
            fn ($value) => self::sanitizeCell(self::displayValue($value)),
            array_pad($mapped, count($columns), null),
        );
    }

    /** @param array<int,mixed> $values @param array<int,string> $headings */
    protected static function xlsxDataRow(int $rowIndex, array $values, array $headings): string
    {
        $cells = '';

        foreach (array_values($values) as $columnIndex => $value) {
            $text = self::xlsxCell(self::displayValue($value));
            $style = self::xlsxBodyStyle((string) ($headings[$columnIndex] ?? ''), $text);
            $reference = self::colLetter($columnIndex + 1).$rowIndex;
            $cells .= '<c r="'.$reference.'" t="inlineStr" s="'.$style.'"><is><t xml:space="preserve">'
                .htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8')
                .'</t></is></c>';
        }

        return '<row r="'.$rowIndex.'">'.$cells.'</row>';
    }

    /** @param array<int,string> $headings */
    protected static function xlsxHeaderRow(array $headings): string
    {
        return '<row r="1">'.collect($headings)->map(
            fn ($heading, $index) => '<c r="'.self::colLetter($index + 1).'1" t="inlineStr" s="1"><is><t xml:space="preserve">'
                .htmlspecialchars((string) $heading, ENT_XML1 | ENT_QUOTES, 'UTF-8')
                .'</t></is></c>'
        )->implode('').'</row>';
    }

    protected static function xlsxColumnWidth(string $heading): int
    {
        $heading = Str::lower($heading);

        if (Str::contains($heading, ['tautan', 'lampiran', 'dokumen', 'foto', 'file', 'gambar', 'media'])) {
            return 54;
        }

        if (Str::contains($heading, ['alamat', 'deskripsi', 'keterangan', 'catatan', 'konten', 'materi', 'hasil', 'peserta', 'sanksi'])) {
            return 42;
        }

        if (Str::contains($heading, ['telepon', 'nomor', 'nik', 'kode', 'id'])) {
            return 26;
        }

        if (Str::contains($heading, 'status')) {
            return 24;
        }

        return 22;
    }

    protected static function xlsxBodyStyle(string $heading, string $value): int
    {
        if (! Str::contains(Str::lower($heading), 'status')) {
            return 2;
        }

        $value = Str::lower($value);
        if (Str::contains($value, ['ditindaklanjuti', 'selesai'])) {
            return 3;
        }

        if (Str::contains($value, ['ditolak', 'gagal'])) {
            return 5;
        }

        if (Str::contains($value, ['belum', 'menunggu', 'pending', 'proses'])) {
            return 4;
        }

        return 2;
    }

    /**
     * Netralkan formula injection pada sel export (CSV/XLSX).
     *
     * Sel yang diawali karakter pemicu formula (= + - @ \t \r) diberi prefix
     * apostrof agar Excel/LibreOffice memperlakukannya sebagai teks. Angka
     * murni (termasuk negatif) dilewatkan apa adanya supaya tetap numerik.
     */
    public static function sanitizeCell(string $value): string
    {
        if (preg_match('/^-?\d+(\.\d+)?$/', $value)) {
            return $value;
        }

        if ($value !== '' && str_contains('=+-@' . "\t\r", $value[0])) {
            return "'" . $value;
        }

        return $value;
    }

    /**
     * Konversi 1-based index → huruf kolom (A, B, …, AA).
     */
    public static function colLetter(int $n): string
    {
        $letter = '';
        while ($n > 0) {
            $mod = ($n - 1) % 26;
            $letter = chr(65 + $mod).$letter;
            $n = intdiv($n - 1, 26);
        }

        return $letter;
    }

    /**
     * Baca file CSV/XLSX menjadi array asosiatif ber-header.
     *
     * @return array<int,array<string,string>>
     */
    public static function readFile(string $absolutePath): array
    {
        if (! is_file($absolutePath)) {
            throw new \RuntimeException('File tidak ditemukan.');
        }

        $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));

        return $ext === 'csv'
            ? self::readCsv($absolutePath)
            : self::readXlsx($absolutePath);
    }

    protected static function readCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new \RuntimeException('Gagal membuka file CSV.');
        }
        // Lepas BOM bila ada.
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);

            return [];
        }
        $header = array_map(fn ($h) => Str::lower((string) $h), $header);

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count(array_filter($row, fn ($c) => trim((string) $c) !== '')) === 0) {
                continue; // lewati baris kosong
            }
            $rows[] = array_combine($header, array_pad($row, count($header), ''));
        }
        fclose($handle);

        return $rows;
    }

    /**
     * Baca XLSX (string inline atau sharedStrings).
     */
    protected static function readXlsx(string $path): array
    {
        $zip = new \ZipArchive();
        if (! $zip->open($path) || ! $zip->locateName('xl/worksheets/sheet1.xml')) {
            throw new \RuntimeException('File XLSX tidak valid atau rusak.');
        }

        // Shared strings (opsional — kita simpan inlineStr).
        $shared = [];
        if ($zip->locateName('xl/sharedStrings.xml')) {
            $sx = simplexml_load_string((string) $zip->getFromName('xl/sharedStrings.xml'));
            if ($sx) {
                foreach ($sx->si as $si) {
                    $shared[] = (string) ($si->t ?? '');
                }
            }
        }

        $sheet = simplexml_load_string((string) $zip->getFromName('xl/worksheets/sheet1.xml'));
        if (! $sheet->sheetData->row) {
            $zip->close();

            return [];
        }

        $rows = [];
        $header = null;
        foreach ($sheet->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $c) {
                $ref = (string) $c['r'];
                $colLetter = preg_replace('/\d+$/', '', $ref);
                $val = '';
                if (isset($c->v)) {
                    if ((string) ($c['t'] ?? '') === 's') {
                        $val = $shared[(int) $c->v] ?? '';
                    } else {
                        $val = (string) $c->v;
                    }
                } elseif (isset($c->is->t)) {
                    $val = (string) $c->is->t;
                }
                $cells[$colLetter] = $val;
            }
            // Urutkan kolom secara konsisten.
            ksort($cells, SORT_NATURAL);

            if ($header === null) {
                $header = array_map(fn ($h) => Str::lower((string) $h), array_values($cells));
                continue;
            }
            $vals = array_values($cells);
            if (count(array_filter($vals, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }
            $rows[] = array_combine($header, array_pad($vals, count($header), ''));
        }
        $zip->close();

        return $rows;
    }

    // ──────────────── Bagian-bagian statik XLSX ────────────────

    protected static function xlsxContentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>';
    }

    protected static function xlsxRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    protected static function xlsxWorkbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="Data" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>';
    }

    protected static function xlsxWorkbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    protected static function xlsxStyles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="3"><font><sz val="11"/><name val="Calibri"/></font>'
            .'<font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
            .'<fills count="6"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FF0F4C4A"/><bgColor indexed="64"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFD1FAE5"/><bgColor indexed="64"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFFEF3C7"/><bgColor indexed="64"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFFEE2E2"/><bgColor indexed="64"/></patternFill></fill></fills>'
            .'<borders count="2"><border/><border><left style="thin"><color rgb="FFE2E8F0"/></left><right style="thin"><color rgb="FFE2E8F0"/></right><top style="thin"><color rgb="FFE2E8F0"/></top><bottom style="thin"><color rgb="FFE2E8F0"/></bottom></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="6">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="4" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="5" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>'
            .'</cellXfs>'
            .'</styleSheet>';
    }
}
