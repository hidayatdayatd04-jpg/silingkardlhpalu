<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pengajuan</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px 20px;
        }
        .info-box {
            background-color: #f8fafc;
            border-left: 4px solid #059669;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-row {
            display: flex;
            margin-bottom: 10px;
        }
        .info-row:last-child {
            margin-bottom: 0;
        }
        .info-label {
            font-weight: 600;
            color: #475569;
            min-width: 140px;
        }
        .info-value {
            color: #1e293b;
        }
        .ticket-box {
            background-color: #f0fdf4;
            border: 2px dashed #059669;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            text-align: center;
        }
        .ticket-label {
            font-size: 12px;
            color: #059669;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .ticket-number {
            font-size: 24px;
            font-weight: 700;
            color: #065f46;
            margin-top: 8px;
            font-family: monospace;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px;
            text-align: center;
            color: #64748b;
            font-size: 14px;
            border-top: 1px solid #e2e8f0;
        }
        .footer p {
            margin: 5px 0;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .message {
            color: #475569;
            margin: 15px 0;
        }
        .cta-button {
            display: inline-block;
            background-color: #059669;
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 600;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🌿 Dinas Lingkungan Hidup Kota Palu</h1>
        </div>
        
        <div class="content">
            <p class="greeting">Yth. <strong>{{ $nama_pemohon }}</strong>,</p>
            
            <p class="message">
                Terima kasih telah mengirimkan <strong>{{ $jenis_pengajuan }}</strong> kepada Dinas Lingkungan Hidup Kota Palu.
            </p>
            
            <p class="message">
                Pengajuan Anda telah berhasil diterima dan sedang dalam proses verifikasi. Silakan simpan nomor tiket di bawah untuk mengecek status pengajuan Anda.
            </p>
            
            <div class="ticket-box">
                <div class="ticket-label">Nomor Tiket Anda</div>
                <div class="ticket-number">{{ $nomor_tiket }}</div>
            </div>
            
            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">Jenis Pengajuan:</span>
                    <span class="info-value">{{ $jenis_pengajuan }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Nama Pemohon:</span>
                    <span class="info-value">{{ $nama_pemohon }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Waktu Pengajuan:</span>
                    <span class="info-value">{{ $tanggal }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">Diterima - Menunggu Verifikasi</span>
                </div>
            </div>
            
            <p class="message">
                Anda dapat mengecek status pengajuan kapan saja melalui website kami dengan memasukkan nomor tiket di atas.
            </p>
            
            <p class="message">
                Jika ada pertanyaan, silakan hubungi kami atau kunjungi kantor Dinas Lingkungan Hidup Kota Palu.
            </p>
        </div>
        
        <div class="footer">
            <p><strong>Dinas Lingkungan Hidup Kota Palu</strong></p>
            <p>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
            <p>&copy; {{ date('Y') }} DLH Kota Palu. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
