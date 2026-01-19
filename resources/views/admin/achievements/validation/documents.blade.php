@extends('layouts.admin')

@section('title', 'Dokumen Prestasi')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.achievements.validation.show', $achievement) }}" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Dokumen Prestasi</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $achievement->event_name }} - {{ $achievement->student?->name }}</p>
        </div>
    </div>

    <!-- Document Statistics -->
    <div class="grid grid-cols-5 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $achievement->documents->count() }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
            <p class="text-2xl font-bold text-gray-500">{{ $achievement->documents->where('status', 'draft')->count() }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Draft</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
            <p class="text-2xl font-bold text-yellow-600">{{ $achievement->documents->where('status', 'pending')->count() }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Pending</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $achievement->documents->where('status', 'approved')->count() }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Approved</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
            <p class="text-2xl font-bold text-red-600">{{ $achievement->documents->where('status', 'rejected')->count() }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Rejected</p>
        </div>
    </div>

    <!-- Documents List -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="font-semibold text-gray-900 dark:text-white">Daftar Dokumen</h3>
        </div>
        
        @if($achievement->documents->isEmpty())
            <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                Belum ada dokumen yang diupload
            </div>
        @else
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($achievement->documents as $document)
                <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-900/30">
                    <div class="flex items-start gap-4">
                        <!-- Preview Thumbnail -->
                        <div class="w-24 h-24 bg-gray-100 dark:bg-gray-900 rounded-lg flex items-center justify-center overflow-hidden flex-shrink-0">
                            @if($document->isImage())
                                <img src="{{ $document->file_url }}" alt="{{ $document->file_name }}" class="w-full h-full object-cover">
                            @elseif($document->isPdf())
                                <svg class="w-12 h-12 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 2l5 5h-5V4z"/>
                                </svg>
                            @elseif($document->document_type === 'link_publikasi')
                                <svg class="w-12 h-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                            @else
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            @endif
                        </div>
                        
                        <!-- Document Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-white">{{ $document->file_name }}</h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-xs px-2 py-0.5 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 rounded-full">
                                            {{ $document->type_name }}
                                        </span>
                                        <x-badge :type="$document->status_badge" size="xs">{{ $document->status_label }}</x-badge>
                                        @if($document->file_size)
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $document->file_size_formatted }}</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Actions -->
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('achievements.documents.preview', $document) }}" target="_blank" class="p-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg" title="Lihat">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    @if($document->revisions->count() > 0)
                                    <a href="{{ route('achievements.documents.history', $document) }}" class="p-2 text-blue-600 hover:text-blue-900 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg" title="Riwayat">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </a>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Revision Notes -->
                            @if($document->revision_notes)
                            <div class="mt-2 p-2 bg-gray-50 dark:bg-gray-900/50 rounded text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Catatan:</span>
                                <span class="text-gray-700 dark:text-gray-300">{{ $document->revision_notes }}</span>
                            </div>
                            @endif
                            
                            <!-- Verification Info -->
                            @if($document->verified_at)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                Diverifikasi oleh {{ $document->verifier?->name }} pada {{ $document->verified_at->format('d M Y H:i') }}
                            </p>
                            @endif
                            
                            <!-- Verification Actions -->
                            @if($document->status === 'pending')
                            <div class="mt-3 flex gap-2">
                                <button type="button" onclick="verifyDocument({{ $document->id }}, 'approve')" class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Setujui
                                </button>
                                <button type="button" onclick="verifyDocument({{ $document->id }}, 'revision')" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Minta Revisi
                                </button>
                                <button type="button" onclick="verifyDocument({{ $document->id }}, 'reject')" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm rounded-lg flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Tolak
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- Verification Modal -->
<div id="verifyModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-md mx-4">
        <h3 id="modalTitle" class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Verifikasi Dokumen</h3>
        <form id="verifyForm" onsubmit="submitVerification(event)">
            <input type="hidden" id="verifyDocumentId">
            <input type="hidden" id="verifyAction">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Catatan</label>
                <textarea id="verifyNotes" rows="3" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500" placeholder="Masukkan catatan..."></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Batal</button>
                <button type="submit" id="modalSubmitBtn" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg">Konfirmasi</button>
            </div>
        </form>
    </div>
</div>

<script>
function verifyDocument(documentId, action) {
    const modal = document.getElementById('verifyModal');
    const title = document.getElementById('modalTitle');
    const submitBtn = document.getElementById('modalSubmitBtn');
    const notesField = document.getElementById('verifyNotes');
    
    document.getElementById('verifyDocumentId').value = documentId;
    document.getElementById('verifyAction').value = action;
    
    if (action === 'approve') {
        title.textContent = 'Setujui Dokumen';
        submitBtn.className = 'px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg';
        submitBtn.textContent = 'Setujui';
        notesField.placeholder = 'Catatan (opsional)';
        notesField.required = false;
    } else if (action === 'revision') {
        title.textContent = 'Minta Revisi Dokumen';
        submitBtn.className = 'px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg';
        submitBtn.textContent = 'Kirim Permintaan';
        notesField.placeholder = 'Jelaskan revisi yang diperlukan...';
        notesField.required = true;
    } else {
        title.textContent = 'Tolak Dokumen';
        submitBtn.className = 'px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg';
        submitBtn.textContent = 'Tolak';
        notesField.placeholder = 'Alasan penolakan...';
        notesField.required = true;
    }
    
    notesField.value = '';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModal() {
    const modal = document.getElementById('verifyModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function submitVerification(event) {
    event.preventDefault();
    
    const documentId = document.getElementById('verifyDocumentId').value;
    const action = document.getElementById('verifyAction').value;
    const notes = document.getElementById('verifyNotes').value;
    
    fetch(`/admin/achievements/documents/${documentId}/verify`, {
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
            alert(data.error || 'Gagal memproses verifikasi');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    });
}

// Close modal on outside click
document.getElementById('verifyModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>
@endsection
