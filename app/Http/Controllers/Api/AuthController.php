<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\OtpSendRequest;
use App\Http\Requests\Api\OtpVerifyRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    /**
     * Login with email + password, return Sanctum token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->findAdminPortalUser($request->email);

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Invalid credentials.', 401);
        }

        // Revoke previous API tokens to prevent accumulation
        $user->tokens()->where('name', 'api-token')->delete();

        $token = $user->createToken(
            'api-token',
            $this->getTokenAbilities($user),
        )->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user->load(['roles', 'permissions'])),
        ], 'Login successful.');
    }

    /**
     * Send OTP to user's email for passwordless login.
     */
    public function sendOtp(OtpSendRequest $request): JsonResponse
    {
        $user = $this->findAdminPortalUser($request->email);

        if (!$user) {
            // Return success even if user not found to prevent enumeration
            return $this->successResponse(null, 'If this email is registered, an OTP has been sent.');
        }

        // Generate 6-digit OTP and cache for 10 minutes
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put("admin_otp_{$user->id}", $otp, now()->addMinutes(10));

        // Send OTP via email
        $html = view('emails.otp', ['otp' => $otp])->render();
        Mail::html($html, function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('GOV.PH Admin Login Code');
        });

        return $this->successResponse(null, 'If this email is registered, an OTP has been sent.');
    }

    /**
     * Verify OTP and return Sanctum token.
     */
    public function verifyOtp(OtpVerifyRequest $request): JsonResponse
    {
        $user = $this->findAdminPortalUser($request->email);

        if (!$user) {
            return $this->errorResponse('Invalid or expired OTP.', 401);
        }

        // Enforce per-user OTP attempt limit (max 3 attempts)
        $attemptsKey = "otp_attempts_{$user->id}";
        $attempts = (int) Cache::get($attemptsKey, 0);

        if ($attempts >= 3) {
            Cache::forget("admin_otp_{$user->id}");
            Cache::forget($attemptsKey);
            return $this->errorResponse('Too many attempts. Request a new OTP.', 429);
        }

        $cachedOtp = Cache::get("admin_otp_{$user->id}");

        if (!$cachedOtp || !hash_equals($cachedOtp, $request->otp)) {
            Cache::put($attemptsKey, $attempts + 1, now()->addMinutes(10));
            return $this->errorResponse('Invalid or expired OTP.', 401);
        }

        // OTP verified — clear it and reset attempts
        Cache::forget("admin_otp_{$user->id}");
        Cache::forget($attemptsKey);

        // Revoke previous API tokens
        $user->tokens()->where('name', 'api-token')->delete();

        $token = $user->createToken(
            'api-token',
            $this->getTokenAbilities($user),
        )->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user->load(['roles', 'permissions'])),
        ], 'OTP verified. Login successful.');
    }

    /**
     * Revoke the current access token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logged out successfully.');
    }

    /**
     * Find a user who can access the admin portal.
     * Mirrors the web AuthController logic.
     */
    private function findAdminPortalUser(string $email): ?User
    {
        $rbacTablesReady = Schema::hasTable('roles')
            && Schema::hasTable('permissions')
            && Schema::hasTable('role_user')
            && Schema::hasTable('permission_role')
            && Schema::hasTable('permission_user');

        if ($rbacTablesReady) {
            return User::where('email', $email)
                ->where(function ($query) {
                    $query->where('is_admin', true)
                        ->orWhereHas('roles', function ($roleQuery) {
                            $roleQuery->whereIn('name', ['admin', 'staff']);
                        })
                        ->orWhereHas('roles.permissions', function ($permissionQuery) {
                            $permissionQuery->whereIn('name', ['admin.access', 'content.manage', 'staff.create']);
                        })
                        ->orWhereHas('permissions', function ($permissionQuery) {
                            $permissionQuery->whereIn('name', ['admin.access', 'content.manage', 'staff.create']);
                        });
                })
                ->first();
        }

        return User::where('email', $email)
            ->where('is_admin', true)
            ->first();
    }

    /**
     * Build token abilities from user's actual permissions.
     */
    private function getTokenAbilities(User $user): array
    {
        $abilities = [];

        if ($user->isAdmin()) {
            $abilities[] = 'admin.access';
            $abilities[] = 'content.manage';
            $abilities[] = 'staff.create';
        } else {
            if ($user->hasPermission('admin.access')) {
                $abilities[] = 'admin.access';
            }
            if ($user->hasPermission('content.manage')) {
                $abilities[] = 'content.manage';
            }
            if ($user->hasPermission('staff.create')) {
                $abilities[] = 'staff.create';
            }
        }

        return $abilities;
    }
}
