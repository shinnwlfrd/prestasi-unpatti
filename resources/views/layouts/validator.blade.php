<!DOCTYPE html>
<html lang="id"
    x-data="{ darkMode: localStorage.getItem('darkMode') === 'true', sidebarOpen: false, mobileSidebarOpen: false }"
    x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode }">

<head>
    <meta charset="utf-8" />
    <script>
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Validator Panel') - SIMAPRES</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Mobile auto-hide navbar */
        @media (max-width: 1023px) {
            .mobile-navbar {
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                will-change: transform;
            }

            .mobile-navbar.navbar-hidden {
                transform: translateY(-100%);
            }

            .mobile-navbar.navbar-visible {
                transform: translateY(0);
            }
        }

        /* Smooth scrollbar for sidebar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(16, 185, 129, 0.3);
            border-radius: 3px;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(16, 185, 129, 0.5);
        }
    </style>
    @stack('styles')
</head>

<body class="bg-gray-100 dark:bg-gray-900 min-h-screen overflow-x-hidden text-sm md:text-base">
    <div class="min-h-screen">
        @php
            $user = auth()->user();
            $currentRole = $user ? $user->getCurrentRole() : null;
            $isPimpinan = $currentRole && $currentRole->role === 'pimpinan';
            $routePrefix = $isPimpinan ? 'pimpinan' : 'validator';
        @endphp

        <!-- Mobile Sidebar Backdrop -->
        <div x-show="mobileSidebarOpen" x-cloak @click="mobileSidebarOpen = false"
            class="fixed inset-0 bg-black/50 z-40 lg:hidden"
            x-transition:enter="transition-opacity ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

        <!-- Sidebar -->
        <aside :class="mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed top-0 left-0 h-screen w-64 flex flex-col bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-transform duration-300 z-50">

            <div
                class="p-4 border-b border-gray-100 dark:border-gray-700/50 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/20 ring-4 ring-emerald-500/10">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                    </div>
                    <div class="flex flex-col">
                        <span
                            class="font-black text-lg text-gray-900 dark:text-white leading-none tracking-tight">SIMAPRES</span>
                        <span
                            class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest mt-1">
                            {{ $isPimpinan ? 'Pimpinan' : 'Validator' }}
                        </span>
                    </div>
                </div>
                <button @click="mobileSidebarOpen = false"
                    class="lg:hidden p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 p-4 space-y-1.5 overflow-y-auto custom-scrollbar">
                @if($isPimpinan)
                    <a href="{{ route($routePrefix . '.dashboard') }}"
                        class="group flex items-center gap-3.5 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs($routePrefix . '.dashboard') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200 dark:shadow-none font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-emerald-600 dark:hover:text-emerald-400' }}">
                        <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span class="text-sm">Dashboard</span>
                    </a>
                @endif

                <div class="pt-4 pb-1">
                    <p class="px-3 text-[11px] font-black text-gray-400 uppercase tracking-[0.2em]">Data</p>
                </div>
                <a href="{{ route($routePrefix . '.students.index') }}"
                    class="group flex items-center gap-3.5 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs($routePrefix . '.students.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200 dark:shadow-none font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-emerald-600 dark:hover:text-emerald-400' }}">
                    <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="text-sm">Mahasiswa</span>
                </a>

                <div class="pt-4 pb-1">
                    <p class="px-3 text-[11px] font-black text-gray-400 uppercase tracking-[0.2em]">
                        {{ $isPimpinan ? 'Monitoring' : 'Verifikasi' }}</p>
                </div>
                <a href="{{ route($routePrefix . '.pending.index') }}"
                    class="group flex items-center gap-3.5 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs($routePrefix . '.pending.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200 dark:shadow-none font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-emerald-600 dark:hover:text-emerald-400' }}">
                    <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <span class="text-sm">{{ $isPimpinan ? 'Lihat Prestasi' : 'Verifikasi Prestasi' }}</span>
                </a>
                <a href="{{ route($routePrefix . '.history') }}"
                    class="group flex items-center gap-3.5 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs($routePrefix . '.history') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200 dark:shadow-none font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-emerald-600 dark:hover:text-emerald-400' }}">
                    <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm">Riwayat</span>
                </a>

                @unless($isPimpinan)
                    <div class="pt-4 pb-1">
                        <p class="px-3 text-[11px] font-black text-gray-400 uppercase tracking-[0.2em]">Aksi</p>
                    </div>
                    <a href="{{ route('validator.submit.form') }}"
                        class="group flex items-center gap-3.5 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('validator.submit.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200 dark:shadow-none font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-emerald-600 dark:hover:text-emerald-400' }}">
                        <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span class="text-sm">Submit Prestasi</span>
                    </a>
                @endunless

                <div class="pt-4 pb-1">
                    <p class="px-3 text-[11px] font-black text-gray-400 uppercase tracking-[0.2em]">Dokumen</p>
                </div>
                <a href="{{ route($routePrefix . '.sk.index') }}"
                    class="group flex items-center gap-3.5 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs($routePrefix . '.sk.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200 dark:shadow-none font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-emerald-600 dark:hover:text-emerald-400' }}">
                    <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="text-sm">Dokumen SK</span>
                </a>

                <div class="pt-4 pb-1">
                    <p class="px-3 text-[11px] font-black text-gray-400 uppercase tracking-[0.2em]">Akun</p>
                </div>
                <a href="{{ route('profile') }}"
                    class="group flex items-center gap-3.5 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('profile') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200 dark:shadow-none font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-emerald-600 dark:hover:text-emerald-400' }}">
                    <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm">Profil Saya</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main
            class="flex-1 min-w-0 flex flex-col lg:ml-64 min-h-screen bg-gray-50 dark:bg-gray-900 transition-all duration-300">
            <!-- Top Bar -->
            <header id="mobileNavbar"
                class="mobile-navbar bg-white/80 dark:bg-gray-800/80 backdrop-blur-md border-b border-gray-100 dark:border-gray-700 py-3.5 flex justify-between items-center sticky top-0 z-30 shadow-sm">
                <div class="w-full px-4 md:px-6 lg:px-8 flex justify-between items-center">
                    <div class="flex items-center gap-4">
                        <button @click="mobileSidebarOpen = !mobileSidebarOpen"
                            class="lg:hidden p-2.5 rounded-xl bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <h1 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">
                            @yield('title', 'Dashboard')</h1>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-4">
                        <button @click="darkMode = !darkMode"
                            class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-all duration-200 border border-gray-200 dark:border-gray-600">
                            <svg x-show="!darkMode" class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                            <svg x-show="darkMode" x-cloak class="w-5 h-5 text-yellow-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </button>

                        <!-- Role Switcher -->
                        @php
                            $activeRoles = $user ? $user->activeRoles()->get() : collect();
                            $currentRoleId = session('active_role_id');
                        @endphp
                        @if($activeRoles->count() > 1)
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" type="button"
                                    class="flex items-center gap-2 px-3 py-2.5 text-xs font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 rounded-xl hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-colors border border-emerald-100 dark:border-emerald-800">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span
                                        class="hidden sm:inline uppercase tracking-wider">{{ $currentRole ? $currentRole->getRoleDisplayName() : 'Role' }}</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div x-show="open" @click.away="open = false"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="transform opacity-0 scale-95"
                                    x-transition:enter-end="transform opacity-100 scale-100"
                                    class="absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 py-1.5 z-[60] overflow-hidden">
                                    <div class="px-4 py-3 border-b border-gray-50 dark:border-gray-700">
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.15em]">Ganti
                                            Peran</p>
                                    </div>
                                    @foreach($activeRoles as $role)
                                        @if($role->id !== $currentRoleId)
                                            <form method="POST" action="{{ route('role.switch') }}" class="block">
                                                @csrf
                                                <input type="hidden" name="role_id" value="{{ $role->id }}">
                                                <button type="submit"
                                                    class="w-full text-left px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                                                    <div class="flex items-center space-x-3">
                                                        <div
                                                            class="w-9 h-9 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm transition-transform group-hover:scale-110">
                                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                            </svg>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-sm font-bold text-gray-900 dark:text-white truncate">
                                                                {{ $role->getRoleDisplayName() }}</p>
                                                            @if($role->level !== 'university')
                                                                <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate mt-0.5">
                                                                    {{ $role->getScopeDescription() }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </button>
                                            </form>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div
                            class="hidden sm:flex items-center gap-3 px-3 py-1.5 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-100 dark:border-gray-600">
                            <div class="flex flex-col items-end">
                                <span
                                    class="text-xs font-bold text-gray-900 dark:text-white leading-none">{{ auth()->user()->name }}</span>
                            </div>
                            <div
                                class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 font-bold text-xs ring-2 ring-white dark:ring-gray-800">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                        </div>

                        <form
                            action="{{ auth()->user()->last_login_method === 'sso' ? route('sso.logout') : route('logout') }}"
                            method="POST">
                            @csrf
                            <button type="submit"
                                class="p-2.5 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/40 transition-all duration-200 hover:scale-105 border border-red-100 dark:border-red-900/30 group">
                                <svg class="w-5 h-5 transition-transform group-hover:rotate-12" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="flex-1 w-full p-4 md:p-6 lg:p-8">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Toast Notifications -->
    <x-toast-notification />

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Toast notifications
            @foreach(['success', 'error', 'warning', 'info'] as $type)
                @if(session($type))
                    showToast('{{ $type }}', '{{ session($type) }}');
                @endif
            @endforeach

            @if($errors->any())
                @foreach($errors->all() as $error)
                    showToast('error', '{{ $error }}');
                @endforeach
            @endif

        // Mobile auto-hide navbar script
        const navbar = document.getElementById('mobileNavbar');
            if (!navbar) return;

            let lastScrollY = window.scrollY;
            let ticking = false;
            const SCROLL_THRESHOLD = 5;
            const TOP_ZONE = 100;

            function isMobile() { return window.innerWidth < 1024; }
            function showNavbar() { navbar.classList.remove('navbar-hidden'); navbar.classList.add('navbar-visible'); }
            function hideNavbar() {
                if (Alpine.store('sidebarOpen') || (document.querySelector('[x-data]') && document.querySelector('[x-data]').__x && document.querySelector('[x-data]').__x.$data.mobileSidebarOpen)) return;
                navbar.classList.remove('navbar-visible');
                navbar.classList.add('navbar-hidden');
            }

            window.addEventListener('scroll', function () {
                if (!ticking) {
                    window.requestAnimationFrame(function () {
                        if (isMobile()) {
                            const currentScrollY = window.scrollY;
                            const delta = currentScrollY - lastScrollY;
                            if (currentScrollY <= TOP_ZONE) showNavbar();
                            else if (Math.abs(delta) > SCROLL_THRESHOLD) {
                                if (delta < 0) showNavbar();
                                else hideNavbar();
                            }
                            lastScrollY = currentScrollY;
                        } else {
                            navbar.classList.remove('navbar-hidden', 'navbar-visible');
                        }
                        ticking = false;
                    });
                    ticking = true;
                }
            }, { passive: true });

            window.addEventListener('resize', function () {
                if (!isMobile()) navbar.classList.remove('navbar-hidden', 'navbar-visible');
            });
        });
    </script>
    @stack('scripts')
</body>

</html>