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
            <img src="{{ storage_path('app/public/' . $config->logo) ?? public_path('logo.png') }}">
            {{-- Logo --}}
        </div>

        <div class="info">
            <h2>Aplikasi Bank Sampah</h2>
            <p>{{ $config->alamat }}</p>
            <p>Telp: {{ $config->no_telp }} | Email: {{ $config->email }}</p>
        </div>

        <div style="clear:both;"></div>
    </div>

{{-- Footer --}}
<div id="footer">
    Halaman <span class="page-number"></span>
</div>

{{-- Debug --}}
{{-- <p style="word-wrap: break-word;">
    {{ $dataStatistik['total_stok'] }}
</p> --}}

{{-- Isi Laporan --}}
<h2>Ringkasan</h2>

<table width="100%">
    <tr>
        <td width="25%" valign="top">Total Transaksi: {{ $dataStatistik['total_transaksi'] }}</td>
        <td width="25%" valign="top">Total Stok: {{ $dataStatistik['total_stok'] }}</td>
        <td width="25%" valign="top">Total Terverifikasi: {{ $dataStatistik['total_transaksi_selesai'] }}</td>
        <td width="25%" valign="top">Total Pending: {{ $dataStatistik['total_transaksi_pending'] }}</td>
    </tr>
</table>

<h3>Info Gudang</h3>
<table width="100%">
    @foreach ($gudang->sortByDesc('total_stok')->take(5)->values() as $g)
    <tr>
        <td width="5%" valign="top">{{ $loop->iteration }}.</td>
        <td valign="top">{{ $g["alamat"] }}</tdidth=>
        <td valign="top">{{ $g["jumlah_transaksi"] }}</td>
        <td valign="top">{{ $g["total_stok"] }}</td>
        <td valign="top">{{ $g["jumlah_transaksi_selesai"] }}</td>
        <td valign="top">{{ $g["jumlah_transaksi_pending"] }}</td>
    </tr>
    @endforeach
</table>

<h3>Distribusi Sampah</h3>
<table width="100%">
    @foreach ($totalStokItem->sortByDesc('stok')->take(10)->values() as $s)
    <tr>
        <td width="5%" valign="top">{{ $loop->iteration }}.</td>
        <td valign="top">{{ $s["nama"] }}</tdidth=>
        <td valign="top">{{ $s["stok"] }}</tdidth=>
        <td valign="top">{{ $s["persentase"] }}</tdidth=>
    </tr>
    @endforeach
</table>

<div style="page-break-after: always;"></div>

<h2>Laporan Top 5 Pengepul</h2>

<h3>Jumlah Transaksi</h3>
<table width="100%">
    @foreach ($pengepul->sortByDesc('jumlah_transaksi')->take(5)->values() as $d)
    <tr>
        <td width="5%" valign="top">{{ $loop->iteration }}.</td>
        <td width="45%" valign="top">{{ $d["nama"] . " (" . $d["lembaga"] . ")" }}</td>
        <td width="50%" valign="top">{{ ": " . $d["jumlah_transaksi"] }}</td>
    </tr>
    @endforeach
</table>

<h3>Total Harga (Rp)</h3>
<table width="100%">
    @foreach ($pengepul->sortByDesc('total_harga')->take(5)->values() as $d)
    <tr>
        <td width="5%" valign="top">{{ $loop->iteration }}.</td>
        <td width="45%" valign="top">{{ $d["nama"] . " (" . $d["lembaga"] . ")" }}</td>
        <td width="50%" valign="top">{{ ": " . $d["total_harga"] }}</td>
    </tr>
    @endforeach
</table>

<h3>Total Berat (Kg)</h3>
<table width="100%">
    @foreach ($pengepul->sortByDesc('total_berat')->take(5)->values() as $d)
    <tr>
        <td width="5%" valign="top">{{ $loop->iteration }}.</td>
        <td width="45%" valign="top">{{ $d["nama"] . " (" . $d["lembaga"] . ")" }}</td>
        <td width="50%" valign="top">{{ ": " . $d["total_berat"] }}</td>
    </tr>
    @endforeach
</table>

<div style="page-break-after: always;"></div>

<h2>Laporan Top 5 Nasabah</h2>

<h3>Jumlah Transaksi</h3>
<table width="100%">
    @foreach ($nasabah->sortByDesc('jumlah_transaksi')->take(5)->values() as $d)
    <tr>
        <td width="5%" valign="top">{{ $loop->iteration }}.</td>
        <td width="45%" valign="top">{{ $d["nama"] }}</td>
        <td width="50%" valign="top">{{ ": " . $d["jumlah_transaksi"] }}</td>
    </tr>
    @endforeach
