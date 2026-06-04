<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Status Penjemputan - Aplikasi Bank Sampah</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f6f7f6;
            margin: 0;
            padding: 0;
            color: #5E6460;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #4A7043;
            padding: 40px 20px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 10px 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            padding: 40px 30px;
            text-align: center;
        }
        .content h2 {
            color: #4A7043;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .content p {
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 25px;
            text-align: left;
        }
        .status-badge {
            display: inline-block;
            background-color: #E8F5E9;
            color: #2E7D32;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 30px;
            border: 1px solid #C8E6C9;
        }
        .details-card {
            background-color: #F9FAFB;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: left;
            border: 1px solid #EDF2F7;
        }
        .details-row {
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
        }
        .details-label {
            font-weight: 600;
            color: #718096;
            font-size: 14px;
        }
        .details-value {
            font-weight: 600;
            color: #2D3748;
            font-size: 14px;
        }
        .cta-button {
            display: inline-block;
            background-color: #4A7043;
            color: #ffffff;
            text-decoration: none;
            padding: 14px 30px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 16px;
            transition: background-color 0.3s ease;
            box-shadow: 0 4px 6px rgba(74, 112, 67, 0.2);
        }
        .footer {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            border-top: 1px solid #edf2f7;
        }
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="font-size: 28px; font-weight: 800; margin-bottom: 8px;">ABS</div>
            <h1>Status Penjemputan</h1>
            <p>Aplikasi Bank Sampah</p>
        </div>
        <div class="content">
            @if($status_type == 'menunggu_persetujuan')
                <div class="status-badge" style="background-color: #FFF3E0; color: #E65100; border-color: #FFE0B2;">MENUNGGU PERSETUJUAN ⏳</div>
                <h2>Halo, {{ $penjemputan->nasabah->nama }}!</h2>
                <p>Petugas kami telah mengonfirmasi jadwal penjemputan sampah Anda. Silakan cek aplikasi untuk menyetujui jadwal penjemputan tersebut agar proses bisa dilanjutkan.</p>

            @elseif($status_type == 'proses')
                <div class="status-badge" style="background-color: #E3F2FD; color: #1565C0; border-color: #BBDEFB;">JADWAL TERKONFIRMASI 📅</div>
                <h2>Halo, {{ $penjemputan->nasabah->nama }}!</h2>
                <p>Terima kasih telah menyetujui jadwal penjemputan. Pesanan Anda saat ini sedang dalam status <strong>DIPROSES</strong>. Tukang kami akan segera menyiapkan armada untuk menjemput sampah Anda sesuai jadwal.</p>

            @elseif($status_type == 'dijemput')
                <div class="status-badge" style="background-color: #FFF8E1; color: #F57F17; border-color: #FFECB3;">SEDANG DIJEMPUT 🚚</div>
                <h2>Halo, {{ $penjemputan->nasabah->nama }}!</h2>
                <p>Tukang kami <strong>{{ $penjemputan->tukang->nama ?? 'Petugas ABS' }}</strong> saat ini sedang dalam perjalanan menuju lokasi Anda. Mohon siapkan sampah yang akan ditimbang.</p>

            @elseif($status_type == 'perlu_input')
                <div class="status-badge" style="background-color: #FCE4EC; color: #C2185B; border-color: #F8BBD0;">PROSES PENIMBANGAN ⚖️</div>
                <h2>Halo, {{ $penjemputan->nasabah->nama }}!</h2>
                <p>Sampah Anda telah tiba di gudang. Saat ini petugas kami sedang melakukan proses penimbangan sampah dan akan segera menginputkan data transaksi ke sistem.</p>

            @elseif($status_type == 'selesai')
                <div class="status-badge">PENJEMPUTAN SELESAI ✅</div>
                <h2>Halo, {{ $penjemputan->nasabah->nama }}!</h2>
                <p>Terima kasih! Proses penjemputan dan penimbangan sampah Anda telah selesai. Saldo dari transaksi ini telah berhasil ditambahkan ke akun Anda.</p>
            @endif

            <div class="details-card">
                <div class="details-row">
                    <span class="details-label">ID Penjemputan:</span>
                    <span class="details-value">{{ $penjemputan->penjemputan_id }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">Tukang Penjemput:</span>
                    <span class="details-value">{{ $penjemputan->tukang->nama ?? '-' }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">Jadwal:</span>
                    <span class="details-value">{{ $penjemputan->jadwal ? \Carbon\Carbon::parse($penjemputan->jadwal)->translatedFormat('d F Y, H:i') : '-' }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">Alamat:</span>
                    <span class="details-value">{{ $penjemputan->alamat ?? '-' }}</span>
                </div>
            </div>

            <p>Anda dapat melihat rincian lengkap transaksi Anda melalui aplikasi.</p>

            <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin-top: 20px; text-align: center; width: 100%;">
                <tr>
                    <td align="center">
                        <a href="{{ env('FRONTEND_URL', 'http://localhost:5173') }}/dashboard-nasabah/sampah-saya?highlight_id={{ $penjemputan->penjemputan_id }}" style="display: inline-block; background-color: #4A7043; color: #ffffff !important; text-decoration: none !important; padding: 14px 30px; border-radius: 10px; font-weight: bold; font-size: 16px;">
                            <span style="color: #ffffff;">Cek Status di Aplikasi</span>
                        </a>
                    </td>
                </tr>
            </table>
        </div>
        <div class="footer">
            <p>Jika Anda memiliki pertanyaan, silakan hubungi tim dukungan kami.</p>
            <p>© {{ date('Y') }} Aplikasi Bank Sampah (ABS). All rights reserved.</p>
        </div>
    </div>
</body>
</html>
