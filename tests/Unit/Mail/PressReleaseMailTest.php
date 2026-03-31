<?php

namespace Tests\Unit\Mail;

use App\Mail\PressReleaseMail;
use App\Models\PressRelease;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PressReleaseMailTest extends TestCase
{
    #[Test]
    public function it_has_correct_subject(): void
    {
        $pressRelease = PressRelease::factory()->create(['title' => 'Test Press Release']);
        $user = User::factory()->create();

        $mail = new PressReleaseMail($pressRelease, $user);

        $this->assertStringContainsString('Test Press Release', $mail->envelope()->subject);
    }

    #[Test]
    public function it_sends_to_user_email(): void
    {
        $pressRelease = PressRelease::factory()->create();
        $user = User::factory()->create(['email' => 'test@example.com']);

        $mail = new PressReleaseMail($pressRelease, $user);
        $envelope = $mail->envelope();

        $this->assertNotEmpty($envelope->to);
        $this->assertCount(1, $envelope->to);
        $this->assertEquals('test@example.com', $envelope->to[0]->address);
    }

    #[Test]
    public function it_passes_correct_data_to_view(): void
    {
        $pressRelease = PressRelease::factory()->create();
        $user = User::factory()->create();

        $mail = new PressReleaseMail($pressRelease, $user);
        $content = $mail->content();

        $this->assertEquals('emails.press-release', $content->view);
    }

    #[Test]
    public function it_has_no_attachments(): void
    {
        $pressRelease = PressRelease::factory()->create();
        $user = User::factory()->create();

        $mail = new PressReleaseMail($pressRelease, $user);

        $this->assertEmpty($mail->attachments());
    }
}
