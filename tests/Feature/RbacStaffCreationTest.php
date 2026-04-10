<?php

/**
 * RBAC & Staff Creation – Feature Test Suite
 *
 * Tests role-based access control for staff management, login flows
 * (password + OTP), session handling, and IP-gated admin routes.
 *
 * Pest concepts demonstrated here:
 *  • it()              – each closure is an independent test case.
 *  • describe()        – groups related tests; label shows as prefix in output.
 *  • $this->seed()     – seeds RbacSeeder to populate roles & permissions.
 *  • $this->actingAs() – authenticates a user for the request.
 *  • expect()->toBe*() – Pest fluent assertions (toBeTrue, toBeNull, etc.).
 *  • assertRedirect(), assertForbidden(), assertSessionHasErrors()
 *                      – Laravel HTTP test assertions (work because Pest binds TestCase).
 *  • Mail::fake()      – Laravel mail fake to capture sent emails without sending.
 *  • Cache::get()      – used to verify OTP was stored/cleared.
 */

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

// ─── Staff - CRUD ─────────────────────────────────────────────────────────────
describe('Staff - CRUD', function () {

    // A non-admin user should be redirected away from the staff creation page.
    it('non admin cannot open create staff page', function (): void {
        $this->seed(RbacSeeder::class);

        $nonAdmin = User::factory()->create();

        $response = $this->actingAs($nonAdmin)->get(route('admin.staff.create'));

        $response->assertRedirect(route('admin.login'));
    });

    // Admin creates a new staff user and verifies role + permissions were attached.
    it('admin can create staff with name email password and permissions', function (): void {
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

        expect($staff)->not->toBeNull();
        expect($staff->hasRole('staff'))->toBeTrue();
        expect($staff->hasPermission('content.manage'))->toBeTrue();
        expect($staff->hasPermission('admin.access'))->toBeTrue();
    });

    // Staff lacking the right permission should get HTTP 403 Forbidden.
    it('staff without admin permission cannot create staff', function (): void {
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
    });

    // Staff who has the staff.create permission CAN create other staff.
    it('staff with staff create permission can create staff', function (): void {
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
    });

    // Admin can view the staff list and see staff names rendered.
    it('admin can view staff list', function (): void {
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
    });

    // Admin can swap a staff member's permissions (old removed, new added).
    it('admin can update staff permissions', function (): void {
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
        expect($staff->fresh()->hasPermission('admin.access'))->toBeTrue();
        expect($staff->fresh()->hasPermission('content.manage'))->toBeFalse();
    });

    // Admin can permanently delete a staff account from the system.
    it('admin can delete staff account', function (): void {
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
    });
});

