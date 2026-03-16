<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Verify Code | GOV.PH</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
</head>
<body class="bg-gray-100 font-[Inter] min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md p-8">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-green-600 rounded-2xl mb-4">
                    <span class="material-symbols-outlined text-white text-3xl">verified_user</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Enter Verification Code</h1>
                <p class="text-sm text-gray-500 mt-1">We sent a 6-digit code to</p>
                <p class="text-sm font-semibold text-gray-700">{{ $email }}</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                    @foreach ($errors->all() as $error)
                        <p class="text-sm text-red-600">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.verify.submit') }}">
                @csrf
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="otp">Verification Code</label>
                    <input
                        class="w-full px-4 py-4 rounded-xl border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 outline-none transition text-center text-2xl font-bold tracking-[0.5em] placeholder-gray-300"
                        id="otp"
                        name="otp"
                        type="text"
                        maxlength="6"
                        pattern="[0-9]{6}"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        required
                        autofocus
                        placeholder="000000"
                    />
                    <p class="text-xs text-gray-400 mt-2 text-center">Code expires in 10 minutes</p>
                </div>
                <button class="w-full py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition text-sm flex items-center justify-center gap-2" type="submit">
                    <span class="material-symbols-outlined text-lg">login</span>
                    Verify & Sign In
                </button>
            </form>

            <div class="mt-6 text-center">
                <form method="POST" action="{{ route('admin.login.send') }}" class="inline">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}"/>
                    <button type="submit" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                        Resend code
                    </button>
                </form>
                <span class="text-gray-300 mx-2">|</span>
                <a href="{{ route('admin.login') }}" class="text-sm text-gray-500 hover:text-gray-700">
                    Use different email
                </a>
            </div>
        </div>
        <p class="text-center text-xs text-gray-400 mt-6">&copy; {{ date('Y') }} GOV.PH Admin Portal</p>
    </div>
</body>
</html>
