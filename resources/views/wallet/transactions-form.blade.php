@extends('admin.layout')

@section('title', 'Wallet Transactions')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-3xl">
    <!-- Header --
>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Generate Wallet Transactions</h1>
        <p class="text-gray-600 mt-2">Create mock wallet transactions for testing and demonstration</p>
    </div>

    <!-- Success Message -->
    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-green-800">✓ {{ session('success') }}</p>
        </div>
    @endif

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-md p-8">
        <form action="{{ route('admin.wallet.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Max Transactions Field -->
            <div>
                <label for="max_transactions" class="block text-sm font-medium text-gray-700 mb-2">
                    Max Transactions Per User
                </label>
                <input
                    type="number"
                    id="max_transactions"
                    name="max_transactions"
                    min="1"
                    max="100"
                    value="{{ old('max_transactions', 10) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="10"
                >
                <p class="text-sm text-gray-500 mt-1">Enter a number between 1 and 100</p>
                @error('max_transactions')
                    <p class="text-red-600 mt-2 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <!-- Role Selector Field -->
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                    Target Role
                </label>
                <select
                    id="role"
                    name="role"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    @foreach ($roles as $role)
                        <option value="{{ $role }}" {{ old('role', 'All') === $role ? 'selected' : '' }}>
                            {{ $role }}
                        </option>
                    @endforeach
                </select>
                <p class="text-sm text-gray-500 mt-1">Select "All" to generate for all users</p>
                @error('role')
                    <p class="text-red-600 mt-2 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="flex gap-4 pt-6">
                <button
                    type="submit"
                    class="flex-1 px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors"
                >
                    Generate Transactions
                </button>
                <a
                    href="{{ route('admin.dashboard') }}"
                    class="flex-1 px-6 py-3 bg-gray-200 text-gray-800 font-medium rounded-lg hover:bg-gray-300 transition-colors text-center"
                >
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <!-- Information Box -->
    <div class="mt-8 p-6 bg-blue-50 border border-blue-200 rounded-lg">
        <h3 class="font-semibold text-blue-900 mb-3">How it works:</h3>
        <ul class="space-y-2 text-sm text-blue-800">
            <li>✓ Creates random transactions (debit &amp; credit) for selected users</li>
            <li>✓ Each user gets 1 to N random transactions (where N is your max)</li>
            <li>✓ Transactions are created as queue jobs - processing happens in background</li>
            <li>✓ After creating transactions, automatically calculates and updates user balances</li>
            <li>✓ User balance = Sum of all credits - Sum of all debits</li>
            <li>✓ You can run this form multiple times to generate more transactions</li>
        </ul>
    </div>
</div>
@endsection