// ─── Login - Password ─────────────────────────────────────────────────────────
describe('Login - Password', function () {

    // Happy-path password login: valid credentials → redirect to dashboard.
    it('staff can login with email and password', function (): void {
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
    });

    // Wrong password → back to login page with validation error, stays guest.
    it('staff cannot login with invalid password', function (): void {
        $this->seed(RbacSeeder::class);

        $staffRole = Role::where('name', 'staff')->firstOrFail();
        $staff = User::factory()->create([
            'email' => 'staff-invalid-pass@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
        ]);
        $staff->roles()->syncWithoutDetaching([$staffRole->id]);

        $response = $this->from(route('admin.login'))->post(route('admin.login.send'), [
            'email' => 'staff-invalid-pass@example.com',
            'password' => 'wrong-password',
            'login_method' => 'password',
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    });
});

// ─── Login - OTP ──────────────────────────────────────────────────────────────
describe('Login - OTP', function () {

    // Full OTP flow: request code → verify code → authenticated + OTP cleared.
    it('staff can login using otp flow', function (): void {
        $this->seed(RbacSeeder::class);
        Mail::fake();

        $staffRole = Role::where('name', 'staff')->firstOrFail();
        $staff = User::factory()->create([
            'email' => 'staff-otp@example.com',
            'is_admin' => false,
        ]);
        $staff->roles()->syncWithoutDetaching([$staffRole->id]);

        $sendOtpResponse = $this->post(route('admin.login.send'), [
            'email' => 'staff-otp@example.com',
            'login_method' => 'otp',
        ]);

        $sendOtpResponse->assertRedirect(route('admin.verify'));

        $otp = Cache::get("admin_otp_{$staff->id}");
        expect($otp)->not->toBeNull();
        expect((bool) preg_match('/^\d{6}$/', $otp))->toBeTrue();

        $verifyResponse = $this->post(route('admin.verify.submit'), [
            'otp' => $otp,
        ]);

        $verifyResponse->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($staff);
        expect(Cache::get("admin_otp_{$staff->id}"))->toBeNull();
    });

    // Wrong OTP code → stays on verify page, user remains a guest.
    it('verify otp rejects invalid code and does not authenticate', function (): void {
        $this->seed(RbacSeeder::class);
        Mail::fake();

        $staffRole = Role::where('name', 'staff')->firstOrFail();
        $staff = User::factory()->create([
            'email' => 'staff-otp-invalid@example.com',
            'is_admin' => false,
        ]);
        $staff->roles()->syncWithoutDetaching([$staffRole->id]);

        $this->post(route('admin.login.send'), [
            'email' => $staff->email,
            'login_method' => 'otp',
        ])->assertRedirect(route('admin.verify'));

        $response = $this->from(route('admin.verify'))->post(route('admin.verify.submit'), [
            'otp' => '999999',
        ]);

        $response->assertRedirect(route('admin.verify'));
        $response->assertSessionHasErrors(['otp']);
        $this->assertGuest();
        expect(Cache::get("admin_otp_{$staff->id}"))->not->toBeNull();
    });

    // Expired OTP (cache cleared) → stays on verify page, guest.
    it('verify otp rejects expired code', function (): void {
        $this->seed(RbacSeeder::class);
        Mail::fake();

        $staffRole = Role::where('name', 'staff')->firstOrFail();
        $staff = User::factory()->create([
            'email' => 'staff-otp-expired@example.com',
            'is_admin' => false,
        ]);
        $staff->roles()->syncWithoutDetaching([$staffRole->id]);

        $this->post(route('admin.login.send'), [
            'email' => $staff->email,
            'login_method' => 'otp',
        ])->assertRedirect(route('admin.verify'));

        Cache::forget("admin_otp_{$staff->id}");

        $response = $this->from(route('admin.verify'))->post(route('admin.verify.submit'), [
            'otp' => '123456',
        ]);

        $response->assertRedirect(route('admin.verify'));
        $response->assertSessionHasErrors(['otp']);
        $this->assertGuest();
    });
});

// ─── Login - Validation ───────────────────────────────────────────────────────
describe('Login - Validation', function () {

    // Regular users (no staff/admin role) should be rejected at login.
    it('staff cannot login if email is not staff or admin', function (): void {
        $this->seed(RbacSeeder::class);

        $user = User::factory()->create([
            'email' => 'regular-user@example.com',
            'is_admin' => false,
        ]);

        $response = $this->from(route('admin.login'))->post(route('admin.login.send'), [
            'email' => $user->email,
            'login_method' => 'otp',
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    });

    // Submitting password login without a password triggers validation error.
    it('password login requires password when login method is password', function (): void {
        $this->seed(RbacSeeder::class);

        $staffRole = Role::where('name', 'staff')->firstOrFail();
        $staff = User::factory()->create([
            'email' => 'staff-missing-password@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
        ]);
        $staff->roles()->syncWithoutDetaching([$staffRole->id]);

        $response = $this->from(route('admin.login'))->post(route('admin.login.send'), [
            'email' => $staff->email,
            'login_method' => 'password',
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    });

    // Accessing /verify without a session (no OTP requested) → back to login.
    it('verify page redirects to login when session is missing user id', function (): void {
        $response = $this->get(route('admin.verify'));

        $response->assertRedirect(route('admin.login'));
    });
});

// ─── Route Access ─────────────────────────────────────────────────────────────
describe('Route Access', function () {

    // IP gating: requests from a non-whitelisted IP get HTTP 404.
    it('admin login routes are not accessible from non admin device ip', function (): void {
        $this->withServerVariables(['REMOTE_ADDR' => '10.123.45.67'])
            ->get(route('admin.login'))
            ->assertNotFound();

        $this->withServerVariables(['REMOTE_ADDR' => '10.123.45.67'])
            ->post(route('admin.login.send'), ['email' => 'nobody@example.com'])
            ->assertNotFound();
    });

    // The generic /login route should redirect to the admin login page.
    it('public login route redirects to admin login', function (): void {
        $response = $this->get(route('login'));

        $response->assertRedirect(route('admin.login'));
    });

    // Public registration is disabled — /register returns 404.
    it('public register route is not available', function (): void {
        $response = $this->get('/register');

        $response->assertNotFound();
    });
});
