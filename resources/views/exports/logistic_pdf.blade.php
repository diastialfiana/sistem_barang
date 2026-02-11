<!DOCTYPE html>
<html>
<head>
    <title>Laporan Logistik</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #333; padding: 5px; text-align: left; }
        th { background-color: #eee; }
        .header { text-align: center; margin-bottom: 20px; }
        .footer { margin-top: 40px; page-break-inside: avoid; }
        .signatures { width: 100%; margin-top: 30px; display: table; }
        .sig-box { display: table-cell; width: 25%; text-align: center; vertical-align: bottom; height: 100px; padding: 10px; }
        .sig-line { width: 80%; border-top: 1px solid #000; margin: 10px auto 0; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Permintaan Logistik</h2>
        <p>Periode: {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Nama Barang</th>
                <th width="10%">Satuan</th>
                <th>Cabang</th>
                <th width="10%" style="text-align: center;">Qty</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->item_name ?? ($item->item->name ?? '-') }}</td>
                <td>{{ $item->item->unit ?? '-' }}</td>
                <td>{{ $item->request->branch->name ?? 'Global' }}</td>
                <td style="text-align: center;">{{ $item->quantity }}</td>
                <td>{{ ucwords(str_replace('_', ' ', $item->request->status)) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        @php
            $minLevel = 3; 
            $isRejected = false;
            $rejectRole = null;

            foreach($items as $item) {
                $s = $item->request->status;
                
                if ($s === 'rejected') {
                    $isRejected = true;
                    $lastRejection = $item->request->approvals->where('status', 'rejected')->last();
                    if ($lastRejection) {
                        $rejectRole = $lastRejection->stage; 
                    }
                    $minLevel = -1; 
                    break; 
                }

                $level = 0;
                if ($s == 'pending_ka') $level = 1;
                elseif ($s == 'pending_ga') $level = 2;
                elseif ($s == 'approved') $level = 3;
                elseif ($s == 'draft' || $s == 'pending_spv') $level = 0;

                if ($level < $minLevel) {
                    $minLevel = $level;
                }
            }

            $capPath = 'C:/xampp/htdocs/sistem_barang/cap/';
            $approvedImg = $capPath . 'approved.png';
            $rejectedImg = $capPath . 'rejected.png';

            function getCapBase64($path) {
                if (file_exists($path)) {
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $data = file_get_contents($path);
                    return 'data:image/' . $type . ';base64,' . base64_encode($data);
                }
                return '';
            }

            $approvedSrc = getCapBase64($approvedImg);
            $rejectedSrc = getCapBase64($rejectedImg);
        @endphp

        <div class="signatures">
        
            <div class="sig-box">
                <p>Dibuat Oleh:</p>
                <br><br><br>
                <div class="sig-line"></div>
                <p>Staff Cabang</p>
            </div>

            <div class="sig-box">
                <p>Diketahui (SPV):</p>
                <br>
                @if($isRejected && $rejectRole == 'spv')
                    @if($rejectedSrc) <img src="{{ $rejectedSrc }}" style="width: 80px; opacity: 0.8;"> @endif
                @elseif(!$isRejected && $minLevel >= 1)
                    @if($approvedSrc) <img src="{{ $approvedSrc }}" style="width: 80px; opacity: 0.8;"> @endif
                @elseif($isRejected && ($rejectRole == 'ka' || $rejectRole == 'ga'))
                     <!-- If rejected by higher up, SPV must have approved it first -->
                     @if($approvedSrc) <img src="{{ $approvedSrc }}" style="width: 80px; opacity: 0.8;"> @endif
                @else
                    <br><br><br>
                @endif
                <div class="sig-line"></div>
                <p>Supervisor Area</p>
            </div>

            <div class="sig-box">
                <p>Disetujui (KA):</p>
                <br>
                @if($isRejected && $rejectRole == 'ka')
                    @if($rejectedSrc) <img src="{{ $rejectedSrc }}" style="width: 80px; opacity: 0.8;"> @endif
                @elseif(!$isRejected && $minLevel >= 2)
                    @if($approvedSrc) <img src="{{ $approvedSrc }}" style="width: 80px; opacity: 0.8;"> @endif
                @elseif($isRejected && $rejectRole == 'ga')
                     <!-- If rejected by GA, KA must have approved it -->
                     @if($approvedSrc) <img src="{{ $approvedSrc }}" style="width: 80px; opacity: 0.8;"> @endif
                @else
                    <br><br><br>
                @endif
                <div class="sig-line"></div>
                <p>Kepala Area</p>
            </div>

            <div class="sig-box">
                <p>Mengetahui (GA):</p>
                <br>
                @if($isRejected && $rejectRole == 'ga')
                    @if($rejectedSrc) <img src="{{ $rejectedSrc }}" style="width: 80px; opacity: 0.8;"> @endif
                @elseif(!$isRejected && $minLevel >= 3)
                    @if($approvedSrc) <img src="{{ $approvedSrc }}" style="width: 80px; opacity: 0.8;"> @endif
                @else
                    <br><br><br>
                @endif
                <div class="sig-line"></div>
                <p>General Affair</p>
            </div>
        </div>
        <p style="font-size: 10px; color: #666; margin-top: 10px;">
            * Tanda tangan digital ini sah berdasarkan data approval sistem.
            @if($minLevel == -1) (Dokumen mengandung item yang Ditolak) @endif
        </p>
    </div>
</body>
</html>
