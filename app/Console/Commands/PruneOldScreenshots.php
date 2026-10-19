<?php

namespace App\Console\Commands;

use App\Models\AttendanceScreenshot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneOldScreenshots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'screenshots:prune {--days=7 : Number of days to keep screenshots}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically delete screenshots older than 7 days (previous week)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $oldScreenshots = AttendanceScreenshot::where('captured_at', '<', $cutoff)->get();
        $count = 0;

        foreach ($oldScreenshots as $screenshot) {
            if ($screenshot->screenshot_path && Storage::disk('public')->exists($screenshot->screenshot_path)) {
                Storage::disk('public')->delete($screenshot->screenshot_path);
            }
            $screenshot->delete();
            $count++;
        }

        $this->info("Successfully pruned {$count} screenshot(s) older than {$days} days.");
        return 0;
    }
}
