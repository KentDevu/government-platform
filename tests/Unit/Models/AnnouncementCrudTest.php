<?php

/**
 * Announcement Model CRUD – Unit Test Suite
 *
 * Tests direct Eloquent model operations: create, read, update, delete,
 * database defaults, and datetime casting — no HTTP layer involved.
 *
 * Pest concepts demonstrated here:
 *  • it()                        – closure-based test, no class needed.
 *  • describe()                  – groups tests; label shows as prefix in output.
 *  • $this->assertModelExists()  – Laravel assertion bound via Pest TestCase.
 *  • $this->assertDatabaseHas()  – verifies a row exists with given columns.
 *  • expect()->toBe()            – Pest fluent assertion for exact equality.
 *  • expect()->toBeInstanceOf()  – checks the runtime type of a value.
 */

use App\Models\Announcement;
use Illuminate\Support\Carbon;

// ─── CRUD Operations ──────────────────────────────────────────────────────────
describe('CRUD Operations', function () {

    // CREATE: build an announcement from factory, persist it, and verify it exists.
    it('can create an announcement record', function (): void {
        $payload = Announcement::factory()->make([
            'category' => 'Public Notice',
            'title' => 'Road Closure Notice',
        ])->toArray();

        $announcement = Announcement::create($payload);

        $this->assertModelExists($announcement);
        $this->assertDatabaseHas('announcements', [
            'id' => $announcement->id,
            'title' => 'Road Closure Notice',
            'category' => 'Public Notice',
        ]);
    });

    // READ: create then fetch by ID; verify attributes match.
    it('can read an existing announcement record', function (): void {
        $announcement = Announcement::factory()->create([
            'title' => 'Community Advisory',
            'category' => 'Advisory',
        ]);

        $storedAnnouncement = Announcement::query()->find($announcement->id);

        expect($storedAnnouncement)->not->toBeNull();
        expect($storedAnnouncement->title)->toBe('Community Advisory');
        expect($storedAnnouncement->category)->toBe('Advisory');
    });

    // UPDATE: change title + excerpt + sort_order, verify the new values in DB.
    it('can update an existing announcement record', function (): void {
        $announcement = Announcement::factory()->create([
            'title' => 'Original Title',
            'excerpt' => 'Original excerpt',
        ]);

        $announcement->update([
            'title' => 'Updated Title',
            'excerpt' => 'Updated excerpt',
            'sort_order' => 25,
        ]);

        $this->assertDatabaseHas('announcements', [
            'id' => $announcement->id,
            'title' => 'Updated Title',
            'excerpt' => 'Updated excerpt',
            'sort_order' => 25,
        ]);
    });

    // DELETE: soft/hard delete the record and verify it no longer exists.
    it('can delete an announcement record', function (): void {
        $announcement = Announcement::factory()->create();

        $announcement->delete();

        $this->assertModelMissing($announcement);
        $this->assertDatabaseMissing('announcements', [
            'id' => $announcement->id,
        ]);
    });
});

// ─── Defaults & Casting ───────────────────────────────────────────────────────
describe('Defaults & Casting', function () {

    // DEFAULTS: columns without explicit values should use migration defaults.
    it('applies database defaults for category color and sort order', function (): void {
        $announcement = Announcement::create([
            'category' => 'Memo',
            'title' => 'Default Values Check',
            'excerpt' => 'Verifies database defaults.',
            'date' => '2026-04-06',
        ]);

        $announcement->refresh();

        expect($announcement->category_color)->toBe('primary');
        expect($announcement->sort_order)->toEqual(0);
    });

    // CASTING: the model's $casts should turn archived_at into a Carbon instance.
    it('casts archived_at to datetime', function (): void {
        $expectedArchivedAt = Carbon::parse('2026-04-06 08:30:00', config('app.timezone'));

        $announcement = Announcement::create([
            'category' => 'Archive',
            'category_color' => 'info',
            'title' => 'Archived Notice',
            'excerpt' => 'Announcement archive test.',
            'date' => '2026-04-06',
            'archived_at' => '2026-04-06 08:30:00',
        ]);

        $announcement->refresh();

        expect($announcement->archived_at)->toBeInstanceOf(Carbon::class);
        expect($announcement->archived_at->toDateString())->toBe($expectedArchivedAt->toDateString());
    });
});
