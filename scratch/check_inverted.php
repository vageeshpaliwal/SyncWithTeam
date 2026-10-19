<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$corrupt = App\Models\Attendance::whereNotNull('clock_out_at')
    ->get()
    ->filter(function($l) {
        return $l->clock_out_at < $l->clock_in_at;
    });

echo "Total inverted records found: " . $corrupt->count() . PHP_EOL;
foreach ($corrupt as $c) {
    echo "ID: {$c->id} | User ID: {$c->user_id} | In: {$c->clock_in_at} | Out: {$c->clock_out_at}" . PHP_EOL;
}
