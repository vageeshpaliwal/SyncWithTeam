<?php

use App\Models\Attendance;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$logs = Attendance::latest()->take(5)->get();
foreach ($logs as $l) {
    echo "ID: {$l->id} | In: {$l->clock_in_at} | Out: {$l->clock_out_at} | Diff: " . ($l->clock_out_at ? $l->clock_in_at->diffInSeconds($l->clock_out_at) : 'Active') . "\n";
}
