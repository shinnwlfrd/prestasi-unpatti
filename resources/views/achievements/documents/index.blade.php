@extends('layouts.app')

@section('title', 'Upload Dokumen')

@section('content')
<div x-data="documentUploader()" class="max-w-4xl mx-auto space-y-6">
    <!-- Header with Back Button -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center gap-4 mb-4">
            @php
                $isValidator = auth()->check() && auth()->user()->role === 'Validator';
                $backRoute = $isValidator ? route('validator.dashboard') : route('student.dashboard');
            @endphp
            <a href="{{ $backRoute }}" 
                class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div class="flex-1">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Upload Dokumen Bukti</h2>
                <p class="text-gray-500 dark:text-gray-400 mt-1">{{ $achievement->event_name }}</p>
            </div>
        </div>
        
        <!-- Info SK Resmi -->
        @if(!isset($isValidatorOrAdmin) || !$isValidatorOrAdmin)
        <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="font-medium text-blue-800 dark:text-blue-200">Informasi Upload Dokumen</p>
                    <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                        Anda dapat mengupload <strong>Sertifikat</strong> dan <strong>Dokumen Pendukung</strong> lainnya. 
                        <strong>SK Resmi</strong> akan diupload oleh Validator/Admin saat proses approval.
                    </p>
                </div>
            </div>
        </div>
        @else
        <div class="mt-4 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="font-medium text-emerald-800 dark:text-emerald-200">Mode Validator/Admin</p>
                    <p class="text-sm text-emerald-700 dark:text-emerald-300 mt-1">
                        Anda dapat mengupload <strong>semua jenis dokumen</strong> termasuk <strong>SK Resmi</strong>, 
                        <strong>Sertifikat</strong>, dan <strong>Dokumen Pendukung</strong> lainnya.
                    </p>
                </div>
            </div>
        </div>
        @endif
        
        @if($isNonAkademik)
        <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="font-medium text-yellow-800 dark:text-yellow-200">Prestasi Non-Akademik</p>
                    <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">Minimal 2 jenis dokumen berbeda diperlukan untuk validasi prestasi non-akademik.</p>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Upload Form -->
    <form id="uploadForm" action="{{ route('achievements.documents.store', $achievement) }}" method="POST" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6" @submit.prevent="submitForm()">
        @csrf
        
        <!-- Drag & Drop Area -->
        <div 
            class="border-2 border-dashed rounded-xl p-8 text-center transition-colors"
            :class="isDragging ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20' : 'border-gray-300 dark:border-gray-600'"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop($event)"
        >
            <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            <p class="mt-4 text-gray-600 dark:text-gray-400">
                Drag & drop file di sini, atau
                <label class="text-purple-600 hover:text-purple-700 dark:text-purple-400 cursor-pointer">
                    <span>pilih file</span>
                    <input type="file" class="hidden" multiple accept=".pdf,.jpg,.jpeg,.png" @change="handleFileSelect($event)">
                </label>
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">PDF, JPG, PNG (Maks. 10MB per file)</p>
        </div>

        <!-- Selected Files -->
        <div x-show="files.length > 0" class="mt-6 space-y-4">
            <h4 class="font-medium text-gray-900 dark:text-white">File yang Dipilih</h4>
            
            <template x-for="(file, index) in files" :key="index">
                <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                    <!-- Preview -->
                    <div class="w-16 h-16 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center overflow-hidden flex-shrink-0">
                        <template x-if="file.preview">
                            <img :src="file.preview" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!file.preview">
                            <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 2l5 5h-5V4z"/>
                            </svg>
                        </template>
                    </div>
                    
                    <!-- File Info -->
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="file.name"></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400" x-text="formatFileSize(file.size)"></p>
                        
                        <!-- Document Type Select -->
                        <select 
                            :name="'document_types[' + index + ']'" 
                            x-model="file.type"
                            class="mt-2 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:border-purple-500 focus:ring-purple-500"
                        >
                            <option value="">Pilih Jenis Dokumen</option>
                            @foreach($documentTypes as $type => $label)
                            <option value="{{ $type }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Upload Progress -->
                    <div x-show="file.uploading" class="w-24">
                        <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-purple-600 transition-all" :style="'width: ' + file.progress + '%'"></div>
                        </div>
                        <p class="text-xs text-gray-500 text-center mt-1" x-text="file.progress + '%'"></p>
                    </div>
                    
                    <!-- Remove Button -->
                    <button type="button" @click="removeFile(index)" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </template>
        </div>

        <!-- External Links -->
        <div class="mt-6">
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-medium text-gray-900 dark:text-white">Link Publikasi (Opsional)</h4>
                <button type="button" @click="addLink()" class="text-sm text-purple-600 hover:text-purple-700 dark:text-purple-400">
                    + Tambah Link
                </button>
            </div>
            
            <template x-for="(link, index) in externalLinks" :key="index">
                <div class="flex gap-3 mb-3">
                    <input 
                        type="text" 
                        :name="'external_links[' + index + '][title]'" 
                        x-model="link.title"
                        placeholder="Judul (opsional)"
                        class="w-1/3 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500"
                    >
                    <input 
                        type="url" 
                        :name="'external_links[' + index + '][url]'" 
                        x-model="link.url"
                        placeholder="https://..."
                        class="flex-1 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500"
                    >
                    <button type="button" @click="removeLink(index)" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>
        </div>

        <!-- Submit Button -->
        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ $backRoute ?? (auth()->check() && auth()->user()->role === 'Validator' ? route('validator.dashboard') : route('student.dashboard')) }}" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                Batal
            </a>
            <button 
                type="submit" 
                class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="files.length === 0 || !allTypesSelected()"
            >
                Upload Dokumen
            </button>
        </div>
    </form>

    <!-- Existing Documents -->
    @if($achievement->documents->isNotEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Dokumen yang Sudah Diunggah</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @foreach($achievement->documents as $document)
                <x-document-card :document="$document" :deletable="true" />
            @endforeach
        </div>
    </div>
    @endif
</div>

<script>
function documentUploader() {
    return {
        files: [],
        externalLinks: [],
        isDragging: false,
        
        handleDrop(event) {
            this.isDragging = false;
            const droppedFiles = Array.from(event.dataTransfer.files);
            this.addFiles(droppedFiles);
        },
        
        handleFileSelect(event) {
            const selectedFiles = Array.from(event.target.files);
            this.addFiles(selectedFiles);
            event.target.value = '';
        },
        
        addFiles(newFiles) {
            newFiles.forEach(file => {
                if (this.validateFile(file)) {
                    const fileObj = {
                        file: file,
                        name: file.name,
                        size: file.size,
                        type: '',
                        preview: null,
                        uploading: false,
                        progress: 0
                    };
                    
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            fileObj.preview = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                    
                    this.files.push(fileObj);
                }
            });
        },
        
        validateFile(file) {
            const maxSize = 10 * 1024 * 1024; // 10MB
            const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
            
            if (file.size > maxSize) {
                alert('File ' + file.name + ' terlalu besar. Maksimal 10MB.');
                return false;
            }
            
            if (!allowedTypes.includes(file.type)) {
                alert('Format file ' + file.name + ' tidak didukung. Gunakan PDF, JPG, atau PNG.');
                return false;
            }
            
            return true;
        },
        
        removeFile(index) {
            this.files.splice(index, 1);
        },
        
        addLink() {
            this.externalLinks.push({ title: '', url: '' });
        },
        
        removeLink(index) {
            this.externalLinks.splice(index, 1);
        },
        
        formatFileSize(bytes) {
            if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
            if (bytes >= 1024) return (bytes / 1024).toFixed(2) + ' KB';
            return bytes + ' bytes';
        },
        
        allTypesSelected() {
            return this.files.every(f => f.type !== '');
        },
        
        submitForm() {
            if (this.files.length === 0) {
                alert('Minimal satu dokumen harus diunggah.');
                return;
            }
            
            if (!this.allTypesSelected()) {
                alert('Pilih jenis untuk setiap dokumen.');
                return;
            }
            
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            
            // Add files
            this.files.forEach((fileObj, index) => {
                formData.append(`documents[${index}]`, fileObj.file);
                formData.append(`document_types[${index}]`, fileObj.type);
            });
            
            // Add external links
            this.externalLinks.forEach((link, index) => {
                if (link.url) {
                    formData.append(`external_links[${index}][url]`, link.url);
                    formData.append(`external_links[${index}][title]`, link.title || '');
                }
            });
            
            // Submit via fetch
            fetch('{{ route("achievements.documents.store", $achievement) }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (response.ok || response.redirected) {
                    window.location.reload();
                } else {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Gagal upload dokumen');
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.message || 'Terjadi kesalahan saat upload dokumen');
            });
        }
    }
}

function deleteDocument(id) {
    if (confirm('Yakin ingin menghapus dokumen ini?')) {
        fetch('/documents/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}
</script>
@endsection
