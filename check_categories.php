<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$items = \App\Models\Item::select('id', 'name', 'category')->whereIn('category', ['Elektronik', 'ELEKTRONIK', 'Electronics', 'ELECTRONICS'])->orWhereNull('category')->get();

foreach ($items as $item) {
    echo "ID: {$item->id}, Name: {$item->name}, Category: '{$item->category}'\n";
}
