<!DOCTYPE html>
<html lang="id" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Dashboard Mahasiswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen transition-colors duration-300">
    <nav class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
                <h1 class="text-xl font-bold text-gray-800 dark:text-white">Portal Prestasi</h1>
            </div>
            <div class="flex items-center gap-4">
                <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    <svg x-show="!darkMode" class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <svg x-show="darkMode" class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>
                <span class="text-gray-600 dark:text-gray-300 font-medium">{{ $student->name ?? 'Mahasiswa' }}</span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        @if(session('success'))
        <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 p-4 rounded-xl mb-6">
            {{ session('success') }}
        </div>
        @endif

        <!-- Info Mahasiswa dengan Foto -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 mb-6 border border-gray-200 dark:border-gray-700">
            <div class="flex flex-col md:flex-row gap-6">
                <!-- Foto Profil -->
                <div class="flex-shrink-0">
                    <img src="{{ $student->photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($student->name ?? 'M') . '&background=3b82f6&color=fff&size=128' }}" 
                         alt="Foto Profil" 
                         class="w-32 h-32 rounded-2xl object-cover border-4 border-blue-100 dark:border-blue-900/50 shadow-lg">
                </div>
                <!-- Informasi -->
                <div class="flex-1">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-1">{{ $student->name ?? '-' }}</h2>
                    <p class="text-blue-600 dark:text-blue-400 font-medium mb-4">{{ $student->student_id ?? '-' }}</p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                            <span class="text-gray-500 dark:text-gray-400 text-xs">Fakultas</span>
                            <p class="font-semibold text-gray-800 dark:text-white">{{ $student->faculty ?? '-' }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                            <span class="text-gray-500 dark:text-gray-400 text-xs">Program Studi</span>
                            <p class="font-semibold text-gray-800 dark:text-white">{{ $student->program_study ?? '-' }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                            <span class="text-gray-500 dark:text-gray-400 text-xs">Semester</span>
                            <p class="font-semibold text-gray-800 dark:text-white">{{ $student->semester ?? '-' }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                            <span class="text-gray-500 dark:text-gray-400 text-xs">IPK</span>
                            <p class="font-semibold text-gray-800 dark:text-white">{{ $student->gpa ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tombol Submit -->
        <div class="mb-6">
            <a href="{{ route('student.achievement.create') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white px-5 py-3 rounded-xl font-medium shadow-lg hover:shadow-xl transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Ajukan Prestasi Baru
            </a>
        </div>

        <!-- Daftar Prestasi -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Daftar Prestasi Saya</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Nama Event</th>
                            <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Jenis</th>
                            <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Level</th>
                            <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Tanggal</th>
                            <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($studentAchievements as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-4 py-3 text-gray-800 dark:text-gray-200">{{ $item->event_name }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $item->achievement->category ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $item->level }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ \Carbon\Carbon::parse($item->event_date)->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $statusColor = match($item->validation_status) {
                                        'Disetujui' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                        'Ditolak' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                        default => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                    };
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColor }}">{{ $item->validation_status }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Belum ada prestasi yang diajukan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<script src="https://instant.page/5.2.0" type="module" integrity="sha384-jnZyxPjiipYXnSU0ber8UYWa/3y+LA2aLGeB5rWGKbgsNJYgLAw0qauPVhSQqxr4"></script>
</body>
</html>
