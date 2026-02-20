<!DOCTYPE html>
<html lang="id"
    x-data="{ darkMode: localStorage.getItem('darkMode') === 'true', sidebarOpen: false, mobileSidebarOpen: false }"
    x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))"
    :class="{ 'dark': darkMode }">

<head>
    <meta charset="utf-8" />
    <script>
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - SIMAPRES</title>
    <!-- Tailwind CSS CDN - For development only. Consider installing via npm for production -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
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

        /* Desktop Optimizations */
        @media (min-width: 1024px) {
            /* Enhanced hover effects */
            .desktop-card-hover {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .desktop-card-hover:hover {
                transform: translateY(-4px);
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            }

            /* Table row hover */
            .desktop-table-row:hover {
                background-color: rgba(139, 92, 246, 0.05);
            }

            /* Smooth scrollbar */
            .custom-scrollbar::-webkit-scrollbar {
                width: 10px;
                height: 10px;
            }
            .custom-scrollbar::-webkit-scrollbar-track {
                background: rgba(0, 0, 0, 0.05);
                border-radius: 5px;
            }
            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: rgba(139, 92, 246, 0.3);
                border-radius: 5px;
            }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                background: rgba(139, 92, 246, 0.5);
            }

            /* Dark mode scrollbar */
            .dark .custom-scrollbar::-webkit-scrollbar-track {
                background: rgba(255, 255, 255, 0.05);
            }
            .dark .custom-scrollbar::-webkit-scrollbar-thumb {
                background: rgba(139, 92, 246, 0.4);
            }
            .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                background: rgba(139, 92, 246, 0.6);
            }
        }

        /* Enhanced Typography */
        @media (min-width: 1280px) {
            .desktop-heading-xl {
                font-size: 2.5rem;
                line-height: 1.2;
            }
            .desktop-heading-lg {
                font-size: 2rem;
                line-height: 1.3;
            }
        }
    </style>
</head>

