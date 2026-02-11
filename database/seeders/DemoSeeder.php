<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Item;
use App\Models\Request;
use App\Models\RequestItem;
use App\Models\RequestApproval;
use Carbon\Carbon;

class DemoSeeder extends Seeder
{
    public function run()
    {
        // 1. Create more Items
        $items = [
            ['name' => 'Kertas A4', 'unit' => 'Rim', 'stock' => 100, 'category' => 'Stationery'],
            ['name' => 'Pulpen Hitam', 'unit' => 'Box', 'stock' => 50, 'category' => 'Stationery'],
            ['name' => 'Pulpen Biru', 'unit' => 'Box', 'stock' => 50, 'category' => 'Stationery'],
            ['name' => 'Spidol Board', 'unit' => 'Pcs', 'stock' => 20, 'category' => 'Stationery'],
            ['name' => 'Buku Tulis', 'unit' => 'Pcs', 'stock' => 200, 'category' => 'Stationery'],
            ['name' => 'Lakban Bening', 'unit' => 'Roll', 'stock' => 30, 'category' => 'Stationery'],
            
            ['name' => 'Laptop Dell', 'unit' => 'Unit', 'stock' => 5, 'category' => 'Electronics'],
            ['name' => 'Mouse Wireless', 'unit' => 'Unit', 'stock' => 15, 'category' => 'Electronics'],
            ['name' => 'Keyboard Mechanical', 'unit' => 'Unit', 'stock' => 10, 'category' => 'Electronics'],
            ['name' => 'Monitor 24"', 'unit' => 'Unit', 'stock' => 8, 'category' => 'Electronics'],
            ['name' => 'Kabel HDMI', 'unit' => 'Pcs', 'stock' => 25, 'category' => 'Electronics'],

            ['name' => 'Kursi Kantor', 'unit' => 'Unit', 'stock' => 10, 'category' => 'Furniture'],
            ['name' => 'Meja Kerja', 'unit' => 'Unit', 'stock' => 5, 'category' => 'Furniture'],
            ['name' => 'Lemari Arsip', 'unit' => 'Unit', 'stock' => 3, 'category' => 'Furniture'],
            
            ['name' => 'Sabun Cuci Tangan', 'unit' => 'Botol', 'stock' => 40, 'category' => 'Cleaning'],
            ['name' => 'Tisu Wajah', 'unit' => 'Box', 'stock' => 100, 'category' => 'Cleaning'],
        ];

        foreach ($items as $item) {
            Item::firstOrCreate(['name' => $item['name']], $item);
        }

        // Get Users
        $staff = User::whereHas('roles', function($q) { $q->where('name', 'user'); })->first();
        $spv = User::whereHas('roles', function($q) { $q->where('name', 'admin_1'); })->first();
        $ka = User::whereHas('roles', function($q) { $q->where('name', 'admin_2'); })->first();
        $ga = User::whereHas('roles', function($q) { $q->where('name', 'super_admin'); })->first();

        if (!$staff) return;

        // 2. Create Requests
        $statuses = [
            'pending_spv' => 5,
            'pending_ka' => 5,
            'pending_ga' => 5,
            'approved' => 3,
            'rejected' => 2
        ];

        $allItems = Item::all();

        foreach ($statuses as $status => $count) {
            for ($i = 0; $i < $count; $i++) {
                $date = Carbon::now()->subDays(rand(0, 30));
                
                $req = Request::create([
                    'code' => 'REQ-' . date('Ymd') . '-' . rand(1000, 9999),
                    'user_id' => $staff->id,
                    'branch_id' => $staff->branch_id,
                    'request_date' => $date,
                    // Use the status key directly as it matches the DB expectation
                    'status' => $status, 
                    'rejection_reason' => $status == 'rejected' ? 'Stok tidak tersedia' : null,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                // Determine step based on logic status
                $currentStep = 1; // Default pending_spv
                if ($status == 'pending_ka') $currentStep = 2;
                if ($status == 'pending_ga') $currentStep = 3;
                if ($status == 'approved') $currentStep = 4;
                if ($status == 'rejected') $currentStep = rand(1, 3); // Rejected at any step

                // Add Items
                $reqItems = $allItems->random(rand(2, 5));
                foreach ($reqItems as $item) {
                    RequestItem::create([
                        'request_id' => $req->id,
                        'item_id' => $item->id,
                        'item_name' => $item->name,
                        'quantity' => rand(1, 5),
                        'notes' => 'Kebutuhan demo',
                        'due_date' => Carbon::now()->addDays(rand(7, 14)),
                    ]);
                }

                // Create Approvals History
                // Schema: id, request_id, approver_id, stage, status, signed_at, timestamps

                // 1. SPV Approval
                if ($currentStep > 1 || ($status == 'rejected' && $currentStep == 1)) {
                    RequestApproval::create([
                        'request_id' => $req->id,
                        'approver_id' => $spv->id, // SPV
                        'stage' => 'spv',
                        'status' => ($status == 'rejected' && $currentStep == 1) ? 'rejected' : 'approved',
                        'signed_at' => $date->copy()->addHours(1),
                        'created_at' => $date->copy()->addHours(1),
                    ]);
                }

                // 2. KA Approval
                if ($currentStep > 2 || ($status == 'rejected' && $currentStep == 2)) {
                    RequestApproval::create([
                        'request_id' => $req->id,
                        'approver_id' => $ka->id, // KA
                        'stage' => 'ka',
                        'status' => ($status == 'rejected' && $currentStep == 2) ? 'rejected' : 'approved',
                        'signed_at' => $date->copy()->addHours(2),
                        'created_at' => $date->copy()->addHours(2),
                    ]);
                }

                // 3. GA Approval
                if ($currentStep > 3 || ($status == 'rejected' && $currentStep == 3)) {
                    RequestApproval::create([
                        'request_id' => $req->id,
                        'approver_id' => $ga->id, // GA
                        'stage' => 'ga',
                        'status' => ($status == 'rejected' && $currentStep == 3) ? 'rejected' : 'approved',
                        'signed_at' => $date->copy()->addHours(3),
                        'created_at' => $date->copy()->addHours(3),
                    ]);
                }
            }
        }
    }
}
