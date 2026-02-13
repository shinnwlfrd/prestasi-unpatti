@extends('layouts.admin')

@section('title', 'Detail Prestasi')

@section('content')
<div class="space-y-6">
    <!-- Back Button -->
    <div>
        <a href="{{ route('admin.student-achievements') }}" class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar
        </a>
    </div>

    <!-- Achievement Header -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $achievement->event_name }}</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">{{ $achievement->organizer }}</p>
                
                <div class="flex items-center gap-4 mt-4 flex-wrap">
                    <span class="px-3 py-1 rounded-full text-sm font-semibold
                        {{ $achievement->level === 'Internasional' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400' : 
                           ($achievement->level === 'Nasional' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : 
                           'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400') }}">
                        {{ $achievement->level }}
                    </span>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                        {{ $achievement->achievement?->category?->name ?? '-' }}
                    </span>
                    @if($achievement->ranking)
                    <span class="px-3 py-1 rounded-full text-sm font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                        Peringkat {{ $achievement->ranking }}
                    </span>
                    @endif
                </div>
            </div>
            
            <div class="text-right">
                @php
                    $statusConfig = [
                        'submitted' => ['label' => 'Diajukan', 'class' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400'],
                        'faculty_review' => ['label' => 'Review Fakultas', 'class' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400'],
                        'faculty_approved' => ['label' => 'Disetujui Fakultas', 'class' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400'],
                        'faculty_rejected' => ['label' => 'Ditolak Fakultas', 'class' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'],
                        'faculty_revision' => ['label' => 'Revisi Fakultas', 'class' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400'],
                        'university_review' => ['label' => 'Review Universitas', 'class' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400'],
                        'university_approved' => ['label' => 'Disetujui Universitas', 'class' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'],
                        'university_rejected' => ['label' => 'Ditolak Universitas', 'class' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'],
                        'Disetujui' => ['label' => 'Disetujui', 'class' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'],
                        'Menunggu' => ['label' => 'Menunggu', 'class' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400'],
                        'Ditolak' => ['label' => 'Ditolak', 'class' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'],
                        'Revisi' => ['label' => 'Revisi', 'class' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400'],
                    ];
                    $status = $statusConfig[$achievement->validation_status] ?? ['label' => $achievement->validation_status, 'class' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'];
                @endphp
                <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $status['class'] }}">
                    {{ $status['label'] }}
                </span>
                @if($achievement->submitted_at)
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    {{ $achievement->submitted_at->format('d M Y H:i') }}
                </p>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Student Information -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informasi Mahasiswa</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nama</p>
                        <p class="text-base font-medium text-gray-900 dark:text-white mt-1">{{ $achievement->student?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">NIM</p>
                        <p class="text-base font-medium text-gray-900 dark:text-white mt-1">{{ $achievement->student_id }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Program Studi</p>
                        <p class="text-base font-medium text-gray-900 dark:text-white mt-1">{{ $achievement->student?->program_study_name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Fakultas</p>
                        <p class="text-base font-medium text-gray-900 dark:text-white mt-1">{{ $achievement->student?->faculty_name ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Achievement Details -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Detail Prestasi</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nama Event</p>
                        <p class="text-base font-medium text-gray-900 dark:text-white mt-1">{{ $achievement->event_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Penyelenggara</p>
                        <p class="text-base font-medium text-gray-900 dark:text-white mt-1">{{ $achievement->organizer }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal Event</p>
                            <p class="text-base font-medium text-gray-900 dark:text-white mt-1">{{ $achievement->event_date?->format('d M Y') ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Peringkat</p>
                            <p class="text-base font-medium text-gray-900 dark:text-white mt-1">{{ $achievement->ranking ?? '-' }}</p>
                        </div>
                    </div>
                    @if($achievement->description)
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Deskripsi</p>
                        <p class="text-base text-gray-900 dark:text-white mt-1">{{ $achievement->description }}</p>
                    </div>
                    @endif
                    @if($achievement->publication_link)
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Link Publikasi</p>
                        <a href="{{ $achievement->publication_link }}" target="_blank" class="text-base text-purple-600 dark:text-purple-400 hover:underline mt-1 inline-block">
                            {{ $achievement->publication_link }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Documents -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Dokumen Pendukung</h2>
                    <a href="{{ route('achievements.documents.index', $achievement) }}" 
                       class="text-sm text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 font-medium">
                        Kelola Dokumen →
                    </a>
                </div>
                <div class="space-y-3">
                    @forelse($achievement->documents as $document)
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $document->type_name ?? $document->document_type }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $document->file_name ?? basename($document->file_path ?? '') }}</p>
                            </div>
                        </div>
                        @if($document->file_path)
                        <a href="{{ route('achievements.documents.preview', $document) }}" target="_blank" 
                           class="px-3 py-1.5 text-sm font-medium text-purple-600 dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded-lg transition-colors">
                            Lihat
                        </a>
                        @endif
                    </div>
                    @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Belum ada dokumen</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Column: Validation Info & Actions -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Aksi Cepat</h2>
                <div class="space-y-3">
                    @if(in_array($achievement->validation_status, ['faculty_approved', 'university_review']))
                        <a href="{{ route('admin.university.show', $achievement) }}" 
                           class="w-full px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Validasi Universitas
                        </a>
                    @endif
                    
                    @if($achievement->is_resubmission && $achievement->resubmission_count > 0)
                        <div class="w-full px-4 py-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <div class="flex items-center gap-2 text-blue-700 dark:text-blue-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                <span class="font-semibold">Review Ulang ke-{{ $achievement->resubmission_count }}</span>
                            </div>
                            @if($achievement->resubmission_reason)
                                <p class="text-sm text-blue-600 dark:text-blue-300 mt-2">{{ $achievement->resubmission_reason }}</p>
                            @endif
                            @if($achievement->last_resubmitted_at)
                                <p class="text-xs text-blue-500 dark:text-blue-400 mt-1">{{ $achievement->last_resubmitted_at->format('d M Y H:i') }}</p>
                            @endif
                        </div>
                    @endif
                    
                    <a href="{{ route('achievements.documents.index', $achievement) }}" 
                       class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Kelola Dokumen
                    </a>
                    
                    <a href="{{ route('admin.validation-logs', ['sa_id' => $achievement->sa_id]) }}" 
                       class="w-full px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Riwayat Validasi
                    </a>
                </div>
            </div>

            <!-- Validation Timeline -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Timeline Validasi</h2>
                <div class="space-y-4">
                    <!-- Submitted -->
                    @if($achievement->submitted_at)
                    <div class="flex gap-3">
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            @if($achievement->faculty_validated_at || $achievement->university_validated_at)
                            <div class="w-0.5 h-full bg-gray-200 dark:bg-gray-700 mt-1"></div>
                            @endif
                        </div>
                        <div class="flex-1 pb-4">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Diajukan</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $achievement->submitted_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Faculty Validation -->
                    @if($achievement->faculty_validated_at)
                    <div class="flex gap-3">
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 {{ in_array($achievement->validation_status, ['faculty_approved', 'university_review', 'university_approved']) ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30' }} rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 {{ in_array($achievement->validation_status, ['faculty_approved', 'university_review', 'university_approved']) ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}" fill="currentColor" viewBox="0 0 20 20">
                                    @if(in_array($achievement->validation_status, ['faculty_approved', 'university_review', 'university_approved']))
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    @else
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    @endif
                                </svg>
                            </div>
                            @if($achievement->university_validated_at)
                            <div class="w-0.5 h-full bg-gray-200 dark:bg-gray-700 mt-1"></div>
                            @endif
                        </div>
                        <div class="flex-1 pb-4">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ in_array($achievement->validation_status, ['faculty_approved', 'university_review', 'university_approved']) ? 'Disetujui' : 'Ditolak' }} Fakultas
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $achievement->faculty_validated_at->format('d M Y H:i') }}</p>
                            @if($achievement->facultyValidator)
                            <p class="text-xs text-gray-500 dark:text-gray-400">oleh {{ $achievement->facultyValidator->name }}</p>
                            @endif
                            @if($achievement->faculty_notes)
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 italic">"{{ $achievement->faculty_notes }}"</p>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- University Validation -->
                    @if($achievement->university_validated_at)
                    <div class="flex gap-3">
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 {{ $achievement->validation_status == 'university_approved' ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30' }} rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 {{ $achievement->validation_status == 'university_approved' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}" fill="currentColor" viewBox="0 0 20 20">
                                    @if($achievement->validation_status == 'university_approved')
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    @else
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    @endif
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $achievement->validation_status == 'university_approved' ? 'Disetujui' : 'Ditolak' }} Universitas
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $achievement->university_validated_at->format('d M Y H:i') }}</p>
                            @if($achievement->universityValidator)
                            <p class="text-xs text-gray-500 dark:text-gray-400">oleh {{ $achievement->universityValidator->name }}</p>
                            @endif
                            @if($achievement->university_notes)
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 italic">"{{ $achievement->university_notes }}"</p>
                            @endif
                        </div>
                    </div>
                    @elseif(in_array($achievement->validation_status, ['faculty_approved', 'university_review']))
                    <div class="flex gap-3">
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Menunggu Validasi Universitas</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Sedang dalam proses review</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Info -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-sm text-blue-800 dark:text-blue-300">
                        <p class="font-medium mb-1">Informasi:</p>
                        <ul class="list-disc list-inside space-y-1 text-xs">
                            <li>Gunakan tombol aksi di atas untuk mengelola prestasi</li>
                            <li>Dokumen dapat dikelola melalui halaman "Kelola Dokumen"</li>
                            <li>Timeline menampilkan riwayat validasi lengkap</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