<body class="bg-gray-100 dark:bg-gray-900 min-h-screen overflow-x-hidden
             text-sm md:text-base lg:text-[15px] 2xl:text-[17px]">
    <div class="min-h-screen">
        @php
            $user = auth()->user();
            $currentRole = $user->getCurrentRole();
            $isPimpinan = $currentRole && $currentRole->role === 'pim<!--  -->pinan';
            $routePrefix = $isPimpinan ? 'pimpinan' : 'validator';
        @endphp
        <!-- Mobile Sidebar Backdrop -->
        <div x-show="mobileSidebarOpen" x-cloak @click="mobileSidebarOpen = false"
            class="fixed inset-0 bg-black/50 z-40 lg:hidden"></div>

        <!-- Sidebar -->
        <aside
            :class="mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed top-0 left-0 h-screen 
                    w-56 sm:w-60 md:w-64 lg:w-64 xl:w-72 2xl:w-80 
                    flex flex-col bg-white dark:bg-gray-800 
                    border-r border-gray-200 dark:border-gray-700 
                    transition-all duration-300 z-50">

            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                    </div>
                    <span class="font-bold text-gray-800 dark:text-white">SIMAPRES Admin</span>
                </div>
                <button @click="mobileSidebarOpen = false"
                    class="lg:hidden p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <!-- Validation System -->
                <div class="pt-2">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Validasi</p>
                </div>
                <a href="{{ route('admin.university.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.university.*') ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Validasi Universitas</span>
                </a>

                <!-- Data Management -->
                <div class="pt-2">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Data</p>
                </div>
                <a href="{{ route('admin.students') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.students') ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>Mahasiswa</span>
                </a>
                <a href="{{ route('admin.student-achievements') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.student-achievements') ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <span>Prestasi Mahasiswa</span>
                </a>
                <a href="{{ route('admin.validation-logs') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.validation-logs') ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Log Validasi</span>
                </a>
                <a href="{{ route('admin.users') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.users') ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Users</span>
                </a>

                <!-- Master Data -->
                <div class="pt-2">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Master Data</p>
                </div>
                <a href="{{ route('admin.categories.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.categories.*') ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <span>Kategori Prestasi</span>
                </a>
                <a href="{{ route('admin.levels.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.levels.*') ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    <span>Level Prestasi</span>
                </a>
                <a href="{{ route('admin.periods.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.periods.*') ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Periode Akademik</span>
                </a>
                <a href="{{ route('admin.sk.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.sk.*') ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Manajemen SK</span>
                </a>

                <!-- Actions -->
                <div class="pt-2">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Aksi</p>
                </div>
                <a href="{{ route('admin.submit.create') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.submit.*') ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Ajukan Prestasi</span>
                </a>

                <!-- Profile -->
                <div class="pt-2">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Akun</p>
                </div>
                <a href="{{ route('profile') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('profile') ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Profil Saya</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 min-w-0 flex flex-col
            ml-0 
            lg:ml-64
            xl:ml-72 
            2xl:ml-80
            min-h-screen 
            overflow-y-auto
            bg-gray-50 dark:bg-gray-900
            transition-all duration-300">

            <!-- Top Bar -->
            <header id="mobileNavbar"
                class="mobile-navbar bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 lg:border-l lg:border-gray-200 dark:lg:border-gray-700 py-4 flex justify-between items-center sticky top-0 z-30">
                <div class="w-full max-w-screen-xl xl:max-w-screen-2xl 2xl:max-w-[1800px] mx-auto px-4 md:px-6 lg:px-8 2xl:px-12 flex justify-between items-center">
                    <div class="flex items-center gap-4">
                        <button @click="mobileSidebarOpen = !mobileSidebarOpen"
                            class="lg:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <h1 class="text-lg sm:text-xl font-semibold text-gray-800 dark:text-white">
                            @yield('title', 'Dashboard')</h1>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-4">
                    <button @click="darkMode = !darkMode"
                        class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200">
                        <svg x-show="!darkMode" class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <svg x-show="darkMode" x-cloak class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </button>

                    <!-- Role Switcher (Inline for top bar) -->
                    @php
                        $user = auth()->user();
                        $activeRoles = $user ? $user->activeRoles()->get() : collect();
                        $currentRoleId = session('active_role_id');
                        $currentRole = $currentRoleId ? $activeRoles->firstWhere('id', $currentRoleId) : $activeRoles->first();
                    @endphp
                    @if($activeRoles->count() > 1)
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" type="button"
                                class="flex items-center gap-2 px-3 py-2 text-sm bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span
                                    class="hidden sm:inline">{{ $currentRole ? $currentRole->getRoleDisplayName() : 'Role' }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition
                                class="absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-[60]">
                                <div class="px-3 py-2 border-b border-gray-200 dark:border-gray-700">
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Beralih Role
                                    </p>
                                </div>
                                @foreach($activeRoles as $role)
                                    @if($role->id !== $currentRoleId)
                                        <form method="POST" action="{{ route('role.switch') }}" class="block">
                                            @csrf
                                            <input type="hidden" name="role_id" value="{{ $role->id }}">
                                            <button type="submit"
                                                class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                                <div class="flex items-center space-x-2">
                                                    <div
                                                        class="w-8 h-8 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="font-medium truncate">{{ $role->getRoleDisplayName() }}</p>
                                                        @if($role->level !== 'university')
                                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
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

                    <div class="hidden sm:flex items-center gap-2">
                        <span class="text-sm text-gray-600 dark:text-gray-300">{{ auth()->user()->name }}</span>
                        @if(auth()->user()->last_login_method === 'sso')
                            <span
                                class="px-2 py-0.5 text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                SSO
                            </span>
                        @endif
                    </div>
                    <form
                        action="{{ auth()->user()->last_login_method === 'sso' ? route('sso.logout') : route('logout') }}"
                        method="POST">
                        @csrf
                        <button type="submit"
                            class="p-2 sm:p-2.5 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/40 transition-all duration-200 hover:scale-105"
                            title="Logout{{ auth()->user()->last_login_method === 'sso' ? ' (SSO)' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="flex-1 w-full
                        max-w-screen-xl xl:max-w-screen-2xl
                        2xl:max-w-[1800px]
                        mx-auto
                        p-4 md:p-6 lg:p-8 xl:p-10 2xl:p-12">

                @yield('content')
            </div>
        </main>
    </div>

    <!-- Toast Notifications -->
    <x-toast-notification />

    <!-- Show session notifications -->
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                showToast('success', '{{ session('success') }}');
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                showToast('error', '{{ session('error') }}');
            });
        </script>
    @endif

    @if(session('warning'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                showToast('warning', '{{ session('warning') }}');
            });
        </script>
    @endif

    @if(session('info'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                showToast('info', '{{ session('info') }}');
            });
        </script>
    @endif

    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                @foreach($errors->all() as $error)
                    showToast('error', '{{ $error }}');
                @endforeach
            });
        </script>
    @endif

    <script src="https://instant.page/5.2.0" type="module"
        integrity="sha384-jnZyxPjiipYXnSU0ygqeac2q7CVYMbh84q0uHVRRxEtvFPiQYbXWUorga2aqZJ0z"></script>

    {{-- Mobile auto-hide navbar script --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const navbar = document.getElementById('mobileNavbar');
        if (!navbar) return;

        let lastScrollY = window.scrollY;
        let ticking = false;
        let tapTimer = null;
        const SCROLL_THRESHOLD = 5;
        const TOP_ZONE = 100;
        const TAP_SHOW_DURATION = 3000; // ms to keep navbar visible after tap

        function isMobile() {
            return window.innerWidth < 1024;
        }

        function showNavbar() {
            navbar.classList.remove('navbar-hidden');
            navbar.classList.add('navbar-visible');
        }

        function hideNavbar() {
            // Don't hide if sidebar is open
            if (document.querySelector('[x-data]') && 
                document.querySelector('[x-data]').__x &&
                document.querySelector('[x-data]').__x.$data.mobileSidebarOpen) {
                return;
            }
            navbar.classList.remove('navbar-visible');
            navbar.classList.add('navbar-hidden');
        }

        function onScroll() {
            if (!isMobile()) {
                // On desktop, always show and remove mobile classes
                navbar.classList.remove('navbar-hidden', 'navbar-visible');
                return;
            }

            const currentScrollY = window.scrollY;
            const delta = currentScrollY - lastScrollY;

            // Always show at top of page
            if (currentScrollY <= TOP_ZONE) {
                showNavbar();
                lastScrollY = currentScrollY;
                return;
            }

            // Only trigger if scroll distance exceeds threshold
            if (Math.abs(delta) < SCROLL_THRESHOLD) return;

            if (delta < 0) {
                // Scrolling UP → show navbar
                showNavbar();
            } else {
                // Scrolling DOWN → hide navbar
                hideNavbar();
            }

            lastScrollY = currentScrollY;
        }

        // Scroll handler with requestAnimationFrame for performance
        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(function() {
                    onScroll();
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });

        // Tap/touch handler — show navbar on screen tap
        document.addEventListener('touchstart', function(e) {
            if (!isMobile()) return;

            // Don't interfere with interactive elements
            const tag = e.target.tagName.toLowerCase();
            const isInteractive = tag === 'a' || tag === 'button' || tag === 'input' || 
                                  tag === 'select' || tag === 'textarea' ||
                                  e.target.closest('a') || e.target.closest('button') || 
                                  e.target.closest('form');

            showNavbar();

            // If tapping on non-interactive area, auto-hide after delay
            if (!isInteractive) {
                clearTimeout(tapTimer);
                tapTimer = setTimeout(function() {
                    if (window.scrollY > TOP_ZONE) {
                        hideNavbar();
                    }
                }, TAP_SHOW_DURATION);
            }
        }, { passive: true });

        // Reset on resize (e.g. rotating device)
        window.addEventListener('resize', function() {
            if (!isMobile()) {
                navbar.classList.remove('navbar-hidden', 'navbar-visible');
            }
        });
    });
    </script>
    
    @stack('scripts')
</body>

</html>