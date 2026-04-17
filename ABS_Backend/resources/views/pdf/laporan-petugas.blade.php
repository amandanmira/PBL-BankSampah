<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Detail Transaksi</title>
    <style>
        body { font-family: sans-serif; }
    </style>
</head>
<body>

{{-- <p>
    {{$pengepul->sortByDesc('jumlah_transaksi')->take(5)->values()}}
</p> --}}

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
