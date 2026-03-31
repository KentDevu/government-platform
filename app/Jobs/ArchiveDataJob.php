<?php

namespace App\Jobs;

use App\Models\Announcement;
use App\Models\PressRelease;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ArchiveDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const ARCHIVE_BATCH_SIZE = 500;

    public $tries = 3;
    public $backoff = [60, 300];
    public $timeout = 600;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $model,
        protected int $monthsOld = 12
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $threshold = now()->subMonths($this->monthsOld);
        $archiveLog = [
            'model' => $this->model,
            'months_old' => $this->monthsOld,
            'records_archived' => 0,
            'timestamp' => now(),
        ];

        match ($this->model) {
            'announcements' => $archiveLog['records_archived'] = $this->archiveAnnouncements($threshold),
            'press_releases' => $archiveLog['records_archived'] = $this->archivePressReleases($threshold),
            default => throw new \InvalidArgumentException("Unknown model: {$this->model}"),
        };

        \Log::info('ArchiveDataJob completed', $archiveLog);
    }

    /**
     * Archive announcements older than threshold.
     */
    private function archiveAnnouncements($threshold): int
    {
        return DB::transaction(function () use ($threshold) {
            $count = 0;
            Announcement::where('created_at', '<', $threshold)
                ->whereNull('archived_at')
                ->chunk(self::ARCHIVE_BATCH_SIZE, function ($announcements) use (&$count) {
                    $ids = $announcements->pluck('id')->toArray();
                    Announcement::whereIn('id', $ids)->update(['archived_at' => now()]);
                    $count += count($ids);
                });
            return $count;
        });
    }

    /**
     * Archive press releases older than threshold.
     */
    private function archivePressReleases($threshold): int
    {
        return DB::transaction(function () use ($threshold) {
            $count = 0;
            PressRelease::where('created_at', '<', $threshold)
                ->whereNull('archived_at')
                ->chunk(self::ARCHIVE_BATCH_SIZE, function ($pressReleases) use (&$count) {
                    $ids = $pressReleases->pluck('id')->toArray();
                    PressRelease::whereIn('id', $ids)->update(['archived_at' => now()]);
                    $count += count($ids);
                });
            return $count;
        });
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('ArchiveDataJob failed', [
            'model' => $this->model,
            'error' => $exception->getMessage(),
        ]);
    }
}
