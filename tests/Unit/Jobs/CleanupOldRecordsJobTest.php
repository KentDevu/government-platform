<?php

namespace Tests\Unit\Jobs;

use App\Jobs\CleanupOldRecordsJob;
use App\Models\Announcement;
use App\Models\PressRelease;
use App\Models\RecentLaw;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CleanupOldRecordsJobTest extends TestCase
{
    #[Test]
    public function it_deletes_announcements_older_than_12_months(): void
    {
        $oldAnnouncement = Announcement::factory()->create([
            'created_at' => now()->subMonths(13),
        ]);
        $recentAnnouncement = Announcement::factory()->create([
            'created_at' => now()->subMonths(6),
        ]);

        $job = new CleanupOldRecordsJob();
        $job->handle();

        $this->assertDatabaseMissing('announcements', ['id' => $oldAnnouncement->id]);
        $this->assertDatabaseHas('announcements', ['id' => $recentAnnouncement->id]);
    }

    #[Test]
    public function it_deletes_press_releases_older_than_24_months(): void
    {
        $oldPressRelease = PressRelease::factory()->create([
            'created_at' => now()->subMonths(25),
        ]);
        $recentPressRelease = PressRelease::factory()->create([
            'created_at' => now()->subMonths(12),
        ]);

        $job = new CleanupOldRecordsJob();
        $job->handle();

        $this->assertDatabaseMissing('press_releases', ['id' => $oldPressRelease->id]);
        $this->assertDatabaseHas('press_releases', ['id' => $recentPressRelease->id]);
    }

    #[Test]
    public function it_deletes_recent_laws_older_than_36_months(): void
    {
        $oldRecentLaw = RecentLaw::factory()->create([
            'created_at' => now()->subMonths(37),
        ]);
        $recentRecentLaw = RecentLaw::factory()->create([
            'created_at' => now()->subMonths(24),
        ]);

        $job = new CleanupOldRecordsJob();
        $job->handle();

        $this->assertDatabaseMissing('recent_laws', ['id' => $oldRecentLaw->id]);
        $this->assertDatabaseHas('recent_laws', ['id' => $recentRecentLaw->id]);
    }

    #[Test]
    public function it_handles_empty_database(): void
    {
        \Log::spy();

        $job = new CleanupOldRecordsJob();
        $job->handle();

        $this->assertDatabaseCount('announcements', 0);
        $this->assertDatabaseCount('press_releases', 0);
        $this->assertDatabaseCount('recent_laws', 0);

        \Log::shouldHaveReceived('info')
            ->withArgs(function ($message, $context) {
                return $message === 'CleanupOldRecordsJob completed'
                    && $context['announcements_deleted'] === 0
                    && $context['press_releases_deleted'] === 0
                    && $context['recent_laws_deleted'] === 0;
            });
    }

    #[Test]
    public function it_does_not_delete_records_at_exact_threshold(): void
    {
        $atThreshold = Announcement::factory()->create([
            'created_at' => now()->subMonths(12),
        ]);

        $job = new CleanupOldRecordsJob();
        $job->handle();

        $this->assertDatabaseHas('announcements', ['id' => $atThreshold->id]);
    }

    #[Test]
    public function it_returns_correct_counts(): void
    {
        \Log::spy();

        // Create multiple old records
        Announcement::factory(5)->create(['created_at' => now()->subMonths(13)]);
        PressRelease::factory(3)->create(['created_at' => now()->subMonths(25)]);
        RecentLaw::factory(2)->create(['created_at' => now()->subMonths(37)]);

        $job = new CleanupOldRecordsJob();
        $job->handle();

        \Log::shouldHaveReceived('info')
            ->withArgs(function ($message, $context) {
                return $message === 'CleanupOldRecordsJob completed'
                    && $context['announcements_deleted'] === 5
                    && $context['press_releases_deleted'] === 3
                    && $context['recent_laws_deleted'] === 2;
            });
    }

    #[Test]
    public function it_has_correct_retry_config(): void
    {
        $job = new CleanupOldRecordsJob();

        $this->assertEquals(3, $job->tries);
        $this->assertEquals([30, 300], $job->backoff);
        $this->assertEquals(600, $job->timeout);
    }
}
