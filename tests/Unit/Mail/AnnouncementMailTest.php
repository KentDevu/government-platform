<?php

/**
 * AnnouncementMail – Unit Test Suite
 *
 * Tests the Mailable class that wraps an Announcement for email delivery.
 * Verifies subject line, recipient address, Blade view, and attachments.
 *
 * Pest concepts demonstrated:
 *  • it()                    – each test is a standalone closure.
 *  • describe()              – groups tests; label shows as prefix in output.
 *  • expect()->toContain()   – substring assertion.
 */

use App\Mail\AnnouncementMail;
use App\Models\Announcement;
use App\Models\User;

// ─── Mail Properties ─────────────────────────────────────────────────────────

describe('Mail Properties', function () {

    // Subject should contain the announcement title.
    it('has correct subject', function (): void {
        $announcement = Announcement::factory()->create(['title' => 'Test Announcement']);
        $user = User::factory()->create();

        $mail = new AnnouncementMail($announcement, $user);

        expect($mail->envelope()->subject)->toContain('Test Announcement');
    });

    // Envelope's "to" field should match the user's email address.
    it('sends to user email', function (): void {
        $announcement = Announcement::factory()->create();
        $user = User::factory()->create(['email' => 'test@example.com']);

        $mail = new AnnouncementMail($announcement, $user);
        $envelope = $mail->envelope();

        expect($envelope->to)->not->toBeEmpty();
        expect($envelope->to)->toHaveCount(1);
        expect($envelope->to[0]->address)->toBe('test@example.com');
    });

    // The mail should render the emails.announcement Blade view.
    it('passes correct data to view', function (): void {
        $announcement = Announcement::factory()->create();
        $user = User::factory()->create();

        $mail = new AnnouncementMail($announcement, $user);
        $content = $mail->content();

        expect($content->view)->toBe('emails.announcement');
    });

    // No file attachments expected on this Mailable.
    it('has no attachments', function (): void {
        $announcement = Announcement::factory()->create();
        $user = User::factory()->create();

        $mail = new AnnouncementMail($announcement, $user);

        expect($mail->attachments())->toBeEmpty();
    });

});
