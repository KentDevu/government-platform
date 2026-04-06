<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

$adminRole = null;

beforeEach(function () use (&$adminRole): void {
    Storage::fake('public');
    $this->seed(Database\Seeders\RbacSeeder::class);

    $adminRole = Role::where('name', 'admin')->firstOrFail();
});

it('authenticated admin user can view their profile', function () use (&$adminRole): void {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone_number' => '+639123456789',
        'address' => 'Manila, Philippines',
        'bio' => 'A passionate developer',
        'avatar_path' => 'avatars/test.jpg',
    ]);
    $user->roles()->syncWithoutDetaching([$adminRole->id]);

    $response = $this->actingAs($user)->get('/admin/profile');

    $response->assertOk();
    $response->assertViewIs('admin.profile.show');
    $response->assertViewHas('user', function ($viewUser) use ($user) {
        return $viewUser->id === $user->id;
    });
});

it('guest cannot view profile', function () use (&$adminRole): void {
    $response = $this->get('/admin/profile');

    $response->assertRedirect(route('admin.login'));
});

it('user can update their profile with valid data', function () use (&$adminRole): void {
    $user = User::factory()->create([
        'name' => 'Old Name',
    ]);
    $user->roles()->syncWithoutDetaching([$adminRole->id]);

    $response = $this->actingAs($user)->put('/admin/profile', [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'phone_number' => '+639987654321',
        'address' => 'Quezon City, Philippines',
        'bio' => 'An updated bio',
    ]);

    $response->assertRedirect(route('admin.profile'));
    $response->assertSessionHasNoErrors();

    expect($user->fresh()->name)->toBe('Updated Name');
    expect($user->fresh()->email)->toBe('updated@example.com');
    expect($user->fresh()->phone_number)->toBe('+639987654321');
    expect($user->fresh()->address)->toBe('Quezon City, Philippines');
    expect($user->fresh()->bio)->toBe('An updated bio');
});

it('user can update profile with avatar upload', function () use (&$adminRole): void {
    $user = User::factory()->create();
    $user->roles()->syncWithoutDetaching([$adminRole->id]);

    $file = UploadedFile::fake()->image('avatar.jpg');

    $response = $this->actingAs($user)->put('/admin/profile', [
        'name' => $user->name,
        'email' => $user->email,
        'avatar' => $file,
    ]);

    $response->assertRedirect(route('admin.profile'));
    $response->assertSessionHasNoErrors();

    Storage::disk('public')->assertExists('avatars/' . $file->hashName());
    expect($user->fresh()->avatar_path)->toContain('avatars/');
});

it('profile update requires validation', function () use (&$adminRole): void {
    $user = User::factory()->create();
    $user->roles()->syncWithoutDetaching([$adminRole->id]);

    $response = $this->actingAs($user)->put('/admin/profile', [
        'name' => '',
        'email' => 'not-an-email',
    ]);

    $response->assertSessionHasErrors(['name', 'email']);
});

it('user can view their profile edit page', function () use (&$adminRole): void {
    $user = User::factory()->create();
    $user->roles()->syncWithoutDetaching([$adminRole->id]);

    $response = $this->actingAs($user)->get('/admin/profile/edit');

    $response->assertOk();
    $response->assertViewIs('admin.profile.edit');
    $response->assertViewHas('user', function ($viewUser) use ($user) {
        return $viewUser->id === $user->id;
    });
});

it('user cannot update another users profile', function () use (&$adminRole): void {
    $user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com',
    ]);
    $user->roles()->syncWithoutDetaching([$adminRole->id]);

    $otherUser = User::factory()->create([
        'name' => 'Other User',
        'email' => 'other@example.com',
    ]);

    // Attempt to “target” another user by sending their ID.
    $response = $this->actingAs($user)->put('/admin/profile', [
        'id' => $otherUser->id,
        'name' => 'Hacked Name',
        'email' => 'hacked@example.com',
    ]);

    $response->assertRedirect(route('admin.profile'));
    $response->assertSessionHasNoErrors();

    // Authenticated user changed.
    expect($user->fresh()->name)->toBe('Hacked Name');
    expect($user->fresh()->email)->toBe('hacked@example.com');

    // Other user did not change.
    expect($otherUser->fresh()->name)->toBe('Other User');
    expect($otherUser->fresh()->email)->toBe('other@example.com');
});
