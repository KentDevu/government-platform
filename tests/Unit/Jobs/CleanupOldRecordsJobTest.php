<?php

use App\Jobs\CleanupOldRecordsJob;
use App\Models\Announcement;
use App\Models\PressRelease;
use App\Models\RecentLaw;
use Illuminate\Support\Facades\Log;

it('deletes announcements older than 12 months', function (): void {
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
});

it('deletes press releases older than 24 months', function (): void {
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
});

it('deletes recent laws older than 36 months', function (): void {
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
});

it('handles empty database', function (): void {
    Log::spy();

    $job = new CleanupOldRecordsJob();
    $job->handle();

    $this->assertDatabaseCount('announcements', 0);
    $this->assertDatabaseCount('press_releases', 0);
    $this->assertDatabaseCount('recent_laws', 0);

    Log::shouldHaveReceived('info')
        ->withArgs(function ($message, $context) {
            return $message === 'CleanupOldRecordsJob completed'
                && $context['announcements_deleted'] === 0
                && $context['press_releases_deleted'] === 0
                && $context['recent_laws_deleted'] === 0;
        });
});

it('does not delete records at exact threshold', function (): void {
    $atThreshold = Announcement::factory()->create([
        'created_at' => now()->subMonths(12),
    ]);

    $job = new CleanupOldRecordsJob();
    $job->handle();

    $this->assertDatabaseHas('announcements', ['id' => $atThreshold->id]);
});

it('returns correct counts', function (): void {
    Log::spy();

    Announcement::factory(5)->create(['created_at' => now()->subMonths(13)]);
    PressRelease::factory(3)->create(['created_at' => now()->subMonths(25)]);
    RecentLaw::factory(2)->create(['created_at' => now()->subMonths(37)]);

    $job = new CleanupOldRecordsJob();
    $job->handle();

    Log::shouldHaveReceived('info')
        ->withArgs(function ($message, $context) {
            return $message === 'CleanupOldRecordsJob completed'
                && $context['announcements_deleted'] === 5
                && $context['press_releases_deleted'] === 3
                && $context['recent_laws_deleted'] === 2;
        });
});

it('has correct retry config', function (): void {
    $job = new CleanupOldRecordsJob();

    expect($job->tries)->toBe(3);
    expect($job->backoff)->toBe([30, 300]);
    expect($job->timeout)->toBe(600);
});
