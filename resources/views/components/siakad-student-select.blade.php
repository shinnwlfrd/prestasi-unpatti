@props(['name' => 'student_ids', 'required' => false, 'error' => null, 'multiple' => true, 'selected' => '[]'])

<div x-data="siakadStudentSearch()" x-init="init('{!! addslashes($selected) !!}')" class="relative">
    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
        Pilih Mahasiswa @if($required)<span class="text-red-500">*</span>@endif
    </label>

    <!-- Hidden inputs for form submission -->
    <template x-for="student in selectedStudents" :key="student.id">
        <input type="hidden" :name="'{{ $name }}[]'" :value="student.id">
    </template>

    <!-- Search Input Area -->
    <div class="relative group">
        <div class="relative">
            <input type="text" x-model="searchQuery" 
                @keydown.enter.prevent="searchStudents()"
                @input="if (searchQuery.trim().length === 0) { results = []; hasSearched = false; showDropdown = false; }"
                @focus="if (searchQuery.trim().length >= 2) showDropdown = true" 
                placeholder="Cari NIM atau Nama lalu tekan Enter (min. 2 karakter)..."
                class="w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 dark:text-white rounded-xl py-4 px-12 text-base font-medium focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 transition-all shadow-sm @if($error) border-red-500 @endif">

            <svg class="w-5 h-5 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>

            <div x-show="loading" class="absolute right-4 top-1/2 -translate-y-1/2">
                <svg class="w-5 h-5 text-purple-600 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
            </div>
        </div>

        <!-- Dropdown Results -->
        <div x-show="showDropdown && (results.length > 0 || hasSearched)" x-cloak @click.away="showDropdown = false"
            class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl overflow-hidden animate-in fade-in slide-in-from-top-2 duration-200">
            <div x-show="loading" class="p-8 text-center">
                <svg class="w-8 h-8 mx-auto text-purple-500 animate-spin mb-3" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Mencari mahasiswa...</p>
            </div>

            <div x-show="!loading && results.length === 0 && hasSearched" class="p-8 text-center">
                <div
                    class="w-12 h-12 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Mahasiswa tidak ditemukan</p>
            </div>

            <div x-show="!loading && results.length > 0" class="max-h-72 overflow-y-auto custom-scrollbar">
                <template x-for="student in results" :key="student.id">
                    <button type="button" @click="toggleStudent(student)"
                        class="w-full px-5 py-4 text-left hover:bg-purple-50 dark:hover:bg-purple-900/20 border-b border-gray-100 dark:border-gray-700 last:border-b-0 transition-all flex items-center justify-between group">
                        <div class="flex items-center gap-4 overflow-hidden">
                            <div
                                class="w-10 h-10 bg-gray-100 dark:bg-gray-700 group-hover:bg-purple-100 dark:group-hover:bg-purple-900/40 rounded-xl flex items-center justify-center text-gray-400 group-hover:text-purple-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div class="overflow-hidden">
                                <p class="font-bold text-gray-900 dark:text-white truncate"
                                    x-text="student.nim + ' - ' + student.nama"></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5"
                                    x-text="student.prodi"></p>
                            </div>
                        </div>
                        <template x-if="isSelected(student.id)">
                            <div class="text-purple-600 animate-in zoom-in duration-200">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </template>
                    </button>
                </template>
            </div>
        </div>
    </div>

    <!-- Quick selection info -->
    <div x-show="selectedStudents.length > 0" class="mt-3 flex flex-wrap gap-2">
        <template x-for="student in selectedStudents" :key="student.id">
            <span
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 text-[10px] font-bold uppercase tracking-wider animate-in fade-in zoom-in duration-300">
                <span x-text="student.nim"></span>
                <button type="button" @click="removeStudent(student.id)"
                    class="hover:text-purple-900 dark:hover:text-white transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </span>
        </template>
    </div>

    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Ketik minimal 2 karakter (NIM atau nama) lalu tekan Enter untuk mencari mahasiswa dari SIAKAD
    </p>

    @if($error)
        <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1 font-medium italic">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                    clip-rule="evenodd" />
            </svg>
            {{ $error }}
        </p>
    @endif
</div>

@once
    @push('scripts')
        <script>
            function siakadStudentSearch() {
                return {
                    searchQuery: '',
                    results: [],
                    selectedStudents: [],
                    showDropdown: false,
                    loading: false,
                    hasSearched: false,

                    init(initialData) {
                        if (initialData) {
                            try {
                                const parsed = JSON.parse(initialData);
                                if (Array.isArray(parsed)) {
                                    this.selectedStudents = parsed;
                                    // Dispatch initial state to sync parent
                                    this.$nextTick(() => this.dispatchChange());
                                }
                            } catch (e) {
                                console.error('Error parsing initial student data:', e);
                            }
                        }
                    },

                    async searchStudents() {
                        const query = this.searchQuery.trim();
                        
                        // Require minimum 2 characters
                        if (query.length < 2) {
                            this.results = [];
                            this.hasSearched = false;
                            this.showDropdown = false;
                            return;
                        }

                        this.loading = true;
                        this.showDropdown = true;
                        this.hasSearched = true;

                        try {
                            const response = await fetch(`/api/siakad/mahasiswa/search?search=${encodeURIComponent(query)}`, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            });

                            if (!response.ok) throw new Error('Network error');

                            const data = await response.json();
                            let filteredResults = data.results || [];

                            // Priority: If searching with a numeric NIM and there's an exact match, show only that
                            if (query.match(/^[0-9]+$/)) {
                                const exactMatch = filteredResults.find(s => s.nim === query);
                                if (exactMatch) {
                                    filteredResults = [exactMatch];
                                }
                            }

                            this.results = filteredResults;
                        } catch (error) {
                            console.error('Error searching mahasiswa:', error);
                            this.results = [];
                        } finally {
                            this.loading = false;
                        }
                    },

                    toggleStudent(student) {
                        const index = this.selectedStudents.findIndex(s => s.id === student.id);

                        if (index === -1) {
                            // Map to what submit.blade.php expects
                            const studentData = {
                                id: student.id,
                                student_id: student.id, // Keep UUID as student_id for attachment index
                                nim: student.nim,
                                name: student.nama, // Mapping nama -> name
                                faculty: student.fakultas, // Mapping fakultas -> faculty
                                prodi: student.prodi
                            };

                            @if($multiple)
                                this.selectedStudents.push(studentData);
                            @else
                                this.selectedStudents =[studentData];
                                this.showDropdown = false;
                            @endif
                            this.searchQuery = '';
                        } else {
                            @if($multiple)
                                this.selectedStudents.splice(index, 1);
                            @else
                                this.selectedStudents =[];
                            @endif
                                            }

                        this.dispatchChange();
                    },

                    removeStudent(id) {
                        this.selectedStudents = this.selectedStudents.filter(s => s.id !== id);
                        this.dispatchChange();
                    },

                    isSelected(id) {
                        return this.selectedStudents.some(s => s.id === id);
                    },

                    dispatchChange() {
                        // Use both standard Alpine dispatch and window dispatch for maximum reliability
                        this.$dispatch('students-changed', this.selectedStudents);
                        window.dispatchEvent(new CustomEvent('students-changed', {
                            detail: this.selectedStudents
                        }));
                    }
                }
            }
        </script>
    @endpush
@endonce