<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ArchiveDataJob;
use App\Models\Announcement;
use App\Models\PressRelease;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ArchiveDataJobTest extends TestCase
{
    #[Test]
    public function it_archives_announcements_older_than_threshold(): void
    {
        $oldAnnouncement = Announcement::factory()->create([
            'created_at' => now()->subMonths(13),
            'archived_at' => null,
        ]);
        $recentAnnouncement = Announcement::factory()->create([
            'created_at' => now()->subMonths(6),
            'archived_at' => null,
        ]);

        $job = new ArchiveDataJob('announcements', 12);
        $job->handle();

        $this->assertDatabaseHas('announcements', [
            'id' => $oldAnnouncement->id,
        ]);
        $this->assertNotNull(Announcement::find($oldAnnouncement->id)->archived_at);

        $this->assertDatabaseHas('announcements', [
            'id' => $recentAnnouncement->id,
        ]);
        $this->assertNull(Announcement::find($recentAnnouncement->id)->archived_at);
    }

    #[Test]
    public function it_archives_press_releases_older_than_threshold(): void
    {
        $oldPressRelease = PressRelease::factory()->create([
            'created_at' => now()->subMonths(13),
            'archived_at' => null,
        ]);
        $recentPressRelease = PressRelease::factory()->create([
            'created_at' => now()->subMonths(6),
            'archived_at' => null,
        ]);

        $job = new ArchiveDataJob('press_releases', 12);
        $job->handle();

        $this->assertDatabaseHas('press_releases', [
            'id' => $oldPressRelease->id,
        ]);
        $this->assertNotNull(PressRelease::find($oldPressRelease->id)->archived_at);

        $this->assertDatabaseHas('press_releases', [
            'id' => $recentPressRelease->id,
        ]);
        $this->assertNull(PressRelease::find($recentPressRelease->id)->archived_at);
    }

    #[Test]
    public function it_throws_exception_for_unknown_model(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown model: unknown_model');

        $job = new ArchiveDataJob('unknown_model', 12);
        $job->handle();
    }

    #[Test]
    public function it_has_correct_retry_config(): void
    {
        $job = new ArchiveDataJob('announcements', 12);

        $this->assertEquals(3, $job->tries);
        $this->assertEquals([60, 300], $job->backoff);
        $this->assertEquals(600, $job->timeout);
    }

    #[Test]
    public function it_archives_with_custom_months_threshold(): void
    {
        $oldAnnouncement = Announcement::factory()->create([
            'created_at' => now()->subMonths(7),
            'archived_at' => null,
        ]);
        $recentAnnouncement = Announcement::factory()->create([
            'created_at' => now()->subMonths(3),
            'archived_at' => null,
        ]);

        $job = new ArchiveDataJob('announcements', 6);
        $job->handle();

        $this->assertNotNull(Announcement::find($oldAnnouncement->id)->archived_at);
        $this->assertNull(Announcement::find($recentAnnouncement->id)->archived_at);
    }

    #[Test]
    public function it_handles_large_batch_via_chunking(): void
    {
        \Log::spy();

        // Create 250 announcements to test chunking (well under 500 chunk size)
        Announcement::factory(250)->create(['created_at' => now()->subMonths(13), 'archived_at' => null]);

        $job = new ArchiveDataJob('announcements', 12);
        $job->handle();

        // Verify all were archived
        $this->assertDatabaseCount('announcements', 250);
        $this->assertEquals(250, Announcement::whereNotNull('archived_at')->count());

        \Log::shouldHaveReceived('info')
            ->withArgs(function ($message, $context) {
                return $message === 'ArchiveDataJob completed'
                    && $context['records_archived'] === 250;
            });
    }

    #[Test]
    public function it_does_not_re_archive_already_archived_records(): void
    {
        $oldArchivedAnnouncement = Announcement::factory()->create([
            'created_at' => now()->subMonths(13),
            'archived_at' => now()->subMonths(6),
        ]);

        $job = new ArchiveDataJob('announcements', 12);
        $job->handle();

        // Should not update the archived_at timestamp
        $this->assertEquals($oldArchivedAnnouncement->archived_at, Announcement::find($oldArchivedAnnouncement->id)->archived_at);
    }

    #[Test]
    public function it_returns_zero_when_no_records_to_archive(): void
    {
        \Log::spy();

        // Create only recent announcements
        Announcement::factory(5)->create(['created_at' => now()->subMonths(3), 'archived_at' => null]);

        $job = new ArchiveDataJob('announcements', 12);
        $job->handle();

        \Log::shouldHaveReceived('info')
            ->withArgs(function ($message, $context) {
                return $message === 'ArchiveDataJob completed'
                    && $context['records_archived'] === 0;
            });
    }
}
