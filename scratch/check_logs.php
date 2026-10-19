<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$logs = App\Models\Attendance::with(['user', 'project', 'task'])->orderByDesc('id')->take(15)->get();
foreach ($logs as $l) {
    $in = $l->clock_in_at ? $l->clock_in_at->format('Y-m-d H:i:s') : 'N/A';
    $out = $l->clock_out_at ? $l->clock_out_at->format('Y-m-d H:i:s') : 'NULL';
    $diff = ($l->clock_in_at && $l->clock_out_at) ? $l->clock_in_at->diffInSeconds($l->clock_out_at) : 0;
    echo "ID: {$l->id} | User: " . ($l->user->full_name ?? $l->user_id) . " | In: {$in} | Out: {$out} | Diff: {$diff}s | Task: " . ($l->task->title ?? 'N/A') . PHP_EOL;
}
