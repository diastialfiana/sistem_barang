<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$branch = App\Models\Branch::where('name', 'Jakarta Timur')->first();
if ($branch) {
    echo "Branch ID: {$branch->id}\n";
    $users = App\Models\User::where('branch_id', $branch->id)->get();
    foreach($users as $u) {
        $reqs = App\Models\Request::where('user_id', $u->id)->count();
        $roles = $u->getRoleNames()->join(', ');
        echo "User: {$u->name}, Roles: {$roles}, Requests: {$reqs}\n";
    }
} else {
    echo "Branch not found\n";
}
