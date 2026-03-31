<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * StaffController: CRUD operations for staff user management
 * All methods check 'staff.create' permission via UserPolicy
 * Only admins or staff with 'staff.create' permission can access
 */
class StaffController extends Controller
{
    /**
     * Display list of all staff users with their permissions
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Policy check: only admins or users with 'staff.create' permission
        $this->authorize('createStaff', User::class);

        $staff = User::query()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'staff');
            })
            ->with(['permissions:id,name,label', 'roles'])
            ->orderBy('name')
            ->get();

        return view('admin.staff.index', compact('staff'));
    }

    /**
     * Show form to create new staff user
     * Form allows: name, email, password, and permission selection
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Policy check: only staff.create permission allowed
        $this->authorize('createStaff', User::class);

        // Load all available permissions for checkbox selection
        $permissions = Permission::orderBy('name')->get();

        return view('admin.staff.create', compact('permissions'));
    }

    /**
     * Store new staff user in database
     * Creates user, assigns 'staff' role, and sets selected permissions
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->authorize('createStaff', User::class);

        // Validate input: name, email (unique), strong password, at least 1 permission
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        // Get or fail: always need a 'staff' role
        $staffRole = Role::where('name', 'staff')->firstOrFail();
        
        // Convert permission names to IDs for bulk assignment
        $permissionIds = Permission::whereIn('name', $validated['permissions'])->pluck('id')->all();

        // Create user with hashed password, explicitly not admin
        $staff = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_admin' => false,  // Prevent privilege escalation
        ]);

        // Assign staff role via role_user pivot table
        $staff->roles()->syncWithoutDetaching([$staffRole->id]);
        
        // Assign permissions via permission_user pivot table
        $staff->permissions()->sync($permissionIds);

        return redirect()->route('admin.staff.index')->with('success', 'Staff account created successfully.');
    }

    /**
     * Show form to edit staff permissions
     * Allows admin to change which permissions a staff user has
     *
     * @param User $staff
     * @return \Illuminate\View\View
     */
    public function edit(User $staff)
    {
        $this->authorize('createStaff', User::class);

        // Load all permissions for checkbox display
        $permissions = Permission::orderBy('name')->get();
        
        // Get currently assigned permission names for pre-select
        $selectedPermissions = $staff->permissions()->pluck('name')->all();

        return view('admin.staff.edit', compact('staff', 'permissions', 'selectedPermissions'));
    }

    /**
     * Update staff permissions
     * Replaces old permissions with newly selected ones
     * Note: sync() removes old perms and assigns new ones (idempotent)
     *
     * @param Request $request
     * @param User $staff
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $staff)
    {
        $this->authorize('createStaff', User::class);

        // Validate: at least 1 permission required
        $validated = $request->validate([
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        // Convert names to IDs
        $permissionIds = Permission::whereIn('name', $validated['permissions'])->pluck('id')->all();
        
        // Replace all existing permissions with new selection
        $staff->permissions()->sync($permissionIds);

        return redirect()->route('admin.staff.index')->with('success', 'Staff permissions updated successfully.');
    }

    public function destroy(User $staff)
    {
        $this->authorize('createStaff', User::class);

        if (auth()->id() === $staff->id) {
            return back()->withErrors(['staff' => 'You cannot delete your own account.']);
        }

        $staff->permissions()->detach();
        $staff->roles()->detach();
        $staff->delete();

        return redirect()->route('admin.staff.index')->with('success', 'Staff account deleted successfully.');
    }
}
