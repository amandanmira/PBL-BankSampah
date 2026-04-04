<!DOCTYPE html>
<html>

<head>
    <title>Verifikasi Email Anda</title>
</head>

<body>
    <h2>Halo, {{ $nasabah->nama }}!</h2>
    <p>Terima kasih telah mendaftar. Silakan klik tombol di bawah ini untuk memverifikasi alamat email Anda dan
        mengaktifkan akun Anda.</p>

    <a href="http://localhost:5173/verify-email/{{ $verification_token }}"
        style="background-color: #4CAF50; color: white; padding: 14px 25px; text-align: center; text-decoration: none; display: inline-block;">
        Verifikasi Email
    </a>

    <p>Jika Anda tidak bisa mengklik tombol di atas, salin dan tempel URL berikut ke browser Anda:</p>
    <p>http://localhost:5173/verify-email/{{ $verification_token }}</p>

    <br>
    <p>Terima kasih,</p>
    <p>Tim Bank Sampah</p>
</body>

</html>
