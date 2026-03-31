<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SendAnnouncementNotificationJob;
use App\Mail\AnnouncementMail;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SendAnnouncementNotificationJobTest extends TestCase
{
    #[Test]
    public function it_sends_notification_to_users_with_email_enabled(): void
    {
        Mail::fake();

        $announcement = Announcement::factory()->create();
        $user1 = User::factory()->create(['email_notifications' => true]);
        $user2 = User::factory()->create(['email_notifications' => true]);
        User::factory()->create(['email_notifications' => false]);

        $job = new SendAnnouncementNotificationJob($announcement);
        $job->handle();

        Mail::assertSent(AnnouncementMail::class, 2);
        Mail::assertSent(AnnouncementMail::class, function ($mail) use ($user1) {
            return $mail->envelope()->to[0]->address === $user1->email;
        });
        Mail::assertSent(AnnouncementMail::class, function ($mail) use ($user2) {
            return $mail->envelope()->to[0]->address === $user2->email;
        });
    }

    #[Test]
    public function it_does_not_send_to_users_with_email_disabled(): void
    {
        Mail::fake();

        $announcement = Announcement::factory()->create();
        User::factory()->create(['email_notifications' => false]);
        User::factory()->create(['email_notifications' => false]);

        $job = new SendAnnouncementNotificationJob($announcement);
        $job->handle();

        Mail::assertNotSent(AnnouncementMail::class);
    }

    #[Test]
    public function it_handles_empty_user_list(): void
    {
        Mail::fake();

        $announcement = Announcement::factory()->create();

        $job = new SendAnnouncementNotificationJob($announcement);
        $job->handle();

        Mail::assertNotSent(AnnouncementMail::class);
    }

    #[Test]
    public function it_serializes_the_model(): void
    {
        $announcement = Announcement::factory()->create();

        $job = new SendAnnouncementNotificationJob($announcement);
        $serialized = serialize($job);
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(SendAnnouncementNotificationJob::class, $unserialized);
    }

    #[Test]
    public function it_has_correct_retry_config(): void
    {
        $job = new SendAnnouncementNotificationJob(Announcement::factory()->create());

        $this->assertEquals(3, $job->tries);
        $this->assertEquals([10, 60, 300], $job->backoff);
        $this->assertEquals(300, $job->timeout);
    }

    #[Test]
    public function it_handles_large_batch_of_users(): void
    {
        Mail::fake();

        $announcement = Announcement::factory()->create();
        User::factory(250)->create(['email_notifications' => true]);

        $job = new SendAnnouncementNotificationJob($announcement);
        $job->handle();

        Mail::assertSent(AnnouncementMail::class, 250);
    }

    #[Test]
    public function it_logs_completion_with_counts(): void
    {
        \Log::spy();
        Mail::fake();

        $announcement = Announcement::factory()->create();
        User::factory(3)->create(['email_notifications' => true]);

        $job = new SendAnnouncementNotificationJob($announcement);
        $job->handle();

        \Log::shouldHaveReceived('info')
            ->withArgs(function ($message, $context) use ($announcement) {
                return $message === 'SendAnnouncementNotificationJob completed'
                    && $context['announcement_id'] === $announcement->id
                    && $context['success_count'] === 3
                    && $context['failure_count'] === 0;
            });
    }
}
