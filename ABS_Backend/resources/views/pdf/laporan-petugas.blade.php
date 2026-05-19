<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Detail Transaksi</title>
    <style>
        @page {
            margin: 130px 40px 60px 40px;
        }

        body {
            font-family: sans-serif;
        }

        #kop-surat {
            position: fixed;
            top: -110px; /* naik ke area margin @page */
            left: 0;
            right: 0;
            height: 70px;
            border-bottom: 3px double #333;
            padding-bottom: 8px;
        }

        #kop-surat .logo {
            float: left;
            width: 80px;
        }

        #kop-surat .logo img {
            width: 70px;
            height: 70px;
        }

        #kop-surat .info {
            margin-left: 90px;
            text-align: center;
        }

        #kop-surat .info h2 {
            margin: 0;
            font-size: 16px;
            text-transform: uppercase;
        }

        #kop-surat .info p {
            margin: 2px 0;
            font-size: 10px;
            color: #555;
        }

        #footer {
            position: fixed;
            bottom: -40px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #777;
            border-top: 1px solid #ccc;
            padding-top: 4px;
        }

        /* Nomor halaman */
        .page-number:after {
            content: counter(page);
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <div id="kop-surat">
        <div class="logo">
            {{-- Gambar harus path absolut atau base64 --}}
            {{-- <img src="{{ public_path('logo.png') }}"> --}}
            Logo
        </div>

        <div class="info">
            <h2>PT. Nama Perusahaan</h2>
            <p>Jl. Contoh Alamat No. 123, Surakarta, Jawa Tengah 57100</p>
            <p>Telp: (0271) 123456 | Email: info@perusahaan.com | www.perusahaan.com</p>
        </div>

        <div style="clear:both;"></div>
    </div>

{{-- Footer --}}
<div id="footer">
    Halaman <span class="page-number"></span>
</div>

{{-- Debug --}}
{{-- <p>
    {{$pengepul->sortByDesc('jumlah_transaksi')->take(5)->values()}}
</p> --}}

{{-- Isi Laporan --}}
<h2>Laporan Pengepul</h2>

<h3>Top 5 Pengepul (Jumlah Transaksi)</h3>
<ol>
    @foreach ($pengepul->sortByDesc('jumlah_transaksi')->take(5)->values() as $d)
    <li>{{ $d["nama"] . " ( " . $d["lembaga"] . " ): " . $d["jumlah_transaksi"] }}</li>
    @endforeach
</ol>

<h3>Top 5 Pengepul (Total Harga)</h3>
<ol>
    @foreach ($pengepul->sortByDesc('total_harga')->take(5)->values() as $d)
    <li>{{ $d["nama"] . " ( " . $d["lembaga"] . " ): " . $d["total_harga"] }}</li>
    @endforeach
</ol>

<h3>Top 5 Pengepul (Total Berat)</h3>
<ol>
    @foreach ($pengepul->sortByDesc('total_berat')->take(5)->values() as $d)
    <li>{{ $d["nama"] . " ( " . $d["lembaga"] . " ): " . $d["total_berat"] }}</li>
    @endforeach
</ol>

<div style="page-break-after: always;"></div>

<h2>Laporan Nasabah</h2>

<h3>Top 5 Nasabah (Jumlah Transaksi)</h3>
<ol>
    @foreach ($nasabah->sortByDesc('jumlah_transaksi')->take(5)->values() as $d)
    <li>{{ $d["nama"] . ": " . $d["jumlah_transaksi"] }}</li>
    @endforeach
</ol>

{{-- <h3>Top 5 Pengepul (Total Harga)</h3>
<ol>
    @foreach ($pengepul->sortByDesc('total_harga')->take(5)->values() as $d)
    <li>{{ $d["nama"] . " ( " . $d["lembaga"] . " ): " . $d["total_harga"] }}</li>
    @endforeach
</ol> --}}

<h3>Top 5 Nasabah (Total Berat)</h3>
<ol>
    @foreach ($pengepul->sortByDesc('total_berat')->take(5)->values() as $d)
    <li>{{ $d["nama"] . ": " . $d["total_berat"] }}</li>
    @endforeach
</ol>

<div style="page-break-after: always;"></div>

<h2>Laporan Sampah</h2>

<table width="100%">
    <tr>
        <td width="50%" valign="top">
            <h2>Laporan Pembelian Sampah</h2>

            <h3>Top 5 Sampah (Jumlah Transaksi)</h3>
            <ol>
                @foreach ($pembelianSampah->sortByDesc('jumlah_transaksi')->take(5)->values() as $d)
                <li>{{ $d["nama"] . " ( " . $d["gudang"] . " ): " . $d["jumlah_transaksi"] }}</li>
                @endforeach
            </ol>

            {{-- <h3>Top 5 Sampah (Total Harga)</h3>
            <ol>
                @foreach ($pembelianSampah->sortByDesc('total_harga')->take(5)->values() as $d)
                <li>{{ $d["nama"] . " ( " . $d["gudang"] . " ): " . $d["total_harga"] }}</li>
                @endforeach
            </ol> --}}

            <h3>Top 5 Sampah (Total Berat)</h3>
            <ol>
                @foreach ($pembelianSampah->sortByDesc('total_berat')->take(5)->values() as $d)
                <li>{{ $d["nama"] . " ( " . $d["gudang"] . " ): " . $d["total_berat"] }}</li>
                @endforeach
            </ol>
        </td>
        <td width="50%" valign="top">
            <h2>Laporan Penjualan Sampah</h2>

            <h3>Top 5 Sampah (Jumlah Transaksi)</h3>
            <ol>
                @foreach ($penjualanSampah->sortByDesc('jumlah_transaksi')->take(5)->values() as $d)
                <li>{{ $d["nama"] . " ( " . $d["gudang"] . " ): " . $d["jumlah_transaksi"] }}</li>
                @endforeach
            </ol>

            <h3>Top 5 Sampah (Total Harga)</h3>
            <ol>
                @foreach ($penjualanSampah->sortByDesc('total_harga')->take(5)->values() as $d)
                <li>{{ $d["nama"] . " ( " . $d["gudang"] . " ): " . $d["total_harga"] }}</li>
                @endforeach
            </ol>

            <h3>Top 5 Sampah (Total Berat)</h3>
            <ol>
                @foreach ($penjualanSampah->sortByDesc('total_berat')->take(5)->values() as $d)
                <li>{{ $d["nama"] . " ( " . $d["gudang"] . " ): " . $d["total_berat"] }}</li>
                @endforeach
            </ol>
        </td>
    </tr>
</table>

</body>
</html>
