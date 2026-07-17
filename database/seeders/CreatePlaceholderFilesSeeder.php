<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class CreatePlaceholderFilesSeeder extends Seeder
{
    /**
     * Membuat file placeholder untuk gambar dan dokumen
     */
    public function run(): void
    {
        $this->info('Creating placeholder files...');
        
        // Create placeholder image (1x1 pixel PNG)
        $placeholderImage = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
        Storage::disk('public')->put('seeder-images/placeholder.jpg', $placeholderImage);
        $this->info('Created: placeholder.jpg');
        
        // Create placeholder PDF
        $placeholderPdf = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj 2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj 3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 612 792]>>endobj\nxref\n0 4\n0000000000 65535 f\n0000000009 00000 n\n0000000056 00000 n\n0000000115 00000 n\ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n190\n%%EOF";
        Storage::disk('public')->put('seeder-documents/placeholder.pdf', $placeholderPdf);
        $this->info('Created: placeholder.pdf');
        
        $this->info('');
        $this->info('Placeholder files created successfully!');
        $this->info('These files will be used as fallback when actual images/documents are not available.');
    }

    private function info(string $message): void
    {
        echo $message . PHP_EOL;
    }
}
