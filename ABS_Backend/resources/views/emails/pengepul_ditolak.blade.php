<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informasi Pendaftaran Pengepul - Aplikasi Bank Sampah</title>
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
            background-color: #7A3E3E;
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
            color: #7A3E3E;
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
            background-color: #FFF5F5;
            color: #C53030;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 30px;
            border: 1px solid #FED7D7;
        }
        .reason-card {
            background-color: #FFF5F5;
            border-left: 4px solid #C53030;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: left;
        }
        .reason-title {
            font-weight: 700;
            color: #C53030;
            font-size: 14px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .reason-text {
            color: #2D3748;
            font-size: 15px;
            line-height: 1.5;
            font-style: italic;
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
            <h1>Informasi Pendaftaran</h1>
            <p>Update Status Registrasi Pengepul</p>
        </div>
        <div class="content">
            <div class="status-badge">PENDAFTARAN DITOLAK ❌</div>
            <h2>Halo, {{ $namaPengepul }}</h2>
            <p>
                Terima kasih atas minat Anda bergabung dengan Aplikasi Bank Sampah. Mohon maaf, setelah melakukan tinjauan terhadap berkas pendaftaran Anda, saat ini kami <strong>belum dapat menyetujui</strong> permohonan Anda.
            </p>
            
            <div class="reason-card">
                <div class="reason-title">Alasan Penolakan:</div>
                <div class="reason-text">"{{ $ket_status }}"</div>
            </div>

            <p>
                Anda dapat mencoba mendaftar kembali dengan memastikan seluruh dokumen pendukung yang diunggah sudah sesuai dengan ketentuan yang berlaku.
            </p>
        </div>
        <div class="footer">
            <p>Jika Anda merasa ini adalah kesalahan, silakan hubungi tim dukungan kami.</p>
            <p>© {{ date('Y') }} Aplikasi Bank Sampah (ABS). All rights reserved.</p>
        </div>
    </div>
</body>
</html>
