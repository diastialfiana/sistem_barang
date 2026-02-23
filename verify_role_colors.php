<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

// Create dummy users with different roles if they don't exist logic can't be easily done without database modification which we should avoid for verification script if possible. 
// Instead let's just mock or use existing users if possible, or even better, just instantiate a User model and mock the relation if possible, or just check the logic unit-test style.
// Since we are in an artisan tinker environment (or can be), we can fetch existing users.

$roles = ['super_admin', 'admin_1', 'admin_2', 'user'];

foreach ($roles as $roleName) {
    echo "Checking role: $roleName\n";
    // Find a user with this role
    $user = User::role($roleName)->first();
    
    if ($user) {
        echo "Found user with role $roleName: {$user->name}\n";
        echo "Color class: {$user->role_color}\n";
    } else {
        echo "No user found with role $roleName. Creating a temporary in-memory user to test match logic if possible or just skipping.\n";
        // We can't easily add roles to non-saved users with spatie/laravel-permission usually without db persistence.
        // Let's try to mock it manually if needed, but for now let's rely on existing data.
    }
    echo "-------------------\n";
}
