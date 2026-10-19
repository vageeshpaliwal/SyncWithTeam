<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$logs = App\Models\Attendance::whereDate('created_at', '2026-10-02')
    ->orWhereDate('date', '2026-10-02')
    ->orWhereDate('clock_in_at', '2026-10-02')
    ->get();

foreach ($logs as $l) {
    $taskTitle = $l->task?->title ?? 'N/A';
    echo "ID: {$l->id} | User ID: {$l->user_id} | In: {$l->clock_in_at} | Out: {$l->clock_out_at} | Task: {$taskTitle}" . PHP_EOL;
}
