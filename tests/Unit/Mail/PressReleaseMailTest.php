<?php

/**
 * PressReleaseMail – Unit Test Suite
 *
 * Tests the Mailable class that wraps a PressRelease for email delivery.
 * Verifies subject line, recipient address, Blade view, and attachments.
 *
 * Pest concepts demonstrated:
 *  • it()                    – each test is a standalone closure.
 *  • describe()              – groups tests; label shows as prefix in output.
 *  • expect()->toContain()   – substring assertion.
 */

use App\Mail\PressReleaseMail;
use App\Models\PressRelease;
use App\Models\User;

// ─── Mail Properties ─────────────────────────────────────────────────────────

describe('Mail Properties', function () {

    // Subject should contain the press release title.
    it('has correct subject', function (): void {
        $pressRelease = PressRelease::factory()->create(['title' => 'Test Press Release']);
        $user = User::factory()->create();

        $mail = new PressReleaseMail($pressRelease, $user);

        expect($mail->envelope()->subject)->toContain('Test Press Release');
    });

    // Envelope's "to" field should match the user's email address.
    it('sends to user email', function (): void {
        $pressRelease = PressRelease::factory()->create();
        $user = User::factory()->create(['email' => 'test@example.com']);

        $mail = new PressReleaseMail($pressRelease, $user);
        $envelope = $mail->envelope();

        expect($envelope->to)->not->toBeEmpty();
        expect($envelope->to)->toHaveCount(1);
        expect($envelope->to[0]->address)->toBe('test@example.com');
    });

    // The mail should render the emails.press-release Blade view.
    it('passes correct data to view', function (): void {
        $pressRelease = PressRelease::factory()->create();
        $user = User::factory()->create();

        $mail = new PressReleaseMail($pressRelease, $user);
        $content = $mail->content();

        expect($content->view)->toBe('emails.press-release');
    });

    // No file attachments expected on this Mailable.
    it('has no attachments', function (): void {
        $pressRelease = PressRelease::factory()->create();
        $user = User::factory()->create();

        $mail = new PressReleaseMail($pressRelease, $user);

        expect($mail->attachments())->toBeEmpty();
    });

});
