<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        // Join with roles table to sort by role name
        $query = User::with(['roles', 'branch'])
            ->select('users.*') // Select user fields to avoid collisions
            ->leftJoin('model_has_roles', function($join) {
                $join->on('users.id', '=', 'model_has_roles.model_id')
                     ->where('model_has_roles.model_type', '=', User::class);
            })
            ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->orderByRaw("FIELD(roles.name, 'super_admin', 'admin_1', 'admin_2', 'user') ASC")
            ->orderBy('created_at', 'desc'); // Secondary sort by newest

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $users = $query->paginate(10);
        $roles = Role::all();
        $branches = Branch::all();

        return view('users.index', compact('users', 'roles', 'branches'));
    }

    public function create()
    {
        $roles = Role::all();
        $branches = Branch::all();
        return view('users.create', compact('roles', 'branches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'required|string|unique:users,nip',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'nullable|string|min:8', // Made nullable
            'role' => 'required|exists:roles,name',
            'branch_id' => 'nullable|exists:branches,id',
            'new_branch_name' => 'nullable|string', // Removed unique validation to allow flexible input
            'job_title' => 'nullable|string',
            'company' => 'nullable|string|max:255',
        ]);

        try {
            // Handle Branch Logic: Create or Find
            if ($request->filled('new_branch_name')) {
                // Use firstOrCreate to avoid duplicates and handle existing names gracefully
                $branch = \App\Models\Branch::firstOrCreate(
                    ['name' => trim($data['new_branch_name'])],
                    ['location_type' => 'dalam_kota'] // Default if creating new
                );
                $data['branch_id'] = $branch->id;
            }

            // Assign a default email if not provided, using NIP
            if (empty($data['email'])) {
                $data['email'] = $data['nip'] . '@internal.system';
            }

            // Default password to NIP if empty
            if (empty($data['password'])) {
                $data['password'] = $data['nip'];
            }

            $this->userService->createUser($data, 'super_admin');
            return redirect()->route('users.index')->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function resetPassword(User $user)
    {
        try {
            // Reset password to NIP
            $user->update([
                'password' => \Illuminate\Support\Facades\Hash::make($user->nip)
            ]);
            
            return back()->with('success', 'Password reset to NIP successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to reset password: ' . $e->getMessage());
        }
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $branches = Branch::all();
        return view('users.edit', compact('user', 'roles', 'branches'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'required|string|unique:users,nip,' . $user->id,
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'role' => 'required|exists:roles,name',
            'branch_id' => 'nullable|exists:branches,id',
            'job_title' => 'nullable|string',
            'company' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8',
        ]);

        try {
            $this->userService->updateUser($user, $data, auth()->user());
            return redirect()->route('users.index')->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(User $user)
    {
        // Prevent deleting self or Super Admin
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot delete yourself.');
        }
        
        $user->delete();
        return back()->with('success', 'User deleted successfully.');
    }
}
