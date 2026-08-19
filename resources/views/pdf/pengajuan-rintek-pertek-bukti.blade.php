<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Bukti Pengajuan {{ $pengajuan->nomor_pengajuan }}</title>
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
        .section-title { font-size: 13px; font-weight: bold; color: #059669; margin-top: 16px; margin-bottom: 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
        .footer { margin-top: 32px; font-size: 10px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>DINAS LINGKUNGAN HIDUP KOTA PALU</h1>
        <p>Bukti Pengajuan RINTEK/PERTEK</p>
    </div>

    <div class="ticket">
        <div>Nomor Pengajuan</div>
        <strong>{{ $pengajuan->nomor_pengajuan }}</strong>
    </div>

    <div class="section-title">Data Perusahaan</div>
    <table>
        <tr><td class="label">Nama Perusahaan</td><td>{{ $pengajuan->nama_perusahaan }}</td></tr>
        <tr><td class="label">Nama Penanggung Jawab</td><td>{{ $pengajuan->nama_penanggung_jawab }}</td></tr>
        <tr><td class="label">NIB</td><td>{{ $pengajuan->nomor_nib }}</td></tr>
        <tr><td class="label">NPWP</td><td>{{ $pengajuan->npwp ?? '-' }}</td></tr>
        <tr><td class="label">Jenis Usaha</td><td>{{ $pengajuan->jenis_usaha }}</td></tr>
        <tr><td class="label">Alamat Lengkap</td><td>{{ $pengajuan->alamat_lengkap }}</td></tr>
        <tr><td class="label">Nomor Telepon</td><td>{{ $pengajuan->nomor_telepon }}</td></tr>
    </table>

    <div class="section-title">Data Pengajuan</div>
    <table>
        <tr><td class="label">Jenis Pengajuan</td><td>{{ $pengajuan->jenis_pengajuan }}</td></tr>
        <tr><td class="label">Tanggal Pengajuan</td><td>{{ $pengajuan->created_at->format('d F Y H:i') }} WITA</td></tr>
        <tr><td class="label">Status</td><td>{{ $pengajuan->status?->label() ?? $pengajuan->status }}</td></tr>
        <tr><td class="label">Keterangan Tambahan</td><td>{{ $pengajuan->keterangan_tambahan ?? '-' }}</td></tr>
    </table>

    <div class="section-title">Dokumen Terunggah</div>
    <table>
        @foreach (\App\Models\PengajuanRintekPertek::DOKUMEN_FIELDS as $field => $label)
            <tr>
                <td class="label">{{ $label }}</td>
                <td>{{ filled($pengajuan->{$field}) ? 'Terunggah' : 'Belum diunggah' }}</td>
            </tr>
        @endforeach
    </table>

    @if ($pengajuan->catatan_verifikasi)
        <div class="section-title">Catatan Verifikasi</div>
        <p>{{ $pengajuan->catatan_verifikasi }}</p>
    @endif

    <div class="footer">
        Dokumen ini dicetak otomatis dari sistem DLH Kota Palu pada {{ now()->format('d/m/Y H:i') }}.
        Simpan nomor pengajuan untuk mengecek status pengajuan.
    </div>
</body>
</html>
