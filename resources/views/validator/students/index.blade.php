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
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Daftar Mahasiswa</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Kelola dan lihat prestasi mahasiswa
                </p>
            </div>
        </div>

        <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6" 
         x-data="{ 
            selectedDept: '{{ request('department') }}',
            selectedProdi: '{{ request('program_study') }}',
            deptToProdi: {{ $deptToProdi->toJson() }},
            prodiToDept: {{ $prodiToDept->toJson() }},
            allProdis: {{ $allProdis->toJson() }},
            
            get availableProdis() {
                if (this.selectedDept) {
                    return this.deptToProdi[this.selectedDept] || [];
                }
                return this.allProdis;
            },

            updateDept() {
                if (this.selectedProdi && this.prodiToDept[this.selectedProdi]) {
                    this.selectedDept = this.prodiToDept[this.selectedProdi];
                }
            },

            updateProdi() {
                if (this.selectedDept && this.selectedProdi) {
                    const prodisInDept = this.deptToProdi[this.selectedDept] || [];
                    if (!prodisInDept.includes(this.selectedProdi)) {
                        this.selectedProdi = '';
                    }
                }
            }
         }">
        <form method="GET" action="{{ route($routePrefix . '.students.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Cari Mahasiswa
                </label>
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Nama atau NIM..."
                    class="w-full py-3 text-base rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <!-- Angkatan -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Angkatan
                </label>
                <select name="angkatan" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Semua Angkatan</option>
                    @foreach($angkatanList as $angkatan)
                        <option value="{{ $angkatan }}" {{ request('angkatan') == $angkatan ? 'selected' : '' }}>
                            {{ $angkatan }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Jurusan (Department) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Jurusan
                </label>
                <select name="department" 
                        x-model="selectedDept"
                        @change="updateProdi()"
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Semua Jurusan</option>
                    @foreach($departmentList as $dept)
                        <option value="{{ $dept }}">
                            {{ $dept }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Program Study -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Program Studi
                </label>
                <select name="program_study" 
                        x-model="selectedProdi"
                        @change="updateDept()"
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Semua Prodi</option>
                    <template x-for="prodi in availableProdis" :key="prodi">
                        <option :value="prodi" x-text="prodi" :selected="prodi == selectedProdi"></option>
                    </template>
                </select>
            </div>

            <!-- Submit -->
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium">
                    Filter
                </button>
            </div>
        </form>
    </div>

        <!-- Students Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            @php
                // Determine which columns to show based on user level
                $showFaculty = false; // User requested to hide faculty column
                $showDepartment = true;
                $showProgramStudy = true;
                $showGPA = false;

                $level = session('operator_level') ?? session('pimpinan_level');

                if ($level === 'department') {
                    // Ketua Jurusan: Sembunyikan Fakultas dan Jurusan
                    $showFaculty = false;
                    $showDepartment = false;
                } elseif ($level === 'program_study') {
                    // Kaprodi: Sembunyikan Fakultas, Jurusan, Prodi, Tampilkan IPK
                    $showFaculty = false;
                    $showDepartment = false;
                    $showProgramStudy = false;
                    $showGPA = true;
                }
            @endphp
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Mahasiswa
                            </th>
                            @if($showFaculty)
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Fakultas
                                </th>
                            @endif
                            @if($showDepartment)
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Jurusan
                                </th>
                            @endif
                            @if($showProgramStudy)
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Program Studi
                                </th>
                            @endif
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Angkatan
                            </th>
                            @if($showGPA)
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    IPK
                                </th>
                            @endif
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Prestasi
                            </th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($students as $student)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full"
                                                src="https://ui-avatars.com/api/?name={{ urlencode($student->name) }}&background=10b981&color=fff"
                                                alt="">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $student->name }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $student->student_id }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                @if($showFaculty)
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            {{ Str::limit($student->faculty ?? '-', 30) }}</div>
                                    </td>
                                @endif
                                @if($showDepartment)
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            {{ Str::limit($student->department ?? '-', 30) }}</div>
                                    </td>
                                @endif
                                @if($showProgramStudy)
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            {{ Str::limit($student->program_study ?? '-', 30) }}</div>
                                    </td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                        {{ $student->angkatan }}
                                    </span>
                                </td>
                                @if($showGPA)
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($student->gpa)
                                            <div class="flex items-center">
                                                <span
                                                    class="text-sm font-semibold {{ $student->gpa >= 3.5 ? 'text-green-600 dark:text-green-400' : ($student->gpa >= 3.0 ? 'text-blue-600 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400') }}">
                                                    {{ number_format($student->gpa, 2) }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-400 dark:text-gray-500">-</span>
                                        @endif
                                    </td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-emerald-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ $student->achievements_count }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route($routePrefix . '.students.show', $student->student_id) }}"
                                        class="inline-flex items-center px-3 py-1.5 bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300 rounded-lg hover:bg-emerald-200 dark:hover:bg-emerald-800">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 4 + ($showFaculty ? 1 : 0) + ($showDepartment ? 1 : 0) + ($showProgramStudy ? 1 : 0) + ($showGPA ? 1 : 0) }}"
                                    class="px-6 py-12 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada mahasiswa ditemukan</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($students->hasPages())
                <div class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                    {{ $students->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection