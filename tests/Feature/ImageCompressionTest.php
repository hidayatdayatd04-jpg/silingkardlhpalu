<?php

namespace Tests\Feature;

use App\Services\ImageCompressionService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageCompressionTest extends TestCase
{
    public function test_image_compression_works_correctly(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not loaded.');
        }

        Storage::fake('public');

        $service = new ImageCompressionService;
        $fakeFile = UploadedFile::fake()->image('test_tree.jpg', 2000, 2000);

        $storedPath = $service->compressAndStore($fakeFile, 'test_laporans');

        $this->assertNotNull($storedPath);
        Storage::disk('public')->assertExists($storedPath);

        $size = Storage::disk('public')->size($storedPath);
        $this->assertLessThan(500 * 1024, $size);
    }
}
