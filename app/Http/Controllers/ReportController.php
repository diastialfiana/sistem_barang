<?php

namespace App\Http\Controllers;

use App\Models\Request as RequestModel;
use App\Models\RequestItem;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Period Filter
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));
        
        // Query RequestItems directly to get item details
        $query = RequestItem::with(['request.branch', 'item'])
            ->whereHas('request', function($q) use ($month, $year) {
                $q->whereMonth('created_at', $month)
                  ->whereYear('created_at', $year)
                  ->whereNotIn('status', ['draft']); // Exclude drafts
            });

        // Category Filter (Using Item Name as proxy for category if no category column exists, or just filter by search)
        // User asked for "Category" but Item table has no category. Use Search instead for now.
        if ($request->filled('search')) {
            $query->whereHas('item', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $items = $query->paginate(20);

        return view('reports.index', compact('items', 'month', 'year'));
    }

    public function exportPdf(Request $request) 
    {
         // Same logic as index
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));
        
        $items = RequestItem::with(['request.branch', 'item', 'request.approvals.approver'])
            ->whereHas('request', function($q) use ($month, $year) {
                $q->whereMonth('created_at', $month)
                  ->whereYear('created_at', $year)
                  ->whereNotIn('status', ['draft']);
            })
            ->get();

        $pdf = Pdf::loadView('exports.logistic_pdf', compact('items', 'month', 'year'));
        $pdf->setPaper('a4', 'landscape');
        return $pdf->download('Laporan-Logistik-'.$year.'-'.$month.'.pdf');
    }
}
