<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Branch;
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

        $branch_id = $request->input('branch_id');

        // Main Query
        $items = $query->withSum(['requestItems as total_keluar' => function($q) use ($branch_id) {
            $q->whereHas('request', function($r) use ($branch_id) {
                $r->where('status', 'approved');
                if ($branch_id) $r->where('branch_id', $branch_id);
            });
        }], 'quantity')
        ->withSum(['requestItems as total_request' => function($q) use ($year, $month, $branch_id) {
            $q->whereHas('request', function($r) use ($year, $month, $branch_id) {
                $r->whereIn('status', ['draft', 'pending_spv', 'pending_ka', 'pending_ga'])
                  ->whereYear('created_at', $year)
                  ->whereMonth('created_at', $month);
                if ($branch_id) $r->where('branch_id', $branch_id);
            });
        }], 'quantity')
        ->orderBy('name')
        ->paginate(10);

        $branches = Branch::all();
        return view('items.index', compact('items', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'stock' => 'required|integer|min:0',
            'branch_id' => 'nullable|exists:branches,id',
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
            'branch_id' => 'nullable|exists:branches,id',
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

        $branch_id = $request->input('branch_id');

        $items = $query->withSum(['requestItems as total_keluar' => function($q) use ($branch_id) {
            $q->whereHas('request', function($r) use ($branch_id) {
                $r->where('status', 'approved');
                if ($branch_id) $r->where('branch_id', $branch_id);
            });
        }], 'quantity')
        ->withSum(['requestItems as total_request' => function($q) use ($year, $month, $branch_id) {
            $q->whereHas('request', function($r) use ($year, $month, $branch_id) {
                $r->whereIn('status', ['draft', 'pending_spv', 'pending_ka', 'pending_ga'])
                  ->whereYear('created_at', $year)
                  ->whereMonth('created_at', $month);
                if ($branch_id) $r->where('branch_id', $branch_id);
            });
        }], 'quantity')
        ->orderBy('name')
        ->get();

        $branches = Branch::all();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.inventory_pdf', compact('items', 'branches'));
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
            'items.*.branch_id' => 'nullable|exists:branches,id',
        ]);

        try {
            $imported = 0;
            $skipped = 0;
            
            foreach ($request->items as $itemData) {
                // Check if already exists (case-insensitive) - include branch_id in check
                $branchId = $itemData['branch_id'] ?? null;
                $exists = Item::whereRaw('LOWER(name) = ?', [strtolower(trim($itemData['name']))])
                    ->where('branch_id', $branchId)
                    ->exists();
                
                if ($exists && !($request->input('force_duplicate', false))) {
                    $skipped++;
                    continue;
                }
                
                Item::create([
                    'name' => trim($itemData['name']),
                    'unit' => $itemData['unit'],
                    'stock' => $itemData['stock'],
                    'category' => $itemData['category'] ?? Item::detectCategory($itemData['name']),
                    'branch_id' => $branchId,
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
        $templatePath = public_path('templates/template_import_barang_baru.xlsx');
        
        if (!file_exists($templatePath)) {
            return back()->with('error', 'Template file tidak ditemukan.');
        }
        
        return response()->download($templatePath, 'Template_Import_Barang_Baru.xlsx', [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
