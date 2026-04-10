<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Api\StoreStaffRequest;
use App\Http\Requests\Api\UpdateStaffRequest;
use App\Http\Resources\UserResource;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function index(): JsonResponse
    {
        $staff = User::query()
            ->whereHas('roles', fn ($query) => $query->where('name', 'staff'))
            ->with(['permissions:id,name,label', 'roles'])
            ->orderBy('name')
            ->get();

        return $this->successResponse(
            UserResource::collection($staff),
        );
    }

    public function show(User $staff): JsonResponse
    {
        if (!$staff->hasRole('staff')) {
            return $this->errorResponse('Staff member not found.', 404);
        }

        $staff->load(['roles', 'permissions']);

        return $this->successResponse(
            new UserResource($staff),
        );
    }

    public function store(StoreStaffRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $staffRole = Role::where('name', 'staff')->firstOrFail();
        $permissionIds = Permission::whereIn('name', $validated['permissions'])
            ->pluck('id')
            ->all();

        $staff = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_admin' => false,
        ]);

        $staff->roles()->syncWithoutDetaching([$staffRole->id]);
        $staff->permissions()->sync($permissionIds);

        $staff->load(['roles', 'permissions']);

        return $this->createdResponse(
            new UserResource($staff),
            'Staff account created successfully.',
        );
    }

    public function update(UpdateStaffRequest $request, User $staff): JsonResponse
    {
        if (!$staff->hasRole('staff')) {
            return $this->errorResponse('Staff member not found.', 404);
        }

        $validated = $request->validated();

        $permissionIds = Permission::whereIn('name', $validated['permissions'])
            ->pluck('id')
            ->all();

        $staff->permissions()->sync($permissionIds);
        $staff->load(['roles', 'permissions']);

        return $this->successResponse(
            new UserResource($staff),
            'Staff permissions updated successfully.',
        );
    }

    public function destroy(User $staff): JsonResponse
    {
        if (!$staff->hasRole('staff')) {
            return $this->errorResponse('Staff member not found.', 404);
        }

        if (auth()->id() === $staff->id) {
            return $this->errorResponse('You cannot delete your own account.', 403);
        }

        $staff->permissions()->detach();
        $staff->roles()->detach();
        $staff->delete();

        return $this->successResponse(null, 'Staff account deleted successfully.');
    }
}
