<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            color: #1e293b;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #059669;
            padding-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
            color: #059669;
        }
        .header p {
            font-size: 12px;
            color: #64748b;
            margin: 5px 0 0;
        }
        .section {
            margin-bottom: 20px;
        }
        .section h2 {
            font-size: 14px;
            font-weight: bold;
            color: #059669;
            margin-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
        }
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-box {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        .stat-box .value {
            font-size: 24px;
            font-weight: bold;
            color: #059669;
        }
        .stat-box .label {
            font-size: 11px;
            color: #64748b;
            margin-top: 5px;
        }
        .highlight {
            background: linear-gradient(135deg, #059669, #14b8a6);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
        }
        .highlight .value {
            font-size: 36px;
            font-weight: bold;
        }
        .highlight .label {
            font-size: 12px;
            opacity: 0.9;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1> LAPORAN KETAATAN PERUSAHAAN </h1>
        <p>Dinas Lingkungan Hidup Kota Palu</p>
        <p>Periode: {{ $bulan }}</p>
    </div>

    <div class="highlight">
        <div class="label">Persentase Ketaatan</div>
        <div class="value">{{ $rekap['persentase_ketaatan'] }}%</div>
    </div>

    <div class="section">
        <h2>REKAPITULASI</h2>
        <div class="stat-grid">
            <div class="stat-box">
                <div class="value">{{ $rekap['total_sidak'] }}</div>
                <div class="label">Total Sidak</div>
            </div>
            <div class="stat-box">
                <div class="value" style="color: #10b981;">{{ $rekap['taat'] }}</div>
                <div class="label">Taat</div>
            </div>
            <div class="stat-box">
                <div class="value" style="color: #ef4444;">{{ $rekap['tidak_taat'] }}</div>
                <div class="label">Tidak Taat</div>
            </div>
            <div class="stat-box">
                <div class="value" style="color: #f59e0b;">{{ $rekap['perlu_pembinaan'] }}</div>
                <div class="label">Perlu Pembinaan</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }}</p>
        <p>Sistem Informasi DLH Kota Palu</p>
    </div>
</body>
</html>
