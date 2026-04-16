@extends('layouts.admin')

@section('title', 'Verifikasi Universitas - Detail')

@section('content')
    <div class="space-y-6">
        <!-- Back Button -->
        <a onclick="window.history.back()"
            class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 cursor-pointer transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali
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
            <div class="mt-4 md:mt-0">
                <span
                    class="px-3 py-1.5 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 inline-flex items-center">
                    Disetujui Fakultas
                </span>
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

                <!-- Faculty Validation -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                    <h3 class="text-lg font-semibold mb-4 border-b border-gray-200 dark:border-gray-700 pb-2 text-gray-900 dark:text-white">
                        Verifikasi Fakultas</h3>
                    <div
                        class="bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-900/50 rounded-lg p-4 flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="font-medium text-green-800 dark:text-green-400 text-sm">Disetujui oleh Fakultas
                            </p>
                            <p class="text-xs text-green-700 dark:text-green-500 mt-1">
                                Verifikator: {{ $achievement->facultyValidator?->name ?? '-' }}
                            </p>
                            @if($achievement->faculty_validated_at)
                                <p class="text-xs text-green-700 dark:text-green-500 mt-0.5">
                                    {{ $achievement->faculty_validated_at->format('d M Y H:i') }}
                                </p>
                            @endif
                            @if($achievement->faculty_notes)
                                <div
                                    class="mt-2 p-3 bg-white dark:bg-gray-800 rounded border border-green-200 dark:border-green-700">
                                    <p class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ $achievement->faculty_notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Documents -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                    <h3 class="text-lg font-semibold mb-4 border-b border-gray-200 dark:border-gray-700 pb-2 text-gray-900 dark:text-white">
                        Sertifikat & Dokumen</h3>
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
                                        title="{{ basename($document->file_path) }}">
                                        {{ basename($document->file_path) }}</p>
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
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                    <h3 class="text-lg font-semibold mb-4 border-b border-gray-200 dark:border-gray-700 pb-2 text-gray-900 dark:text-white">
                        Keputusan Verifikasi</h3>
                    <form action="{{ route('admin.university.validate', $achievement) }}" method="POST"
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
                            </select>
                        </div>

                        <!-- SK Selection (shown when approve) -->
                        <div id="sk-selection" style="display: none;" class="relative mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Pilih SK (Opsional)
                            </label>
                            <div class="relative">
                                <input type="text" id="sk-search"
                                    class="w-full pl-3 pr-10 py-2 text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md focus:ring-primary-500 focus:border-primary-500"
                                    placeholder="Ketik Nomor SK atau Judul lalu tekan Enter" autocomplete="off">
                                <input type="hidden" name="sk_id" id="sk-id">

                                <div id="sk-loading" class="absolute right-3 top-2.5 hidden">
                                    <svg class="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </div>
                            </div>

                            <!-- Selected SK Display -->
                            <div id="selected-sk" class="mt-2 hidden">
                                <div
                                    class="flex items-center justify-between p-3 bg-primary-50 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-800 rounded-lg">
                                    <div>
                                        <p class="text-sm font-medium text-primary-900 dark:text-primary-300"
                                            id="selected-sk-number"></p>
                                        <p class="text-xs text-primary-700 dark:text-primary-400"
                                            id="selected-sk-title"></p>
                                    </div>
                                    <button type="button" onclick="clearSelectedSK()"
                                        class="text-gray-400 hover:text-red-500">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Search Results -->
                            <div id="sk-results"
                                class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                            </div>
                        </div>

                        <!-- Notes (for approve) -->
                        <div id="notes-field" class="mb-6">
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan</label>
                            <textarea name="notes" id="notes-textarea" rows="4"
                                placeholder="Catatan untuk mahasiswa dan validator fakultas..."
                                class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"></textarea>
                        </div>

                        <!-- Rejection Reason (for reject) -->
                        <div id="rejection-field" style="display: none;" class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Alasan Penolakan <span class="text-red-500">*</span>
                            </label>
                            <textarea name="rejection_reason" id="rejection-textarea" rows="4" required
                                placeholder="Jelaskan alasan penolakan..."
                                class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full text-sm border @error('rejection_reason') border-red-500 @else border-gray-300 @enderror dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500">{{ old('rejection_reason') }}</textarea>
                            @error('rejection_reason')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Alasan penolakan wajib diisi dan akan dilihat oleh mahasiswa
                            </p>
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

                <!-- Validation Timeline -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 lg:p-8 xl:p-10 shadow-sm">
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
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">Diajukan
                                                    Mahasiswa</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                    {{ $achievement->submitted_at?->format('d M Y H:i') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>

                            <!-- Faculty Approved -->
                            <li>
                                <div class="relative pb-8">
                                    <span aria-hidden="true"
                                        class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700"></span>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span
                                                class="h-8 w-8 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                                <svg class="w-4 h-4 text-green-500" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">Disetujui
                                                    Fakultas</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                    oleh {{ $achievement->facultyValidator?->name ?? '-' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>

                            <!-- University Pending -->
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
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">Menunggu
                                                    Verifikasi Universitas</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Sedang dalam
                                                    proses review</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
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
                            <h4 class="text-sm font-medium text-primary-900 dark:text-primary-300 mb-2">Catatan Penting:
                            </h4>
                            <ul class="list-disc pl-4 text-xs text-primary-800 dark:text-primary-400 space-y-1">
                                <li>Prestasi sudah disetujui fakultas</li>
                                <li>SK wajib dipilih saat approval</li>
                                <li>Keputusan ini bersifat final</li>
                                <li>Mahasiswa akan menerima notifikasi</li>
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
            // Show rejection field if there's a validation error
            @if($errors->has('rejection_reason') || old('action') === 'reject')
                document.addEventListener('DOMContentLoaded', function() {
                    const actionSelect = document.getElementById('validation-action');
                    const rejectionField = document.getElementById('rejection-field');
                    const rejectionTextarea = document.getElementById('rejection-textarea');

                    if (actionSelect) {
                        actionSelect.value = 'reject';
                        rejectionField.style.display = 'block';
                        rejectionTextarea.required = true;
                    }
                });
            @endif

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

            document.addEventListener('DOMContentLoaded', function() {
                const skSearch = document.getElementById('sk-search');
                const skResults = document.getElementById('sk-results');
                const skIdInput = document.getElementById('sk-id');
                const selectedSkDiv = document.getElementById('selected-sk');
                const skLoading = document.getElementById('sk-loading');
                let searchTimeout;

                // Handle Enter key only
                skSearch.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault(); // Prevent form submission
                        const query = this.value;
                        if (query.length >= 2) {
                            performSearch(query);
                        }
                    }
                });

                function performSearch(query) {
                    skLoading.classList.remove('hidden');
                    skResults.classList.add('hidden');

                    fetch(`/api/sk-documents/search?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            skResults.innerHTML = '';
                            if (data.length > 0) {
                                data.forEach(sk => {
                                    const div = document.createElement('div');
                                    div.className =
                                        'px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm dark:text-gray-300';
                                    div.innerHTML = `
                                        <div class="font-medium">${sk.sk_number}</div>
                                        <div class="text-xs text-gray-500">${sk.title}</div>
                                    `;
                                    div.onclick = () => selectSK(sk);
                                    skResults.appendChild(div);
                                });
                                skResults.classList.remove('hidden');
                            } else {
                                skResults.innerHTML =
                                    '<div class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada SK ditemukan</div>';
                                skResults.classList.remove('hidden');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        })
                        .finally(() => {
                            skLoading.classList.add('hidden');
                        });
                }

                // Hide results when clicking outside
                document.addEventListener('click', function(e) {
                    if (!skSearch.contains(e.target) && !skResults.contains(e.target)) {
                        skResults.classList.add('hidden');
                    }
                });
            });

            function selectSK(sk) {
                document.getElementById('sk-id').value = sk.id;
                document.getElementById('selected-sk-number').textContent = sk.sk_number;
                document.getElementById('selected-sk-title').textContent = sk.title;

                document.getElementById('sk-search').classList.add('hidden');
                document.getElementById('selected-sk').classList.remove('hidden');
                document.getElementById('sk-results').classList.add('hidden');
            }

            function clearSelectedSK() {
                document.getElementById('sk-id').value = '';
                document.getElementById('sk-search').value = '';

                document.getElementById('sk-search').classList.remove('hidden');
                document.getElementById('selected-sk').classList.add('hidden');
                document.getElementById('sk-search').focus();
            }

            document.getElementById('validation-action')?.addEventListener('change', function() {
                const action = this.value;
                const skSelection = document.getElementById('sk-selection');
                const notesField = document.getElementById('notes-field');
                const rejectionField = document.getElementById('rejection-field');

                const notesTextarea = document.getElementById('notes-textarea');
                const rejectionTextarea = document.getElementById('rejection-textarea');
                const skIdInput = document.getElementById('sk-id');

                // Hide all fields first
                skSelection.style.display = 'none';
                notesField.style.display = 'none';
                rejectionField.style.display = 'none';

                // Remove required from all
                notesTextarea.required = false;
                rejectionTextarea.required = false;
                skIdInput.required = false; // SK is optional

                // Clear values when hiding to prevent submission
                if (action !== 'approve') {
                    notesTextarea.value = '';
                    skIdInput.value = '';
                }
                if (action !== 'reject') {
                    rejectionTextarea.value = '';
                }

                if (action === 'approve') {
                    skSelection.style.display = 'block';
                    notesField.style.display = 'block';
                    // SK is optional, no required attribute
                } else if (action === 'reject') {
                    rejectionField.style.display = 'block';
                    rejectionTextarea.required = true;
                }
            });

            // Form validation before submit
            document.getElementById('validation-form')?.addEventListener('submit', function(e) {
                const action = document.getElementById('validation-action').value;
                const rejectionTextarea = document.getElementById('rejection-textarea');

                if (action === 'reject') {
                    const rejectionReason = rejectionTextarea.value.trim();
                    if (!rejectionReason) {
                        e.preventDefault();
                        showToast('warning', 'Alasan penolakan wajib diisi!');
                        rejectionTextarea.focus();
                        return false;
                    }
                }
            });
        </script>
    @endpush
@endsection