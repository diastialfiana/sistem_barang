<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Request as RequestModel;
use App\Services\RequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    protected $requestService;

    public function __construct(RequestService $requestService)
    {
        $this->requestService = $requestService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = RequestModel::with(['user', 'branch', 'items']);

        if ($user->hasRole('user')) {
            $query->where('user_id', $user->id);
        } elseif ($user->hasRole('admin_1')) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($user->hasRole('admin_2')) {
        }

        if ($request->has('location_type') && in_array($request->location_type, ['dalam_kota', 'luar_kota'])) {
            $query->whereHas('branch', function($q) use ($request) {
                $q->where('location_type', $request->location_type);
            });
        }

        // Filter: Month
        if ($request->has('month') && $request->month) {
            $date = \Carbon\Carbon::createFromFormat('Y-m', $request->month);
            $query->whereMonth('created_at', $date->month)
                  ->whereYear('created_at', $date->year);
        }

        // Filter: Status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('requests.index', compact('requests'));
    }

    public function exportRecap(Request $request)
    {
        $locationType = $request->query('location_type'); 
        $month = $request->query('month');
        
        $filename = 'Recap-Request-' . ($locationType ? ucfirst(str_replace('_', ' ', $locationType)) : 'All');
        if($month) {
            $filename .= '-' . $month;
        } else {
            $filename .= '-' . date('Y-m-d');
        }
        $filename .= '.xlsx';
        
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\RequestRecapExport($locationType, $month), $filename);
    }

    public function create()
    {
        $items = Item::all();
        return view('requests.create', compact('items'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'request_date' => 'required|date',
            'items' => 'required|array',
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.due_date' => 'required|date|after_or_equal:today',
            'items.*.notes' => 'nullable|string',
        ]);

       
        foreach ($data['items'] as $index => &$itemData) {
            $item = Item::where('name', $itemData['item_name'])->first();
            
            if ($item) {
                $itemData['item_id'] = $item->id;
                if ($itemData['quantity'] > $item->stock) {
                    return back()
                        ->withInput()
                        ->withErrors(['items.' . $index . '.quantity' => "Stok tidak cukup untuk {$item->name}. Tersedia: {$item->stock}"]);
                }
            } else {
                $itemData['item_id'] = null; 
            }
        }

        try {
            $requestModel = $this->requestService->createRequest(Auth::user(), $data, $data['items']);
            
            $message = 'Request saved as draft.';
            if ($request->input('save_action') === 'submit') {
                $this->requestService->submitRequest($requestModel);
                $message = 'Request created and submitted successfully.';
            }

            return redirect()->route('requests.index')->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function show(RequestModel $request)
    {
        $request->load(['items.item', 'approvals.approver', 'user', 'branch']);
        return view('requests.show', compact('request'));
    }

    public function submit(RequestModel $request)
    {
        try {
            $this->requestService->submitRequest($request);
            return back()->with('success', 'Request submitted for approval.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function approve(RequestModel $request)
    {
        try {
            $this->requestService->approveRequest($request, Auth::user());
            return back()->with('success', 'Request approved.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(RequestModel $request)
    {
        request()->validate([
            'reason' => 'required|string|max:255'
        ]);

        try {
            $this->requestService->rejectRequest($request, Auth::user(), request('reason'));
            return back()->with('success', 'Request rejected.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function export(RequestModel $request)
    {
        if (!Auth::user()->hasAnyRole(['super_admin', 'admin_1', 'admin_2'])) {
             if ($request->user_id !== Auth::id()) abort(403);
        }

        $request->load(['items.item', 'approvals.approver', 'user', 'branch']);

        if (request('type') == 'excel') {
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\RequestExport($request), 'Request-'.$request->code.'.xlsx');
        }
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.request_pdf', compact('request'));
        return $pdf->download('Request-'.$request->code.'.pdf');
    }

    public function exportPdfList(Request $request)
    {
        $user = Auth::user();
        $query = RequestModel::with(['user', 'branch', 'items.item', 'approvals.approver'])
            ->where('status', 'approved'); 

        if ($user->hasRole('user')) {
            $query->where('user_id', $user->id);
        } elseif ($user->hasRole('admin_1')) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($user->hasRole('admin_2')) {
           
        }
        
        if ($request->has('location_type') && in_array($request->location_type, ['dalam_kota', 'luar_kota'])) {
            $query->whereHas('branch', function($q) use ($request) {
                $q->where('location_type', $request->location_type);
            });
        }
        
        if ($request->has('month') && $request->month) {
            $date = \Carbon\Carbon::createFromFormat('Y-m', $request->month);
            $query->whereMonth('created_at', $date->month)
                  ->whereYear('created_at', $date->year);
        }
        
        $requests = $query->orderBy('created_at', 'desc')->get();

        $requesterUser = null;

        $staffReq = $requests->first(function($r) {
            return $r->user && $r->user->hasRole('user');
        });

        if ($staffReq && $staffReq->user) {
             $requesterUser = $staffReq->user;
        }
        if (!$requesterUser) {
             $branchIds = \App\Models\Branch::query();
             if ($request->has('location_type')) {
                 $branchIds->where('location_type', $request->location_type);
             }
             $branchIds = $branchIds->pluck('id');

             if ($branchIds->isNotEmpty()) {
                 $staffInBranch = \App\Models\User::role('user')
                    ->whereIn('branch_id', $branchIds)
                    ->first();
                 if ($staffInBranch) $requesterUser = $staffInBranch;
             }
        }

        if (!$requesterUser) {
            $anyStaff = \App\Models\User::role('user')->first();
            if ($anyStaff) $requesterUser = $anyStaff;
        }

        if (!$requesterUser) $requesterUser = Auth::user();

        $spvUser = \App\Models\User::role('admin_1')->first(); 
        $kaUser = \App\Models\User::role('admin_2')->first();
        $gaUser = \App\Models\User::role('super_admin')->first();
        
        $firstReq = $requests->first();
        if ($firstReq && $firstReq->branch_id) {
            $branchSpv = \App\Models\User::role('admin_1')->where('branch_id', $firstReq->branch_id)->first();
            if ($branchSpv) $spvUser = $branchSpv;
        }
        
        $filename = 'Daftar-Request-';
        if ($request->has('month') && $request->month) {
             $filename .= $request->month;
        } else {
             $filename .= date('Y-m-d');
        }
        $filename .= '.pdf';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.request_list_pdf', compact('requests', 'requesterUser', 'spvUser', 'kaUser', 'gaUser', 'request'));
        $pdf->setPaper('a4', 'landscape');
        return $pdf->download($filename);
    }

    public function exportExcelList(Request $request)
    {
        $filename = 'Daftar-Request-';
        if ($request->has('month') && $request->month) {
             $filename .= $request->month;
        } else {
             $filename .= date('Y-m-d');
        }
        $filename .= '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\RequestListExport($request), $filename);
    }
    public function edit(RequestModel $request)
    {
        $user = Auth::user();
        if ($user->id !== $request->user_id && !($user->hasRole('admin_1') && $user->branch_id === $request->branch_id)) {
            abort(403, 'Unauthorized');
        }

        if (!in_array($request->status, ['draft', 'pending_spv'])) {
            return back()->with('error', 'Cannot edit request with status: ' . $request->status);
        }

        $request->load(['items']);
        $items = Item::all(); 
        
        return view('requests.edit', compact('request', 'items'));
    }

    public function update(Request $httpRequest, RequestModel $request)
    {
        if (!in_array($request->status, ['draft', 'pending_spv'])) {
             return back()->with('error', 'Cannot edit request with status: ' . $request->status);
        }

        $data = $httpRequest->validate([
            'request_date' => 'required|date',
            'items' => 'required|array',
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.due_date' => 'required|date',
            'items.*.notes' => 'nullable|string',
        ]);

        foreach ($data['items'] as $index => &$itemData) {
            $item = Item::where('name', $itemData['item_name'])->first();
            
            if ($item) {
                $itemData['item_id'] = $item->id;
               
                if ($itemData['quantity'] > $item->stock) {
                     return back()
                        ->withInput()
                        ->withErrors(['items.' . $index . '.quantity' => "Stok tidak cukup untuk {$item->name}. Tersedia: {$item->stock}"]);
                }
            } else {
                $itemData['item_id'] = null;
            }
        }

        try {
            $this->requestService->updateRequest($request, Auth::user(), $data, $data['items']);
            return redirect()->route('requests.show', $request->id)->with('success', 'Request updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(RequestModel $request)
    {
        if (!in_array($request->status, ['draft', 'pending_spv'])) {
             return back()->with('error', 'Cannot delete request with status: ' . $request->status);
        }

        try {
            $this->requestService->deleteRequest($request, Auth::user());
            return redirect()->route('requests.index')->with('success', 'Request deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
