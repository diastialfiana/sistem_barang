<!DOCTYPE html>
<html>
<head>
    <title>Request {{ $request->code }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 11px; margin: 0; padding: 20px; }
        
        .header-title { 
            text-align: center; 
            font-weight: bold; 
            font-size: 16px; 
            text-transform: uppercase; 
            margin-bottom: 20px;
            text-decoration: underline;
        }

        .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info-table td { padding: 3px 0; vertical-align: top; }
        .info-label { font-weight: bold; width: 120px; }
        .info-colon { width: 10px; text-align: center; }
        .info-value { }

        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 6px; }
        .data-table th { background-color: #f0f0f0; text-align: center; font-weight: bold; }
        .data-table td { vertical-align: middle; }
        
        .category-row { background-color: #e0e0e0; font-weight: bold; text-transform: uppercase; text-align: left; }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        .signature-table { width: 100%; border-collapse: collapse; margin-top: 30px; page-break-inside: avoid; }
        .signature-table td { border: 1px solid #000; width: 25%; text-align: center; vertical-align: top; padding: 8px 5px; position: relative; }
        .signature-header { font-weight: bold; background-color: #f0f0f0; display: block; padding: 5px 0; margin-bottom: 8px; }
        .signature-name { font-weight: bold; text-decoration: underline; display: block; margin-top: 3px; margin-bottom: 2px; }
        .signature-nip { font-size: 10px; display: block; margin-bottom: 2px; }
        .signature-job { font-size: 10px; display: block; font-style: italic; }
        .stamp-area { position: relative; height: 40px; margin: 5px 0; display: flex; align-items: center; justify-content: center; }

        .status-approved { color: green; }
        .status-rejected { color: red; }
        .status-pending { color: orange; }
    </style>
</head>
<body>

    <div class="header-title">REQUEST BARANG PROCUREMENT GA</div>

    <table class="info-table">
        <tr>
           <td width="50%">
                <table width="100%">
                    <tr>
                        <td class="info-label">Cabang</td>
                        <td class="info-colon">:</td>
                        <td class="info-value">{{ $request->branch->name }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Company</td>
                        <td class="info-colon">:</td>
                        <td class="info-value">{{ $request->user->company ?? 'Bank Mega' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">No. Request</td>
                        <td class="info-colon">:</td>
                        <td class="info-value">{{ $request->code }}</td>
                    </tr>
                </table>
            </td>
           <td width="50%">
                <table width="100%">
                    <tr>
                        <td class="info-label">Tanggal</td>
                        <td class="info-colon">:</td>
                        <td class="info-value">{{ \Carbon\Carbon::parse($request->request_date)->format('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Requester</td>
                        <td class="info-colon">:</td>
                        <td class="info-value">{{ $request->user->name }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Status</td>
                        <td class="info-colon">:</td>
                        <td class="info-value">{{ strtoupper(str_replace('_', ' ', $request->status)) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="45%">Nama Barang</th>
                <th width="15%">Satuan</th>
                <th width="20%">{{ $request->branch->name }}</th>
                <th width="15%">Total Barang</th>
            </tr>
        </thead>
        <tbody>
            @php
                $groupedItems = $request->items->groupBy(function($item) {
                    $category = $item->item->category;
                    if (empty($category) || $category === 'UNCATEGORIZED') {
                        $category = \App\Models\Item::detectCategory($item->item->name);
                    }
                    return $category;
                });
                $no = 0;
            @endphp

            @foreach($groupedItems as $category => $items)
                <tr>
                    <td colspan="5" class="category-row">{{ strtoupper($category) }}</td>
                </tr>
                @foreach($items as $item)
                    @php $no++; @endphp
                    <tr>
                        <td class="text-center">{{ $no }}</td>
                        <td>{{ $item->item->name }}</td>
                        <td class="text-center">{{ $item->item->unit }}</td>
                        <td class="text-center">{{ $request->branch->name }}</td> 
                        <td class="text-center">{{ $item->quantity }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    @php
        $spv = $request->approvals->where('stage', 'spv')->first();
        $ka = $request->approvals->where('stage', 'ka')->first();
        $ga = $request->approvals->where('stage', 'ga')->first();
   
        if ($spv && $spv->status === 'approved') {
            $spvStatus = ['text' => 'APPROVED', 'class' => 'status-approved'];
        } elseif ($spv && $spv->status === 'rejected') {
            $spvStatus = ['text' => 'REJECTED', 'class' => 'status-rejected'];
        } else {
            $spvStatus = ['text' => 'PENDING', 'class' => 'status-pending'];
        }
        
        if ($ka && $ka->status === 'approved') {
            $kaStatus = ['text' => 'APPROVED', 'class' => 'status-approved'];
        } elseif ($ka && $ka->status === 'rejected') {
            $kaStatus = ['text' => 'REJECTED', 'class' => 'status-rejected'];
        } else {
            $kaStatus = ['text' => 'PENDING', 'class' => 'status-pending'];
        }
      
        if (($spv && $spv->status === 'rejected') || ($ka && $ka->status === 'rejected')) {
            $gaStatus = ['text' => 'TIDAK DAPAT DIPROSES', 'class' => 'status-rejected'];
        } elseif ($ga && $ga->status === 'approved') {
            $gaStatus = ['text' => 'APPROVED', 'class' => 'status-approved'];
        } elseif ($ga && $ga->status === 'rejected') {
            $gaStatus = ['text' => 'REJECTED', 'class' => 'status-rejected'];
        } else {
            $gaStatus = ['text' => 'PENDING', 'class' => 'status-pending'];
        }

        $spvName = $spv ? $spv->approver->name : (\App\Models\User::role('admin_1')->where('branch_id', $request->branch_id)->first()->name ?? '.....................');
        $kaName = $ka ? $ka->approver->name : (\App\Models\User::role('admin_2')->first()->name ?? '.....................');
        $gaName = $ga ? $ga->approver->name : (\App\Models\User::role('super_admin')->first()->name ?? '.....................');

        $spvNip = $spv ? ($spv->approver->nip ?? '-') : (\App\Models\User::role('admin_1')->where('branch_id', $request->branch_id)->first()->nip ?? '..........');
        $kaNip = $ka ? ($ka->approver->nip ?? '-') : (\App\Models\User::role('admin_2')->first()->nip ?? '..........');
        $gaNip = $ga ? ($ga->approver->nip ?? '-') : (\App\Models\User::role('super_admin')->first()->nip ?? '..........');
    @endphp

    <table class="signature-table">
        <tr>
            <td>
                <span class="signature-header">Dibuat Oleh</span>
                <div style="height: 40px; margin: 5px 0;"></div>
                <span class="signature-name">{{ $request->user->name }}</span>
                <span class="signature-nip">NIP: {{ $request->user->nip ?? '-' }}</span>
                <span class="signature-job">{{ $request->user->job_title ?? 'Staff Logistik' }}</span>
            </td>
            <td>
                <span class="signature-header">Mengetahui (SPV)</span>
                <div class="stamp-area">
                    @if($spvStatus['text'] === 'APPROVED')
                        <img src="{{ 'file://' . base_path('cap/approved.png') }}" style="width:80px; height:auto; opacity:0.7;">
                    @elseif($spvStatus['text'] === 'REJECTED')
                        <img src="{{ 'file://' . base_path('cap/rejected.png') }}" style="width:80px; height:auto; opacity:0.7;">
                    @endif
                </div>
                <span class="signature-name">{{ $spvName }}</span>
                <span class="signature-nip">NIP: {{ $spvNip }}</span>
                <span class="signature-job">Supervisor</span>
            </td>
            <td>
                <span class="signature-header">Mengetahui (KA)</span>
                <div class="stamp-area">
                    @if($kaStatus['text'] === 'APPROVED')
                        <img src="{{ 'file://' . base_path('cap/approved.png') }}" style="width:80px; height:auto; opacity:0.7;">
                    @elseif($kaStatus['text'] === 'REJECTED')
                        <img src="{{ 'file://' . base_path('cap/rejected.png') }}" style="width:80px; height:auto; opacity:0.7;">
                    @endif
                </div>
                <span class="signature-name">{{ $kaName }}</span>
                <span class="signature-nip">NIP: {{ $kaNip }}</span>
                <span class="signature-job">Kepala Area</span>
            </td>
            <td>
                <span class="signature-header">Disetujui (GA)</span>
                <div class="stamp-area">
                    @if($gaStatus['text'] === 'APPROVED')
                        <img src="{{ 'file://' . base_path('cap/approved.png') }}" style="width:80px; height:auto; opacity:0.7;">
                    @elseif($gaStatus['text'] === 'REJECTED')
                        <img src="{{ 'file://' . base_path('cap/rejected.png') }}" style="width:80px; height:auto; opacity:0.7;">
                    @elseif($gaStatus['text'] === 'TIDAK DAPAT DIPROSES')
                        <span class="{{ $gaStatus['class'] }}" style="font-weight:bold; display:block; margin-top:15px;">{{ $gaStatus['text'] }}</span>
                    @endif
                </div>
                <span class="signature-name">{{ $gaName }}</span>
                <span class="signature-nip">NIP: {{ $gaNip }}</span>
                <span class="signature-job">Procurement / GA</span>
            </td>
        </tr>
    </table>

</body>
</html>
