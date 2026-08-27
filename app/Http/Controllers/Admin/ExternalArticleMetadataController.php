<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ExternalArticleMetadataException;
use App\Http\Controllers\Controller;
use App\Services\ExternalArticleMetadataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExternalArticleMetadataController extends Controller
{
    public function __invoke(Request $request, ExternalArticleMetadataService $service): JsonResponse
    {
        $validated = $request->validate([
            'external_url' => ['required', 'string', 'max:4096'],
        ], [
            'external_url.required' => 'Link berita wajib diisi.',
        ]);

        try {
            // Thumbnail divalidasi ketika endpoint image dipanggil. Preview
            // metadata cukup mengambil HTML sekali agar UI tidak menunggu dua
            // unduhan gambar berurutan sebelum gambar mulai dirender.
            $metadata = $service->fetchMetadata($validated['external_url']);
            $token = Str::random(40);
            $previews = collect((array) $request->session()->get('external_article_previews', []))
                ->filter(fn ($preview) => is_array($preview)
                    && (int) ($preview['expires_at'] ?? 0) >= now()->timestamp)
                ->take(-4)
                ->all();

            $previews[$token] = [
                'url' => $metadata['image_url'],
                'expires_at' => now()->addMinutes(10)->timestamp,
            ];
            $request->session()->put('external_article_previews', $previews);

            return response()->json([
                ...$metadata,
                'preview_image_url' => route('admin.artikel.metadata.preview-image', $token, false),
            ]);
        } catch (ExternalArticleMetadataException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
