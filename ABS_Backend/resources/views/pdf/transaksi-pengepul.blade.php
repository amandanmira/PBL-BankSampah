<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Detail Transaksi</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; }
    </style>
</head>
<body>

<h2>Detail Transaksi</h2>

<p>ID: {{ $transaksi->transaksi_id ?? '-' }}</p>
<p>Status: {{ $transaksi->status ?? '-' }}</p>
<p>Deadline: {{ $transaksi->deadline ?? '-' }}</p>
<p>Tanggal: {{ $transaksi->created_at ?? '-' }}</p>

<table>
    <thead>
        <tr>
            <th>Nama Item</th>
            <th>Berat</th>
            <th>Harga</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($transaksi->detailTransaksi as $d)
        <tr>
            <td>{{ $d->sampah->itemSampah->nama ?? '-' }}</td>
            <td>{{ $d->berat ?? '-' }}</td>
            <td>{{ $d->harga ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
