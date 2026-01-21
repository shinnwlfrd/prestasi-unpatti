@props(['document', 'deletable' => false, 'verifiable' => false])

<div class="relative group border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden bg-white dark:bg-gray-800" data-document-id="{{ $document->id }}" data-status="{{ $document->status }}">
    <!-- Status Badge -->
    <div class="absolute top-2 left-2 z-10">
        <span class="px-2 py-1 text-xs font-medium rounded-full
            @if($document->status === 'draft') bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300
            @elseif($document->status === 'pending') bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400
            @elseif($document->status === 'revision') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
            @elseif($document->status === 'approved') bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400
            @elseif($document->status === 'rejected') bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400
            @endif">
            {{ $document->status_label }}
        </span>
    </div>

    <!-- Preview Area -->
    <div class="aspect-[4/3] bg-gray-100 dark:bg-gray-900 flex items-center justify-center">
        @if($document->isImage())
            <img src="{{ $document->file_url }}" alt="{{ $document->file_name }}" class="w-full h-full object-cover">
        @elseif($document->isPdf())
            <div class="text-center p-4">
                <svg class="w-16 h-16 mx-auto text-red-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 2l5 5h-5V4zM8.5 13h1.5v4H8.5v-4zm3 0h1.5v4H11.5v-4zm3 0h1.5v4H14.5v-4z"/>
                </svg>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">PDF</p>
            </div>
        @elseif($document->document_type === 'link_publikasi')
            <div class="text-center p-4">
                <svg class="w-16 h-16 mx-auto text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                </svg>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Link Eksternal</p>
            </div>
        @else
            <div class="text-center p-4">
                <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Dokumen</p>
            </div>
        @endif
        
        <!-- Overlay Actions -->
        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
            <a href="{{ route('achievements.documents.preview', $document) }}" target="_blank" class="p-2 bg-white rounded-full hover:bg-gray-100" title="Lihat">
                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </a>
            @if($document->revisions && $document->revisions->count() > 0)
            <a href="{{ route('achievements.documents.history', $document) }}" class="p-2 bg-blue-500 rounded-full hover:bg-blue-600" title="Riwayat">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </a>
            @endif
            @if($deletable && $document->canBeDeleted())
            <button type="button" onclick="deleteDocument({{ $document->id }})" class="p-2 bg-red-500 rounded-full hover:bg-red-600" title="Hapus">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
            @endif
        </div>
    </div>
    
    <!-- Info -->
    <div class="p-3">
        <p class="text-sm font-medium text-gray-900 dark:text-white truncate" title="{{ $document->file_name }}">
            {{ $document->file_name }}
        </p>
        <div class="flex items-center justify-between mt-1">
            <span class="text-xs px-2 py-0.5 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 rounded-full">
                {{ $document->type_name }}
            </span>
            @if($document->file_size)
            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $document->file_size_formatted }}</span>
            @endif
        </div>
        
        <!-- Revision Notes -->
        @if($document->status === 'revision' && $document->revision_notes)
        <div class="mt-2 p-2 bg-blue-50 dark:bg-blue-900/20 rounded text-xs text-blue-700 dark:text-blue-400">
            <strong>Catatan Revisi:</strong> {{ Str::limit($document->revision_notes, 50) }}
        </div>
        @endif
        
        <!-- Rejection Notes -->
        @if($document->status === 'rejected' && $document->revision_notes)
        <div class="mt-2 p-2 bg-red-50 dark:bg-red-900/20 rounded text-xs text-red-700 dark:text-red-400">
            <strong>Alasan Ditolak:</strong> {{ Str::limit($document->revision_notes, 50) }}
        </div>
        @endif

        <!-- Action Buttons for User -->
        @if($deletable && $document->canBeEdited())
        <div class="mt-2 flex gap-2">
            @if($document->status === 'draft')
            <button type="button" onclick="submitDocument({{ $document->id }})" class="flex-1 text-xs px-2 py-1 bg-yellow-500 hover:bg-yellow-600 text-white rounded">
                Submit
            </button>
            @endif
            <label class="flex-1 text-xs px-2 py-1 bg-gray-500 hover:bg-gray-600 text-white rounded text-center cursor-pointer">
                Ganti
                <input type="file" class="hidden" onchange="replaceDocument({{ $document->id }}, this)" accept=".pdf,.jpg,.jpeg,.png">
            </label>
        </div>
        @endif
        
        <!-- Upload Replacement for Rejected Documents (Student) -->
        @if($deletable && $document->status === 'rejected' && !auth()->check())
        <div class="mt-2">
            <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Dokumen ditolak. Upload dokumen baru:</p>
            <a href="{{ route('achievements.documents.index', $document->studentAchievement) }}" 
                class="block text-center text-xs px-2 py-1 bg-purple-500 hover:bg-purple-600 text-white rounded">
                Upload Dokumen Baru
            </a>
        </div>
        @endif

        <!-- Verification Buttons for Admin -->
        @if($verifiable && $document->status === 'pending')
        <div class="mt-2 flex gap-1">
            <button type="button" onclick="verifyDocument({{ $document->id }}, 'approve')" class="flex-1 text-xs px-2 py-1 bg-green-500 hover:bg-green-600 text-white rounded">
                Setuju
            </button>
            <button type="button" onclick="verifyDocument({{ $document->id }}, 'revision')" class="flex-1 text-xs px-2 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded">
                Revisi
            </button>
            <button type="button" onclick="verifyDocument({{ $document->id }}, 'reject')" class="flex-1 text-xs px-2 py-1 bg-red-500 hover:bg-red-600 text-white rounded">
                Tolak
            </button>
        </div>
        @endif
        
        <!-- Admin Actions for Approved/Rejected Documents -->
        @if(auth()->check() && auth()->user()->role === 'Admin' && in_array($document->status, ['approved', 'rejected']))
        <div class="mt-2 space-y-1">
            <button type="button" onclick="revertDocument({{ $document->id }})" 
                class="w-full text-xs px-2 py-1 bg-orange-500 hover:bg-orange-600 text-white rounded">
                Kembalikan ke Pending
            </button>
            <button type="button" onclick="addNoteToDocument({{ $document->id }})" 
                class="w-full text-xs px-2 py-1 bg-gray-500 hover:bg-gray-600 text-white rounded">
                Tambah Catatan
            </button>
        </div>
        @endif
    </div>
</div>
