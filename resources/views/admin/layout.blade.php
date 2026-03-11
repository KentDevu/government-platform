<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>@yield('title', 'Admin Dashboard') | GOV.PH</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet"/>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen">

    {{-- Mobile Sidebar Overlay --}}
    <div id="admin-overlay" class="fixed inset-0 z-[60] bg-black/50 hidden lg:hidden" onclick="toggleSidebar()"></div>

    {{-- Top Navbar --}}
    <nav class="sticky top-0 z-50 bg-white border-b border-gray-200 shadow-sm">
        <div class="px-4 sm:px-6 h-14 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button onclick="toggleSidebar()" class="lg:hidden p-2 -ml-2 rounded-md hover:bg-gray-100">
                    <span class="material-symbols-outlined text-xl text-gray-600">menu</span>
                </button>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                        <span class="material-symbols-outlined text-white text-lg" style="font-variation-settings: 'FILL' 1;">shield</span>
                    </div>
                    <div class="hidden sm:block leading-tight">
                        <div class="text-sm font-bold text-gray-900">GOV.PH</div>
                        <div class="text-[10px] font-medium text-gray-400 uppercase tracking-widest">Admin Panel</div>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('landing') }}" target="_blank" class="hidden sm:inline-flex items-center gap-1 text-xs text-gray-500 hover:text-blue-600 px-3 py-1.5 rounded-md hover:bg-gray-50 border border-gray-200">
                    <span class="material-symbols-outlined text-sm">open_in_new</span> View Site
                </a>
                <div class="flex items-center gap-2 pl-3 border-l border-gray-200">
                    <div class="w-7 h-7 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center">
                        <span class="material-symbols-outlined text-base" style="font-variation-settings: 'FILL' 1;">person</span>
                    </div>
                    <span class="hidden sm:inline text-xs font-medium text-gray-600">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-md" type="submit" title="Logout">
                            <span class="material-symbols-outlined text-lg">logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
        {{-- Sidebar --}}
        <aside id="admin-sidebar" class="fixed lg:sticky top-0 lg:top-14 left-0 z-[70] lg:z-auto w-64 h-screen lg:h-[calc(100vh-3.5rem)] bg-white border-r border-gray-200 overflow-y-auto -translate-x-full lg:translate-x-0 transition-transform duration-200 flex-shrink-0">
            {{-- Mobile header --}}
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 lg:hidden">
                <span class="text-sm font-semibold text-gray-900">Menu</span>
                <button onclick="toggleSidebar()" class="p-1 rounded-md hover:bg-gray-100">
                    <span class="material-symbols-outlined text-lg text-gray-500">close</span>
                </button>
            </div>

            <div class="p-3">
                {{-- Dashboard --}}
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span class="material-symbols-outlined text-[20px]" {{ request()->routeIs('admin.dashboard') ? 'style=font-variation-settings:\'FILL\'1' : '' }}>dashboard</span>
                    <span>Dashboard</span>
                </a>

                {{-- Content Section --}}
                <div class="mt-6 mb-2 px-3">
                    <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Content</span>
                </div>
                <a href="{{ route('admin.resource.index', 'hero') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-sm {{ request()->is('admin/hero*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span class="material-symbols-outlined text-[20px]">image</span>
                    <span>Hero Settings</span>
                </a>
                <a href="{{ route('admin.resource.index', 'services') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-sm {{ request()->is('admin/services*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span class="material-symbols-outlined text-[20px]">home_repair_service</span>
                    <span>Services</span>
                </a>
                <a href="{{ route('admin.resource.index', 'announcements') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-sm {{ request()->is('admin/announcements*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span class="material-symbols-outlined text-[20px]">campaign</span>
                    <span>Announcements</span>
                </a>
                <a href="{{ route('admin.resource.index', 'press-releases') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-sm {{ request()->is('admin/press-releases*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span class="material-symbols-outlined text-[20px]">newspaper</span>
                    <span>Press Releases</span>
                </a>

                {{-- Agencies Section --}}
                <div class="mt-6 mb-2 px-3">
                    <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Agencies</span>
                </div>
                <a href="{{ route('admin.resource.index', 'agency-groups') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-sm {{ request()->is('admin/agency-groups*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span class="material-symbols-outlined text-[20px]">folder</span>
                    <span>Agency Groups</span>
                </a>
                <a href="{{ route('admin.resource.index', 'agencies') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-sm {{ request()->is('admin/agencies*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span class="material-symbols-outlined text-[20px]">account_balance</span>
                    <span>Agencies</span>
                </a>

                {{-- Government Section --}}
                <div class="mt-6 mb-2 px-3">
                    <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Government</span>
                </div>
                <a href="{{ route('admin.resource.index', 'leaders') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-sm {{ request()->is('admin/leaders*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span class="material-symbols-outlined text-[20px]">person</span>
                    <span>Leaders</span>
                </a>
                <a href="{{ route('admin.resource.index', 'departments') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-sm {{ request()->is('admin/departments*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span class="material-symbols-outlined text-[20px]">domain</span>
                    <span>Departments</span>
                </a>
                <a href="{{ route('admin.resource.index', 'chambers') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-sm {{ request()->is('admin/chambers*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span class="material-symbols-outlined text-[20px]">groups</span>
                    <span>Chambers</span>
                </a>
                <a href="{{ route('admin.resource.index', 'recent-laws') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-sm {{ request()->is('admin/recent-laws*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span class="material-symbols-outlined text-[20px]">gavel</span>
                    <span>Recent Laws</span>
                </a>
                <a href="{{ route('admin.resource.index', 'courts') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-sm {{ request()->is('admin/courts*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span class="material-symbols-outlined text-[20px]">assured_workload</span>
                    <span>Courts</span>
                </a>
                <a href="{{ route('admin.resource.index', 'judiciary-functions') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-sm {{ request()->is('admin/judiciary-functions*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span class="material-symbols-outlined text-[20px]">balance</span>
                    <span>Judiciary Functions</span>
                </a>
            </div>
        </aside>

        {{-- Main Content --}}
        <main class="flex-1 min-w-0 p-4 sm:p-6 lg:p-8">
            @if (session('success'))
                <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700 flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">check_circle</span>
                    {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const overlay = document.getElementById('admin-overlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
        document.querySelectorAll('#admin-sidebar a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) toggleSidebar();
            });
        });
    </script>
</body>
</html>
