<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

final class UserProfileController extends Controller
{
    public function show(): View
    {
        $user = auth()->user()->load(['roles', 'permissions']);

        return view('admin.profile.show', compact('user'));
    }

    public function edit(): View
    {
        $user = auth()->user();

        return view('admin.profile.edit', compact('user'));
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            // Store new avatar
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar_path'] = $path;
        }

        $user->update($data);

        return redirect()
            ->route('admin.profile')
            ->with('success', 'Profile updated successfully.');
    }
}
