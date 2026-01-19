@extends('layouts.app')

@section('title', 'Validasi Prestasi')
@section('subtitle', 'Detail & Validasi')

@section('content')
<div x-data="validationForm()" class="space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('validator.dashboard') }}" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
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
            @php
                $statusConfig = [
                    'pending' => ['label' => 'Menunggu', 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'],
                    'approved' => ['label' => 'Disetujui', 'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'],
                    'rejected' => ['label' => 'Ditolak', 'class' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'],
                    'need_revision' => ['label' => 'Perlu Revisi', 'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'],
                ];
                $status = $statusConfig[$achievement->validation_status] ?? $statusConfig['pending'];
            @endphp
            <span class="px-3 py-1.5 rounded-full text-xs font-semibold {{ $status['class'] }}">{{ $status['label'] }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Left: Achievement Details & Documents -->
        <div class="space-y-6">
            <!-- Achievement Info -->
            <x-card title="Informasi Prestasi">
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
            </x-card>

            <!-- Documents -->
            <x-card title="Dokumen Bukti ({{ $achievement->documents->count() }})" :padding="false">
                <div class="p-6">
                    @php
                        $docStats = [
                            'pending' => $achievement->documents->where('status', 'pending')->count(),
                            'approved' => $achievement->documents->where('status', 'approved')->count(),
                            'rejected' => $achievement->documents->where('status', 'rejected')->count(),
                        ];
                    @endphp
                    <div class="flex gap-2 mb-4 text-xs">
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400 rounded">{{ $docStats['pending'] }} Pending</span>
                        <span class="px-2 py-1 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded">{{ $docStats['approved'] }} Approved</span>
                        <span class="px-2 py-1 bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 rounded">{{ $docStats['rejected'] }} Rejected</span>
                    </div>
                    
                    @if($achievement->documents->isEmpty())
                        <p class="text-gray-500 dark:text-gray-400 text-center py-8">Belum ada dokumen yang diunggah</p>
                    @else
                        <div class="grid grid-cols-1 gap-4">
                            @foreach($achievement->documents as $document)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                                @if($document->isPdf())
                                                    <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                                                    </svg>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-white text-sm">{{ $document->type_name }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $document->file_name }} • {{ $document->file_size_formatted }}</p>
                                            </div>
                                        </div>
                                        <span class="px-2 py-1 rounded text-xs font-medium bg-{{ $document->status_badge }}-100 text-{{ $document->status_badge }}-700 dark:bg-{{ $document->status_badge }}-900/30 dark:text-{{ $document->status_badge }}-400">
                                            {{ $document->status_label }}
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-center gap-2 mt-3">
                                        <a href="{{ route('achievements.documents.preview', $document) }}" target="_blank"
                                            class="px-3 py-1.5 text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/40">
                                            Lihat
                                        </a>
                                        @if($document->status === 'pending')
                                            <button type="button" onclick="verifyDocument({{ $document->id }}, 'approve')"
                                                class="px-3 py-1.5 text-xs font-medium text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/40">
                                                Setujui
                                            </button>
                                            <button type="button" onclick="verifyDocument({{ $document->id }}, 'reject')"
                                                class="px-3 py-1.5 text-xs font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/40">
                                                Tolak
                                            </button>
                                            <button type="button" onclick="verifyDocument({{ $document->id }}, 'revision')"
                                                class="px-3 py-1.5 text-xs font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 rounded-lg hover:bg-amber-100 dark:hover:bg-amber-900/40">
                                                Minta Revisi
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </x-card>
        </div>

        <!-- Right: Validation Form -->
        <div class="space-y-6">
            <!-- Progress Bar -->
            <x-card title="Progress Validasi">
                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-gray-600 dark:text-gray-400">Item Tervalidasi</span>
                        <span class="font-medium text-gray-900 dark:text-white" x-text="checkedCount + '/6'"></span>
                    </div>
                    <div class="w-full h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500 rounded-full transition-all duration-300" :style="'width: ' + (checkedCount / 6 * 100) + '%'"></div>
                    </div>
                </div>
            </x-card>

            <!-- Validation Checklist -->
            <form action="{{ route('validator.achievements.validate', $achievement) }}" method="POST">
                @csrf
                <x-card title="Checklist Validasi">
                    <div class="space-y-3">
                        @php
                            $checklistItems = [
                                'nama_peserta' => 'Nama Peserta',
                                'nama_lomba' => 'Nama Lomba',
                                'tanggal' => 'Tanggal Pelaksanaan',
                                'peringkat' => 'Peringkat/Ranking',
                                'penyelenggara' => 'Penyelenggara',
                                'keaslian_dokumen' => 'Keaslian Dokumen',
                            ];
                        @endphp
                        @foreach($checklistItems as $key => $label)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <div class="flex items-start gap-3">
                                    <div class="flex items-center h-6">
                                        <input type="checkbox" name="checklist[{{ $key }}_valid]" id="{{ $key }}_valid" value="1"
                                            {{ ($checklist->{$key . '_valid'} ?? false) ? 'checked' : '' }}
                                            class="w-5 h-5 text-emerald-600 bg-gray-100 border-gray-300 rounded focus:ring-emerald-500 cursor-pointer"
                                            @change="updateProgress()">
                                    </div>
                                    <div class="flex-1">
                                        <label for="{{ $key }}_valid" class="font-medium text-gray-900 dark:text-white cursor-pointer">{{ $label }}</label>
                                        <input type="text" name="checklist[{{ $key }}_notes]" placeholder="Catatan (opsional)"
                                            value="{{ $checklist->{$key . '_notes'} ?? '' }}"
                                            class="w-full mt-2 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Overall Notes -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Catatan Keseluruhan</label>
                        <textarea name="notes" rows="3" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Catatan tambahan untuk verifikasi...">{{ $checklist->overall_notes ?? '' }}</textarea>
                    </div>

                    <!-- Rejection/Revision Reason -->
                    <div x-show="selectedAction === 'reject'" x-cloak class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                        <label class="block text-sm font-medium text-red-700 dark:text-red-400 mb-2">Alasan Penolakan *</label>
                        <textarea name="rejection_reason" rows="2" 
                            :required="selectedAction === 'reject'"
                            class="w-full border-red-300 dark:border-red-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-red-500 focus:ring-red-500" 
                            placeholder="Jelaskan alasan penolakan..."></textarea>
                    </div>

                    <div x-show="selectedAction === 'request_revision'" x-cloak class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <label class="block text-sm font-medium text-blue-700 dark:text-blue-400 mb-2">Alasan Permintaan Revisi *</label>
                        <textarea name="revision_reason" rows="2" 
                            :required="selectedAction === 'request_revision'"
                            class="w-full border-blue-300 dark:border-blue-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                            placeholder="Jelaskan dokumen apa yang diperlukan..."></textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3 mt-6">
                        <button type="submit" name="action" value="approve" 
                            @click.prevent="selectedAction = 'approve'; $nextTick(() => $el.closest('form').submit())"
                            class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 rounded-lg font-medium flex items-center justify-center gap-2">
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
                            Revisi
                        </button>
                    </div>
                </x-card>
            </form>

            <!-- Validation History -->
            @if($achievement->validationLogs->isNotEmpty())
            <x-card title="Riwayat Validasi">
                <div class="space-y-4">
                    @foreach($achievement->validationLogs->take(5) as $log)
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
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $log->validated_at?->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </x-card>
            @endif
        </div>
    </div>
</div>

<script>
function validationForm() {
    return {
        selectedAction: '',
        checkedCount: {{ $checklist->checked_count ?? 0 }},
        
        updateProgress() {
            const checkboxes = document.querySelectorAll('input[type="checkbox"][name^="checklist"]');
            this.checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
        }
    }
}

function verifyDocument(documentId, action) {
    let notes = '';
    
    if (action === 'reject' || action === 'revision') {
        notes = prompt(action === 'reject' ? 'Alasan penolakan:' : 'Catatan revisi yang diperlukan:');
        if (!notes) {
            alert('Catatan wajib diisi untuk ' + (action === 'reject' ? 'penolakan' : 'permintaan revisi'));
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
            alert(data.error || 'Gagal memproses verifikasi');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    });
}
</script>
@endsection
