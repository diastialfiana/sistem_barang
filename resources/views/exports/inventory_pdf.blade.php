<!DOCTYPE html>
<html>
<head>
    <title>Laporan Inventory Barang</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 30px; }
        

    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Inventory Barang</h2>
        <p>Dicetak Tanggal: {{ date('d F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Satuan</th>
                <th style="text-align: center;">Stock (Awal)</th>
                <th style="text-align: center;">Keluar</th>
                <th style="text-align: center;">Sisa</th>
                <th style="text-align: center;">Request</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->unit }}</td>
                <td style="text-align: center;">{{ $item->stock + ($item->total_keluar ?? 0) }}</td>
                <td style="text-align: center;">{{ $item->total_keluar ?? 0 }}</td>
                <td style="text-align: center;">{{ $item->stock }}</td>
                <td style="text-align: center;">{{ $item->total_request ?? 0 }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
