<!DOCTYPE html>
<html lang="id" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true', showModal: false, modalType: '', currentSaId: null, showCertificate: false, certificateUrl: '' }" :class="{ 'dark': darkMode }">
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
        @if(session('success'))
        <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 p-4 rounded-xl mb-6">
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 p-4 rounded-xl mb-6">
            <ul class="list-disc list-inside">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
        @endif

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

        <!-- Tombol Submit Prestasi -->
        <div class="mb-6">
            <a href="{{ route('validator.submit.form') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-green-500 to-teal-600 hover:from-green-600 hover:to-teal-700 text-white px-5 py-3 rounded-xl font-medium shadow-lg hover:shadow-xl transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Ajukan Prestasi Mahasiswa
            </a>
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
                            <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">Bukti</th>
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
                                @if($ach->certificate)
                                <button @click="showCertificate = true; certificateUrl = '{{ asset('storage/' . $ach->certificate) }}'" class="text-blue-600 dark:text-blue-400 hover:underline text-xs font-medium">
                                    Lihat Bukti
                                </button>
                                @else
                                <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <button @click="showModal = true; modalType = 'approve'; currentSaId = {{ $ach->sa_id }}" class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-xs font-medium rounded-lg">Setujui</button>
                                    <button @click="showModal = true; modalType = 'reject'; currentSaId = {{ $ach->sa_id }}" class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs font-medium rounded-lg">Tolak</button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Tidak ada prestasi menunggu validasi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Lihat Bukti -->
    <div x-show="showCertificate" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showCertificate" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75 transition-opacity" @click="showCertificate = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div x-show="showCertificate" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Bukti Prestasi</h3>
                        <button @click="showCertificate = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-4 min-h-96 flex items-center justify-center">
                        <img :src="certificateUrl" alt="Bukti Prestasi" class="max-w-full max-h-96 object-contain">
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                    <a :href="certificateUrl" target="_blank" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-500 hover:bg-blue-600 text-base font-medium text-white focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Download</a>
                    <button type="button" @click="showCertificate = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Validasi -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75 transition-opacity" @click="showModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form :action="'/validator/achievements/' + currentSaId + '/' + modalType" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div :class="modalType === 'approve' ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30'" class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                                <svg x-show="modalType === 'approve'" class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <svg x-show="modalType === 'reject'" class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title" x-text="modalType === 'approve' ? 'Setujui Prestasi' : 'Tolak Prestasi'"></h3>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Tipe Validasi <span class="text-red-500">*</span>
                                        </label>
                                        <select name="validation_type" required class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white text-sm focus:ring-2 focus:ring-green-500">
                                            <option value="">-- Pilih Tipe Validasi --</option>
                                            <option value="SK Resmi">SK Resmi (SK Lomba/Kompetisi)</option>
                                            <option value="Dokumen Internal">Dokumen Internal (Surat Kampus)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Upload Dokumen Validasi <span class="text-red-500">*</span>
                                        </label>
                                        <input type="file" name="sk_document" required accept=".pdf,.jpg,.jpeg,.png" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-green-50 file:text-green-700 dark:file:bg-green-900/30 dark:file:text-green-400">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Format: PDF, JPG, PNG. Maks: 5MB</p>
                                    </div>
                                    <div x-show="modalType === 'reject'">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Alasan Penolakan <span class="text-red-500">*</span>
                                        </label>
                                        <textarea name="notes" rows="3" :required="modalType === 'reject'" placeholder="Masukkan alasan penolakan..." class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white text-sm focus:ring-2 focus:ring-red-500"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button type="submit" :class="modalType === 'approve' ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600'" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none sm:ml-3 sm:w-auto sm:text-sm" x-text="modalType === 'approve' ? 'Setujui' : 'Tolak'"></button>
                        <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>[x-cloak] { display: none !important; }</style>
    <script src="https://instant.page/5.2.0" type="module" integrity="sha384-jnZyxPjiipYXnSU0ber8UYWa/3y+LA2aLGeB5rWGKbgsNJYgLAw0qauPVhSQqxr4"></script>
</body>
</html>
