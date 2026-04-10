<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

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
        'phone_number',
        'address',
        'bio',
        'avatar_path',
        'email_notifications',
        'role',
        'balance',
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
            'balance' => 'decimal:2',
            'password' => 'hashed',
            'email_notifications' => 'boolean',
        ];
    }

    /**
     * Get all wallet transactions for this user.
     */
    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * A user can have many roles (admin, staff, etc.)
     * Joins through role_user pivot table
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * A user can have direct permission assignments
     * Used for per-user permission overrides
     * Joins through permission_user pivot table
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Check if user has a specific role by name
     * Returns false if tables don't exist (pre-migration safety)
     *
     * @param string $role Role name (e.g., 'admin', 'staff')
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        // Guard: RBAC tables might not exist during initial migration
        if (!Schema::hasTable('roles') || !Schema::hasTable('role_user')) {
            return false;
        }

        return $this->roles()->where('name', $role)->exists();
    }

    /**
     * Check if user has a specific permission (via role OR direct assignment)
     * Checks two paths:
     *   1. User → Role → Permission (role-based permissions)
     *   2. User → Permission (direct assignment for per-user overrides)
     *
     * @param string $permission Permission name (e.g., 'staff.create', 'content.manage')
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        // Guard: permissions table might not exist yet
        if (!Schema::hasTable('permissions')) {
            return false;
        }

        // If role tables missing, fall back to direct permission check
        if (!Schema::hasTable('roles') || !Schema::hasTable('role_user') || !Schema::hasTable('permission_role')) {
            return $this->permissionsTableExistsAndHas($permission);
        }

        // Check: does user have this permission via a role?
        $hasRolePermission = $this->roles()
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('name', $permission);
            })
            ->exists();

        if ($hasRolePermission) {
            return true;
        }

        // Check: does user have this permission via direct assignment?
        return $this->permissionsTableExistsAndHas($permission);
    }

    /**
     * Determine if user is admin
     * Admin check supports three paths for backward compatibility:
     *   1. Legacy is_admin flag on users table
     *   2. User has 'admin' role
     *   3. User has 'admin.access' permission
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return (bool) $this->is_admin
            || $this->hasRole('admin')
            || $this->hasPermission('admin.access');
    }

    /**
     * Can user access the admin panel at all?
     * Broader than isAdmin() - allows staff with specific permissions
     *
     * @return bool
     */
    public function canAccessAdminPanel(): bool
    {
        // Allow any user with admin/staff role
        if ((bool) $this->is_admin || $this->hasRole('admin') || $this->hasRole('staff')) {
            return true;
        }

        // Allow any user with any admin-relevant permission
        return $this->hasPermission('admin.access')
            || $this->hasPermission('content.manage')
            || $this->hasPermission('staff.create');
    }

    /**
     * Helper: check if user has direct permission (not via role)
     * Internal method for permission_user pivot table checks
     *
     * @param string $permission
     * @return bool
     */
    protected function permissionsTableExistsAndHas(string $permission): bool
    {
        if (!Schema::hasTable('permission_user')) {
            return false;
        }

        return $this->permissions()->where('name', $permission)->exists();
    }

    /**
     * Calculate user balance from wallet transactions.
     * Balance = sum of all credits - sum of all debits
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function calculateBalance()
    {
        return $this->walletTransactions()
            ->selectRaw('
                COALESCE(SUM(CASE WHEN type = "credit" THEN value ELSE 0 END), 0) as total_credits,
                COALESCE(SUM(CASE WHEN type = "debit" THEN value ELSE 0 END), 0) as total_debits
            ')
            ->first();
    }

    /**
     * Get the calculated balance without loading all transactions.
     * Returns: credits - debits
     *
     * @return float
     */
    public function getCalculatedBalance(): float
    {
        $balanceData = $this->calculateBalance();
        return (float) $balanceData->total_credits - (float) $balanceData->total_debits;
    }
}
