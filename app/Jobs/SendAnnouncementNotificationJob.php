<?php

namespace App\Jobs;

use App\Mail\AnnouncementMail;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendAnnouncementNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const NOTIFICATION_BATCH_SIZE = 100;

    public $tries = 3;
    /** @var array Retry delays: 10s, 1m, 5m */
    public $backoff = [10, 60, 300];
    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Announcement $announcement
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $successCount = 0;
        $failureCount = 0;

        User::where('email_notifications', true)
            ->chunk(self::NOTIFICATION_BATCH_SIZE, function ($users) use (&$successCount, &$failureCount) {
                foreach ($users as $user) {
                    try {
                        Mail::to($user->email)->send(new AnnouncementMail($this->announcement, $user));
                        $successCount++;
                    } catch (\Exception $e) {
                        $failureCount++;
                        \Log::warning('Failed to queue announcement notification', [
                            'user_id' => $user->id,
                            'user_email' => $user->email,
                            'announcement_id' => $this->announcement->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

        \Log::info('SendAnnouncementNotificationJob completed', [
            'announcement_id' => $this->announcement->id,
            'success_count' => $successCount,
            'failure_count' => $failureCount,
        ]);
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('SendAnnouncementNotificationJob failed', [
            'announcement_id' => $this->announcement->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
