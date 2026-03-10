<!DOCTYPE html>
<html lang="id" x-data="guideApp()" x-init="init()" :class="{ 'dark': darkMode }">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Panduan Penggunaan - SIMAPRES UNPATTI</title>
    <meta name="description"
        content="Panduan lengkap penggunaan Sistem Informasi Manajemen Prestasi Mahasiswa Universitas Pattimura (SIMAPRES UNPATTI) untuk Mahasiswa, Validator, Pimpinan, dan Admin.">
    @include('partials.pwa-meta')

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(99, 102, 241, 0.3);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(99, 102, 241, 0.5);
        }

        /* Smooth scroll */
        html {
            scroll-behavior: smooth;
        }

        /* Sidebar active indicator */
        .sidebar-active {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(99, 102, 241, 0.05));
            border-left: 3px solid rgb(99, 102, 241);
        }

        .dark .sidebar-active {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.25), rgba(99, 102, 241, 0.1));
        }

        /* Content styling */
        .guide-content h3 {
            scroll-margin-top: 100px;
        }

        /* Glassmorphism */
        .glass {
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        /* Animated gradient border */
        .gradient-border {
            background: linear-gradient(135deg, #6366f1, #8b5cf6, #06b6d4);
            background-size: 200% 200%;
            animation: gradientShift 4s ease infinite;
        }

        @keyframes gradientShift {

            0%,
            100% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }
        }

        /* Fade in animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.5s ease-out forwards;
        }

        /* Step number */
        .step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            font-weight: 700;
            font-size: 13px;
            flex-shrink: 0;
        }

        /* Mobile sidebar overlay */
        .sidebar-overlay {
            transition: opacity 0.3s ease;
        }

        /* Print styles */
        @media print {
            .no-print {
                display: none !important;
            }

            .print-break {
                page-break-before: always;
            }
        }

        /* Back to top button */
        .back-to-top {
            transition: all 0.3s ease;
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-slate-900 text-gray-800 dark:text-gray-200 min-h-screen">

    <!-- Top Header Bar -->
    <header
        class="fixed top-0 left-0 right-0 z-50 h-16 glass bg-white/80 dark:bg-slate-900/80 border-b border-gray-200/50 dark:border-slate-700/50 no-print">
        <div class="flex items-center justify-between h-full px-4 lg:px-6">
            <!-- Left: Logo + Mobile Menu -->
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = !sidebarOpen"
                    class="lg:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors">
                    <span class="material-icons-round text-gray-600 dark:text-gray-300">menu</span>
                </button>
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <img src="{{ asset('img/logo.png') }}" class="w-9 h-9 object-contain" alt="Logo UNPATTI">
                    <div>
                        <h1 class="text-base font-bold text-gray-900 dark:text-white leading-tight">SIMAPRES UNPATTI
                        </h1>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 leading-tight">Panduan Penggunaan Sistem
                        </p>
                    </div>
                </a>
            </div>

            <!-- Right: Actions -->
            <div class="flex items-center gap-2">
                <!-- Search Toggle -->
                <button @click="searchOpen = !searchOpen"
                    class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors">
                    <span class="material-icons-round text-gray-500 dark:text-gray-400 text-xl">search</span>
                </button>

                <!-- Dark Mode Toggle -->
                <button @click="darkMode = !darkMode"
                    class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors">
                    <span x-show="!darkMode" class="material-icons-round text-gray-500 text-xl">dark_mode</span>
                    <span x-show="darkMode" x-cloak
                        class="material-icons-round text-yellow-400 text-xl">light_mode</span>
                </button>

                <!-- Print -->
                <button @click="window.print()"
                    class="hidden sm:block p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors"
                    title="Cetak Panduan">
                    <span class="material-icons-round text-gray-500 dark:text-gray-400 text-xl">print</span>
                </button>

                <!-- Login -->
                <a href="{{ route('login') }}"
                    class="ml-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors flex items-center gap-2">
                    <span class="material-icons-round text-base">login</span>
                    <span class="hidden sm:inline">Masuk</span>
                </a>
            </div>
        </div>

        <!-- Search Bar (Collapsible) -->
        <div x-show="searchOpen" x-cloak x-transition.opacity
            class="absolute top-full left-0 right-0 p-3 bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 shadow-lg">
            <div class="max-w-2xl mx-auto relative">
                <span class="material-icons-round absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                <input type="text" x-model="searchQuery" @input.debounce.300ms="filterSections()"
                    placeholder="Cari panduan... (contoh: login, upload, validasi)"
                    class="w-full pl-10 pr-10 py-2.5 rounded-lg border border-gray-200 dark:border-slate-600 bg-gray-50 dark:bg-slate-700 text-gray-800 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all">
                <button @click="searchOpen = false; searchQuery = ''; filterSections()"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <span class="material-icons-round text-xl">close</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
        class="fixed inset-0 z-40 bg-black/50 sidebar-overlay lg:hidden no-print"></div>

    <!-- Sidebar Navigation -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed top-16 left-0 bottom-0 z-40 w-72 bg-white dark:bg-slate-800 border-r border-gray-200 dark:border-slate-700 overflow-y-auto transition-transform duration-300 no-print">

        <div class="p-4">
            <!-- Role Tabs -->
            <div class="flex flex-wrap gap-1.5 p-1 bg-gray-100 dark:bg-slate-700 rounded-lg mb-4">
                <template x-for="tab in tabs" :key="tab.id">
                    <button @click="activeTab = tab.id; sidebarOpen = false"
                        :class="activeTab === tab.id ? 'bg-white dark:bg-slate-600 shadow-sm text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="flex-1 min-w-[calc(50%-4px)] px-2 py-1.5 text-xs font-semibold rounded-md transition-all duration-200 flex items-center justify-center gap-1">
                        <span class="material-icons-round text-sm" x-text="tab.icon"></span>
                        <span x-text="tab.shortLabel"></span>
                    </button>
                </template>
            </div>

            <!-- Sidebar Menu -->
            <nav class="space-y-0.5">
                <template x-for="(section, index) in getActiveSections()" :key="section.id">
                    <a :href="'#' + section.id" @click="sidebarOpen = false"
                        :class="activeSection === section.id ? 'sidebar-active text-indigo-700 dark:text-indigo-300 font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-slate-700/50'"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-all duration-200 cursor-pointer">
                        <span class="material-icons-round text-lg" x-text="section.icon"></span>
                        <span x-text="section.title" class="truncate"></span>
                    </a>
                </template>
            </nav>

            <!-- Quick Links -->
            <div class="mt-6 pt-4 border-t border-gray-200 dark:border-slate-700">
                <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">
                    Lainnya</p>
                <a href="#faq" @click="activeTab = 'faq'; sidebarOpen = false"
                    :class="activeTab === 'faq' ? 'sidebar-active text-indigo-700 dark:text-indigo-300 font-semibold' : 'text-gray-600 dark:text-gray-400'"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-all cursor-pointer">
                    <span class="material-icons-round text-lg">help_outline</span>
                    <span>FAQ & Troubleshooting</span>
                </a>
                <a href="#kontak" @click="activeTab = 'faq'; sidebarOpen = false"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-all cursor-pointer">
                    <span class="material-icons-round text-lg">support_agent</span>
                    <span>Kontak Support</span>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="lg:ml-72 pt-16 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- Hero Section -->
            <div x-show="activeTab === 'overview'" x-cloak class="fade-in-up">
                <div
                    class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-700 p-8 sm:p-12 mb-8 text-white">
                    <div
                        class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2">
                    </div>
                    <div
                        class="absolute bottom-0 left-0 w-48 h-48 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2">
                    </div>
                    <div class="relative">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="px-3 py-1 bg-white/20 rounded-full text-xs font-semibold tracking-wide">PANDUAN
                                RESMI</span>
                            <span
                                class="px-3 py-1 bg-white/20 rounded-full text-xs font-semibold tracking-wide">v2.0</span>
                        </div>
                        <h1 class="text-3xl sm:text-4xl font-black mb-3 leading-tight">
                            Panduan Penggunaan<br>
                            <span class="text-indigo-200">SIMAPRES UNPATTI</span>
                        </h1>
                        <p class="text-indigo-100 text-base sm:text-lg max-w-2xl leading-relaxed">
                            Sistem Informasi Manajemen Prestasi Mahasiswa — Platform digital untuk mengelola dan
                            memvalidasi prestasi mahasiswa Universitas Pattimura.
                        </p>

                        <!-- Quick Stats -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-8">
                            <div class="bg-white/10 rounded-xl p-3 text-center backdrop-blur-sm">
                                <div class="text-2xl font-bold">4</div>
                                <div class="text-xs text-indigo-200">Role Pengguna</div>
                            </div>
                            <div class="bg-white/10 rounded-xl p-3 text-center backdrop-blur-sm">
                                <div class="text-2xl font-bold">40+</div>
                                <div class="text-xs text-indigo-200">Fitur Sistem</div>
                            </div>
                            <div class="bg-white/10 rounded-xl p-3 text-center backdrop-blur-sm">
                                <div class="text-2xl font-bold">SSO</div>
                                <div class="text-xs text-indigo-200">Autentikasi</div>
                            </div>
                            <div class="bg-white/10 rounded-xl p-3 text-center backdrop-blur-sm">
                                <div class="text-2xl font-bold">2 Tahap</div>
                                <div class="text-xs text-indigo-200">Validasi</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Role Selection Cards -->
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Pilih Panduan Sesuai Role Anda</h2>
                <div class="grid sm:grid-cols-2 gap-4 mb-8">
                    <template x-for="tab in tabs.filter(t => t.id !== 'overview')" :key="tab.id">
                        <button @click="activeTab = tab.id"
                            class="group relative overflow-hidden bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6 text-left hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-lg transition-all duration-300">
                            <div
                                class="absolute top-0 right-0 w-20 h-20 bg-indigo-50 dark:bg-indigo-900/20 rounded-bl-[40px] flex items-center justify-center group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/30 transition-colors">
                                <span class="material-icons-round text-indigo-500 text-2xl" x-text="tab.icon"></span>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 dark:text-white text-lg mb-1" x-text="tab.label">
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400" x-text="tab.desc"></p>
                                <div
                                    class="flex items-center gap-1 mt-3 text-indigo-600 dark:text-indigo-400 text-sm font-semibold group-hover:gap-2 transition-all">
                                    <span>Buka Panduan</span>
                                    <span class="material-icons-round text-base">arrow_forward</span>
                                </div>
                            </div>
                        </button>
                    </template>
                </div>

                <!-- Flow Diagram -->
                <div
                    class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6 mb-8">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <span class="material-icons-round text-indigo-600">account_tree</span>
                        Alur Validasi Prestasi
                    </h2>
                    <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-3 text-sm">
                        <div
                            class="flex items-center gap-2 bg-blue-50 dark:bg-blue-900/20 px-4 py-2.5 rounded-xl border border-blue-100 dark:border-blue-800">
                            <span class="material-icons-round text-blue-600 dark:text-blue-400 text-lg">person</span>
                            <span class="font-semibold text-blue-700 dark:text-blue-300">Mahasiswa Submit</span>
                        </div>
                        <span class="material-icons-round text-gray-400 text-xl">arrow_forward</span>
                        <div
                            class="flex items-center gap-2 bg-amber-50 dark:bg-amber-900/20 px-4 py-2.5 rounded-xl border border-amber-100 dark:border-amber-800">
                            <span
                                class="material-icons-round text-amber-600 dark:text-amber-400 text-lg">verified_user</span>
                            <span class="font-semibold text-amber-700 dark:text-amber-300">Validasi Fakultas</span>
                        </div>
                        <span class="material-icons-round text-gray-400 text-xl">arrow_forward</span>
                        <div
                            class="flex items-center gap-2 bg-purple-50 dark:bg-purple-900/20 px-4 py-2.5 rounded-xl border border-purple-100 dark:border-purple-800">
                            <span
                                class="material-icons-round text-purple-600 dark:text-purple-400 text-lg">admin_panel_settings</span>
                            <span class="font-semibold text-purple-700 dark:text-purple-300">Validasi Universitas</span>
                        </div>
                        <span class="material-icons-round text-gray-400 text-xl">arrow_forward</span>
                        <div
                            class="flex items-center gap-2 bg-green-50 dark:bg-green-900/20 px-4 py-2.5 rounded-xl border border-green-100 dark:border-green-800">
                            <span
                                class="material-icons-round text-green-600 dark:text-green-400 text-lg">check_circle</span>
                            <span class="font-semibold text-green-700 dark:text-green-300">Approved ✅</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===================== PANDUAN MAHASISWA ===================== --}}
            <div x-show="activeTab === 'student'" x-cloak class="guide-content fade-in-up">
                @include('guide.sections.student')
            </div>

            {{-- ===================== PANDUAN VALIDATOR ===================== --}}
            <div x-show="activeTab === 'validator'" x-cloak class="guide-content fade-in-up">
                @include('guide.sections.validator')
            </div>

            {{-- ===================== PANDUAN PIMPINAN ===================== --}}
            <div x-show="activeTab === 'pimpinan'" x-cloak class="guide-content fade-in-up">
                @include('guide.sections.pimpinan')
            </div>

            {{-- ===================== PANDUAN ADMIN ===================== --}}
            <div x-show="activeTab === 'admin'" x-cloak class="guide-content fade-in-up">
                @include('guide.sections.admin')
            </div>

            {{-- ===================== FAQ ===================== --}}
            <div x-show="activeTab === 'faq'" x-cloak class="guide-content fade-in-up">
                @include('guide.sections.faq')
            </div>

            <!-- Footer -->
            <footer class="mt-16 pt-8 border-t border-gray-200 dark:border-slate-700">
                <div
                    class="flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500 dark:text-gray-400">
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('img/logo.png') }}" class="w-6 h-6" alt="Logo">
                        <span>© {{ date('Y') }} Universitas Pattimura — SIMAPRES v2.0</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <a href="mailto:prestasi@unpatti.ac.id"
                            class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">prestasi@unpatti.ac.id</a>
                        <span>•</span>
                        <a href="{{ route('login') }}"
                            class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Login</a>
                    </div>
                </div>
            </footer>
        </div>
    </main>

    <!-- Back to Top Button -->
    <button x-show="showBackToTop" x-cloak @click="window.scrollTo({top: 0, behavior: 'smooth'})"
        class="fixed bottom-6 right-6 z-50 w-12 h-12 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full shadow-lg flex items-center justify-center transition-all hover:scale-110 no-print back-to-top">
        <span class="material-icons-round">keyboard_arrow_up</span>
    </button>

    <script>
        function guideApp() {
            return {
                darkMode: localStorage.getItem('darkMode') === 'true',
                sidebarOpen: false,
                searchOpen: false,
                searchQuery: '',
                activeTab: 'overview',
                activeSection: '',
                showBackToTop: false,

                tabs: [
                    { id: 'overview', label: 'Beranda', shortLabel: 'Home', icon: 'home', desc: '' },
                    { id: 'student', label: 'Mahasiswa', shortLabel: 'Mhs', icon: 'school', desc: '7 fitur — Submit & tracking prestasi' },
                    { id: 'validator', label: 'Validator Fakultas', shortLabel: 'Validator', icon: 'verified_user', desc: '10 fitur — Validasi & manajemen SK' },
                    { id: 'pimpinan', label: 'Pimpinan', shortLabel: 'Pimpinan', icon: 'leaderboard', desc: '9 fitur — Analytics & monitoring' },
                    { id: 'admin', label: 'Admin / Super Admin', shortLabel: 'Admin', icon: 'admin_panel_settings', desc: '14 fitur — Full system management' },
                    { id: 'faq', label: 'FAQ & Troubleshooting', shortLabel: 'FAQ', icon: 'help_outline', desc: '' },
                ],

                sections: {
                    student: [
                        { id: 's-login', title: 'Login ke Sistem', icon: 'login' },
                        { id: 's-dashboard', title: 'Dashboard', icon: 'dashboard' },
                        { id: 's-submit', title: 'Mengajukan Prestasi', icon: 'add_circle' },
                        { id: 's-upload', title: 'Upload Dokumen', icon: 'upload_file' },
                        { id: 's-status', title: 'Melihat Status', icon: 'pending_actions' },
                        { id: 's-revision', title: 'Revisi Dokumen', icon: 'edit_document' },
                        { id: 's-review', title: 'Request Review Ulang', icon: 'rate_review' },
                    ],
                    validator: [
                        { id: 'v-dashboard', title: 'Dashboard', icon: 'dashboard' },
                        { id: 'v-pending', title: 'Validasi Prestasi', icon: 'pending_actions' },
                        { id: 'v-detail', title: 'Review Detail', icon: 'preview' },
                        { id: 'v-approve', title: 'Menyetujui Prestasi', icon: 'check_circle' },
                        { id: 'v-reject', title: 'Menolak Prestasi', icon: 'cancel' },
                        { id: 'v-revision', title: 'Request Revisi', icon: 'edit_note' },
                        { id: 'v-history', title: 'History Validasi', icon: 'history' },
                        { id: 'v-students', title: 'Data Mahasiswa', icon: 'people' },
                        { id: 'v-submit', title: 'Submit untuk Mahasiswa', icon: 'person_add' },
                        { id: 'v-sk', title: 'Mengelola SK', icon: 'description' },
                    ],
                    pimpinan: [
                        { id: 'p-dashboard', title: 'Dashboard Pimpinan', icon: 'dashboard' },
                        { id: 'p-hierarki', title: 'Perbandingan Hierarkis', icon: 'account_tree' },
                        { id: 'p-ranking', title: 'Ranking Efisiensi', icon: 'emoji_events' },
                        { id: 'p-risk', title: 'Risk Indicators', icon: 'warning' },
                        { id: 'p-filter', title: 'Filter Periode', icon: 'filter_alt' },
                        { id: 'p-topstudents', title: 'Top Students & GPA', icon: 'stars' },
                        { id: 'p-detail', title: 'Detail Mahasiswa', icon: 'person_search' },
                        { id: 'p-pending', title: 'Pending Validasi', icon: 'pending' },
                        { id: 'p-export', title: 'Export Data', icon: 'download' },
                    ],
                    admin: [
                        { id: 'a-dashboard', title: 'Dashboard Admin', icon: 'dashboard' },
                        { id: 'a-validation', title: 'Validasi Universitas', icon: 'verified' },
                        { id: 'a-bulk', title: 'Bulk Assignment SK', icon: 'dynamic_feed' },
                        { id: 'a-students', title: 'Manajemen Mahasiswa', icon: 'people' },
                        { id: 'a-achievements', title: 'Manajemen Prestasi', icon: 'military_tech' },
                        { id: 'a-logs', title: 'Validation Logs', icon: 'receipt_long' },
                        { id: 'a-users', title: 'Manajemen User', icon: 'manage_accounts' },
                        { id: 'a-categories', title: 'Kategori Prestasi', icon: 'category' },
                        { id: 'a-levels', title: 'Level Prestasi', icon: 'signal_cellular_alt' },
                        { id: 'a-periods', title: 'Periode Akademik', icon: 'calendar_month' },
                        { id: 'a-sk', title: 'Manajemen SK', icon: 'description' },
                        { id: 'a-submit', title: 'Submit Prestasi', icon: 'add_task' },
                        { id: 'a-anomaly', title: 'Anomaly Detection', icon: 'bug_report' },
                        { id: 'a-export', title: 'Export & Reporting', icon: 'download' },
                    ],
                    faq: [
                        { id: 'faq-umum', title: 'Pertanyaan Umum', icon: 'quiz' },
                        { id: 'faq-trouble', title: 'Troubleshooting', icon: 'build' },
                        { id: 'kontak', title: 'Kontak Support', icon: 'support_agent' },
                    ],
                },

                init() {
                    this.$watch('darkMode', val => localStorage.setItem('darkMode', val));

                    // Handle hash on load
                    if (window.location.hash) {
                        const hash = window.location.hash.substring(1);
                        for (const [tabId, secs] of Object.entries(this.sections)) {
                            if (secs.find(s => s.id === hash)) {
                                this.activeTab = tabId;
                                this.activeSection = hash;
                                break;
                            }
                        }
                    }

                    // Scroll listener for back to top
                    window.addEventListener('scroll', () => {
                        this.showBackToTop = window.scrollY > 400;
                    });
                },

                getActiveSections() {
                    return this.sections[this.activeTab] || [];
                },

                filterSections() {
                    // Simple search - just highlight or filter
                }
            }
        }
    </script>

    @include('partials.pwa-sw-register')
</body>

</html>