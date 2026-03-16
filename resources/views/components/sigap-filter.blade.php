@props([
    'showFaculty' => true,
    'showDepartment' => true,
    'showStudyProgram' => true,
    'selectedFaculty' => null,
    'selectedDepartment' => null,
    'selectedStudyProgram' => null,
    'formId' => 'filter-form'
])

<div class="grid grid-cols-1 md:grid-cols-3 gap-4" x-data="sigapFilter({
    showFaculty: {{ $showFaculty ? 'true' : 'false' }},
    showDepartment: {{ $showDepartment ? 'true' : 'false' }},
    showStudyProgram: {{ $showStudyProgram ? 'true' : 'false' }},
    selectedFaculty: '{{ $selectedFaculty }}',
    selectedDepartment: '{{ $selectedDepartment }}',
    selectedStudyProgram: '{{ $selectedStudyProgram }}',
    formId: '{{ $formId }}'
})">
    @if($showFaculty)
    <!-- Faculty Filter -->
    <div>
        <label for="faculty_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Fakultas
        </label>
        <select 
            id="faculty_id" 
            name="faculty_id" 
            x-model="facultyId"
            @change="onFacultyChange"
            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
        >
            <option value="">Semua Fakultas</option>
            <template x-for="faculty in faculties" :key="faculty.id">
                <option :value="faculty.id" x-text="faculty.nama_en"></option>
            </template>
        </select>
    </div>
    @endif

    @if($showDepartment)
    <!-- Department Filter -->
    <div>
        <label for="department_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Jurusan
        </label>
        <select 
            id="department_id" 
            name="department_id" 
            x-model="departmentId"
            @change="onDepartmentChange"
            :disabled="!facultyId"
            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
            <option value="">Semua Jurusan</option>
            <template x-for="department in departments" :key="department.id">
                <option :value="department.id" x-text="department.nama_en"></option>
            </template>
        </select>
    </div>
    @endif

    @if($showStudyProgram)
    <!-- Study Program Filter -->
    <div>
        <label for="program_study_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Program Studi
        </label>
        <select 
            id="program_study_id" 
            name="program_study_id" 
            x-model="studyProgramId"
            @change="onStudyProgramChange"
            :disabled="!departmentId"
            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
            <option value="">Semua Program Studi</option>
            <template x-for="program in studyPrograms" :key="program.id">
                <option :value="program.id" x-text="program.nama_en"></option>
            </template>
        </select>
    </div>
    @endif
</div>

@push('scripts')
<script>
function sigapFilter(config) {
    return {
        faculties: [],
        departments: [],
        studyPrograms: [],
        facultyId: config.selectedFaculty || '',
        departmentId: config.selectedDepartment || '',
        studyProgramId: config.selectedStudyProgram || '',
        loading: false,

        init() {
            this.loadFaculties();
            
            // Load initial data if selections exist
            if (this.facultyId) {
                this.loadDepartments(this.facultyId);
            }
            if (this.departmentId) {
                this.loadStudyPrograms(this.departmentId);
            }
        },

        async loadFaculties() {
            try {
                const response = await fetch('/api/sigap/faculties');
                const data = await response.json();
                if (data.success) {
                    this.faculties = data.data;
                }
            } catch (error) {
                console.error('Error loading faculties:', error);
            }
        },

        async loadDepartments(facultyId) {
            if (!facultyId) {
                this.departments = [];
                this.studyPrograms = [];
                return;
            }

            try {
                const response = await fetch(`/api/sigap/departments?faculty_id=${facultyId}`);
                const data = await response.json();
                if (data.success) {
                    this.departments = data.data;
                }
            } catch (error) {
                console.error('Error loading departments:', error);
            }
        },

        async loadStudyPrograms(departmentId) {
            if (!departmentId) {
                this.studyPrograms = [];
                return;
            }

            try {
                const response = await fetch(`/api/sigap/study-programs?department_id=${departmentId}`);
                const data = await response.json();
                if (data.success) {
                    this.studyPrograms = data.data;
                }
            } catch (error) {
                console.error('Error loading study programs:', error);
            }
        },

        onFacultyChange() {
            this.departmentId = '';
            this.studyProgramId = '';
            this.departments = [];
            this.studyPrograms = [];
            
            if (this.facultyId) {
                this.loadDepartments(this.facultyId);
            }
            
            this.submitForm();
        },

        onDepartmentChange() {
            this.studyProgramId = '';
            this.studyPrograms = [];
            
            if (this.departmentId) {
                this.loadStudyPrograms(this.departmentId);
            }
            
            this.submitForm();
        },

        onStudyProgramChange() {
            this.submitForm();
        },

        submitForm() {
            // Auto-submit form when filter changes
            const form = document.getElementById(config.formId);
            if (form) {
                form.submit();
            }
        }
    }
}
</script>
@endpush
