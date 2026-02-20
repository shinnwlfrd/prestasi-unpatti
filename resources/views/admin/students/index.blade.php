@extends('layouts.admin')
@section('title', 'Daftar Mahasiswa')
@section('content')
    <div class="space-y-6 lg:space-y-8">
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 sm:gap-6">
            <div>
                <h2 class="text-xl sm:text-2xl lg:text-3xl xl:text-4xl 2xl:text-5xl font-bold text-gray-900 dark:text-white">Daftar Mahasiswa</h2>
                <p class="text-sm sm:text-base lg:text-lg xl:text-xl text-gray-500 dark:text-gray-400 mt-1">Kelola data mahasiswa dan pantau capaian prestasi mereka.</p>
            </div>
            <a href="{{ route('admin.submit.create') }}"
                class="w-full sm:w-auto justify-center px-4 sm:px-5 lg:px-6 xl:px-8 py-2.5 sm:py-3 lg:py-3.5 xl:py-4 
                       bg-purple-600 hover:bg-purple-700 text-white 
                       rounded-lg lg:rounded-xl 
                       text-sm sm:text-base lg:text-lg
                       font-medium flex items-center gap-2 transition-all duration-200 shadow-sm hover:shadow-md">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Prestasi
            </a>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5 lg:gap-6 xl:gap-8">
            <div class="bg-white dark:bg-gray-800 
                        rounded-lg sm:rounded-xl lg:rounded-2xl 
                        border border-gray-200 dark:border-gray-700 
                        p-4 sm:p-5 lg:p-6 xl:p-8 2xl:p-10
                        desktop-card-hover
                        shadow-sm hover:shadow-lg
                        transition-all duration-300">
                <div class="flex items-center gap-3 lg:gap-4 xl:gap-5">
                    <div class="p-2 lg:p-3 xl:p-4 bg-purple-100 dark:bg-purple-900/30 rounded-lg lg:rounded-xl flex-shrink-0">
                        <svg class="w-5 h-5 lg:w-6 lg:h-6 xl:w-7 xl:h-7 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm lg:text-base text-gray-500 dark:text-gray-400">Total Mahasiswa</p>
                        <p class="text-xl sm:text-2xl lg:text-3xl xl:text-4xl font-bold text-gray-900 dark:text-white">{{ $students->total() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 
                        rounded-lg sm:rounded-xl lg:rounded-2xl 
                        border border-gray-200 dark:border-gray-700 
                        p-4 sm:p-5 lg:p-6 xl:p-8 2xl:p-10
                        desktop-card-hover
                        shadow-sm hover:shadow-lg
                        transition-all duration-300">
                <div class="flex items-center gap-3 lg:gap-4 xl:gap-5">
                    <div class="p-2 lg:p-3 xl:p-4 bg-green-100 dark:bg-green-900/30 rounded-lg lg:rounded-xl flex-shrink-0">
                        <svg class="w-5 h-5 lg:w-6 lg:h-6 xl:w-7 xl:h-7 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm lg:text-base text-gray-500 dark:text-gray-400">Mahasiswa Berprestasi</p>
                        <p class="text-xl sm:text-2xl lg:text-3xl xl:text-4xl font-bold text-gray-900 dark:text-white">
                            {{ $students->where('achievements_count', '>', 0)->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 
                        rounded-lg sm:rounded-xl lg:rounded-2xl 
                        border border-gray-200 dark:border-gray-700 
                        p-4 sm:p-5 lg:p-6 xl:p-8 2xl:p-10
                        desktop-card-hover
                        shadow-sm hover:shadow-lg
                        transition-all duration-300">
                <div class="flex items-center gap-3 lg:gap-4 xl:gap-5">
                    <div class="p-2 lg:p-3 xl:p-4 bg-blue-100 dark:bg-blue-900/30 rounded-lg lg:rounded-xl flex-shrink-0">
                        <svg class="w-5 h-5 lg:w-6 lg:h-6 xl:w-7 xl:h-7 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm lg:text-base text-gray-500 dark:text-gray-400">Fakultas</p>
                        <p class="text-xl sm:text-2xl lg:text-3xl xl:text-4xl font-bold text-gray-900 dark:text-white">{{ $facultyCount }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="bg-white dark:bg-gray-800 
                    rounded-lg sm:rounded-xl lg:rounded-2xl 
                    border border-gray-200 dark:border-gray-700 
                    p-4 sm:p-5 lg:p-6 xl:p-8 2xl:p-10
                    shadow-sm">
            <form method="GET" action="{{ url()->current() }}" class="space-y-4 sm:space-y-5 lg:space-y-6">
                <!-- SIGAP Cascade Filter -->
                <x-sigap-filter-simple :faculties="$sigapFaculties" :departments="$sigapDepartments"
                    :studyPrograms="$sigapStudyPrograms" :selectedFaculty="$selectedFaculty"
                    :selectedDepartment="$selectedDepartment" :selectedStudyProgram="$selectedStudyProgram" />

                <!-- Search & Additional Filters -->
                <div class="flex flex-col lg:flex-row gap-3 sm:gap-4 pt-4 sm:pt-6">
                    <!-- Search -->
                    <div class="flex-1">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Cari NIM, nama, atau email mahasiswa..."
                                class="pl-10 w-full 
                                       px-3 sm:px-4 lg:px-5 xl:px-6
                                       py-2.5 sm:py-3 lg:py-3.5 xl:py-4
                                       text-sm sm:text-base lg:text-lg
                                       border border-gray-300 dark:border-gray-600 
                                       dark:bg-gray-700 dark:text-white 
                                       rounded-lg lg:rounded-xl
                                       focus:ring-2 focus:ring-purple-500 focus:border-transparent
                                       transition-all duration-200">
                        </div>
                    </div>

                    <!-- Filter Angkatan -->
                    <select name="angkatan" onchange="this.form.submit()"
                        class="w-full lg:w-auto 
                               px-3 sm:px-4 lg:px-5 xl:px-6
                               py-2.5 sm:py-3 lg:py-3.5 xl:py-4
                               text-sm sm:text-base lg:text-lg
                               border border-gray-300 dark:border-gray-600 
                               dark:bg-gray-700 dark:text-white 
                               rounded-lg lg:rounded-xl
                               focus:ring-2 focus:ring-purple-500 focus:border-transparent
                               transition-all duration-200">
                        <option value="">Semua Angkatan</option>
                        @for($year = 2025; $year >= 2020; $year--)
                            <option value="{{ $year }}" {{ request('angkatan') == $year ? 'selected' : '' }}>Angkatan {{ $year }}
                            </option>
                        @endfor
                    </select>

                    <!-- Buttons -->
                    <div class="flex flex-col sm:flex-row gap-2 w-full lg:w-auto">
                        @if(request()->hasAny(['search', 'faculty_id', 'department_id', 'program_study_id', 'angkatan']))
                            <a href="{{ url()->current() }}"
                                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span class="hidden sm:inline">Reset</span>
                            </a>
                        @endif
                        <button type="submit"
                            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <span class="hidden sm:inline">Cari</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl lg:rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
            <div class="px-5 lg:px-6 xl:px-8 py-4 lg:py-5 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base lg:text-lg xl:text-xl font-semibold text-gray-900 dark:text-white">Daftar Mahasiswa</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Total: <strong class="text-gray-900 dark:text-white">{{ $students->total() }}</strong> mahasiswa
                        </p>
                    </div>
                </div>
            </div>

            <!-- Mobile Card View -->
            <div class="lg:hidden p-4 space-y-4">
                @php
                    $user = auth()->user();
                    $showDepartment = true;
                    $showProgramStudy = true;

                    if ($user->isPimpinan()) {
                        $level = session('pimpinan_level');
                        if ($level === 'department') {
                            $showDepartment = false;
                        } elseif ($level === 'program_study') {
                            $showDepartment = false;
                            $showProgramStudy = false;
                        }
                    } elseif ($user->isOperator()) {
                        $level = session('operator_level');
                        if ($level === 'department') {
                            $showDepartment = false;
                        } elseif ($level === 'program_study') {
                            $showDepartment = false;
                            $showProgramStudy = false;
                        }
                    }
                @endphp

                @forelse($students as $s)
                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                        <!-- Header -->
                        <div class="flex items-start gap-3">
                            <div class="w-11 h-11 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center shrink-0">
                                <span class="text-purple-600 dark:text-purple-400 font-semibold text-sm">
                                    {{ strtoupper(substr($s->name, 0, 1)) }}
                                </span>
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 dark:text-white text-sm truncate">
                                    {{ $s->name }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    NIM: {{ $s->student_id }}
                                </p>
                            </div>

                            <span class="px-3 py-1 text-xs rounded-full font-medium
                                bg-purple-100 text-purple-700
                                dark:bg-purple-900/30 dark:text-purple-400">
                                {{ $s->achievements_count }} Prestasi
                            </span>
                        </div>

                        <!-- Divider -->
                        <div class="my-3 border-t border-gray-200 dark:border-gray-700"></div>

                        <!-- Detail Akademik -->
                        <div class="space-y-2 text-xs text-gray-600 dark:text-gray-300">
                            <div class="flex justify-between">
                                <span class="text-gray-400">Fakultas</span>
                                <span class="text-right font-medium truncate max-w-[60%]">
                                    {{ $s->faculty ?? '-' }}
                                </span>
                            </div>

                            @if($showDepartment)
                            <div class="flex justify-between">
                                <span class="text-gray-400">Jurusan</span>
                                <span class="text-right font-medium truncate max-w-[60%]">
                                    {{ $s->department ?? '-' }}
                                </span>
                            </div>
                            @endif

                            @if($showProgramStudy)
                            <div class="flex justify-between">
                                <span class="text-gray-400">Prodi</span>
                                <span class="text-right font-medium truncate max-w-[60%]">
                                    {{ $s->program_study ?? '-' }}
                                </span>
                            </div>
                            @endif

                            <div class="flex justify-between">
                                <span class="text-gray-400">Angkatan</span>
                                <span class="font-medium">
                                    {{ $s->angkatan ?? '-' }}
                                </span>
                            </div>
                        </div>

                        <!-- Action -->
                        <div class="mt-4">
                            <a href="{{ route('admin.students.show', $s->student_id) }}"
                                class="block w-full text-center text-sm font-medium py-2 rounded-lg
                                bg-purple-600 text-white
                                hover:bg-purple-700 transition">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-sm text-gray-500 dark:text-gray-400 py-6">
                        @if(request()->hasAny(['search', 'faculty_id', 'department_id', 'program_study_id', 'angkatan']))
                            Tidak ada mahasiswa yang sesuai dengan filter.
                        @else
                            Belum ada data mahasiswa.
                        @endif
                    </div>
                @endforelse
            </div>

            <!-- Desktop Table View -->
            <div class="hidden lg:block overflow-x-auto custom-scrollbar">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-5 lg:px-6 xl:px-8 py-3 lg:py-4 text-left text-xs lg:text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">NIM</th>
                            <th class="px-5 lg:px-6 xl:px-8 py-3 lg:py-4 text-left text-xs lg:text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama</th>
                            <th class="px-5 lg:px-6 xl:px-8 py-3 lg:py-4 text-left text-xs lg:text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fakultas</th>
                            @if($showDepartment)
                                <th class="px-5 lg:px-6 xl:px-8 py-3 lg:py-4 text-left text-xs lg:text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jurusan</th>
                            @endif
                            @if($showProgramStudy)
                                <th class="px-5 lg:px-6 xl:px-8 py-3 lg:py-4 text-left text-xs lg:text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Prodi</th>
                            @endif
                            <th class="px-5 lg:px-6 xl:px-8 py-3 lg:py-4 text-left text-xs lg:text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Angkatan</th>
                            <th class="px-5 lg:px-6 xl:px-8 py-3 lg:py-4 text-left text-xs lg:text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Prestasi</th>
                            <th class="px-5 lg:px-6 xl:px-8 py-3 lg:py-4 text-right text-xs lg:text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @php
                            $user = auth()->user();
                            $showDepartment = true;
                            $showProgramStudy = true;

                            if ($user->isPimpinan()) {
                                $level = session('pimpinan_level');
                                if ($level === 'department') {
                                    $showDepartment = false;
                                } elseif ($level === 'program_study') {
                                    $showDepartment = false;
                                    $showProgramStudy = false;
                                }
                            } elseif ($user->isOperator()) {
                                $level = session('operator_level');
                                if ($level === 'department') {
                                    $showDepartment = false;
                                } elseif ($level === 'program_study') {
                                    $showDepartment = false;
                                    $showProgramStudy = false;
                                }
                            }
                        @endphp

                        @forelse($students as $s)
                            <tr class="desktop-table-row transition-colors">
                                <td class="px-5 lg:px-6 xl:px-8 py-4 lg:py-5 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center flex-shrink-0">
                                            <span class="text-purple-600 dark:text-purple-400 font-semibold text-sm">{{ substr($s->name, 0, 1) }}</span>
                                        </div>
                                        <span class="font-medium text-gray-900 dark:text-white text-sm">{{ $s->student_id }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $s->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $s->email }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-700 dark:text-gray-300">{{ Str::limit($s->faculty ?? '-', 25) }}</div>
                                </td>
                                @if($showDepartment)
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-700 dark:text-gray-300">{{ Str::limit($s->department ?? '-', 25) }}</div>
                                    </td>
                                @endif
                                @if($showProgramStudy)
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-700 dark:text-gray-300">{{ Str::limit($s->program_study ?? '-', 25) }}</div>
                                    </td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $s->angkatan ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                                            {{ $s->achievements_count }}
                                        </span>
                                        @if($s->achievements_count >= 5)
                                            <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <a href="{{ route('admin.students.show', $s->student_id) }}"
                                        class="text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300 font-medium text-sm">
                                        Lihat Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 8 + ($showDepartment ? 1 : 0) + ($showProgramStudy ? 1 : 0) }}" class="px-6 py-12 text-center">
                                    @if(request()->hasAny(['search', 'faculty_id', 'department_id', 'program_study_id', 'angkatan']))
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada mahasiswa yang sesuai dengan filter.</p>
                                    @else
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada data mahasiswa.</p>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $students->links() }}
            </div>
        </div>
    </div>
@endsection
