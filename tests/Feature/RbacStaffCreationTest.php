<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RbacStaffCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_open_create_staff_page(): void
    {
        $this->seed(RbacSeeder::class);

        $nonAdmin = User::factory()->create();

        $response = $this->actingAs($nonAdmin)->get(route('admin.staff.create'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_create_staff_with_name_email_password_and_permissions(): void
    {
        $this->seed(RbacSeeder::class);

        $adminRole = Role::where('name', 'admin')->firstOrFail();
        $admin = User::factory()->create(['is_admin' => false]);
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);

        $response = $this->actingAs($admin)->post(route('admin.staff.store'), [
            'name' => 'Staff User',
            'email' => 'staff@example.com',
            'password' => 'password123',
            'permissions' => ['content.manage', 'admin.access'],
        ]);

        $response->assertRedirect(route('admin.staff.index'));

        $staff = User::where('email', 'staff@example.com')->first();

        $this->assertNotNull($staff);
        $this->assertTrue($staff->hasRole('staff'));
        $this->assertTrue($staff->hasPermission('content.manage'));
        $this->assertTrue($staff->hasPermission('admin.access'));
    }

    public function test_staff_without_admin_permission_cannot_create_staff(): void
    {
        $this->seed(RbacSeeder::class);

        $staffRole = Role::where('name', 'staff')->firstOrFail();
        $staff = User::factory()->create();
        $staff->roles()->syncWithoutDetaching([$staffRole->id]);

        $response = $this->actingAs($staff)->post(route('admin.staff.store'), [
            'name' => 'Blocked User',
            'email' => 'blocked@example.com',
            'password' => 'password123',
            'permissions' => ['content.manage'],
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'blocked@example.com']);
    }

    public function test_staff_with_staff_create_permission_can_create_staff(): void
    {
        $this->seed(RbacSeeder::class);

        $staffRole = Role::where('name', 'staff')->firstOrFail();
        $staffCreatePermission = Permission::where('name', 'staff.create')->firstOrFail();

        $staff = User::factory()->create();
        $staff->roles()->syncWithoutDetaching([$staffRole->id]);
        $staff->permissions()->syncWithoutDetaching([$staffCreatePermission->id]);

        $response = $this->actingAs($staff)->post(route('admin.staff.store'), [
            'name' => 'Created By Staff',
            'email' => 'created-by-staff@example.com',
            'password' => 'password123',
            'permissions' => ['content.manage'],
        ]);

        $response->assertRedirect(route('admin.staff.index'));
        $this->assertDatabaseHas('users', ['email' => 'created-by-staff@example.com']);
    }

    public function test_admin_can_view_staff_list(): void
    {
        $this->seed(RbacSeeder::class);

        $adminRole = Role::where('name', 'admin')->firstOrFail();
        $staffRole = Role::where('name', 'staff')->firstOrFail();

        $admin = User::factory()->create(['is_admin' => false]);
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);

        $staff = User::factory()->create(['name' => 'Listed Staff']);
        $staff->roles()->syncWithoutDetaching([$staffRole->id]);

        $response = $this->actingAs($admin)->get(route('admin.staff.index'));

        $response->assertOk();
        $response->assertSee('Listed Staff');
    }

    public function test_admin_can_update_staff_permissions(): void
    {
        $this->seed(RbacSeeder::class);

        $adminRole = Role::where('name', 'admin')->firstOrFail();
        $staffRole = Role::where('name', 'staff')->firstOrFail();
        $contentManage = Permission::where('name', 'content.manage')->firstOrFail();
        $adminAccess = Permission::where('name', 'admin.access')->firstOrFail();

        $admin = User::factory()->create(['is_admin' => false]);
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);

        $staff = User::factory()->create();
        $staff->roles()->syncWithoutDetaching([$staffRole->id]);
        $staff->permissions()->sync([$contentManage->id]);

        $response = $this->actingAs($admin)->put(route('admin.staff.update', $staff), [
            'permissions' => ['admin.access'],
        ]);

        $response->assertRedirect(route('admin.staff.index'));
        $this->assertTrue($staff->fresh()->hasPermission('admin.access'));
        $this->assertFalse($staff->fresh()->hasPermission('content.manage'));
    }

    public function test_admin_can_delete_staff_account(): void
    {
        $this->seed(RbacSeeder::class);

        $adminRole = Role::where('name', 'admin')->firstOrFail();
        $staffRole = Role::where('name', 'staff')->firstOrFail();

        $admin = User::factory()->create(['is_admin' => false]);
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);

        $staff = User::factory()->create();
        $staff->roles()->syncWithoutDetaching([$staffRole->id]);

        $response = $this->actingAs($admin)->delete(route('admin.staff.destroy', $staff));

        $response->assertRedirect(route('admin.staff.index'));
        $this->assertDatabaseMissing('users', ['id' => $staff->id]);
    }

    public function test_staff_can_login_with_email_and_password(): void
    {
        $this->seed(RbacSeeder::class);

        $staffRole = Role::where('name', 'staff')->firstOrFail();
        $staff = User::factory()->create([
            'email' => 'staff-login@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
        ]);
        $staff->roles()->syncWithoutDetaching([$staffRole->id]);

        $response = $this->post(route('admin.login.send'), [
            'email' => 'staff-login@example.com',
            'password' => 'password123',
            'login_method' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($staff);
    }
}
