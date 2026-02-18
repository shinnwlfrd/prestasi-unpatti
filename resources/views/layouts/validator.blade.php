<!DOCTYPE html>
<html lang="id" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true', mobileSidebarOpen: false }"
    x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode }">

<head>
    <meta charset="UTF-8">
    <script>
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Validator Dashboard') - Sistem Prestasi Mahasiswa</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>

    @stack('styles')
</head>

<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        @php
            $user = auth()->user();
            $currentRole = $user->getCurrentRole();
            $isPimpinan = $currentRole && $currentRole->role === 'pimpinan';
            $routePrefix = $isPimpinan ? 'pimpinan' : 'validator';
        @endphp

        <!-- Mobile Sidebar Backdrop -->
        <div x-show="mobileSidebarOpen" x-cloak @click="mobileSidebarOpen = false"
            class="fixed inset-0 bg-black/50 z-40 lg:hidden" x-transition:enter="transition-opacity ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

        <aside :class="mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed h-screen bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 w-64 overflow-y-auto transition-transform duration-300 z-50">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                    </div>
                    <span class="font-bold text-gray-800 dark:text-white">
                        @if($currentRole && $currentRole->role === 'pimpinan')
                            Pimpinan Panel
                        @elseif($currentRole && $currentRole->role === 'operator')
                            Operator Panel
                        @endif
                    </span>
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
            <nav class="p-4 space-y-2 overflow-y-auto" style="height: calc(100vh - 73px);">


                @if($isPimpinan)
                    <a href="{{ route($routePrefix . '.dashboard') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs($routePrefix . '.dashboard') ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span>Dashboard</span>
                    </a>
                @endif

                <!-- Data Section -->
                <div class="pt-2">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Data</p>
                </div>
                <a href="{{ route($routePrefix . '.students.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs($routePrefix . '.students.*') ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>Mahasiswa</span>
                </a>

                <!-- Validation Section -->
                <div class="pt-2">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        {{ $isPimpinan ? 'Monitoring' : 'Validasi' }}</p>
                </div>
                <a href="{{ route($routePrefix . '.pending.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs($routePrefix . '.pending.*') ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <span>{{ $isPimpinan ? 'Lihat Prestasi' : 'Validasi Prestasi' }}</span>
                </a>
                <a href="{{ route($routePrefix . '.history') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs($routePrefix . '.history') ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Riwayat</span>
                </a>

                @unless($isPimpinan)
                    <!-- Actions Section (Only for Validator) -->
                    <div class="pt-2">
                        <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Aksi</p>
                    </div>
                    <a href="{{ route('validator.submit.form') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('validator.submit.*') ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Submit Prestasi</span>
                    </a>
                @endunless

                <!-- Documents Section -->
                <div class="pt-2">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Dokumen</p>
                </div>
                <a href="{{ route($routePrefix . '.sk.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs($routePrefix . '.sk.*') ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Dokumen SK</span>
                </a>

                <!-- Profile Section -->
                <div class="pt-2">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Akun</p>
                </div>
                <a href="{{ route('profile') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('profile') ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Profil Saya</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 lg:ml-64 min-h-screen">
            <!-- Top Bar -->
            <header
                class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex justify-between items-center sticky top-0 z-30">
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
                    <button @click="darkMode = !darkMode" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700">
                        <svg x-show="!darkMode" class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <svg x-show="darkMode" class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor"
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
                                class="absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-50">
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
                    </form>
                </div>
            </header>

            <!-- Page Content -->
            <div class="p-4 sm:p-6">
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

    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                @foreach($errors->all() as $error)
                    showToast('error', '{{ $error }}');
                @endforeach
            });
        </script>
    @endif

    @stack('scripts')
</body>

</html>