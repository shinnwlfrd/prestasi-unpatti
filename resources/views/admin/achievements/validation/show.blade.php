@extends('layouts.admin')

@section('title', 'Validasi Prestasi')

@section('content')
<div x-data="validationForm()" class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.achievements.validation.index') }}" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $achievement->event_name }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $achievement->student?->name }} - {{ $achievement->student_id }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <x-badge :type="$achievement->status_badge" size="md">{{ $achievement->status_label }}</x-badge>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Left: Achievement Details & Documents -->
        <div class="space-y-6">
            <!-- Achievement Info -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informasi Prestasi</h3>
                <dl class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Nama Lomba</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $achievement->event_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Kategori</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $achievement->achievement?->category ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Tingkat</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $achievement->level }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Penyelenggara</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $achievement->organizer }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Tanggal Pelaksanaan</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $achievement->event_date?->format('d F Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Peringkat</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $achievement->ranking ?? '-' }}</dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Deskripsi</dt>
                        <dd class="text-sm text-gray-900 dark:text-white">{{ $achievement->description ?? '-' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Documents -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Dokumen Bukti ({{ $achievement->documents->count() }})</h3>
                    <div class="flex items-center gap-3">
                        @php
                            $docStats = [
                                'pending' => $achievement->documents->where('status', 'pending')->count(),
                                'approved' => $achievement->documents->where('status', 'approved')->count(),
                                'rejected' => $achievement->documents->where('status', 'rejected')->count(),
                            ];
                        @endphp
                        <div class="flex gap-2 text-xs">
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400 rounded">{{ $docStats['pending'] }} Pending</span>
                            <span class="px-2 py-1 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded">{{ $docStats['approved'] }} Approved</span>
                            <span class="px-2 py-1 bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 rounded">{{ $docStats['rejected'] }} Rejected</span>
                        </div>
                        <a href="{{ route('admin.achievements.validation.documents', $achievement) }}" class="text-sm text-purple-600 hover:text-purple-700 dark:text-purple-400">
                            Lihat Detail →
                        </a>
                    </div>
                </div>
                @if($achievement->documents->isEmpty())
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">Belum ada dokumen yang diunggah</p>
                @else
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($achievement->documents as $document)
                            <x-document-card :document="$document" :verifiable="true" />
                        @endforeach
                    </div>
                    
                    <!-- Bulk Actions -->
                    @if($achievement->documents->where('status', 'pending')->count() > 0)
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex gap-2">
                            <button type="button" onclick="bulkVerify('approve')" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg">
                                Setujui Semua Pending
                            </button>
                            <button type="button" onclick="bulkVerify('reject')" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm rounded-lg">
                                Tolak Semua Pending
                            </button>
                        </div>
                    </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Right: Validation Form -->
        <div class="space-y-6">
            <!-- Progress Bar -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Progress Validasi</h3>
                <x-progress-bar :percentage="$checklist->progress_percentage ?? 0" id="validation-progress">
                    Item Tervalidasi
                </x-progress-bar>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <span x-text="checkedCount">{{ $checklist->checked_count ?? 0 }}</span> dari 6 item sudah divalidasi
                </p>
            </div>

            <!-- Validation Checklist -->
            <form action="{{ route('admin.achievements.validation.process', $achievement) }}" method="POST" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                @csrf
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Checklist Validasi</h3>
                
                <div class="space-y-3">
                    @foreach(\App\Models\ValidationChecklist::CHECKLIST_ITEMS as $key => $label)
                        <x-checklist-item 
                            :name="$key" 
                            :label="$label" 
                            :checked="$checklist->{$key . '_valid'} ?? false"
                            :notes="$checklist->{$key . '_notes'} ?? ''"
                        />
                    @endforeach
                </div>

                <!-- Overall Notes -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Catatan Keseluruhan</label>
                    <textarea name="checklist[overall_notes]" rows="3" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500" placeholder="Catatan tambahan untuk verifikasi...">{{ $checklist->overall_notes ?? '' }}</textarea>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 space-y-4">
                    <div x-show="selectedAction === 'reject'" x-cloak class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                        <label class="block text-sm font-medium text-red-700 dark:text-red-400 mb-2">Alasan Penolakan *</label>
                        <textarea name="rejection_reason" rows="2" 
                            :required="selectedAction === 'reject'"
                            class="w-full border-red-300 dark:border-red-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-red-500 focus:ring-red-500" 
                            placeholder="Jelaskan alasan penolakan..."></textarea>
                    </div>

                    <div x-show="selectedAction === 'request_revision'" x-cloak class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <label class="block text-sm font-medium text-blue-700 dark:text-blue-400 mb-2">Alasan Permintaan Revisi *</label>
                        <textarea name="revision_reason" rows="2" 
                            :required="selectedAction === 'request_revision'"
                            class="w-full border-blue-300 dark:border-blue-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                            placeholder="Jelaskan dokumen apa yang diperlukan..."></textarea>
                        <div class="mt-3">
                            <label class="block text-sm font-medium text-blue-700 dark:text-blue-400 mb-2">Dokumen yang Diperlukan</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach(\App\Models\AchievementDocument::DOCUMENT_TYPES as $type => $label)
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="required_documents[]" value="{{ $type }}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" name="action" value="approve" 
                            @click.prevent="selectedAction = 'approve'; $nextTick(() => $el.closest('form').submit())"
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg font-medium flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Approve
                        </button>
                        <button type="submit" name="action" value="reject" 
                            @click.prevent="selectedAction = 'reject'; if(document.querySelector('[name=rejection_reason]').value) { $el.closest('form').submit(); } else { alert('Alasan penolakan wajib diisi'); }"
                            class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-lg font-medium flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Reject
                        </button>
                        <button type="submit" name="action" value="request_revision" 
                            @click.prevent="selectedAction = 'request_revision'; if(document.querySelector('[name=revision_reason]').value) { $el.closest('form').submit(); } else { alert('Alasan revisi wajib diisi'); }"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-medium flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Minta Revisi
                        </button>
                    </div>
                </div>
            </form>

            <!-- Validation History -->
            @if($achievement->validationLogs->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Riwayat Validasi</h3>
                <div class="space-y-4">
                    @foreach($achievement->validationLogs->take(3) as $log)
                    <div class="flex gap-3 pb-4 border-b border-gray-200 dark:border-gray-700 last:border-0 last:pb-0">
                        <div class="flex-shrink-0 w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-900 dark:text-white">
                                <span class="font-medium">{{ $log->validator?->name }}</span> mengubah status dari 
                                <span class="font-medium">{{ $log->old_status }}</span> ke 
                                <span class="font-medium">{{ $log->new_status }}</span>
                            </p>
                            @if($log->notes)
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $log->notes }}</p>
                            @endif
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $log->validated_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @if($achievement->validationLogs->count() > 3)
                <a href="{{ route('admin.achievements.validation.history', $achievement) }}" class="block text-center text-purple-600 hover:text-purple-700 dark:text-purple-400 text-sm mt-4">
                    Lihat semua riwayat →
                </a>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function validationForm() {
    return {
        selectedAction: '',
        checkedCount: {{ $checklist->checked_count ?? 0 }},
        totalItems: 6,
        
        updateProgress() {
            const checkboxes = document.querySelectorAll('input[type="checkbox"][name^="checklist"]');
            this.checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
            const percentage = (this.checkedCount / this.totalItems) * 100;
            
            const progressBar = document.querySelector('#validation-progress div > div');
            if (progressBar) {
                progressBar.style.width = percentage + '%';
                progressBar.className = progressBar.className.replace(/bg-(green|yellow|red)-500/g, '');
                if (percentage >= 80) {
                    progressBar.classList.add('bg-green-500');
                } else if (percentage >= 50) {
                    progressBar.classList.add('bg-yellow-500');
                } else {
                    progressBar.classList.add('bg-red-500');
                }
            }
        }
    }
}

// Document Verification Functions
function verifyDocument(documentId, action) {
    let notes = '';
    
    if (action === 'reject' || action === 'revision') {
        notes = prompt(action === 'reject' ? 'Alasan penolakan:' : 'Catatan revisi yang diperlukan:');
        if (!notes) {
            alert('Catatan wajib diisi untuk ' + (action === 'reject' ? 'penolakan' : 'permintaan revisi'));
            return;
        }
    }
    
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

function bulkVerify(action) {
    let notes = '';
    
    if (action === 'reject') {
        notes = prompt('Alasan penolakan untuk semua dokumen:');
        if (!notes) {
            alert('Alasan wajib diisi untuk penolakan');
            return;
        }
    }
    
    if (!confirm(`Yakin ingin ${action === 'approve' ? 'menyetujui' : 'menolak'} semua dokumen pending?`)) {
        return;
    }
    
    // Get all pending document IDs
    const pendingDocs = document.querySelectorAll('[data-status="pending"]');
    const documentIds = Array.from(pendingDocs).map(el => el.dataset.documentId);
    
    // For simplicity, we'll reload after bulk action
    // In production, you'd want a proper bulk endpoint
    const promises = documentIds.map(id => 
        fetch(`/admin/achievements/documents/${id}/verify`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ action: action, notes: notes || 'Bulk ' + action })
        })
    );
    
    Promise.all(promises).then(() => location.reload());
}
</script>
@endsection
