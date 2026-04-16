@extends('layouts.validator')

@section('title', 'Manajemen SK')

@section('content')
    <div class="max-w-7xl mx-auto" x-data="skManagement()">
        <div class="max-w-7xl mx-auto" x-data="skManagement()">
            <!-- Integrated SK Table Card -->
            <div
                class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                <div class="px-5 lg:px-6 xl:px-8 py-4 lg:py-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-base lg:text-lg xl:text-xl font-semibold text-gray-900 dark:text-white">
                                Manajemen Surat Keputusan (SK)</h2>
                        </div>

                        <!-- Compact Search and Actions -->
                        <div class="flex items-center gap-3">
                            <div class="relative w-full lg:w-72">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input type="text" id="skSearch" placeholder="Cari Nomor SK atau Judul..."
                                    class="pl-9 w-full py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 transition-all">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Nomor SK</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Judul</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Metode Upload</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Tanggal Terbit</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Penerbit</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($skDocuments as $sk)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ $sk->sk_number }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-gray-900 dark:text-white">{{ $sk->title }}</span>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ $sk->assignments_count }} prestasi ter-assign
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($sk->file_type === 'file')
                                            <span
                                                class="px-2 py-1 text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full">File
                                                PDF</span>
                                        @elseif($sk->file_type === 'link')
                                            <span
                                                class="px-2 py-1 text-xs bg-purple-100 dark:bg-purple-900/30 text-cyan-600 dark:text-cyan-400 rounded-full">Link
                                                Eksternal</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400">
                                        {{ $sk->issued_date->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                        {{ $sk->issued_by }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-2">
                                            <!-- Lihat Dokumen -->
                                            @if($sk->file_type === 'file')
                                                <a href="{{ route('validator.sk.preview', $sk) }}" target="_blank"
                                                    class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                                                    title="Lihat Dokumen">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </a>
                                            @elseif($sk->file_type === 'link')
                                                <a href="{{ $sk->external_link }}" target="_blank"
                                                    class="p-2 text-cyan-600 hover:bg-purple-50 dark:hover:bg-purple-900/30 rounded-lg transition-colors"
                                                    title="Buka Link">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                    </svg>
                                                </a>
                                            @endif

                                            <!-- Assign ke Prestasi -->
                                            <button
                                                @click="openAssignModal({{ $sk->id }}, '{{ addslashes($sk->sk_number) }}', '{{ addslashes($sk->title) }}')"
                                                class="p-2 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/30 rounded-lg transition-colors"
                                                title="Assign ke Prestasi">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="text-gray-500 dark:text-gray-400">Belum ada SK yang tersedia</p>
                                        <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Hubungi admin untuk upload SK
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($skDocuments->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $skDocuments->links() }}
                    </div>
                @endif
            </div>

            <!-- Assign Modal -->
            <div x-show="showAssignModal" x-cloak @click.self="closeAssignModal()"
                @keydown.escape.window="closeAssignModal()"
                class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                <div @click.stop x-show="showAssignModal" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-6xl w-full max-h-[90vh] overflow-hidden flex flex-col">

                    <!-- Modal Header -->
                    <div
                        class="bg-gradient-to-r from-purple-50 to-blue-50 dark:from-purple-900/20 dark:to-blue-900/20 border-b border-purple-200 dark:border-purple-800 px-6 py-4">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-purple-600 rounded-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Assign SK ke Prestasi</h3>
                                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1"
                                        x-text="'SK: ' + selectedSk.number"></p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400" x-text="selectedSk.title"></p>
                                </div>
                            </div>
                            <button @click="closeAssignModal()"
                                class="p-2 hover:bg-white/50 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="flex-1 overflow-y-auto p-6">
                        <div x-show="loading" class="flex items-center justify-center py-12">
                            <svg class="animate-spin h-8 w-8 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </div>

                        <div x-show="!loading && achievements.length === 0" class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="text-gray-500 dark:text-gray-400 font-medium">Tidak ada prestasi yang tersedia</p>
                            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Tidak ada prestasi dengan status
                                "Menunggu"
                                yang belum memiliki SK</p>
                        </div>

                        <div x-show="!loading && achievements.length > 0">
                            <!-- Search Bar -->
                            <div class="mb-4">
                                <div class="relative">
                                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    <input type="text" x-model="searchQuery"
                                        class="w-full pl-10 pr-4 py-3 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                        placeholder="Cari berdasarkan NIM, nama mahasiswa, atau nama lomba...">
                                </div>
                            </div>



                            <!-- Table -->
                            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                                        <tr>
                                            <th class="px-4 py-3 text-left w-12">
                                                <span class="sr-only">Select</span>
                                            </th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                NIM</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                Nama Mahasiswa</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                Nama Lomba</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                Kategori</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                Tingkat</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                Tanggal Event</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        <template x-for="achievement in filteredAchievements" :key="achievement.sa_id">
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                                <td class="px-4 py-3">
                                                    <input type="checkbox" :value="achievement.sa_id"
                                                        @change="toggleAchievement(achievement.sa_id)"
                                                        :checked="selectedAchievements.includes(achievement.sa_id)"
                                                        class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500 dark:bg-gray-700 dark:border-gray-600">
                                                </td>
                                                <td class="px-4 py-3 text-gray-900 dark:text-white font-medium"
                                                    x-text="achievement.student.student_id"></td>
                                                <td class="px-4 py-3 text-gray-900 dark:text-white"
                                                    x-text="achievement.student.name"></td>
                                                <td class="px-4 py-3 text-gray-900 dark:text-white"
                                                    x-text="achievement.event_name"></td>
                                                <td class="px-4 py-3 text-gray-600 dark:text-gray-400"
                                                    x-text="achievement.achievement.category.name"></td>
                                                <td class="px-4 py-3">
                                                    <span
                                                        class="px-2 py-1 text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full"
                                                        x-text="achievement.level"></span>
                                                </td>
                                                <td class="px-4 py-3 text-gray-600 dark:text-gray-400"
                                                    x-text="formatDate(achievement.event_date)"></td>
                                            </tr>
                                        </template>
                                        <!-- Empty state or Search prompt -->
                                        <tr x-show="filteredAchievements.length === 0">
                                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                                <span x-show="!searchQuery" class="flex flex-col items-center">
                                                    <svg class="w-8 h-8 mb-2 text-gray-400" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                    </svg>
                                                    Silakan masukkan NIM atau Nama Mahasiswa pada kolom pencarian untuk
                                                    memulai.
                                                </span>
                                                <span x-show="searchQuery" class="flex flex-col items-center">
                                                    <svg class="w-8 h-8 mb-2 text-gray-400" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <span>Tidak ada prestasi yang cocok dengan pencarian "<span
                                                            x-text="searchQuery"></span>".</span>
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Notes -->
                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Catatan (Opsional)
                                </label>
                                <textarea x-model="notes" rows="2"
                                    class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                    placeholder="Catatan untuk assignment ini"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div x-show="!loading && achievements.length > 0"
                        class="border-t border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-700/50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                                <span class="text-gray-700 dark:text-gray-300">
                                    <span class="font-bold text-purple-600 dark:text-purple-400"
                                        x-text="selectedAchievements.length"></span> prestasi dipilih
                                </span>
                            </div>
                            <div class="flex gap-3">
                                <button type="button" @click="closeAssignModal()"
                                    class="px-4 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg flex items-center gap-2 transition-colors font-medium">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Batal
                                </button>
                                <button type="button" @click="submitAssignment()"
                                    :disabled="selectedAchievements.length === 0 || submitting"
                                    class="px-4 py-2.5 bg-green-600 hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white rounded-lg flex items-center gap-2 transition-colors font-medium">
                                    <svg x-show="!submitting" class="w-5 h-5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <svg x-show="submitting" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    <span x-text="submitting ? 'Memproses...' : 'Assign SK & Approve Prestasi'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function skManagement() {
                return {
                    showAssignModal: false,
                    selectedSk: {},
                    achievements: [],
                    selectedAchievements: [],
                    searchQuery: '',
                    notes: '',
                    loading: false,
                    submitting: false,

                    init() {
                        const urlParams = new URLSearchParams(window.location.search);
                        const assignSkId = urlParams.get('assign');
                        if (assignSkId) {
                            // We need to find the SK details from the table if possible, 
                            // or just call openAssignModal with IDs if we can get them.
                            // Since we only have skId from URL, we might need skNumber/Title too.
                            // For simplicity, let's just use the ID and fetch details if needed, 
                            // or just wait for the user to click.
                            // Alternatively, we can just pass the SK number/title in the URL too.
                            const skNumber = urlParams.get('number') || '';
                            const skTitle = urlParams.get('title') || '';
                            this.openAssignModal(assignSkId, skNumber, skTitle);
                        }
                    },

                    get filteredAchievements() {
                        if (!this.searchQuery) return [];

                        const query = this.searchQuery.toLowerCase();
                        return this.achievements.filter(a => {
                            return a.student.student_id.toLowerCase().includes(query) ||
                                a.student.name.toLowerCase().includes(query) ||
                                a.event_name.toLowerCase().includes(query);
                        });
                    },

                    async openAssignModal(skId, skNumber, skTitle) {
                        this.selectedSk = { id: skId, number: skNumber, title: skTitle };
                        this.showAssignModal = true;
                        this.loading = true;
                        this.achievements = [];
                        this.selectedAchievements = [];
                        this.searchQuery = '';
                        this.notes = '';

                        try {
                            const response = await fetch(`/validator/sk/${skId}/achievements`);
                            const data = await response.json();
                            this.achievements = data.achievements || [];
                        } catch (error) {
                            console.error('Error loading achievements:', error);
                            showToast('error', 'Gagal memuat data prestasi');
                        } finally {
                            this.loading = false;
                        }
                    },

                    closeAssignModal() {
                        this.showAssignModal = false;
                        this.selectedSk = {};
                        this.achievements = [];
                        this.selectedAchievements = [];
                        this.searchQuery = '';
                        this.notes = '';
                    },

                    toggleAchievement(saId) {
                        const index = this.selectedAchievements.indexOf(saId);
                        if (index > -1) {
                            this.selectedAchievements.splice(index, 1);
                        } else {
                            this.selectedAchievements.push(saId);
                        }
                    },

                    toggleAll(checked) {
                        if (checked) {
                            this.selectedAchievements = this.filteredAchievements.map(a => a.sa_id);
                        } else {
                            this.selectedAchievements = [];
                        }
                    },

                    selectAll() {
                        this.selectedAchievements = this.filteredAchievements.map(a => a.sa_id);
                    },

                    deselectAll() {
                        this.selectedAchievements = [];
                    },

                    async submitAssignment() {
                        if (this.selectedAchievements.length === 0) {
                            showToast('warning', 'Pilih minimal 1 prestasi');
                            return;
                        }

                        window.showConfirm(`Assign SK ke ${this.selectedAchievements.length} prestasi dan approve semuanya?`, async () => {
                            this.submitting = true;

                            try {
                                const formData = new FormData();
                                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                                this.selectedAchievements.forEach(id => {
                                    formData.append('achievement_ids[]', id);
                                });
                                if (this.notes) {
                                    formData.append('notes', this.notes);
                                }

                                const response = await fetch(`/validator/sk/${this.selectedSk.id}/process-assignment`, {
                                    method: 'POST',
                                    body: formData
                                });

                                if (response.ok) {
                                    window.location.reload();
                                } else {
                                    showToast('error', 'Gagal memproses assignment');
                                }
                            } catch (error) {
                                console.error('Error submitting assignment:', error);
                                showToast('error', 'Terjadi kesalahan saat memproses assignment');
                            } finally {
                                this.submitting = false;
                            }
                        }, 'success', 'Konfirmasi Assign SK');
                    },

                    formatDate(dateString) {
                        const date = new Date(dateString);
                        return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
                    }
                }
            }
        </script>

        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>
@endsection