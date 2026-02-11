<?php

namespace App\Exports;

use App\Models\RequestItem;
use App\Models\Item;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RequestRecapExport implements FromCollection, WithHeadings, WithEvents, WithStyles
{
    protected $locationType;
    protected $month;
    protected $branches;

    public function __construct($locationType = null, $month = null)
    {
        $this->locationType = $locationType;
        $this->month = $month;
        $this->branches = $this->getBranches();
    }

    protected function normalizeCategory($category)
    {
        // Normalize category variations to standard Indonesian names
        $categoryMap = [
            'electronics' => 'Elektronik',
            'elektronik' => 'Elektronik',
            'stationery' => 'Alat Tulis Kantor',
            'furniture' => 'Peralatan Kantor',
            'cleaning' => 'Perlengkapan Kebersihan',
        ];
        
        $lower = strtolower($category);
        return $categoryMap[$lower] ?? $category;
    }
    
    protected function getBranches()
    {
        // "Active Requests" logic:
        $reqQuery = \App\Models\Request::with('branch')
            ->where('status', 'approved'); // Only final approved by GA
            
        if ($this->locationType) {
            $reqQuery->whereHas('branch', function($b) {
                $b->where('location_type', $this->locationType);
            });
        }
        
        if ($this->month) {
            $date = \Carbon\Carbon::createFromFormat('Y-m', $this->month);
            $reqQuery->whereMonth('created_at', $date->month)
                     ->whereYear('created_at', $date->year);
        }
        
        $requests = $reqQuery->get();
        return $requests->map(function($r) { return $r->branch; })->unique('id')->sortBy('name');
    }

    public function collection()
    {
        // 1. Build Query with Role Filtering
        $user = \Illuminate\Support\Facades\Auth::user();
        
        $query = RequestItem::with(['item', 'request.branch', 'request.user']) 
            ->whereHas('request', function($q) use ($user) {
                $q->where('status', 'approved'); // Only final approved by GA
                
                if ($user->hasRole('user')) {
                    $q->where('user_id', $user->id);
                } elseif ($user->hasRole('admin_1')) {
                    $q->where('branch_id', $user->branch_id);
                }
                
                if ($this->locationType) {
                    $q->whereHas('branch', function($b) {
                        $b->where('location_type', $this->locationType);
                    });
                }
                
                if ($this->month) {
                    $date = \Carbon\Carbon::createFromFormat('Y-m', $this->month);
                    $q->whereMonth('created_at', $date->month)
                      ->whereYear('created_at', $date->year);
                }
            });

        $items = $query->get();
        
        $grouped = $items->groupBy(function($rItem) {
            $category = $rItem->item->category;
            // Auto-detect category if empty or UNCATEGORIZED
            if (empty($category) || $category === 'UNCATEGORIZED') {
                $category = \App\Models\Item::detectCategory($rItem->item->name);
            }
            // Normalize category to prevent duplicates
            return $this->normalizeCategory($category);
        });

        $exportData = new Collection();
        $no = 0;

        foreach ($grouped as $category => $categoryItems) {
            // Category Header
            $exportData->push(['CATEGORY_HEADER', strtoupper($category)]);

            // Group by Item ID
            $groupedByItem = $categoryItems->groupBy('item_id');

            foreach ($groupedByItem as $itemId => $itemGroup) {
                $first = $itemGroup->first();
                $itemName = $first->item->name;
                $unit = $first->item->unit;
                
                $no++;
                
                $row = [
                    $no,
                    $itemName,
                    $unit,
                ];
                
                $totalQty = 0;
                foreach ($this->branches as $branch) {
                    $qty = $itemGroup->filter(function($i) use ($branch) {
                        return $i->request->branch_id == $branch->id;
                    })->sum('quantity');
                    
                    $row[] = $qty > 0 ? $qty : '-';
                    $totalQty += $qty;
                }
                
                $row[] = $totalQty;
                $exportData->push($row);
            }
        }

        return $exportData;
    }

    public function headings(): array
    {
        $headers = [
            'No',
            'Nama Barang',
            'Satuan',
        ];

        foreach ($this->branches as $branch) {
            $headers[] = $branch->name;
        }

        $headers[] = 'Total';
        
        return $headers;
    }

    public function styles(Worksheet $sheet)
    {
        return [
             1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                
                $totalCols = 3 + $this->branches->count() + 1; // No, Name, Unit (3) + Branches + Total
                $highestColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

                // Insert Body Rows (Header Metadata)
                $sheet->insertNewRowBefore(1, 9); 
                
                // --- META DATA ---
                $branchLabel = $this->locationType ? ucfirst(str_replace('_', ' ', $this->locationType)) : 'Semua Lokasi';
                if($this->branches->count() == 1) $branchLabel = $this->branches->first()->name;
                
                $overallStatus = 'APPROVED'; // Forced

                $sheet->mergeCells('A1:' . $highestColumn . '1');
                $sheet->setCellValue('A1', 'REQUEST BARANG PROCUREMENT GA');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setUnderline(true);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Determining "Requester" for the Recap Header
                $requesterUser = null;

                // Strategy 1: Find ANY request matching the filter that was created by a Staff
                $query = \App\Models\Request::with(['user'])
                    ->where('status', 'approved'); // Only final approved by GA
                
                if ($this->locationType) {
                     $query->whereHas('branch', function($q) { $q->where('location_type', $this->locationType); });
                }
                
                if ($this->month) {
                    $date = \Carbon\Carbon::createFromFormat('Y-m', $this->month);
                    $query->whereMonth('created_at', $date->month)
                          ->whereYear('created_at', $date->year);
                }

                // Check if we can find a staff among the actual requestors
                $staffReq = (clone $query)->whereHas('user', function($q) {
                    $q->whereHas('roles', function($q2) {
                        $q2->where('name', 'user');
                    });
                })->first();

                if ($staffReq && $staffReq->user) {
                     $requesterUser = $staffReq->user;
                }

                // Strategy 2: If no staff found in requests (e.g. only Admins made requests), find ANY Staff in the relevant branches
                if (!$requesterUser) {
                     // Get branches for this location type
                     $branchIds = \App\Models\Branch::query();
                     if ($this->locationType) {
                         $branchIds->where('location_type', $this->locationType);
                     }
                     $branchIds = $branchIds->pluck('id');

                     if ($branchIds->isNotEmpty()) {
                         $staffInBranch = \App\Models\User::role('user')
                            ->whereIn('branch_id', $branchIds)
                            ->first();
                         if ($staffInBranch) $requesterUser = $staffInBranch;
                     }
                }

                // Strategy 3: Find ANY Staff user in the system (Fallback)
                if (!$requesterUser) {
                    $anyStaff = \App\Models\User::role('user')->first();
                    if ($anyStaff) $requesterUser = $anyStaff;
                }

                // Final Fallback: Auth user
                if (!$requesterUser) $requesterUser = \Illuminate\Support\Facades\Auth::user();

                $requesterName = $requesterUser->name;
                $company = $requesterUser->company ?? 'Bank Mega';
                
                $dateLabel = date('d F Y');
                if ($this->month) {
                    $dateObj = \Carbon\Carbon::createFromFormat('Y-m', $this->month);
                    $dateLabel = $dateObj->format('F Y');
                }

                 // Info Data
                $headers = [
                    2 => ['Cabang:', $branchLabel, 'Tanggal:', $dateLabel],
                    3 => ['Company:', $company, 'Requester:', $requesterName],
                    4 => ['No. Request:', 'RECAP-'.date('Ymd'), 'Status:', $overallStatus], 
                ];

                foreach($headers as $row => $data) {
                    $sheet->setCellValue('A'.$row, $data[0]);
                    $sheet->setCellValue('B'.$row, $data[1]);
                    
                    $colRight = 'D'; 
                    if ($totalCols < 5) $colRight = 'C'; 
                    
                    $sheet->setCellValue($colRight.$row, $data[2]);
                    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($colRight)+1).$row, $data[3]);
                    
                    $sheet->getStyle('A'.$row)->getFont()->setBold(true);
                    $sheet->getStyle($colRight.$row)->getFont()->setBold(true);
                }

                // --- TABLE HEADER (Row 10) ---
                $tableStart = 10; 
                $sheet->getStyle('A'.$tableStart . ':' . $highestColumn . $tableStart)->getFont()->setBold(true);
                $sheet->getStyle('A'.$tableStart . ':' . $highestColumn . $tableStart)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A'.$tableStart . ':' . $highestColumn . $tableStart)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('A'.$tableStart . ':' . $highestColumn . $tableStart)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('F0F0F0');
                
                // --- TABLE BODY ---
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle('A'.$tableStart . ':' . $highestColumn . $highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                for ($i = $tableStart + 1; $i <= $highestRow; $i++) {
                    $valA = $sheet->getCell('A' . $i)->getValue();
                    
                    if ($valA === 'CATEGORY_HEADER') {
                         $catName = $sheet->getCell('B' . $i)->getValue();
                        
                        $sheet->setCellValue('A' . $i, $catName);
                        $sheet->setCellValue('B' . $i, '');
                        $sheet->mergeCells('A' . $i . ':' . $highestColumn . $i);

                        $style = $sheet->getStyle('A' . $i);
                        $style->getFont()->setBold(true);
                        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D0D0D0');
                        $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    } else {
                        // Data Row
                        $sheet->getStyle('A' . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // No
                        $sheet->getStyle('C' . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Unit
                        $sheet->getStyle('D' . $i . ':' . $highestColumn . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                }
                
                // Column Widths - Set to 30 for A-F
                $sheet->getColumnDimension('A')->setWidth(30);  // No 
                $sheet->getColumnDimension('B')->setWidth(30); // Name
                $sheet->getColumnDimension('C')->setWidth(30); // Unit
                $sheet->getColumnDimension('D')->setWidth(30);
                $sheet->getColumnDimension('E')->setWidth(30);
                $sheet->getColumnDimension('F')->setWidth(30);
                
                // --- FOOTER SIGNATURE ---
                $sigStart = $highestRow + 4;
                
                $colSPV = 'B';
                $colKA = 'C';
                $colGA = 'D';
     
                // Headers
                $sheet->setCellValue('A' . $sigStart, 'Dibuat Oleh');
                $sheet->setCellValue($colSPV . $sigStart, 'Mengetahui (SPV)');
                $sheet->setCellValue($colKA . $sigStart, 'Mengetahui (KA)');
                $sheet->setCellValue($colGA . $sigStart, 'Disetujui (GA)');
                
                $sheet->getStyle('A'.$sigStart)->getFont()->setBold(true);
                $sheet->getStyle($colSPV.$sigStart)->getFont()->setBold(true);
                $sheet->getStyle($colKA.$sigStart)->getFont()->setBold(true);
                $sheet->getStyle($colGA.$sigStart)->getFont()->setBold(true);
                
                // FORCED APPROVED STATUS
                $statusRow = $sigStart + 1;
                $sheet->setCellValue('A'.$statusRow, 'CREATED');
                $sheet->setCellValue($colSPV.$statusRow, 'APPROVED');
                $sheet->setCellValue($colKA.$statusRow, 'APPROVED');
                $sheet->setCellValue($colGA.$statusRow, 'APPROVED');
                
                // Color Styling - Use specific columns to ensure all get colored
                $sheet->getStyle($colSPV.$statusRow)->getFont()->setBold(true)->getColor()->setARGB('008000'); // Green
                $sheet->getStyle($colKA.$statusRow)->getFont()->setBold(true)->getColor()->setARGB('008000'); // Green
                $sheet->getStyle($colGA.$statusRow)->getFont()->setBold(true)->getColor()->setARGB('008000'); // Green
                
                $sheet->getStyle('A'.$statusRow)->getFont()->setBold(true)->getColor()->setARGB('000000'); // Created is black

                $nameRow = $sigStart + 4;
                $sheet->setCellValue('A'.$nameRow, $requesterName);
                
                // Use requesterUser for NIP
                $sheet->setCellValue('A'.($nameRow+1), 'NIP: ' . ($requesterUser->nip ?? '-'));
                $sheet->setCellValue('A'.($nameRow+2), $requesterUser->job_title ?? 'Staff Logistik');

                // Officials
                $spvUser = \App\Models\User::role('admin_1')->first(); 
                $kaUser = \App\Models\User::role('admin_2')->first();
                $gaUser = \App\Models\User::role('super_admin')->first();
                
                // Determine branch from any available request to correct the SPV signature if possible
                $paramReq = $query->first(); 
                 if ($paramReq && $paramReq->branch_id) {
                    $branchSpv = \App\Models\User::role('admin_1')->where('branch_id', $paramReq->branch_id)->first();
                    if ($branchSpv) $spvUser = $branchSpv;
                }

                $sheet->setCellValue($colSPV . $nameRow, $spvUser ? $spvUser->name : '.....................');
                $sheet->setCellValue($colSPV . ($nameRow+1), 'NIP: ' . ($spvUser->nip ?? '..........'));
                $sheet->setCellValue($colSPV . ($nameRow+2), 'Supervisor');

                $sheet->setCellValue($colKA . $nameRow, $kaUser ? $kaUser->name : '.....................');
                $sheet->setCellValue($colKA . ($nameRow+1), 'NIP: ' . ($kaUser->nip ?? '..........'));
                $sheet->setCellValue($colKA . ($nameRow+2), 'Kepala Area');

                $sheet->setCellValue($colGA . $nameRow, $gaUser ? $gaUser->name : '.....................');
                $sheet->setCellValue($colGA . ($nameRow+1), 'NIP: ' . ($gaUser->nip ?? '..........'));
                $sheet->setCellValue($colGA . ($nameRow+2), 'Procurement / GA');

                // Center alignment for sigs
                $sheet->getStyle('A'.$sigStart.':A'.($nameRow+2))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($colSPV.$sigStart.':'.$colSPV.($nameRow+2))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($colKA.$sigStart.':'.$colKA.($nameRow+2))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($colGA.$sigStart.':'.$colGA.($nameRow+2))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Center alignment for sigs (NO BORDERS)
                $sheet->getStyle('A'.$sigStart.':'.$highestColumn.($nameRow+2))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}
