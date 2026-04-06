<?php

use App\Mail\PressReleaseMail;
use App\Models\PressRelease;
use App\Models\User;

it('has correct subject', function (): void {
    $pressRelease = PressRelease::factory()->create(['title' => 'Test Press Release']);
    $user = User::factory()->create();

    $mail = new PressReleaseMail($pressRelease, $user);

    expect($mail->envelope()->subject)->toContain('Test Press Release');
});

it('sends to user email', function (): void {
    $pressRelease = PressRelease::factory()->create();
    $user = User::factory()->create(['email' => 'test@example.com']);

    $mail = new PressReleaseMail($pressRelease, $user);
    $envelope = $mail->envelope();

    expect($envelope->to)->not->toBeEmpty();
    expect($envelope->to)->toHaveCount(1);
    expect($envelope->to[0]->address)->toBe('test@example.com');
});

it('passes correct data to view', function (): void {
    $pressRelease = PressRelease::factory()->create();
    $user = User::factory()->create();

    $mail = new PressReleaseMail($pressRelease, $user);
    $content = $mail->content();

    expect($content->view)->toBe('emails.press-release');
});

it('has no attachments', function (): void {
    $pressRelease = PressRelease::factory()->create();
    $user = User::factory()->create();

    $mail = new PressReleaseMail($pressRelease, $user);

    expect($mail->attachments())->toBeEmpty();
});
