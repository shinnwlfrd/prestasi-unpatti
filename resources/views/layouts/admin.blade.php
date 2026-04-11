<!DOCTYPE html>
<html lang="id"
    x-data="{ darkMode: localStorage.getItem('darkMode') === 'true', sidebarOpen: false, mobileSidebarOpen: false }"
    x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode }">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - SIMAPRES</title>
    @include('partials.pwa-meta')

    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Dark mode script -->
    <script>
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }
    </script>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif']
                    },
                    colors: {
                        primary: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81'
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Active Menu Indicator */
        .menu-active {
            position: relative;
        }

        .menu-active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 60%;
            background: #4f46e5;
            border-radius: 0 3px 3px 0;
        }

        /* Smooth Transitions */
        * {
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Background Pattern - removed for clean look */

        /* Dashboard Optimizations */
        @media (min-width: 1024px) {
            .dash-card {
                padding: 1.5rem;
                border-radius: 0.75rem;
            }

            .dash-stat-value {
                font-size: 2rem;
                line-height: 1.2;
                letter-spacing: -0.02em;
                font-weight: 700;
            }

            .dash-stat-label {
                font-size: 0.75rem;
                letter-spacing: 0.025em;
                font-weight: 500;
            }

            .dash-chart-container {
                height: 20rem !important;
            }

            .dash-chart-container-lg {
                height: 24rem !important;
            }
        }

        @media (min-width: 1280px) {
            .dash-card {
                padding: 1.75rem !important;
            }

            .dash-stat-value {
                font-size: 2.5rem !important;
            }

            .dash-chart-container {
                height: 22rem !important;
            }

            .dash-chart-container-lg {
                height: 26rem !important;
            }
        }

        /* Glass Effect - simplified */
        .glass {
            background: rgba(255, 255, 255, 0.95);
        }

        .dark .glass {
            background: rgba(17, 24, 39, 0.95);
        }

        /* Hover Effects - simplified */
        .hover-lift {
            transition: box-shadow 0.15s ease;
        }
    </style>
</head>


<body class="bg-gray-50 dark:bg-[#0a0a0a] min-h-screen overflow-x-hidden antialiased">
    <div class="min-h-screen">
        @php
            $user = auth()->user();
            $currentRole = $user ? $user->getCurrentRole() : null;
        @endphp

        <!-- Mobile Sidebar Backdrop -->
        <div x-show="mobileSidebarOpen" x-cloak @click="mobileSidebarOpen = false"
            x-transition:enter="transition-opacity duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden"></div>

        <!-- Sidebar -->
        <aside :class="mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'" class="fixed top-0 left-0 h-screen w-64 lg:w-64 xl:w-72
                      flex flex-col bg-white dark:bg-gray-900 
                      border-r border-gray-200/50 dark:border-gray-800/50
                      transition-transform duration-200 z-50">

            <!-- Logo -->
            <div class="p-5 border-b border-gray-100 dark:border-gray-800/50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 flex items-center justify-center p-0.5">
                        <img src="{{ asset('img/logo.png') }}" class="w-full h-full object-contain" alt="Logo UNPATTI">
                    </div>
                    <span class="font-bold text-lg text-gray-900 dark:text-white">SIMAPRES</span>
                </div>
                <button @click="mobileSidebarOpen = false"
                    class="lg:hidden p-1.5 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>


            <!-- Navigation -->
            <nav class="p-4 space-y-2 overflow-y-auto h-[calc(100vh-70px)]">
                <a href="{{ route('admin.dashboard') }}"
                    class="group flex items-center gap-3.5 px-4 py-3.5 rounded-lg transition-all duration-150 {{ request()->routeIs('admin.dashboard') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 font-semibold menu-active' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/50' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <div class="pt-3">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Verifikasi</p>
                </div>
                <a href="{{ route('admin.university.index') }}"
                    class="group flex items-center gap-3.5 px-4 py-3.5 rounded-lg transition-all duration-150 {{ request()->routeIs('admin.university.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 font-semibold menu-active' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/50' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Verifikasi Universitas</span>
                </a>

                <div class="pt-3">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Data</p>
                </div>
                <a href="{{ route('admin.students') }}"
                    class="group flex items-center gap-3.5 px-4 py-3.5 rounded-lg transition-all duration-150 {{ request()->routeIs('admin.students') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 font-semibold menu-active' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/50' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>Mahasiswa</span>
                </a>
                <a href="{{ route('admin.student-achievements') }}"
                    class="group flex items-center gap-3.5 px-4 py-3.5 rounded-lg transition-all duration-150 {{ request()->routeIs('admin.student-achievements') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 font-semibold menu-active' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/50' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <span>Prestasi</span>
                </a>
                <a href="{{ route('admin.validation-logs') }}"
                    class="group flex items-center gap-3.5 px-4 py-3.5 rounded-lg transition-all duration-150 {{ request()->routeIs('admin.validation-logs') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 font-semibold menu-active' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/50' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Log Verifikasi</span>
                </a>
                <a href="{{ route('admin.users') }}"
                    class="group flex items-center gap-3.5 px-4 py-3.5 rounded-lg transition-all duration-150 {{ request()->routeIs('admin.users') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 font-semibold menu-active' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/50' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Users</span>
                </a>

                <div class="pt-3">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Master Data</p>
                </div>
                <a href="{{ route('admin.categories.index') }}"
                    class="group flex items-center gap-3.5 px-4 py-3.5 rounded-lg transition-all duration-150 {{ request()->routeIs('admin.categories.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 font-semibold menu-active' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/50' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <span>Kategori</span>
                </a>
                <a href="{{ route('admin.levels.index') }}"
                    class="group flex items-center gap-3.5 px-4 py-3.5 rounded-lg transition-all duration-150 {{ request()->routeIs('admin.levels.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 font-semibold menu-active' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/50' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    <span>Level</span>
                </a>
                <a href="{{ route('admin.periods.index') }}"
                    class="group flex items-center gap-3.5 px-4 py-3.5 rounded-lg transition-all duration-150 {{ request()->routeIs('admin.periods.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 font-semibold menu-active' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/50' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Periode</span>
                </a>
                <a href="{{ route('admin.sk.index') }}"
                    class="group flex items-center gap-3.5 px-4 py-3.5 rounded-lg transition-all duration-150 {{ request()->routeIs('admin.sk.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 font-semibold menu-active' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/50' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Manajemen SK</span>
                </a>

                <div class="pt-3">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Aksi</p>
                </div>
                <a href="{{ route('admin.submit.create') }}"
                    class="group flex items-center gap-3.5 px-4 py-3.5 rounded-lg transition-all duration-150 {{ request()->routeIs('admin.submit.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 font-semibold menu-active' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/50' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Ajukan Prestasi</span>
                </a>

                <div class="pt-3">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Akun</p>
                </div>
                <a href="{{ route('profile') }}"
                    class="group flex items-center gap-3.5 px-4 py-3.5 rounded-lg transition-all duration-150 {{ request()->routeIs('profile') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 font-semibold menu-active' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/50' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Profil Saya</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="lg:ml-64 xl:ml-72 min-h-screen transition-all duration-200">
            <!-- Top Bar -->
            <header class="glass sticky top-0 z-30 border-b border-gray-200/50 dark:border-gray-800/50">
                <div class="px-4 lg:px-6 py-4 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <button @click="mobileSidebarOpen = true"
                            class="lg:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">@yield('title', 'Dashboard')
                        </h1>
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- Dark Mode Toggle -->
                        <button @click="darkMode = !darkMode"
                            class="p-2 rounded-lg hover:bg-gray-100 shadow-lg dark:hover:bg-gray-800 transition-colors">
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



                        <!-- User Menu -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" type="button"
                                class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-200 group">
                                <div
                                    class="w-9 h-9 bg-primary-600 rounded-xl flex items-center justify-center text-white text-sm font-bold">
                                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                                </div>
                                <div class="hidden md:block text-left">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white leading-tight">
                                        {{ Str::limit(auth()->user()->name ?? 'User', 20) }}
                                    </p>
                                    @php
                                        $currentRole = auth()->user()->getCurrentRole();
                                    @endphp
                                    @if($currentRole)
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $currentRole->getRoleDisplayName() }}
                                        </p>
                                    @endif
                                </div>
                                <svg class="hidden md:block w-4 h-4 text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open" @click.away="open = false" x-cloak
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                                class="absolute right-0 mt-3 w-72 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden z-50">

                                <!-- User Info Header -->
                                <div
                                    class="px-5 py-4 bg-primary-50 dark:bg-primary-900/20 border-b border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-12 h-12 bg-primary-600 rounded-xl flex items-center justify-center text-white text-lg font-bold">
                                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold text-gray-900 dark:text-white truncate">
                                                {{ auth()->user()->name ?? 'User' }}
                                            </p>
                                            <p class="text-xs text-gray-600 dark:text-gray-400 truncate">
                                                {{ auth()->user()->email ?? '' }}
                                            </p>
                                            @php
                                                $currentRole = auth()->user()->getCurrentRole();
                                            @endphp
                                            @if($currentRole)
                                                <span
                                                    class="inline-flex items-center gap-1 mt-1.5 px-2 py-0.5 rounded-md text-xs font-medium bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ $currentRole->getRoleDisplayName() }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Menu Items -->
                                <div class="py-2">
                                    <a href="{{ route('profile') }}"
                                        class="flex items-center gap-3 px-5 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all duration-150 group">
                                        <div
                                            class="w-9 h-9 bg-gray-100 dark:bg-gray-700 rounded-xl flex items-center justify-center text-gray-600 dark:text-gray-400 group-hover:bg-primary-100 dark:group-hover:bg-primary-900/30 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-all">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <p
                                                class="font-medium group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                                Profil Saya</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Kelola informasi akun
                                            </p>
                                        </div>
                                        <svg class="w-4 h-4 text-gray-400 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>

                                <!-- Logout -->
                                <div class="border-t border-gray-200 dark:border-gray-700 py-2">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                            class="flex items-center gap-3 w-full px-5 py-3 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all duration-150 group">
                                            <div
                                                class="w-9 h-9 bg-red-50 dark:bg-red-900/20 rounded-xl flex items-center justify-center text-red-600 dark:text-red-400 group-hover:bg-red-100 dark:group-hover:bg-red-900/30 transition-all">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                                </svg>
                                            </div>
                                            <div class="flex-1 text-left">
                                                <p class="font-medium">Keluar</p>
                                                <p class="text-xs text-red-500 dark:text-red-400">Logout dari sistem</p>
                                            </div>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="p-4 lg:p-6">
                @if(session('success'))
                    <div
                        class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
                        </div>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
    @include('partials.pwa-sw-register')
</body>

</html>