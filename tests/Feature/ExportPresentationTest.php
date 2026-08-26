<?php

namespace Tests\Feature;

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
                ['Nomor Telepon', 'Status', 'Lampiran (Tautan aman)'],
                [['081234567890', 'Ditindaklanjuti', 'https://contoh.test/file-bukti']],
                $path,
            );

            $this->assertFileExists($path);

            $zip = new \ZipArchive();
            $this->assertTrue($zip->open($path) === true);
            $sheet = (string) $zip->getFromName('xl/worksheets/sheet1.xml');
            $zip->close();

            $this->assertStringContainsString('state="frozen"', $sheet);
            $this->assertStringContainsString('<autoFilter ref="A1:C2"/>', $sheet);
            $this->assertStringContainsString('r="A2" t="inlineStr"', $sheet);
            $this->assertStringContainsString('081234567890', $sheet);
            $this->assertStringContainsString('s="3"', $sheet);
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}
