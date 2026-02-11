<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserRoleLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;

class UserService
{
    public function createUser(array $data, string $creatorRole)
    {
        return DB::transaction(function () use ($data, $creatorRole) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'branch_id' => $data['branch_id'] ?? null,
                'job_title' => $data['job_title'] ?? null,
                'company' => $data['company'] ?? null,
            ]);

            $user->assignRole($data['role']);

            return $user;
        });
    }

    public function updateUser(User $user, array $data, User $updater)
    {
        return DB::transaction(function () use ($user, $data, $updater) {
            
            // 1. Check Role Change
            if (isset($data['role']) && !$user->hasRole($data['role'])) {
                $this->changeRole($user, $data['role'], $updater);
            }

            // 2. Update Basic Info
            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'branch_id' => $data['branch_id'] ?? $user->branch_id,
                'job_title' => $data['job_title'] ?? $user->job_title,
                'company' => $data['company'] ?? $user->company,
            ]);

            // 3. Password (Optional)
            if (!empty($data['password'])) {
                $user->update(['password' => Hash::make($data['password'])]);
            }

            return $user;
        });
    }

    public function changeRole(User $user, string $newRole, User $updater)
    {
        // Validation: Prevent downgrade if active requests exist
        $this->validateRoleTransition($user, $newRole);

        $oldRole = $user->getRoleNames()->first();

        // Audit Log
        UserRoleLog::create([
            'user_id' => $user->id,
            'old_role' => $oldRole,
            'new_role' => $newRole,
            'changed_by' => $updater->id,
            'changed_at' => now(),
        ]);

        // Sync Role
        $user->syncRoles([$newRole]);
    }

    private function validateRoleTransition(User $user, string $newRole)
    {
        // Example Rule: Cannot demote SPV/KA if they have pending approvals
        // (Simplified for now, can be expanded)
        $currentRole = $user->getRoleNames()->first();
        
        if ($currentRole === 'admin_1' && $newRole === 'user') {
            // Check if SPV has pending tasks (optional strict check)
        }
        
        return true; 
    }
}
