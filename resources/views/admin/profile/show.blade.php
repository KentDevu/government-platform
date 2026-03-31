@extends('admin.layout')

@section('title', 'My Profile')

@section('content')
    <div>
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">My Profile</h1>
                <p class="text-sm text-gray-500 mt-1">Manage your account information and preferences</p>
            </div>
            <a href="{{ route('admin.profile.edit') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                <span class="material-symbols-outlined text-lg">edit</span>
                Edit Profile
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Profile Card --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                    <div class="h-24 bg-gradient-to-br from-blue-500 to-blue-700"></div>
                    <div class="px-6 pb-6">
                        <div class="-mt-12 mb-4">
                            @if ($user->avatar_path)
                                <img src="{{ Storage::url($user->avatar_path) }}"
                                     alt="{{ $user->name }}"
                                     class="w-24 h-24 rounded-full border-4 border-white shadow-lg object-cover">
                            @else
                                <div class="w-24 h-24 rounded-full border-4 border-white shadow-lg bg-gray-100 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-4xl text-gray-400">person</span>
                                </div>
                            @endif
                        </div>

                        <h2 class="text-xl font-bold text-gray-900">{{ $user->name }}</h2>
                        <p class="text-sm text-gray-500 mb-4">{{ $user->email }}</p>

                        <div class="space-y-3">
                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-gray-400 material-symbols-outlined">badge</span>
                                <span class="text-gray-600">Role:</span>
                                <span class="font-medium text-gray-900">
                                    {{ $user->isAdmin() ? 'Administrator' : 'Staff Member' }}
                                </span>
                            </div>

                            @if ($user->phone_number)
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="text-gray-400 material-symbols-outlined">phone</span>
                                    <span class="text-gray-600">{{ $user->phone_number }}</span>
                                </div>
                            @endif

                            @if ($user->address)
                                <div class="flex items-start gap-2 text-sm">
                                    <span class="text-gray-400 material-symbols-outlined shrink-0">location_on</span>
                                    <span class="text-gray-600">{{ $user->address }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Details Card --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- About Section --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <div class="px-6 py-5 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900">About</h3>
                    </div>
                    <div class="p-6">
                        @if ($user->bio)
                            <p class="text-gray-700 leading-relaxed">{{ $user->bio }}</p>
                        @else
                            <p class="text-gray-400 italic">No bio added yet. Click "Edit Profile" to add one.</p>
                        @endif
                    </div>
                </div>

                {{-- Account Information --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <div class="px-6 py-5 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900">Account Information</h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <div class="px-6 py-4 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Email Address</p>
                                <p class="text-gray-900">{{ $user->email }}</p>
                            </div>
                            <span class="material-symbols-outlined text-gray-300">email</span>
                        </div>
                        <div class="px-6 py-4 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Account Created</p>
                                <p class="text-gray-900">{{ $user->created_at->format('F j, Y \a\t g:i A') }}</p>
                            </div>
                            <span class="material-symbols-outlined text-gray-300">calendar_today</span>
                        </div>
                        @if ($user->email_verified_at)
                        <div class="px-6 py-4 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Email Verified</p>
                                <p class="text-gray-900">{{ $user->email_verified_at->format('F j, Y') }}</p>
                            </div>
                            <span class="material-symbols-outlined text-green-400">verified</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Roles & Permissions --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <div class="px-6 py-5 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900">Roles & Permissions</h3>
                    </div>
                    <div class="p-6">
                        @if ($user->roles->isNotEmpty())
                            <div class="flex flex-wrap gap-2">
                                @foreach ($user->roles as $role)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-700 text-sm font-medium rounded-full">
                                        <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">shield</span>
                                        {{ ucfirst($role->name) }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-400 italic">No roles assigned</p>
                        @endif

                        @if ($user->permissions->isNotEmpty())
                            <div class="mt-4">
                                <p class="text-sm font-medium text-gray-500 mb-2">Direct Permissions</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($user->permissions as $permission)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded-md">
                                            {{ $permission->display_name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
