<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Permission extends Model
{
    /**
     * Mass-assignable attributes
     * name: unique identifier ('staff.create', 'content.manage')
     * label: human-readable description
     */
    protected $fillable = [
        'name',
        'label',
    ];

    /**
     * Permissions can belong to many roles
     * Joins through permission_role pivot table
     * Example: 'staff.create' permission belongs to 'admin' and 'manager' roles
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Permissions can be assigned directly to users (for per-user overrides)
     * Joins through permission_user pivot table
     * Example: grant 'staff.create' directly to staff member Jane
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function getDisplayNameAttribute(): string
    {
        $map = [
            'content.manage' => 'Manage Content',
            'staff.create' => 'Manage Staff',
            'admin.access' => 'Admin Access',
        ];

        if (isset($map[$this->name])) {
            return $map[$this->name];
        }

        return Str::of($this->name)
            ->replace('.', ' ')
            ->replace('_', ' ')
            ->title()
            ->toString();
    }
}
