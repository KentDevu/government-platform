<?php

use App\Jobs\SendPressReleaseNotificationJob;
use App\Mail\PressReleaseMail;
use App\Models\PressRelease;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

it('sends notification to users with email enabled', function (): void {
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
});

it('does not send to users with email disabled', function (): void {
    Mail::fake();

    $pressRelease = PressRelease::factory()->create();
    User::factory()->create(['email_notifications' => false]);
    User::factory()->create(['email_notifications' => false]);

    $job = new SendPressReleaseNotificationJob($pressRelease);
    $job->handle();

    Mail::assertNotSent(PressReleaseMail::class);
});

it('handles empty user list', function (): void {
    Mail::fake();

    $pressRelease = PressRelease::factory()->create();

    $job = new SendPressReleaseNotificationJob($pressRelease);
    $job->handle();

    Mail::assertNotSent(PressReleaseMail::class);
});

it('serializes the model', function (): void {
    $pressRelease = PressRelease::factory()->create();

    $job = new SendPressReleaseNotificationJob($pressRelease);
    $serialized = serialize($job);
    $unserialized = unserialize($serialized);

    expect($unserialized)->toBeInstanceOf(SendPressReleaseNotificationJob::class);
});

it('has correct retry config', function (): void {
    $job = new SendPressReleaseNotificationJob(PressRelease::factory()->create());

    expect($job->tries)->toBe(3);
    expect($job->backoff)->toBe([10, 60, 300]);
    expect($job->timeout)->toBe(300);
});

it('handles large batch of users', function (): void {
    Mail::fake();

    $pressRelease = PressRelease::factory()->create();
    User::factory(250)->create(['email_notifications' => true]);

    $job = new SendPressReleaseNotificationJob($pressRelease);
    $job->handle();

    Mail::assertSent(PressReleaseMail::class, 250);
});

it('logs completion with counts', function (): void {
    Log::spy();
    Mail::fake();

    $pressRelease = PressRelease::factory()->create();
    User::factory(3)->create(['email_notifications' => true]);

    $job = new SendPressReleaseNotificationJob($pressRelease);
    $job->handle();

    Log::shouldHaveReceived('info')
        ->withArgs(function ($message, $context) use ($pressRelease) {
            return $message === 'SendPressReleaseNotificationJob completed'
                && $context['press_release_id'] === $pressRelease->id
                && $context['success_count'] === 3
                && $context['failure_count'] === 0;
        });
});
