<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class NipSeeder extends Seeder
{
    public function run()
    {
        // 1. Super Admin (Global Access)
        $admin = User::role('super_admin')->first();
        if (!$admin) {
            // Fallback if no super_admin exists
            $admin = User::first();
        }

        if ($admin) {
            $admin->update([
                'nip' => '123456',
                'password' => Hash::make('password')
            ]);
            $this->command->info("LOGINDETAIL: Admin (Super) | NIP: 123456 | Password: password");
        }

        // 2. Other Users
        $others = User::where('id', '!=', $admin->id ?? 0)->get();
        foreach($others as $user) {
            $nip = '8888' . str_pad($user->id, 3, '0', STR_PAD_LEFT);
            $user->update([
                'nip' => $nip,
                'password' => Hash::make('password')
            ]);
            $role = $user->getRoleNames()->first() ?? 'No Role';
            $this->command->info("LOGINDETAIL: {$user->name} ({$role}) | NIP: {$nip} | Password: password");
        }
    }
}
