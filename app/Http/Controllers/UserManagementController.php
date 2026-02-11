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
        $query = User::with(['roles', 'branch']);

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('nip', 'like', '%' . $request->search . '%');
            });
        }

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
            'password' => 'required|string|min:8',
            'role' => 'required|exists:roles,name',
            'branch_id' => 'nullable|exists:branches,id',
            'job_title' => 'nullable|string',
            'company' => 'nullable|string|max:255',
        ]);

        try {
            // Assign a default email if not provided, using NIP
            if (empty($data['email'])) {
                $data['email'] = $data['nip'] . '@internal.system';
            }

            $this->userService->createUser($data, 'super_admin');
            return redirect()->route('users.index')->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
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
