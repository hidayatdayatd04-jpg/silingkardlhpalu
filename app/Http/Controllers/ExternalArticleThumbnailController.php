<?php

namespace App\Http\Controllers;

use App\Enums\ArtikelStatus;
use App\Exceptions\ExternalArticleMetadataException;
use App\Models\Artikel;
use App\Services\ExternalArticleMetadataService;
use Illuminate\Http\Response;

class ExternalArticleThumbnailController extends Controller
{
    public function __invoke(Artikel $artikel, ExternalArticleMetadataService $service): Response
    {
        abort_unless($artikel->isExternal() && filled($artikel->external_thumbnail_url), 404);

        $isPublic = $artikel->status === ArtikelStatus::PUBLISHED
            && $artikel->tanggal_publish?->startOfDay()->lte(now());

        abort_unless($isPublic || auth()->check(), 404);

        try {
            $image = $service->fetchImage($artikel->external_thumbnail_url);
        } catch (ExternalArticleMetadataException $e) {
            abort(404, 'Thumbnail berita tidak dapat dimuat saat ini.');
        }

        $mime = strtolower(trim(explode(';', $image['content_type'])[0]));

        return response($image['body'], 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
