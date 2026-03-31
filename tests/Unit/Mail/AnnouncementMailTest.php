<?php

namespace Tests\Unit\Mail;

use App\Mail\AnnouncementMail;
use App\Models\Announcement;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AnnouncementMailTest extends TestCase
{
    #[Test]
    public function it_has_correct_subject(): void
    {
        $announcement = Announcement::factory()->create(['title' => 'Test Announcement']);
        $user = User::factory()->create();

        $mail = new AnnouncementMail($announcement, $user);

        $this->assertStringContainsString('Test Announcement', $mail->envelope()->subject);
    }

    #[Test]
    public function it_sends_to_user_email(): void
    {
        $announcement = Announcement::factory()->create();
        $user = User::factory()->create(['email' => 'test@example.com']);

        $mail = new AnnouncementMail($announcement, $user);
        $envelope = $mail->envelope();

        $this->assertNotEmpty($envelope->to);
        $this->assertCount(1, $envelope->to);
        $this->assertEquals('test@example.com', $envelope->to[0]->address);
    }

    #[Test]
    public function it_passes_correct_data_to_view(): void
    {
        $announcement = Announcement::factory()->create();
        $user = User::factory()->create();

        $mail = new AnnouncementMail($announcement, $user);
        $content = $mail->content();

        $this->assertEquals('emails.announcement', $content->view);
    }

    #[Test]
    public function it_has_no_attachments(): void
    {
        $announcement = Announcement::factory()->create();
        $user = User::factory()->create();

        $mail = new AnnouncementMail($announcement, $user);

        $this->assertEmpty($mail->attachments());
    }
}
