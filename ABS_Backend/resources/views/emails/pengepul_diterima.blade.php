<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Pengepul Diterima - Aplikasi Bank Sampah</title>
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
            @php $konfig = \App\Models\KonfigurasiWeb::first(); @endphp
            @if($konfig && $konfig->logo)
                @php
                    $logoUrl = \Illuminate\Support\Str::startsWith($konfig->logo, 'http') ? $konfig->logo : url('storage/' . $konfig->logo);
                @endphp
                <img src="{{ $logoUrl }}" alt="Logo ABS" style="max-height: 50px; margin-bottom: 8px;">
            @endif
            <h1>Pendaftaran Diterima</h1>
            <p>Selamat Bergabung di Ekosistem Kami</p>
        </div>
        <div class="content">
            <div class="status-badge">PENDAFTARAN DISETUJUI ✅</div>
            <h2>Halo, {{ $pengepul->nama }}! 👋</h2>
            <p>
                Kami dengan senang hati menginformasikan bahwa pendaftaran Anda sebagai <strong>Pengepul</strong> untuk lembaga <strong>{{ $pengepul->nama_lembaga }}</strong> telah kami <strong>TERIMA</strong>.
            </p>
            
            <div class="details-card">
                <div class="details-row">
                    <span class="details-label">Username:</span>
                    <span class="details-value">{{ $pengepul->username }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">Lembaga:</span>
                    <span class="details-value">{{ $pengepul->nama_lembaga }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">Status Akun:</span>
                    <span class="details-value" style="color: #2E7D32;">Aktif</span>
                </div>
            </div>

            <p>
                Sekarang Anda dapat masuk ke dashboard pengepul untuk mulai mengelola transaksi sampah dan berkontribusi bagi lingkungan.
            </p>

            <a href="{{ config('app.frontend_url') }}/login" class="cta-button">Masuk ke Dashboard</a>
        </div>
        <div class="footer">
            <p>Jika Anda memiliki pertanyaan, silakan hubungi tim dukungan kami.</p>
            <p>© {{ date('Y') }} Aplikasi Bank Sampah (ABS). All rights reserved.</p>
        </div>
    </div>
</body>
</html>
