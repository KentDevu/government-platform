<?php

namespace App\Jobs;

use App\Models\Announcement;
use App\Models\PressRelease;
use App\Models\RecentLaw;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CleanupOldRecordsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [30, 300];
    public $timeout = 600;

    private const ANNOUNCEMENT_RETENTION_MONTHS = 12;
    private const PRESS_RELEASE_RETENTION_MONTHS = 24;
    private const RECENT_LAW_RETENTION_MONTHS = 36;

    /**
     * Create a new job instance.
     */
    public function __construct() {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $cleanupLog = [
            'announcements_deleted' => 0,
            'press_releases_deleted' => 0,
            'recent_laws_deleted' => 0,
            'timestamp' => now(),
        ];

        \DB::transaction(function () use (&$cleanupLog) {
            // Delete announcements older than ANNOUNCEMENT_RETENTION_MONTHS
            $annThreshold = now()->subMonths(self::ANNOUNCEMENT_RETENTION_MONTHS);
            $cleanupLog['announcements_deleted'] = Announcement::where('created_at', '<', $annThreshold)->delete();

            // Delete press releases older than PRESS_RELEASE_RETENTION_MONTHS
            $prThreshold = now()->subMonths(self::PRESS_RELEASE_RETENTION_MONTHS);
            $cleanupLog['press_releases_deleted'] = PressRelease::where('created_at', '<', $prThreshold)->delete();

            // Delete recent laws older than RECENT_LAW_RETENTION_MONTHS
            $rlThreshold = now()->subMonths(self::RECENT_LAW_RETENTION_MONTHS);
            $cleanupLog['recent_laws_deleted'] = RecentLaw::where('created_at', '<', $rlThreshold)->delete();
        });

        \Log::info('CleanupOldRecordsJob completed', $cleanupLog);
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('CleanupOldRecordsJob failed', [
            'error' => $exception->getMessage(),
        ]);
    }
}
