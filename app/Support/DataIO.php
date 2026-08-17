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
    public static function csvDownload(Builder $builder, array $columns, string $filename, ?array $headings = null)
    {
        return response()->streamDownload(function () use ($builder, $columns, $headings) {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 supaya Excel membuka karakter spesial dengan benar.
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headings ?? self::headings($columns));

            (clone $builder)->chunk(500, function ($rows) use ($handle, $columns) {
                foreach ($rows as $row) {
                    fputcsv($handle, collect($columns)
                        ->map(fn ($c) => self::sanitizeCell(self::displayValue($row->{$c} ?? null)))
                        ->all());
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
    public static function writeCsvFile(Builder $builder, array $columns, string $absolutePath, ?array $headings = null): void
    {
        $dir = dirname($absolutePath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $handle = fopen($absolutePath, 'w');
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, $headings ?? self::headings($columns));

        (clone $builder)->chunk(500, function ($rows) use ($handle, $columns) {
            foreach ($rows as $row) {
                fputcsv($handle, collect($columns)
                    ->map(fn ($c) => self::sanitizeCell(self::displayValue($row->{$c} ?? null)))
                    ->all());
            }
        });

        fclose($handle);
    }

    /**
     * Tulis file .xlsx asli (bagi-kali/baris) ke path absolut.
     *
     * @param  array<int,string>  $columns
     */
    public static function writeXlsx(Builder $builder, array $columns, string $absolutePath, ?array $headings = null): void
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
        $rowIndex = 1;
        (clone $builder)->chunk(500, function ($rows) use ($columns, &$rowsXml, &$rowIndex) {
            foreach ($rows as $row) {
                $cells = '';
                $col = 0;
                foreach ($columns as $column) {
                    $value = self::xlsxCell(self::displayValue($row->{$column} ?? null));
                    $attr = ' r="' . self::colLetter($col + 1) . $rowIndex . '"';
                    if (preg_match('/^-?\d+(\.\d+)?$/', $value)) {
                        $cells .= '<c'.$attr.'><v>'.$value.'</v></c>';
                    } else {
                        $cells .= '<c'.$attr.' t="inlineStr"><is><t>'.htmlspecialchars($value, ENT_QUOTES).'</t></is></c>';
                    }
                    $col++;
                }
                $rowsXml .= '<row r="'.$rowIndex.'">'.$cells.'</row>';
                $rowIndex++;
            }
        });

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<cols>'
            . collect($columns)->map(fn ($c, $i) => '<col min="'.($i + 1).'" max="'.($i + 1).'" width="18" customWidth="true"/>')->implode('')
            . '</cols>'
            . '<sheetData>'
            . '<row r="1">'
            . collect($headings)->map(fn ($h, $i) => '<c r="'.self::colLetter($i + 1).'1" t="inlineStr" s="1"><is><t>'.htmlspecialchars($h, ENT_QUOTES).'</t></is></c>')->implode('')
            . '</row>'
            . $rowsXml
            . '</sheetData></worksheet>';

        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();
    }

    /**
     * Sanitasi nilai sel (trim, newline → spasi).
     */
    /**
     * Tulis XLSX dari baris siap-pakai (headings + rows berupa string/angka).
     */
    public static function writeXlsxRows(array $headings, array $rows, string $absolutePath): void
    {
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
            $cells = '';
            $col = 0;
            foreach ($row as $value) {
                $value = self::xlsxCell((string) $value);
                $attr = ' r="' . self::colLetter($col + 1) . $rowIndex . '"';
                if (preg_match('/^-?\d+(\.\d+)?$/', $value)) {
                    $cells .= '<c' . $attr . '><v>' . $value . '</v></c>';
                } else {
                    $cells .= '<c' . $attr . ' t="inlineStr"><is><t>' . htmlspecialchars($value, ENT_QUOTES) . '</t></is></c>';
                }
                $col++;
            }
            $rowsXml .= '<row r="' . $rowIndex . '">' . $cells . '</row>';
        }

        $headRow = '<row r="1">' . collect($headings)->map(
            fn ($h, $i) => '<c r="' . self::colLetter($i + 1) . '1" t="inlineStr" s="1"><is><t>' . htmlspecialchars((string) $h, ENT_QUOTES) . '</t></is></c>'
        )->implode('') . '</row>';

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<cols>'
            . collect($headings)->map(fn ($c, $i) => '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="18" customWidth="true"/>')->implode('')
            . '</cols>'
            . '<sheetData>' . $headRow . $rowsXml . '</sheetData></worksheet>';

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
        return self::sanitizeCell(trim(str_replace(["\r", "\n"], ' ', $value)));
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
            .'<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
            .'<fills count="1"><fill><patternFill patternType="none"/></fill></fills>'
            .'<borders count="1"><border/></borders>'
            .'<cellStyleXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/><xf numFmtId="0" fontId="1" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="1" applyFont="1"/></cellXfs>'
            .'</styleSheet>';
    }
}
