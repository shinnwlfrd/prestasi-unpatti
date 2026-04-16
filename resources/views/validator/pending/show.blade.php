@extends('layouts.validator')

@section('title', 'Verifikasi Fakultas - Detail')

@section('content')
    @php
        $user = auth()->user();
        $currentRole = $user->getCurrentRole();
        $isPimpinan = $currentRole && $currentRole->role === 'pimpinan';
        $routePrefix = $isPimpinan ? 'pimpinan' : 'validator';
    @endphp

    <div class="space-y-6">
        <!-- Back Button -->
        <a href="{{ route($routePrefix . '.pending.index') }}"
            class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 cursor-pointer transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali ke Daftar
        </a>

        <!-- Achievement Header -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">{{ $achievement->event_name }}</h2>
                <p class="text-gray-500 dark:text-gray-400 mb-4">{{ $achievement->organizer }}</p>
                <div class="flex flex-wrap gap-2">
                    <span
                        class="px-2.5 py-1 rounded-full text-xs font-medium
                                    {{ $achievement->level === 'Internasional' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300' :
                                        ($achievement->level === 'Nasional' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' :
                                            'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300') }}">
                        {{ $achievement->level }}
                    </span>
                    <span
                        class="px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                        {{ $achievement->achievement?->category?->name }}
                    </span>
                    @if($achievement->ranking)
                        <span
                            class="px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                            Peringkat {{ $achievement->ranking }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="mt-4 md:mt-0 text-right">
                <span class="px-3 py-1.5 rounded-full text-sm font-medium 
                    {{ $achievement->validation_status === 'submitted' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' :
                        'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400' }} inline-flex items-center">
                    {{ $achievement->status_label }}
                </span>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    Diajukan: {{ $achievement->submitted_at?->format('d M Y H:i') }}
                </p>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Left Column: Details -->
            <div class="flex-1 space-y-6">
                <!-- Student Information -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                    <h3 class="text-lg font-semibold mb-4 border-b border-gray-200 dark:border-gray-700 pb-2 text-gray-900 dark:text-white">
                        Informasi Mahasiswa</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Nama</p>
                            <p class="font-medium text-sm text-gray-900 dark:text-white">
                                {{ $achievement->student?->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">NIM</p>
                            <p class="font-medium text-sm text-gray-900 dark:text-white">
                                {{ $achievement->student_id }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Program Studi</p>
                            <p class="font-medium text-sm text-gray-900 dark:text-white">
                                {{ $achievement->student?->program_study_name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Fakultas</p>
                            <p class="font-medium text-sm text-gray-900 dark:text-white">
                                {{ $achievement->student?->faculty_name ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Achievement Details -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                    <h3 class="text-lg font-semibold mb-4 border-b border-gray-200 dark:border-gray-700 pb-2 text-gray-900 dark:text-white">
                        Detail Prestasi</h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Nama Event</p>
                            <p class="font-medium text-sm text-gray-900 dark:text-white">
                                {{ $achievement->event_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Penyelenggara</p>
                            <p class="font-medium text-sm text-gray-900 dark:text-white">
                                {{ $achievement->organizer }}</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Tanggal Event</p>
                                <p class="font-medium text-sm text-gray-900 dark:text-white">
                                    {{ $achievement->event_date?->format('d M Y') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Peringkat</p>
                                <p class="font-medium text-sm text-gray-900 dark:text-white">
                                    {{ $achievement->ranking ?? '-' }}</p>
                            </div>
                        </div>
                        @if($achievement->description)
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Deskripsi</p>
                                <p class="font-medium text-sm text-gray-900 dark:text-white">
                                    {{ $achievement->description }}</p>
                            </div>
                        @endif
                        @if($achievement->publication_link)
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Link Publikasi</p>
                                <a href="{{ $achievement->publication_link }}" target="_blank"
                                    class="text-sm text-primary-600 dark:text-primary-400 hover:underline inline-block">
                                    {{ $achievement->publication_link }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Documents -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                    <h3 class="text-lg font-semibold mb-4 border-b border-gray-200 dark:border-gray-700 pb-2 text-gray-900 dark:text-white">
                        Sertifikat & Dokumen Pendukung</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        {{-- Main Certificate --}}
                        @if($achievement->certificate)
                            <div
                                class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden flex flex-col group cursor-pointer hover:border-primary-500 transition-colors">
                                <div class="bg-gray-100 dark:bg-gray-700 h-32 flex items-center justify-center relative">
                                    @php
                                        $certExt = strtolower(pathinfo($achievement->certificate, PATHINFO_EXTENSION));
                                    @endphp
                                    @if(in_array($certExt, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                        <img src="{{ Storage::url($achievement->certificate) }}"
                                            alt="Sertifikat Prestasi"
                                            class="w-full h-full object-cover">
                                    @else
                                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    @endif
                                    <div
                                        class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </div>
                                </div>
                                <div
                                    class="p-3 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex-1 flex flex-col justify-between">
                                    <p class="text-sm font-medium truncate mb-2 text-gray-900 dark:text-white"
                                        title="Sertifikat Prestasi">Sertifikat Prestasi</p>
                                    <button type="button"
                                        onclick="openPreviewModal('{{ Storage::url($achievement->certificate) }}', 'Sertifikat Prestasi')"
                                        class="w-full py-1.5 px-3 bg-primary-50 text-primary-700 dark:bg-primary-900/20 dark:text-primary-400 hover:bg-primary-100 dark:hover:bg-primary-900/40 rounded text-xs font-medium transition-colors">
                                        Buka Dokumen
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- Supporting Documents --}}
                        @forelse($achievement->documents as $document)
                            <div
                                class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden flex flex-col group cursor-pointer hover:border-primary-500 transition-colors">
                                <div class="bg-gray-100 dark:bg-gray-700 h-32 flex items-center justify-center relative">
                                    @php
                                        $docExt = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
                                    @endphp
                                    @if(in_array($docExt, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                        <img src="{{ Storage::url($document->file_path) }}"
                                            alt="{{ $document->document_type }}"
                                            class="w-full h-full object-cover">
                                    @elseif($docExt === 'pdf')
                                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    @else
                                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    @endif
                                    <div
                                        class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </div>
                                </div>
                                <div
                                    class="p-3 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex-1 flex flex-col justify-between">
                                    <p class="text-sm font-medium truncate mb-2 text-gray-900 dark:text-white"
                                        title="{{ $document->document_type }}">{{ $document->document_type }}</p>
                                    <button type="button"
                                        onclick="openPreviewModal('{{ Storage::url($document->file_path) }}', '{{ $document->document_type }}')"
                                        class="w-full py-1.5 px-3 bg-primary-50 text-primary-700 dark:bg-primary-900/20 dark:text-primary-400 hover:bg-primary-100 dark:hover:bg-primary-900/40 rounded text-xs font-medium transition-colors">
                                        Buka Dokumen
                                    </button>
                                </div>
                            </div>
                        @empty
                            @if(!$achievement->certificate)
                                <div class="col-span-full p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-center">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada dokumen</p>
                                </div>
                            @endif
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Column: Verification Form & Timeline -->
            <div class="w-full lg:w-96 space-y-6">
                <!-- Verification Decision Form -->
                @unless($isPimpinan)
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                    <h3 class="text-lg font-semibold mb-4 border-b border-gray-200 dark:border-gray-700 pb-2 text-gray-900 dark:text-white">
                        Keputusan Verifikasi</h3>
                    <form action="{{ route('validator.pending.validate', $achievement) }}" method="POST"
                        id="validation-form">
                        @csrf

                        <!-- Action Selection -->
                        <div class="mb-4">
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Keputusan</label>
                            <select name="action" id="validation-action" required
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-sm border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-primary-500 focus:border-primary-500 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="">Pilih Keputusan</option>
                                <option value="approve">Setujui</option>
                                <option value="reject">Tolak</option>
                                <option value="request_revision">Minta Revisi</option>
                            </select>
                        </div>

                        <!-- Notes (for approve) -->
                        <div id="notes-field" class="mb-6">
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan</label>
                            <textarea name="notes" id="notes-textarea" rows="4"
                                placeholder="Catatan untuk mahasiswa dan admin universitas..."
                                class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"></textarea>
                        </div>

                        <!-- Rejection Reason (for reject) -->
                        <div id="rejection-field" style="display: none;" class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Alasan Penolakan <span class="text-red-500">*</span>
                            </label>
                            <textarea name="rejection_reason" id="rejection-textarea" rows="4"
                                placeholder="Jelaskan alasan penolakan..."
                                class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full text-sm border @error('rejection_reason') border-red-500 @else border-gray-300 @enderror dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500">{{ old('rejection_reason') }}</textarea>
                            @error('rejection_reason')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Alasan penolakan wajib diisi dan akan dilihat oleh mahasiswa
                            </p>
                        </div>

                        <!-- Revision Reason (for request_revision) -->
                        <div id="revision-field" style="display: none;" class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Alasan Revisi <span class="text-red-500">*</span>
                            </label>
                            <textarea name="revision_reason" id="revision-textarea" rows="4"
                                placeholder="Jelaskan apa yang perlu diperbaiki..."
                                class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full text-sm border @error('revision_reason') border-red-500 @else border-gray-300 @enderror dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500">{{ old('revision_reason') }}</textarea>
                            @error('revision_reason')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <button type="submit"
                            class="w-full flex justify-center items-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Kirim Verifikasi
                        </button>
                    </form>
                </div>
                @endunless

                <!-- Validation Timeline -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                    <h3 class="text-lg font-semibold mb-4 border-b border-gray-200 dark:border-gray-700 pb-2 text-gray-900 dark:text-white">
                        Timeline Verifikasi</h3>
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            <!-- Submitted -->
                            <li>
                                <div class="relative pb-8">
                                    <span aria-hidden="true"
                                        class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700"></span>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span
                                                class="h-8 w-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                                <svg class="w-4 h-4 text-blue-500" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">Diajukan Mahasiswa</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                    {{ $achievement->submitted_at?->format('d M Y H:i') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>

                            @foreach($achievement->validationLogs->sortBy('validated_at') as $log)
                            <li>
                                <div class="relative pb-8">
                                    <span aria-hidden="true"
                                        class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700"></span>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span
                                                class="h-8 w-8 rounded-full 
                                                {{ str_contains($log->new_status, 'Approved') || str_contains($log->new_status, 'Disetujui') ? 'bg-green-100 dark:bg-green-900/30' : 
                                                   (str_contains($log->new_status, 'Reject') || str_contains($log->new_status, 'Tolak') ? 'bg-red-100 dark:bg-red-900/30' : 'bg-yellow-100 dark:bg-yellow-900/30') }} 
                                                flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                                @if(str_contains($log->new_status, 'Approved') || str_contains($log->new_status, 'Disetujui'))
                                                    <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                @elseif(str_contains($log->new_status, 'Reject') || str_contains($log->new_status, 'Tolak'))
                                                    <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                    </svg>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $log->new_status }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                    oleh {{ $log->validator?->name ?? 'System' }}
                                                </p>
                                                @if($log->notes)
                                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-2 italic">"{{ $log->notes }}"</p>
                                                @endif
                                            </div>
                                            <div class="text-right text-xs whitespace-nowrap text-gray-500">
                                                {{ $log->validated_at?->format('d M H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endforeach

                            @if($achievement->validation_status === 'submitted' || $achievement->validation_status === 'Menunggu')
                            <li>
                                <div class="relative">
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span
                                                class="h-8 w-8 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                                <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400"
                                                    fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">Menunggu Verifikasi Fakultas</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Sedang dalam proses review</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- Info Box -->
                <div
                    class="bg-primary-50 dark:bg-primary-900/10 border border-primary-100 dark:border-primary-900/30 rounded-xl p-5">
                    <div class="flex items-start">
                        <svg class="w-4 h-4 text-primary-600 dark:text-primary-400 mr-2 mt-0.5 flex-shrink-0"
                            fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                        <div>
                            <h4 class="text-sm font-medium text-primary-900 dark:text-primary-300 mb-2">Panduan Verifikasi:
                            </h4>
                            <ul class="list-disc pl-4 text-xs text-primary-800 dark:text-primary-400 space-y-1">
                                <li>Periksa kelengkapan dokumen pendukung</li>
                                <li>Verifikasi keaslian sertifikat (jika ada)</li>
                                <li>Pastikan tingkat prestasi sudah sesuai</li>
                                <li>Berikan catatan yang jelas jika minta revisi</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div id="preview-modal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"
                onclick="closePreviewModal()"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal panel -->
            <div
                class="inline-block w-full overflow-hidden text-left align-bottom transition-all transform bg-white shadow-xl dark:bg-gray-800 rounded-lg sm:my-8 sm:align-middle sm:max-w-5xl">
                <div class="px-4 pt-5 pb-4 bg-white dark:bg-gray-800 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white" id="modal-title">
                            Preview Dokumen
                        </h3>
                        <button type="button" onclick="closePreviewModal()"
                            class="text-gray-400 hover:text-gray-500 focus:outline-none">
                            <span class="sr-only">Close</span>
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="mt-2 h-[75vh] bg-gray-100 dark:bg-gray-900 rounded-lg overflow-hidden">
                        <iframe id="preview-frame" src="" class="w-full h-full border-0"></iframe>
                    </div>
                    <div class="mt-4 flex justify-end gap-2">
                        <a id="download-link" href="" download target="_blank"
                            class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition-colors inline-flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download
                        </a>
                        <button type="button" onclick="closePreviewModal()"
                            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-900 dark:text-white rounded-lg font-medium transition-colors">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function openPreviewModal(url, title) {
                const modal = document.getElementById('preview-modal');
                const frame = document.getElementById('preview-frame');
                const modalTitle = document.getElementById('modal-title');
                const downloadLink = document.getElementById('download-link');

                // Set iframe src with #toolbar=0 for PDF to hide toolbar
                const fileUrl = url.toLowerCase().endsWith('.pdf') ? url + '#toolbar=0&navpanes=0' : url;
                frame.src = fileUrl;

                if (title) {
                    modalTitle.textContent = title;
                }

                // Set download link
                downloadLink.href = url;

                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden'; // Prevent scrolling
            }

            function closePreviewModal() {
                const modal = document.getElementById('preview-modal');
                const frame = document.getElementById('preview-frame');

                modal.classList.add('hidden');
                frame.src = '';
                document.body.style.overflow = ''; // Restore scrolling
            }

            document.getElementById('validation-action')?.addEventListener('change', function() {
                const action = this.value;
                const notesField = document.getElementById('notes-field');
                const rejectionField = document.getElementById('rejection-field');
                const revisionField = document.getElementById('revision-field');

                const notesTextarea = document.getElementById('notes-textarea');
                const rejectionTextarea = document.getElementById('rejection-textarea');
                const revisionTextarea = document.getElementById('revision-textarea');

                // Hide all fields first
                notesField.style.display = 'none';
                rejectionField.style.display = 'none';
                revisionField.style.display = 'none';

                // Remove required from all
                notesTextarea.required = false;
                rejectionTextarea.required = false;
                revisionTextarea.required = false;

                // Open correct field
                if (action === 'approve') {
                    notesField.style.display = 'block';
                } else if (action === 'reject') {
                    rejectionField.style.display = 'block';
                    rejectionTextarea.required = true;
                } else if (action === 'request_revision') {
                    revisionField.style.display = 'block';
                    revisionTextarea.required = true;
                }
            });

            // Form validation
            document.getElementById('validation-form')?.addEventListener('submit', function(e) {
                const action = document.getElementById('validation-action').value;
                if (!action) {
                    e.preventDefault();
                    showToast('warning', 'Harap pilih keputusan verifikasi!');
                    return;
                }

                if (action === 'reject') {
                    const reason = document.getElementById('rejection-textarea').value.trim();
                    if (!reason) {
                        e.preventDefault();
                        showToast('warning', 'Alasan penolakan wajib diisi!');
                    }
                } else if (action === 'request_revision') {
                    const reason = document.getElementById('revision-textarea').value.trim();
                    if (!reason) {
                        e.preventDefault();
                        showToast('warning', 'Alasan revisi wajib diisi!');
                    }
                }
            });
        </script>
    @endpush
@endsection