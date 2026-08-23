<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,jpg,png,webp,avif,heic,heif|max:5120',
        ]);

        $file = $request->file('file');

        // Gambar otomatis dikompres & dikonversi ke WebP (URL signed tidak
        // bergantung pada ekstensi, sehingga konten artikel tetap tampil).
        $path = app(FileUploadService::class)->store($file, 'artikel-images', 'public');

        if ($path === false) {
            return response()->json([
                'success' => false,
                'message' => 'Gambar gagal diunggah. Silakan coba lagi.',
            ], 500);
        }

        try {
            $url = Storage::disk('public')->temporaryUrl($path, now()->addHours(24));
        } catch (\Throwable $e) {
            report($e);
            $url = Storage::url($path);
        }

        return response()->json([
            'success' => true,
            'file' => $url,
            'files' => [$url],
            'isImages' => [true],
            'message' => 'Gambar berhasil diunggah',
        ]);
    }
}
