<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Surat Sanksi #{{ $sanksi->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 24px; border-bottom: 2px solid #dc2626; padding-bottom: 12px; }
        .header h1 { font-size: 16px; margin: 0 0 4px; }
        .header p { margin: 0; color: #64748b; font-size: 11px; }
        .title { text-align: center; font-size: 14px; font-weight: bold; margin: 20px 0; text-decoration: underline; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        td { padding: 6px 8px; vertical-align: top; border-bottom: 1px solid #e2e8f0; }
        td.label { width: 35%; font-weight: bold; color: #475569; }
        .body-text { text-align: justify; margin: 16px 0; }
        .footer { margin-top: 48px; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>DINAS LINGKUNGAN HIDUP KOTA PALU</h1>
        <p>Surat Sanksi Administratif Bidang Tata Penataan</p>
    </div>

    <div class="title">SURAT SANKSI ADMINISTRATIF</div>

    <table>
        <tr><td class="label">Perusahaan</td><td>{{ $sanksi->pelanggaran?->sidak?->objekPengawasan?->nama_perusahaan }}</td></tr>
        <tr><td class="label">Penanggung Jawab</td><td>{{ $sanksi->pelanggaran?->sidak?->objekPengawasan?->nama_penanggung_jawab }}</td></tr>
        <tr><td class="label">Alamat</td><td>{{ $sanksi->pelanggaran?->sidak?->objekPengawasan?->alamat }}</td></tr>
        <tr><td class="label">Jenis Pelanggaran</td><td>{{ $sanksi->pelanggaran?->jenis_pelanggaran }}</td></tr>
        @if ($sanksi->pelanggaran?->pasal_dilanggar)
            <tr><td class="label">Pasal Dilanggar</td><td>{{ $sanksi->pelanggaran->pasal_dilanggar }}</td></tr>
        @endif
        <tr><td class="label">Jenis Sanksi</td><td>{{ $sanksi->jenis_sanksi?->label() }}</td></tr>
        <tr><td class="label">Status Sanksi</td><td>{{ $sanksi->status_sanksi?->label() }}</td></tr>
        @if ($sanksi->batas_waktu_perbaikan)
            <tr><td class="label">Batas Waktu Perbaikan</td><td>{{ $sanksi->batas_waktu_perbaikan->format('d F Y') }}</td></tr>
        @endif
    </table>

    <div class="body-text">
        Berdasarkan hasil sidak dan temuan pelanggaran lingkungan hidup sebagaimana tercantum di atas,
        dengan ini diberikan sanksi administratif berupa <strong>{{ $sanksi->jenis_sanksi?->label() }}</strong>
        kepada objek pengawasan tersebut.
        @if ($sanksi->catatan)
            {{ $sanksi->catatan }}
        @endif
    </div>

    <div class="footer">
        <p>Palu, {{ now()->format('d F Y') }}</p>
        <p>Kepala Bidang Tata Penataan</p>
        <br><br><br>
        <p><strong>DLH Kota Palu</strong></p>
    </div>

    <p style="margin-top: 40px; font-size: 10px; color: #94a3b8; text-align: center;">
        Dokumen dicetak otomatis dari sistem DLH Kota Palu pada {{ now()->format('d/m/Y H:i') }}.
    </p>
</body>
</html>
