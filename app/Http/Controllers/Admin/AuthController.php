<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    /**
     * Show the email input form (step 1).
     */
    public function showLogin()
    {
        if (auth()->check() && auth()->user()->canAccessAdminPanel()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    /**
     * Validate email, generate OTP, send via SMTP (step 1 → step 2).
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'login_method' => 'nullable|in:otp,password',
        ]);

        $user = $this->findAdminPortalUserByEmail($request->email);

        if (!$user) {
            return back()->withErrors(['email' => 'No staff/admin account found with this email.'])->onlyInput('email');
        }

        if ($request->input('login_method') === 'password') {
            $request->validate(['password' => 'required|string']);

            if (!Hash::check($request->password, $user->password)) {
                return back()->withErrors(['password' => 'Invalid password.'])->onlyInput('email');
            }

            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('admin.dashboard');
        }

        // Generate 6-digit OTP and store in cache for 10 minutes
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put("admin_otp_{$user->id}", $otp, now()->addMinutes(10));

        // Send OTP via email (HTML template)
        $html = view('emails.otp', ['otp' => $otp])->render();
        Mail::html($html, function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('GOV.PH Admin Login Code');
        });

        // Store user ID in session for the verify step
        $request->session()->put('otp_user_id', $user->id);
        $request->session()->put('otp_email', $user->email);

        return redirect()->route('admin.verify');
    }

    protected function findAdminPortalUserByEmail(string $email): ?User
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
     * Show the OTP verification form (step 2).
     */
    public function showVerify(Request $request)
    {
        if (!$request->session()->has('otp_user_id')) {
            return redirect()->route('admin.login');
        }

        $email = $request->session()->get('otp_email');

        return view('admin.verify', compact('email'));
    }

    /**
     * Verify the OTP and log in (step 2 → dashboard).
     */
    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required|string|size:6']);

        $userId = $request->session()->get('otp_user_id');

        if (!$userId) {
            return redirect()->route('admin.login');
        }

        $cachedOtp = Cache::get("admin_otp_{$userId}");

        if (!$cachedOtp || !hash_equals($cachedOtp, $request->otp)) {
            return back()->withErrors(['otp' => 'Invalid or expired code. Please try again.']);
        }

        // OTP verified — log in the user
        Cache::forget("admin_otp_{$userId}");
        $request->session()->forget(['otp_user_id', 'otp_email']);

        Auth::loginUsingId($userId);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('landing');
    }
}
