<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password Anda</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .header {
            background-color: #4A7043;
            color: #ffffff;
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            padding: 40px 30px;
            color: #333333;
            line-height: 1.6;
        }
        .icon-wrapper {
            text-align: center;
            margin-bottom: 25px;
        }
        .icon-circle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            background-color: #f0f4f0;
            border-radius: 50%;
            color: #4A7043;
        }
        .content h2 {
            color: #4A7043;
            font-size: 20px;
            font-weight: 700;
            text-align: center;
            margin-top: 0;
        }
        .content p {
            font-size: 14px;
            margin-bottom: 20px;
        }
        .btn-wrapper {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            background-color: #4A7043;
            color: #ffffff !important;
            padding: 14px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 16px;
            display: inline-block;
            transition: background-color 0.3s;
        }
        .link-box {
            background-color: #f9f9f9;
            border: 1px solid #eeeeee;
            border-radius: 8px;
            padding: 15px;
            margin-top: 25px;
            word-break: break-all;
        }
        .link-box p {
            margin: 0 0 10px;
            font-size: 12px;
            color: #666666;
        }
        .link-box a {
            color: #4A7043;
            font-size: 13px;
            text-decoration: none;
        }
        .warning-box {
            background-color: #fff9f0;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin-top: 25px;
            border-radius: 4px;
        }
        .warning-box h4 {
            margin: 0 0 5px;
            color: #d97706;
            font-size: 14px;
            font-weight: 700;
        }
        .warning-box p {
            margin: 0;
            font-size: 13px;
            color: #92400e;
        }
        .footer {
            padding: 30px;
            text-align: center;
            font-size: 12px;
            color: #888888;
            background-color: #fafafa;
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
            <h1>Aplikasi Bank Sampah</h1>
            <p>Ubah Sampah Jadi Berkah</p>
        </div>
        
        <div class="content">
            <div class="icon-wrapper">
                <div class="icon-circle">
                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </div>
            </div>

            <h2>Reset Password Anda</h2>
            
            <p>Halo, <strong>{{ $name }}</strong>,</p>
            
            <p>Kami menerima permintaan untuk mereset password akun ABS Anda yang terdaftar dengan email <strong>{{ $email }}</strong>.</p>
            
            <p>Untuk melanjutkan proses reset password, silakan klik tombol di bawah ini:</p>
            
            <div class="btn-wrapper">
                <a href="{{ $url }}" class="btn">Reset Password Saya</a>
            </div>
            
            <div class="link-box">
                <p>Atau salin dan tempel link berikut ke browser Anda:</p>
                <a href="{{ $url }}">{{ $url }}</a>
            </div>
            
            <div class="warning-box">
                <h4>⚠️ Penting!</h4>
                <p>Link ini hanya berlaku untuk <strong>1 kali penggunaan</strong> dan akan kedaluwarsa dalam <strong>60 menit</strong>.</p>
            </div>
            
            <p style="margin-top: 30px;">Jika Anda tidak meminta reset password, abaikan email ini. Password Anda tidak akan berubah.</p>
            
            <p>Terima kasih,<br><strong>Tim ABS</strong></p>
        </div>
        
        <div class="footer">
            <p>Email ini dikirim secara otomatis, mohon untuk tidak membalas email ini.</p>
            <p>&copy; {{ date('Y') }} Aplikasi Bank Sampah (ABS). All rights reserved.</p>
        </div>
    </div>
</body>
</html>
