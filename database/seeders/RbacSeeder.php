<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * RbacSeeder: Initialize RBAC system with default roles and permissions
 * Called during php artisan migrate --seed
 * Creates: 3 permissions, 2 roles, and assigns permissions to roles
 * Links: existing admin users to admin role
 */
class RbacSeeder extends Seeder
{
    public function run(): void
    {
        // Step 1: Create permissions
        $permissions = [
            'admin.access' => 'Access admin-restricted actions',
            'content.manage' => 'Manage website content',
            'staff.create' => 'Create staff accounts',
        ];

        // Use updateOrCreate to handle re-runs without duplication
        foreach ($permissions as $name => $label) {
            Permission::updateOrCreate(['name' => $name], ['label' => $label]);
        }

        // Step 2: Create roles (or fetch existing)
        $adminRole = Role::updateOrCreate(['name' => 'admin'], ['label' => 'Administrator']);
        $staffRole = Role::updateOrCreate(['name' => 'staff'], ['label' => 'Staff']);

        // Step 3: Assign permissions to roles
        // Admin gets all permissions
        $adminRole->permissions()->sync(Permission::whereIn('name', [
            'admin.access',
            'content.manage',
            'staff.create',
        ])->pluck('id')->all());

        // Staff role starts with no permissions (assigned per-user)
        $staffRole->permissions()->sync([]);

        // Step 4: Assign admin role to existing is_admin users
        // Enables backward compatibility: is_admin flag → admin role
        User::where('is_admin', true)->get()->each(function (User $user) use ($adminRole) {
            $user->roles()->syncWithoutDetaching([$adminRole->id]);
        });
    }
}
