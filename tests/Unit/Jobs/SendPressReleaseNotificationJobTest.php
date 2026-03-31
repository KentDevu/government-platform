<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SendPressReleaseNotificationJob;
use App\Mail\PressReleaseMail;
use App\Models\PressRelease;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SendPressReleaseNotificationJobTest extends TestCase
{
    #[Test]
    public function it_sends_notification_to_users_with_email_enabled(): void
    {
        Mail::fake();

        $pressRelease = PressRelease::factory()->create();
        $user1 = User::factory()->create(['email_notifications' => true]);
        $user2 = User::factory()->create(['email_notifications' => true]);
        User::factory()->create(['email_notifications' => false]);

        $job = new SendPressReleaseNotificationJob($pressRelease);
        $job->handle();

        Mail::assertSent(PressReleaseMail::class, 2);
        Mail::assertSent(PressReleaseMail::class, function ($mail) use ($user1) {
            return $mail->envelope()->to[0]->address === $user1->email;
        });
        Mail::assertSent(PressReleaseMail::class, function ($mail) use ($user2) {
            return $mail->envelope()->to[0]->address === $user2->email;
        });
    }

    #[Test]
    public function it_does_not_send_to_users_with_email_disabled(): void
    {
        Mail::fake();

        $pressRelease = PressRelease::factory()->create();
        User::factory()->create(['email_notifications' => false]);
        User::factory()->create(['email_notifications' => false]);

        $job = new SendPressReleaseNotificationJob($pressRelease);
        $job->handle();

        Mail::assertNotSent(PressReleaseMail::class);
    }

    #[Test]
    public function it_handles_empty_user_list(): void
    {
        Mail::fake();

        $pressRelease = PressRelease::factory()->create();

        $job = new SendPressReleaseNotificationJob($pressRelease);
        $job->handle();

        Mail::assertNotSent(PressReleaseMail::class);
    }

    #[Test]
    public function it_serializes_the_model(): void
    {
        $pressRelease = PressRelease::factory()->create();

        $job = new SendPressReleaseNotificationJob($pressRelease);
        $serialized = serialize($job);
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(SendPressReleaseNotificationJob::class, $unserialized);
    }

    #[Test]
    public function it_has_correct_retry_config(): void
    {
        $job = new SendPressReleaseNotificationJob(PressRelease::factory()->create());

        $this->assertEquals(3, $job->tries);
        $this->assertEquals([10, 60, 300], $job->backoff);
        $this->assertEquals(300, $job->timeout);
    }

    #[Test]
    public function it_handles_large_batch_of_users(): void
    {
        Mail::fake();

        $pressRelease = PressRelease::factory()->create();
        User::factory(250)->create(['email_notifications' => true]);

        $job = new SendPressReleaseNotificationJob($pressRelease);
        $job->handle();

        Mail::assertSent(PressReleaseMail::class, 250);
    }

    #[Test]
    public function it_logs_completion_with_counts(): void
    {
        \Log::spy();
        Mail::fake();

        $pressRelease = PressRelease::factory()->create();
        User::factory(3)->create(['email_notifications' => true]);

        $job = new SendPressReleaseNotificationJob($pressRelease);
        $job->handle();

        \Log::shouldHaveReceived('info')
            ->withArgs(function ($message, $context) use ($pressRelease) {
                return $message === 'SendPressReleaseNotificationJob completed'
                    && $context['press_release_id'] === $pressRelease->id
                    && $context['success_count'] === 3
                    && $context['failure_count'] === 0;
            });
    }
}
