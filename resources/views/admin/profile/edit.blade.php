@extends('admin.layout')

@section('title', 'Edit Profile')

@section('content')
    <div>
        {{-- Header --}}
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('admin.profile') }}" class="p-2 rounded-lg hover:bg-gray-100 text-gray-500 hover:text-gray-700">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Profile</h1>
                <p class="text-sm text-gray-500 mt-1">Update your personal information</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="p-6 space-y-6">
                    {{-- Avatar Upload --}}
                    <div class="flex items-center gap-6 pb-6 border-b border-gray-100">
                        <div class="relative group">
                            @if ($user->avatar_path)
                                <img src="{{ Storage::url($user->avatar_path) }}"
                                     alt="{{ $user->name }}"
                                     class="w-24 h-24 rounded-full object-cover ring-4 ring-gray-50">
                            @else
                                <div class="w-24 h-24 rounded-full ring-4 ring-gray-50 bg-gray-100 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-4xl text-gray-400">person</span>
                                </div>
                            @endif
                            <label class="absolute inset-0 flex items-center justify-center bg-black/50 text-white rounded-full opacity-0 group-hover:opacity-100 cursor-pointer transition-opacity">
                                <span class="material-symbols-outlined">camera_alt</span>
                                <input type="file" name="avatar" accept="image/*" class="hidden" id="avatar-input">
                            </label>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Profile Photo</p>
                            <p class="text-xs text-gray-500 mt-1">Click on the photo to upload a new one (2MB max)</p>
                            @error('avatar')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Full Name</label>
                        <input type="text"
                               name="name"
                               id="name"
                               value="{{ old('name', $user->name) }}"
                               class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                               required>
                        @error('name')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
                        <input type="email"
                               name="email"
                               id="email"
                               value="{{ old('email', $user->email) }}"
                               class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                               required>
                        @error('email')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Phone Number --}}
                        <div>
                            <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1.5">Phone Number</label>
                            <input type="text"
                                   name="phone_number"
                                   id="phone_number"
                                   value="{{ old('phone_number', $user->phone_number) }}"
                                   class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                                   placeholder="+63 912 345 6789">
                            @error('phone_number')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Address --}}
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-1.5">Address</label>
                            <input type="text"
                                   name="address"
                                   id="address"
                                   value="{{ old('address', $user->address) }}"
                                   class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                                   placeholder="City, Province">
                            @error('address')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Bio --}}
                    <div>
                        <label for="bio" class="block text-sm font-medium text-gray-700 mb-1.5">About</label>
                        <textarea name="bio"
                                  id="bio"
                                  rows="4"
                                  class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all resize-none"
                                  placeholder="Tell us a little about yourself...">{{ old('bio', $user->bio) }}</textarea>
                        <p class="text-xs text-gray-400 mt-1">{{ strlen(old('bio', $user->bio ?? '')) }}/1000 characters</p>
                        @error('bio')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                    <a href="{{ route('admin.profile') }}"
                       class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        <span class="material-symbols-outlined text-lg">save</span>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('avatar-input').addEventListener('change', function(e) {
            if (this.files.length > 0) {
                // Show preview
                const file = this.files[0];
                const reader = new FileReader();
                reader.onload = (event) => {
                    // Update preview immediately
                    const preview = this.closest('.group').querySelector('img') || this.closest('.group').querySelector('div');
                    if (preview.tagName === 'IMG') {
                        preview.src = event.target.result;
                    } else {
                        const img = document.createElement('img');
                        img.src = event.target.result;
                        img.className = 'w-24 h-24 rounded-full object-cover ring-4 ring-gray-50';
                        preview.replaceWith(img);
                    }
                };
                reader.readAsDataURL(file);
                
                // Auto-submit form
                this.closest('form').submit();
            }
        });
    </script>
@endsection
