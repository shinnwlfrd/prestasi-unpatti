@extends('layouts.admin')

@section('title', 'Riwayat Validasi')

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
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Riwayat Validasi</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $achievement->event_name }}</p>
        </div>
    </div>

    <!-- Timeline -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        @if($logs->isEmpty())
            <p class="text-center text-gray-500 dark:text-gray-400 py-8">Belum ada riwayat validasi</p>
        @else
            <div class="relative">
                <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>
                <div class="space-y-8">
                    @foreach($logs as $log)
                    <div class="relative flex gap-6">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center z-10
                            @if($log->new_status === 'approved') bg-green-500
                            @elseif($log->new_status === 'rejected') bg-red-500
                            @elseif($log->new_status === 'need_revision') bg-blue-500
                            @else bg-yellow-500
                            @endif">
                            @if($log->new_status === 'approved')
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            @elseif($log->new_status === 'rejected')
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            @elseif($log->new_status === 'need_revision')
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            @else
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @endif
                        </div>
                        <div class="flex-1 bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        Status diubah dari 
                                        <span class="px-2 py-0.5 bg-gray-200 dark:bg-gray-700 rounded text-sm">{{ ucfirst($log->old_status) }}</span>
                                        ke 
                                        <span class="px-2 py-0.5 
                                            @if($log->new_status === 'approved') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                            @elseif($log->new_status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                            @elseif($log->new_status === 'need_revision') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400
                                            @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400
                                            @endif rounded text-sm">{{ ucfirst(str_replace('_', ' ', $log->new_status)) }}</span>
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        oleh <span class="font-medium">{{ $log->validator?->name ?? 'System' }}</span>
                                    </p>
                                </div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $log->validated_at->format('d M Y H:i') }}
                                </span>
                            </div>
                            @if($log->notes)
                            <div class="mt-3 p-3 bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700">
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $log->notes }}</p>
                            </div>
                            @endif
                            @if($log->metadata)
                            <div class="mt-3">
                                @if(isset($log->metadata['required_documents']))
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Dokumen yang diminta: 
                                    @foreach($log->metadata['required_documents'] as $doc)
                                        <span class="inline-block px-2 py-0.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded text-xs mr-1">
                                            {{ \App\Models\AchievementDocument::DOCUMENT_TYPES[$doc] ?? $doc }}
                                        </span>
                                    @endforeach
                                </p>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
