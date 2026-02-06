@extends('layouts.admin')

@section('title', 'Detail Mahasiswa')

@section('content')
<div class="space-y-6">
    <!-- Back Button -->
    <div>
        <a href="{{ route('admin.students') }}" class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar Mahasiswa
        </a>
    </div>

    <!-- Student Profile Header -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl p-6 text-white">
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-4">
                <div class="w-20 h-20 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center border-4 border-white/30">
                    <span class="text-3xl font-bold">{{ substr($student->name, 0, 1) }}</span>
                </div>
                <div>
                    <h1 class="text-2xl font-bold">{{ $student->name }}</h1>
                    <p class="text-purple-100 mt-1">{{ $student->student_id }}</p>
                    <div class="flex items-center gap-4 mt-2">
                        <span class="px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full text-sm font-medium">
                            {{ $student->program_study ?? '-' }}
                        </span>
                        @if($student->angkatan)
                        <span class="px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full text-sm font-medium">
                            Angkatan {{ $student->angkatan }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="text-right">
                <div class="px-4 py-2 bg-white/20 backdrop-blur-sm rounded-lg">
                    <p class="text-sm text-purple-100">IPK</p>
                    <p class="text-3xl font-bold">{{ number_format($student->gpa, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Prestasi</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $achievements->total() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Disetujui</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $achievements->whereIn('validation_status', ['university_approved', 'Disetujui'])->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg">
                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $achievements->whereIn('validation_status', ['submitted', 'faculty_review', 'university_review', 'Menunggu'])->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Skor Kredibilitas</p>
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($student->credibility_score ?? 0, 1) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Student Info -->
        <div class="space-y-6">
            <!-- Personal Information -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informasi Pribadi</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Email</p>
                        <p class="text-base text-gray-900 dark:text-white mt-1">{{ $student->email ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">NIM</p>
                        <p class="text-base text-gray-900 dark:text-white mt-1">{{ $student->student_id }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Angkatan</p>
                        <p class="text-base text-gray-900 dark:text-white mt-1">{{ $student->angkatan ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Academic Information -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informasi Akademik</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Fakultas</p>
                        <p class="text-base text-gray-900 dark:text-white mt-1">{{ $student->faculty ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Jurusan</p>
                        <p class="text-base text-gray-900 dark:text-white mt-1">{{ $student->department ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Program Studi</p>
                        <p class="text-base text-gray-900 dark:text-white mt-1">{{ $student->program_study ?? $student->program ?? '-' }}</p>
                    </div>
                    @if($student->program_study_code)
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Kode Prodi</p>
                        <p class="text-base text-gray-900 dark:text-white mt-1">{{ $student->program_study_code }}</p>
                    </div>
                    @endif
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">IPK</p>
                        <p class="text-base font-semibold text-gray-900 dark:text-white mt-1">{{ number_format($student->gpa, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Aksi Cepat</h2>
                <div class="space-y-2">
                    <a href="{{ route('admin.submit.create', ['student_id' => $student->student_id]) }}" 
                       class="w-full px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Prestasi
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Column: Achievements List -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Daftar Prestasi</h2>
                </div>

                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($achievements as $achievement)
                    <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start gap-3">
                                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold text-gray-900 dark:text-white truncate">
                                            {{ $achievement->event_name }}
                                        </h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 truncate">
                                            {{ $achievement->organizer }}
                                        </p>
                                        
                                        <div class="flex items-center gap-2 mt-3 flex-wrap">
                                            <span class="px-2 py-1 rounded text-xs font-medium
                                                {{ $achievement->level === 'Internasional' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' : 
                                                   ($achievement->level === 'Nasional' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 
                                                   'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400') }}">
                                                {{ $achievement->level }}
                                            </span>
                                            <span class="px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                                {{ $achievement->achievement?->category?->name ?? '-' }}
                                            </span>
                                            @if($achievement->ranking)
                                            <span class="px-2 py-1 rounded text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                                                Peringkat {{ $achievement->ranking }}
                                            </span>
                                            @endif
                                            @php
                                                $statusConfig = [
                                                    'submitted' => ['label' => 'Diajukan', 'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'],
                                                    'faculty_review' => ['label' => 'Review Fakultas', 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'],
                                                    'faculty_approved' => ['label' => 'Disetujui Fakultas', 'class' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400'],
                                                    'faculty_rejected' => ['label' => 'Ditolak Fakultas', 'class' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'],
                                                    'faculty_revision' => ['label' => 'Revisi Fakultas', 'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'],
                                                    'university_review' => ['label' => 'Review Universitas', 'class' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400'],
                                                    'university_approved' => ['label' => 'Disetujui Universitas', 'class' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'],
                                                    'university_rejected' => ['label' => 'Ditolak Universitas', 'class' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'],
                                                    'Disetujui' => ['label' => 'Disetujui', 'class' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'],
                                                    'Menunggu' => ['label' => 'Menunggu', 'class' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400'],
                                                    'Ditolak' => ['label' => 'Ditolak', 'class' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'],
                                                    'Revisi' => ['label' => 'Revisi', 'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'],
                                                ];
                                                $status = $statusConfig[$achievement->validation_status] ?? ['label' => $achievement->validation_status, 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'];
                                            @endphp
                                            <span class="px-2 py-1 rounded text-xs font-medium {{ $status['class'] }}">
                                                {{ $status['label'] }}
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-4 mt-3 text-xs text-gray-500 dark:text-gray-400">
                                            <span class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                {{ \Carbon\Carbon::parse($achievement->event_date)->format('d M Y') }}
                                            </span>
                                            @if($achievement->submitted_at)
                                            <span class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Diajukan {{ $achievement->submitted_at->diffForHumans() }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.student-achievements.show', $achievement->sa_id) }}" 
                                   class="p-2 text-purple-600 hover:bg-purple-50 dark:text-purple-400 dark:hover:bg-purple-900/20 rounded-lg transition-colors" 
                                   title="Lihat Detail">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Belum ada prestasi</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Mahasiswa ini belum memiliki prestasi yang tercatat</p>
                        <div class="mt-6">
                            <a href="{{ route('admin.submit.create', ['student_id' => $student->student_id]) }}" 
                               class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-colors gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Tambah Prestasi
                            </a>
                        </div>
                    </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if($achievements->hasPages())
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $achievements->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
