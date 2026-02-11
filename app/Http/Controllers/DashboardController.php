<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //
    public function index()
    {
        $user = auth()->user();
        $baseQuery = \App\Models\Request::query();

        // 1. Determine Stats based on Role
        if ($user->hasRole(['super_admin', 'admin_1', 'admin_2'])) {
            // Admin Logic
            $pendingQuery = clone $baseQuery;
            if ($user->hasRole('admin_1')) {
                $pendingQuery->where('status', 'pending_spv')->where('branch_id', $user->branch_id);
            } elseif ($user->hasRole('admin_2')) {
                $pendingQuery->where('status', 'pending_ka');
            } elseif ($user->hasRole('super_admin')) {
                $pendingQuery->where('status', 'pending_ga');
            }

            $stats = [
                [
                    'label' => 'Perlu Approval',
                    'value' => $pendingQuery->count(),
                    'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                    'color' => 'amber'
                ],
                [
                    'label' => 'Total Request',
                    'value' => \App\Models\Request::whereMonth('created_at', now()->month)->count(),
                    'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                    'color' => 'blue'
                ],
                [
                    'label' => 'Item Keluar',
                    'value' => \App\Models\RequestItem::whereHas('request', fn($q) => $q->where('status', 'approved'))->sum('quantity'),
                    'icon' => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4',
                    'color' => 'emerald'
                ],
            ];
            
            $recentRequests = \App\Models\Request::with('user')->latest()->take(5)->get();

        } else {
            // Staff/User Logic
            $stats = [
                [
                    'label' => 'Draft', 
                    'value' => \App\Models\Request::where('user_id', $user->id)->where('status', 'draft')->count(), 
                    'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z', 
                    'color' => 'slate'
                ],
                [
                    'label' => 'Proses', 
                    'value' => \App\Models\Request::where('user_id', $user->id)->whereIn('status', ['pending_spv', 'pending_ka', 'pending_ga'])->count(), 
                    'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 
                    'color' => 'blue'
                ],
                [
                    'label' => 'Selesai', 
                    'value' => \App\Models\Request::where('user_id', $user->id)->where('status', 'approved')->count(), 
                    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 
                    'color' => 'emerald'
                ],
            ];
            $recentRequests = \App\Models\Request::where('user_id', $user->id)->latest()->take(5)->get();
        }

        // 2. Chart Data: Top 5 Items Out (Approved)
        $topItems = \App\Models\RequestItem::whereHas('request', fn($q) => $q->where('status', 'approved'))
            ->selectRaw('item_name, SUM(quantity) as total_qty')
            ->groupBy('item_name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        return view('dashboard', compact('stats', 'recentRequests', 'topItems'));
    }
}
