<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$latest = \App\Models\AttendanceScreenshot::latest()->first();
if ($latest) {
    echo "ID: " . $latest->id . "\n";
    echo "User ID: " . $latest->user_id . "\n";
    echo "Path: " . $latest->screenshot_path . "\n";
    echo "Captured At: " . $latest->captured_at . "\n";
    echo "File Exists on Disk: " . (\Illuminate\Support\Facades\Storage::disk('public')->exists($latest->screenshot_path) ? 'YES' : 'NO') . "\n";
    echo "URL: " . $latest->url . "\n";
} else {
    echo "No screenshot found in database.\n";
}
