<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OgImageProxyController extends Controller
{
    private const ALLOWED_EXT = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'];

    private const MIME = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'avif' => 'image/avif',
    ];

    public function show(Request $request)
    {
        $path = (string) $request->query('path', '');

        abort_unless($path !== '' && ! str_contains($path, '..') && Storage::disk('public')->exists($path), 404);

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        abort_unless(in_array($ext, self::ALLOWED_EXT, true), 403);

        $contents = Storage::disk('public')->get($path);

        return response($contents, 200, [
            'Content-Type' => self::MIME[$ext],
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
