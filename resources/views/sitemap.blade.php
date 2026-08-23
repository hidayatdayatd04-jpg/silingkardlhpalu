{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($static as $url)
    <url>
        <loc>{{ $url['loc'] }}</loc>
        <changefreq>{{ $url['changefreq'] }}</changefreq>
        <priority>{{ $url['priority'] }}</priority>
    </url>
@endforeach
@foreach($artikels as $artikel)
    <url>
        <loc>{{ $base }}/berita/{{ $artikel->slug }}</loc>
@php
    // Gunakan updated_at sebagai lastmod jika valid, fallback ke tanggal_publish.
    // Jika keduanya tidak valid/tidak ada, omit lastmod (sesuai revisi #3 & #8).
    $lastmod = null;
    if (!empty($artikel->updated_at)) {
        try { $lastmod = \Carbon\Carbon::parse($artikel->updated_at)->toDateString(); } catch (\Throwable $e) { $lastmod = null; }
    }
    if ($lastmod === null && !empty($artikel->tanggal_publish)) {
        try { $lastmod = \Carbon\Carbon::parse($artikel->tanggal_publish)->toDateString(); } catch (\Throwable $e) { $lastmod = null; }
    }
@endphp
@if($lastmod)
        <lastmod>{{ $lastmod }}</lastmod>
@endif
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
@endforeach
</urlset>
