@props(['name' => 'student_ids', 'required' => true, 'error' => null, 'multiple' => true, 'facultyId' => null])

<div x-data="studentMultiSearch()">
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
        Mahasiswa <span class="text-red-500">*</span>
        <span x-show="selectedStudents.length > 0" class="text-sm font-normal text-gray-500">
            (<span x-text="selectedStudents.length"></span> dipilih)
        </span>
    </label>

    <!-- Hidden inputs for form submission -->
    <template x-for="student in selectedStudents" :key="student.student_id">
        <input type="hidden" :name="'{{ $name }}[]'" :value="student.student_id">
    </template>

    <!-- Search Input -->
    <div class="relative">
        <div class="relative">
            <input type="text" x-model="searchQuery" @keydown.enter.prevent="searchStudents()"
                @focus="showDropdown = true" @click.away="showDropdown = false"
                placeholder="Ketik nama atau NIM mahasiswa lalu tekan Enter..."
                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl py-4 px-10 text-base font-medium focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all shadow-sm {{ $error ? 'border-red-500' : '' }}">
            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <div x-show="loading" class="absolute right-3 top-1/2 -translate-y-1/2">
                <svg class="w-5 h-5 text-purple-600 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
            </div>
            <button type="button" x-show="selectedStudents.length > 0" @click="clearAllSelections()"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-600 transition-colors"
                title="Hapus semua pilihan">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Dropdown Results -->
        <div x-show="showDropdown && (students.length > 0 || searchQuery.length >= 2)" x-cloak
            class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-80 overflow-y-auto">

            <!-- Loading State -->
            <div x-show="loading" class="p-4 text-center text-gray-500 dark:text-gray-400">
                <svg class="w-6 h-6 mx-auto animate-spin mb-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                Mencari mahasiswa...
            </div>

            <!-- No Results -->
            <div x-show="!loading && students.length === 0 && searchQuery.length >= 2"
                class="p-4 text-center text-gray-500 dark:text-gray-400">
                <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Tidak ada mahasiswa ditemukan
            </div>

            <!-- Results List -->
            <div x-show="!loading && students.length > 0">
                <template x-for="student in students" :key="student.student_id">
                    <button type="button" @click="toggleStudent(student)"
                        :class="isSelected(student.student_id) ? 'bg-purple-50 dark:bg-purple-900/30' : ''"
                        class="w-full px-4 py-3 text-left hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 last:border-b-0 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3 flex-1">
                                <!-- Checkbox -->
                                <div class="flex-shrink-0">
                                    <div :class="isSelected(student.student_id) ? 'bg-purple-600 border-purple-600' : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600'"
                                        class="w-5 h-5 rounded border-2 flex items-center justify-center transition-colors">
                                        <svg x-show="isSelected(student.student_id)" class="w-3 h-3 text-white"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900 dark:text-white" x-text="student.name"></p>
                                    <div class="flex items-center gap-3 mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                            </svg>
                                            <span x-text="student.student_id"></span>
                                        </span>
                                        <span x-text="student.faculty"></span>
                                        <span x-show="student.program" x-text="student.program"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </button>
                </template>
            </div>

            <!-- Hint -->
            <div x-show="searchQuery.length < 2 && students.length === 0"
                class="p-4 text-center text-sm text-gray-500 dark:text-gray-400">
                Ketik minimal 2 karakter untuk mencari mahasiswa
            </div>
        </div>
    </div>

    <!-- Selected Students Display -->
    <div x-show="selectedStudents.length > 0" x-cloak class="mt-3 space-y-2">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Mahasiswa Terpilih (<span x-text="selectedStudents.length"></span>)
            </p>
            <button type="button" @click="clearAllSelections()"
                class="text-xs text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 font-medium">
                Hapus Semua
            </button>
        </div>

        <div class="space-y-2 max-h-60 overflow-y-auto">
            <template x-for="student in selectedStudents" :key="student.student_id">
                <div
                    class="p-3 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-700 rounded-lg">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3 flex-1">
                            <div class="p-2 bg-purple-100 dark:bg-purple-900/40 rounded-lg flex-shrink-0">
                                <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 dark:text-white text-sm truncate"
                                    x-text="student.name"></p>
                                <div
                                    class="flex items-center gap-2 mt-1 text-xs text-gray-600 dark:text-gray-400 flex-wrap">
                                    <span x-text="student.student_id"></span>
                                    <span>•</span>
                                    <span x-text="student.faculty" class="truncate"></span>
                                    <template x-if="student.program">
                                        <span class="flex items-center gap-1">
                                            <span>•</span>
                                            <span x-text="student.program" class="truncate"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <button type="button" @click="removeStudent(student.student_id)"
                            class="text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    @if($error)
        <p class="text-red-500 text-sm mt-1">{{ $error }}</p>
    @endif
</div>

@once
    @push('scripts')
        <script>
            function studentMultiSearch() {
                return {
                    searchQuery: '',
                    students: [],
                    selectedStudents: [],
                    showDropdown: false,
                    loading: false,
                    facultyId: '{{ $facultyId ?? '' }}',

                    async searchStudents() {
                        const query = this.searchQuery.trim();

                        // Minimum 2 characters to search
                        if (query.length < 2) {
                            this.students = [];
                            return;
                        }

                        this.loading = true;
                        this.showDropdown = true;

                        try {
                            let searchUrl = `/api/sigap/students/search?q=${encodeURIComponent(query)}`;
                            if (this.facultyId) {
                                searchUrl += `&faculty_id=${encodeURIComponent(this.facultyId)}`;
                            }
                            const response = await fetch(searchUrl, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            });

                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }

                            const data = await response.json();
                            if (Array.isArray(data)) {
                                this.students = data;
                            } else {
                                this.students = data.data || data.students || [];
                            }
                        } catch (error) {
                            console.error('Error searching students:', error);
                            this.students = [];
                        } finally {
                            this.loading = false;
                        }
                    },

                    toggleStudent(student) {
                        const index = this.selectedStudents.findIndex(s => s.student_id === student.student_id);

                        if (index > -1) {
                            // Remove if already selected
                            this.selectedStudents.splice(index, 1);
                        } else {
                            // Add if not selected
                            this.selectedStudents.push(student);
                        }

                        // Clear search after selection
                        this.searchQuery = '';
                        this.students = [];
                        this.$dispatch('students-changed', this.selectedStudents);
                    },

                    removeStudent(studentId) {
                        const index = this.selectedStudents.findIndex(s => s.student_id === studentId);
                        if (index > -1) {
                            this.selectedStudents.splice(index, 1);
                            this.$dispatch('students-changed', this.selectedStudents);
                        }
                    },

                    clearAllSelections() {
                        this.selectedStudents = [];
                        this.searchQuery = '';
                        this.students = [];
                        this.showDropdown = false;
                        this.$dispatch('students-changed', this.selectedStudents);
                    },

                    isSelected(studentId) {
                        return this.selectedStudents.some(s => s.student_id === studentId);
                    }
                }
            }
        </script>
    @endpush
@endonce