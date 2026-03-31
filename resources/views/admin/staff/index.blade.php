@extends('admin.layout')

@section('title', 'Staff')

@section('content')
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Staff</h1>
                <p class="text-sm text-gray-500 mt-1">Manage staff accounts and their permissions.</p>
            </div>
            <a href="{{ route('admin.staff.create') }}" class="inline-flex items-center gap-2 h-10 px-4 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">
                <span class="material-symbols-outlined text-lg">group_add</span>
                Create Staff
            </a>
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

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Avatar</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Name</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Email</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Permissions</th>
                        <th class="text-right px-4 py-3 font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($staff as $member)
                        <tr class="border-b border-gray-100 last:border-b-0">
                            <td class="px-4 py-3">
                                @if ($member->avatar_path)
                                    <img src="{{ Storage::url($member->avatar_path) }}"
                                         alt="{{ $member->name }}"
                                         class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-sm text-gray-400">person</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $member->name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $member->email }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse ($member->permissions as $permission)
                                        <span class="inline-flex items-center px-2 py-1 rounded-md bg-blue-50 text-blue-700 text-xs font-medium">{{ $permission->label }}</span>
                                    @empty
                                        <span class="text-xs text-gray-400">No permissions</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.staff.edit', $member) }}" class="inline-flex items-center gap-1 h-8 px-3 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">
                                        <span class="material-symbols-outlined text-base">edit</span>
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.staff.destroy', $member) }}" onsubmit="return confirm('Delete this staff account?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 h-8 px-3 rounded-md border border-red-200 text-red-600 hover:bg-red-50">
                                            <span class="material-symbols-outlined text-base">delete</span>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-500">No staff accounts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
