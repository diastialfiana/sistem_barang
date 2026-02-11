<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

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
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\InventoryExport($request), 'Inventory-'.date('Y-m-d').'.xlsx');
    }
}
