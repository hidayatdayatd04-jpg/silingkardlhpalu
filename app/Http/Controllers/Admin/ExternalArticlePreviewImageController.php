<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ExternalArticleMetadataException;
use App\Http\Controllers\Controller;
use App\Services\ExternalArticleMetadataService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ExternalArticlePreviewImageController extends Controller
{
    public function __invoke(
        Request $request,
        string $token,
        ExternalArticleMetadataService $service,
    ): Response {
        $preview = $request->session()->get('external_article_previews.'.$token);

        if (! is_array($preview) || blank($preview['url'] ?? null)
            || (int) ($preview['expires_at'] ?? 0) < now()->timestamp) {
            $request->session()->forget('external_article_previews.'.$token);
            abort(404);
        }

        try {
            $image = $service->fetchImage($preview['url']);
        } catch (ExternalArticleMetadataException $e) {
            abort(404, 'Thumbnail berita tidak dapat dimuat saat ini.');
        }

        $mime = strtolower(trim(explode(';', $image['content_type'])[0]));

        return response($image['body'], 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=300',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
