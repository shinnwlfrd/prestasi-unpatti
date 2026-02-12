@extends('layouts.app')

@section('title', 'Ajukan Prestasi')
@section('subtitle', 'Form Pengajuan')

@php
    $userName = session('student_id') ?? 'Mahasiswa';
@endphp

@section('content')
    <div class="max-w-2xl mx-auto animate-fade-in">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('student.dashboard') }}"
                class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Dashboard
            </a>
        </div>

        <!-- Form Card -->
        <x-card title="Form Pengajuan Prestasi">
            <form action="{{ route('student.achievement.store') }}" method="POST" enctype="multipart/form-data"
                class="space-y-5" x-data="formComponent()" @submit="loading = true">
                @csrf

                <!-- Kategori Prestasi -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Kategori Prestasi <span class="text-red-500">*</span>
                    </label>
                    <select name="category_id" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                        <option value="">-- Pilih Kategori Prestasi --</option>
                        @foreach($categories ?? [] as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nama Event -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nama Event <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="event_name" value="{{ old('event_name') }}" required
                        placeholder="Contoh: Lomba Debat Nasional 2024"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    @error('event_name')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Level -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Level <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-3 gap-3">
                        @foreach(['Universitas', 'Nasional', 'Internasional'] as $lvl)
                            <label class="relative">
                                <input type="radio" name="level" value="{{ $lvl }}" {{ old('level') == $lvl ? 'checked' : '' }}
                                    required class="peer sr-only">
                                <div
                                    class="px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-center cursor-pointer transition-all peer-checked:border-indigo-500 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-900/30 hover:border-gray-300 dark:hover:border-gray-500">
                                    <span
                                        class="text-sm font-medium text-gray-700 dark:text-gray-300 peer-checked:text-indigo-600 dark:peer-checked:text-indigo-400">{{ $lvl }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('level')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Penyelenggara -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Penyelenggara <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="organizer" value="{{ old('organizer') }}" required
                        placeholder="Contoh: Kementerian Pendidikan"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    @error('organizer')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tanggal & Peringkat -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tanggal Event <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="event_date" value="{{ old('event_date') }}" required
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                        @error('event_date')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Peringkat/Ranking <span class="text-gray-400 text-xs">(Opsional)</span>
                        </label>
                        <input type="text" name="ranking" value="{{ old('ranking') }}"
                            placeholder="Contoh: Juara 1, Finalis"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                        @error('ranking')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Deskripsi -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Deskripsi <span class="text-gray-400 text-xs">(Opsional)</span>
                    </label>
                    <textarea name="description" rows="3" placeholder="Jelaskan prestasi yang diraih..."
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">{{ old('description') }}</textarea>
                </div>

                <!-- Upload File -->
                <div x-init="addFile()" x-cloak id="fileUploadSection">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Upload Dokumen <span class="text-red-500">*</span>
                    </label>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                        Dokumen pertama adalah <strong>Sertifikat/Piagam</strong> (wajib). Anda bisa menambahkan dokumen
                        pendukung lainnya.
                    </p>

                    <!-- File List -->
                    <div class="space-y-3 mb-3">
                        <template x-for="(fileItem, index) in files" :key="index">
                            <div class="border-2 border-gray-300 dark:border-gray-600 rounded-xl p-4 bg-white dark:bg-gray-800"
                                :class="index === 0 ? 'border-indigo-300 dark:border-indigo-700 bg-indigo-50 dark:bg-indigo-900/20' : ''">

                                <!-- Label untuk dokumen pertama -->
                                <div x-show="index === 0" class="mb-2 flex items-center gap-2">
                                    <span
                                        class="px-2 py-1 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 text-xs font-semibold rounded-full">
                                        Dokumen Utama
                                    </span>
                                    <span class="text-xs text-gray-600 dark:text-gray-400">Sertifikat/Piagam (Wajib)</span>
                                </div>

                                <div class="flex items-start gap-3">
                                    <!-- Preview -->
                                    <div
                                        class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center overflow-hidden flex-shrink-0 border-2 border-gray-200 dark:border-gray-600 relative group">
                                        <template x-if="fileItem.preview">
                                            <img :src="fileItem.preview" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!fileItem.preview && fileItem.file">
                                            <div class="text-center p-2">
                                                <svg class="w-8 h-8 text-red-500 mx-auto" fill="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path
                                                        d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 2l5 5h-5V4z" />
                                                </svg>
                                                <span class="text-xs text-gray-500">PDF</span>
                                            </div>
                                        </template>
                                        <template x-if="!fileItem.file">
                                            <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                        </template>

                                        <!-- View Button Overlay -->
                                        <button x-show="fileItem.file" type="button" @click="viewFile(fileItem)"
                                            class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-60 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100"
                                            title="Lihat file">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- File Input & Info -->
                                    <div class="flex-1 space-y-2">
                                        <!-- File Input -->
                                        <div class="relative">
                                            <input type="file" :name="'documents[' + index + ']'"
                                                accept="application/pdf,image/jpeg,image/jpg,image/png"
                                                @change="handleFileChange(index, $event)" :required="index === 0"
                                                class="w-full text-sm text-gray-600 dark:text-gray-400 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900/30 dark:file:text-indigo-400 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50 cursor-pointer">
                                        </div>

                                        <!-- File Info -->
                                        <div x-show="fileItem.file"
                                            class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-2 border border-gray-200 dark:border-gray-700">
                                            <div class="flex items-start justify-between gap-2">
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate"
                                                        x-text="fileItem.fileName"></p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400"
                                                        x-text="getFileSize(fileItem.file)"></p>
                                                </div>
                                                <div class="flex items-center gap-1 flex-shrink-0">
                                                    <!-- View Button -->
                                                    <button type="button" @click="viewFile(fileItem)"
                                                        class="p-1.5 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded transition-colors"
                                                        title="Lihat file">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                    </button>
                                                    <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Document Type Selector (hanya untuk dokumen ke-2 dst) -->
                                        <div x-show="index > 0">
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Jenis Dokumen
                                            </label>
                                            <select :name="'document_types[' + index + ']'" x-model="fileItem.type"
                                                class="w-full text-sm px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                                <option value="supporting_document">Dokumen Pendukung</option>
                                                <option value="photo">Foto Kegiatan</option>
                                                <option value="other">Lainnya</option>
                                            </select>
                                        </div>

                                        <!-- Hidden input untuk dokumen pertama (certificate) -->
                                        <input x-show="index === 0" type="hidden" :name="'document_types[' + index + ']'"
                                            value="certificate">
                                    </div>

                                    <!-- Remove Button (hanya untuk dokumen ke-2 dst) -->
                                    <button type="button" @click="removeFile(index)" x-show="index > 0"
                                        class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors flex-shrink-0"
                                        title="Hapus dokumen">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Add More Button -->
                    <button type="button" @click="addFile()"
                        class="w-full py-3 px-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-gray-600 dark:text-gray-400 hover:border-indigo-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/10 transition-colors flex items-center justify-center gap-2 font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Dokumen Pendukung
                    </button>

                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-3 flex items-start gap-2">
                        <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Format: PDF, JPG, PNG. Ukuran maksimal: 5MB per file. Dokumen pertama (Sertifikat/Piagam)
                            wajib diupload.</span>
                    </p>
                    @error('documents')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                    @error('documents.*')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Info Box -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex-shrink-0 w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-blue-800 dark:text-blue-300 text-sm">Tips Pengajuan Prestasi</h4>
                            <ul class="text-xs text-blue-600 dark:text-blue-400 mt-1 space-y-1">
                                <li>• Pastikan data yang diisi lengkap dan akurat</li>
                                <li>• Sertakan Sertifikat/Piagam resmi</li>
                                <li>• Tambahkan Surat Keterangan jika diperlukan</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-4">
                    <button type="submit" :disabled="loading || !hasAtLeastOneCertificate()"
                        class="w-full py-4 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-3">
                        <svg x-show="loading" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <svg x-show="!loading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span x-text="loading ? 'Mengirim...' : 'Ajukan Prestasi'"></span>
                    </button>
                    <p class="text-xs text-center text-gray-500 dark:text-gray-400 mt-2">
                        Pastikan minimal 1 sertifikat sudah diupload sebelum submit
                    </p>
                </div>
            </form>
        </x-card>
    </div>
@endsection

@push('styles')
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        function formComponent() {
            return {
                loading: false,
                files: [],
                addFile() {
                    this.files.push({
                        file: null,
                        type: this.files.length === 0 ? 'certificate' : 'supporting_document',
                        preview: null,
                        fileName: ''
                    });
                },
                removeFile(index) {
                    this.files.splice(index, 1);
                },
                handleFileChange(index, event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.files[index].file = file;
                        this.files[index].fileName = file.name;
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                this.files[index].preview = e.target.result;
                            };
                            reader.readAsDataURL(file);
                        } else {
                            this.files[index].preview = null;
                        }
                    }
                },
                hasAtLeastOneCertificate() {
                    return this.files.some(f => f.file && f.type === 'certificate');
                },
                getFileSize(file) {
                    if (!file) return '';
                    const bytes = file.size;
                    if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
                    if (bytes >= 1024) return (bytes / 1024).toFixed(2) + ' KB';
                    return bytes + ' bytes';
                },
                viewFile(fileItem) {
                    if (fileItem.preview) {
                        const win = window.open('', '_blank');
                        const html = `
                        <html>
                            <head>
                                <title>${fileItem.fileName}</title>
                                <style>
                                    body { margin: 0; display: flex; justify-content: center; align-items: center; min-height: 100vh; background: #000; }
                                    img { max-width: 100%; max-height: 100vh; }
                                </style>
                            </head>
                            <body>
                                <img src="${fileItem.preview}" />
                            </body>
                        </html>
                    `;
                        win.document.write(html);
                    } else if (fileItem.file) {
                        const url = URL.createObjectURL(fileItem.file);
                        window.open(url, '_blank');
                    }
                }
            }
        }
    </script>
@endpush