@extends('layouts.validator')

@section('title', 'Detail Mahasiswa')

@section('content')
    @php
        $user = auth()->user();
        $currentRole = $user->getCurrentRole();
        $isPimpinan = $currentRole && $currentRole->role === 'pimpinan';
        $routePrefix = $isPimpinan ? 'pimpinan' : 'validator';
    @endphp

    <div class="space-y-6">
        <!-- Back Button -->
        <div>
            <a href="{{ url()->previous() == url()->current() ? route($routePrefix . '.students.index') : url()->previous() }}"
                class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
        </div>

        <!-- Student Profile Card -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="bg-gradient-to-r from-emerald-500 to-teal-600 h-32"></div>
            <div class="px-6 pb-6">
                <div class="flex items-end -mt-16 mb-4">
                    <img class="h-32 w-32 rounded-full border-4 border-white dark:border-gray-800"
                        src="https://ui-avatars.com/api/?name={{ urlencode($student->name) }}&background=10b981&color=fff&size=128"
                        alt="{{ $student->name }}">
                    <div class="ml-6 mb-2">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $student->name }}</h1>
                        <p class="text-gray-600 dark:text-gray-400">{{ $student->student_id }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Email</p>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $student->email }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Program Studi</p>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $student->program_study }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Fakultas</p>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $student->faculty }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Angkatan</p>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $student->angkatan }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Prestasi</p>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $stats['total'] }} prestasi</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 dark:bg-blue-900 rounded-md p-3">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 dark:bg-green-900 rounded-md p-3">
                        <svg class="h-6 w-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Disetujui</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['approved'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 dark:bg-yellow-900 rounded-md p-3">
                        <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending</p>
                        <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['pending'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-100 dark:bg-red-900 rounded-md p-3">
                        <svg class="h-6 w-6 text-red-600 dark:text-red-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Ditolak</p>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $stats['rejected'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Achievements List -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Daftar Prestasi</h2>
            </div>
            <div class="p-6">
                @forelse($achievements as $achievement)
                    <div
                        class="mb-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ $achievement->event_name }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    {{ $achievement->category_name }} • {{ $achievement->level }}
                                </p>
                                <div class="mt-3 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                    <span>Diajukan: {{ $achievement->submitted_at->format('d M Y') }}</span>
                                    <a href="{{ route('validator.pending.show', $achievement->sa_id) }}"
                                        class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 font-medium">
                                        Lihat Detail →
                                    </a>
                                </div>
                            </div>
                            <div class="ml-4">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                        'submitted' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                        'faculty_review' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                        'faculty_approved' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                        'faculty_rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                        'university_review' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300',
                                        'university_approved' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                        'university_rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                        'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                        'revision_requested' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
                                        'appeal_submitted' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
                                        'appeal_approved' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300',
                                        'appeal_rejected' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-300',
                                    ];
                                    $statusLabels = [
                                        'pending' => 'Pending',
                                        'submitted' => 'Diajukan',
                                        'faculty_review' => 'Review Fakultas',
                                        'faculty_approved' => 'Disetujui Fakultas',
                                        'faculty_rejected' => 'Ditolak Fakultas',
                                        'university_review' => 'Review Universitas',
                                        'university_approved' => 'Disetujui Universitas',
                                        'university_rejected' => 'Ditolak Universitas',
                                        'rejected' => 'Ditolak',
                                        'revision_requested' => 'Perlu Revisi',
                                        'appeal_submitted' => 'Banding Diajukan',
                                        'appeal_approved' => 'Banding Diterima',
                                        'appeal_rejected' => 'Banding Ditolak',
                                    ];
                                @endphp
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full {{ $statusColors[$achievement->validation_status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                    @if($achievement->validation_status === 'university_approved' || $achievement->validation_status === 'appeal_approved')
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    @elseif(in_array($achievement->validation_status, ['rejected', 'faculty_rejected', 'university_rejected', 'appeal_rejected']))
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    @elseif($achievement->validation_status === 'revision_requested')
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                    {{ $statusLabels[$achievement->validation_status] ?? ucfirst(str_replace('_', ' ', $achievement->validation_status)) }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Belum ada prestasi</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection