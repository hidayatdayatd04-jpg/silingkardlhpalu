<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ExternalArticleMetadataException;
use App\Http\Controllers\Controller;
use App\Services\ExternalArticleMetadataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            return response()->json($service->preview($validated['external_url']));
        } catch (ExternalArticleMetadataException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
