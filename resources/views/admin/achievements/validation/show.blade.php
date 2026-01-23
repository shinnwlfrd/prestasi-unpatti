@extends('layouts.admin')

@section('title', 'Validasi Prestasi')

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div x-data="validationForm()" class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            @if(request('back'))
                <a href="{{ request('back') }}" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg" title="Kembali">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
            @else
                <button onclick="window.history.back()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg" title="Kembali">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </button>
            @endif
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
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Prestasi</h3>
                    @if($achievement->is_appeal)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-bold bg-orange-500 text-white shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            PRESTASI BANDING
                        </span>
                    @endif
                </div>
                
                @if($achievement->is_appeal)
                <div class="mb-6 p-5 bg-gradient-to-r from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-amber-900/20 border-l-4 border-orange-500 rounded-r-lg shadow-sm">
                    <div class="flex gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-bold text-orange-900 dark:text-orange-200 text-base mb-2">Informasi Banding</h4>
                            <div class="space-y-2">
                                <div>
                                    <p class="text-xs font-semibold text-orange-800 dark:text-orange-300 uppercase tracking-wide mb-1">Alasan Banding:</p>
                                    <p class="text-sm text-orange-900 dark:text-orange-100 leading-relaxed">{{ $achievement->appeal_reason }}</p>
                                </div>
                                @if($achievement->publication_link)
                                    <div>
                                        <p class="text-xs font-semibold text-orange-800 dark:text-orange-300 uppercase tracking-wide mb-1">Link Publikasi:</p>
                                        <a href="{{ $achievement->publication_link }}" target="_blank" 
                                            class="inline-flex items-center gap-1 text-sm text-orange-700 dark:text-orange-300 hover:text-orange-900 dark:hover:text-orange-100 font-medium underline">
                                            {{ Str::limit($achievement->publication_link, 60) }}
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        </a>
                                    </div>
                                @endif
                                <div class="pt-2 border-t border-orange-200 dark:border-orange-800">
                                    <p class="text-xs text-orange-700 dark:text-orange-400">
                                        <svg class="w-3.5 h-3.5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Diajukan pada: <strong>{{ $achievement->appealed_at?->format('d M Y, H:i') }} WIT</strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
                <dl class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Nama Lomba</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $achievement->event_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Kategori</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $achievement->achievement?->category?->name ?? '-' }}</dd>
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
            <form action="{{ route('admin.achievements.validation.process', $achievement) }}" method="POST" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
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
                        <button type="button"
                            @click="showApproveModal = true"
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg font-medium flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Approve
                        </button>
                        <button type="submit" name="action" value="reject" 
                            @click.prevent="selectedAction = 'reject'; const reason = document.querySelector('[name=rejection_reason]'); if(reason.value) { $el.closest('form').submit(); } else { reason.focus(); }"
                            class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-lg font-medium flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Reject
                        </button>
                        <button type="submit" name="action" value="request_revision" 
                            @click.prevent="selectedAction = 'request_revision'; const reason = document.querySelector('[name=revision_reason]'); if(reason.value) { $el.closest('form').submit(); } else { reason.focus(); }"
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

    <!-- Modal Upload SK Resmi -->
    <div x-show="showApproveModal" 
         x-cloak
         @click.self="showApproveModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
        <div @click.away="showApproveModal = false" 
             class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Approve Prestasi</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Upload SK Resmi untuk approve</p>
                    </div>
                </div>
                <button @click="showApproveModal = false" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-4">
                <!-- Info Box -->
                <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="font-medium text-green-800 dark:text-green-200 text-sm">Informasi Penting</p>
                            <p class="text-sm text-green-700 dark:text-green-300 mt-1">
                                Anda akan menyetujui prestasi <strong>{{ $achievement->event_name }}</strong> untuk mahasiswa <strong>{{ $achievement->student?->name }}</strong>.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Achievement Summary -->
                <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Ringkasan Prestasi</h4>
                    <dl class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Mahasiswa</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">{{ $achievement->student?->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">NIM</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">{{ $achievement->student_id }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Kategori</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">{{ $achievement->achievement?->category?->name ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Level</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">{{ $achievement->level }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Upload SK Resmi -->
                <div class="space-y-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            Upload SK Resmi <span class="text-red-500">*</span>
                        </span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
                            File SK Resmi wajib diupload untuk approve prestasi
                        </span>
                    </label>
                    
                    <div class="relative">
                        <input type="file" 
                               id="sk_resmi_modal" 
                               accept=".pdf,.jpg,.jpeg,.png" 
                               @change="handleFileSelect($event)"
                               class="hidden">
                        <label for="sk_resmi_modal" 
                               class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-xl cursor-pointer transition-colors"
                               :class="skFile ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-green-400 bg-gray-50 dark:bg-gray-900/50'">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-10 h-10 mb-3" :class="skFile ? 'text-green-500' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <p class="mb-2 text-sm" :class="skFile ? 'text-green-700 dark:text-green-300 font-medium' : 'text-gray-500 dark:text-gray-400'">
                                    <span x-show="!skFile">Klik untuk pilih file atau drag & drop</span>
                                    <span x-show="skFile" x-text="skFileName"></span>
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">PDF, JPG, PNG (Maks. 10MB)</p>
                            </div>
                        </label>
                    </div>

                    <!-- File Preview -->
                    <div x-show="skFile" x-cloak class="p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-green-900 dark:text-green-100" x-text="skFileName"></p>
                                    <p class="text-xs text-green-600 dark:text-green-400" x-text="skFileSize"></p>
                                </div>
                            </div>
                            <button type="button" @click="clearFile()" class="p-1 hover:bg-green-100 dark:hover:bg-green-900/40 rounded">
                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Catatan Tambahan (Opsional) -->
                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                        Catatan Approval <span class="text-gray-400 text-xs">(Opsional)</span>
                    </label>
                    <textarea x-model="approvalNotes" 
                              rows="3" 
                              placeholder="Tambahkan catatan untuk approval ini..."
                              class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"></textarea>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="sticky bottom-0 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 px-6 py-4 flex gap-3">
                <button type="button" 
                        @click="showApproveModal = false"
                        class="flex-1 px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 font-medium transition-colors">
                    Batal
                </button>
                <button type="button" 
                        @click="submitApproval()"
                        :disabled="!skFile"
                        :class="skFile ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-400 cursor-not-allowed'"
                        class="flex-1 px-4 py-2.5 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span x-show="!isSubmitting">Approve Prestasi</span>
                    <span x-show="isSubmitting">Memproses...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function validationForm() {
    return {
        selectedAction: '',
        checkedCount: {{ $checklist->checked_count ?? 0 }},
        totalItems: 6,
        showApproveModal: false,
        skFile: null,
        skFileName: '',
        skFileSize: '',
        approvalNotes: '',
        isSubmitting: false,
        
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
        },
        
        handleFileSelect(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            // Validate file type
            const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
                alert('Format file tidak didukung. Gunakan PDF, JPG, atau PNG.');
                event.target.value = '';
                return;
            }
            
            // Validate file size (10MB)
            const maxSize = 10 * 1024 * 1024;
            if (file.size > maxSize) {
                alert('Ukuran file terlalu besar. Maksimal 10MB.');
                event.target.value = '';
                return;
            }
            
            this.skFile = file;
            this.skFileName = file.name;
            this.skFileSize = this.formatFileSize(file.size);
        },
        
        clearFile() {
            this.skFile = null;
            this.skFileName = '';
            this.skFileSize = '';
            document.getElementById('sk_resmi_modal').value = '';
        },
        
        formatFileSize(bytes) {
            if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
            if (bytes >= 1024) return (bytes / 1024).toFixed(2) + ' KB';
            return bytes + ' bytes';
        },
        
        submitApproval() {
            if (!this.skFile) {
                alert('SK Resmi wajib diupload untuk approve prestasi!');
                return;
            }
            
            if (this.isSubmitting) return;
            
            this.isSubmitting = true;
            this.selectedAction = 'approve';
            
            // Create new FormData
            const formData = new FormData();
            
            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                             document.querySelector('input[name="_token"]')?.value || 
                             '{{ csrf_token() }}';
            formData.append('_token', csrfToken);
            
            // Add action (REQUIRED)
            formData.append('action', 'approve');
            
            // Add SK file (REQUIRED)
            formData.append('sk_resmi', this.skFile);
            
            // Add approval notes if provided
            if (this.approvalNotes) {
                formData.append('notes', this.approvalNotes);
            }
            
            // Add checklist data from form
            const form = document.querySelector('form[action*="validation"]');
            const checklistInputs = form.querySelectorAll('input[name^="checklist"], textarea[name^="checklist"]');
            checklistInputs.forEach(input => {
                if (input.type === 'checkbox') {
                    formData.append(input.name, input.checked ? '1' : '0');
                } else if (input.value) {
                    formData.append(input.name, input.value);
                }
            });
            
            // Submit form
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (response.ok || response.redirected) {
                    // Success - redirect to validation index
                    window.location.href = '{{ route("admin.achievements.validation.index") }}';
                } else {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Gagal approve prestasi');
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.message || 'Terjadi kesalahan saat approve prestasi');
                this.isSubmitting = false;
            });
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
