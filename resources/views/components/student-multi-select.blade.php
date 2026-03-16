<div x-data="studentMultiSelect()" class="space-y-4">
    <!-- Search Input -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Cari Mahasiswa
        </label>
        <div class="relative">
            <input 
                type="text" 
                x-model="searchQuery"
                @input.debounce.300ms="searchStudents()"
                @focus="showResults = true"
                placeholder="Ketik nama atau NIM mahasiswa..."
                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500 pl-10"
            >
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            
            <!-- Loading Indicator -->
            <div x-show="loading" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <svg class="animate-spin h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>

        <!-- Search Results Dropdown -->
        <div 
            x-show="showResults && searchResults.length > 0" 
            @click.away="showResults = false"
            class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
        >
            <template x-for="student in searchResults" :key="student.id">
                <div 
                    @click="selectStudent(student)"
                    class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-emerald-50 dark:hover:bg-emerald-900/20"
                >
                    <div class="flex items-center">
                        <span class="font-medium text-gray-900 dark:text-white block truncate" x-text="student.name"></span>
                    </div>
                    <div class="flex items-center space-x-2 mt-1">
                        <span class="text-xs text-gray-500 dark:text-gray-400" x-text="student.student_id"></span>
                        <span class="text-xs text-gray-400 dark:text-gray-500">•</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400" x-text="student.program_study"></span>
                    </div>
                </div>
            </template>
        </div>

        <!-- No Results -->
        <div 
            x-show="showResults && searchQuery.length >= 3 && searchResults.length === 0 && !loading"
            class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg rounded-md py-3 text-center text-sm text-gray-500 dark:text-gray-400"
        >
            Tidak ada mahasiswa ditemukan
        </div>
    </div>

    <!-- Selected Students -->
    <div x-show="selectedStudents.length > 0">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Mahasiswa Terpilih (<span x-text="selectedStudents.length"></span>)
        </label>
        <div class="space-y-2 max-h-48 overflow-y-auto">
            <template x-for="student in selectedStudents" :key="student.id">
                <div class="flex items-center justify-between p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-emerald-100 dark:bg-emerald-900 rounded-full flex items-center justify-center">
                            <span class="text-sm font-bold text-emerald-600 dark:text-emerald-300" x-text="student.name.charAt(0)"></span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="student.name"></p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                <span x-text="student.student_id"></span> • <span x-text="student.program_study"></span>
                            </p>
                        </div>
                    </div>
                    <button 
                        type="button"
                        @click="removeStudent(student.id)"
                        class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </template>
        </div>

        <!-- Hidden inputs for form submission -->
        <template x-for="student in selectedStudents" :key="student.id">
            <input type="hidden" name="student_ids[]" :value="student.id">
        </template>
    </div>

    <!-- Empty State -->
    <div x-show="selectedStudents.length === 0" class="text-center py-8 bg-gray-50 dark:bg-gray-700 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Belum ada mahasiswa dipilih</p>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Gunakan pencarian di atas untuk menambah mahasiswa</p>
    </div>
</div>

<script>
function studentMultiSelect() {
    return {
        searchQuery: '',
        searchResults: [],
        selectedStudents: [],
        showResults: false,
        loading: false,

        async searchStudents() {
            if (this.searchQuery.length < 3) {
                this.searchResults = [];
                return;
            }

            this.loading = true;
            
            try {
                const response = await fetch(`{{ $searchUrl ?? route('validator.students.search') }}?q=${encodeURIComponent(this.searchQuery)}`);
                const data = await response.json();
                
                // Filter out already selected students
                this.searchResults = data.filter(student => 
                    !this.selectedStudents.some(selected => selected.id === student.id)
                );
            } catch (error) {
                console.error('Error searching students:', error);
                this.searchResults = [];
            } finally {
                this.loading = false;
            }
        },

        selectStudent(student) {
            if (!this.selectedStudents.some(s => s.id === student.id)) {
                this.selectedStudents.push(student);
            }
            this.searchQuery = '';
            this.searchResults = [];
            this.showResults = false;
        },

        removeStudent(studentId) {
            this.selectedStudents = this.selectedStudents.filter(s => s.id !== studentId);
        }
    }
}
</script>
