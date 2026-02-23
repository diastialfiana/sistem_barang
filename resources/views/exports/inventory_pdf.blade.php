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
                <th>Cabang</th>
                <th>Satuan</th>
                <th style="text-align: center;">Stock (Awal)</th>
                <th style="text-align: center;">Keluar</th>
                <th style="text-align: center;">Sisa (READY)</th>
                <th style="text-align: center;">Request (PENDING)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                @php
                    $activeBranches = $branches->filter(function($b) use ($item) {
                        return isset($item->branch_breakdown[$b->id]) || isset($item->branch_pending[$b->id]);
                    });
                    
                    if (request('branch_id')) {
                        $activeBranches = $activeBranches->where('id', request('branch_id'));
                    }
                    $rowspan = $activeBranches->count() + 1;
                @endphp
                <tr>
                    <td rowspan="{{ $rowspan }}" style="vertical-align: top;">{{ $loop->iteration }}</td>
                    <td rowspan="{{ $rowspan }}" style="vertical-align: top;">
                        <strong>{{ $item->name }}</strong><br>
                        <small style="color: #666;">{{ $item->category }}</small>
                    </td>
                    <td style="background-color: #f9f9f9;">Gudang Pusat</td>
                    <td rowspan="{{ $rowspan }}" style="vertical-align: top;">{{ $item->unit }}</td>
                    <td style="text-align: center;">{{ $item->stock + ($item->total_keluar ?? 0) }}</td>
                    <td style="text-align: center;">{{ $item->total_keluar ?? 0 }}</td>
                    <td style="text-align: center;"><strong>{{ $item->stock }}</strong></td>
                    <td style="text-align: center; color: #ccc;">-</td>
                </tr>
                @foreach($activeBranches as $branch)
                <tr>
                    <td>{{ $branch->name }}</td>
                    <td style="text-align: center; color: #ccc;">-</td>
                    <td style="text-align: center; color: #ccc;">-</td>
                    <td style="text-align: center;"><strong>{{ $item->branch_breakdown[$branch->id] ?? 0 }}</strong></td>
                    <td style="text-align: center; color: #008000;">{{ $item->branch_pending[$branch->id] ?? 0 }}</td>
                </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

</body>
</html>
