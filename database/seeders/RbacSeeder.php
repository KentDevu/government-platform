<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'admin.access' => 'Access admin-restricted actions',
            'content.manage' => 'Manage website content',
            'staff.create' => 'Create staff accounts',
        ];

        foreach ($permissions as $name => $label) {
            Permission::updateOrCreate(['name' => $name], ['label' => $label]);
        }

        $adminRole = Role::updateOrCreate(['name' => 'admin'], ['label' => 'Administrator']);
        $staffRole = Role::updateOrCreate(['name' => 'staff'], ['label' => 'Staff']);

        $adminRole->permissions()->sync(Permission::whereIn('name', [
            'admin.access',
            'content.manage',
            'staff.create',
        ])->pluck('id')->all());

        $staffRole->permissions()->sync([]);

        User::where('is_admin', true)->get()->each(function (User $user) use ($adminRole) {
            $user->roles()->syncWithoutDetaching([$adminRole->id]);
        });
    }
}
