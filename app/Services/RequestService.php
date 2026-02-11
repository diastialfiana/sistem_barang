<?php

namespace App\Services;

use App\Models\Request;
use App\Models\RequestApproval;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class RequestService
{
    public function createRequest(User $user, array $data, array $items)
    {
        return DB::transaction(function () use ($user, $data, $items) {
            // Generate Code: REQ-YYYYMM-XXXX
            $count = Request::whereYear('created_at', date('Y'))
                ->whereMonth('created_at', date('m'))
                ->count() + 1;
            $code = 'REQ-' . date('Ym') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $request = Request::create([
                'code' => $code,
                'user_id' => $user->id,
                'branch_id' => $user->branch_id, // User must have branch
                'request_date' => $data['request_date'],
                'status' => 'draft',
            ]);

            foreach ($items as $item) {
                $request->items()->create([
                    'item_id' => $item['item_id'],
                    'item_name' => $item['item_name'],
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                    'due_date' => $item['due_date'] ?? null,
                ]);
            }

            return $request;
        });
    }

    public function submitRequest(Request $request)
    {
        if ($request->status !== 'draft' && $request->status !== 'rejected') {
            throw new Exception("Request cannot be submitted from status: {$request->status}");
        }

        $request->update(['status' => 'pending_spv', 'rejection_reason' => null]);
        return $request;
    }

    public function approveRequest(Request $request, User $approver)
    {
        // Validation: Approver Role vs Request Status
        $this->validateApprovalAccess($request, $approver);

        return DB::transaction(function () use ($request, $approver) {
            $currentStatus = $request->status;
            $nextStatus = '';
            $stage = '';

            switch ($currentStatus) {
                case 'pending_spv':
                    $nextStatus = 'pending_ka';
                    $stage = 'spv';
                    break;
                case 'pending_ka':
                    $nextStatus = 'pending_ga';
                    $stage = 'ka';
                    break;
                case 'pending_ga':
                    $nextStatus = 'approved';
                    $stage = 'ga';
                    break;
                default:
                    throw new Exception("Invalid status for approval: $currentStatus");
            }

            // Log Approval
            RequestApproval::create([
                'request_id' => $request->id,
                'approver_id' => $approver->id,
                'stage' => $stage,
                'status' => 'approved',
                'signed_at' => now('Asia/Jakarta'),
            ]);

            // Update Request
            $request->update(['status' => $nextStatus]);

            // Reduce Stock if Final Approval (Status becomes 'approved')
            if ($nextStatus === 'approved') {
                foreach ($request->items as $requestItem) {
                    $item = \App\Models\Item::find($requestItem->item_id);
                    if ($item) {
                        $item->decrement('stock', $requestItem->quantity); // Use decrement for atomicity
                        
                        // Optional: Log stock movement if a StockLog model exists (not requested but good practice, skipping for now to stick to scope)
                    }
                }
            }

            return $request;
        });
    }

    public function rejectRequest(Request $request, User $rejecter, string $reason)
    {
        // Validation: Approver Role vs Request Status
        $this->validateApprovalAccess($request, $rejecter);

        return DB::transaction(function () use ($request, $rejecter, $reason) {
            $currentStatus = $request->status;
            $stage = match ($currentStatus) {
                'pending_spv' => 'spv',
                'pending_ka' => 'ka',
                'pending_ga' => 'ga',
                default => throw new Exception("Invalid status for rejection"),
            };

            // Log Rejection
            RequestApproval::create([
                'request_id' => $request->id,
                'approver_id' => $rejecter->id,
                'stage' => $stage,
                'status' => 'rejected',
                'signed_at' => now('Asia/Jakarta'),
            ]);

            // Update Request
            $request->update([
                'status' => 'rejected',
                'rejection_reason' => $reason
            ]);

            // SEND NOTIFICATIONS
            // 1. Always notify the Requester (Staff)
            \Illuminate\Support\Facades\Notification::send($request->user, new \App\Notifications\RequestStatusChanged($request, $stage, $reason));

            // 2. If rejected by KA, notify SPV (who approved it previously)
            if ($stage === 'ka') {
                 // Find SPV who approved it
                 $spvApproval = RequestApproval::where('request_id', $request->id)
                    ->where('stage', 'spv')
                    ->where('status', 'approved')
                    ->latest()
                    ->first();
                 
                 if ($spvApproval) {
                     $spv = User::find($spvApproval->approver_id);
                     if ($spv) {
                         \Illuminate\Support\Facades\Notification::send($spv, new \App\Notifications\RequestStatusChanged($request, $stage, $reason));
                     }
                 }
            }

            // 3. If rejected by GA, notify KA and SPV
            if ($stage === 'ga') {
                 // Find SPV & KA
                 $approvals = RequestApproval::where('request_id', $request->id)
                    ->where('status', 'approved')
                    ->whereIn('stage', ['spv', 'ka'])
                    ->get();
                 
                 foreach ($approvals as $approval) {
                     $approver = User::find($approval->approver_id);
                     if ($approver) {
                         \Illuminate\Support\Facades\Notification::send($approver, new \App\Notifications\RequestStatusChanged($request, $stage, $reason));
                     }
                 }
            }

            return $request;
        });
    }

    public function updateRequest(Request $request, User $actor, array $data, array $items)
    {
        // Validation: Logic to ensure only Owner or SPV can edit
        // Restricted to pending statuses only.
        if (in_array($request->status, ['approved', 'rejected'])) {
            throw new Exception("Cannot edit request with status: {$request->status}");
        }
        
        // Allow Owner (Staff) or Branch SPV (Admin 1)
        if ($actor->id !== $request->user_id && !($actor->hasRole('admin_1') && $actor->branch_id === $request->branch_id)) {
             throw new Exception("Unauthorized: You do not have permission to edit this request.");
        }

        return DB::transaction(function () use ($request, $data, $items) {
            // 1. Update Request Details
            $request->update([
                'request_date' => $data['request_date'],
                // code, user_id, branch_id usually don't change
            ]);

            // 2. Sync Items (Strategy: Delete User's items and re-create)
            // Note: If we preserve IDs, we need more complex logic. 
            // For this requirements, mostly re-creating is fine as there are no deep relations to items yet (like goods issue).
            $request->items()->delete();

            foreach ($items as $item) {
                $request->items()->create([
                    'item_id' => $item['item_id'],
                    'item_name' => $item['item_name'],
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                    'due_date' => $item['due_date'] ?? null,
                ]);
            }

            return $request;
        });
    }

    public function deleteRequest(Request $request, User $actor)
    {
        // Restricted to pending statuses only.
        if (in_array($request->status, ['approved', 'rejected'])) {
            throw new Exception("Cannot delete request with status: {$request->status}");
        }

        // Allow Owner (Staff) or Branch SPV (Admin 1)
        if ($actor->id !== $request->user_id && !($actor->hasRole('admin_1') && $actor->branch_id === $request->branch_id)) {
             throw new Exception("Unauthorized: You do not have permission to delete this request.");
        }

        return DB::transaction(function () use ($request) {
            // 1. Delete Items
            $request->items()->delete();

            // 2. Delete Approvals
            $request->approvals()->delete();

            // 3. Delete Request
            $request->delete();

            return true;
        });
    }

    private function validateApprovalAccess(Request $request, User $user)
    {
        if ($user->hasRole('admin_1') && $request->status === 'pending_spv') {
            // SPV can only approve own branch
            if ($user->branch_id !== $request->branch_id) {
                throw new Exception("Unauthorized: SPV belongs to different branch.");
            }
            return true;
        }

        if ($user->hasRole('admin_2') && $request->status === 'pending_ka') {
            // KA manages SPV, currently simplified: KA can approve any branch in their area (assuming logic exists)
            // For now, let's assume KA approves any branch or verify via relationship if implemented
            return true;
        }

        if ($user->hasRole('super_admin') && $request->status === 'pending_ga') {
            return true;
        }

        throw new Exception("Unauthorized or Invalid Stage for User Role.");
    }
}
