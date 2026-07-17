<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Berita Acara Sidak #{{ $sidak->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 24px; border-bottom: 2px solid #059669; padding-bottom: 12px; }
        .header h1 { font-size: 16px; margin: 0 0 4px; }
        .header p { margin: 0; color: #64748b; font-size: 11px; }
        .title { text-align: center; font-size: 14px; font-weight: bold; margin: 20px 0; text-decoration: underline; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        td { padding: 6px 8px; vertical-align: top; border-bottom: 1px solid #e2e8f0; }
        td.label { width: 35%; font-weight: bold; color: #475569; }
        .section { margin-top: 16px; }
        .section h3 { font-size: 12px; margin: 0 0 8px; color: #334155; }
        .footer { margin-top: 48px; }
        .signature { width: 45%; display: inline-block; text-align: center; margin-top: 40px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>DINAS LINGKUNGAN HIDUP KOTA PALU</h1>
        <p>Bidang Tata Penataan — Berita Acara Sidak Lapangan</p>
    </div>

    <div class="title">BERITA ACARA SIDAK</div>

    <table>
        <tr><td class="label">Tanggal Sidak</td><td>{{ $sidak->tanggal_sidak->format('d F Y') }}</td></tr>
        <tr><td class="label">Objek Pengawasan</td><td>{{ $sidak->objekPengawasan?->nama_perusahaan }}</td></tr>
        <tr><td class="label">Penanggung Jawab</td><td>{{ $sidak->objekPengawasan?->nama_penanggung_jawab }}</td></tr>
        <tr><td class="label">Alamat</td><td>{{ $sidak->objekPengawasan?->alamat }}</td></tr>
        <tr><td class="label">Nama Petugas</td><td>{{ $sidak->nama_petugas }}</td></tr>
        <tr><td class="label">Hasil Sidak</td><td>{{ $sidak->hasil_label ?? $sidak->hasil ?? '-' }}</td></tr>
        <tr><td class="label">Status Tindak Lanjut</td><td>{{ $sidak->status_tindak_lanjut?->label() ?? '-' }}</td></tr>
    </table>

    @if ($sidak->temuan)
        <div class="section">
            <h3>Temuan</h3>
            <p>{{ $sidak->temuan }}</p>
        </div>
    @endif

    @if ($sidak->rekomendasi)
        <div class="section">
            <h3>Rekomendasi</h3>
            <p>{{ $sidak->rekomendasi }}</p>
        </div>
    @endif

    <div class="footer">
        <div class="signature">
            <p>Petugas Sidak,</p>
            <br><br><br>
            <p><strong>{{ $sidak->nama_petugas }}</strong></p>
        </div>
        <div class="signature" style="float: right;">
            <p>Palu, {{ $sidak->tanggal_sidak->format('d F Y') }}</p>
            <p>Mengetahui,</p>
            <br><br><br>
            <p><strong>Kepala Bidang Tata Penataan</strong></p>
            <p>DLH Kota Palu</p>
        </div>
    </div>

    <p style="margin-top: 80px; font-size: 10px; color: #94a3b8; text-align: center;">
        Dokumen dicetak otomatis dari sistem DLH Kota Palu pada {{ now()->format('d/m/Y H:i') }}.
    </p>
</body>
</html>
