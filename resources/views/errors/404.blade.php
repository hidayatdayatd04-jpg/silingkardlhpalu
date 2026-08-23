{{-- Halaman 404 ramah untuk pengguna non-teknis. Pesan khusus dari
     abort(404, '...') ditampilkan bila ada (mis. "File tidak ditemukan."),
     selain itu pakai teks bawaan. Tanpa layout agar tetap tampil bila
     aplikasi/CSS sedang bermasalah. --}}
@php
    $msg = trim((string) $exception->getMessage());
    if ($msg === '' || stripos($msg, 'not found') === 0 || stripos($msg, 'not found.') === 0) {
        $msg = 'Halaman atau berkas yang Anda cari tidak ditemukan.';
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Halaman tidak ditemukan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: #f1f5f9;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            color: #0f172a;
        }
        .card {
            max-width: 420px;
            width: 100%;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 40px 32px;
            text-align: center;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .06);
        }
        .code { font-size: 40px; font-weight: 800; color: #059669; letter-spacing: .02em; }
        h1 { font-size: 17px; font-weight: 700; margin: 12px 0 8px; line-height: 1.5; }
        p { font-size: 13px; color: #64748b; line-height: 1.6; }
        a {
            display: inline-block;
            margin-top: 24px;
            padding: 10px 20px;
            background: #059669;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            border-radius: 10px;
            text-decoration: none;
        }
        a:hover { background: #047857; }
    </style>
</head>
<body>
    <main class="card">
        <p class="code">404</p>
        <h1>{{ $msg }}</h1>
        <p>Silakan kembali ke halaman sebelumnya, atau coba beberapa saat lagi.</p>
        <a href="{{ url('/') }}">Kembali ke Beranda</a>
    </main>
</body>
</html>
