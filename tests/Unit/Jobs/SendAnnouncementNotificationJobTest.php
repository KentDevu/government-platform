<?php

use App\Jobs\SendAnnouncementNotificationJob;
use App\Mail\AnnouncementMail;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

it('sends notification to users with email enabled', function (): void {
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
});

it('does not send to users with email disabled', function (): void {
    Mail::fake();

    $announcement = Announcement::factory()->create();
    User::factory()->create(['email_notifications' => false]);
    User::factory()->create(['email_notifications' => false]);

    $job = new SendAnnouncementNotificationJob($announcement);
    $job->handle();

    Mail::assertNotSent(AnnouncementMail::class);
});

it('handles empty user list', function (): void {
    Mail::fake();

    $announcement = Announcement::factory()->create();

    $job = new SendAnnouncementNotificationJob($announcement);
    $job->handle();

    Mail::assertNotSent(AnnouncementMail::class);
});

it('serializes the model', function (): void {
    $announcement = Announcement::factory()->create();

    $job = new SendAnnouncementNotificationJob($announcement);
    $serialized = serialize($job);
    $unserialized = unserialize($serialized);

    expect($unserialized)->toBeInstanceOf(SendAnnouncementNotificationJob::class);
});

it('has correct retry config', function (): void {
    $job = new SendAnnouncementNotificationJob(Announcement::factory()->create());

    expect($job->tries)->toBe(3);
    expect($job->backoff)->toBe([10, 60, 300]);
    expect($job->timeout)->toBe(300);
});

it('handles large batch of users', function (): void {
    Mail::fake();

    $announcement = Announcement::factory()->create();
    User::factory(250)->create(['email_notifications' => true]);

    $job = new SendAnnouncementNotificationJob($announcement);
    $job->handle();

    Mail::assertSent(AnnouncementMail::class, 250);
});

it('logs completion with counts', function (): void {
    Log::spy();
    Mail::fake();

    $announcement = Announcement::factory()->create();
    User::factory(3)->create(['email_notifications' => true]);

    $job = new SendAnnouncementNotificationJob($announcement);
    $job->handle();

    Log::shouldHaveReceived('info')
        ->withArgs(function ($message, $context) use ($announcement) {
            return $message === 'SendAnnouncementNotificationJob completed'
                && $context['announcement_id'] === $announcement->id
                && $context['success_count'] === 3
                && $context['failure_count'] === 0;
        });
});
