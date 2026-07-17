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
            grid-template-columns: repeat(2, 1fr);
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #e2e8f0;
            padding: 8px 12px;
            text-align: left;
            font-size: 11px;
        }
        th {
            background: #f1f5f9;
            font-weight: bold;
            color: #475569;
        }
        tr:nth-child(even) {
            background: #f8fafc;
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
        <h1> LAPORAN PEMBINAAN & SOSIALISASI </h1>
        <p>Dinas Lingkungan Hidup Kota Palu</p>
        <p>Periode: {{ $bulan }}</p>
    </div>

    <div class="section">
        <h2>REKAPITULASI</h2>
        <div class="stat-grid">
            <div class="stat-box">
                <div class="value">{{ $rekap['total_sosialisasi'] }}</div>
                <div class="label">Total Kegiatan</div>
            </div>
            <div class="stat-box">
                <div class="value">{{ $rekap['total_peserta'] }}</div>
                <div class="label">Total Peserta</div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>DAFTAR KEGIATAN</h2>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Judul</th>
                    <th>Jumlah Peserta</th>
                    <th>Evaluasi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sosialisasis as $sosialisasi)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $sosialisasi->tanggal->format('d M Y') }}</td>
                        <td>{{ $sosialisasi->judul }}</td>
                        <td>{{ $sosialisasi->pesertas->count() }}</td>
                        <td>{{ Str::limit($sosialisasi->hasil_evaluasi, 30) ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }}</p>
        <p>Sistem Informasi DLH Kota Palu</p>
    </div>
</body>
</html>
