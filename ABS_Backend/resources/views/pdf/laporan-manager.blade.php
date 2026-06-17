<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Riwayat Sampah</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap">
    <style>
        @page {
            margin: 40px 40px 40px 40px;
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #2D3748;
            font-size: 12px;
            line-height: 1.4;
            background-color: #ffffff;
        }

        /* Utility Classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .w-full { width: 100%; }
        
        /* Header styling */
        .header-container {
            width: 100%;
            margin-bottom: 15px;
        }
        
        .header-title {
            font-size: 24px;
            font-weight: 800;
            color: #3D5A35;
            margin: 0 0 10px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .periode-box {
            background-color: #F8FAF6;
            border-radius: 8px;
            padding: 8px 15px;
            display: inline-block;
            margin-top: 5px;
        }
        
        .periode-title {
            font-size: 11px;
            font-weight: bold;
            color: #718096;
            margin: 0;
        }
        
        .periode-value {
            font-size: 13px;
            font-weight: 800;
            color: #4A5568;
            margin: 2px 0 0 0;
        }
        
        .logo-container {
            float: right;
            background-color: #4A7043;
            border-radius: 12px;
            padding: 10px;
            width: 50px;
            height: 50px;
            text-align: center;
        }
        
        .logo-container img {
            width: 45px;
            height: 45px;
        }
        
        .separator-line {
            border: none;
            border-top: 2px solid #4A7043;
            margin: 15px 0 20px 0;
            clear: both;
        }

        /* Top Cards */
        .card-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px 0;
            margin: 0 -12px;
        }

        .stat-card {
            background-color: #F8FAF6;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 12px 15px;
            text-align: left;
        }

        .stat-card-label {
            font-size: 10px;
            font-weight: bold;
            color: #718096;
            text-transform: capitalize;
            margin-bottom: 4px;
        }

        .stat-card-value {
            font-size: 22px;
            font-weight: 800;
            color: #3D5A35;
        }

        .stat-card-unit {
            font-size: 12px;
            color: #718096;
            font-weight: bold;
        }

        .color-danger {
            color: #B45309;
        }

        /* Section Headings */
        .section-header {
            margin: 25px 0 12px 0;
            font-size: 12px;
            font-weight: 800;
            color: #2D3748;
            text-transform: uppercase;
            border-left: 3px solid #4A7043;
            padding-left: 8px;
            letter-spacing: 0.5px;
        }

        /* Rasio Keberhasilan */
        .ratio-container {
            background-color: #ffffff;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .progress-bar-container {
            width: 100%;
            height: 18px;
            background-color: #E2E8F0;
            border-radius: 9px;
            overflow: hidden;
        }

        .progress-bar-segment {
            height: 18px;
            float: left;
        }

        .progress-bar-success {
            background-color: #4A7043;
        }

        .progress-bar-failed {
            background-color: #A76A42;
        }

        .ratio-legend {
            margin-top: 10px;
            font-size: 11px;
            color: #4A5568;
        }

        .legend-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 4px;
            vertical-align: middle;
        }

        .dot-success { background-color: #4A7043; }
        .dot-failed { background-color: #A76A42; }

        .legend-item {
            display: inline-block;
            margin-right: 15px;
        }

        /* Details layout: 2 Columns */
        .columns-table {
            width: 100%;
            border-collapse: collapse;
        }

        .column-td {
            width: 50%;
            vertical-align: top;
        }

        /* Source Distribution Cards */
        .source-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px 0;
            margin: 0 -12px;
        }
        
        .source-card {
            background-color: #F8FAF6;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 15px;
        }

        .source-card-title {
            font-size: 11px;
            font-weight: bold;
            color: #4A5568;
            margin-bottom: 5px;
        }

        .source-card-count {
            font-size: 18px;
            font-weight: 800;
            color: #3D5A35;
        }

        .source-card-sub {
            font-size: 10px;
            color: #718096;
            margin-top: 2px;
        }

        /* Progress list (Trash Category) */
        .progress-list-item {
            margin-bottom: 8px;
            position: relative;
        }

        .progress-list-label {
            width: 70px;
            display: inline-block;
            font-weight: bold;
            color: #4A5568;
            vertical-align: middle;
            font-size: 11px;
        }

        .progress-list-track {
            display: inline-block;
            width: 250px;
            height: 18px;
            background-color: #F7FAFC;
            border-radius: 9px;
            vertical-align: middle;
            position: relative;
            overflow: hidden;
            border: 1px solid #EDF2F7;
        }

        .progress-list-fill {
            height: 18px;
            background-color: #4A7043;
            border-radius: 9px;
            text-align: right;
        }

        .progress-list-val-inside {
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            padding-right: 8px;
            line-height: 18px;
        }

        .progress-list-percentage {
            display: inline-block;
            width: 45px;
            text-align: right;
            font-size: 11px;
            color: #718096;
            vertical-align: middle;
            font-weight: bold;
        }

        /* Top Nasabah Cards */
        .nasabah-card {
            background-color: #F8FAF6;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 8px 12px;
            margin-bottom: 8px;
            width: 90%;
        }

        .nasabah-rank-circle {
            background-color: #4A7043;
            color: #ffffff;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-block;
            text-align: center;
            line-height: 20px;
            font-weight: bold;
            font-size: 11px;
            margin-right: 8px;
            vertical-align: middle;
        }

        .nasabah-info {
            display: inline-block;
            vertical-align: middle;
        }

        .nasabah-name {
            font-weight: bold;
            color: #2D3748;
            font-size: 11px;
        }

        .nasabah-stats {
            color: #718096;
            font-size: 10px;
            margin-top: 1px;
        }

        /* Standard Table Styling */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .report-table th {
            background-color: #F8FAF6;
            border: 1px solid #E2E8F0;
            color: #718096;
            font-weight: bold;
            padding: 8px 10px;
            font-size: 10px;
            text-transform: uppercase;
            text-align: left;
        }

        .report-table td {
            border: 1px solid #E2E8F0;
            padding: 8px 10px;
            font-size: 11px;
        }

        .report-table tr:nth-child(even) {
            background-color: #FCFDFB;
        }

        .badge-success {
            background-color: #E6F4EA;
            color: #137333;
            border-radius: 12px;
            padding: 2px 8px;
            font-weight: bold;
            font-size: 10px;
            display: inline-block;
        }

        .badge-failed {
            background-color: #FCE8E6;
            color: #C5221F;
            border-radius: 12px;
            padding: 2px 8px;
            font-weight: bold;
            font-size: 10px;
            display: inline-block;
        }

        .badge-neutral {
            background-color: #F1F3F4;
            color: #5F6368;
            border-radius: 12px;
            padding: 2px 8px;
            font-weight: bold;
            font-size: 10px;
            display: inline-block;
        }

        /* Pengepul Summary Box */
        .profit-table-footer-box {
            background-color: #EAF2E8;
            border-radius: 8px;
            padding: 12px 15px;
            margin-top: 10px;
            font-size: 11px;
            font-weight: bold;
            color: #3D5A35;
        }

        /* Note block (Catatan) */
        .note-container {
            background-color: #FDF8F5;
            border: 1px solid #FADBD8;
            border-radius: 12px;
            padding: 15px;
            margin-top: 25px;
        }

        .note-header {
            font-size: 12px;
            font-weight: bold;
            color: #C0392B;
            margin-bottom: 5px;
        }

        .note-header-icon {
            background-color: #C0392B;
            color: #ffffff;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: inline-block;
            text-align: center;
            line-height: 16px;
            font-size: 10px;
            font-weight: bold;
            margin-right: 5px;
        }

        .note-body {
            font-size: 11px;
            color: #7F8C8D;
            margin-left: 21px;
        }

        /* Footer */
        .footer-separator {
            border: none;
            border-top: 1px solid #4A7043;
            margin: 40px 0 15px 0;
            clear: both;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-logo-td {
            width: 60px;
            vertical-align: top;
        }

        .footer-logo-box {
            background-color: #4A7043;
            border-radius: 8px;
            padding: 6px;
            width: 38px;
            height: 38px;
            text-align: center;
        }

        .footer-logo-box img {
            width: 34px;
            height: 34px;
        }

        .footer-info-td {
            vertical-align: top;
            padding-left: 10px;
            font-size: 10px;
            color: #718096;
            line-height: 1.5;
        }

        .footer-copyright-td {
            vertical-align: bottom;
            text-align: right;
            font-size: 9px;
            color: #A0AEC0;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    {{-- Header Section --}}
    <div class="header-container">
        @php
            $logoPath = ($config->logo && file_exists(storage_path('app/public/' . $config->logo)) && (pathinfo($config->logo, PATHINFO_EXTENSION) !== 'webp' || function_exists('imagecreatefromwebp'))) 
                        ? storage_path('app/public/' . $config->logo) 
                        : public_path('logo.png');
        @endphp
        <div class="logo-container">
            <img src="{{ $logoPath }}">
        </div>
        
        <div>
            <h1 class="header-title">Laporan Riwayat Sampah</h1>
            <div class="periode-box">
                <p class="periode-title">Periode: {{ $periodeText }}</p>
                <p class="periode-value">{{ $gudangAlamat ?? 'Semua Gudang' }}</p>
            </div>
        </div>
    </div>
    
    <div class="separator-line"></div>

    {{-- Top Cards Summary --}}
    <table class="card-table">
        <tr>
            <td width="25%">
                <div class="stat-card">
                    <div class="stat-card-label">Total Transaksi</div>
                    <div class="stat-card-value">{{ $totalTransaksi }}</div>
                </div>
            </td>
            <td width="25%">
                <div class="stat-card">
                    <div class="stat-card-label">Total Berat</div>
                    <div class="stat-card-value">
                        {{ number_format($totalBerat, 1, ',', '.') }}<span class="stat-card-unit">kg</span>
                    </div>
                </div>
            </td>
            <td width="25%">
                <div class="stat-card">
                    <div class="stat-card-label">Selesai</div>
                    <div class="stat-card-value">{{ $selesaiCount }}</div>
                </div>
            </td>
            <td width="25%">
                <div class="stat-card">
                    <div class="stat-card-label">Tidak Terlaksana</div>
                    <div class="stat-card-value color-danger">{{ $tidakTerlaksanaCount }}</div>
                </div>
            </td>
        </tr>
    </table>

    @if($gudangAlamat)
        {{-- Version 1: Per Gudang --}}
        
        {{-- Rasio Keberhasilan --}}
        <div class="section-header">Rasio Keberhasilan Transaksi</div>
        <div class="ratio-container">
            @php
                $selesaiPercent = $totalTransaksi > 0 ? round(($selesaiCount / $totalTransaksi) * 100) : 0;
                $failedPercent = $totalTransaksi > 0 ? 100 - $selesaiPercent : 0;
            @endphp
            <div class="progress-bar-container">
                @if($selesaiPercent > 0)
                    <div class="progress-bar-segment progress-bar-success" style="width: {{ $selesaiPercent }}%;"></div>
                @endif
                @if($failedPercent > 0)
                    <div class="progress-bar-segment progress-bar-failed" style="width: {{ $failedPercent }}%;"></div>
                @endif
            </div>
            <div class="ratio-legend">
                <div class="legend-item">
                    <span class="legend-dot dot-success"></span>
                    Selesai &mdash; {{ $selesaiCount }} transaksi
                </div>
                <div class="legend-item">
                    <span class="legend-dot dot-failed"></span>
                    Tidak Terlaksana &mdash; {{ $tidakTerlaksanaCount }} transaksi
                </div>
            </div>
        </div>

        {{-- Distribusi Sumber Transaksi --}}
        <div class="section-header">Distribusi Sumber Transaksi</div>
        <table class="source-table" style="margin-bottom: 20px;">
            <tr>
                <td width="50%">
                    <div class="source-card">
                        <div class="source-card-title">Request Jemput</div>
                        <div class="source-card-count">{{ $jemputCount }} <span style="font-size: 11px; font-weight: normal; color: #718096;">transaksi</span></div>
                        <div class="source-card-sub">{{ number_format($jemputWeight, 1, ',', '.') }} kg total</div>
                    </div>
                </td>
                <td width="50%">
                    <div class="source-card">
                        <div class="source-card-title">Setor Manual</div>
                        <div class="source-card-count">{{ $setorManualCount }} <span style="font-size: 11px; font-weight: normal; color: #718096;">transaksi</span></div>
                        <div class="source-card-sub">{{ number_format($setorManualWeight, 1, ',', '.') }} kg total</div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Distribusi Jenis Sampah & Top Nasabah side-by-side --}}
        <table class="columns-table">
            <tr>
                {{-- Left Column: Distribusi Jenis Sampah --}}
                <td class="column-td" style="padding-right: 15px;">
                    <div class="section-header">Distribusi Jenis Sampah</div>
                    <div style="margin-top: 10px;">
                        <table style="width: 100%; border-collapse: collapse; margin-top: 5px;">
                            @foreach($jenisSampahList as $item)
                            <tr>
                                <td style="width: 75px; font-weight: bold; color: #4A5568; font-size: 11px; padding: 6px 0; vertical-align: middle;">
                                    {{ $item['nama'] }}
                                </td>
                                <td style="padding: 6px 0; vertical-align: middle;">
                                    <div style="width: 100%; height: 16px; background-color: #F8FAF6; border: 1px solid #E2E8F0; border-radius: 8px; overflow: hidden; position: relative;">
                                        @php
                                            $widthVal = max($item['persentase'], 18);
                                        @endphp
                                        <div style="width: {{ $item['persentase'] }}%; min-width: 45px; height: 16px; background-color: #4A7043; border-radius: 8px; overflow: hidden;">
                                            <table style="width: 100%; height: 16px; border-collapse: collapse; margin: 0; padding: 0; border: none;">
                                                <tr style="border: none; background: transparent;">
                                                    <td style="text-align: right; vertical-align: middle; color: #ffffff; font-size: 9px; font-weight: bold; padding-right: 6px; line-height: 1; border: none; background: transparent; white-space: nowrap;">
                                                        {{ number_format($item['berat'], 1, ',', '.') }} kg
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                                <td style="width: 45px; text-align: right; font-weight: bold; font-size: 11px; color: #718096; padding: 6px 0; vertical-align: middle;">
                                    {{ number_format($item['persentase'], 1, ',', '.') }}%
                                </td>
                            </tr>
                            @endforeach
                        </table>
                    </div>
                </td>
                
                {{-- Right Column: Top Nasabah --}}
                <td class="column-td" style="padding-left: 15px;">
                    <div class="section-header">Top Nasabah</div>
                    <div style="margin-top: 10px;">
                        @foreach($topNasabah as $index => $n)
                            <table style="width: 100%; border-collapse: collapse; background-color: #F8FAF6; border: 1px solid #E2E8F0; border-radius: 12px; margin-bottom: 8px;">
                                <tr>
                                    <td style="width: 35px; padding: 10px; vertical-align: middle; text-align: center;">
                                        <div style="background-color: #4A7043; color: #ffffff; width: 22px; height: 22px; border-radius: 11px; text-align: center; line-height: 22px; font-weight: bold; font-size: 11px; margin: 0 auto;">
                                            {{ $index + 1 }}
                                        </div>
                                    </td>
                                    <td style="padding: 10px 10px 10px 0; vertical-align: middle; text-align: left;">
                                        <div style="font-weight: bold; color: #2D3748; font-size: 11px; line-height: 1.2;">{{ $n['nama'] }}</div>
                                        <div style="color: #718096; font-size: 9px; margin-top: 3px; line-height: 1;">{{ $n['transaksi'] }} transaksi &bull; {{ number_format($n['berat'], 1, ',', '.') }} kg</div>
                                    </td>
                                </tr>
                            </table>
                        @endforeach
                    </div>
                </td>
            </tr>
        </table>

        {{-- Ringkasan Per Petugas --}}
        <div class="section-header" style="margin-top: 25px;">Ringkasan Per Petugas</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Petugas</th>
                    <th>Total Transaksi</th>
                    <th>Selesai</th>
                    <th>Tidak Terlaksana</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ringkasanPetugas as $p)
                    <tr>
                        <td class="font-bold">{{ $p['nama'] }}</td>
                        <td>{{ $p['total'] }}</td>
                        <td><span class="badge-success">{{ $p['selesai'] }}</span></td>
                        <td><span class="badge-failed">{{ $p['tidak_terlaksana'] }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Penjualan ke Pengepul --}}
        <div class="section-header" style="margin-top: 25px;">Penjualan Ke Pengepul</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Pengepul</th>
                    <th>Jenis Sampah</th>
                    <th>Total Berat</th>
                    <th>Diterima</th>
                    <th>Keuntungan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($penjualanPengepulList as $penj)
                    <tr>
                        <td>
                            <span class="font-bold">{{ $penj['pengepul'] }}</span><br>
                            <span style="font-size: 9px; color: #718096;">{{ $penj['tanggal'] }}</span>
                        </td>
                        <td>{{ $penj['jenis_sampah'] }}</td>
                        <td>{{ number_format($penj['total_berat'], 1, ',', '.') }} kg</td>
                        <td>Rp {{ number_format($penj['diterima'], 0, ',', '.') }}</td>
                        <td class="font-bold" style="color: #4A7043;">Rp {{ number_format($penj['keuntungan'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr style="background-color: #F8FAF6; font-weight: bold;">
                    <td colspan="2">Total</td>
                    <td>{{ number_format($totalPengepulBerat, 1, ',', '.') }} kg</td>
                    <td>Rp {{ number_format($totalPengepulDiterima, 0, ',', '.') }}</td>
                    <td style="color: #4A7043;">Rp {{ number_format($totalPengepulKeuntungan, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
        
        <div class="profit-table-footer-box">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="50%">Total dibayar ke nasabah: <span style="font-weight: 800;">Rp {{ number_format($totalDibayarNasabah, 0, ',', '.') }}</span></td>
                    <td width="50%" align="right">Keuntungan bank sampah: <span style="font-weight: 800;">Rp {{ number_format($totalPengepulKeuntungan, 0, ',', '.') }}</span> (margin {{ number_format($marginNasabah, 1, ',', '.') }}%)</td>
                </tr>
            </table>
        </div>

    @else
        {{-- Version 2: Semua Gudang --}}
        
        {{-- Distribusi Sumber Transaksi --}}
        <div class="section-header">Distribusi Sumber Transaksi</div>
        <table class="source-table" style="margin-bottom: 20px;">
            <tr>
                <td width="50%">
                    <div class="source-card">
                        <div class="source-card-title">Request Jemput</div>
                        <div class="source-card-count">{{ $jemputCount }} <span style="font-size: 11px; font-weight: normal; color: #718096;">transaksi</span></div>
                        <div class="source-card-sub">{{ number_format($jemputWeight, 1, ',', '.') }} kg total</div>
                    </div>
                </td>
                <td width="50%">
                    <div class="source-card">
                        <div class="source-card-title">Setor Manual</div>
                        <div class="source-card-count">{{ $setorManualCount }} <span style="font-size: 11px; font-weight: normal; color: #718096;">transaksi</span></div>
                        <div class="source-card-sub">{{ number_format($setorManualWeight, 1, ',', '.') }} kg total</div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Data Per Gudang --}}
        <div class="section-header">Data Per Gudang</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Gudang</th>
                    <th>Transaksi</th>
                    <th>Total Berat</th>
                    <th>Selesai</th>
                    <th>Tidak Terlaksana</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dataPerGudang as $g)
                    <tr>
                        <td class="font-bold">{{ $g['nama'] }}</td>
                        <td>{{ $g['transaksi'] }}</td>
                        <td>{{ number_format($g['berat'], 1, ',', '.') }} kg</td>
                        <td><span class="badge-success">{{ $g['selesai'] }}</span></td>
                        <td><span class="badge-neutral">{{ $g['tidak_terlaksana'] }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Distribusi Jenis Sampah (Full Width in Semua Gudang version) --}}
        <div class="section-header">Distribusi Jenis Sampah</div>
        <div style="margin-top: 10px; background-color: #ffffff; border: 1px solid #E2E8F0; border-radius: 12px; padding: 15px;">
            <table style="width: 100%; border-collapse: collapse;">
                @foreach($jenisSampahList as $item)
                <tr>
                    <td style="width: 100px; font-weight: bold; color: #4A5568; font-size: 11px; padding: 8px 0; vertical-align: middle;">
                        {{ $item['nama'] }}
                    </td>
                    <td style="padding: 8px 0; vertical-align: middle;">
                        <div style="width: 100%; height: 16px; background-color: #F8FAF6; border: 1px solid #E2E8F0; border-radius: 8px; overflow: hidden; position: relative;">
                            <div style="width: {{ $item['persentase'] }}%; min-width: 45px; height: 16px; background-color: #4A7043; border-radius: 8px; overflow: hidden;">
                                <table style="width: 100%; height: 16px; border-collapse: collapse; margin: 0; padding: 0; border: none;">
                                    <tr style="border: none; background: transparent;">
                                        <td style="text-align: right; vertical-align: middle; color: #ffffff; font-size: 9px; font-weight: bold; padding-right: 8px; line-height: 1; border: none; background: transparent; white-space: nowrap;">
                                            {{ number_format($item['berat'], 1, ',', '.') }} kg
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </td>
                    <td style="width: 50px; text-align: right; font-weight: bold; font-size: 11px; color: #718096; padding: 8px 0; vertical-align: middle;">
                        {{ number_format($item['persentase'], 1, ',', '.') }}%
                    </td>
                </tr>
                @endforeach
            </table>
        </div>

        {{-- Ringkasan Penjualan Ke Pengepul --}}
        <div class="section-header" style="margin-top: 25px;">Ringkasan Penjualan Ke Pengepul</div>
        <table class="card-table">
            <tr>
                <td width="25%">
                    <div class="stat-card">
                        <div class="stat-card-label">Jumlah Pengepul</div>
                        <div class="stat-card-value" style="font-size: 18px;">
                            {{ $penjualanPengepulList->pluck('pengepul')->unique()->count() }} <span style="font-size: 11px; font-weight: normal; color: #718096;">pengepul</span>
                        </div>
                    </div>
                </td>
                <td width="25%">
                    <div class="stat-card">
                        <div class="stat-card-label">Total Berat Terjual</div>
                        <div class="stat-card-value" style="font-size: 18px;">
                            {{ number_format($totalPengepulBerat, 1, ',', '.') }} <span style="font-size: 11px; font-weight: normal; color: #718096;">kg</span>
                        </div>
                    </div>
                </td>
                <td width="25%">
                    <div class="stat-card">
                        <div class="stat-card-label">Dibayar ke Nasabah</div>
                        <div class="stat-card-value" style="font-size: 16px; color: #2D3748;">
                            Rp {{ number_format($totalDibayarNasabah, 0, ',', '.') }}
                        </div>
                    </div>
                </td>
                <td width="25%">
                    <div class="stat-card">
                        <div class="stat-card-label">Diterima dari Pengepul</div>
                        <div class="stat-card-value" style="font-size: 16px; color: #4A7043;">
                            Rp {{ number_format($totalPengepulDiterima, 0, ',', '.') }}
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        
        <div class="profit-table-footer-box" style="margin-top: 15px;">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td>Keuntungan bank sampah dari selisih harga:</td>
                    <td align="right" style="font-size: 14px; font-weight: 800;">
                        Rp {{ number_format($totalPengepulKeuntungan, 0, ',', '.') }} <span style="font-size: 10px; font-weight: normal; color: #718096;">(margin {{ number_format($marginNasabah, 1, ',', '.') }}%)</span>
                    </td>
                </tr>
            </table>
        </div>

    @endif

    {{-- Footer Section --}}
    <hr class="footer-separator">
    <table class="footer-table">
        <tr>
            <td class="footer-logo-td">
                <div class="footer-logo-box">
                    <img src="{{ $logoPath }}">
                </div>
            </td>
            <td class="footer-info-td">
                <strong>{{ $config->alamat ?? 'Jl. Surakarta No. 09, Surakarta, Jawa Tengah' }}</strong><br>
                Tel: {{ $config->no_telp ?? '08589821247' }}<br>
                Email: {{ $config->email ?? 'abs@gmail.com' }}
            </td>
            <td class="footer-copyright-td">
                &copy; 2026 Aplikasi Bank Sampah<br>All Rights Reserved
            </td>
        </tr>
    </table>

</body>
</html>
