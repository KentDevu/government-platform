<!DOCTYPE html>
<html class="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>@yield('title', 'GOV.PH | Official Philippine Government Portal')</title>

    {{-- Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />

    {{-- Styles --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gov-background-light font-sans text-slate-800 antialiased transition-colors duration-300">
<div class="relative flex min-h-screen flex-col overflow-x-hidden">

    {{-- ==================== NAVBAR ==================== --}}
    <nav class="sticky top-0 z-50 w-full border-b border-slate-200 bg-white/95 backdrop-blur-md">
        <div class="max-w-[1280px] mx-auto px-4 lg:px-10 h-20 flex items-center justify-between gap-8">
            <div class="flex items-center gap-6">
                {{-- Logo --}}
                <a href="{{ route('landing') }}" class="flex items-center gap-3">
                    <div class="size-10 flex items-center justify-center text-gov-primary">
                        <svg class="w-full h-full" fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                            <path d="M39.5563 34.1455V13.8546C39.5563 15.708 36.8773 17.3437 32.7927 18.3189C30.2914 18.916 27.263 19.2655 24 19.2655C20.737 19.2655 17.7086 18.916 15.2073 18.3189C11.1227 17.3437 8.44365 15.708 8.44365 13.8546V34.1455C8.44365 35.9988 11.1227 37.6346 15.2073 38.6098C17.7086 39.2069 20.737 39.5564 24 39.5564C27.263 39.5564 30.2914 39.2069 32.7927 38.6098C36.8773 37.6346 39.5563 35.9988 39.5563 34.1455Z" fill="currentColor"></path>
                            <path clip-rule="evenodd" d="M10.4485 13.8519C10.4749 13.9271 10.6203 14.246 11.379 14.7361C12.298 15.3298 13.7492 15.9145 15.6717 16.3735C18.0007 16.9296 20.8712 17.2655 24 17.2655C27.1288 17.2655 29.9993 16.9296 32.3283 16.3735C34.2508 15.9145 35.702 15.3298 36.621 14.7361C37.3796 14.246 37.5251 13.9271 37.5515 13.8519C37.5287 13.7876 37.4333 13.5973 37.0635 13.2931C36.5266 12.8516 35.6288 12.3647 34.343 11.9175C31.79 11.0295 28.1333 10.4437 24 10.4437C19.8667 10.4437 16.2099 11.0295 13.657 11.9175C12.3712 12.3647 11.4734 12.8516 10.9365 13.2931C10.5667 13.5973 10.4713 13.7876 10.4485 13.8519ZM37.5563 18.7877C36.3176 19.3925 34.8502 19.8839 33.2571 20.2642C30.5836 20.9025 27.3973 21.2655 24 21.2655C20.6027 21.2655 17.4164 20.9025 14.7429 20.2642C13.1498 19.8839 11.6824 19.3925 10.4436 18.7877V34.1275C10.4515 34.1545 10.5427 34.4867 11.379 35.027C12.298 35.6207 13.7492 36.2054 15.6717 36.6644C18.0007 37.2205 20.8712 37.5564 24 37.5564C27.1288 37.5564 29.9993 37.2205 32.3283 36.6644C34.2508 36.2054 35.702 35.6207 36.621 35.027C37.4573 34.4867 37.5485 34.1546 37.5563 34.1275V18.7877ZM41.5563 13.8546V34.1455C41.5563 36.1078 40.158 37.5042 38.7915 38.3869C37.3498 39.3182 35.4192 40.0389 33.2571 40.5551C30.5836 41.1934 27.3973 41.5564 24 41.5564C20.6027 41.5564 17.4164 41.1934 14.7429 40.5551C12.5808 40.0389 10.6502 39.3182 9.20848 38.3869C7.84205 37.5042 6.44365 36.1078 6.44365 34.1455L6.44365 13.8546C6.44365 12.2684 7.37223 11.0454 8.39581 10.2036C9.43325 9.3505 10.8137 8.67141 12.343 8.13948C15.4203 7.06909 19.5418 6.44366 24 6.44366C28.4582 6.44366 32.5797 7.06909 35.657 8.13948C37.1863 8.67141 38.5667 9.3505 39.6042 10.2036C40.6278 11.0454 41.5563 12.2684 41.5563 13.8546Z" fill="currentColor" fill-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="flex flex-col">
                        <h1 class="font-display text-xl font-bold tracking-[-0.03em] text-slate-900 leading-none">GOV.PH</h1>
                        <span class="text-[10px] font-semibold uppercase tracking-[0.12em] text-gov-secondary">Republic of the Philippines</span>
                    </div>
                </a>

                {{-- Nav Links --}}
                <div class="hidden xl:flex items-center gap-6">
                    @php
                        $currentRoute = Route::currentRouteName();
                        $tabs = [
                            ['label' => 'Services', 'route' => 'services'],
                            ['label' => 'Agencies', 'route' => 'agencies'],
                            ['label' => 'Executive', 'route' => 'executive'],
                            ['label' => 'Legislative', 'route' => 'legislative'],
                            ['label' => 'Judiciary', 'route' => 'judiciary'],
                        ];
                    @endphp
                    @foreach ($tabs as $tab)
                        <a class="nav-link-animated text-[13px] font-medium transition-colors {{ $currentRoute === $tab['route'] ? 'text-gov-primary active' : 'text-slate-600 hover:text-gov-primary' }}"
                           href="{{ route($tab['route']) }}">
                            {{ $tab['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Search & Sign In --}}
            <div class="flex flex-1 justify-end items-center gap-4 max-w-md">
                <div class="relative w-full group hidden md:block">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-gov-primary transition-colors">search</span>
                    <input class="w-full bg-slate-100 border-none rounded-lg pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-gov-primary/20 focus:bg-white transition-all" placeholder="Search services, agencies..." type="text" />
                </div>
                <a href="{{ route('admin.login') }}" class="hidden md:flex h-10 px-5 items-center justify-center rounded-lg bg-gov-primary text-white text-sm font-bold hover:shadow-lg hover:shadow-gov-primary/30 transition-all whitespace-nowrap">
                    Sign In
                </a>
                {{-- Hamburger Button (mobile only) --}}
                <button id="sidebar-open" class="xl:hidden flex items-center justify-center size-10 rounded-lg hover:bg-slate-100 transition-colors" aria-label="Open menu">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>
            </div>
        </div>
    </nav>

    {{-- ==================== MOBILE SIDEBAR ==================== --}}
    <div id="sidebar-backdrop" class="fixed inset-0 z-[60] bg-black/50 backdrop-blur-sm hidden" aria-hidden="true"></div>
    <aside id="sidebar" class="fixed top-0 right-0 z-[70] h-full w-80 max-w-[85vw] bg-white shadow-2xl translate-x-full transition-transform duration-300 ease-in-out">
        <div class="flex items-center justify-between p-5 border-b border-slate-200">
            <div class="flex items-center gap-2">
                <div class="size-8 text-gov-primary">
                    <svg class="w-full h-full" fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                        <path d="M39.5563 34.1455V13.8546C39.5563 15.708 36.8773 17.3437 32.7927 18.3189C30.2914 18.916 27.263 19.2655 24 19.2655C20.737 19.2655 17.7086 18.916 15.2073 18.3189C11.1227 17.3437 8.44365 15.708 8.44365 13.8546V34.1455C8.44365 35.9988 11.1227 37.6346 15.2073 38.6098C17.7086 39.2069 20.737 39.5564 24 39.5564C27.263 39.5564 30.2914 39.2069 32.7927 38.6098C36.8773 37.6346 39.5563 35.9988 39.5563 34.1455Z" fill="currentColor"></path>
                    </svg>
                </div>
                <span class="text-lg font-extrabold tracking-tighter">GOV.PH</span>
            </div>
            <button id="sidebar-close" class="size-10 flex items-center justify-center rounded-lg hover:bg-slate-100 transition-colors" aria-label="Close menu">
                <span class="material-symbols-outlined text-2xl">close</span>
            </button>
        </div>

        {{-- Mobile Search --}}
        <div class="p-5 border-b border-slate-100">
            <div class="relative w-full group">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                <input class="w-full bg-slate-100 border-none rounded-lg pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-gov-primary/20 focus:bg-white transition-all" placeholder="Search services, agencies..." type="text" />
            </div>
        </div>

        {{-- Mobile Nav Links --}}
        <nav class="p-5">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Navigation</p>
            @php
                $sidebarRoute = Route::currentRouteName();
                $sidebarTabs = [
                    ['label' => 'Home', 'route' => 'landing', 'icon' => 'home'],
                    ['label' => 'Services', 'route' => 'services', 'icon' => 'grid_view'],
                    ['label' => 'Agencies', 'route' => 'agencies', 'icon' => 'account_balance'],
                    ['label' => 'Executive', 'route' => 'executive', 'icon' => 'gavel'],
                    ['label' => 'Legislative', 'route' => 'legislative', 'icon' => 'balance'],
                    ['label' => 'Judiciary', 'route' => 'judiciary', 'icon' => 'assured_workload'],
                ];
            @endphp
            <div class="space-y-1">
                @foreach ($sidebarTabs as $tab)
                    <a href="{{ route($tab['route']) }}"
                       class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-colors
                              {{ $sidebarRoute === $tab['route'] ? 'bg-gov-primary/10 text-gov-primary' : 'text-slate-700 hover:bg-slate-50 hover:text-gov-primary' }}">
                        <span class="material-symbols-outlined text-xl">{{ $tab['icon'] }}</span>
                        {{ $tab['label'] }}
                    </a>
                @endforeach
            </div>
        </nav>

        {{-- Mobile Sign In --}}
        <div class="absolute bottom-0 left-0 right-0 p-5 border-t border-slate-100">
            <a href="{{ route('admin.login') }}" class="w-full h-12 rounded-xl bg-gov-primary text-white font-bold text-sm hover:shadow-lg hover:shadow-gov-primary/30 transition-all flex items-center justify-center">
                Sign In
            </a>
        </div>
    </aside>

    {{-- ==================== PAGE CONTENT ==================== --}}
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- ==================== FOOTER ==================== --}}
    <footer class="bg-slate-900 text-slate-400 pt-16 pb-8 border-t-4 border-gov-primary">
        <div class="max-w-[1280px] mx-auto px-4 lg:px-10">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
                {{-- Brand --}}
                <div>
                    <div class="flex items-center gap-3 mb-6">
                        <div class="size-8 text-white">
                            <svg fill="currentColor" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                                <path d="M39.5563 34.1455V13.8546C39.5563 15.708 36.8773 17.3437 32.7927 18.3189C30.2914 18.916 27.263 19.2655 24 19.2655C20.737 19.2655 17.7086 18.916 15.2073 18.3189C11.1227 17.3437 8.44365 15.708 8.44365 13.8546V34.1455C8.44365 35.9988 11.1227 37.6346 15.2073 38.6098C17.7086 39.2069 20.737 39.5564 24 39.5564C27.263 39.5564 30.2914 39.2069 32.7927 38.6098C36.8773 37.6346 39.5563 35.9988 39.5563 34.1455Z"></path>
                            </svg>
                        </div>
                        <h2 class="font-display text-white text-lg font-bold tracking-[-0.02em]">GOV.PH</h2>
                    </div>
                    <p class="text-[13px] leading-[1.7] mb-6 text-slate-400">
                        The official portal of the Philippine Government. Providing citizens with easy access to public information and government services.
                    </p>
                    <div class="flex gap-4">
                        <a class="size-10 rounded-full bg-slate-800 flex items-center justify-center hover:bg-gov-primary transition-colors" href="#">
                            <span class="material-symbols-outlined text-white">social_leaderboard</span>
                        </a>
                        <a class="size-10 rounded-full bg-slate-800 flex items-center justify-center hover:bg-gov-primary transition-colors" href="#">
                            <svg class="size-5 fill-white" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path></svg>
                        </a>
                        <a class="size-10 rounded-full bg-slate-800 flex items-center justify-center hover:bg-gov-primary transition-colors" href="#">
                            <span class="material-symbols-outlined text-white">mail</span>
                        </a>
                    </div>
                </div>

                {{-- Quick Links --}}
                <div>
                    <h4 class="font-display text-white font-semibold mb-6 text-[15px]">Government Quick Links</h4>
                    <ul class="space-y-3.5 text-[13px]">
                        <li><a class="hover:text-gov-primary transition-colors" href="#">Official Gazette</a></li>
                        <li><a class="hover:text-gov-primary transition-colors" href="#">Open Data Portal</a></li>
                        <li><a class="hover:text-gov-primary transition-colors" href="#">Transparency Seal</a></li>
                        <li><a class="hover:text-gov-primary transition-colors" href="#">Freedom of Information</a></li>
                    </ul>
                </div>

                {{-- Contact --}}
                <div>
                    <h4 class="font-display text-white font-semibold mb-6 text-[15px]">Support & Contact</h4>
                    <ul class="space-y-3.5 text-[13px]">
                        <li class="flex gap-2"><span class="material-symbols-outlined text-sm text-gov-primary">call</span> 8-888-8888</li>
                        <li class="flex gap-2"><span class="material-symbols-outlined text-sm text-gov-primary">pin_drop</span> Malacañan Palace, JP Laurel St., San Miguel, Manila</li>
                        <li class="flex gap-2"><span class="material-symbols-outlined text-sm text-gov-primary">help</span> Help & Support Center</li>
                    </ul>
                </div>

                {{-- Transparency Seals --}}
                <div class="flex flex-col items-center lg:items-end">
                    <h4 class="font-display text-white font-semibold mb-6 text-[15px]">Transparency Seals</h4>
                    <div class="flex gap-6">
                        <img alt="Transparency Seal" class="h-20 w-auto hover:scale-105 transition-transform" src="/assets/img/seal-transparency.svg" />
                        <img alt="FOI Seal" class="h-20 w-auto hover:scale-105 transition-transform" src="/assets/img/seal-foi.svg" />
                    </div>
                </div>
            </div>

            <div class="pt-8 border-t border-slate-800 flex flex-col md:flex-row items-center justify-between gap-4 text-xs">
                <p>&copy; {{ date('Y') }} GOV.PH | Republic of the Philippines. All rights reserved.</p>
                <div class="flex gap-6">
                    <a class="hover:text-white transition-colors" href="#">Privacy Policy</a>
                    <a class="hover:text-white transition-colors" href="#">Terms of Use</a>
                    <a class="hover:text-white transition-colors" href="#">Accessibility</a>
                </div>
            </div>
        </div>
    </footer>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        const openBtn = document.getElementById('sidebar-open');
        const closeBtn = document.getElementById('sidebar-close');

        function openSidebar() {
            sidebar.classList.remove('translate-x-full');
            sidebar.classList.add('translate-x-0');
            backdrop.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.add('translate-x-full');
            sidebar.classList.remove('translate-x-0');
            backdrop.classList.add('hidden');
            document.body.style.overflow = '';
        }

        openBtn.addEventListener('click', openSidebar);
        closeBtn.addEventListener('click', closeSidebar);
        backdrop.addEventListener('click', closeSidebar);
    });
</script>
</body>
</html>
