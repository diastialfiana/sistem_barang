<?php

namespace App\Exports;

use App\Models\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RequestExport implements FromCollection, WithHeadings, WithEvents, WithStyles
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $items = $this->request->items;
        // Group by category with auto-detection
        $grouped = $items->groupBy(function($item) {
             $category = $item->item->category;
             // Auto-detect category if empty or UNCATEGORIZED
             if (empty($category) || $category === 'UNCATEGORIZED') {
                 $category = \App\Models\Item::detectCategory($item->item->name);
             }
             return $category;
        });

        $exportData = collect();
        $no = 0;

        foreach ($grouped as $category => $categoryItems) {
            // Category Header Row (Market)
            // We use a specific marker string we can detect in AfterSheet
            $exportData->push(['CATEGORY_HEADER', strtoupper($category), '', '', '']);

            foreach ($categoryItems as $rItem) {
                $no++;
                $exportData->push([
                    $no,
                    $rItem->item->name,
                    $rItem->item->unit,
                    $this->request->branch->name,
                    $rItem->quantity
                ]);
            }
        }
        
        return $exportData;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Barang',
            'Satuan',
            'Cabang Perusahaan', // Matches 'Cabang' / 'Jakarta Pusat' column concept
            'Total Barang'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Initial styles, more specific ones in AfterSheet
        return [
             // Header Row (which will be pushed down)
            1 => [
                'font' => ['bold' => true], 
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'F0F0F0']]
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                $highestRow = $sheet->getHighestRow();
                
                // 1. Insert Top Header Rows (Insert 9 rows, pushed Headings to row 10)
                $sheet->insertNewRowBefore(1, 9);
                
                // 2. Set Header Info
                $sheet->mergeCells('A1:E1');
                $sheet->setCellValue('A1', 'REQUEST BARANG PROCUREMENT GA');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setUnderline(true);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Info Data
                $headers = [
                    2 => ['Cabang:', $this->request->branch->name, 'Tanggal:', \Carbon\Carbon::parse($this->request->request_date)->format('d F Y')],
                    3 => ['Company:', $this->request->user->company ?? 'Bank Mega', 'Requester:', $this->request->user->name],
                    4 => ['No. Request:', $this->request->code, 'Status:', strtoupper(str_replace('_', ' ', $this->request->status))],
                ];

                foreach($headers as $row => $data) {
                    $sheet->setCellValue('A'.$row, $data[0]);
                    $sheet->setCellValue('B'.$row, $data[1]);
                    // Gap in C
                    $sheet->setCellValue('D'.$row, $data[2]);
                    $sheet->setCellValue('E'.$row, $data[3]);
                    
                    $sheet->getStyle('A'.$row)->getFont()->setBold(true);
                    $sheet->getStyle('D'.$row)->getFont()->setBold(true);
                }

                // 3. Table Styling
                $tableStart = 10; // Original headings are here now
                $highestRow = $sheet->getHighestRow(); 

                // First pass: identify category headers and format cells
                $categoryRows = [];
                for ($i = $tableStart + 1; $i <= $highestRow; $i++) {
                    $valA = $sheet->getCell('A' . $i)->getValue();
                    
                    if ($valA === 'CATEGORY_HEADER') {
                        $categoryRows[] = $i;
                        $catName = $sheet->getCell('B' . $i)->getValue();
                        
                        $sheet->setCellValue('A' . $i, $catName);
                        $sheet->setCellValue('B' . $i, ''); // Clear B
                        $sheet->mergeCells('A' . $i . ':E' . $i);
                        
                        $style = $sheet->getStyle('A' . $i);
                        $style->getFont()->setBold(true);
                        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('E0E0E0'); // Gray Match
                        $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT); // Left align as per standard category headers
                    } else {
                        // Data Row Styling
                        $sheet->getStyle('A'.$i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // No
                        $sheet->getStyle('C'.$i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Unit
                        $sheet->getStyle('E'.$i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Qty
                    }
                }
                
                // Second pass: add spacing between categories (insert from bottom to top)
                if (count($categoryRows) > 1) {
                    for ($idx = count($categoryRows) - 1; $idx > 0; $idx--) {
                        $rowToInsertBefore = $categoryRows[$idx];
                        $sheet->insertNewRowBefore($rowToInsertBefore, 10); // 10 rows = ~30cm
                    }
                }
                
                // Get updated highest row after insertions
                $highestRow = $sheet->getHighestRow();
                
                // Apply Borders ONLY to table area (not signatures)
                $sheet->getStyle('A'.$tableStart.':E'.$highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // Column Widths
                $sheet->getColumnDimension('A')->setWidth(25);
                $sheet->getColumnDimension('B')->setWidth(40);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(25);

                // 4. Symmetrical Signatures
                $sigStart = $highestRow + 4;
                
                // Headers
                $headers = ['Dibuat Oleh', 'Mengetahui (SPV)', 'Mengetahui (KA)', 'Disetujui (GA)'];
                $cols = ['A', 'B', 'C', 'D']; // Using first 4 cols, E is unused forsigs or we stretch?
                // The table has 5 cols. Let's use A, B+C, D+E? Or just A,B,C,D assuming E is empty-ish?
                // User requirement: "Symmetrical". 4 items. 5 columns. 
                // Best to merge E into D or just use A,B,C,D and leave E.
                // Let's use A, B, C, D (and set their widths roughly equal visually or acceptable).
                
                // Helper to get approval objects (for names/NIPs)
                $spv = $this->request->approvals->where('stage', 'spv')->first();
                $ka = $this->request->approvals->where('stage', 'ka')->first();
                $ga = $this->request->approvals->where('stage', 'ga')->first();

                // Get actual approval status
                if ($spv && $spv->status === 'approved') {
                    $spvS = ['text' => 'APPROVED', 'color' => '008000'];
                } elseif ($spv && $spv->status === 'rejected') {
                    $spvS = ['text' => 'REJECTED', 'color' => 'FF0000'];
                } else {
                    $spvS = ['text' => 'PENDING', 'color' => 'FFA500'];
                }
                
                if ($ka && $ka->status === 'approved') {
                    $kaS = ['text' => 'APPROVED', 'color' => '008000'];
                } elseif ($ka && $ka->status === 'rejected') {
                    $kaS = ['text' => 'REJECTED', 'color' => 'FF0000'];
                } else {
                    $kaS = ['text' => 'PENDING', 'color' => 'FFA500'];
                }
                
                // GA status - check if SPV or KA rejected first
                if (($spv && $spv->status === 'rejected') || ($ka && $ka->status === 'rejected')) {
                    $gaS = ['text' => 'TIDAK DAPAT DIPROSES', 'color' => 'FF0000'];
                } elseif ($ga && $ga->status === 'approved') {
                    $gaS = ['text' => 'APPROVED', 'color' => '008000'];
                } elseif ($ga && $ga->status === 'rejected') {
                    $gaS = ['text' => 'REJECTED', 'color' => 'FF0000'];
                } else {
                    $gaS = ['text' => 'PENDING', 'color' => 'FFA500'];
                }


                // Row 1: Titles
                $row = $sigStart;
                $sheet->setCellValue('A'.$row, $headers[0]);
                $sheet->setCellValue('B'.$row, $headers[1]);
                $sheet->setCellValue('C'.$row, $headers[2]);
                $sheet->setCellValue('D'.$row, $headers[3]);
                
                $sheet->getStyle('A'.$row.':D'.$row)->getFont()->setBold(true);
                $sheet->getStyle('A'.$row.':D'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('F0F0F0');
                
                // Row 2: Status
                $row++;
                $sheet->setCellValue('A'.$row, 'CREATED'); // Always created
                $sheet->setCellValue('B'.$row, $spvS['text']);
                $sheet->setCellValue('C'.$row, $kaS['text']);
                $sheet->setCellValue('D'.$row, $gaS['text']);
                
                 // Colors
                $sheet->getStyle('A'.$row)->getFont()->getColor()->setARGB('000000'); // Created Black
                $sheet->getStyle('B'.$row)->getFont()->getColor()->setARGB($spvS['color']);
                $sheet->getStyle('C'.$row)->getFont()->getColor()->setARGB($kaS['color']);
                $sheet->getStyle('D'.$row)->getFont()->getColor()->setARGB($gaS['color']);
                $sheet->getStyle('A'.$row.':D'.$row)->getFont()->setBold(true);
                $sheet->getStyle('A'.$row.':D'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Row 3: Gap (Names are lower)
                $nameRow = $row + 3;
                
                // Get Officials (Fallback to role search if approval missing)
                $spvUser = $spv ? $spv->approver : \App\Models\User::role('admin_1')->where('branch_id', $this->request->branch_id ?? 1)->first();
                if(!$spvUser) $spvUser = \App\Models\User::role('admin_1')->first();
                
                $kaUser = $ka ? $ka->approver : \App\Models\User::role('admin_2')->first();
                $gaUser = $ga ? $ga->approver : \App\Models\User::role('super_admin')->first();

                // Names
                $sheet->setCellValue('A'.$nameRow, $this->request->user->name);
                $sheet->setCellValue('B'.$nameRow, $spvUser ? $spvUser->name : '.....................');
                $sheet->setCellValue('C'.$nameRow, $kaUser ? $kaUser->name : '.....................');
                $sheet->setCellValue('D'.$nameRow, $gaUser ? $gaUser->name : '.....................');
                
                $sheet->getStyle('A'.$nameRow.':D'.$nameRow)->getFont()->setBold(true)->setUnderline(true);

                // NIP
                $nipRow = $nameRow + 1;
                $sheet->setCellValue('A'.$nipRow, 'NIP: ' . ($this->request->user->nip ?? '-'));
                $sheet->setCellValue('B'.$nipRow, 'NIP: ' . ($spvUser->nip ?? '..........'));
                $sheet->setCellValue('C'.$nipRow, 'NIP: ' . ($kaUser->nip ?? '..........'));
                $sheet->setCellValue('D'.$nipRow, 'NIP: ' . ($gaUser->nip ?? '..........'));
                
                // Job Title
                $jobRow = $nipRow + 1;
                $sheet->setCellValue('A'.$jobRow, $this->request->user->job_title ?? 'Staff Logistik');
                $sheet->setCellValue('B'.$jobRow, $spvUser->job_title ?? 'Supervisor');
                $sheet->setCellValue('C'.$jobRow, $kaUser->job_title ?? 'Kepala Area');
                $sheet->setCellValue('D'.$jobRow, $gaUser->job_title ?? 'Procurement / GA');
                
                $sheet->getStyle('A'.$jobRow.':D'.$jobRow)->getFont()->setItalic(true)->setSize(10);
                
                // Center All Signature Block
                $sheet->getStyle('A'.$sigStart.':D'.$jobRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // NO BORDERS for signature section
            },
        ];
    }
}
