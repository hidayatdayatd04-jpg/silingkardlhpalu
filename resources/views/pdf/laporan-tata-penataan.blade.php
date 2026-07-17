<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Tata Penataan - DLH Kota Palu</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #059669; padding-bottom: 10px; }
        .header h2 { margin: 5px 0; color: #059669; font-size: 18px; text-transform: uppercase; }
        .header p { margin: 2px 0; color: #475569; font-size: 12px; }
        .stat-grid { display: flex; gap: 15px; margin: 20px 0; }
        .stat-box { flex: 1; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; text-align: center; }
        .stat-box .value { font-size: 24px; font-weight: bold; color: #059669; }
        .stat-box .label { font-size: 10px; color: #64748b; text-transform: uppercase; margin-top: 5px; }
        .footer { margin-top: 30px; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan & Statistik Tata Penataan</h2>
        <p>Dinas Lingkungan Hidup Kota Palu</p>
        <p>Periode: {{ $bulan }}</p>
    </div>

    <div class="stat-grid">
        <div class="stat-box">
            <div class="value">{{ $rekap['pengaduan'] }}</div>
            <div class="label">Pengaduan</div>
        </div>
        <div class="stat-box">
            <div class="value">{{ $rekap['sidak'] }}</div>
            <div class="label">Sidak</div>
        </div>
        <div class="stat-box">
            <div class="value">{{ $rekap['pelanggaran'] }}</div>
            <div class="label">Pelanggaran</div>
        </div>
        <div class="stat-box">
            <div class="value">{{ $rekap['sanksi'] }}</div>
            <div class="label">Sanksi</div>
        </div>
    </div>

    <div class="footer">
        <p>Dokumen ini digenerate secara otomatis pada {{ now()->translatedFormat('d F Y, H:i') }} WITA</p>
        <p>Dinas Lingkungan Hidup Kota Palu</p>
    </div>
</body>
</html>
