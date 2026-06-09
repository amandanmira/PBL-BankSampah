<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Penarikan Saldo Nasabah</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap">
    <style>
        @page {
            margin: 130px 45px 60px 45px;
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #333333;
            font-size: 11px;
            line-height: 1.4;
        }

        #kop-surat {
            position: fixed;
            top: -110px;
            left: 0;
            right: 0;
            height: 80px;
            border-bottom: 2px solid #4A7043;
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
            color: #4A7043;
            font-weight: bold;
        }

        #kop-surat .info p {
            margin: 3px 0;
            font-size: 10px;
            color: #666666;
        }

        #footer {
            position: fixed;
            bottom: -40px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #888888;
            border-top: 1px solid #e1e8e4;
            padding-top: 6px;
        }

        .page-number:after {
            content: counter(page);
        }

        .title-section {
            text-align: center;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        .title-section h1 {
            margin: 0;
            font-size: 16px;
            color: #4A7043;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .title-section p {
            margin: 4px 0 0 0;
            font-size: 10px;
            color: #666666;
        }

        /* Section Header with Green Bar */
        .section-header {
            margin-top: 20px;
            margin-bottom: 12px;
        }
        .section-header .bar {
            display: inline-block;
            width: 4px;
            height: 14px;
            background-color: #4A7043;
            vertical-align: middle;
            margin-right: 6px;
            border-radius: 2px;
        }
        .section-header .text {
            font-size: 11px;
            font-weight: bold;
            color: #2D3748;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            vertical-align: middle;
        }

        /* Summary Cards Table */
        .card-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .card-td {
            width: 32%;
            vertical-align: top;
        }
        .card-spacer {
            width: 2%;
        }
        .card-box {
            background-color: #f8faf9;
            border: 1px solid #e1e8e4;
            border-radius: 12px;
            padding: 14px 12px;
            text-align: left;
        }
        .card-box-danger {
            background-color: #FFFDF5;
            border: 1px solid #FBE8C5;
            border-radius: 12px;
            padding: 14px 12px;
            text-align: left;
        }
        .card-label {
            font-size: 9px;
            font-weight: bold;
            color: #888888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .card-value {
            font-size: 20px;
            font-weight: bold;
            color: #2D3748;
            margin-bottom: 4px;
        }
        .card-value-green {
            font-size: 20px;
            font-weight: bold;
            color: #4A7043;
            margin-bottom: 4px;
        }
        .card-value-danger {
            font-size: 20px;
            font-weight: bold;
            color: #D97706;
            margin-bottom: 4px;
        }
        .card-desc {
            font-size: 9px;
            color: #a0a0a0;
        }

        /* Table and layout */
        .dist-table {
            width: 100%;
            border-spacing: 0;
            border: 1px solid #e1e8e4;
            border-radius: 12px;
            background-color: #ffffff;
            margin-bottom: 20px;
        }
        .dist-table th {
            padding: 10px 14px;
            font-size: 9px;
            font-weight: bold;
            color: #888888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: left;
            background-color: #fafbfa;
            border-bottom: 1px solid #e1e8e4;
        }
        .dist-table td {
            border-bottom: 1px solid #f0f3f1;
            padding: 10px 14px;
            font-size: 11px;
            color: #333333;
            vertical-align: middle;
        }
        .dist-table tr:last-child td {
            border-bottom: none;
        }
        .dist-table .total-row td {
            background-color: #fafbfa;
            font-weight: bold;
            border-top: 1px solid #e1e8e4;
        }

        /* Rounded corners for dist-table spacing */
        .dist-table tr:first-child th:first-child {
            border-top-left-radius: 12px;
        }
        .dist-table tr:first-child th:last-child {
            border-top-right-radius: 12px;
        }
        .dist-table tr:last-child td:first-child {
            border-bottom-left-radius: 12px;
        }
        .dist-table tr:last-child td:last-child {
            border-bottom-right-radius: 12px;
        }

        /* Badge Icon */
        .badge-icon {
            display: inline-block;
            width: 24px;
            height: 24px;
            line-height: 24px;
            text-align: center;
            background-color: #5D7A56;
            color: #ffffff;
            font-weight: bold;
            font-size: 10px;
            border-radius: 6px;
            margin-right: 8px;
            vertical-align: middle;
        }
        .bank-name {
            font-size: 11px;
            font-weight: bold;
            color: #333333;
            vertical-align: middle;
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
        .font-medium {
            font-weight: 500;
        }

        /* Rasio Section Container */
        .rasio-box {
            border: 1px solid #e1e8e4;
            border-radius: 12px;
            background-color: #ffffff;
            padding: 16px;
            margin-bottom: 20px;
        }

        /* Progress Bar */
        .progress-bar-table {
            width: 100%;
            height: 12px;
            border-collapse: collapse;
            border-radius: 6px;
            overflow: hidden;
            background-color: #e1e8e4;
            margin-bottom: 16px;
        }
        .progress-selesai {
            background-color: #4A7043;
            height: 12px;
            padding: 0;
        }
        .progress-tolak {
            background-color: #FFB703;
            height: 12px;
            padding: 0;
        }

        /* Legend Layout */
        .legend-table {
            width: 100%;
            border-collapse: collapse;
        }
        .legend-col {
            vertical-align: top;
        }
        .legend-item {
            margin-bottom: 4px;
        }
        .dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 6px;
            vertical-align: middle;
        }
        .dot-selesai {
            background-color: #4A7043;
        }
        .dot-tolak {
            background-color: #FFB703;
        }
        .legend-label {
            font-size: 9px;
            font-weight: bold;
            color: #888888;
            letter-spacing: 0.5px;
            vertical-align: middle;
        }
        .legend-percentage {
            font-size: 20px;
            font-weight: bold;
            color: #333333;
            margin-bottom: 2px;
        }
        .legend-desc {
            font-size: 9px;
            color: #888888;
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

    @php
        $status_selesai = $summary->status_selesai ?? 0;
        $status_tolak = $summary->status_tolak ?? 0;
        $status_pending = $summary->status_pending ?? 0;
        $total_final = $status_selesai + $status_tolak;
        
        $selesai_percentage = $total_final > 0 ? round(($status_selesai / $total_final) * 100) : 0;
        $tolak_percentage = $total_final > 0 ? 100 - $selesai_percentage : 0;
        
        $total_nilai_selesai = $summary->total_nilai_selesai ?? 0;
        $total_nilai_tolak = $summary->total_nilai_tolak ?? 0;
    @endphp

    {{-- 1. Status Final Transaksi --}}
    <div class="section-header">
        <span class="bar"></span><span class="text">Status Final Transaksi</span>
    </div>

    <table class="card-table">
        <tr>
            <td class="card-td">
                <div class="card-box">
                    <div class="card-label">Total Transaksi Final</div>
                    <div class="card-value">{{ number_format($total_final, 0, ',', '.') }}</div>
                    <div class="card-desc">transaksi tercatat</div>
                </div>
            </td>
            <td class="card-spacer">&nbsp;</td>
            <td class="card-td">
                <div class="card-box">
                    <div class="card-label">Total Nominal Selesai</div>
                    <div class="card-value-green">Rp {{ number_format($total_nilai_selesai, 0, ',', '.') }}</div>
                    <div class="card-desc">sudah dicairkan</div>
                </div>
            </td>
            <td class="card-spacer">&nbsp;</td>
            <td class="card-td">
                <div class="card-box-danger">
                    <div class="card-label">Total Ditolak</div>
                    <div class="card-value-danger">{{ number_format($status_tolak, 0, ',', '.') }}</div>
                    <div class="card-desc">transaksi gagal</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- 2. Distribusi Bank Tujuan --}}
    <div class="section-header">
        <span class="bar"></span><span class="text">Distribusi Bank Tujuan</span>
    </div>

    <table class="dist-table">
        <thead>
            <tr>
                <th width="50%">Nama Bank / E-Wallet</th>
                <th width="20%" class="text-center">Jml Transaksi</th>
                <th width="30%" class="text-right">Total Nominal</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total_dist_transaksi = 0;
                $total_dist_nominal = 0;
            @endphp
            @forelse ($bankDistribution as $bank)
                @php
                    $total_dist_transaksi += $bank->jumlah_transaksi;
                    $total_dist_nominal += $bank->total_nominal;
                    $words = explode(' ', trim($bank->nama_bank ?? ''));
                    if (count($words) >= 2) {
                        $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                    } else {
                        $initials = strtoupper(substr($words[0] ?? '??', 0, 2));
                    }
                @endphp
                <tr>
                    <td>
                        <span class="badge-icon">{{ $initials }}</span>
                        <span class="bank-name">{{ $bank->nama_bank }}</span>
                    </td>
                    <td class="text-center font-medium">{{ number_format($bank->jumlah_transaksi, 0, ',', '.') }}</td>
                    <td class="text-right font-bold">Rp {{ number_format($bank->total_nominal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center" style="padding: 20px; color: #888888;">Tidak ada data distribusi bank untuk periode ini.</td>
                </tr>
            @endforelse
            @if(count($bankDistribution) > 0)
                <tr class="total-row">
                    <td class="font-bold" style="padding-left: 14px;">Total</td>
                    <td class="text-center font-bold">{{ number_format($total_dist_transaksi, 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: #4A7043;">Rp {{ number_format($total_dist_nominal, 0, ',', '.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    {{-- 3. Rasio Keberhasilan Transfer --}}
    <div class="section-header">
        <span class="bar"></span><span class="text">Rasio Keberhasilan Transfer</span>
    </div>

    <div class="rasio-box">
        <table class="progress-bar-table">
            <tr>
                @if($total_final > 0)
                    @if($selesai_percentage > 0)
                        <td class="progress-selesai" style="width: {{ $selesai_percentage }}%;"></td>
                    @endif
                    @if($tolak_percentage > 0)
                        <td class="progress-tolak" style="width: {{ $tolak_percentage }}%;"></td>
                    @endif
                @else
                    <td style="background-color: #e1e8e4; height: 12px;"></td>
                @endif
            </tr>
        </table>

        <table class="legend-table">
            <tr>
                <td class="legend-col" width="50%">
                    <div class="legend-item">
                        <span class="dot dot-selesai"></span>
                        <span class="legend-label">SELESAI</span>
                    </div>
                    <div class="legend-percentage">{{ $selesai_percentage }}%</div>
                    <div class="legend-desc">{{ number_format($status_selesai, 0, ',', '.') }} transaksi - Rp {{ number_format($total_nilai_selesai, 0, ',', '.') }}</div>
                </td>
                <td class="legend-col" width="50%">
                    <div class="legend-item">
                        <span class="dot dot-tolak"></span>
                        <span class="legend-label">DITOLAK</span>
                    </div>
                    <div class="legend-percentage">{{ $tolak_percentage }}%</div>
                    <div class="legend-desc">{{ number_format($status_tolak, 0, ',', '.') }} transaksi - Rp {{ number_format($total_nilai_tolak, 0, ',', '.') }}</div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
