<?php

use App\Jobs\ArchiveDataJob;
use App\Models\Announcement;
use App\Models\PressRelease;
use Illuminate\Support\Facades\Log;

it('archives announcements older than threshold', function (): void {
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
    expect(Announcement::find($oldAnnouncement->id)->archived_at)->not->toBeNull();

    $this->assertDatabaseHas('announcements', [
        'id' => $recentAnnouncement->id,
    ]);
    expect(Announcement::find($recentAnnouncement->id)->archived_at)->toBeNull();
});

it('archives press releases older than threshold', function (): void {
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
    expect(PressRelease::find($oldPressRelease->id)->archived_at)->not->toBeNull();

    $this->assertDatabaseHas('press_releases', [
        'id' => $recentPressRelease->id,
    ]);
    expect(PressRelease::find($recentPressRelease->id)->archived_at)->toBeNull();
});

it('throws exception for unknown model', function (): void {
    $job = new ArchiveDataJob('unknown_model', 12);

    $job->handle();
})->throws(InvalidArgumentException::class, 'Unknown model: unknown_model');

it('has correct retry config', function (): void {
    $job = new ArchiveDataJob('announcements', 12);

    expect($job->tries)->toBe(3);
    expect($job->backoff)->toBe([60, 300]);
    expect($job->timeout)->toBe(600);
});

it('archives with custom months threshold', function (): void {
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

    expect(Announcement::find($oldAnnouncement->id)->archived_at)->not->toBeNull();
    expect(Announcement::find($recentAnnouncement->id)->archived_at)->toBeNull();
});

it('handles large batch via chunking', function (): void {
    Log::spy();

    Announcement::factory(250)->create(['created_at' => now()->subMonths(13), 'archived_at' => null]);

    $job = new ArchiveDataJob('announcements', 12);
    $job->handle();

    $this->assertDatabaseCount('announcements', 250);
    expect(Announcement::whereNotNull('archived_at')->count())->toBe(250);

    Log::shouldHaveReceived('info')
        ->withArgs(function ($message, $context) {
            return $message === 'ArchiveDataJob completed'
                && $context['records_archived'] === 250;
        });
});

it('does not re archive already archived records', function (): void {
    $oldArchivedAnnouncement = Announcement::factory()->create([
        'created_at' => now()->subMonths(13),
        'archived_at' => now()->subMonths(6),
    ]);

    $job = new ArchiveDataJob('announcements', 12);
    $job->handle();

    expect(Announcement::find($oldArchivedAnnouncement->id)->archived_at)
        ->toEqual($oldArchivedAnnouncement->archived_at);
});

it('returns zero when no records to archive', function (): void {
    Log::spy();

    Announcement::factory(5)->create(['created_at' => now()->subMonths(3), 'archived_at' => null]);

    $job = new ArchiveDataJob('announcements', 12);
    $job->handle();

    Log::shouldHaveReceived('info')
        ->withArgs(function ($message, $context) {
            return $message === 'ArchiveDataJob completed'
                && $context['records_archived'] === 0;
        });
});
