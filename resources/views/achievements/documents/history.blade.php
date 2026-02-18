@extends('layouts.app')

@section('title', 'Riwayat Dokumen')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-4">
                @if($document->studentAchievement)
                    <a href="{{ route('achievements.documents.index', $document->studentAchievement) }}"
                        class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                @else
                    <a href="javascript:history.back()"
                        class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                @endif
                <div class="flex-1">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Riwayat Dokumen</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $document->file_name }}</p>
                </div>
                <x-badge :type="$document->status_badge" size="md">{{ $document->status_label }}</x-badge>
            </div>
        </div>

        <!-- Document Info -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Informasi Dokumen</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Jenis Dokumen</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $document->type_name }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Ukuran File</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $document->file_size_formatted }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Prestasi</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ $document->studentAchievement?->event_name }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Diupload</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ $document->created_at->format('d M Y H:i') }}</dd>
                </div>
                @if($document->verified_at)
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Diverifikasi oleh</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $document->verifier?->name ?? '-' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Tanggal Verifikasi</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $document->verified_at->format('d M Y H:i') }}</dd>
                    </div>
                @endif
            </dl>

            @if($document->revision_notes)
                <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Catatan:</p>
                    <p class="text-sm text-gray-900 dark:text-white">{{ $document->revision_notes }}</p>
                </div>
            @endif
        </div>

        <!-- Revision Timeline -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-6">Riwayat Perubahan</h3>

            @if($document->revisions->isEmpty())
                <p class="text-center text-gray-500 dark:text-gray-400 py-8">Belum ada riwayat perubahan</p>
            @else
                <div class="relative">
                    <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>
                    <div class="space-y-6">
                        @foreach($document->revisions as $revision)
                            <div class="relative flex gap-4">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center z-10
                                        @if($revision->action === 'approved') bg-green-500
                                        @elseif($revision->action === 'rejected') bg-red-500
                                        @elseif($revision->action === 'revision_requested') bg-blue-500
                                        @elseif($revision->action === 'replaced') bg-yellow-500
                                        @else bg-gray-500
                                        @endif">
                                    @if($revision->action === 'approved')
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @elseif($revision->action === 'rejected')
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    @elseif($revision->action === 'revision_requested')
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    @elseif($revision->action === 'replaced')
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="flex-1 bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <x-badge :type="$revision->action_badge"
                                                size="sm">{{ $revision->action_label }}</x-badge>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                oleh <span
                                                    class="font-medium text-gray-900 dark:text-white">{{ $revision->performer?->name ?? 'System' }}</span>
                                            </p>
                                        </div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $revision->created_at->format('d M Y H:i') }}
                                        </span>
                                    </div>
                                    @if($revision->notes)
                                        <div
                                            class="mt-2 p-2 bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700">
                                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $revision->notes }}</p>
                                        </div>
                                    @endif
                                    @if($revision->file_name && $revision->action === 'replaced')
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                            File sebelumnya: {{ $revision->file_name }}
                                        </p>
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