<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function index()
    {
        $this->authorize('createStaff', User::class);

        $staff = User::query()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'staff');
            })
            ->with(['permissions:id,name'])
            ->orderBy('name')
            ->get();

        return view('admin.staff.index', compact('staff'));
    }

    public function create()
    {
        $this->authorize('createStaff', User::class);

        $permissions = Permission::orderBy('name')->get();

        return view('admin.staff.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $this->authorize('createStaff', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $staffRole = Role::where('name', 'staff')->firstOrFail();
        $permissionIds = Permission::whereIn('name', $validated['permissions'])->pluck('id')->all();

        $staff = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_admin' => false,
        ]);

        $staff->roles()->syncWithoutDetaching([$staffRole->id]);
        $staff->permissions()->sync($permissionIds);

        return redirect()->route('admin.staff.index')->with('success', 'Staff account created successfully.');
    }

    public function edit(User $staff)
    {
        $this->authorize('createStaff', User::class);

        $permissions = Permission::orderBy('name')->get();
        $selectedPermissions = $staff->permissions()->pluck('name')->all();

        return view('admin.staff.edit', compact('staff', 'permissions', 'selectedPermissions'));
    }

    public function update(Request $request, User $staff)
    {
        $this->authorize('createStaff', User::class);

        $validated = $request->validate([
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $permissionIds = Permission::whereIn('name', $validated['permissions'])->pluck('id')->all();
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
