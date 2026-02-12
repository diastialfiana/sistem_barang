<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Imports\ItemsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Period Filter
        $period = $request->input('period', date('Y-m'));
        [$year, $month] = explode('-', $period);

        // Calculate 'keluar' (ALL approved requests, not just current period)
        $items = $query->withSum(['requestItems as total_keluar' => function($q) {
            $q->whereHas('request', function($r) {
                $r->where('status', 'approved');
            });
        }], 'quantity')
        // Calculate 'pending_request' (requests not yet approved/rejected)
        ->withSum(['requestItems as total_request' => function($q) use ($year, $month) {
            $q->whereHas('request', function($r) use ($year, $month) {
                // Assuming we want to see requests made in this period that are still pending
                // Or maybe all pending requests regardless of period? 
                // Context: User likely wants to see activity for the month.
                $r->whereIn('status', ['draft', 'pending_spv', 'pending_ka', 'pending_ga'])
                  ->whereYear('created_at', $year)
                  ->whereMonth('created_at', $month);
            });
        }], 'quantity')
        ->orderBy('name')
        ->paginate(10);

        return view('items.index', compact('items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'stock' => 'required|integer|min:0',
        ]);

        Item::create($request->all());

        return back()->with('success', 'Item berhasil ditambahkan.');
    }

    public function update(Request $request, Item $item)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'stock' => 'required|integer|min:0',
        ]);

        $item->update($request->all());

        return back()->with('success', 'Item berhasil diperbarui.');
    }

    public function destroy(Item $item)
    {
        // Check if item has been used in any requests
        if ($item->requestItems()->count() > 0) {
            return back()->with('error', 'Item tidak dapat dihapus karena sudah pernah digunakan dalam request. Hapus request terkait terlebih dahulu atau ubah item request ke barang lain.');
        }
        
        $item->delete();
        return back()->with('success', 'Item berhasil dihapus.');
    }

    public function exportPdf(Request $request)
    {
        $query = Item::query();
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Period Filter
        $period = $request->input('period', date('Y-m'));
        [$year, $month] = explode('-', $period);

        // Calculate 'keluar' (ALL approved requests, not just current period)
        $items = $query->withSum(['requestItems as total_keluar' => function($q) {
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

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.inventory_pdf', compact('items'));
        return $pdf->download('Inventory-'.date('Y-m-d').'.pdf');
    }

    public function exportExcel(Request $request)
    {
        // Simple collection export for MVP
        // In real app, create a formal Export class
        return Excel::download(new \App\Exports\InventoryExport($request), 'Inventory-'.date('Y-m-d').'.xlsx');
    }

    public function previewImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048',
        ], [
            'file.required' => 'File Excel harus dipilih.',
            'file.mimes' => 'File harus berformat Excel (.xlsx atau .xls).',
            'file.max' => 'Ukuran file maksimal 2MB.',
        ]);

        try {
            $import = new ItemsImport();
            Excel::import($import, $request->file('file'));
            
            // Check if there are validation errors from the import
            if ($import->hasErrors()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terdapat kesalahan pada beberapa baris Excel.',
                    'errors' => $import->getErrors(),
                ], 422);
            }
            
            $importData = $import->getImportData();
            
            // Check if no valid data found
            if (empty($importData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data valid yang ditemukan di file Excel. Pastikan Excel memiliki header dan minimal satu baris data.',
                ], 422);
            }
            
            // Separate new items and duplicates
            $newItems = array_filter($importData, function($item) {
                return !$item['is_duplicate'];
            });
            
            $duplicates = array_filter($importData, function($item) {
                return $item['is_duplicate'];
            });
            
            return response()->json([
                'success' => true,
                'data' => [
                    'all_items' => $importData,
                    'new_items' => array_values($newItems),
                    'duplicates' => array_values($duplicates),
                    'total' => count($importData),
                    'new_count' => count($newItems),
                    'duplicate_count' => count($duplicates),
                ],
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses file Excel: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function processImport(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.name' => 'required|string|max:255',
            'items.*.unit' => 'required|string|max:50',
            'items.*.stock' => 'required|integer|min:0',
            'items.*.category' => 'nullable|string',
        ]);

        try {
            $imported = 0;
            $skipped = 0;
            
            foreach ($request->items as $itemData) {
                // Check if already exists (case-insensitive)
                $exists = Item::whereRaw('LOWER(name) = ?', [strtolower(trim($itemData['name']))])->exists();
                
                if ($exists && !($request->input('force_duplicate', false))) {
                    $skipped++;
                    continue;
                }
                
                Item::create([
                    'name' => trim($itemData['name']),
                    'unit' => $itemData['unit'],
                    'stock' => $itemData['stock'],
                    'category' => $itemData['category'] ?? Item::detectCategory($itemData['name']),
                ]);
                
                $imported++;
            }
            
            return response()->json([
                'success' => true,
                'message' => "Import berhasil! {$imported} data ditambahkan" . ($skipped > 0 ? ", {$skipped} data dilewati." : "."),
                'imported' => $imported,
                'skipped' => $skipped,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengimport data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        $templatePath = public_path('templates/template_import_barang.xlsx');
        
        if (!file_exists($templatePath)) {
            return back()->with('error', 'Template file tidak ditemukan.');
        }
        
        return response()->download($templatePath, 'Template_Import_Barang.xlsx');
    }
}
