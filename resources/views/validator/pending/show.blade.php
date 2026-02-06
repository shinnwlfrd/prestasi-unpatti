@extends('layouts.validator')

@section('title', 'Validasi Prestasi')

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
        <a href="{{ route($routePrefix . '.pending.index') }}" class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
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
                
                <div class="flex items-center gap-4 mt-4">
                    <span class="px-3 py-1 rounded-full text-sm font-semibold
                        {{ $achievement->level === 'Internasional' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400' : 
                           ($achievement->level === 'Nasional' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : 
                           'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400') }}">
                        {{ $achievement->level }}
                    </span>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                        {{ $achievement->achievement?->category?->name }}
                    </span>
                    @if($achievement->ranking)
                    <span class="px-3 py-1 rounded-full text-sm font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                        Peringkat {{ $achievement->ranking }}
                    </span>
                    @endif
                </div>
            </div>
            
            <div class="text-right">
                <span class="px-3 py-1 rounded-full text-sm font-semibold
                    {{ $achievement->validation_status === 'submitted' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : 
                       'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400' }}">
                    {{ $achievement->status_label }}
                </span>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    Diajukan: {{ $achievement->submitted_at?->format('d M Y H:i') }}
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Achievement Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Student Information -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informasi Mahasiswa</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nama</p>
                        <p class="text-base font-medium text-gray-900 dark:text-white mt-1">{{ $achievement->student?->name }}</p>
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
                            <p class="text-base font-medium text-gray-900 dark:text-white mt-1">{{ $achievement->event_date?->format('d M Y') }}</p>
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
                        <a href="{{ $achievement->publication_link }}" target="_blank" class="text-base text-emerald-600 dark:text-emerald-400 hover:underline mt-1 inline-block">
                            {{ $achievement->publication_link }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Documents -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Dokumen Pendukung</h2>
                <div class="space-y-3">
                    @forelse($achievement->documents as $document)
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $document->document_type }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ basename($document->file_path) }}</p>
                            </div>
                        </div>
                        <a href="{{ Storage::url($document->file_path) }}" target="_blank" 
                           class="px-3 py-1.5 text-sm font-medium text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-lg transition-colors">
                            Lihat
                        </a>
                    </div>
                    @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Tidak ada dokumen</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Column: Validation Form -->
        <div class="space-y-6">
            @unless($isPimpinan)
            <!-- Validation Form -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Validasi Fakultas</h2>
                
                <form action="{{ route('validator.pending.validate', $achievement) }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <!-- Action Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Keputusan</label>
                        <select name="action" id="validation-action" required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                            <option value="">Pilih Keputusan</option>
                            <option value="approve">✓ Setujui</option>
                            <option value="reject">✗ Tolak</option>
                            <option value="revision">↻ Minta Revisi</option>
                        </select>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Catatan <span class="text-red-500">*</span>
                        </label>
                        <textarea name="notes" rows="4" required
                                  placeholder="Berikan catatan untuk mahasiswa..."
                                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"></textarea>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Catatan akan dilihat oleh mahasiswa dan admin universitas
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Kirim Validasi
                    </button>
                </form>
            </div>
            @endunless

            <!-- Validation History -->
            @if($achievement->validationLogs->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Riwayat Validasi</h2>
                <div class="space-y-3">
                    @foreach($achievement->validationLogs->sortByDesc('validated_at') as $log)
                    <div class="border-l-4 border-gray-300 dark:border-gray-600 pl-4 py-2">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $log->validator?->name ?? 'System' }}
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $log->validated_at?->format('d M Y H:i') }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $log->old_status }} → {{ $log->new_status }}
                        </p>
                        @if($log->notes)
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            {{ $log->notes }}
                        </p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Quick Info -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-sm text-blue-800 dark:text-blue-300">
                        <p class="font-medium mb-1">Panduan Validasi:</p>
                        <ul class="list-disc list-inside space-y-1 text-xs">
                            <li>Periksa kelengkapan dokumen</li>
                            <li>Verifikasi keaslian sertifikat</li>
                            <li>Pastikan tingkat sesuai</li>
                            <li>Berikan catatan yang jelas</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('validation-action')?.addEventListener('change', function() {
    const action = this.value;
    const notesLabel = document.querySelector('label[for="notes"]');
    const notesTextarea = document.querySelector('textarea[name="notes"]');
    
    if (action === 'approve') {
        notesTextarea.placeholder = 'Catatan approval (opsional)...';
        notesTextarea.required = false;
    } else if (action === 'reject') {
        notesTextarea.placeholder = 'Jelaskan alasan penolakan...';
        notesTextarea.required = true;
    } else if (action === 'revision') {
        notesTextarea.placeholder = 'Jelaskan apa yang perlu diperbaiki...';
        notesTextarea.required = true;
    }
});
</script>
@endpush
@endsection
