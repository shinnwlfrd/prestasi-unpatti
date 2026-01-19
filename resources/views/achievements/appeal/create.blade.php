@extends('layouts.app')

@section('title', 'Ajukan Banding')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg">
                <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Ajukan Banding</h2>
                <p class="text-gray-500 dark:text-gray-400">{{ $achievement->event_name }}</p>
            </div>
        </div>
    </div>

    <!-- Rejection Info -->
    @php
        $latestLog = $achievement->validationLogs()->where('new_status', 'rejected')->latest()->first();
    @endphp
    @if($latestLog)
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-6">
        <h3 class="font-semibold text-red-800 dark:text-red-200 mb-2">Alasan Penolakan</h3>
        <p class="text-red-700 dark:text-red-300">{{ $latestLog->notes ?? 'Tidak ada alasan yang diberikan.' }}</p>
        <p class="text-sm text-red-600 dark:text-red-400 mt-2">
            Ditolak oleh {{ $latestLog->validator?->name }} pada {{ $latestLog->validated_at->format('d M Y H:i') }}
        </p>
    </div>
    @endif

    <!-- Appeal Form -->
    <form action="{{ route('achievements.appeal.store', $achievement) }}" method="POST" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        @csrf
        
        <div class="space-y-6">
            <!-- Appeal Reason -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Alasan Banding <span class="text-red-500">*</span>
                </label>
                <textarea 
                    name="appeal_reason" 
                    rows="5" 
                    required
                    minlength="50"
                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500 @error('appeal_reason') border-red-500 @enderror"
                    placeholder="Jelaskan mengapa Anda mengajukan banding dan bukti tambahan apa yang Anda miliki... (minimal 50 karakter)"
                >{{ old('appeal_reason') }}</textarea>
                @error('appeal_reason')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Minimal 50 karakter</p>
            </div>

            <!-- Additional Documents -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Dokumen Tambahan (Opsional)
                </label>
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center">
                    <input type="file" name="additional_documents[]" multiple accept=".pdf,.jpg,.jpeg,.png" class="hidden" id="additional-docs">
                    <label for="additional-docs" class="cursor-pointer">
                        <svg class="w-10 h-10 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">Klik untuk upload dokumen tambahan</p>
                        <p class="text-sm text-gray-500">PDF, JPG, PNG (Maks. 10MB)</p>
                    </label>
                </div>
            </div>

            <!-- Document Types -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Jenis Dokumen Tambahan
                </label>
                <div class="flex flex-wrap gap-3">
                    @foreach(\App\Models\AchievementDocument::DOCUMENT_TYPES as $type => $label)
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="document_types[]" value="{{ $type }}" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="mt-8 flex justify-end gap-3">
            <a href="{{ url()->previous() }}" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                Batal
            </a>
            <button type="submit" class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium">
                Ajukan Banding
            </button>
        </div>
    </form>

    <!-- Current Documents -->
    @if($achievement->documents->isNotEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Dokumen Saat Ini</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @foreach($achievement->documents as $document)
                <x-document-card :document="$document" />
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
