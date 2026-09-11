<?php

use App\Models\Attendance;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$logs = Attendance::whereNotNull('clock_out_at')->get();
foreach ($logs as $l) {
    if ($l->clock_out_at < $l->clock_in_at) {
        // Fix inverted clock_out_at timestamp saved during UTC transition
        $l->update(['clock_out_at' => $l->clock_in_at]);
        echo "Fixed Attendance ID {$l->id}\n";
    }
}
echo "Cleaned up corrupt timestamps.\n";
