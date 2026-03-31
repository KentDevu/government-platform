<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    /**
     * Mass-assignable attributes
     * name: unique identifier ('admin', 'staff')
     * label: human-readable name ('Administrator', 'Staff Member')
     */
    protected $fillable = [
        'name',
        'label',
    ];

    /**
     * Roles can be assigned to many users
     * Joins through role_user pivot table
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Roles can have many permissions
     * Joins through permission_role pivot table
     *
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }
}
