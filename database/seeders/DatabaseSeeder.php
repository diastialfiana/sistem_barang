<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Roles & Permissions
        $roles = [
            'user' => ['create_requests', 'view_own_requests'],
            'admin_1' => ['view_branch_requests', 'approve_spv'],
            'admin_2' => ['view_area_requests', 'approve_ka'],
            'super_admin' => ['view_all_requests', 'approve_ga', 'manage_master_data', 'export_reports'],
        ];

        foreach ($roles as $roleName => $perms) {
            $role = \Spatie\Permission\Models\Role::create(['name' => $roleName]);
            foreach ($perms as $perm) {
                $p = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $perm]);
                $role->givePermissionTo($p);
            }
        }

        // 2. Branches
        $branches = ['Jakarta Pusat', 'Bandung', 'Surabaya', 'Medan'];
        $branchObjs = [];
        foreach ($branches as $b) {
            $branchObjs[$b] = \App\Models\Branch::create([
                'name' => $b,
                'address' => 'Jl. ' . $b . ' No. 1'
            ]);
        }

        // 3. Items
        \App\Models\Item::create(['name' => 'Kertas A4', 'unit' => 'Rim', 'stock' => 100]);
        \App\Models\Item::create(['name' => 'Pulpen Hitam', 'unit' => 'Box', 'stock' => 50]);
        \App\Models\Item::create(['name' => 'Laptop', 'unit' => 'Unit', 'stock' => 10]);

        // 4. Users
        // Super Admin
        $ga = \App\Models\User::create([
            'name' => 'Procurement/General Affair',
            'email' => 'ga@example.com',
            'password' => bcrypt('password'),
            'job_title' => 'Procurement',
        ]);
        $ga->assignRole('super_admin');

        // Admin 2 (KA) - Manages SPVs
        $ka = \App\Models\User::create([
            'name' => 'Kepala Area 1',
            'email' => 'ka@example.com',
            'password' => bcrypt('password'),
            'job_title' => 'Kepala Area',
        ]);
        $ka->assignRole('admin_2');

        // Admin 1 (SPV) - Jakarta
        $spv = \App\Models\User::create([
            'name' => 'SPV Jakarta',
            'email' => 'spv@example.com',
            'password' => bcrypt('password'),
            'branch_id' => $branchObjs['Jakarta Pusat']->id,
            'job_title' => 'Supervisor',
        ]);
        $spv->assignRole('admin_1');

        // User (Staff) - Jakarta
        $staff = \App\Models\User::create([
            'name' => 'Staff Jakarta',
            'email' => 'staff@example.com',
            'password' => bcrypt('password'),
            'branch_id' => $branchObjs['Jakarta Pusat']->id,
            'job_title' => 'Staff Logistik',
        ]);
        $staff->assignRole('user');

        // 5. Generate NIPs
        $this->call(NipSeeder::class);

        // 6. Demo Data
        $this->call(DemoSeeder::class);
    }
}
