<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Penarikan Saldo Nasabah</title>
    <style>
        @page {
            margin: 130px 45px 60px 45px;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            font-size: 12px;
            line-height: 1.4;
        }

        #kop-surat {
            position: fixed;
            top: -110px;
            left: 0;
            right: 0;
            height: 80px;
            border-bottom: 2px solid #2d4a3e;
            padding-bottom: 10px;
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
            font-size: 18px;
            text-transform: uppercase;
            color: #2d4a3e;
            font-weight: bold;
        }

        #kop-surat .info p {
            margin: 3px 0;
            font-size: 11px;
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
            border-top: 1px solid #ddd;
            padding-top: 6px;
        }

        .page-number:after {
            content: counter(page);
        }

        .title-section {
            text-align: center;
            margin-top: 10px;
            margin-bottom: 25px;
        }

        .title-section h1 {
            margin: 0;
            font-size: 18px;
            color: #2d4a3e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .title-section p {
            margin: 5px 0 0 0;
            font-size: 11px;
            color: #666;
        }

        /* Summary Cards / Boxes */
        .summary-container {
            width: 100%;
            margin-bottom: 25px;
            border-collapse: collapse;
        }

        .summary-card {
            background-color: #f8faf9;
            border: 1px solid #e1e8e4;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
        }

        .summary-card .label {
            font-size: 10px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 5px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .summary-card .value {
            font-size: 16px;
            font-weight: bold;
            color: #2d4a3e;
        }

        .summary-card .sub-value {
            font-size: 9px;
            color: #888;
            margin-top: 3px;
        }

        /* Tables styling */
        .section-title {
            font-size: 13px;
            color: #2d4a3e;
            border-bottom: 2px solid #e1e8e4;
            padding-bottom: 5px;
            margin-top: 25px;
            margin-bottom: 12px;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table.data-table th {
            background-color: #2d4a3e;
            color: white;
            font-weight: bold;
            text-align: left;
            padding: 8px 10px;
            font-size: 11px;
            text-transform: uppercase;
            border: 1px solid #2d4a3e;
        }

        table.data-table td {
            padding: 8px 10px;
            border: 1px solid #e1e8e4;
            font-size: 11px;
        }

        table.data-table tr:nth-child(even) td {
            background-color: #f8faf9;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        /* Top Nasabah Grid Layout */
        .grid-table {
            width: 100%;
        }

        .grid-col {
            width: 48%;
            vertical-align: top;
        }

        .grid-spacer {
            width: 4%;
        }

        .badge-selesai {
            background-color: #d1e7dd;
            color: #0f5132;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            display: inline-block;
        }

        .badge-pending {
            background-color: #fff3cd;
            color: #664d03;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            display: inline-block;
        }
    </style>
</head>
<body>

    {{-- Header / Kop Surat --}}
    <div id="kop-surat">
        <div class="logo">
            @php
                $logoPath = $config->logo && file_exists(storage_path('app/public/' . $config->logo)) 
                            ? storage_path('app/public/' . $config->logo) 
                            : public_path('logo.png');
            @endphp
            <img src="{{ $logoPath }}">
        </div>

        <div class="info">
            <h2>{{ $config->nama_aplikasi ?? 'Aplikasi Bank Sampah' }}</h2>
            <p>{{ $config->alamat }}</p>
            <p>Telp: {{ $config->no_telp }} | Email: {{ $config->email }}</p>
        </div>
        <div style="clear:both;"></div>
    </div>

    {{-- Footer --}}
    <div id="footer">
        Laporan Penarikan Saldo Nasabah - Halaman <span class="page-number"></span>
    </div>

    {{-- Title --}}
    <div class="title-section">
        <h1>Laporan Penarikan Saldo Nasabah</h1>
        <p>Periode: 
            @if($startDate)
                {{ Carbon\Carbon::parse($startDate)->isoFormat('D MMMM Y') }}
            @else
                Semua Waktu
            @endif
            - 
            {{ Carbon\Carbon::parse($endDate)->isoFormat('D MMMM Y') }}
        </p>
    </div>

    {{-- Summary Cards (Kolom) --}}
    <table class="summary-container">
        <tr>
            <td width="23%">
                <div class="summary-card">
                    <div class="label">Total Penarikan</div>
                    <div class="value">{{ number_format($summary->total_penarikan, 0, ',', '.') }}</div>
                    <div class="sub-value">Transaksi</div>
                </div>
            </td>
            <td width="2%">&nbsp;</td>
            <td width="23%">
                <div class="summary-card">
                    <div class="label">Total Nilai</div>
                    <div class="value">Rp {{ number_format($summary->total_nilai, 0, ',', '.') }}</div>
                    <div class="sub-value">Nominal Penarikan</div>
                </div>
            </td>
            <td width="2%">&nbsp;</td>
            <td width="23%">
                <div class="summary-card">
                    <div class="label">Status Selesai</div>
                    <div class="value">{{ number_format($summary->status_selesai, 0, ',', '.') }}</div>
                    <div class="sub-value">Transaksi Disetujui</div>
                </div>
            </td>
            <td width="2%">&nbsp;</td>
            <td width="23%">
                <div class="summary-card">
                    <div class="label">Status Pending</div>
                    <div class="value">{{ number_format($summary->status_pending, 0, ',', '.') }}</div>
                    <div class="sub-value">Menunggu Konfirmasi</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Tabel Gudang --}}
    <div class="section-title">Rekapitulasi Berdasarkan Gudang</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="45%">Alamat Gudang</th>
                <th width="15%" class="text-center">Total Penarikan</th>
                <th width="15%" class="text-right">Total Nilai</th>
                <th width="10%" class="text-center">Selesai</th>
                <th width="10%" class="text-center">Pending</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($gudangReport as $index => $gudang)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $gudang['alamat'] }}</td>
                    <td class="text-center font-bold">{{ number_format($gudang['total_penarikan'], 0, ',', '.') }}</td>
                    <td class="text-right font-bold">Rp {{ number_format($gudang['total_nilai'], 0, ',', '.') }}</td>
                    <td class="text-center"><span class="badge-selesai">{{ number_format($gudang['status_selesai'], 0, ',', '.') }}</span></td>
                    <td class="text-center"><span class="badge-pending">{{ number_format($gudang['status_pending'], 0, ',', '.') }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data penarikan untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="page-break-after: always;"></div>

    {{-- Top 5 Nasabah --}}
    <div class="section-title">Top 5 Nasabah Teraktif</div>
    
    <table class="grid-table">
        <tr>
            <!-- Kolom 1: Berdasarkan Jumlah Penarikan -->
            <td class="grid-col">
                <div style="font-size: 12px; font-weight: bold; color: #2d4a3e; margin-bottom: 8px; border-bottom: 1px solid #e1e8e4; padding-bottom: 4px;">
                    Berdasarkan Jumlah Penarikan
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th width="10%" class="text-center">No</th>
                            <th width="55%">Nama Nasabah</th>
                            <th width="35%" class="text-center">Jml Transaksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topNasabahByCount as $index => $nasabah)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $nasabah->nama }}</td>
                                <td class="text-center font-bold">{{ number_format($nasabah->total_penarikan) }}x</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </td>

            <!-- Spacer -->
            <td class="grid-spacer">&nbsp;</td>

            <!-- Kolom 2: Berdasarkan Total Nilai Penarikan -->
            <td class="grid-col">
                <div style="font-size: 12px; font-weight: bold; color: #2d4a3e; margin-bottom: 8px; border-bottom: 1px solid #e1e8e4; padding-bottom: 4px;">
                    Berdasarkan Total Nilai Penarikan
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th width="10%" class="text-center">No</th>
                            <th width="50%">Nama Nasabah</th>
                            <th width="40%" class="text-right">Total Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topNasabahByAmount as $index => $nasabah)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $nasabah->nama }}</td>
                                <td class="text-right font-bold">Rp {{ number_format($nasabah->total_nilai, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>
