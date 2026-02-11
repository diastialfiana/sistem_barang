<?php

namespace App\Exports;

use App\Models\Item;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class InventoryExport implements FromCollection, WithHeadings, WithMapping, WithEvents, ShouldAutoSize
{
    protected $request;
    protected $rowCount = 0;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = Item::query();
        if ($this->request->has('search')) {
            $query->where('name', 'like', '%' . $this->request->search . '%');
        }

        // Period Filter
        $period = $this->request->input('period', date('Y-m'));
        [$year, $month] = explode('-', $period);

        // Calculate 'keluar' (ALL approved requests, not just current period)
        $data = $query->withSum(['requestItems as total_keluar' => function($q) {
            $q->whereHas('request', function($r) {
                $r->where('status', 'approved');
            });
        }], 'quantity')
        ->withSum(['requestItems as total_request' => function($q) use ($year, $month) {
            $q->whereHas('request', function($r) use ($year, $month) {
                $r->whereIn('status', ['draft', 'pending_spv', 'pending_ka', 'pending_ga'])
                  ->whereYear('created_at', $year)
                  ->whereMonth('created_at', $month);
            });
        }], 'quantity')
        ->orderBy('name')
        ->get();
        
        $this->rowCount = $data->count();
        return $data;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Barang',
            'Satuan',
            'Stock (Awal)',
            'Keluar',
            'Sisa',
            'Request',
        ];
    }

    public function map($item): array
    {
        static $no = 0;
        $no++;
        
        return [
            $no,
            $item->name,
            $item->unit,
            $item->stock + ($item->total_keluar ?? 0),
            $item->total_keluar ?? 0,
            $item->stock,
            $item->total_request ?? 0,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                $highestRow = $sheet->getHighestRow();
                
                // Add signature section without borders
                $sigStart = $highestRow + 3;
                
                // Signature headers
                $sheet->setCellValue('A' . $sigStart, 'Dibuat Oleh');
                $sheet->setCellValue('C' . $sigStart, 'Mengetahui (SPV)');
                $sheet->setCellValue('E' . $sigStart, 'Mengetahui (KA)');
                $sheet->setCellValue('G' . $sigStart, 'Disetujui (GA)');
                
                // Bold headers
                $sheet->getStyle('A'.$sigStart)->getFont()->setBold(true);
                $sheet->getStyle('C'.$sigStart)->getFont()->setBold(true);
                $sheet->getStyle('E'.$sigStart)->getFont()->setBold(true);
                $sheet->getStyle('G'.$sigStart)->getFont()->setBold(true);
                
                // Status row (APPROVED stamps)
                $statusRow = $sigStart + 1;
                $sheet->setCellValue('A'.$statusRow, 'CREATED');
                $sheet->setCellValue('C'.$statusRow, 'APPROVED');
                $sheet->setCellValue('E'.$statusRow, 'APPROVED');
                $sheet->setCellValue('G'.$statusRow, 'APPROVED');
                
                // Green color for approved
                $sheet->getStyle('C'.$statusRow)->getFont()->setBold(true)->getColor()->setARGB('008000');
                $sheet->getStyle('E'.$statusRow)->getFont()->setBold(true)->getColor()->setARGB('008000');
                $sheet->getStyle('G'.$statusRow)->getFont()->setBold(true)->getColor()->setARGB('008000');
                
                // Names (placeholder - can be populated from user data)
                $nameRow = $sigStart + 4;
                
                $requesterUser = Auth::user();
                $spvUser = \App\Models\User::role('admin_1')->first();
                $kaUser = \App\Models\User::role('admin_2')->first();
                $gaUser = \App\Models\User::role('super_admin')->first();
                
                // Requester
                $sheet->setCellValue('A'.$nameRow, $requesterUser ? $requesterUser->name : '.....................');
                $sheet->setCellValue('A'.($nameRow+1), 'NIP: ' . ($requesterUser->nip ?? '..........'));
                $sheet->setCellValue('A'.($nameRow+2), 'Staff Logistik');
                
                // SPV
                $sheet->setCellValue('C'.$nameRow, $spvUser ? $spvUser->name : '.....................');
                $sheet->setCellValue('C'.($nameRow+1), 'NIP: ' . ($spvUser->nip ?? '..........'));
                $sheet->setCellValue('C'.($nameRow+2), 'Supervisor');
                
                // KA
                $sheet->setCellValue('E'.$nameRow, $kaUser ? $kaUser->name : '.....................');
                $sheet->setCellValue('E'.($nameRow+1), 'NIP: ' . ($kaUser->nip ?? '..........'));
                $sheet->setCellValue('E'.($nameRow+2), 'Kepala Area');
                
                // GA
                $sheet->setCellValue('G'.$nameRow, $gaUser ? $gaUser->name : '.....................');
                $sheet->setCellValue('G'.($nameRow+1), 'NIP: ' . ($gaUser->nip ?? '..........'));
                $sheet->setCellValue('G'.($nameRow+2), 'Procurement / GA');
                
                // Center alignment for signature section (NO BORDERS)
                $sheet->getStyle('A'.$sigStart.':G'.($nameRow+2))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}
