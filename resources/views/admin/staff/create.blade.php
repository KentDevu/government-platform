@extends('admin.layout')

@section('title', 'Create Staff')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('admin.staff.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-blue-600 mb-3">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                Back to Staff List
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Create Staff</h1>
            <p class="text-sm text-gray-500 mt-1">Only admin users can create staff accounts.</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.staff.store') }}" class="bg-white border border-gray-200 rounded-xl p-6 space-y-5">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" />
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" />
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input id="password" name="password" type="password" required
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" />
            </div>

            <div>
                <p class="block text-sm font-medium text-gray-700 mb-2">Staff Permissions</p>
                <div class="grid sm:grid-cols-2 gap-2">
                    @foreach ($permissions as $permission)
                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                   {{ in_array($permission->name, old('permissions', [])) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                            <span>{{ $permission->display_name }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="text-xs text-gray-500 mt-2">Select one or more permissions. Includes admin permission when needed.</p>
            </div>

            <div class="pt-2">
                <button type="submit" class="inline-flex items-center justify-center h-10 px-4 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">
                    Create Staff
                </button>
            </div>
        </form>
    </div>
@endsection
