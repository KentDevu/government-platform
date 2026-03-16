<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Admin Login | GOV.PH</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
</head>
<body class="bg-gray-100 font-[Inter] min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md p-8">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-2xl mb-4">
                    <span class="material-symbols-outlined text-white text-3xl">admin_panel_settings</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Admin Portal</h1>
                <p class="text-sm text-gray-500 mt-1">Sign in using password or request a login code</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                    @foreach ($errors->all() as $error)
                        <p class="text-sm text-red-600">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.send') }}">
                @csrf
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="email">Email Address</label>
                    <input class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition text-sm" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus placeholder="admin@example.com"/>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="password">Password</label>
                    <input class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition text-sm" id="password" name="password" type="password" placeholder="Enter password"/>
                </div>

                <div class="space-y-3">
                    <button class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition text-sm flex items-center justify-center gap-2" type="submit" name="login_method" value="password">
                        <span class="material-symbols-outlined text-lg">login</span>
                        Sign In with Password
                    </button>
                    <button class="w-full py-3 bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 font-semibold rounded-xl transition text-sm flex items-center justify-center gap-2" type="submit" name="login_method" value="otp">
                        <span class="material-symbols-outlined text-lg">mail</span>
                        Send Login Code
                    </button>
                </div>
            </form>
        </div>
        <p class="text-center text-xs text-gray-400 mt-6">&copy; {{ date('Y') }} GOV.PH Admin Portal</p>
    </div>
</body>
</html>
