<?php

/**
 * ArchiveDataJob – Unit Test Suite
 *
 * Tests the queue job that sets `archived_at` on records older than
 * a configurable month threshold. Covers announcements, press releases,
 * unknown model handling, retry config, chunking, and idempotency.
 *
 * Pest concepts demonstrated:
 *  • it()                    – each test is a standalone closure.
 *  • describe()              – groups tests; label shows as prefix in output.
 *  • ->throws(Exception, msg) – Pest shorthand for expecting an exception.
 *  • Log::spy()              – Laravel spy to assert log messages were written.
 *  • expect()->not->toBeNull() – negated fluent assertion.
 */

use App\Jobs\ArchiveDataJob;
use App\Models\Announcement;
use App\Models\PressRelease;
use Illuminate\Support\Facades\Log;

// ─── Archive Logic ────────────────────────────────────────────────────────────
describe('Archive Logic', function () {

    // Old announcement (>12mo) gets archived_at set; recent one stays null.
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

    // Same archiving logic applied to press_releases table.
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

    // Custom threshold (6 months) should still respect the age boundary.
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
});

// ─── Edge Cases ───────────────────────────────────────────────────────────────
describe('Edge Cases', function () {

    // Passing an unsupported model name should throw InvalidArgumentException.
    it('throws exception for unknown model', function (): void {
        $job = new ArchiveDataJob('unknown_model', 12);

        $job->handle();
    })->throws(InvalidArgumentException::class, 'Unknown model: unknown_model');

    // 250 records processed in chunks; all should be archived + log verified.
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

    // Already-archived records should keep their original archived_at timestamp.
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

    // When no records qualify, the job logs 0 archived and doesn't error.
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
});

// ─── Config ───────────────────────────────────────────────────────────────────
describe('Config', function () {

    // Verify the job's retry/backoff/timeout properties are correct.
    it('has correct retry config', function (): void {
        $job = new ArchiveDataJob('announcements', 12);

        expect($job->tries)->toBe(3);
        expect($job->backoff)->toBe([60, 300]);
        expect($job->timeout)->toBe(600);
    });
});
