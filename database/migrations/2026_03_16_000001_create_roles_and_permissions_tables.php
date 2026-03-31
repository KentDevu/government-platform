<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Table 1: Roles (admin, staff, manager, etc.)
        // Stores role definitions with human-readable labels
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();     // 'admin', 'staff' — used in code
            $table->string('label')->nullable();  // 'Administrator', 'Staff Member' — for display
            $table->timestamps();
        });

        // Table 2: Permissions (fine-grained abilities)
        // Stores individual permissions that can be assigned to roles or users
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();     // 'staff.create', 'content.manage'
            $table->string('label')->nullable();  // 'Create staff accounts'
            $table->timestamps();
        });

        // Table 3: Permission ↔ Role junction
        // Links permissions to roles: what can a role do?
        // Example: admin role has staff.create, content.manage permissions
        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        // Table 4: Role ↔ User junction
        // Assigns roles to users: what role does a user have?
        // Example: user John has admin role
        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'user_id']);
        });

        // Table 5: Permission ↔ User junction (per-user overrides)
        // Assigns permissions directly to users (bypasses roles)
        // Example: staff member Kent gets admin.access permission directly
        // Used for: granting individual users elevated permissions without changing roles
        Schema::create('permission_user', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['permission_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_user');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
