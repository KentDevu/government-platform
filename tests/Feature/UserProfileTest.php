<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed(\Database\Seeders\RbacSeeder::class);
    }

    private function createAdminUser(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_authenticated_admin_user_can_view_their_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone_number' => '+639123456789',
            'address' => 'Manila, Philippines',
            'bio' => 'A passionate developer',
            'avatar_path' => 'avatars/test.jpg',
        ]);
        $user->roles()->attach(1); // admin role

        $response = $this->actingAs($user)->get('/admin/profile');

        $response->assertOk();
        $response->assertViewIs('admin.profile.show');
        $response->assertViewHas('user', function ($viewUser) use ($user) {
            return $viewUser->id === $user->id;
        });
    }

    public function test_guest_cannot_view_profile(): void
    {
        $response = $this->get('/admin/profile');

        $response->assertRedirect(route('admin.login'));
    }

    public function test_user_can_update_their_profile_with_valid_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
        ]);
        $user->roles()->attach(1); // admin role

        $response = $this->actingAs($user)->put('/admin/profile', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone_number' => '+639987654321',
            'address' => 'Quezon City, Philippines',
            'bio' => 'An updated bio',
        ]);

        $response->assertRedirect(route('admin.profile'));
        $response->assertSessionHasNoErrors();

        $this->assertSame('Updated Name', $user->fresh()->name);
        $this->assertSame('updated@example.com', $user->fresh()->email);
        $this->assertSame('+639987654321', $user->fresh()->phone_number);
        $this->assertSame('Quezon City, Philippines', $user->fresh()->address);
        $this->assertSame('An updated bio', $user->fresh()->bio);
    }

    public function test_user_can_update_profile_with_avatar_upload(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(1); // admin role

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->actingAs($user)->put('/admin/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $file,
        ]);

        $response->assertRedirect(route('admin.profile'));
        $response->assertSessionHasNoErrors();

        Storage::disk('public')->assertExists('avatars/' . $file->hashName());

        $this->assertStringContainsString('avatars/', $user->fresh()->avatar_path);
    }

    public function test_profile_update_requires_validation(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(1); // admin role

        $response = $this->actingAs($user)->put('/admin/profile', [
            'name' => '',
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors(['name', 'email']);
    }

    public function test_user_can_view_their_profile_edit_page(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(1); // admin role

        $response = $this->actingAs($user)->get('/admin/profile/edit');

        $response->assertOk();
        $response->assertViewIs('admin.profile.edit');
        $response->assertViewHas('user', function ($viewUser) use ($user) {
            return $viewUser->id === $user->id;
        });
    }

    public function test_user_cannot_update_another_users_profile(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(1); // admin role
        $otherUser = User::factory()->create();

        $response = $this->actingAs($user)->put('/admin/profile', [
            'name' => 'Hacked Name',
            'email' => 'hacked@example.com',
        ]);

        $response->assertRedirect(route('admin.profile'));

        $this->assertNotSame('Hacked Name', $otherUser->fresh()->name);
    }
}
