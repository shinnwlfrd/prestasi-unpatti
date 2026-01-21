@extends('layouts.app')

@section('title', 'Ajukan Banding')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Ajukan Banding</h2>
                <p class="text-gray-500 dark:text-gray-400">{{ $achievement->event_name }}</p>
            </div>
        </div>
        
        <!-- Info Box -->
        <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="font-medium text-blue-800 dark:text-blue-200 text-sm">Informasi Banding</p>
                    <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                        Prestasi Anda diminta untuk direvisi. Anda dapat mengajukan banding jika merasa sudah memenuhi persyaratan dan ingin langsung disetujui tanpa revisi.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Revision Info -->
    @php
        $latestLog = $achievement->validationLogs()
            ->whereIn('new_status', ['Ditolak', 'Revisi'])
            ->latest('validated_at')
            ->first();
    @endphp
    @if($latestLog)
    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-6">
        <h3 class="font-semibold text-yellow-800 dark:text-yellow-200 mb-2">
            @if($latestLog->new_status === 'Ditolak')
                Alasan Penolakan
            @else
                Alasan Permintaan Revisi
            @endif
        </h3>
        <p class="text-yellow-700 dark:text-yellow-300">{{ $latestLog->notes ?? 'Tidak ada alasan yang diberikan.' }}</p>
        <p class="text-sm text-yellow-600 dark:text-yellow-400 mt-2">
            @if($latestLog->new_status === 'Ditolak')
                Ditolak oleh {{ $latestLog->validator?->name }} pada {{ $latestLog->validated_at->format('d M Y H:i') }}
            @else
                Diminta revisi oleh {{ $latestLog->validator?->name }} pada {{ $latestLog->validated_at->format('d M Y H:i') }}
            @endif
        </p>
    </div>
    @endif

    <!-- Appeal Form -->
    <form action="{{ route('achievements.appeal.store', $achievement) }}" method="POST" enctype="multipart/form-data" 
          class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6"
          x-data="{ submitting: false }" 
          @submit="submitting = true">
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
                    placeholder="Jelaskan mengapa prestasi Anda sudah memenuhi persyaratan dan layak disetujui tanpa revisi... (minimal 50 karakter)"
                >{{ old('appeal_reason') }}</textarea>
                @error('appeal_reason')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Minimal 50 karakter. Jelaskan bukti tambahan yang Anda miliki.</p>
            </div>

            <!-- Link Publikasi -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Link Publikasi
                </label>
                <input 
                    type="url" 
                    name="publication_link" 
                    value="{{ old('publication_link') }}"
                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500 @error('publication_link') border-red-500 @enderror"
                    placeholder="https://contoh.com/publikasi-prestasi"
                >
                @error('publication_link')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Masukkan link publikasi online jika ada (website, media sosial, dll)</p>
            </div>

            <!-- Info Box -->
            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="font-medium text-blue-800 dark:text-blue-200 text-sm">Catatan Penting</p>
                        <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                            Anda harus mengisi <strong>minimal salah satu</strong>: Link Publikasi <strong>ATAU</strong> Upload Dokumen Tambahan (atau keduanya).
                        </p>
                    </div>
                </div>
            </div>

            <!-- Additional Documents -->
            <div x-data="documentUploader()">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Dokumen Tambahan
                </label>
                @error('additional_documents')
                    <p class="text-red-500 text-sm mb-2">{{ $message }}</p>
                @enderror
                
                <!-- Upload Area -->
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center">
                    <input type="file" @change="addFiles($event)" multiple accept=".pdf,.jpg,.jpeg,.png" class="hidden" id="additional-docs">
                    <label for="additional-docs" class="cursor-pointer">
                        <svg class="w-10 h-10 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">Klik untuk upload dokumen tambahan</p>
                        <p class="text-sm text-gray-500">PDF, JPG, PNG (Maks. 10MB per file)</p>
                    </label>
                </div>

                <!-- File List -->
                <div x-show="files.length > 0" class="mt-4 space-y-3">
                    <template x-for="(file, index) in files" :key="index">
                        <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700">
                            <div class="flex-shrink-0 w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="file.name"></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400" x-text="formatFileSize(file.size)"></p>
                                
                                <!-- Document Type Selector -->
                                <select :name="'document_types[' + index + ']'" required
                                    class="mt-2 w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg">
                                    <option value="">Pilih Jenis Dokumen</option>
                                    @foreach(\App\Models\AchievementDocument::DOCUMENT_TYPES as $type => $label)
                                        @if($type !== 'sk_resmi' && $type !== 'link_publikasi')
                                        <option value="{{ $type }}">{{ $label }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <button type="button" @click="removeFile(index)" class="flex-shrink-0 p-1 text-red-600 hover:text-red-700 dark:text-red-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>

                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <strong>Catatan:</strong> SK Resmi hanya dapat diupload oleh Validator/Admin saat proses approval.
                </p>
            </div>
        </div>

        <!-- Submit -->
        <div class="mt-8 flex justify-end gap-3">
            <a href="{{ url()->previous() }}" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                Batal
            </a>
            <button type="submit" 
                    :disabled="submitting"
                    :class="submitting ? 'opacity-50 cursor-not-allowed' : ''"
                    class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium flex items-center gap-2">
                <svg x-show="submitting" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-text="submitting ? 'Memproses...' : 'Ajukan Banding'"></span>
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

<script>
function documentUploader() {
    return {
        files: [],
        fileObjects: [], // Store actual File objects
        
        addFiles(event) {
            const newFiles = Array.from(event.target.files);
            
            // Validate each file
            newFiles.forEach(file => {
                // Check file size (10MB)
                if (file.size > 10 * 1024 * 1024) {
                    alert(`File ${file.name} terlalu besar. Maksimal 10MB.`);
                    return;
                }
                
                // Check file type
                const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                if (!allowedTypes.includes(file.type)) {
                    alert(`File ${file.name} format tidak didukung. Gunakan PDF, JPG, atau PNG.`);
                    return;
                }
                
                // Add to display list
                this.files.push({
                    name: file.name,
                    size: file.size,
                    type: file.type
                });
                
                // Store actual file object
                this.fileObjects.push(file);
            });
            
            // Reset input
            event.target.value = '';
            
            // Update hidden inputs
            this.updateFormFiles();
        },
        
        removeFile(index) {
            this.files.splice(index, 1);
            this.fileObjects.splice(index, 1);
            this.updateFormFiles();
        },
        
        updateFormFiles() {
            // Remove existing hidden file inputs
            const existingInputs = document.querySelectorAll('input[name="additional_documents[]"][type="file"]:not(#additional-docs)');
            existingInputs.forEach(input => input.remove());
            
            // Create new hidden inputs for each file
            if (this.fileObjects.length > 0) {
                const form = document.querySelector('form');
                const dt = new DataTransfer();
                
                this.fileObjects.forEach(file => {
                    dt.items.add(file);
                });
                
                // Create single hidden input with all files
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'file';
                hiddenInput.name = 'additional_documents[]';
                hiddenInput.multiple = true;
                hiddenInput.style.display = 'none';
                hiddenInput.files = dt.files;
                
                form.appendChild(hiddenInput);
            }
        },
        
        formatFileSize(bytes) {
            if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
            if (bytes >= 1024) return (bytes / 1024).toFixed(2) + ' KB';
            return bytes + ' bytes';
        }
    }
}
</script>
@endsection
