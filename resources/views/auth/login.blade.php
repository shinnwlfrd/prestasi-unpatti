<!DOCTYPE html>
<html lang="id" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true', showPassword: false, loading: false }"
    x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode }">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - Sistem Prestasi UNPATTI</title>
    @include('partials.pwa-meta')
    <!-- Tailwind CSS CDN - For development only. Consider installing via npm for production -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    animation: {
                        'gradient': 'gradient 8s ease infinite',
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-soft': 'pulseSoft 3s infinite',
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] {
            display: none !important;
        }

        @keyframes gradient {

            0%,
            100% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        @keyframes pulseSoft {

            0%,
            100% {
                opacity: 0.4;
            }

            50% {
                opacity: 0.2;
            }
        }

        .animate-gradient {
            animation: gradient 8s ease infinite;
            background-size: 200% 200%;
        }

        .glass {
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
    </style>
</head>

<body class="min-h-screen overflow-hidden relative transition-colors duration-300">
    @php /** @var \Illuminate\Support\ViewErrorBag $errors */ @endphp
    <!-- Animated Background -->
    <div class="fixed inset-0 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-900 dark:to-slate-800">
    </div>

    <!-- Dark Mode Toggle -->
    <button @click="darkMode = !darkMode"
        class="fixed top-6 right-6 z-50 p-3 rounded-xl glass bg-white/20 dark:bg-slate-800/50 shadow-xl hover:scale-110 transition-all duration-300">
        <svg x-show="!darkMode" class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
        </svg>
        <svg x-show="darkMode" x-cloak class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
    </button>

    <!-- Login Card -->
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-8 border border-gray-200 dark:border-slate-700">
                <!-- Logo -->
                <div class="text-center mb-8">
                    <div class="relative inline-block">
                        <div class="w-24 h-24 mx-auto flex items-center justify-center p-1">
                            <img src="{{ asset('img/logo.png') }}" class="w-full h-full object-contain"
                                alt="Logo UNPATTI">
                        </div>
                    </div>
                    <h1 class="mt-5 text-2xl font-bold text-gray-900 dark:text-white">
                        SIMAPRES UNPATTI</h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">Sistem Manajemen Prestasi Mahasiswa</p>
                </div>

                <!-- SSO Only Mode -->
                <div class="text-center">
                    <a href="{{ route('sso.redirect') }}"
                        class="w-full inline-flex items-center justify-center gap-3 py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-lg rounded-xl transition-all duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        Login dengan SSO Unpatti
                    </a>
                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                        Gunakan akun SSO Unpatti Anda untuk masuk
                    </p>
                </div>

                <!-- Footer -->
                <div class="mt-8 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">© {{ date('Y') }} Universitas Pattimura</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">SIMAPRES v2.0</p>
                </div>
            </div>
        </div>
    </div>
    @include('partials.pwa-sw-register')
</body>

</html>