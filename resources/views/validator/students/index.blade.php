@extends('layouts.validator')

@section('title', 'Daftar Mahasiswa')

@section('content')
    @php
        $user = auth()->user();
        $currentRole = $user->getCurrentRole();
        $isPimpinan = $currentRole && $currentRole->role === 'pimpinan';
        $routePrefix = $isPimpinan ? 'pimpinan' : 'validator';
    @endphp

    <div class="space-y-6">
        <!-- Table with Integrated Filter -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm"
             x-data="{ 
                selectedFaculty: '{{ request('faculty', (count($facultyList) === 1 ? $facultyList[0] : '')) }}',
                selectedDept: '{{ request('department') }}',
                selectedProdi: '{{ request('program_study') }}',
                facultyToDept: {{ json_encode($facultyToDept) }},
                deptToProdi: {{ json_encode($deptToProdi) }},
                deptToFaculty: {{ json_encode($deptToFaculty) }},
                prodiToDept: {{ json_encode($prodiToDept) }},
                prodiToFaculty: {{ json_encode($prodiToFaculty) }},
                allDepts: {{ json_encode($allDepts) }},
                allProdis: {{ json_encode($allProdis) }},
                userChangedProdi: false,
                userChangedDept: false,
                
                init() {
                    console.log('Initialized with:', {
                        selectedFaculty: this.selectedFaculty,
                        selectedDept: this.selectedDept,
                        selectedProdi: this.selectedProdi,
                        prodiToDept: this.prodiToDept,
                        prodiToFaculty: this.prodiToFaculty
                    });
                },
                
                get availableDepts() {
                    if (this.userChangedProdi && this.selectedProdi) {
                        return this.allDepts;
                    }
                    if (this.selectedFaculty) {
                        return this.facultyToDept[this.selectedFaculty] || [];
                    }
                    return this.allDepts;
                },

                get availableProdis() {
                    if (this.userChangedProdi) {
                        return this.allProdis;
                    }
                    if (this.selectedDept) {
                        return this.deptToProdi[this.selectedDept] || [];
                    }
                    return this.allProdis;
                },

                onFacultyChange() {
                    this.userChangedProdi = false;
                    this.userChangedDept = false;
                    const availableDepts = this.facultyToDept[this.selectedFaculty] || [];
                    if (this.selectedDept && !availableDepts.includes(this.selectedDept)) {
                        this.selectedDept = '';
                        this.selectedProdi = '';
                    }
                },

                onDeptChange() {
                    this.userChangedDept = true;
                    this.userChangedProdi = false;
                    if (this.selectedDept && this.deptToFaculty[this.selectedDept]) {
                        this.selectedFaculty = this.deptToFaculty[this.selectedDept];
                    }
                    const availableProdis = this.deptToProdi[this.selectedDept] || [];
                    if (this.selectedProdi && !availableProdis.includes(this.selectedProdi)) {
                        this.selectedProdi = '';
                    }
                },

                onProdiChange() {
                    this.userChangedProdi = true;
                    if (this.selectedProdi) {
                        if (this.prodiToDept[this.selectedProdi]) {
                            this.selectedDept = this.prodiToDept[this.selectedProdi];
                        }
                        if (this.prodiToFaculty[this.selectedProdi]) {
                            this.selectedFaculty = this.prodiToFaculty[this.selectedProdi];
                        }
                    } else {
                        this.userChangedProdi = false;
                    }
                }
             }">
            <div class="px-5 lg:px-6 xl:px-8 py-4 lg:py-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-base lg:text-lg xl:text-xl font-semibold text-gray-900 dark:text-white">
                            Daftar Mahasiswa
                            @php
                                $scopeText = '';
                                if ($level === 'faculty') {
                                    $scopeText = session('operator_faculty_name') ?? session('pimpinan_faculty_name') ?? auth()->user()->faculty;
                                } elseif ($level === 'department') {
                                    $scopeText = session('operator_department_name') ?? session('pimpinan_department_name');
                                } elseif ($level === 'program_study') {
                                    $scopeText = session('operator_program_study_name') ?? session('pimpinan_program_study_name');
                                }
                            @endphp
                            @if($scopeText)
                                <span class="text-gray-400 dark:text-gray-500 font-normal"> - {{ $scopeText }}</span>
                            @endif
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Total: <strong class="text-gray-900 dark:text-white">{{ $students->total() }}</strong> mahasiswa
                        </p>
                    </div>
                </div>

                <!-- Integrated Compact Filter -->
                <form method="GET" action="{{ route($routePrefix . '.students.index') }}" class="space-y-4">
                    <div class="flex flex-wrap xl:flex-nowrap items-center gap-3">
                        <!-- Cascade Filters Group -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 flex-grow w-full xl:w-auto">
                            @if($showFaculty)
                                <select name="faculty" x-model="selectedFaculty" @change="onFacultyChange()"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                    <option value="">Semua Fakultas</option>
                                    @foreach($facultyList as $faculty)
                                        <option value="{{ $faculty }}">{{ $faculty }}</option>
                                    @endforeach
                                </select>
                            @endif

                            @if($showDepartment)
                                <select name="department" x-model="selectedDept" @change="onDeptChange()"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                    <option value="">Semua Jurusan</option>
                                    <template x-for="dept in availableDepts" :key="dept">
                                        <option :value="dept" x-text="dept" :selected="dept == selectedDept"></option>
                                    </template>
                                </select>
                            @endif

                            @if($showProgramStudy)
                                <select name="program_study" x-model="selectedProdi" @change="onProdiChange()"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                    <option value="">Semua Prodi</option>
                                    <template x-for="prodi in availableProdis" :key="prodi">
                                        <option :value="prodi" x-text="prodi" :selected="prodi == selectedProdi"></option>
                                    </template>
                                </select>
                            @endif
                        </div>

                        <!-- Search & Angkatan -->
                        <div class="flex flex-wrap sm:flex-nowrap items-center gap-2 w-full xl:w-auto">
                            <select name="angkatan" onchange="this.form.submit()"
                                class="w-full sm:w-32 px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                <option value="">Angkatan</option>
                                @foreach($angkatanList as $angkatan)
                                    <option value="{{ $angkatan }}" {{ request('angkatan') == $angkatan ? 'selected' : '' }}>{{ $angkatan }}</option>
                                @endforeach
                            </select>

                            <div class="relative flex-grow sm:w-64">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama/NIM..."
                                    class="pl-9 w-full py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                            </div>

                            <div class="flex items-center gap-2">
                                @if(request()->hasAny(['search', 'faculty', 'department', 'program_study', 'angkatan']))
                                    <a href="{{ route($routePrefix . '.students.index') }}" class="p-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-500 dark:text-gray-400 rounded-lg transition-colors border border-gray-200 dark:border-gray-600" title="Reset Filters">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </a>
                                @endif
                                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors font-bold text-xs shadow-sm">
                                    Filter
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Table View -->
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th scope="col" class="px-5 lg:px-6 xl:px-8 py-3 lg:py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Mahasiswa
                            </th>
                            @if($showFaculty)
                                <th scope="col" class="px-5 lg:px-6 xl:px-8 py-3 lg:py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Fakultas
                                </th>
                            @endif
                            @if($showDepartment)
                                <th scope="col" class="px-5 lg:px-6 xl:px-8 py-3 lg:py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Jurusan
                                </th>
                            @endif
                            @if($showProgramStudy)
                                <th scope="col" class="px-5 lg:px-6 xl:px-8 py-3 lg:py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Program Studi
                                </th>
                            @endif
                            <th scope="col" class="px-5 lg:px-6 xl:px-8 py-3 lg:py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Angkatan
                            </th>
                            @if($showGPA)
                                <th scope="col" class="px-5 lg:px-6 xl:px-8 py-3 lg:py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    IPK
                                </th>
                            @endif
                            <th scope="col" class="px-5 lg:px-6 xl:px-8 py-3 lg:py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Prestasi
                            </th>
                            <th scope="col" class="px-5 lg:px-6 xl:px-8 py-3 lg:py-4 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($students as $student)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td class="px-5 lg:px-6 xl:px-8 py-4 lg:py-5 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-xl"
                                                src="https://ui-avatars.com/api/?name={{ urlencode($student->name) }}&background=10b981&color=fff"
                                                alt="">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-bold text-gray-900 dark:text-white">
                                                {{ $student->name }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $student->student_id }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                @if($showFaculty)
                                    <td class="px-5 lg:px-6 xl:px-8 py-4 lg:py-5">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            {{ Str::limit($student->faculty ?? '-', 30) }}</div>
                                    </td>
                                @endif
                                @if($showDepartment)
                                    <td class="px-5 lg:px-6 xl:px-8 py-4 lg:py-5">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            {{ Str::limit($student->department ?? '-', 30) }}</div>
                                    </td>
                                @endif
                                @if($showProgramStudy)
                                    <td class="px-5 lg:px-6 xl:px-8 py-4 lg:py-5">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            {{ Str::limit($student->program_study ?? '-', 30) }}</div>
                                    </td>
                                @endif
                                <td class="px-5 lg:px-6 xl:px-8 py-4 lg:py-5 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-bold rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-800/50">
                                        {{ $student->angkatan }}
                                    </span>
                                </td>
                                @if($showGPA)
                                    <td class="px-5 lg:px-6 xl:px-8 py-4 lg:py-5 whitespace-nowrap text-sm">
                                        @if($student->gpa)
                                            <span class="font-black {{ $student->gpa >= 3.5 ? 'text-green-600 dark:text-green-400' : ($student->gpa >= 3.0 ? 'text-blue-600 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400') }}">
                                                {{ number_format($student->gpa, 2) }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500">-</span>
                                        @endif
                                    </td>
                                @endif
                                <td class="px-5 lg:px-6 xl:px-8 py-4 lg:py-5 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <div class="p-1 px-2 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/50 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                            <span class="text-xs font-black">
                                                {{ $student->achievements_count }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 lg:px-6 xl:px-8 py-4 lg:py-5 whitespace-nowrap text-right">
                                    <a href="{{ route($routePrefix . '.students.show', $student->student_id) }}"
                                        class="inline-flex items-center px-4 py-2 bg-gray-50 dark:bg-gray-700/50 text-emerald-600 dark:text-emerald-400 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-900/30 font-bold text-xs transition-colors border border-gray-100 dark:border-gray-700">
                                        DETAIL
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-5 lg:px-6 xl:px-8 py-12 text-center text-gray-500 dark:text-gray-400">
                                    Tidak ada mahasiswa ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($students->hasPages())
                <div class="px-5 lg:px-6 xl:px-8 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $students->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection