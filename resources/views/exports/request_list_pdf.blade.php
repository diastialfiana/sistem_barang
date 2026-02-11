<!DOCTYPE html>
<html>
<head>
    <title>Daftar Request Barang</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10px; margin: 0; padding: 10px; }
        
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
        .data-table th { background-color: #f0f0f0; text-align: center; font-weight: bold; vertical-align: middle; }
        .data-table td { vertical-align: middle; }
        
        .category-row { background-color: #d0d0d0; font-weight: bold; text-transform: uppercase; text-align: left; padding-left: 10px; }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        .signature-table { width: 100%; border-collapse: collapse; margin-top: 30px; page-break-inside: avoid; }
        .signature-table td { border: 1px solid #000; width: 25%; text-align: center; vertical-align: top; padding: 10px 5px; }
        .signature-header { font-weight: bold; background-color: #f0f0f0; display: block; padding-bottom: 10px; margin-bottom: 10px; border-bottom: 1px solid #ddd; }
        .signature-status { font-weight: bold; font-size: 11px; margin-bottom: 30px; display: block; text-transform: uppercase; }
        .signature-name { font-weight: bold; text-decoration: underline; display: block; }
        .signature-nip { font-size: 9px; display: block; }
        .signature-job { font-size: 9px; display: block; font-style: italic; }

        .status-approved { color: green; }
        .status-rejected { color: red; }
    </style>
</head>
<body>
    @php
        $locationType = request('location_type');
        $branchLabel = $locationType ? ucfirst(str_replace('_', ' ', $locationType)) : 'Semua Lokasi';
        
        $uniqueBranches = $requests->pluck('branch_id')->unique();
        if ($uniqueBranches->count() === 1 && $requests->first()->branch) {
            $branchLabel = $requests->first()->branch->name;
        }

        // Data passed from controller: $requesterUser, $spvUser, $kaUser, $gaUser
        $requesterName = $requesterUser->name ?? '-';
        $company = $requesterUser->company ?? 'Bank Mega';
        $reqDate = date('d F Y');
        $reqNo = 'RECAP-' . date('Ymd');
   
        $overallStatus = 'APPROVED';

        $allItems = $requests->pluck('items')->flatten();
        $groupedItems = $allItems->groupBy(function($item) {
             $category = $item->item->category;   
             if (empty($category) || $category === 'UNCATEGORIZED') {
                 $category = \App\Models\Item::detectCategory($item->item->name);
             }
             return $category;
        });
        
        $branchCols = $requests->pluck('branch')->unique('id')->sortBy('name');
        
        $stampPath = 'file://' . base_path('cap/approved.png'); 
    @endphp

    <div class="header-title">REQUEST BARANG PROCUREMENT GA</div>

    <table class="info-table">
        <tr>
        
            <td width="50%">
                <table width="100%">
                    <tr>
                        <td class="info-label">Cabang</td>
                        <td class="info-colon">:</td>
                        <td class="info-value">{{ $branchLabel }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Company</td>
                        <td class="info-colon">:</td>
                        <td class="info-value">{{ $company }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">No. Request</td>
                        <td class="info-colon">:</td>
                        <td class="info-value">{{ $reqNo }}</td>
                    </tr>
                </table>
            </td>
        
            <td width="50%">
                <table width="100%">
                    <tr>
                        <td class="info-label">Tanggal</td>
                        <td class="info-colon">:</td>
                        <td class="info-value">{{ $reqDate }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Requester</td>
                        <td class="info-colon">:</td>
                        <td class="info-value">{{ $requesterName }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Status</td>
                        <td class="info-colon">:</td>
                        <td class="info-value">{{ $overallStatus }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="25%">Nama Barang</th>
                <th width="10%">Satuan</th>
                @foreach($branchCols as $branch)
                    <th>{{ $branch->name }}</th>
                @endforeach
                <th width="10%">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 0; @endphp
            @foreach($groupedItems as $category => $items)
                <tr>
                    <td colspan="{{ 4 + $branchCols->count() }}" class="category-row">{{ strtoupper($category) }}</td>
                </tr>
                
                @foreach($items->groupBy('item_id') as $itemId => $itemGroup)
                    @php
                        $firstItem = $itemGroup->first()->item;
                        $no++;
                        $totalRow = 0;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $no }}</td>
                        <td>{{ $firstItem->name }}</td>
                        <td class="text-center">{{ $firstItem->unit }}</td>
                        
                        @foreach($branchCols as $branch)
                            @php
                                $qty = $itemGroup->where('request.branch_id', $branch->id)->sum('quantity');
                                $totalRow += $qty;
                            @endphp
                            <td class="text-center">{{ $qty > 0 ? $qty : '-' }}</td>
                        @endforeach
                        
                        <td class="text-center" style="font-weight: bold;">{{ $totalRow }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td>
                <span class="signature-header">Dibuat Oleh</span>
                <br>
        
                <div style="height:35px;"></div>
                <br>
                <span class="signature-name">{{ $requesterName }}</span>
                <span class="signature-nip">NIP: {{ $requesterUser->nip ?? '-' }}</span>
                <span class="signature-job">{{ $requesterUser->job_title ?? 'Staff Logistik' }}</span>
            </td>
            <td>
                <span class="signature-header">Mengetahui (SPV)</span>
             
                <div class="stamp-area" style="height:60px; display:flex; justify-content:center; align-items:center;">
                    <img src="{{ $stampPath }}" style="width:90px; height:auto; opacity:0.8;">
                </div>
                <span class="signature-name">{{ $spvUser ? $spvUser->name : '.....................' }}</span>
                <span class="signature-nip">NIP: {{ $spvUser->nip ?? '..........' }}</span>
                <span class="signature-job">Supervisor</span>
            </td>
            <td>
                <span class="signature-header">Mengetahui (KA)</span>
              
                <div class="stamp-area" style="height:60px; display:flex; justify-content:center; align-items:center;">
                     <img src="{{ $stampPath }}" style="width:90px; height:auto; opacity:0.8;">
                </div>
                <span class="signature-name">{{ $kaUser ? $kaUser->name : '.....................' }}</span>
                <span class="signature-nip">NIP: {{ $kaUser->nip ?? '..........' }}</span>
                <span class="signature-job">Kepala Area</span>
            </td>
            <td>
                <span class="signature-header">Disetujui (GA)</span>
               
                <div class="stamp-area" style="height:60px; display:flex; justify-content:center; align-items:center;">
                     <img src="{{ $stampPath }}" style="width:90px; height:auto; opacity:0.8;">
                </div>
                <span class="signature-name">{{ $gaUser ? $gaUser->name : '.....................' }}</span>
                <span class="signature-nip">NIP: {{ $gaUser->nip ?? '..........' }}</span>
                <span class="signature-job">Procurement / GA</span>
            </td>
        </tr>
    </table>

</body>
</html>
