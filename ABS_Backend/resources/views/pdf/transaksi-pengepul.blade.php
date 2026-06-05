<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice Penjualan / Tanda Terima</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap">
    <style>
        body { font-family: 'Plus Jakarta Sans', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .text-gray { color: #666; }
        .text-green { color: #4A7043; }
        .uppercase { text-transform: uppercase; }
        .header-line { border-bottom: 3px solid #000; margin-top: 15px; margin-bottom: 20px; }
        .info-table { width: 100%; margin-bottom: 30px; }
        .info-table td { vertical-align: top; }
        .label { font-size: 10px; color: #888; text-transform: uppercase; margin-bottom: 4px; }
        .value { font-size: 12px; font-weight: bold; color: #000; margin-bottom: 12px; }
        
        .status-badge {
            color: #fff;
            padding: 4px 10px;
            font-size: 11px;
            border-radius: 4px;
            display: inline-block;
        }

        .bg-green { background-color: #4A7043; }
        .bg-red { background-color: #dc2626; }
        .bg-amber { background-color: #d97706; }
        .bg-blue { background-color: #2563eb; }

        .product-table { width: 100%; border-collapse: collapse; margin-bottom: 40px; }
        .product-table th, .product-table td { border: 1px solid #000; padding: 10px; }
        .product-table th { background-color: #f9f9f9; text-align: left; font-size: 11px; text-transform: uppercase; }
        .product-table td { vertical-align: middle; }
        
        .product-name { font-weight: bold; color: #000; margin: 0 0 4px 0; }
        .product-detail { font-size: 11px; color: #666; margin: 0; }
        .product-total { font-weight: bold; color: #000; text-align: right; }

        .total-section { text-align: right; margin-bottom: 40px; }
        .total-line { border-bottom: 3px solid #000; margin-bottom: 10px; }
        .total-label { font-size: 10px; color: #888; text-transform: uppercase; margin-bottom: 4px; }
        .total-value { font-size: 14px; font-weight: bold; color: #4A7043; }
        
        .dashed-line { border-bottom: 1px dashed #000; margin-bottom: 20px; }
        .footer-message { text-align: center; font-weight: bold; font-size: 12px; margin-bottom: 40px; }
        
        .footer-bottom { width: 100%; font-size: 10px; color: #888; }
        .footer-bottom td { vertical-align: bottom; }
        
        .scissors-icon { font-size: 16px; color: #aaa; text-align: center; }
    </style>
</head>
<body>

    <div class="text-center">
        <div class="text-green font-bold" style="font-size: 14px;">Aplikasi Bank Sampah</div>
        <div class="text-gray uppercase" style="font-size: 12px; margin-top: 4px; letter-spacing: 1px;">INVOICE PENJUALAN / TANDA TERIMA</div>
    </div>
    
    <div class="header-line"></div>

    <table class="info-table">
        <tr>
            <td width="50%">
                <div class="label">NAMA PENGEPUL</div>
                <div class="value">
                    {{ $transaksi->pengepul->nama ?? '-' }} 
                    {{ !empty($transaksi->pengepul->nama_lembaga) ? ' - ' . $transaksi->pengepul->nama_lembaga : '' }}
                </div>
                
                <div class="label" style="margin-top: 10px;">STATUS</div>
                <div style="margin-bottom: 12px;">
                    @php
                        $statusClass = 'bg-green';
                        if ($transaksi->status == 'tolak' || $transaksi->status == 'batal') {
                            $statusClass = 'bg-red';
                        } elseif ($transaksi->status == 'pending') {
                            $statusClass = 'bg-amber';
                        } elseif ($transaksi->status == 'proses') {
                            $statusClass = 'bg-blue';
                        }
                    @endphp
                    <span class="status-badge {{ $statusClass }}">
                        {{ ucfirst(str_replace('_', ' ', $transaksi->status)) }}
                    </span>
                </div>
            </td>
            <td width="50%" class="text-right">
                <div class="label">ID PESANAN</div>
                <div class="value">#{{ $transaksi->transaksi_id }}</div>
                
                <div class="label" style="margin-top: 10px;">TANGGAL TRANSAKSI</div>
                <div class="value" style="font-weight: normal;">{{ \Carbon\Carbon::parse($transaksi->created_at)->translatedFormat('d F Y, H:i:s') }}</div>
            </td>
        </tr>
    </table>

    <table class="product-table">
        <thead>
            <tr>
                <th colspan="2">RINCIAN PRODUK</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach ($transaksi->detailTransaksi as $d)
            @php 
                $subtotal = $d->berat * $d->harga;
                $total += $subtotal;
            @endphp
            <tr>
                <td>
                    <p class="product-name">{{ $d->sampah->itemSampah->nama ?? '-' }} (Gudang {{ $d->sampah->gudang_id ?? '-' }})</p>
                    <p class="product-detail">{{ number_format($d->berat, 0, ',', '.') }} kg &times; Rp {{ number_format($d->harga, 0, ',', '.') }}</p>
                </td>
                <td class="product-total">
                    Rp {{ number_format($subtotal, 0, ',', '.') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-line"></div>
    <div class="total-section">
        <div class="total-label">TOTAL PEMBAYARAN</div>
        <div class="total-value">Rp {{ number_format($total, 0, ',', '.') }}</div>
    </div>

    <div class="dashed-line"></div>
    <div class="footer-message">
        Terima kasih telah bermitra dengan Aplikasi Bank Sampah
    </div>

    <table class="footer-bottom">
        <tr>
            <td width="50%" class="uppercase">
                DICETAK OLEH SISTEM ABS
            </td>
            <td width="50%" class="text-right">
                Dicetak pada:<br>
                {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i:s') }}
            </td>
        </tr>
    </table>

    <table style="width: 100%; margin-top: 30px;">
        <tr>
            <td style="width: 48%; border-bottom: 1px solid #ddd;"></td>
            <td style="width: 4%; text-align: center;" class="scissors-icon">&#9986;</td>
            <td style="width: 48%; border-bottom: 1px solid #ddd;"></td>
        </tr>
    </table>

</body>
</html>
