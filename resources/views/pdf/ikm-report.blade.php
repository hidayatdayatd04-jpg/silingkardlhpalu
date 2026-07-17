<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Survei IKM - DLH Kota Palu</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #059669;
            padding-pb: 10px;
        }
        .header h2 {
            margin: 5px 0;
            color: #059669;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header p {
            margin: 2px 0;
            color: #475569;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: center;
        }
        th {
            background-color: #f8fafc;
            color: #1e293b;
            font-weight: bold;
        }
        .left {
            text-align: left;
        }
        .summary-box {
            margin-top: 25px;
            padding: 15px;
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            font-size: 12px;
            color: #166534;
        }
        .summary-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Dinas Lingkungan Hidup Kota Palu</h2>
        <p>Laporan Hasil Survei Indeks Kepuasan Masyarakat (IKM)</p>
        <p>Dicetak pada: {{ now()->format('d-m-Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 7%">I1</th>
                <th style="width: 7%">I2</th>
                <th style="width: 7%">I3</th>
                <th style="width: 7%">I4</th>
                <th style="width: 7%">I5</th>
                <th style="width: 7%">I6</th>
                <th style="width: 7%">I7</th>
                <th class="left" style="width: 38%">Saran</th>
                <th style="width: 10%">Rata-rata</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->indikator_1 }}</td>
                    <td>{{ $item->indikator_2 }}</td>
                    <td>{{ $item->indikator_3 }}</td>
                    <td>{{ $item->indikator_4 }}</td>
                    <td>{{ $item->indikator_5 }}</td>
                    <td>{{ $item->indikator_6 }}</td>
                    <td>{{ $item->indikator_7 }}</td>
                    <td class="left">{{ $item->saran ?? '-' }}</td>
                    <td>{{ number_format($item->nilai_rata_rata, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-box">
        <div class="summary-title">Ringkasan Hasil Survei</div>
        <div>Total Responden: {{ count($items) }} orang</div>
        <div>Rata-rata Indeks Kepuasan: {{ number_format($average, 2) }}</div>
    </div>
</body>
</html>
