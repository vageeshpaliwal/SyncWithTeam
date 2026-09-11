<?php

use App\Models\Attendance;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Truncate test attendance data with mismatched UTC/IST timestamps
Attendance::truncate();

echo "All legacy test attendance logs cleared successfully.\n";
