<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email - Aplikasi Bank Sampah</title>
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
            margin-bottom: 30px;
        }
        .otp-container {
            background-color: #4A7043;
            color: #ffffff;
            font-size: 42px;
            font-weight: 800;
            letter-spacing: 12px;
            padding: 25px;
            border-radius: 12px;
            display: inline-block;
            margin-bottom: 30px;
            box-shadow: 0 4px 10px rgba(74, 112, 67, 0.2);
        }
        .warning {
            color: #d97706;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
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
        .tips {
            font-style: italic;
            margin-bottom: 15px;
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
            <h1>Aplikasi Bank Sampah</h1>
            <p>Ubah Sampah Jadi Berkah</p>
        </div>
        <div class="content">
            <h2>Halo, Pahlawan Lingkungan! 🌍</h2>
            <p>
                Langkah pertamamu untuk mulai peduli lingkungan sekaligus mendapatkan manfaat finansial sudah dimulai. 
                Kami sangat senang menyambutmu di Aplikasi Bank Sampah (ABS)!
            </p>
            <div style="width: 40px; height: 3px; background-color: #4fd1c5; margin: 0 auto 25px;"></div>
            <p>
                Untuk menyelesaikan pendaftaran dan mengaktifkan akunmu, silakan masukkan 6 digit kode OTP berikut di halaman registrasi:
            </p>
            <div class="otp-container">
                {{ $otp }}
            </div>
            <div class="warning">
                ⚠️ Kode OTP ini bersifat rahasia. Jangan berikan kepada siapa pun.
            </div>
            <p style="font-size: 13px; color: #718096;">
                Kode ini hanya berlaku selama <strong>5 menit</strong>.
            </p>
        </div>
        <div class="footer">
            <p class="tips">💡 Tips: Jika kamu tidak merasa mendaftar di Aplikasi Bank Sampah, silakan abaikan email ini.</p>
            <p>© {{ date('Y') }} Aplikasi Bank Sampah (ABS). All rights reserved.</p>
        </div>
    </div>
</body>
</html>
