<?php

namespace Tests\Feature;

use App\Models\PengaduanPengendalian;
use App\Models\PengaduanPengendalianFoto;
use App\Support\Admin\AdminRegistry;
use App\Support\Admin\AdminResourceExporter;
use App\Support\DataIO;
use Illuminate\Support\Str;
use Tests\TestCase;

class ExportPresentationTest extends TestCase
{
    public function test_xlsx_keeps_identifiers_as_text_and_includes_readable_table_features(): void
    {
        $path = storage_path('app/testing/export-'.Str::uuid().'.xlsx');

        try {
            DataIO::writeXlsxRows(
                ['Nomor Telepon', 'Status', 'Lampiran'],
                [['081234567890', 'Ditindaklanjuti', '1. file-bukti.pdf - https://contoh.test/file-bukti.pdf']],
                $path,
                'Pengaduan Pengendalian',
            );

            $this->assertFileExists($path);

            $zip = new \ZipArchive();
            $this->assertTrue($zip->open($path) === true);
            $sheet = (string) $zip->getFromName('xl/worksheets/sheet1.xml');
            $styles = (string) $zip->getFromName('xl/styles.xml');
            $workbook = (string) $zip->getFromName('xl/workbook.xml');
            $sheetRels = (string) $zip->getFromName('xl/worksheets/_rels/sheet1.xml.rels');
            $zip->close();

            $this->assertXmlIsWellFormed($sheet, 'sheet1.xml harus berupa XML valid agar Excel tidak mengosongkan data.');
            $this->assertXmlIsWellFormed($styles, 'styles.xml harus berupa XML valid agar Excel dapat membaca format workbook.');
            $this->assertXmlIsWellFormed($workbook, 'workbook.xml harus berupa XML valid.');
            $this->assertXmlIsWellFormed($sheetRels, 'sheet1.xml.rels harus berupa XML valid.');

            $this->assertStringContainsString('name="Pengaduan Pengendalian"', $workbook);
            $this->assertStringContainsString('<dimension ref="A1:C2"/>', $sheet);
            $this->assertStringContainsString('activeCell="A1"', $sheet);
            $this->assertStringContainsString('<autoFilter ref="A1:C2"/>', $sheet);
            $this->assertStringContainsString('r="A2" t="inlineStr"', $sheet);
            $this->assertStringContainsString('081234567890', $sheet);
            $this->assertStringContainsString('s="3"', $sheet);
            $this->assertStringContainsString('s="6"', $sheet);
            $this->assertStringContainsString('<hyperlinks>', $sheet);
            $this->assertStringContainsString('Target="https://contoh.test/file-bukti.pdf"', $sheetRels);
            $this->assertLessThan(
                strpos($sheet, '<cols>'),
                strpos($sheet, '<sheetViews>'),
                'OOXML mewajibkan sheetViews berada sebelum cols agar Excel tidak membuang worksheet.',
            );
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function test_exporter_formats_photos_as_neat_numbered_links_without_parentheses(): void
    {
        $record = new PengaduanPengendalian(['nomor_tiket' => 'PDL-TEST-001']);
        $record->id = 1;
        $record->setRelation('fotos', collect([
            new PengaduanPengendalianFoto([
                'path_foto' => 'pengaduan-pengendalian/bukti-uji-1.webp',
                'status' => 'selesai',
            ]),
            new PengaduanPengendalianFoto([
                'path_foto' => 'pengaduan-pengendalian/bukti-uji-2.webp',
                'status' => 'selesai',
            ]),
        ]));

        $row = app(AdminResourceExporter::class)->row($record, [
            'slug' => 'pengaduan-pengendalian',
        ], [
            'columns' => [
                'nomor_tiket' => 'Nomor Tiket',
                '__relation_fotos' => 'Foto Bukti',
            ],
            'direct_file_columns' => [],
            'relation_columns' => [
                '__relation_fotos' => [
                    'relation' => 'fotos',
                    'path_field' => 'path_foto',
                ],
            ],
        ]);

        $this->assertSame('PDL-TEST-001', $row[0]);
        $this->assertStringContainsString('1. bukti-uji-1.webp - http', $row[1]);
        $this->assertStringContainsString('2. bukti-uji-2.webp - http', $row[1]);
        $this->assertStringNotContainsString('(', $row[1]);
        $this->assertStringNotContainsString(')', $row[1]);
        $this->assertStringContainsString('pengaduan-pengendalian', $row[1]);
    }

    public function test_id_field_is_excluded_from_export_columns(): void
    {
        $columns = AdminRegistry::exportColumns('pengaduan-pengendalian', PengaduanPengendalian::class);

        $this->assertArrayNotHasKey('id', $columns);
    }

    private function assertXmlIsWellFormed(string $xml, string $message): void
    {
        $document = new \DOMDocument();
        $this->assertTrue($document->loadXML($xml, LIBXML_NONET), $message);
    }
}
