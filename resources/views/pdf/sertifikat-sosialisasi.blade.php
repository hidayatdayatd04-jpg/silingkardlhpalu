<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Sertifikat Sosialisasi — {{ $peserta->objekPengawasan?->nama_perusahaan }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; }
        .border { border: 3px double #059669; padding: 32px; margin: 16px; }
        .header { text-align: center; margin-bottom: 24px; }
        .header h1 { font-size: 18px; margin: 0 0 4px; color: #059669; }
        .header p { margin: 0; color: #64748b; font-size: 11px; }
        .title { text-align: center; font-size: 22px; font-weight: bold; margin: 24px 0 8px; letter-spacing: 2px; }
        .subtitle { text-align: center; font-size: 12px; color: #64748b; margin-bottom: 24px; }
        .recipient { text-align: center; font-size: 16px; font-weight: bold; margin: 16px 0; }
        .company { text-align: center; font-size: 14px; color: #475569; margin-bottom: 24px; }
        .body { text-align: center; line-height: 1.8; margin: 24px 40px; }
        .footer { margin-top: 40px; text-align: center; }
        .date { margin-top: 32px; text-align: center; font-size: 11px; color: #64748b; }
    </style>
</head>
<body>
    <div class="border">
        <div class="header">
            <h1>DINAS LINGKUNGAN HIDUP KOTA PALU</h1>
            <p>Bidang Tata Penataan</p>
        </div>

        <div class="title">SERTIFIKAT</div>
        <div class="subtitle">Kehadiran Sosialisasi Lingkungan Hidup</div>

        <div class="body">
            Diberikan kepada perusahaan:
        </div>

        <div class="recipient">{{ $peserta->objekPengawasan?->nama_perusahaan }}</div>
        <div class="company">Penanggung Jawab: {{ $peserta->objekPengawasan?->nama_penanggung_jawab }}</div>

        <div class="body">
            Sebagai bukti kehadiran dalam kegiatan sosialisasi<br>
            <strong>"{{ $sosialisasi->judul }}"</strong><br>
            yang diselenggarakan pada {{ $sosialisasi->tanggal->format('d F Y') }}.
        </div>

        <div class="footer">
            <p>Kepala Bidang Tata Penataan</p>
            <br><br><br>
            <p><strong>DLH Kota Palu</strong></p>
        </div>

        <div class="date">
            Palu, {{ $sosialisasi->tanggal->format('d F Y') }}
        </div>
    </div>
</body>
</html>
