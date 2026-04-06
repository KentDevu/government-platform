<?php

use App\Models\Announcement;
use Illuminate\Support\Carbon;

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

it('can delete an announcement record', function (): void {
    $announcement = Announcement::factory()->create();

    $announcement->delete();

    $this->assertModelMissing($announcement);
    $this->assertDatabaseMissing('announcements', [
        'id' => $announcement->id,
    ]);
});

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
