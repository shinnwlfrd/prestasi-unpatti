@extends('layouts.admin')

@section('title', 'Verifikasi Universitas - Detail')

@section('content')
    <div class="space-y-6">
        <!-- Back Button -->
        <div>
            <button onclick="window.history.back()"
                class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali
            </button>
        </div>

        <!-- Achievement Header -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $achievement->event_name }}</h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">{{ $achievement->organizer }}</p>

                    <div class="flex items-center gap-4 mt-4">
                        <span class="px-3 py-1 rounded-full text-sm font-semibold
                                        {{ $achievement->level === 'Internasional' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400' :
        ($achievement->level === 'Nasional' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' :
            'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400') }}">
                            {{ $achievement->level }}
                        </span>
                        <span
                            class="px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                            {{ $achievement->achievement?->category?->name }}
                        </span>
                        @if($achievement->ranking)
                            <span
                                class="px-3 py-1 rounded-full text-sm font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                Peringkat {{ $achievement->ranking }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="text-right">
                    <span
                        class="px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                        Disetujui Fakultas
                    </span>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                        {{ $achievement->faculty_validated_at?->format('d M Y H:i') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Student Information -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informasi Mahasiswa</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Nama</p>
                            <p class="text-base font-medium text-gray-900 dark:text-white mt-1">
                                {{ $achievement->student?->name }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">NIM</p>
                            <p class="text-base font-medium text-gray-900 dark:text-white mt-1">
                                {{ $achievement->student_id }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Program Studi</p>
                            <p class="text-base font-medium text-gray-900 dark:text-white mt-1">
                                {{ $achievement->student?->program_study_name ?? '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Fakultas</p>
                            <p class="text-base font-medium text-gray-900 dark:text-white mt-1">
                                {{ $achievement->student?->faculty_name ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Achievement Details -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Detail Prestasi</h2>
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Nama Event</p>
                            <p class="text-base font-medium text-gray-900 dark:text-white mt-1">
                                {{ $achievement->event_name }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Penyelenggara</p>
                            <p class="text-base font-medium text-gray-900 dark:text-white mt-1">
                                {{ $achievement->organizer }}
                            </p>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal Event</p>
                                <p class="text-base font-medium text-gray-900 dark:text-white mt-1">
                                    {{ $achievement->event_date?->format('d M Y') }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Peringkat</p>
                                <p class="text-base font-medium text-gray-900 dark:text-white mt-1">
                                    {{ $achievement->ranking ?? '-' }}
                                </p>
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
                                <a href="{{ $achievement->publication_link }}" target="_blank"
                                    class="text-base text-purple-600 dark:text-purple-400 hover:underline mt-1 inline-block">
                                    {{ $achievement->publication_link }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Faculty Validation History -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Verifikasi Fakultas</h2>
                    <div
                        class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <div
                                class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="font-medium text-green-900 dark:text-green-300">Disetujui oleh Fakultas</p>
                                    <span
                                        class="text-sm text-green-700 dark:text-green-400">{{ $achievement->faculty_validated_at?->format('d M Y H:i') }}</span>
                                </div>
                                <p class="text-sm text-green-800 dark:text-green-300 mb-1">
                                    Validator: <strong>{{ $achievement->facultyValidator?->name }}</strong>
                                </p>
                                @if($achievement->faculty_notes)
                                    <div
                                        class="mt-2 p-3 bg-white dark:bg-gray-800 rounded border border-green-200 dark:border-green-700">
                                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $achievement->faculty_notes }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documents -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Dokumen Bukti</h2>

                    <div class="space-y-6">
                        <!-- Main Document -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Dokumen Utama (Sertifikat)
                            </h3>
                            @if($achievement->certificate)
                                <div
                                    class="flex items-center justify-between p-4 bg-purple-50 dark:bg-purple-900/20 border border-purple-100 dark:border-purple-800 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">Sertifikat Prestasi</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Dokumen Utama</p>
                                        </div>
                                    </div>
                                    <button type="button"
                                        onclick="openPreviewModal('{{ Storage::url($achievement->certificate) }}', 'Sertifikat Prestasi')"
                                        class="px-3 py-1.5 text-sm font-medium text-purple-600 dark:text-purple-400 hover:bg-purple-100 dark:hover:bg-purple-900/40 rounded-lg transition-colors inline-flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Preview
                                    </button>
                                </div>
                            @else
                                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-center">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada dokumen sertifikat</p>
                                </div>
                            @endif
                        </div>

                        <!-- Supporting Documents -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Dokumen Pendukung</h3>
                            <div class="space-y-3">
                                @forelse($achievement->documents as $document)
                                    <div
                                        class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 bg-gray-100 dark:bg-gray-600 rounded-lg flex items-center justify-center">
                                                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $document->document_type }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ basename($document->file_path) }}
                                                </p>
                                            </div>
                                        </div>
                                        <button type="button"
                                            onclick="openPreviewModal('{{ Storage::url($document->file_path) }}', '{{ $document->document_type }}')"
                                            class="px-3 py-1.5 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors inline-flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Preview
                                        </button>
                                    </div>
                                @empty
                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-400 text-center py-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                        Tidak ada dokumen pendukung</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: University Validation Form -->
            <div class="space-y-6">
                <!-- Validation Form -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Keputusan Verifikasi</h2>

                    <form action="{{ route('admin.university.validate', $achievement) }}" method="POST" class="space-y-4" id="validation-form">
                        @csrf

                        <!-- Action Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Keputusan</label>
                            <select name="action" id="validation-action" required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="">Pilih Keputusan</option>
                                <option value="approve">✓ Setujui & Terbitkan SK</option>
                                <option value="reject">✗ Tolak</option>
                            </select>
                        </div>

                        <!-- SK Selection (shown when approve) -->
                        <div id="sk-selection" style="display: none;" class="relative">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Pilih SK (Opsional)
                            </label>

                            <div class="relative">
                                <input type="text" id="sk-search"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
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
                                    class="flex items-center justify-between p-3 bg-purple-50 dark:bg-purple-900/20 border border-purple-100 dark:border-purple-800 rounded-lg">
                                    <div>
                                        <p class="text-sm font-medium text-purple-900 dark:text-purple-300"
                                            id="selected-sk-number"></p>
                                        <p class="text-xs text-purple-700 dark:text-purple-400" id="selected-sk-title"></p>
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
                        <div id="notes-field">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Catatan
                            </label>
                            <textarea name="notes" id="notes-textarea" rows="4"
                                placeholder="Catatan untuk mahasiswa dan validator fakultas..."
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                        </div>

                        <!-- Rejection Reason (for reject) -->
                        <div id="rejection-field" style="display: none;">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Alasan Penolakan <span class="text-red-500">*</span>
                            </label>
                            <textarea name="rejection_reason" id="rejection-textarea" rows="4" required
                                placeholder="Jelaskan alasan penolakan..."
                                class="w-full px-4 py-2 border @error('rejection_reason') border-red-500 @else border-gray-300 @enderror dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">{{ old('rejection_reason') }}</textarea>
                            @error('rejection_reason')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Alasan penolakan wajib diisi dan akan dilihat oleh mahasiswa
                            </p>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit"
                            class="w-full px-4 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Kirim Verifikasi
                        </button>
                    </form>
                </div>

                <!-- Validation Timeline -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Timeline Verifikasi</h2>
                    <div class="space-y-4">
                        <!-- Submitted -->
                        <div class="flex gap-3">
                            <div class="flex flex-col items-center">
                                <div
                                    class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="w-0.5 h-full bg-gray-200 dark:bg-gray-700 mt-1"></div>
                            </div>
                            <div class="flex-1 pb-4">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Diajukan Mahasiswa</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $achievement->submitted_at?->format('d M Y H:i') }}
                                </p>
                            </div>
                        </div>

                        <!-- Faculty Approved -->
                        <div class="flex gap-3">
                            <div class="flex flex-col items-center">
                                <div
                                    class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="w-0.5 h-full bg-gray-200 dark:bg-gray-700 mt-1"></div>
                            </div>
                            <div class="flex-1 pb-4">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Disetujui Fakultas</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $achievement->faculty_validated_at?->format('d M Y H:i') }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">oleh
                                    {{ $achievement->facultyValidator?->name }}
                                </p>
                            </div>
                        </div>

                        <!-- University Pending -->
                        <div class="flex gap-3">
                            <div class="flex flex-col items-center">
                                <div
                                    class="w-8 h-8 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Menunggu Verifikasi Universitas
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Sedang dalam proses review</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info -->
                <div
                    class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-xl p-4">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400 flex-shrink-0 mt-0.5" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-sm text-purple-800 dark:text-purple-300">
                            <p class="font-medium mb-1">Catatan Penting:</p>
                            <ul class="list-disc list-inside space-y-1 text-xs">
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
    <div id="preview-modal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
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
                        <iframe id="preview-frame" src=""
                            class="w-full h-full border-0"></iframe>
                    </div>
                    <div class="mt-4 flex justify-end gap-2">
                        <a id="download-link" href="" download target="_blank"
                            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-colors inline-flex items-center gap-2">
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

            document.addEventListener('DOMContentLoaded', function () {
                const skSearch = document.getElementById('sk-search');
                const skResults = document.getElementById('sk-results');
                const skIdInput = document.getElementById('sk-id');
                const selectedSkDiv = document.getElementById('selected-sk');
                const skLoading = document.getElementById('sk-loading');
                let searchTimeout;

                // Handle Enter key only
                skSearch.addEventListener('keydown', function (e) {
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
                                    div.className = 'px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm dark:text-gray-300';
                                    div.innerHTML = `
                                                        <div class="font-medium">${sk.sk_number}</div>
                                                        <div class="text-xs text-gray-500">${sk.title}</div>
                                                    `;
                                    div.onclick = () => selectSK(sk);
                                    skResults.appendChild(div);
                                });
                                skResults.classList.remove('hidden');
                            } else {
                                skResults.innerHTML = '<div class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada SK ditemukan</div>';
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
                document.addEventListener('click', function (e) {
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

            document.getElementById('validation-action')?.addEventListener('change', function () {
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
                        alert('Alasan penolakan wajib diisi!');
                        rejectionTextarea.focus();
                        return false;
                    }
                }
            });
        </script>
    @endpush
@endsection