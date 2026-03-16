<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function hasRole(string $role): bool
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('role_user')) {
            return false;
        }

        return $this->roles()->where('name', $role)->exists();
    }

    public function hasPermission(string $permission): bool
    {
        if (!Schema::hasTable('permissions')) {
            return false;
        }

        if (!Schema::hasTable('roles') || !Schema::hasTable('role_user') || !Schema::hasTable('permission_role')) {
            return $this->permissionsTableExistsAndHas($permission);
        }

        $hasRolePermission = $this->roles()
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('name', $permission);
            })
            ->exists();

        if ($hasRolePermission) {
            return true;
        }

        return $this->permissionsTableExistsAndHas($permission);
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin
            || $this->hasRole('admin')
            || $this->hasPermission('admin.access');
    }

    public function canAccessAdminPanel(): bool
    {
        if ((bool) $this->is_admin || $this->hasRole('admin') || $this->hasRole('staff')) {
            return true;
        }

        return $this->hasPermission('admin.access')
            || $this->hasPermission('content.manage')
            || $this->hasPermission('staff.create');
    }

    protected function permissionsTableExistsAndHas(string $permission): bool
    {
        if (!Schema::hasTable('permission_user')) {
            return false;
        }

        return $this->permissions()->where('name', $permission)->exists();
    }
}
