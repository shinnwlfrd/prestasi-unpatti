@extends('layouts.validator')

@section('title', 'Dokumen Prestasi')
@section('subtitle', 'Verifikasi Dokumen')

@section('content')
<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('validator.pending.index') }}" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Dokumen: {{ $achievement->event_name }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $achievement->student?->name }} - {{ $achievement->student_id }}</p>
            </div>
        </div>
    </div>

    <!-- Document Stats -->
    @php
        $docStats = [
            'total' => $achievement->documents->count(),
            'draft' => $achievement->documents->where('status', 'draft')->count(),
            'pending' => $achievement->documents->where('status', 'pending')->count(),
            'approved' => $achievement->documents->where('status', 'approved')->count(),
            'rejected' => $achievement->documents->where('status', 'rejected')->count(),
            'revision' => $achievement->documents->where('status', 'revision')->count(),
        ];
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $docStats['total'] }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Total</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
            <p class="text-2xl font-bold text-gray-500">{{ $docStats['draft'] }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Draft</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
            <p class="text-2xl font-bold text-amber-600">{{ $docStats['pending'] }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Pending</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
            <p class="text-2xl font-bold text-emerald-600">{{ $docStats['approved'] }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Approved</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
            <p class="text-2xl font-bold text-red-600">{{ $docStats['rejected'] }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Rejected</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $docStats['revision'] }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Revisi</p>
        </div>
    </div>

    <!-- Documents List -->
    <x-card title="Daftar Dokumen" :padding="false">
        @if($achievement->documents->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="text-gray-500 dark:text-gray-400">Belum ada dokumen yang diunggah</p>
            </div>
        @else
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($achievement->documents as $document)
                    <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <div class="flex items-start gap-4">
                            <!-- Icon -->
                            <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                @if($document->isPdf())
                                    <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                    </svg>
                                @elseif($document->isImage())
                                    <svg class="w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                                    </svg>
                                @else
                                    <svg class="w-6 h-6 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </div>

                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-3 mb-1">
                                    <h4 class="font-semibold text-gray-900 dark:text-white">{{ $document->type_name }}</h4>
                                    <span class="px-2 py-0.5 rounded text-xs font-medium bg-{{ $document->status_badge }}-100 text-{{ $document->status_badge }}-700 dark:bg-{{ $document->status_badge }}-900/30 dark:text-{{ $document->status_badge }}-400">
                                        {{ $document->status_label }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $document->file_name }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                    {{ $document->file_size_formatted }} • Diupload {{ $document->created_at->diffForHumans() }}
                                </p>
                                
                                @if($document->revision_notes)
                                    <div class="mt-2 p-2 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
                                        <p class="text-xs text-amber-700 dark:text-amber-400">
                                            <span class="font-medium">Catatan:</span> {{ $document->revision_notes }}
                                        </p>
                                    </div>
                                @endif

                                @if($document->verifier)
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                                        Diverifikasi oleh {{ $document->verifier->name }} pada {{ $document->verified_at?->format('d M Y H:i') }}
                                    </p>
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2">
                                <a href="{{ route('achievements.documents.preview', $document) }}" target="_blank"
                                    class="p-2 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors"
                                    title="Lihat Dokumen">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('achievements.documents.history', $document) }}"
                                    class="p-2 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                                    title="Riwayat">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </a>
                                
                                @if($document->status === 'pending')
                                    <button onclick="verifyDocument({{ $document->id }}, 'approve')"
                                        class="p-2 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-colors"
                                        title="Setujui">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                    <button onclick="verifyDocument({{ $document->id }}, 'reject')"
                                        class="p-2 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors"
                                        title="Tolak">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                    <button onclick="verifyDocument({{ $document->id }}, 'revision')"
                                        class="p-2 rounded-lg bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-colors"
                                        title="Minta Revisi">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-card>
</div>

<script>
function verifyDocument(documentId, action) {
    let notes = '';
    
    if (action === 'reject' || action === 'revision') {
        notes = prompt(action === 'reject' ? 'Alasan penolakan:' : 'Catatan revisi yang diperlukan:');
        if (!notes) {
            showToast('warning', 'Catatan wajib diisi untuk ' + (action === 'reject' ? 'penolakan' : 'permintaan revisi'));
            return;
        }
    }
    
    fetch(`/validator/documents/${documentId}/verify`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ action: action, notes: notes })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showToast('error', data.error || 'Gagal memproses verifikasi');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'Terjadi kesalahan');
    });
}
</script>
@endsection
