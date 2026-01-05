<!DOCTYPE html>
<html lang="id" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Dashboard Validator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen transition-colors duration-300">
    <nav class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-teal-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h1 class="text-xl font-bold text-gray-800 dark:text-white">Panel Validator</h1>
            </div>
            <div class="flex items-center gap-4">
                <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700">
                    <svg x-show="!darkMode" class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <svg x-show="darkMode" class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>
                <span class="text-gray-600 dark:text-gray-300 font-medium">{{ auth()->user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        <!-- Info Validator dengan Foto -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 mb-6 border border-gray-200 dark:border-gray-700">
            <div class="flex flex-col md:flex-row gap-6">
                <div class="flex-shrink-0">
                    <img src="{{ auth()->user()->photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=10b981&color=fff&size=128' }}" 
                         alt="Foto Profil" class="w-24 h-24 rounded-2xl object-cover border-4 border-green-100 dark:border-green-900/50 shadow-lg">
                </div>
                <div class="flex-1">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-1">{{ auth()->user()->name }}</h2>
                    <p class="text-green-600 dark:text-green-400 font-medium mb-3">{{ auth()->user()->role }}</p>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Validasi prestasi mahasiswa yang menunggu persetujuan</p>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="flex gap-4 mb-6 border-b border-gray-200 dark:border-gray-700">
            <a href="{{ route('validator.dashboard') }}" class="pb-3 px-1 border-b-2 border-green-500 text-green-600 dark:text-green-400 font-medium">Menunggu Validasi</a>
            <a href="{{ route('validator.history') }}" class="pb-3 px-1 border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">Riwayat</a>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Mahasiswa</th>
                            <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Prestasi</th>
                            <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Level</th>
                            <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Tanggal</th>
                            <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($pendingAchievements as $ach)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-800 dark:text-white">{{ $ach->student->name ?? '-' }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $ach->student->student_id ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-gray-800 dark:text-gray-200">{{ $ach->event_name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $ach->achievement->name ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $ach->level }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ \Carbon\Carbon::parse($ach->event_date)->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <form action="/validator/achievements/{{ $ach->sa_id }}/approve" method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-xs font-medium rounded-lg">Setujui</button>
                                    </form>
                                    <button onclick="rejectPrestasi({{ $ach->sa_id }})" class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs font-medium rounded-lg">Tolak</button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Tidak ada prestasi menunggu validasi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function rejectPrestasi(saId) {
            const notes = prompt("Masukkan alasan penolakan:");
            if (!notes || notes.trim() === "") return;
            fetch(`/validator/achievements/${saId}/reject`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ notes })
            }).then(r => r.ok ? location.reload() : alert('Gagal menolak.'));
        }
    </script>
<script src="https://instant.page/5.2.0" type="module" integrity="sha384-jnZyxPjiipYXnSU0ber8UYWa/3y+LA2aLGeB5rWGKbgsNJYgLAw0qauPVhSQqxr4"></script>
</body>
</html>
