@extends('layouts.admin')

@section('title', 'Detail Banding')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.appeals.index') }}" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Detail Banding</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $appeal->studentAchievement?->event_name }}</p>
        </div>
        <div class="ml-auto">
            <x-badge :type="$appeal->status_badge" size="md">{{ $appeal->status_label }}</x-badge>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Appeal Info -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Informasi Banding</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Mahasiswa</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $appeal->studentAchievement?->student?->name ?? '-' }} ({{ $appeal->student_id }})
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Tanggal Pengajuan</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $appeal->created_at->format('d F Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Alasan Banding</dt>
                        <dd class="text-sm text-gray-900 dark:text-white mt-1 p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                            {{ $appeal->appeal_reason }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Achievement Info -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Informasi Prestasi</h3>
                <dl class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Nama Lomba</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $appeal->studentAchievement?->event_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Tingkat</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $appeal->studentAchievement?->level }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Penyelenggara</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $appeal->studentAchievement?->organizer }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Skor Kredibilitas</dt>
                        <dd class="text-sm font-medium">
                            <x-badge :type="$appeal->studentAchievement?->credibility_badge ?? 'secondary'">
                                {{ number_format($appeal->studentAchievement?->credibility_score ?? 0, 0) }}%
                            </x-badge>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Documents & Review -->
        <div class="space-y-6">
            <!-- Documents -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Dokumen</h3>
                @if($appeal->studentAchievement?->documents->isEmpty())
                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">Tidak ada dokumen</p>
                @else
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($appeal->studentAchievement->documents as $document)
                            <x-document-card :document="$document" />
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Review Form -->
            @if($appeal->status === 'pending')
            <form action="{{ route('admin.appeals.review', $appeal) }}" method="POST" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                @csrf
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Review Banding</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Catatan Review <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            name="review_notes" 
                            rows="4" 
                            required
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500"
                            placeholder="Berikan catatan untuk keputusan Anda..."
                        ></textarea>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" name="action" value="approve" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg font-medium flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Terima Banding
                        </button>
                        <button type="submit" name="action" value="reject" class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-lg font-medium flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Tolak Banding
                        </button>
                    </div>
                </div>
            </form>
            @else
            <!-- Review Result -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Hasil Review</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Direview oleh</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $appeal->reviewer?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Tanggal Review</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $appeal->reviewed_at?->format('d F Y H:i') ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Catatan</dt>
                        <dd class="text-sm text-gray-900 dark:text-white mt-1 p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                            {{ $appeal->review_notes ?? '-' }}
                        </dd>
                    </div>
                </dl>
            </div>
            @endif

            <!-- Validation History -->
            @if($appeal->studentAchievement?->validationLogs->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Riwayat Validasi</h3>
                <div class="space-y-3">
                    @foreach($appeal->studentAchievement->validationLogs->take(5) as $log)
                    <div class="flex gap-3 pb-3 border-b border-gray-200 dark:border-gray-700 last:border-0 last:pb-0">
                        <div class="flex-shrink-0 w-2 h-2 mt-2 rounded-full 
                            @if($log->new_status === 'approved') bg-green-500
                            @elseif($log->new_status === 'rejected') bg-red-500
                            @else bg-yellow-500
                            @endif"></div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-900 dark:text-white">
                                {{ ucfirst(str_replace('_', ' ', $log->old_status)) }} → {{ ucfirst(str_replace('_', ' ', $log->new_status)) }}
                            </p>
                            @if($log->notes)
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($log->notes, 100) }}</p>
                            @endif
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                {{ $log->validator?->name }} - {{ $log->validated_at->format('d M Y H:i') }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
