<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('screenshots:prune {days=30 : Number of days to retain screenshots}', function ($days = 30) {
    $days = (int) $days;
    $cutoff = now()->subDays($days);
    $this->info("Pruning screenshots captured before: {$cutoff->toDateTimeString()}");

    $oldScreenshots = \App\Models\AttendanceScreenshot::where('captured_at', '<', $cutoff)->get();
    $deleted = 0;

    foreach ($oldScreenshots as $s) {
        if ($s->screenshot_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($s->screenshot_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($s->screenshot_path);
        }
        $s->delete();
        $deleted++;
    }

    $this->info("Completed. Deleted {$deleted} old screenshot(s).");
})->purpose('Prune attendance tracker screenshots older than 30 days (or specified days)')->daily();

