<!DOCTYPE html>
<html lang="id" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true', sidebarOpen: true, showModal: false }"
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
    <title>@yield('title', 'Dashboard') - SIMAPRES UNPATTI</title>
    <!-- Tailwind CSS CDN - For development only. Consider installing via npm for production -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: { 50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc', 400: '#818cf8', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca', 800: '#3730a3', 900: '#312e81' },
                        accent: { 50: '#f0fdfa', 100: '#ccfbf1', 200: '#99f6e4', 300: '#5eead4', 400: '#2dd4bf', 500: '#14b8a6', 600: '#0d9488', 700: '#0f766e', 800: '#115e59', 900: '#134e4a' }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-out',
                        'slide-up': 'slideUp 0.4s ease-out',
                        'pulse-soft': 'pulseSoft 2s infinite',
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] {
            display: none !important;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulseSoft {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.8;
            }
        }

        .glass {
            background: rgba(255, 255, 255, 0.95);
        }

        .dark .glass {
            background: rgba(30, 41, 59, 0.95);
        }
    </style>
    @stack('styles')
</head>

<body class="bg-gray-50 dark:bg-slate-900 min-h-screen transition-colors duration-300">

    @php
        // IMPORTANT: Check auth()->check() FIRST to prevent role confusion
        // Validators/Admins use Laravel auth, Students use session
        if (auth()->check()) {
            $role = auth()->user()->role;
            $userName = auth()->user()->name;
        } elseif (session('auth_role') === 'student') {
            $role = 'student';
            $userName = session('student_name', 'Mahasiswa');
        } else {
            $role = 'guest';
            $userName = 'Guest';
        }

        $roleColors = [
            'student' => ['from-indigo-600', 'to-indigo-600', 'bg-indigo-600', 'text-indigo-600', 'border-indigo-200'],
            'Validator' => ['from-indigo-600', 'to-indigo-600', 'bg-indigo-600', 'text-indigo-600', 'border-indigo-200'],
            'Admin' => ['from-indigo-600', 'to-indigo-600', 'bg-indigo-600', 'text-indigo-600', 'border-indigo-200'],
            'guest' => ['from-gray-500', 'to-gray-600', 'bg-gray-500', 'text-gray-600', 'border-gray-200'],
        ];
        $colors = $roleColors[$role] ?? $roleColors['guest'];
    @endphp

    <!-- Navbar -->
    <nav class="glass border-b border-gray-200/50 dark:border-gray-700/50 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo & Brand -->
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-gray-800 dark:text-white">SIMAPRES</h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            @yield('subtitle', 'Sistem Manajemen Prestasi')</p>
                    </div>
                </div>

                <!-- Navigation Links -->
                @hasSection('nav-links')
                    @yield('nav-links')
                @endif

                <!-- Right Side -->
                <div class="flex items-center gap-3">
                    <!-- Dark Mode Toggle -->
                    <button @click="darkMode = !darkMode"
                        class="p-2.5 rounded-xl bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200">
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

                    <!-- User Info -->
                    @php
                        $profileRoute = session('auth_role') === 'student'
                            ? route('student.profile')
                            : (auth()->check() ? route('profile') : '#');
                    @endphp
                    <div
                        class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-gray-100/50 dark:bg-gray-700/50">
                        <a href="{{ $profileRoute }}"
                            class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                            <div
                                class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white text-sm font-bold">
                                {{ strtoupper(substr($userName ?? 'U', 0, 1)) }}
                            </div>
                            <div class="flex flex-col">
                                <span
                                    class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $userName ?? 'User' }}</span>
                                @if(auth()->check() && auth()->user()->last_login_method === 'sso')
                                    <span class="text-xs text-blue-500 dark:text-blue-400 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        SSO
                                    </span>
                                @elseif(session('auth_role') === 'student')
                                    <span class="text-xs text-blue-500 dark:text-blue-400 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        SIAKAD
                                    </span>
                                @endif
                            </div>
                        </a>
                    </div>

                    <!-- Logout -->
                    @php
                        $logoutRoute = (auth()->check() && auth()->user()->last_login_method === 'sso')
                            ? route('sso.logout')
                            : route('logout');
                    @endphp
                    <form action="{{ $logoutRoute }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="p-2.5 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/40 transition-all duration-200"
                            title="Logout{{ auth()->check() && auth()->user()->last_login_method === 'sso' ? ' (SSO)' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-auto py-6 text-center text-sm text-gray-500 dark:text-gray-400">
        <p>© {{ date('Y') }} Universitas Pattimura. Sistem Prestasi Mahasiswa.</p>
    </footer>

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
    <script src="https://instant.page/5.2.0" type="module"
        integrity="sha384-jnZyxPjiipYXnSU0ygqeac2q7CVYMbh84q0uHVRRxEtvFPiQYbXWUorga2aqZJ0z"></script>
</body>

</html>