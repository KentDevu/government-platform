<?php

/**
 * User Profile Management – Feature Test Suite
 *
 * Tests profile viewing, editing, updating (with and without avatar),
 * validation, and protection against targeting other users' profiles.
 *
 * Pest concepts demonstrated here:
 *  • beforeEach()         – runs before every test to seed RBAC and fake storage.
 *  • describe()           – groups related tests; label shows in terminal output.
 *  • use (&$variable)     – shares state between beforeEach and test closures.
 *  • Storage::fake()      – replaces the real disk with a virtual one for safe file assertions.
 *  • expect()->toBe()     – Pest fluent assertion for exact value matching.
 *  • assertSessionHasErrors() – Laravel assertion for validation error bags.
 */

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

$adminRole = null;

// beforeEach: fake the storage disk and seed roles/permissions before each test.
beforeEach(function () use (&$adminRole): void {
    Storage::fake('public');
    $this->seed(Database\Seeders\RbacSeeder::class);

    $adminRole = Role::where('name', 'admin')->firstOrFail();
});

// ─── Profile - View ───────────────────────────────────────────────────────────
describe('Profile - View', function () use (&$adminRole) {

    // Authenticated admin can view their own profile page.
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

    // Unauthenticated visitor should be redirected to login.
    it('guest cannot view profile', function (): void {
        $response = $this->get('/admin/profile');

        $response->assertRedirect(route('admin.login'));
    });

    // Admin can reach the /profile/edit form.
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
});

// ─── Profile - Update ─────────────────────────────────────────────────────────
describe('Profile - Update', function () use (&$adminRole) {

    // User updates their name, email, phone, address, bio — all persisted.
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

    // Avatar upload: file is stored on the faked disk and the path is saved.
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
});

// ─── Profile - Validation ─────────────────────────────────────────────────────
describe('Profile - Validation', function () use (&$adminRole) {

    // Empty name + bad email → validation errors on both fields.
    it('profile update requires validation', function () use (&$adminRole): void {
        $user = User::factory()->create();
        $user->roles()->syncWithoutDetaching([$adminRole->id]);

        $response = $this->actingAs($user)->put('/admin/profile', [
            'name' => '',
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors(['name', 'email']);
    });
});

// ─── Profile - Security ───────────────────────────────────────────────────────
describe('Profile - Security', function () use (&$adminRole) {

    // Mass-assignment guard: sending another user's ID only updates the authenticated user.
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

        // Attempt to "target" another user by sending their ID.
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
});
