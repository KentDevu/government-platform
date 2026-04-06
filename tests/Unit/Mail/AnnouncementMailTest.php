<?php

use App\Mail\AnnouncementMail;
use App\Models\Announcement;
use App\Models\User;

it('has correct subject', function (): void {
    $announcement = Announcement::factory()->create(['title' => 'Test Announcement']);
    $user = User::factory()->create();

    $mail = new AnnouncementMail($announcement, $user);

    expect($mail->envelope()->subject)->toContain('Test Announcement');
});

it('sends to user email', function (): void {
    $announcement = Announcement::factory()->create();
    $user = User::factory()->create(['email' => 'test@example.com']);

    $mail = new AnnouncementMail($announcement, $user);
    $envelope = $mail->envelope();

    expect($envelope->to)->not->toBeEmpty();
    expect($envelope->to)->toHaveCount(1);
    expect($envelope->to[0]->address)->toBe('test@example.com');
});

it('passes correct data to view', function (): void {
    $announcement = Announcement::factory()->create();
    $user = User::factory()->create();

    $mail = new AnnouncementMail($announcement, $user);
    $content = $mail->content();

    expect($content->view)->toBe('emails.announcement');
});

it('has no attachments', function (): void {
    $announcement = Announcement::factory()->create();
    $user = User::factory()->create();

    $mail = new AnnouncementMail($announcement, $user);

    expect($mail->attachments())->toBeEmpty();
});
