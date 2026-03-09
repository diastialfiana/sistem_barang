<?php

namespace App\Http\Controllers;

use App\Models\RequestItem;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RecapController extends Controller
{
    public function index()
    {
        // Get unique months from RequestItems that are ATK
        $months = RequestItem::join('items', 'request_items.item_id', '=', 'items.id')
            ->join('requests', 'request_items.request_id', '=', 'requests.id')
            ->where('items.category', 'Alat Tulis Kantor')
            ->whereIn('requests.status', ['approved', 'pending_ga', 'pending_ka', 'pending_spv'])
            ->select(
                DB::raw("MONTH(requests.request_date) as month"),
                DB::raw("YEAR(requests.request_date) as year"),
                DB::raw("SUM(request_items.quantity) as total_qty"),
                DB::raw("SUM(request_items.quantity * items.price) as total_price")
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return view('recaps.index', compact('months'));
    }

    public function show($year, $month)
    {
        $date = Carbon::createFromDate($year, $month, 1);
        $label = $date->translatedFormat('F Y');

        $items = RequestItem::with(['item', 'request.user', 'request.branch'])
            ->join('items', 'request_items.item_id', '=', 'items.id')
            ->join('requests', 'request_items.request_id', '=', 'requests.id')
            ->where('items.category', 'Alat Tulis Kantor')
            ->whereIn('requests.status', ['approved', 'pending_ga', 'pending_ka', 'pending_spv'])
            ->whereMonth('requests.request_date', $month)
            ->whereYear('requests.request_date', $year)
            ->select('request_items.*')
            ->get();

        return view('recaps.show', compact('items', 'label', 'year', 'month'));
    }
}