</table>

{{-- <h3>Top 5 Pengepul (Total Harga)</h3>
<ol>
    @foreach ($pengepul->sortByDesc('total_harga')->take(5)->values() as $d)
    <li>{{ $d["nama"] . " ( " . $d["lembaga"] . " ): " . $d["total_harga"] }}</li>
    @endforeach
</ol> --}}

<h3>Total Berat (Kg)</h3>
<table width="100%">
    @foreach ($nasabah->sortByDesc('total_berat')->take(5)->values() as $d)
    <tr>
        <td width="5%" valign="top">{{ $loop->iteration }}.</td>
        <td width="45%" valign="top">{{ $d["nama"] }}</td>
        <td width="50%" valign="top">{{ ": " . $d["total_berat"] }}</td>
    </tr>
    @endforeach
</table>

<div style="page-break-after: always;"></div>

<h2>Laporan Top 5 Sampah</h2>

<table width="100%">
    <tr>
        <td width="50%" valign="top">
            <h2>Pembelian Sampah</h2>

            <h3>Jumlah Transaksi</h3>
            <table width="100%">
                @foreach ($pembelianSampah->sortByDesc('jumlah_transaksi')->take(5)->values() as $d)
                <tr>
                    <td width="5%" valign="top">{{ $loop->iteration }}.</td>
                    <td width="65%" valign="top">{{ $d["nama"] . " (" . $d["gudang"] . ")" }}</td>
                    <td width="30%" valign="top">{{ ": " . $d["jumlah_transaksi"] }}</td>
                </tr>
                @endforeach
            </table>

            {{-- <h3>Top 5 Sampah (Total Harga)</h3>
            <ol>
                @foreach ($pembelianSampah->sortByDesc('total_harga')->take(5)->values() as $d)
                <li>{{ $d["nama"] . " ( " . $d["gudang"] . " ): " . $d["total_harga"] }}</li>
                @endforeach
            </ol> --}}

            <h3>Top 5 Sampah (Total Berat)</h3>
            <table width="100%">
                @foreach ($pembelianSampah->sortByDesc('total_berat')->take(5)->values() as $d)
                <tr>
                    <td width="5%" valign="top">{{ $loop->iteration }}.</td>
                    <td width="65%" valign="top">{{ $d["nama"] . " (" . $d["gudang"] . ")" }}</td>
                    <td width="30%" valign="top">{{ ": " . $d["total_berat"] }}</td>
                </tr>
                @endforeach
            </table>
        </td>
        <td width="50%" valign="top">
            <h2>Penjualan Sampah</h2>

            <h3>Jumlah Transaksi</h3>
            <table width="100%">
                @foreach ($penjualanSampah->sortByDesc('jumlah_transaksi')->take(5)->values() as $d)
                <tr>
                    <td width="5%" valign="top">{{ $loop->iteration }}.</td>
                    <td width="65%" valign="top">{{ $d["nama"] . " (" . $d["gudang"] . ")" }}</td>
                    <td width="30%" valign="top">{{ ": " . $d["jumlah_transaksi"] }}</td>
                </tr>
                @endforeach
            </table>

            <h3>Total Harga (Rp)</h3>
            <table width="100%">
                @foreach ($penjualanSampah->sortByDesc('total_harga')->take(5)->values() as $d)
                <tr>
                    <td width="5%" valign="top">{{ $loop->iteration }}.</td>
                    <td width="65%" valign="top">{{ $d["nama"] . " (" . $d["gudang"] . ")" }}</td>
                    <td width="30%" valign="top">{{ ": " . $d["total_harga"] }}</td>
                </tr>
                @endforeach
            </table>

            <h3>Total Berat (Kg)</h3>
            <table width="100%">
                @foreach ($penjualanSampah->sortByDesc('total_berat')->take(5)->values() as $d)
                <tr>
                    <td width="5%" valign="top">{{ $loop->iteration }}.</td>
                    <td width="65%" valign="top">{{ $d["nama"] . " (" . $d["gudang"] . ")" }}</td>
                    <td width="30%" valign="top">{{ ": " . $d["total_berat"] }}</td>
                </tr>
                @endforeach
            </table>
        </td>
    </tr>
</table>

</body>
</html>
