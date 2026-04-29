<!DOCTYPE html>
<html lang="id" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" 
      x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - SIMAPRES UNPATTI</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS (Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine.js -->

    
    <style>
        [x-cloak] { display: none !important; }
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        .dark .glass {
            background: rgba(17, 24, 39, 0.7);
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 min-h-screen flex flex-col font-sans transition-colors duration-300">
    <!-- Background Decoration -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-indigo-500/10 rounded-full blur-[120px] animate-pulse-slow"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-purple-500/10 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 2s;"></div>
    </div>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center p-6 relative z-10">
        <div class="max-w-xl w-full text-center">
            <!-- Illustration / Error Code -->
            <div class="relative mb-8 inline-block">
                <div class="text-[12rem] font-extrabold leading-none tracking-tighter opacity-10 select-none animate-float">
                    @yield('code')
                </div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="bg-indigo-600 dark:bg-indigo-500 w-24 h-24 rounded-3xl rotate-12 flex items-center justify-center shadow-2xl shadow-indigo-500/40">
                        @yield('icon')
                    </div>
                </div>
            </div>

            <!-- Text Content -->
            <h1 class="text-3xl md:text-4xl font-extrabold mb-4 tracking-tight">
                @yield('message_title')
            </h1>
            <p class="text-slate-600 dark:text-slate-400 text-lg mb-10 max-w-md mx-auto leading-relaxed">
                @yield('message_description')
            </p>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ url('/') }}" class="w-full sm:w-auto px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl shadow-lg shadow-indigo-600/25 transition-all hover:-translate-y-1 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Kembali ke Beranda
                </a>
                <button onclick="window.history.back()" class="w-full sm:w-auto px-8 py-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-750 font-bold rounded-2xl transition-all hover:-translate-y-1 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Halaman Sebelumnya
                </button>
            </div>
            
            @if(app()->environment('local') && isset($exception))
                <div class="mt-12 text-left bg-red-50 dark:bg-red-900/20 p-6 rounded-2xl border border-red-100 dark:border-red-900/30 overflow-auto">
                    <p class="text-red-600 dark:text-red-400 font-mono text-sm">
                        {{ $exception->getMessage() }}
                    </p>
                </div>
            @endif
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-10 text-center text-slate-400 dark:text-slate-600 text-sm relative z-10">
        <div class="flex items-center justify-center gap-4 mb-4">
            <img src="{{ asset('img/logo.png') }}" alt="Logo UNPATTI" class="w-8 h-8 opacity-50 grayscale">
            <span class="w-1 h-1 bg-slate-300 dark:bg-slate-700 rounded-full"></span>
            <span class="font-bold tracking-widest uppercase text-[10px]">SIMAPRES UNPATTI</span>
        </div>
        <p>&copy; {{ date('Y') }} Universitas Pattimura. Hak Cipta Dilindungi.</p>
    </footer>

    <!-- Dark Mode Toggle Button -->
    <button @click="darkMode = !darkMode" 
            class="fixed bottom-6 right-6 z-50 p-4 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 hover:scale-110 active:scale-95 transition-all">
        <svg x-show="!darkMode" class="w-6 h-6 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
        </svg>
        <svg x-show="darkMode" x-cloak class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
        </svg>
    </button>
</body>
</html>
