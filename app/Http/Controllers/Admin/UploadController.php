<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
        ]);

        $file = $request->file('file');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('artikel-images', $filename, 'public');

        $url = Storage::disk('public')->url($path);

        return response()->json([
            'success' => true,
            'file' => $url,
            'files' => [$url],
            'isImages' => [true],
            'message' => 'Gambar berhasil diunggah',
        ]);
    }
}
