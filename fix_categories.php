<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Update Electronics -> Elektronik
$updated = \App\Models\Item::where('category', 'Electronics')->update(['category' => 'Elektronik']);
echo "Updated $updated items from 'Electronics' to 'Elektronik'\n";

// Check other potential variations
$variations = [
    'ELECTRONICS' => 'Elektronik',
    'ELEKTRONIK' => 'Elektronik',
    'elektronik' => 'Elektronik',
    'electronics' => 'Elektronik',
];

foreach ($variations as $from => $to) {
    $count = \App\Models\Item::where('category', $from)->update(['category' => $to]);
    if ($count > 0) {
        echo "Updated $count items from '$from' to '$to'\n";
    }
}

// Show final categories
echo "\n=== Current Categories ===\n";
$categories = \App\Models\Item::select('category', \DB::raw('count(*) as total'))
    ->groupBy('category')
    ->orderBy('category')
    ->get();

foreach ($categories as $cat) {
    echo "{$cat->category}: {$cat->total} items\n";
}
