@props([
    'faculties' => [],
    'departments' => [],
    'studyPrograms' => [],
    'selectedFaculty' => null,
    'selectedDepartment' => null,
    'selectedStudyProgram' => null,
    'showFaculty' => true,
    'showDepartment' => true,
    'showStudyProgram' => true,
])

<div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4">
    @if($showFaculty)
    <!-- Faculty Filter -->
    <div>
        <label for="faculty_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Fakultas
        </label>
        <select 
            id="faculty_id" 
            name="faculty_id" 
            onchange="handleFacultyChange(this)"
            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
        >
            <option value="">Semua Fakultas</option>
            @foreach($faculties as $faculty)
                <option value="{{ $faculty['id'] }}" {{ $selectedFaculty == $faculty['id'] ? 'selected' : '' }}>
                    {{ $faculty['nama_en'] }}
                </option>
            @endforeach
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
            onchange="handleDepartmentChange(this)"
            {{ !$selectedFaculty ? 'disabled' : '' }}
            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
            <option value="">Semua Jurusan</option>
            @foreach($departments as $department)
                <option value="{{ $department['id'] }}" {{ $selectedDepartment == $department['id'] ? 'selected' : '' }}>
                    {{ $department['nama_en'] }}
                </option>
            @endforeach
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
            onchange="handleProgramChange(this)"
            {{ !$selectedDepartment ? 'disabled' : '' }}
            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
            <option value="">Semua Program Studi</option>
            @foreach($studyPrograms as $program)
                <option value="{{ $program['id'] }}" {{ $selectedStudyProgram == $program['id'] ? 'selected' : '' }}>
                    {{ $program['nama_en'] }}
                </option>
            @endforeach
        </select>
    </div>
    @endif
</div>

<script>
function handleFacultyChange(select) {
    // Reset department and program study when faculty changes
    const deptSelect = document.getElementById('department_id');
    const progSelect = document.getElementById('program_study_id');
    
    if (deptSelect) {
        deptSelect.value = '';
        deptSelect.disabled = !select.value;
    }
    if (progSelect) {
        progSelect.value = '';
        progSelect.disabled = true;
    }
    
    // Submit form immediately
    select.form.submit();
}

function handleDepartmentChange(select) {
    // Reset program study when department changes
    const progSelect = document.getElementById('program_study_id');
    
    if (progSelect) {
        progSelect.value = '';
        progSelect.disabled = !select.value;
    }
    
    // Submit form immediately
    select.form.submit();
}

function handleProgramChange(select) {
    // Submit form immediately when program is selected
    select.form.submit();
}
</script>
