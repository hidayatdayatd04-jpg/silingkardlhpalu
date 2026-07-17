<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Bukti Permohonan {{ $permohonan->nomor_tiket }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; }
        .header { text-align: center; margin-bottom: 24px; border-bottom: 2px solid #059669; padding-bottom: 12px; }
        .header h1 { font-size: 16px; margin: 0 0 4px; }
        .header p { margin: 0; color: #64748b; font-size: 11px; }
        .ticket { text-align: center; background: #f1f5f9; padding: 12px; margin: 16px 0; border-radius: 4px; }
        .ticket strong { font-size: 18px; letter-spacing: 1px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        td { padding: 6px 8px; vertical-align: top; border-bottom: 1px solid #e2e8f0; }
        td.label { width: 35%; font-weight: bold; color: #475569; }
        .footer { margin-top: 32px; font-size: 10px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>DINAS LINGKUNGAN HIDUP KOTA PALU</h1>
        <p>Bukti Pengajuan Permohonan/Rekomendasi</p>
    </div>

    <div class="ticket">
        <div>Nomor Tiket</div>
        <strong>{{ $permohonan->nomor_tiket }}</strong>
    </div>

    <table>
        <tr><td class="label">Nama Perusahaan</td><td>{{ $permohonan->nama_perusahaan }}</td></tr>
        <tr><td class="label">Nama Pemilik/PJ</td><td>{{ $permohonan->nama_pemilik }}</td></tr>
        <tr><td class="label">NPWP</td><td>{{ $permohonan->npwp }}</td></tr>
        <tr><td class="label">Jenis Usaha</td><td>{{ $permohonan->jenis_usaha }}</td></tr>
        <tr><td class="label">Alamat</td><td>{{ $permohonan->alamat_lengkap }}</td></tr>
        <tr><td class="label">Telepon</td><td>{{ $permohonan->nomor_telepon }}</td></tr>
        <tr><td class="label">Email</td><td>{{ $permohonan->email }}</td></tr>
        <tr><td class="label">Jenis Pengajuan</td><td>{{ $permohonan->jenis_pengajuan }}</td></tr>
        <tr><td class="label">Tanggal Pengajuan</td><td>{{ $permohonan->created_at->format('d F Y H:i') }} WITA</td></tr>
        <tr><td class="label">Status</td><td>{{ $permohonan->status }}</td></tr>
        <tr><td class="label">Jumlah Dokumen Pendukung</td><td>{{ $permohonan->dokumens->count() }} file</td></tr>
    </table>

    <div class="footer">
        Dokumen ini dicetak otomatis dari sistem DLH Kota Palu pada {{ now()->format('d/m/Y H:i') }}.
        Simpan nomor tiket untuk mengecek status permohonan.
    </div>
</body>
</html>
