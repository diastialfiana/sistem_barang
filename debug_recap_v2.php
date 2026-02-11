<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- BRANCHES ---\n";
$branches = \App\Models\Branch::all();
foreach($branches as $b) {
    echo "ID: {$b->id} | Name: {$b->name} | Location: " . ($b->location_type ?? 'N/A') . "\n";
}

echo "\n--- USERS ---\n";
$users = \App\Models\User::with('roles')->get();
foreach($users as $u) {
    $roleName = $u->getRoleNames()->first() ?? 'None';
    echo "ID: {$u->id} | Name: {$u->name} | BranchID: {$u->branch_id} | Role: {$roleName} | Job: {$u->job_title}\n";
}
