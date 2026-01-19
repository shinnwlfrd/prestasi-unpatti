<!DOCTYPE html>
<html lang="id" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true', sidebarOpen: true, showModal: false }"
    :class="{ 'dark': darkMode }">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Sistem Prestasi UNPATTI</title>
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
            backdrop-filter: blur(12px);
            background: rgba(255, 255, 255, 0.7);
        }

        .dark .glass {
            background: rgba(30, 41, 59, 0.8);
        }

        .gradient-border {
            background: linear-gradient(135deg, #6366f1, #14b8a6);
            padding: 2px;
        }

        .gradient-border>* {
            background: white;
        }

        .dark .gradient-border>* {
            background: #1e293b;
        }
    </style>
    @stack('styles')
</head>

<body
    class="bg-gradient-to-br from-slate-50 via-white to-indigo-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 min-h-screen transition-colors duration-300">

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
            'student' => ['from-blue-500', 'to-indigo-600', 'bg-blue-500', 'text-blue-600', 'border-blue-200'],
            'Validator' => ['from-emerald-500', 'to-teal-600', 'bg-emerald-500', 'text-emerald-600', 'border-emerald-200'],
            'Admin' => ['from-purple-500', 'to-pink-600', 'bg-purple-500', 'text-purple-600', 'border-purple-200'],
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
                    <div
                        class="w-10 h-10 bg-gradient-to-br {{ $colors[0] }} {{ $colors[1] }} rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-gray-800 dark:text-white">Prestasi UNPATTI</h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400">@yield('subtitle', 'Portal Mahasiswa')</p>
                    </div>
                </div>

                <!-- Navigation Links -->
                @hasSection('nav-links')
                    @yield('nav-links')
                @endif

                <!-- Right Side -->
                <div class="flex items-center gap-3">
                    <!-- Dark Mode Toggle -->
                    <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)"
                        class="p-2.5 rounded-xl bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200 hover:scale-105">
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
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-gray-100/50 dark:bg-gray-700/50">
                        <a href="{{ $profileRoute }}" class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br {{ $colors[0] }} {{ $colors[1] }} flex items-center justify-center text-white text-sm font-bold">
                                {{ strtoupper(substr($userName ?? 'U', 0, 1)) }}
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $userName ?? 'User' }}</span>
                                @if(auth()->check() && auth()->user()->last_login_method === 'sso')
                                    <span class="text-xs text-blue-500 dark:text-blue-400 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        SSO
                                    </span>
                                @elseif(session('auth_role') === 'student')
                                    <span class="text-xs text-blue-500 dark:text-blue-400 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
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
                            class="p-2.5 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/40 transition-all duration-200 hover:scale-105"
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
        <!-- Alerts -->
        @if(session('success'))
            <div class="mb-6 animate-slide-up">
                <div
                    class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800">
                    <div
                        class="flex-shrink-0 w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-800 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <p class="text-emerald-700 dark:text-emerald-400 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 animate-slide-up">
                <div
                    class="flex items-start gap-3 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                    <div
                        class="flex-shrink-0 w-10 h-10 rounded-xl bg-red-100 dark:bg-red-800 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-red-700 dark:text-red-400 font-medium">Terjadi kesalahan:</p>
                        <ul class="mt-1 text-sm text-red-600 dark:text-red-400 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-auto py-6 text-center text-sm text-gray-500 dark:text-gray-400">
        <p>© {{ date('Y') }} Universitas Pattimura. Sistem Prestasi Mahasiswa.</p>
    </footer>

    @stack('scripts')
    <script src="https://instant.page/5.2.0" type="module"
        integrity="sha384-jnZyxPjiipYXnSU0ber8UYWa/3y+LA2aLGeB5rWGKbgsNJYgLAw0qauPVhSQqxr4"></script>
</body>

</html>