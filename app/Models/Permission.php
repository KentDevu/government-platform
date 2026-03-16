<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'label',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

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
